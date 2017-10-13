<?php
class ControllerExtensionModuleIntrumCdp extends Controller {
    public function eventShowPaymentMethods($route, &$data) {
        array_pop($data["payment_methods"]);
    }
}
