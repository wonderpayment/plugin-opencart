<?php
/**
 * WonderPayment 后台控制器
 * 兼容 OpenCart 1.x, 2.x, 3.x, 4.x
 */

class ControllerExtensionPaymentWonderpayment extends Controller {
    private $error = array();

    public function install() {
        $this->load->model('extension/payment/wonderpayment');
        $this->model_extension_payment_wonderpayment->install();
    }

    public function uninstall() {
        $this->load->model('extension/payment/wonderpayment');
        $this->model_extension_payment_wonderpayment->uninstall();
    }

    public function index() {
        $this->load->language('extension/payment/wonderpayment');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('localisation/currency');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_wonderpayment', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['appid'])) {
            $data['error_appid'] = $this->error['appid'];
        } else {
            $data['error_appid'] = '';
        }

        if (isset($this->error['private_key'])) {
            $data['error_private_key'] = $this->error['private_key'];
        } else {
            $data['error_private_key'] = '';
        }

        if (isset($this->error['public_key'])) {
            $data['error_public_key'] = $this->error['public_key'];
        } else {
            $data['error_public_key'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/wonderpayment', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/wonderpayment', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        // App ID
        if (isset($this->request->post['payment_wonderpayment_appid'])) {
            $data['payment_wonderpayment_appid'] = $this->request->post['payment_wonderpayment_appid'];
        } else {
            $data['payment_wonderpayment_appid'] = $this->config->get('payment_wonderpayment_appid');
        }

        // Private Key
        if (isset($this->request->post['payment_wonderpayment_private_key'])) {
            $data['payment_wonderpayment_private_key'] = $this->request->post['payment_wonderpayment_private_key'];
        } else {
            $data['payment_wonderpayment_private_key'] = $this->config->get('payment_wonderpayment_private_key');
        }

        // Public Key
        if (isset($this->request->post['payment_wonderpayment_public_key'])) {
            $data['payment_wonderpayment_public_key'] = $this->request->post['payment_wonderpayment_public_key'];
        } else {
            $data['payment_wonderpayment_public_key'] = $this->config->get('payment_wonderpayment_public_key');
        }

        // Environment
        if (isset($this->request->post['payment_wonderpayment_environment'])) {
            $data['payment_wonderpayment_environment'] = $this->request->post['payment_wonderpayment_environment'];
        } else {
            $data['payment_wonderpayment_environment'] = $this->config->get('payment_wonderpayment_environment') ? $this->config->get('payment_wonderpayment_environment') : 'stg';
        }

        // Total
        if (isset($this->request->post['payment_wonderpayment_total'])) {
            $data['payment_wonderpayment_total'] = $this->request->post['payment_wonderpayment_total'];
        } else {
            $data['payment_wonderpayment_total'] = $this->config->get('payment_wonderpayment_total');
        }

        // Language
        if (isset($this->request->post['payment_wonderpayment_language'])) {
            $data['payment_wonderpayment_language'] = $this->request->post['payment_wonderpayment_language'];
        } else {
            $data['payment_wonderpayment_language'] = $this->config->get('payment_wonderpayment_language');
        }

        // Order Status
        if (isset($this->request->post['payment_wonderpayment_order_status_id'])) {
            $data['payment_wonderpayment_order_status_id'] = $this->request->post['payment_wonderpayment_order_status_id'];
        } else {
            $data['payment_wonderpayment_order_status_id'] = $this->config->get('payment_wonderpayment_order_status_id');
        }

        // Refund Order Status
        if (isset($this->request->post['payment_wonderpayment_refund_order_status_id'])) {
            $data['payment_wonderpayment_refund_order_status_id'] = $this->request->post['payment_wonderpayment_refund_order_status_id'];
        } else {
            $data['payment_wonderpayment_refund_order_status_id'] = $this->config->get('payment_wonderpayment_refund_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // 加载语言列表
        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        // Admin Language
        if (isset($this->request->post['payment_wonderpayment_admin_language'])) {
            $data['payment_wonderpayment_admin_language'] = $this->request->post['payment_wonderpayment_admin_language'];
        } else {
            $data['payment_wonderpayment_admin_language'] = $this->config->get('payment_wonderpayment_admin_language') ?: $this->config->get('config_admin_language');
        }

        // Geo Zone
        if (isset($this->request->post['payment_wonderpayment_geo_zone_id'])) {
            $data['payment_wonderpayment_geo_zone_id'] = $this->request->post['payment_wonderpayment_geo_zone_id'];
        } else {
            $data['payment_wonderpayment_geo_zone_id'] = $this->config->get('payment_wonderpayment_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        // Status
        if (isset($this->request->post['payment_wonderpayment_status'])) {
            $data['payment_wonderpayment_status'] = $this->request->post['payment_wonderpayment_status'];
        } else {
            $data['payment_wonderpayment_status'] = $this->config->get('payment_wonderpayment_status');
        }

        // Sort Order
        if (isset($this->request->post['payment_wonderpayment_sort_order'])) {
            $data['payment_wonderpayment_sort_order'] = $this->request->post['payment_wonderpayment_sort_order'];
        } else {
            $data['payment_wonderpayment_sort_order'] = $this->config->get('payment_wonderpayment_sort_order');
        }

        // Debug
        if (isset($this->request->post['payment_wonderpayment_debug'])) {
            $data['payment_wonderpayment_debug'] = $this->request->post['payment_wonderpayment_debug'];
        } else {
            $data['payment_wonderpayment_debug'] = $this->config->get('payment_wonderpayment_debug');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['heading_title'] = $this->language->get('heading_title');

        // 传递 user_token 给模板
        if (isset($this->session->data['user_token'])) {
            $data['user_token'] = $this->session->data['user_token'];
        } else {
            $data['user_token'] = '';
        }

        // 传递语言文本给模板
        $data['entry_appid'] = $this->language->get('entry_appid');
        $data['entry_private_key'] = $this->language->get('entry_private_key');
        $data['entry_public_key'] = $this->language->get('entry_public_key');
        $data['entry_environment'] = $this->language->get('entry_environment');
        $data['entry_admin_language'] = $this->language->get('entry_admin_language');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_refund_order_status'] = $this->language->get('entry_refund_order_status');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_debug'] = $this->language->get('entry_debug');
        $data['entry_webhook_public_key'] = $this->language->get('entry_webhook_public_key');
        $data['entry_webhook_public_key_placeholder'] = $this->language->get('entry_webhook_public_key_placeholder');
        $data['entry_language'] = $this->language->get('entry_language');
        $data['entry_total'] = $this->language->get('entry_total');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_confirm_switch_account'] = $this->language->get('text_confirm_switch_account');
        $data['text_confirm_use_config'] = $this->language->get('text_confirm_use_config');
        $data['text_config_saved'] = $this->language->get('text_config_saved');
        $data['text_back'] = $this->language->get('text_back');
        $data['text_save'] = $this->language->get('text_save');
        $data['text_scan_qrcode'] = $this->language->get('text_scan_qrcode');
        $data['text_waiting_scan'] = $this->language->get('text_waiting_scan');
        $data['text_qrcode_expired'] = $this->language->get('text_qrcode_expired');
        $data['text_qrcode_expired_hint'] = $this->language->get('text_qrcode_expired_hint');
        $data['text_refresh'] = $this->language->get('text_refresh');
        $data['text_qrcode_config'] = $this->language->get('text_qrcode_config');
        $data['text_qrcode_help'] = $this->language->get('text_qrcode_help');
        $data['text_config_process_title'] = $this->language->get('text_config_process_title');
        $data['text_config_process_step1'] = $this->language->get('text_config_process_step1');
        $data['text_config_process_step2'] = $this->language->get('text_config_process_step2');
        $data['text_config_process_step3'] = $this->language->get('text_config_process_step3');
        $data['text_config_notice_title'] = $this->language->get('text_config_notice_title');
        $data['text_config_notice_item1'] = $this->language->get('text_config_notice_item1');
        $data['text_config_notice_item2'] = $this->language->get('text_config_notice_item2');
        $data['text_config_notice_item3'] = $this->language->get('text_config_notice_item3');
        $data['text_config_notice_item4'] = $this->language->get('text_config_notice_item4');
        $data['text_operation_instructions'] = $this->language->get('text_operation_instructions');
        $data['text_operation_step1'] = $this->language->get('text_operation_step1');
        $data['text_operation_step2'] = $this->language->get('text_operation_step2');
        $data['text_operation_step3'] = $this->language->get('text_operation_step3');
        $data['text_operation_step4'] = $this->language->get('text_operation_step4');
        $data['text_operation_select_step1'] = $this->language->get('text_operation_select_step1');
        $data['text_operation_select_step2'] = $this->language->get('text_operation_select_step2');
        $data['text_operation_select_step3'] = $this->language->get('text_operation_select_step3');
        $data['text_operation_select_step4'] = $this->language->get('text_operation_select_step4');
        $data['text_operation_config_step1'] = $this->language->get('text_operation_config_step1');
        $data['text_operation_config_step2'] = $this->language->get('text_operation_config_step2');
        $data['text_operation_config_step3'] = $this->language->get('text_operation_config_step3');
        $data['text_operation_config_step4'] = $this->language->get('text_operation_config_step4');
        $data['text_step_scan'] = $this->language->get('text_step_scan');
        $data['text_step_select'] = $this->language->get('text_step_select');
        $data['text_step_other_config'] = $this->language->get('text_step_other_config');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['text_qrcode_cancelled'] = $this->language->get('text_qrcode_cancelled');
        $data['text_select_business'] = $this->language->get('text_select_business');
        $data['text_confirm_business'] = $this->language->get('text_confirm_business');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_sandbox'] = $this->language->get('text_sandbox');
        $data['text_live'] = $this->language->get('text_live');
        $data['text_zh_cn'] = $this->language->get('text_zh_cn');
        $data['text_zh_tw'] = $this->language->get('text_zh_tw');
        $data['text_en_gb'] = $this->language->get('text_en_gb');
        $data['text_generating_keys'] = $this->language->get('text_generating_keys');
        $data['text_please_wait'] = $this->language->get('text_please_wait');
        $data['text_copied'] = $this->language->get('text_copied');
        $data['text_no_business_selected'] = $this->language->get('text_no_business_selected');
        $data['text_login_success'] = $this->language->get('text_login_success');
        $data['text_business_status_active'] = $this->language->get('text_business_status_active');
        $data['text_confirm_change_language'] = $this->language->get('text_confirm_change_language');
        $data['text_business_status_inactive'] = $this->language->get('text_business_status_inactive');
        $data['text_business_type'] = $this->language->get('text_business_type');
        $data['text_business_country'] = $this->language->get('text_business_country');
        $data['text_business_currency'] = $this->language->get('text_business_currency');
        $data['text_business_role'] = $this->language->get('text_business_role');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_generate_qrcode'] = $this->language->get('button_generate_qrcode');
        $data['button_refund'] = $this->language->get('button_refund');

        $data['help_order_status'] = $this->language->get('help_order_status');
        $data['help_refund_order_status'] = $this->language->get('help_refund_order_status');
        $data['help_geo_zone'] = $this->language->get('help_geo_zone');
        $data['help_admin_language'] = $this->language->get('help_admin_language');
        $data['help_status'] = $this->language->get('help_status');
        $data['help_sort_order'] = $this->language->get('help_sort_order');
        $data['help_debug'] = $this->language->get('help_debug');
        $data['help_webhook_public_key'] = $this->language->get('help_webhook_public_key');
        $data['help_environment'] = $this->language->get('help_environment');
        $data['help_total'] = $this->language->get('help_total');
        $data['help_setup'] = $this->language->get('help_setup');

        $this->response->setOutput($this->load->view('extension/payment/wonderpayment', $data));
    }

    /**
     * 退款功能 - 从订单详情页面调用
     */
    public function refund() {
        $this->load->language('extension/payment/wonderpayment');
        $this->load->model('extension/payment/wonderpayment');
        $this->load->model('sale/order');

        $json = array();

        if (isset($this->request->post['order_id'])) {
            $order_id = (int)$this->request->post['order_id'];
        } else {
            $json['error'] = $this->language->get('error_order_id');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if (isset($this->request->post['refund_amount'])) {
            $refundAmount = (float)$this->request->post['refund_amount'];
        } else {
            $json['error'] = $this->language->get('error_refund_amount_required');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if (isset($this->request->post['transaction_uuid'])) {
            $transaction_uuid = $this->request->post['transaction_uuid'];
        } else {
            $transaction_uuid = '';
        }

        if (!$this->user->hasPermission('modify', 'sale/return')) {
            $json['error'] = $this->language->get('error_permission_refund');
        } else {
            // 检查订单信息
            $order_info = $this->model_sale_order->getOrder($order_id);
            if (!$order_info) {
                $json['error'] = $this->language->get('error_order_not_found');
            } else {
                // 检查订单是否使用WonderPayment支付
                if ($order_info['payment_code'] != 'wonderpayment') {
                    $json['error'] = $this->language->get('error_payment_method_not_supported');
                } else {
                    $settlementCurrency = $order_info['currency_code'];
                    $rate = 1;

                    // 检查是否已退款
                    $this->model_extension_payment_wonderpayment->ensureRefundSchema();
                    $existing_refunds = $this->model_extension_payment_wonderpayment->getRefundsByOrderId($order_id);
                    $summary = $this->model_extension_payment_wonderpayment->getRefundSummary($order_id, $order_info['currency_code'], $settlementCurrency, $rate);
                    $total_refunded = $summary['total_original'];

                    if ($total_refunded >= $order_info['total']) {
                        $json['error'] = $this->language->get('error_order_fully_refunded');
                    } else {
                        // 验证退款金额
                        $available_refund = $order_info['total'] - $total_refunded;
                        if ($refundAmount > $available_refund) {
                            $json['error'] = $this->language->get('error_refund_amount_exceeded') . '，可用金额: ' . $this->currency->format($available_refund, $order_info['currency_code']);
                        } else if ($refundAmount <= 0) {
                            $json['error'] = $this->language->get('text_invalid_refund_amount');
                        } else {
                            try {
                                // 初始化 WonderPayment SDK
                                $this->load->library('wonderpayment');
                                
                                $wonderpayment_config = array(
                                    'appid' => $this->config->get('payment_wonderpayment_appid'),
                                    'signaturePrivateKey' => $this->config->get('payment_wonderpayment_private_key'),
                                    'webhookVerifyPublicKey' => $this->config->get('payment_wonderpayment_public_key'),
                                    'callback_url' => HTTPS_CATALOG . 'index.php?route=extension/payment/wonderpayment/callback',
                                    'redirect_url' => HTTPS_CATALOG . 'index.php?route=extension/payment/wonderpayment/redirect',
                                    'environment' => $this->config->get('payment_wonderpayment_environment') ?: 'stg'
                                );

                                $wonderpayment = new WonderPayment($wonderpayment_config, $this->log);

                                // 检查是否有交易UUID，如果没有则尝试从API获取
                                $payment_info = $this->model_extension_payment_wonderpayment->getOrderPaymentInfo($order_id);
                                if (empty($transaction_uuid) && !empty($payment_info['transaction_uuid'])) {
                                    $transaction_uuid = $payment_info['transaction_uuid'];
                                }

                                if (empty($transaction_uuid)) {
                                    // 尝试从API查询交易信息
                                    if (!empty($payment_info['reference_number'])) {
                                        $order_number = !empty($payment_info['order_number']) ? $payment_info['order_number'] : null;
                                        $query_response = $wonderpayment->queryOrder($payment_info['reference_number'], $order_number, null);

                                        if (isset($query_response['data']['order']['transactions']) && is_array($query_response['data']['order']['transactions'])) {
                                            $transactions = $query_response['data']['order']['transactions'];
                                            $matched = null;

                                            foreach ($transactions as $t) {
                                                if (isset($t['allow_refund']) && $t['allow_refund'] === true) {
                                                    $matched = $t;
                                                    break;
                                                }
                                            }

                                            if (!$matched && !empty($transactions)) {
                                                $matched = $transactions[0];
                                            }

                                            if ($matched && !empty($matched['uuid'])) {
                                                $transaction_uuid = $matched['uuid'];
                                            }
                                        } elseif (isset($query_response['data']['order']['transaction']['uuid'])) {
                                            $transaction_uuid = $query_response['data']['order']['transaction']['uuid'];
                                        }

                                        if (!empty($transaction_uuid)) {
                                            // 更新到数据库
                                            if (isset($this->model_extension_payment_wonderpayment) && method_exists($this->model_extension_payment_wonderpayment, 'updateTransactionUuid')) {
                                                $this->model_extension_payment_wonderpayment->updateTransactionUuid($order_id, $transaction_uuid);
                                            }
                                        }
                                    }
                                }

                                if (empty($transaction_uuid)) {
                                    $json['error'] = $this->language->get('error_invalid_transaction_uuid');
                                } else {
                                    // 调用退款API
                                    $reference_number = $payment_info['reference_number'];
                                    $order_number = isset($payment_info['order_number']) ? $payment_info['order_number'] : null;
                                    $refund_note = 'Manual refund from admin panel';
                                    $refundAmountSettlement = $refundAmount;

                                    $response = $wonderpayment->refundTransaction(
                                        $reference_number, // reference_number
                                        $transaction_uuid, // transaction_uuid
                                        $refundAmountSettlement, // refund_amount (settlement)
                                        $order_number, // order_number
                                        $refund_note // refund note
                                    );

                                    // 检查退款结果
                                    if (isset($response['code']) && $response['code'] == 200) {
                                        // 退款成功，记录退款信息
                                        $response['original_amount'] = $refundAmount;
                                        $response['original_currency'] = $order_info['currency_code'];
                                        $response['settlement_amount'] = $refundAmountSettlement;
                                        $response['settlement_currency'] = $settlementCurrency;

                                        $refund_data = array(
                                            'order_id' => $order_id,
                                            'reference_number' => $payment_info['reference_number'],
                                            'transaction_uuid' => $transaction_uuid,
                                            'refund_amount' => $refundAmountSettlement,
                                            'refund_currency' => $settlementCurrency,
                                            'refund_data' => json_encode($response),
                                            'status' => 'success',
                                            'date_added' => date('Y-m-d H:i:s')
                                        );

                                        // 开始数据库事务
                                        $this->db->query("START TRANSACTION");
                                        
                                        try {
                                            // 构建插入字段
                                            $insert_fields = array(
                                                "order_id = '" . (int)$refund_data['order_id'] . "'",
                                                "reference_number = '" . $this->db->escape($refund_data['reference_number']) . "'",
                                                "refund_amount = '" . (float)$refund_data['refund_amount'] . "'",
                                                "refund_currency = '" . $this->db->escape($refund_data['refund_currency']) . "'",
                                                "original_amount = '" . (float)$refundAmount . "'",
                                                "original_currency = '" . $this->db->escape($order_info['currency_code']) . "'",
                                                "refund_data = '" . $this->db->escape($refund_data['refund_data']) . "'",
                                                "status = '" . $this->db->escape($refund_data['status']) . "'",
                                                "date_added = '" . $refund_data['date_added'] . "'"
                                            );

                                            // 只有当transaction_uuid不为空时才添加
                                            if (!empty($refund_data['transaction_uuid'])) {
                                                $insert_fields[] = "transaction_uuid = '" . $this->db->escape($refund_data['transaction_uuid']) . "'";
                                            }

                                            $this->db->query("INSERT INTO " . DB_PREFIX . "wonderpayment_refund SET " . implode(', ', $insert_fields));

                                            // 更新OpenCart订单状态为已退款
                                            // 首先检查是否存在"Refunded"订单状态，如果没有则创建或使用相近的状态
                                            $refunded_status_id = $this->config->get('payment_wonderpayment_refund_order_status_id');
                                            if ($refunded_status_id) {
                                                $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '" . (int)$refunded_status_id . "' WHERE order_id = '" . (int)$order_id . "'");
                                                $this->log->write('OpenCart订单状态已更新为已退款 (状态ID: ' . $refunded_status_id . ')');
                                                
                                                // 添加订单历史记录
                                            $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$refunded_status_id . "', notify = '0', comment = 'WonderPayment退款成功，退款金额: " . $this->db->escape($this->currency->format($refundAmount, $order_info['currency_code'])) . " (" . $this->db->escape($this->currency->format($refundAmountSettlement, $settlementCurrency)) . ")', date_added = NOW()");
                                            } else {
                                                $this->log->write('警告: 未配置"Refunded"订单状态，无法更新订单状态');
                                            }
                                            
                                            // 提交事务
                                            $this->db->query("COMMIT");
                                            
                                            $json['success'] = $this->language->get('text_refund_success') . '！' . $this->language->get('text_refund_amount') . ': ' . $this->currency->format($refundAmount, $order_info['currency_code']) . ' (' . $this->currency->format($refundAmountSettlement, $settlementCurrency) . ')';
                                        } catch (Exception $e) {
                                            // 回滚事务
                                            $this->db->query("ROLLBACK");
                                            $this->log->write('退款数据库操作失败，已回滚事务: ' . $e->getMessage());
                                            $json['error'] = $this->language->get('error_refund_success') . '但' . $this->language->get('error_database_record_failed') . ': ' . $e->getMessage();
                                        }
                                    } else {
                                        if ($this->isInvalidCurrencyResponse($response)) {
                                            $json['error'] = $this->language->get('error_invalid_currency');
                                        } else {
                                            $json['error'] = $this->language->get('error_refund_failed') . ': ' . (isset($response['message']) ? $response['message'] : $this->language->get('error_unknown') . '，' . $this->language->get('text_response_code') . ': ' . (isset($response['code']) ? $response['code'] : 'N/A'));
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                $json['error'] = $this->language->get('error_refund_exception') . ': ' . $e->getMessage();
                            }
                        }
                    }
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * 更新后台语言设置
     */
    public function updateAdminLanguage() {
        $this->load->language('extension/payment/wonderpayment');

        $json = array();

        if (!isset($this->request->post['language'])) {
            $json['error'] = 'Language parameter is required';
        } else {
            $language = $this->request->post['language'];

            // 验证语言是否存在
            $this->load->model('localisation/language');
            $language_info = $this->model_localisation_language->getLanguageByCode($language);

            if ($language_info) {
                // 更新配置
                $this->load->model('setting/setting');

                // 更新系统的admin_language设置
                $this->model_setting_setting->editSettingValue('config', 'config_admin_language', $language);

                // 更新插件的admin_language设置
                $this->model_setting_setting->editSettingValue('payment_wonderpayment', 'payment_wonderpayment_admin_language', $language);

                $json['success'] = true;
            } else {
                $json['error'] = 'Invalid language code';
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function isInvalidCurrencyResponse($response) {
        if (!is_array($response)) {
            return false;
        }

        if (isset($response['message']) && is_string($response['message'])) {
            if (strtolower(trim($response['message'])) === 'invalid currency') {
                return true;
            }
        }

        if (isset($response['error_code']) && is_string($response['error_code'])) {
            if (strtoupper(trim($response['error_code'])) === 'EO100102') {
                return true;
            }
        }

        return false;
    }
    protected function logJson($log, $prefix, $data) {
        $log->write($prefix . json_encode($this->sanitizeLogData($data), JSON_UNESCAPED_UNICODE));
    }

    protected function sanitizeLogData($data) {
        $sensitive_keys = array(
            'email',
            'phone',
            'telephone',
            'address',
            'firstname',
            'lastname',
            'first_name',
            'last_name',
            'name',
            'token',
            'key',
            'private_key',
            'public_key'
        );

        if (is_array($data)) {
            $clean = array();
            foreach ($data as $key => $value) {
                if (is_string($key) && in_array(strtolower($key), $sensitive_keys, true)) {
                    $clean[$key] = '***';
                } else {
                    $clean[$key] = $this->sanitizeLogData($value);
                }
            }
            return $clean;
        }

        return $data;
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/wonderpayment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_wonderpayment_appid']) {
            $this->error['appid'] = $this->language->get('error_appid');
        }

        if (!$this->request->post['payment_wonderpayment_private_key']) {
            $this->error['private_key'] = $this->language->get('error_private_key');
        }

        if (!$this->request->post['payment_wonderpayment_public_key']) {
            $this->error['public_key'] = $this->language->get('error_public_key');
        }

        return !$this->error;
    }

    // ==================== QR Code Config Related Methods ====================

    /**
     * 生成二维码
     */
    public function qrcode() {
        $this->load->language('extension/payment/wonderpayment');

        $json = array();

        try {
            // 初始化日志
            $log = new Log('wonderpayment.log');
            
            // 获取当前选择的环境
            $environment = isset($this->request->post['environment']) ? $this->request->post['environment'] : 'stg';
            
            // 记录请求日志
            $log->write('=== qrcode 接口请求 ===');
            $log->write('环境: ' . $environment);
            $this->logJson($log, '请求数据: ', $this->request->post);

            // 加载 WonderPayment 库
            $lib_path = DIR_SYSTEM . 'library/wonderpayment.php';
            if (!file_exists($lib_path)) {
                throw new \Exception('WonderPayment 库文件不存在');
            }

            require_once $lib_path;

            // 初始化 SDK 用于二维码登录
            $wonderpayment = WonderPayment::createForQRCode($environment);

            // 生成二维码
            $qrCode = $wonderpayment->createQRCode();

            $json['success'] = true;
            $json['uuid'] = $qrCode['uuid'];
            $json['sUrl'] = $qrCode['sUrl'];
            $json['lUrl'] = $qrCode['lUrl'];
            $json['expiresAt'] = $qrCode['expiresAt'];

            // 记录响应日志
            $this->logJson($log, 'qrcode 接口响应: ', $json);

        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            // 记录错误日志
            $log = new Log('wonderpayment.log');
            $log->write('qrcode 接口错误: ' . $e->getMessage());
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * 轮询二维码状态
     */
    public function pollQRCodeStatus() {
        $json = array();

        if (!isset($this->request->post['uuid'])) {
            $json['success'] = false;
            $json['error'] = 'UUID 不能为空';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $uuid = $this->request->post['uuid'];
        $environment = isset($this->request->post['environment']) ? $this->request->post['environment'] : 'stg';

        try {
            // 初始化日志
            $log = new Log('wonderpayment.log');
            
            // 加载 WonderPayment 库
            $lib_path = DIR_SYSTEM . 'library/wonderpayment.php';
            require_once $lib_path;

            // 初始化 SDK 用于二维码登录
            $wonderpayment = WonderPayment::createForQRCode($environment);

            // 获取二维码状态
            $response = $wonderpayment->getQRCodeStatus($uuid);

            if (isset($response['data'])) {
                $data = $response['data'];

                $json['success'] = true;

                // 检查是否已过期
                if (isset($data['is_expired']) && $data['is_expired'] === true) {
                    $json['status'] = 'EXPIRED';
                }
                // 检查是否已取消
                elseif (isset($data['is_cancel']) && $data['is_cancel'] === true) {
                    $json['status'] = 'CANCELLED';
                }
                // 检查是否已扫描
                elseif (isset($data['is_scan']) && $data['is_scan'] === true) {
                    // 记录 access_token 的值
                    $log->write('轮询成功 - is_scan=true, access_token=' . (isset($data['access_token']) ? ($data['access_token'] === '' ? 'EMPTY STRING' : substr($data['access_token'], 0, 20) . '...') : 'NOT SET'));

                    // 只有当 access_token 存在且不为空时，才认为登录成功
                    if (isset($data['access_token']) && !empty($data['access_token']) && $data['access_token'] !== '') {
                        $json['status'] = 'SCANNED';
                        $json['accessToken'] = $data['access_token'];
                        $log->write('返回 SCANNED 状态和 accessToken 给前端');

                        // 如果有商家 ID，返回给前端
                        if (isset($data['business_id']) && !empty($data['business_id'])) {
                            $json['businessId'] = $data['business_id'];
                        }
                    } else {
                        // 已扫描但还没有 access_token，继续等待
                        $json['status'] = 'PENDING';
                        $log->write('已扫描但 access_token 为空，返回 PENDING 状态');
                    }
                }
                // 还在等待扫描
                else {
                    $json['status'] = 'PENDING';
                }
            } else {
                $json['success'] = false;
                $json['error'] = '响应格式错误';
            }

        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * 获取商家列表
     */
    public function getBusinesses() {
        $json = array();

        if (!isset($this->request->post['access_token'])) {
            $json['success'] = false;
            $json['error'] = '访问令牌不能为空';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $accessToken = $this->request->post['access_token'];
        $environment = isset($this->request->post['environment']) ? $this->request->post['environment'] : 'stg';

        try {
            // 初始化日志
            $log = new Log('wonderpayment.log');
            
            // 记录请求日志
            $log->write('=== getBusinesses 接口请求 ===');
            $log->write('环境: ' . $environment);
            $this->logJson($log, 'POST 数据: ', $this->request->post);
            $log->write('访问令牌: ' . (empty($accessToken) ? 'EMPTY' : substr($accessToken, 0, 20) . '...(' . strlen($accessToken) . ' chars)'));

            // 加载 WonderPayment 库
            $lib_path = DIR_SYSTEM . 'library/wonderpayment.php';
            require_once $lib_path;

            // 初始化 SDK 用于二维码登录
            $log->write('Calling createForQRCode with: environment=' . $environment . ', jwtToken=EMPTY, userAccessToken=' . (empty($accessToken) ? 'EMPTY' : 'SET (' . strlen($accessToken) . ' chars)'));
            $wonderpayment = WonderPayment::createForQRCode($environment, '', $accessToken);

            // 获取商家列表
            $response = $wonderpayment->getBusinesses();

            // 记录响应日志
            $this->logJson($log, 'getBusinesses 接口响应: ', $response);

            if (isset($response['data'])) {
                $json['success'] = true;
                $json['businesses'] = $response['data'];
            } else {
                $json['success'] = false;
                $json['error'] = '无法获取商家列表';
            }

        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            // 记录错误日志
            $log = new Log('wonderpayment.log');
            $log->write('getBusinesses 接口错误: ' . $e->getMessage());
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * 生成 App ID
     */
    public function generateAppId() {
        $json = array();

        if (!isset($this->request->post['business_id'])) {
            $json['success'] = false;
            $json['error'] = '商家 ID 不能为空';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if (!isset($this->request->post['access_token'])) {
            $json['success'] = false;
            $json['error'] = '访问令牌不能为空';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $businessId = $this->request->post['business_id'];
        $accessToken = $this->request->post['access_token'];
        $environment = isset($this->request->post['environment']) ? $this->request->post['environment'] : 'stg';

        try {
            // 初始化日志
            $log = new Log('wonderpayment.log');
            
            // 记录请求日志
            $log->write('=== generateAppId 接口请求 ===');
            $log->write('环境: ' . $environment);
            $log->write('商家 ID: ' . $businessId);

            // 加载 WonderPayment 库
            $lib_path = DIR_SYSTEM . 'library/wonderpayment.php';
            require_once $lib_path;

            // 使用 SDK 生成 RSA 密钥对（2048位）
            $log->write('开始生成 RSA 密钥对...');
            $keyPair = PaymentSDK::generateKeyPair(2048);
            $log->write('RSA 密钥对生成成功');

            // 初始化 SDK 用于二维码登录
            $wonderpayment = WonderPayment::createForQRCode($environment, '', $accessToken);

            // 使用生成的公钥调用 generateAppId
            $log->write('调用 generateAppId 接口...');
            $response = $wonderpayment->generateAppId($businessId, $keyPair['public_key']);

            // 记录响应日志
            $this->logJson($log, 'generateAppId 接口响应: ', $response);

            if (isset($response['data'])) {
                $data = $response['data'];

                // Wonder API returns webhook_public_key in base64; decode to PEM when possible.
                $webhookPublicKey = '';
                if (isset($data['webhook_public_key']) && $data['webhook_public_key'] !== '') {
                    $decodedKey = base64_decode($data['webhook_public_key'], true);
                    $webhookPublicKey = ($decodedKey !== false) ? trim($decodedKey) : $data['webhook_public_key'];
                }

                // 提取配置信息
                $config = array(
                    'appid' => isset($data['app_id']) ? $data['app_id'] : '',
                    'private_key' => $keyPair['private_key'], // 使用本地生成的私钥
                    'signature_public_key' => $keyPair['public_key'], // 本地签名公钥（非 webhook 公钥）
                    'webhook_public_key' => $webhookPublicKey // Wonder 回调验签公钥
                );

                $json['success'] = true;
                $json['config'] = $config;

                $log->write('配置生成成功: appid=' . $config['appid']);
            } else {
                $json['success'] = false;
                $json['error'] = '无法生成 App ID';
                $log->write('generateAppId 接口错误: 响应中没有 data 字段');
            }

        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            // 记录错误日志
            $log = new Log('wonderpayment.log');
            $log->write('generateAppId 接口错误: ' . $e->getMessage());
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
