<?php
/**
 * The controller class must extend the parent class i.e. Controller
 * The controller name must be like Controller + directory path (with first character of each folder in capital) + file name (with first character in capital)
 * For version 2.3.0.0 and upper, the name of the controller must be ControllerExtensionModuleFirstModule
 */
class ControllerExtensionModuleIntrumCdp extends Controller {

    public function install()
    {
        $this->load->model('setting/setting');
        $this->load->model('extension/event');
        $this->model_extension_event->addEvent('cdp_payments', 'catalog/view/checkout/payment_method/before', 'extension/module/intrum_cdp/eventShowPaymentMethods');
        $this->model_extension_event->addEvent('cdp_payments_tmx', 'catalog/view/common/header/before', 'extension/module/intrum_cdp/eventShowTmx');
        $this->model_extension_event->addEvent('cdp_payments_saveorderid', 'catalog/controller/checkout/confirm/after', 'extension/module/intrum_cdp/eventSaveOrder');
        $this->model_extension_event->addEvent('cdp_payments_success', 'catalog/controller/checkout/success/after', 'extension/module/intrum_cdp/eventSuccessBefore');
        $this->db->query("
            CREATE TABLE `" . DB_PREFIX . "plugin_byjuno_transactions` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `requestid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
              `requesttype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `datecolumn` datetime NOT NULL,
              `xml_request` text COLLATE utf8_unicode_ci NOT NULL,
              `xml_responce` text COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		");
    }

    public function uninstall() {
        $this->load->model('setting/setting');
        $this->load->model('extension/event');

       // $settings = $this->model_setting_setting->getSetting('openbaypro');
       // $settings['openbaypro_status'] = 0;
       // $this->model_setting_setting->editSetting('openbaypro', $settings);

        $this->model_extension_event->deleteEvent('cdp_payments');
        $this->model_extension_event->deleteEvent('cdp_payments_tmx');
        $this->model_extension_event->deleteEvent('cdp_payments_saveorderid');
        $this->model_extension_event->deleteEvent('cdp_payments_success');
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "plugin_byjuno_transactions`");
    }
    /**
     * property named $error is defined to put errors
     * @var array
     */
    private $error = array();
    /**
     * Basic function of the controller. This can be called using route=module/intrum_cdp
     */

    private function getStatuses()
    {
        $status["status1"] = Array("id" => 1, "name" => "There are serious negative indicators (status 1)");
        $status["status2"] = Array("id" => 2, "name" => "All payment methods (status 2)");
        $status["status3"] = Array("id" => 3, "name" => "Manual post-processing (currently not yet in use) (status 3)");
        $status["status4"] = Array("id" => 4, "name" => "Postal address is incorrect (status 4)");
        $status["status5"] = Array("id" => 5, "name" => "Enquiry exceeds the credit limit (the credit limit is specified in the cooperation agreement) (status 5)");
        $status["status6"] = Array("id" => 6, "name" => "Customer specifications not met (optional) (status 6)");
        $status["status7"] = Array("id" => 7, "name" => "Enquiry exceeds the net credit limit (enquiry amount plus open items exceeds credit limit) (status 7)");
        $status["status8"] = Array("id" => 8, "name" => "Person queried is not of creditworthy age (status 8))");
        $status["status9"] = Array("id" => 9, "name" => "Delivery address does not match invoice address (for payment guarantee only) (status 9)");
        $status["status10"] = Array("id" => 10, "name" => "Household cannot be identified at this address (status 10))");
        $status["status11"] = Array("id" => 11, "name" => "Country is not supported (status 11)");
        $status["status12"] = Array("id" => 12, "name" => "Party queried is not a natural person (status 12)");
        $status["status13"] = Array("id" => 13, "name" => "System is in maintenance mode (status 13)");
        $status["status14"] = Array("id" => 14, "name" => "Address with high fraud risk (status 14)");
        $status["status15"] = Array("id" => 15, "name" => "Allowance is too low (status 15)");
        $status["status0"] = Array("id" => 0, "name" => "Fail to connect or Internal error (status Error)");

        return $status;
    }

