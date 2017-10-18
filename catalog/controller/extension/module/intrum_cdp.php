<?php
require_once(DIR_SYSTEM . 'library/Intrum/api/byjuno.php');
require_once(DIR_SYSTEM . 'library/Intrum/api/helper.php');
class ControllerExtensionModuleIntrumCdp extends Controller {

    private function getTotal()
    {
        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        $sort_order = array();

        $results = $this->model_extension_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get($result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);

                // We have to put the totals in an array so that they pass by reference.
                $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
        }
        return $total;
    }

    public function eventShowPaymentMethods($route, &$data) {
        $this->load->model('extension/extension');
        $this->load->model('setting/setting');
        $totals = Array();

        /* @var $cart Cart\Cart */
        $cart = $this->cart;
        $request = CreateCDPOpencartRequestIntrum(
            $this->session->data["payment_address"],
            $this->session->data["shipping_address"],
            $this->session->data,
            $this->getTotal(),
            $this->config,
            ""
        );

        $mode = $this->config->get("intrum_cdp_mode");
        $b2b = $this->config->get("intrum_cdp_b2b");
        $statusLog = "CDP request";
        if ($request->getCompanyName1() != '' && $b2b == 'enable') {
            $statusLog = "CDP request for company";
            $xml = $request->createRequestCompany();
        } else {
            $xml = $request->createRequest();
        }
        $byjunoCommunicator = new ByjunoCommunicator();
        if (isset($mode) && $mode == 'Live') {
            $byjunoCommunicator->setServer('live');
        } else {
            $byjunoCommunicator->setServer('test');
        }
        $response = $byjunoCommunicator->sendRequest($xml);
        $statusCDP = 0;
        if (isset($response)) {
            $byjunoResponse = new ByjunoResponse();
            $byjunoResponse->setRawResponse($response);
            $byjunoResponse->processResponse();
            $statusCDP = (int)$byjunoResponse->getCustomerRequestStatus();
           // $this->saveLog($request, $xml, $response, $statusCDP, $statusLog);
            if (intval($statusCDP) > 15) {
                $statusCDP = 0;
            }
        }
        $disabled = $this->config->get("intrum_cdp_status_".$statusCDP);
        $unset = false;
        foreach($data["payment_methods"] as $method) {
            if (in_array($method["code"], $disabled)) {
                unset($data["payment_methods"][$method["code"]]);
                $unset = true;
            }
        }
        if ($unset && empty($data["payment_methods"])) {
            $this->load->language('checkout/checkout');
            $data['error_warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));
        }
    }

    public function eventSaveOrder($route, &$data) {
        $this->session->data["intrum_order"] = $this->session->data['order_id'];
    }

    public function eventSuccessBefore($route, &$data) {
        echo "get order info: ". $this->session->data['intrum_order'];
    }
}
