<?php
class ControllerExtensionModuleIntrumCdp extends Controller {

    public function eventShowPaymentMethods($route, &$data) {
        echo '<pre>';
        var_dump($this->session->data["shipping_address"]);
        var_dump($this->session->data["payment_address"]);
        echo '</pre>';
        array_pop($data["payment_methods"]);
    }

    public function eventSaveOrder($route, &$data) {
        $this->session->data["intrum_order"] = $this->session->data['order_id'];
    }

    public function eventSuccessBefore($route, &$data) {
        echo "get order info: ". $this->session->data['intrum_order'];
    }
}