    private function getPaymentMehods()
    {
        $this->load->model('extension/extension');

        $extensions = $this->model_extension_extension->getInstalled('payment');
        $data = array();

        $files = glob(DIR_APPLICATION . 'controller/{extension/payment,payment}/*.php', GLOB_BRACE);

        if ($files) {
            foreach ($files as $file) {
                $extension = basename($file, '.php');
                if (!in_array($extension, $extensions)) {
                    continue;
                }

                $this->load->language('extension/payment/' . $extension);

                $text_link = $this->language->get('text_' . $extension);

                if ($text_link != 'text_' . $extension) {
                    $link = $this->language->get('text_' . $extension);
                } else {
                    $link = '';
                }

                $data[] = array(
                    'code'       => $extension,
                    'name'       => $this->language->get('heading_title'),
                    'link'       => $link,
                    'status'     => $this->config->get($extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                    'sort_order' => $this->config->get($extension . '_sort_order'),
                    'install'   => $this->url->link('extension/extension/payment/install', 'token=' . $this->session->data['token'] . '&extension=' . $extension, true),
                    'uninstall' => $this->url->link('extension/extension/payment/uninstall', 'token=' . $this->session->data['token'] . '&extension=' . $extension, true),
                    'installed' => in_array($extension, $extensions),
                    'edit'      => $this->url->link('extension/payment/' . $extension, 'token=' . $this->session->data['token'], true)
                );
            }
        }
        return $data;
    }

    public function index() {
        /**
         * Loads the language file. Path of the file along with file name must be given
         */
        $this->load->language('extension/module/intrum_cdp');
        /**
         * Sets the title to the html page
         */
        $this->document->setTitle($this->language->get('heading_title'));
        /**
         * Loads the model file. Path of the file to be given
         */
        $this->load->model('setting/setting');
        /**
         * Checks whether the request type is post. If yes, then calls the validate function.
         */
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            /**
             * The function named 'editSetting' of a model is called in this way
             * The first argument is the code of the module and the second argument contains all the post values
             * The code must be same as your file name
             */
            $this->model_setting_setting->editSetting('intrum_cdp', $this->request->post);
            /**
             * The success message is kept in the session
             */
            $this->session->data['success'] = $this->language->get('text_success');
            /**
             * The redirection works in this way.
             * After insertion of data, it will redirect to extension/module file along with the token
             * The success message will be shown there
             */
            $this->response->redirect($this->url->link('extension/module/intrum_cdp', 'token=' . $this->session->data['token'], true));
        }
        /**
         * Putting the language into the '$data' array
         * This is the way how you get the language from the language file
         */
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        /**
         * If there is any warning in the private property '$error', then it will be put into '$data' array
         */
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        /**
         * Breadcrumbs are declared as array
         */
        $data['breadcrumbs'] = array();
        /**
         * Breadcrumbs are defined
         */
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/intrum_cdp', 'token=' . $this->session->data['token'], true)
        );
        /**
         * Form action url is created and defined to $data['action']
         */
        $data['action'] = $this->url->link('extension/module/intrum_cdp', 'token=' . $this->session->data['token'], true);
        /**
         * Cancel/back button url which will lead you to module list
         */
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);
        /**
         * checks whether the value exists in the post request
         */
        if (isset($this->request->post['intrum_cdp_status'])) {
            $data['intrum_cdp_status'] = $this->request->post['intrum_cdp_status'];
        } else {
            $data['intrum_cdp_status'] = $this->config->get('intrum_cdp_status');
        }
        if (isset($this->request->post['intrum_cdp_mode'])) {
            $data['intrum_cdp_mode'] = $this->request->post['intrum_cdp_mode'];
        } else {
            $data['intrum_cdp_mode'] = $this->config->get('intrum_cdp_mode');
        }
        if (isset($this->request->post['intrum_cdp_b2b'])) {
            $data['intrum_cdp_b2b'] = $this->request->post['intrum_cdp_b2b'];
        } else {
            $data['intrum_cdp_b2b'] = $this->config->get('intrum_cdp_b2b');
        }
        if (isset($this->request->post['intrum_cdp_client_id'])) {
            $data['intrum_cdp_client_id'] = $this->request->post['intrum_cdp_client_id'];
        } else {
            $data['intrum_cdp_client_id'] = $this->config->get('intrum_cdp_client_id');
        }
        if (isset($this->request->post['intrum_cdp_user_id'])) {
            $data['intrum_cdp_user_id'] = $this->request->post['intrum_cdp_user_id'];
        } else {
            $data['intrum_cdp_user_id'] = $this->config->get('intrum_cdp_user_id');
        }
        if (isset($this->request->post['intrum_cdp_password'])) {
            $data['intrum_cdp_password'] = $this->request->post['intrum_cdp_password'];
        } else {
            $data['intrum_cdp_password'] = $this->config->get('intrum_cdp_password');
        }
        if (isset($this->request->post['intrum_cdp_tech_email'])) {
            $data['intrum_cdp_tech_email'] = $this->request->post['intrum_cdp_tech_email'];
        } else {
            $data['intrum_cdp_tech_email'] = $this->config->get('intrum_cdp_tech_email');
        }
        if (isset($this->request->post['intrum_cdp_threatmetrix_enabled'])) {
            $data['intrum_cdp_threatmetrix_enabled'] = $this->request->post['intrum_cdp_threatmetrix_enabled'];
        } else {
            $data['intrum_cdp_threatmetrix_enabled'] = $this->config->get('intrum_cdp_threatmetrix_enabled');
        }
        if (isset($this->request->post['intrum_cdp_threatmetrix_id'])) {
            $data['intrum_cdp_threatmetrix_id'] = $this->request->post['intrum_cdp_threatmetrix_id'];
        } else {
            $data['intrum_cdp_threatmetrix_id'] = $this->config->get('intrum_cdp_threatmetrix_id');
        }
        if (isset($this->request->post['intrum_cdp_gender_id'])) {
            $data['intrum_cdp_gender_id'] = $this->request->post['intrum_cdp_gender_id'];
        } else {
            $data['intrum_cdp_gender_id'] = $this->config->get('intrum_cdp_gender_id');
        }
        if (isset($this->request->post['intrum_cdp_gender_male_possible_prefix_array'])) {
            $data['intrum_cdp_gender_male_possible_prefix_array'] = $this->request->post['intrum_cdp_gender_male_possible_prefix_array'];
        } else {
            $data['intrum_cdp_gender_male_possible_prefix_array'] = $this->config->get('intrum_cdp_gender_male_possible_prefix_array');
        }
        if (isset($this->request->post['intrum_cdp_gender_female_possible_prefix_array'])) {
            $data['intrum_cdp_gender_female_possible_prefix_array'] = $this->request->post['intrum_cdp_gender_female_possible_prefix_array'];
        } else {
            $data['intrum_cdp_gender_female_possible_prefix_array'] = $this->config->get('intrum_cdp_gender_female_possible_prefix_array');
        }
        if (isset($this->request->post['intrum_cdp_dob_id'])) {
            $data['intrum_cdp_dob_id'] = $this->request->post['intrum_cdp_dob_id'];
        } else {
            $data['intrum_cdp_dob_id'] = $this->config->get('intrum_cdp_dob_id');
        }
        for ($i = 0 ; $i <= 15; $i++) {
            if (isset($this->request->post['status_'.$i])) {
                $data['intrum_cdp_status_'.$i] = $this->request->post['intrum_cdp_status_'.$i];
            } else {
                $data['intrum_cdp_status_'.$i] = $this->config->get('intrum_cdp_status_'.$i);
            }
            if (!isset($data['intrum_cdp_status_'.$i])) {
                $data['intrum_cdp_status_'.$i] = Array();
            }
        }
        $payments = $this->getPaymentMehods();
        $data["payment_methods"] = $payments;
        $data["statuses"] = $this->getStatuses();
        /**
         * Header data is loaded
         */
        $data['header'] = $this->load->controller('common/header');
        /**
         * Column left part is loaded
         */
        $data['column_left'] = $this->load->controller('common/column_left');
        /**
         * Footer data is loaded
         */
        $data['footer'] = $this->load->controller('common/footer');
        /**
         * Using this function tpl file is called and all the data of controller is passed through '$data' array
         * This is for Opencart 2.2.0.0 version. There will be minor changes as per the version.
         */


        $data['intrumlogtab'] = $this->url->link('extension/module/intrum_cdp/intrumlog', 'token=' . $this->session->data['token'], true);
        $data['intrumsettingstab'] = $this->url->link('extension/module/intrum_cdp', 'token=' . $this->session->data['token'], true);

        $this->response->setOutput($this->load->view('extension/module/intrum_cdp', $data));
    }
    /**
     * validate function validates the values of the post and also the permission
     * @return boolean return true if any of the index of $error contains value
     */
    protected function validate() {
        /**
         * Check whether the current user has the permissions to modify the settings of the module
         * The permissions are set in System->Users->User groups
         */
        if (!$this->user->hasPermission('modify', 'extension/module/intrum_cdp')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function intrumlog() {

        $data = Array();
        /**
         * Loads the language file. Path of the file along with file name must be given
         */
        $this->load->language('extension/module/intrum_cdp');
        /**
         * Sets the title to the html page
         */
        $this->document->setTitle($this->language->get('heading_title'));
        /**
         * Loads the model file. Path of the file to be given
         */
        $this->load->model('setting/setting');
        $this->load->model('extension/intrum_log');

        $data['heading_title'] = "Intrum CDP transaction logs";

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        /**
         * If there is any warning in the private property '$error', then it will be put into '$data' array
         */
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        /**
         * Breadcrumbs are declared as array
         */
        $data['breadcrumbs'] = array();
        /**
         * Breadcrumbs are defined
         */
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/intrum_cdp', 'token=' . $this->session->data['token'], true)
        );
        /**
         * Cancel/back button url which will lead you to module list
         */
        $data['cancel'] = $this->url->link('extension/module/intrum_cdp', 'token=' . $this->session->data['token'] . '&type=module', true);

        $sort = 'c.id';
        $order = 'DESC';

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        $url = '';
        $data['logs'] = array();
        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $logs_total = $this->model_extension_intrum_log->getTotalLogs();

        $results = $this->model_extension_intrum_log->getLogs($filter_data);

        foreach ($results as $result) {
            $data['logs'][] = array(
                'id' => $result['id'],
                'requestid' => $result['requestid'],
                'requesttype'    => $result['requesttype'],
                'firstname'    => $result['firstname'],
                'lastname'    => $result['lastname'],
                'ip'    => $result['ip'],
                'status'    => $result['status'],
                'datecolumn'    => $result['datecolumn'],
                'edit'    => $this->url->link('extension/module/intrum_cdp/edit', 'token=' . $this->session->data['token'] . '&logid=' . $result['id'] . $url, true)
            );
        }

        $data['text_list'] = $this->language->get('text_list');
        $data['button_edit'] = $this->language->get('button_edit');


        $url = '';
        $pagination = new Pagination();
        $pagination->total = $logs_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('extension/module/intrum_cdp/intrumlog', 'token=' . $this->session->data['token'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($logs_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($logs_total - $this->config->get('config_limit_admin'))) ? $logs_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $logs_total, ceil($logs_total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['intrumlogtab'] = $this->url->link('extension/module/intrum_cdp/intrumlog', 'token=' . $this->session->data['token'], true);
        $data['intrumsettingstab'] = $this->url->link('extension/module/intrum_cdp', 'token=' . $this->session->data['token'], true);

        $this->response->setOutput($this->load->view('extension/module/intrum_cdp_logs', $data));

    }

    public function edit() {

        $data = Array();
        /**
         * Loads the language file. Path of the file along with file name must be given
         */
        $this->load->language('extension/module/intrum_cdp');
        /**
         * Sets the title to the html page
         */
        $this->document->setTitle($this->language->get('heading_title'));
        /**
         * Loads the model file. Path of the file to be given
         */
        $this->load->model('setting/setting');
        $this->load->model('extension/intrum_log');

        $data['heading_title'] = "Intrum CDP transaction";

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        /**
         * If there is any warning in the private property '$error', then it will be put into '$data' array
         */
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        /**
         * Breadcrumbs are declared as array
         */
        $data['breadcrumbs'] = array();
        /**
         * Breadcrumbs are defined
         */
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/intrum_cdp', 'token=' . $this->session->data['token'], true)
        );
        /**
         * Cancel/back button url which will lead you to module list
         */
        $data['cancel'] = 'javascript:window.history.back()';

        $logid = 0;
        if (isset($this->request->get['logid'])) {
            $logid = $this->request->get['logid'];
        }

        $data["log"] = $this->model_extension_intrum_log->getLog($logid);
        if ($data["log"]) {
            $domInput = new DOMDocument();
            $domInput->preserveWhiteSpace = FALSE;
            $domInput->loadXML($data["log"]["xml_request"]);
            $elem = $domInput->getElementsByTagName('Request');
            $elem->item(0)->removeAttribute("UserID");
            $elem->item(0)->removeAttribute("Password");

            $domInput->formatOutput = TRUE;
            libxml_use_internal_errors(true);
            $testXml = simplexml_load_string($data["log"]["xml_responce"]);
            $domOutput = new \DOMDocument();
            $domOutput->preserveWhiteSpace = FALSE;
            $data["log"]["xml_request"] = '<code style="width: 100%; word-wrap: break-word; white-space: pre-wrap;">'.htmlspecialchars($domInput->saveXml()).'</code>';
            if ($testXml) {
                $domOutput->loadXML($data["log"]["xml_responce"]);
                $domOutput->formatOutput = TRUE;
                $data["log"]["xml_responce"] = '<code style="width: 100%; word-wrap: break-word; white-space: pre-wrap;">'.htmlspecialchars($domOutput->saveXml()).'</code>';
            }
            else {
                $data["log"]["xml_responce"] = 'Response empty';
            }
        }



        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['intrumlogtab'] = $this->url->link('extension/module/intrum_cdp/intrumlog', 'token=' . $this->session->data['token'], true);
        $data['intrumsettingstab'] = $this->url->link('extension/module/intrum_cdp', 'token=' . $this->session->data['token'], true);

        $this->response->setOutput($this->load->view('extension/module/intrum_cdp_log_view', $data));

    }
}