<?php

function getClientIp() {
    $ipaddress = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if(!empty($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if(!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if(!empty($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if(!empty($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }
    $ipd = explode(",", $ipaddress);
    return trim(end($ipd));
}



function CreateCDPOpencartRequestIntrum($payment_address, $shipping_address, $session_data, $total, Config $config, $tmx) {

    $request = new ByjunoRequest();
    $request->setClientId($config->get("intrum_cdp_client_id"));
    $request->setUserID($config->get("intrum_cdp_user_id"));
    $request->setPassword($config->get("intrum_cdp_password"));
    $request->setVersion("1.00");
    $request->setRequestEmail($config->get("intrum_cdp_tech_email"));
    $userEmail = '';
    $userPhone = '';
    $userFax = '';
    $addressId = '';
    if (!empty($session_data["guest"])) {
        $userEmail = $session_data["guest"]["email"];
        $userPhone = $session_data["guest"]["telephone"];
        $userFax = $session_data["guest"]["fax"];
        $addressId = "guest";
    }

    $lang = 'de';
    if (!empty($session_data["language"]) && strlen($session_data["language"]) > 4) {
        $lang = substr($session_data["language"], 0, 2);
    }
    $request->setLanguage($lang);
    $request->setRequestId(uniqid((String)$addressId."_"));
    if (empty($reference)) {
        $request->setCustomerReference(uniqid("guest_"));
    } else {
        $request->setCustomerReference($session_data["customer_id"]);
    }
    $request->setFirstName((String)$payment_address['firstname']);
    $request->setLastName((String)$payment_address['lastname']);
    $request->setFirstLine(trim((String)$payment_address['address_1'].' '.$payment_address['address_2']));
    $request->setCountryCode(strtoupper((String)$payment_address["iso_code_2"]));
    $request->setPostCode((String)$payment_address['postcode']);
    $request->setTown((String)$payment_address['city']);
    $request->setFax((String)$userFax);

    if (!empty($payment_address["company"])) {
        $request->setCompanyName1($payment_address["company"]);
    }

    $request->setGender(0);
    $request->setTelephonePrivate((String)$userPhone);
    $request->setEmail($userEmail);

    $extraInfo["Name"] = 'ORDERCLOSED';
    $extraInfo["Value"] = "NO";
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'ORDERAMOUNT';
    $extraInfo["Value"] = $total;
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'ORDERCURRENCY';
    $extraInfo["Value"] = $session_data["currency"];
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'IP';
    $extraInfo["Value"] = getClientIp();
    $request->setExtraInfo($extraInfo);

    if (!empty($tmx)) {
        $extraInfo["Name"] = 'DEVICE_FINGERPRINT_ID';
        $extraInfo["Value"] = $tmx;
        $request->setExtraInfo($extraInfo);
    }

    /* shipping information */
    $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
    $extraInfo["Value"] = $shipping_address['firstname'];
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_LASTNAME';
    $extraInfo["Value"] = $shipping_address['lastname'];
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_FIRSTLINE';
    $extraInfo["Value"] = trim($shipping_address['address_1'].' '.$shipping_address['address_2']);
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_HOUSENUMBER';
    $extraInfo["Value"] = '';
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_COUNTRYCODE';
    $extraInfo["Value"] = $shipping_address["iso_code_2"];
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_POSTCODE';
    $extraInfo["Value"] = $shipping_address['postcode'];
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_TOWN';
    $extraInfo["Value"] = $shipping_address['city'];
    $request->setExtraInfo($extraInfo);

    if (!empty($orderId)) {
        $extraInfo["Name"] = 'ORDERID';
        $extraInfo["Value"] = $orderId;
        $request->setExtraInfo($extraInfo);
    }
    $extraInfo["Name"] = 'PAYMENTMETHOD';
    $extraInfo["Value"] = 'XXX';///mapMethod($paymentmethod);
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'CONNECTIVTY_MODULE';
    $extraInfo["Value"] = 'Intrum Opencart module 1.0.0';
    $request->setExtraInfo($extraInfo);
    return $request;

}

function CreateCDPProceedOpencartRequestIntrum(\Shopware_Controllers_Frontend_PaymentInvoice $order)
{
    /* @var \Shopware\Models\Order\Billing $billing */
    $billing = $order->getBilling();
    /* @var \Shopware\Models\Order\Shipping $shipping */
    $shipping = $order->getShipping();
    $request = new \ByjunoRequest();
    $request->setClientId(Shopware()->Config()->getByNamespace("ByjunoPayments", "byjuno_clientid"));
    $request->setUserID(Shopware()->Config()->getByNamespace("ByjunoPayments", "byjuno_userid"));
    $request->setPassword(Shopware()->Config()->getByNamespace("ByjunoPayments", "byjuno_password"));
    $request->setVersion("1.00");
    $request->setRequestEmail(Shopware()->Config()->getByNamespace("ByjunoPayments", "byjuno_techemail"));


    $sql     = 'SELECT `locale` FROM s_core_locales WHERE id = ' . intval(Shopware()->Shop()->getLocale()->getId());
    $langName = Shopware()->Db()->fetchRow($sql);
    $lang = 'de';
    if (!empty($langName["locale"]) && strlen($langName["locale"]) > 4) {
        $lang = substr($langName["locale"], 0, 2);
    }
    $request->setLanguage($lang);

    $request->setRequestId(uniqid((String)$billing->getId()));
    $reference = $billing->getCustomer();
    if (empty($reference)) {
        $request->setCustomerReference("guest_".$billing->getId());
    } else {
        $request->setCustomerReference($billing->getCustomer()->getId());
    }
    $request->setFirstName((String)$billing->getFirstName());
    $request->setLastName((String)$billing->getLastName());
    $request->setFirstLine(trim((String)$billing->getStreet().' '.$billing->getAdditionalAddressLine1().' '.$billing->getAdditionalAddressLine1()));
    $request->setCountryCode(strtoupper((String)$billing->getCountry()->getIso()));
    $request->setPostCode((String)$billing->getZipCode());
    $request->setTown((String)$billing->getCity());

	if (!empty($reference) && !empty($billing->getCustomer()->getBirthday()) && substr($billing->getCustomer()->getBirthday(), 0, 4) != '0000') {
		$request->setDateOfBirth((String)$billing->getCustomer()->getBirthday());
	}

    $request->setTelephonePrivate((String)$billing->getPhone());
    if (!empty($reference)) {
        $request->setEmail((String)$billing->getCustomer()->getEmail());
    }

    $extraInfo["Name"] = 'ORDERCLOSED';
    $extraInfo["Value"] = 'NO';
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'ORDERAMOUNT';
    $extraInfo["Value"] = $order->getInvoiceAmount();
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'ORDERCURRENCY';
    $extraInfo["Value"] = $order->getCurrency();
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'IP';
    $extraInfo["Value"] = getClientIp();
    $request->setExtraInfo($extraInfo);

    $tmx_enable = Shopware()->Config()->getByNamespace("ByjunoPayments", "byjuno_threatmetrixenable");
    $tmxorgid = Shopware()->Config()->getByNamespace("ByjunoPayments", "byjuno_threatmetrix");
    if (isset($tmx_enable) && $tmx_enable == 'Enabled' && isset($tmxorgid) && $tmxorgid != '' && !empty($_SESSION["byjuno_tmx"])) {
        $extraInfo["Name"] = 'DEVICE_FINGERPRINT_ID';
        $extraInfo["Value"] = $_SESSION["byjuno_tmx"];
        $request->setExtraInfo($extraInfo);
    }

    /* shipping information */
    $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
    $extraInfo["Value"] = $shipping->getFirstName();
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_LASTNAME';
    $extraInfo["Value"] = $shipping->getLastName();
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_FIRSTLINE';
    $extraInfo["Value"] = trim((String)$shipping->getStreet().' '.$shipping->getAdditionalAddressLine1().' '.$shipping->getAdditionalAddressLine1());
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_HOUSENUMBER';
    $extraInfo["Value"] = '';
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_COUNTRYCODE';
    $extraInfo["Value"] = $shipping->getCountry()->getIso();
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_POSTCODE';
    $extraInfo["Value"] = $shipping->getZipCode();
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'DELIVERY_TOWN';
    $extraInfo["Value"] = $shipping->getCity();
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'CONNECTIVTY_MODULE';
    $extraInfo["Value"] = 'Byjuno ShopWare module 1.3.0';
    $request->setExtraInfo($extraInfo);
    return $request;

}

function SaveLog(ByjunoS4Request $request, $xml_request, $xml_response, $status, $type, $firstName, $lastName)
{
    $sql     = '
            INSERT INTO s_plugin_byjuno_transactions (requestid, requesttype, firstname, lastname, ip, status, datecolumn, xml_request, xml_responce)
                    VALUES (?,?,?,?,?,?,?,?,?)
        ';
    Shopware()->Db()->query($sql, Array(
        $request->getRequestId(),
        $type,
        $firstName,
        $lastName,
        $_SERVER['REMOTE_ADDR'],
        (($status != "") ? $status : 'Error'),
        date('Y-m-d\TH:i:sP'),
        $xml_request,
        $xml_response
    ));
}