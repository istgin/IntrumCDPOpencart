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


function mapMethod(Config $config, $method) {
    $payment = $config->get("intrum_cdp_map_".$method);
    if ($payment) {
        return $payment;
    }
    return 'UNKNOWN';
}

function CreateCDPOpencartRequestIntrum($payment_address, $shipping_address, $session_data, $total, Config $config, $tmx, $customer_info) {

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
    } else {
        $userEmail = $customer_info["email"];
        $userPhone = $customer_info["telephone"];
        $userFax = $customer_info["fax"];
        $addressId = $payment_address["address_id"];
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

    $g = $config->get("intrum_cdp_gender_id");
    $request->setGender('0');
    $gender_male_possible_prefix = explode(";", strtolower($config->get("intrum_cdp_gender_male_possible_prefix_array")));
    $gender_female_possible_prefix = explode(";", strtolower($config->get("intrum_cdp_gender_female_possible_prefix_array")));

    if (isset($g) && intval($g) > 0 && isset($payment_address["custom_field"][$g])) {
        if (in_array(strtolower($payment_address["custom_field"][$g]), $gender_male_possible_prefix)) {
            $request->setGender('1');
        } else if (in_array(strtolower($payment_address["custom_field"][$g]), $gender_female_possible_prefix)) {
            $request->setGender('2');
        }
    }

    $d = $config->get("intrum_cdp_dob_id");
    if (isset($d) && intval($d) > 0 && isset($payment_address["custom_field"][$d])) {
        try {
            $dobObject = new DateTime($payment_address["custom_field"][$d]);
            if ($dobObject != null) {
                $request->setDateOfBirth($dobObject->format('Y-m-d'));
            }
        } catch (\Exception $e) {

        }
    }

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
    if (isset($shipping_address)) {
        $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
        $extraInfo["Value"] = $shipping_address['firstname'];
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'DELIVERY_LASTNAME';
        $extraInfo["Value"] = $shipping_address['lastname'];
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'DELIVERY_FIRSTLINE';
        $extraInfo["Value"] = trim($shipping_address['address_1'] . ' ' . $shipping_address['address_2']);
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
    }

    $extraInfo["Name"] = 'CONNECTIVTY_MODULE';
    $extraInfo["Value"] = 'Intrum Opencart module 1.0.0';
    $request->setExtraInfo($extraInfo);
    return $request;

}

function CreateCDPProceedOpencartRequestIntrum($order, Config $config, $tmx)
{
    $request = new ByjunoRequest();
    $request->setClientId($config->get("intrum_cdp_client_id"));
    $request->setUserID($config->get("intrum_cdp_user_id"));
    $request->setPassword($config->get("intrum_cdp_password"));
    $request->setVersion("1.00");
    $request->setRequestEmail($config->get("intrum_cdp_tech_email"));


    $lang = 'de';
    if (!empty($order["language_code"]) && strlen($order["language_code"]) > 4) {
        $lang = substr($order["language_code"], 0, 2);
    }
    $request->setLanguage($lang);

    $request->setRequestId(uniqid((String)$order["order_id"]));
    if ($order["customer_id"] == "0") {
        $request->setCustomerReference(uniqid("guest_"));
    } else {
        $request->setCustomerReference($order["customer_id"]);
    }
    $request->setFirstName((String)$order["payment_firstname"]);
    $request->setLastName((String)$order["payment_lastname"]);
    $request->setFirstLine(trim((String)$order["payment_address_1"].' '.$order["payment_address_2"]));
    $request->setCountryCode(strtoupper((String)$order["payment_iso_code_2"]));
    $request->setPostCode((String)$order["payment_postcode"]);
    $request->setTown((String)$order["payment_city"]);
    $request->setFax((String)$order["fax"]);

    if (!empty($order["payment_company"])) {
        $request->setCompanyName1($order["payment_company"]);
    }

    $g = $config->get("intrum_cdp_gender_id");
    $request->setGender('0');
    $gender_male_possible_prefix = explode(";", strtolower($config->get("intrum_cdp_gender_male_possible_prefix_array")));
    $gender_female_possible_prefix = explode(";", strtolower($config->get("intrum_cdp_gender_female_possible_prefix_array")));

    if (isset($g) && intval($g) > 0 && isset($order["payment_custom_field"][$g])) {
        if (in_array(strtolower($order["payment_custom_field"][$g]), $gender_male_possible_prefix)) {
            $request->setGender('1');
        } else if (in_array(strtolower($order["payment_custom_field"][$g]), $gender_female_possible_prefix)) {
            $request->setGender('2');
        }
    }

    $d = $config->get("intrum_cdp_dob_id");
    if (isset($d) && intval($d) > 0 && isset($order["payment_custom_field"][$d])) {
        try {
            $dobObject = new DateTime($order["payment_custom_field"][$d]);
            if ($dobObject != null) {
                $request->setDateOfBirth($dobObject->format('Y-m-d'));
            }
        } catch (\Exception $e) {

        }
    }

    $request->setTelephonePrivate((String)$order["telephone"]);
    $request->setEmail((String)$order["email"]);

    $extraInfo["Name"] = 'ORDERCLOSED';
    $extraInfo["Value"] = 'YES';
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'ORDERAMOUNT';
    $extraInfo["Value"] = $order["total"];
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'ORDERCURRENCY';
    $extraInfo["Value"] = $order["currency_code"];
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
    if (isset($order['shipping_firstname'])) {
        $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
        $extraInfo["Value"] = $order['shipping_firstname'];
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'DELIVERY_LASTNAME';
        $extraInfo["Value"] = $order['shipping_lastname'];
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'DELIVERY_FIRSTLINE';
        $extraInfo["Value"] = trim($order['shipping_address_1'] . ' ' . $order['shipping_address_2']);
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'DELIVERY_HOUSENUMBER';
        $extraInfo["Value"] = '';
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'DELIVERY_COUNTRYCODE';
        $extraInfo["Value"] = $order["shipping_iso_code_2"];
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'DELIVERY_POSTCODE';
        $extraInfo["Value"] = $order['shipping_postcode'];
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'DELIVERY_TOWN';
        $extraInfo["Value"] = $order['shipping_city'];
        $request->setExtraInfo($extraInfo);
    }
    $extraInfo["Name"] = 'PAYMENTMETHOD';
    $extraInfo["Value"] = mapMethod($config, $order["payment_code"]);
    $request->setExtraInfo($extraInfo);

    $extraInfo["Name"] = 'CONNECTIVTY_MODULE';
    $extraInfo["Value"] = 'Intrum Opencart module 1.0.0';
    $request->setExtraInfo($extraInfo);
    return $request;

}

function SaveLog($db, ByjunoRequest $request, $xml_request, $xml_response, $status, $type) {
    $sql     = '
            INSERT INTO '.DB_PREFIX.'plugin_byjuno_transactions
            SET
            requestid = \''.$db->escape($request->getRequestId()).'\',
            requesttype = \''.$db->escape($type).'\',
            firstname = \''.$db->escape($request->getFirstName()).'\',
            lastname = \''.$db->escape($request->getLastName()).'\',
            ip = \''.$db->escape($_SERVER['REMOTE_ADDR']).'\',
            status = \''.$db->escape((($status != 0) ? $status : 'Error')).'\',
            datecolumn = \''.$db->escape(date('Y-m-d\TH:i:sP')).'\',
            xml_request = \''.$db->escape($xml_request).'\',
            xml_responce = \''.$db->escape($xml_response).'\'
        ';
    $db->query($sql);
}