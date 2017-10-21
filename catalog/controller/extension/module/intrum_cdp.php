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
        $this->load->model('account/customer');
        $totals = Array();
        $shippingAddress = null;
        if (isset($this->session->data["shipping_address"])) {
            $shippingAddress = $this->session->data["shipping_address"];
        }

        /* @var $cart Cart\Cart */
        $cart = $this->cart;
        $tmx = "";
        $intrum_cdp_threatmetrix_enabled = $this->config->get("intrum_cdp_threatmetrix_enabled");
        $intrum_cdp_threatmetrix_id = $this->config->get("intrum_cdp_threatmetrix_id");
        if ($intrum_cdp_threatmetrix_enabled == 'enabled' && !empty($intrum_cdp_threatmetrix_id) && !empty($this->session->data["intrum_tmx"])) {
            $tmx = $this->session->data["intrum_tmx"];
        }
        $customer_info = array();
        if (!empty($this->session->data["customer_id"])) {
            $customer_info = $this->model_account_customer->getCustomer($this->session->data["customer_id"]);
        }

        $request = CreateCDPOpencartRequestIntrum(
            $this->session->data["payment_address"],
            $shippingAddress,
            $this->session->data,
            $this->getTotal(),
            $this->config,
            $tmx,
            $customer_info
        );

        $mode = $this->config->get("intrum_cdp_mode");
        $b2b = $this->config->get("intrum_cdp_b2b");
        $statusLog = "CDP request";
        if ($request->getCompanyName1() != '' && $b2b == 'enabled') {
            $statusLog = "CDP request for company";
            $xml = $request->createRequestCompany();
        } else {
            $xml = $request->createRequest();
        }
        $byjunoCommunicator = new ByjunoCommunicator();
        if (isset($mode) && $mode == 'live') {
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
            SaveLog($this->db, $request, $xml, $response, $statusCDP, $statusLog);
            if (intval($statusCDP) > 15) {
                $statusCDP = 0;
            }
        }
        $disabled = $this->config->get("intrum_cdp_status_".$statusCDP);
        if (!empty($disabled)) {
            $unset = false;
            foreach ($data["payment_methods"] as $method) {
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
    }

    public function eventShowTmx($route, &$data) {
        $this->load->model('setting/setting');
        $intrum_cdp_threatmetrix_enabled = $this->config->get("intrum_cdp_threatmetrix_enabled");
        $intrum_cdp_threatmetrix_id = $this->config->get("intrum_cdp_threatmetrix_id");
        if ($intrum_cdp_threatmetrix_enabled == 'enabled' && !empty($intrum_cdp_threatmetrix_id) && !isset($this->session->data["intrum_tmx"])) {
            $this->session->data["intrum_tmx"] = session_id();
            $session = $this->session->data["intrum_tmx"];
            $data["analytics"][] = '<script type="text/javascript" src="https://h.online-metrix.net/fp/tags.js?org_id='.$intrum_cdp_threatmetrix_id.'&session_id='.$session.'&pageid=checkout"></script>
<noscript>
    <iframe style="width: 100px; height: 100px; border: 0; position: absolute; top: -5000px;" src="https://h.online-metrix.net/tags?org_id='.$intrum_cdp_threatmetrix_id.'&session_id='.$session.'&pageid=checkout"></iframe>
</noscript>';
        }
    }

    public function eventSaveOrder($route, &$data) {
        $this->session->data["intrum_order"] = $this->session->data['order_id'];
    }

    public function eventSuccessBefore($route, &$data) {
        $this->load->model('checkout/order');
        $this->load->model('setting/setting');
        $this->load->model('account/customer');
        $orderDetails = $this->model_checkout_order->getOrder($this->session->data['intrum_order']);

        $tmx = "";
        $intrum_cdp_threatmetrix_enabled = $this->config->get("intrum_cdp_threatmetrix_enabled");
        $intrum_cdp_threatmetrix_id = $this->config->get("intrum_cdp_threatmetrix_id");
        if ($intrum_cdp_threatmetrix_enabled == 'enabled' && !empty($intrum_cdp_threatmetrix_id) && !empty($this->session->data["intrum_tmx"])) {
            $tmx = $this->session->data["intrum_tmx"];
        }

        $request = CreateCDPProceedOpencartRequestIntrum(
            $orderDetails,
            $this->config,
            $tmx
        );

        $mode = $this->config->get("intrum_cdp_mode");
        $b2b = $this->config->get("intrum_cdp_b2b");
        $statusLog = "CDP order complete";
        if ($request->getCompanyName1() != '' && $b2b == 'enabled') {
            $statusLog = "CDP order complete for company";
            $xml = $request->createRequestCompany();
        } else {
            $xml = $request->createRequest();
        }
        $byjunoCommunicator = new ByjunoCommunicator();
        if (isset($mode) && $mode == 'live') {
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
            SaveLog($this->db, $request, $xml, $response, $statusCDP, $statusLog);
            if (intval($statusCDP) > 15) {
                $statusCDP = 0;
            }
        }
    }
}
