<?php
/**
 * WonderPayment 前台控制器
 * 兼容 OpenCart 1.x, 2.x, 3.x, 4.x
 */

// 开启错误报告以便调试
error_reporting(E_ALL);
ini_set('display_errors', 1);

class ControllerExtensionPaymentWonderpayment extends Controller {
    public function index() {
        $this->load->language('extension/payment/wonderpayment');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_i_have_paid'] = $this->language->get('button_i_have_paid');
        $data['button_continue_payment'] = $this->language->get('button_continue_payment');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['text_unpaid_alert'] = $this->language->get('text_unpaid_alert');

        $this->load->model('checkout/order');

        // 在确认订单页面，订单还未创建，所以这里只返回视图，不创建支付
        // 支付链接会在用户点击确认按钮后通过 AJAX 请求获取
        $data['action'] = $this->url->link('extension/payment/wonderpayment/confirm', '', true);
        $data['method'] = 'post';

        // 添加查询支付状态的URL
        $data['check_payment_url'] = $this->url->link('extension/payment/wonderpayment/checkPaymentStatus', '', true);

        // 传递订单ID给模板，以便前端可以访问
        if (isset($this->session->data['order_id'])) {
            $data['order_id'] = $this->session->data['order_id'];
        }

        return $this->load->view('extension/payment/wonderpayment', $data);
    }

    /**
     * 确认支付
     * 用户点击确认按钮后调用此方法创建支付
     * 根据文档要求：生成reference_number，调用createPaymentLink，返回payment_link
     */
    public function confirm() {
        $json = array();

        try {
            $this->load->language('extension/payment/wonderpayment');
            $this->load->model('checkout/order');
            $this->load->model('extension/payment/wonderpayment');

            // 检查订单是否存在
            if (!isset($this->session->data['order_id'])) {
                $json['error'] = $this->language->get('error_order_not_found');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            $order_id = $this->session->data['order_id'];
            $order_info = $this->model_checkout_order->getOrder($order_id);
            if (!$order_info) {
                $json['error'] = $this->language->get('error_order_info_missing');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            // 初始化日志
            $log = new Log('wonderpayment.log');
            $log->write('=== WonderPayment Confirm 开始处理订单 #' . $order_id . ' ===');

            // 验证必要配置
            $appid = $this->config->get('payment_wonderpayment_appid');
            $privateKey = $this->config->get('payment_wonderpayment_private_key');
            $publicKey = $this->config->get('payment_wonderpayment_public_key');

            if (empty($appid) || empty($privateKey) || empty($publicKey)) {
                $json['error'] = $this->language->get('error_config_missing');
                $log->write('支付配置错误：缺少必要的配置项');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            // 加载 WonderPayment 库
            $lib_path = $this->resolveWonderpaymentLibraryPath();
            if (!$lib_path) {
                $json['error'] = $this->language->get('error_library_missing');
                $log->write('错误：WonderPayment 库文件不存在');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            require_once $lib_path;
            $log->write('成功加载 WonderPayment 库文件');

            // 构建配置
            $callback_url = $this->url->link('extension/payment/wonderpayment/callback', '', true);
            $redirect_url = $this->url->link('extension/payment/wonderpayment/checkPaymentStatus', '', true);

            $config = array(
                'appid' => $appid,
                'signaturePrivateKey' => $privateKey,
                'webhookVerifyPublicKey' => $publicKey,
                'callback_url' => $callback_url,
                'redirect_url' => $redirect_url,
                'environment' => $this->config->get('payment_wonderpayment_environment') ?: 'stg'
            );

            // 初始化 WonderPayment 适配器
            $wonderpayment = new WonderPayment($config, $this->config->get('payment_wonderpayment_debug') ? $log : null);
            $log->write('WonderPayment 适配器初始化成功');

            // 获取订单记录，生成 reference_number
            // 根据文档：reference_number格式：opencart的订单id-count-时间戳
            $orderRecord = $this->model_extension_payment_wonderpayment->getOrderRecord($order_id);
            $count = $orderRecord ? ($orderRecord['count'] + 1) : 1;
            $timestamp = time();
            $reference_number = $order_id . '-' . $count . '-' . $timestamp;
            $log->write('生成 reference_number: ' . $reference_number);

            // 构建订单数据（根据文档附录3的请求格式）
            $orderData = $this->buildOrderData($order_info, $order_id, $reference_number, $callback_url, $redirect_url, $log);

            $this->logJson($log, '调用 createPaymentLink，订单数据: ', $orderData);

            // 调用 SDK 创建支付链接
            $response = $wonderpayment->createPayment($orderData);

            $this->logJson($log, 'createPaymentLink 响应: ', $response);

            // 检查响应
            if ($this->isInvalidCurrencyResponse($response)) {
                $json['error'] = $this->language->get('error_invalid_currency');
            } elseif (isset($response['data']) && isset($response['data']['payment_link']) && !empty($response['data']['payment_link'])) {
                $payment_link = $response['data']['payment_link'];
                $order_number = isset($response['data']['order']['number']) ? $response['data']['order']['number'] : '';

                // 获取交易信息
                $transaction_uuid = null;
                if (isset($response['data']['order']['transactions']) && !empty($response['data']['order']['transactions'])) {
                    $transaction_uuid = $response['data']['order']['transactions'][0]['uuid'];
                }

                $log->write('成功获取支付链接: ' . $payment_link);

                // 保存订单记录到数据库
                $this->model_extension_payment_wonderpayment->addOrderRecord($order_id, $reference_number, $order_number, $transaction_uuid);
                $log->write('保存订单记录: 订单 #' . $order_id . ', reference_number: ' . $reference_number . ', order_number: ' . $order_number . ', transaction_uuid: ' . $transaction_uuid);

                // 返回支付链接
                $json['redirect'] = $payment_link;
            } else {
                $json['error'] = $this->language->get('error_payment_link_missing');
                $log->write('错误：未返回有效的支付链接');
                $this->logJson($log, '响应: ', $response);
            }

        } catch (Exception $e) {
            $json['error'] = $this->language->get('error_payment_exception') . '：' . $e->getMessage();
            if (isset($log)) {
                $log->write('支付异常: ' . $e->getMessage());
                $log->write('异常堆栈: ' . $e->getTraceAsString());
            }
        } catch (Error $e) {
            $json['error'] = $this->language->get('error_payment_error') . '：' . $e->getMessage();
            if (isset($log)) {
                $log->write('支付错误: ' . $e->getMessage());
                $log->write('错误堆栈: ' . $e->getTraceAsString());
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * 异步回调处理
     */
    public function callback() {
        $this->load->model('checkout/order');

        // 初始化日志
        $log = new Log('wonderpayment.log');

        $this->logJson($log, 'WonderPayment 回调收到: ', $this->request->post);

        // 验证回调数据
        if (isset($this->request->post['reference_number']) || isset($this->request->post['order_id'])) {
            $reference_number = isset($this->request->post['reference_number']) ? $this->request->post['reference_number'] : null;
            
            if ($reference_number) {
                // 通过reference_number查找订单记录，然后获取实际的订单ID
                $this->load->model('extension/payment/wonderpayment');
                $order_record = $this->model_extension_payment_wonderpayment->getOrderRecordByReferenceNumber($reference_number);
                if ($order_record) {
                    $order_id = $order_record['order_id'];
                } else {
                    // 如果通过reference_number找不到，尝试直接作为订单ID
                    $order_id = $reference_number;
                }
            } else {
                $order_id = $this->request->post['order_id'];
            }

            $order_info = $this->model_checkout_order->getOrder($order_id);

            if ($order_info) {
                // 加载 WonderPayment 库
                $wonderpaymentLibraryPath = $this->resolveWonderpaymentLibraryPath();
                if (!$wonderpaymentLibraryPath) {
                    $log->write('错误：回调函数中 WonderPayment 库文件不存在于任何可能的路径');
                    echo 'fail';
                    return;
                }

                $log->write('回调函数：加载 WonderPayment 库文件: ' . $wonderpaymentLibraryPath);
                try {
                    require_once $wonderpaymentLibraryPath;
                    $log->write('回调函数：WonderPayment 库文件加载成功');
                } catch (Throwable $e) { // 使用 Throwable 来捕获 Error 和 Exception
                    $log->write('回调函数：加载 WonderPayment 库文件时发生错误: ' . $e->getMessage());
                    echo 'fail';
                    return;
                }

                // 构建配置
                $config = array(
                    'appid' => $this->config->get('payment_wonderpayment_appid'),
                    'signaturePrivateKey' => $this->config->get('payment_wonderpayment_private_key'),
                    'webhookVerifyPublicKey' => $this->config->get('payment_wonderpayment_public_key'),
                    'callback_url' => '',
                    'redirect_url' => '',
                    'environment' => $this->config->get('payment_wonderpayment_environment') ? $this->config->get('payment_wonderpayment_environment') : 'stg'
                );

                try {
                    $wonderpayment = new WonderPayment($config, $this->config->get('payment_wonderpayment_debug') ? $log : null);

                    // 验证 Webhook 签名
                    $verified = $wonderpayment->verifyWebhook($this->request->post);

                    if ($verified) {
                        // 检查支付状态
                        if (isset($this->request->post['status']) && $this->request->post['status'] == 'paid') {
                            // 更新订单状态
                            $order_status_id = $this->config->get('payment_wonderpayment_order_status_id');
                            $this->model_checkout_order->addOrderHistory($order_id, $order_status_id);

                            // 查询WonderPayment API获取完整的订单信息，特别是transaction_uuid
                            try {
                                $reference_number = isset($this->request->post['reference_number']) ? $this->request->post['reference_number'] : $order_id;
                                $order_number = isset($this->request->post['order_id']) ? $this->request->post['order_id'] : null;
                                
                                $log->write('Webhook: 订单支付成功，查询WonderPayment获取transaction_uuid，reference_number: ' . $reference_number);
                                
                                // 查询订单详情
                                $order_response = $wonderpayment->queryOrder($reference_number, $order_number, null);
                                
                                if (isset($order_response['code']) && $order_response['code'] == 200 && isset($order_response['data']['order'])) {
                                    $order_data = $order_response['data']['order'];
                                    
                                    // 提取transaction_uuid
                                    $transaction_uuid = null;
                                    if (isset($order_data['transactions']) && is_array($order_data['transactions']) && !empty($order_data['transactions'])) {
                                        foreach ($order_data['transactions'] as $transaction) {
                                            if (isset($transaction['uuid']) && !empty($transaction['uuid'])) {
                                                $transaction_uuid = $transaction['uuid'];
                                                $log->write('Webhook: 从transactions数组获取transaction_uuid: ' . $transaction_uuid);
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // 如果仍未找到，尝试其他位置
                                    if (empty($transaction_uuid) && isset($order_data['transaction']) && isset($order_data['transaction']['uuid'])) {
                                        $transaction_uuid = $order_data['transaction']['uuid'];
                                        $log->write('Webhook: 从transaction对象获取transaction_uuid: ' . $transaction_uuid);
                                    }
                                    
                                    // 如果获取到transaction_uuid，更新数据库
                                    if (!empty($transaction_uuid)) {
                                        $this->load->model('extension/payment/wonderpayment');
                                        // 安全调用模型方法，避免代理对象问题
                                        if (isset($this->model_extension_payment_wonderpayment) && method_exists($this->model_extension_payment_wonderpayment, 'updateTransactionUuid')) {
                                            $this->model_extension_payment_wonderpayment->updateTransactionUuid($order_id, $transaction_uuid);
                                            $log->write('Webhook: transaction_uuid已更新到数据库，订单ID: ' . $order_id . ', UUID: ' . $transaction_uuid);
                                        } else {
                                            $log->write('Webhook: 模型或方法不存在，无法更新transaction_uuid');
                                        }
                                    } else {
                                        $log->write('Webhook: 未能从API响应中获取到transaction_uuid');
                                    }
                                } else {
                                    $log->write('Webhook: 查询订单API失败或响应格式不正确');
                                }
                            } catch (Exception $e) {
                                $log->write('Webhook: 查询transaction_uuid时出错: ' . $e->getMessage());
                                $log->write('Webhook: 错误详情: ' . $e->getTraceAsString());
                            }

                            $log->write('WonderPayment 订单 #' . $order_id . ' 支付成功');
                        } else {
                            $log->write('WonderPayment 订单 #' . $order_id . ' 支付状态: ' . (isset($this->request->post['status']) ? $this->request->post['status'] : 'unknown'));
                        }

                        echo 'success';
                    } else {
                        $log->write('WonderPayment 签名验证失败');
                        echo 'fail';
                    }
                } catch (Exception $e) {
                    $log->write('WonderPayment 回调异常: ' . $e->getMessage());
                    echo 'fail';
                }
            } else {
                $log->write('WonderPayment 订单不存在: ' . $order_id);
                echo 'fail';
            }
        } else {
            $log->write('WonderPayment 回调数据无效');
            echo 'fail';
        }
    }

    /**
     * 检查支付状态
     * 用于用户点击"我已支付"后查询订单状态
     */
    public function checkPaymentStatus() {
        $this->load->language('extension/payment/wonderpayment');
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/wonderpayment');

        $order_id = null;
        
        // 首先尝试从会话获取订单ID
        if(isset($this->session->data['order_id'])) {
            $order_id = $this->session->data['order_id'];
        } 

        // 如果会话中没有订单ID，尝试从POST/GET请求中获取
        if (!$order_id) {
            // 优先从POST请求获取（前端可能通过AJAX传递）
            if (isset($this->request->post['order_id'])) {
                $order_id = (int)$this->request->post['order_id'];
            } elseif (isset($this->request->get['order_id'])) {
                $order_id = (int)$this->request->get['order_id'];
            }
        }

        // 如果仍然没有订单ID，尝试通过reference_number反向查找
        if (!$order_id) {
            // 检查请求中是否包含reference_number或其他标识符
            $reference_number = null;
            
            // 检查可能的参数名称
            if (isset($this->request->get['reference_number'])) {
                $reference_number = $this->request->get['reference_number'];
            } elseif (isset($this->request->get['reference'])) {
                $reference_number = $this->request->get['reference'];
            } elseif (isset($this->request->get['ref'])) {
                $reference_number = $this->request->get['ref'];
            }
            
            // 如果找到了reference_number，通过数据库查找订单ID
            if ($reference_number) {
                $order_record = $this->model_extension_payment_wonderpayment->getOrderRecordByReferenceNumber($reference_number);
                if ($order_record && isset($order_record['order_id'])) {
                    $order_id = $order_record['order_id'];
                }
            }
        }

        if (!$order_id) {
            $json['error'] = $this->language->get('error_order_id_missing');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (!$order_info) {
            $json['error'] = $this->language->get('error_order_info_missing');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // 初始化日志
        $log = new Log('wonderpayment.log');

        // 加载 WonderPayment 库
        $wonderpaymentLibraryPath = $this->resolveWonderpaymentLibraryPath();
        if (!$wonderpaymentLibraryPath) {
            $json['error'] = $this->language->get('error_library_missing');
            $log->write('错误：检查支付状态函数中 WonderPayment 库文件不存在于任何可能的路径');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $log->write('检查支付状态函数：加载 WonderPayment 库文件: ' . $wonderpaymentLibraryPath);
        try {
            require_once $wonderpaymentLibraryPath;
            $log->write('检查支付状态函数：WonderPayment 库文件加载成功');
        } catch (Throwable $e) { // 使用 Throwable 来捕获 Error 和 Exception
            $json['error'] = $this->language->get('error_payment_system') . '：' . $e->getMessage();
            $log->write('检查支付状态函数：加载 WonderPayment 库文件时发生错误: ' . $e->getMessage());
            $log->write('错误堆栈: ' . $e->getTraceAsString());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // 构建配置
        $config = array(
            'appid' => $this->config->get('payment_wonderpayment_appid'),
            'signaturePrivateKey' => $this->config->get('payment_wonderpayment_private_key'),
            'webhookVerifyPublicKey' => $this->config->get('payment_wonderpayment_public_key'),
            'callback_url' => '',
            'redirect_url' => '',
            'environment' => $this->config->get('payment_wonderpayment_environment') ? $this->config->get('payment_wonderpayment_environment') : 'stg'
        );

        try {
            $wonderpayment = new WonderPayment($config, $this->config->get('payment_wonderpayment_debug') ? $log : null);

            // 获取订单记录以获取最新的reference_number
            $this->load->model('extension/payment/wonderpayment');
            $orderRecord = $this->model_extension_payment_wonderpayment->getOrderRecord($order_id);

            if (!$orderRecord || empty($orderRecord['reference_number'])) {
                $log->write('未找到订单记录或reference_number为空，订单ID: ' . $order_id);
                $json['error'] = $this->language->get('error_payment_record_missing');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            $reference_number = $orderRecord['reference_number'];
            $log->write('使用reference_number查询订单状态: ' . $reference_number);

            // 查询订单状态
            $response = $wonderpayment->queryOrder($reference_number);

            $this->logJson($log, 'WonderPayment 订单状态查询响应: ', $response);

            // 检查订单状态
            if (isset($response['data']['order'])) {
                $order_data = $response['data']['order'];
                $correspondence_state = isset($order_data['correspondence_state']) ? $order_data['correspondence_state'] : '';

                // 如果订单已支付，更新订单状态并跳转到成功页面
                if ($correspondence_state === 'paid') {
                    $log->write('WonderPayment 订单 #' . $order_id . ' 支付成功，更新订单状态');

                    // 更新订单状态
                    $order_status_id = $this->config->get('payment_wonderpayment_order_status_id');
                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id);

                    // 更新数据库中的支付状态
                    $this->load->model('extension/payment/wonderpayment');
                    $this->model_extension_payment_wonderpayment->updatePaymentStatus($order_id, 'paid');

                    // 在支付成功后，查询并保存transaction_uuid
                    try {
                        // 提取transaction_uuid
                        $transaction_uuid = null;
                        if (isset($order_data['transactions']) && is_array($order_data['transactions']) && !empty($order_data['transactions'])) {
                            foreach ($order_data['transactions'] as $transaction) {
                                if (isset($transaction['uuid']) && !empty($transaction['uuid'])) {
                                    $transaction_uuid = $transaction['uuid'];
                                    $log->write('checkPaymentStatus: 从transactions数组获取transaction_uuid: ' . $transaction_uuid);
                                    break;
                                }
                            }
                        }
                        
                        // 如果仍未找到，尝试其他位置
                        if (empty($transaction_uuid) && isset($order_data['transaction']) && isset($order_data['transaction']['uuid'])) {
                            $transaction_uuid = $order_data['transaction']['uuid'];
                            $log->write('checkPaymentStatus: 从transaction对象获取transaction_uuid: ' . $transaction_uuid);
                        }
                        
                        // 如果获取到transaction_uuid，更新数据库
                        if (!empty($transaction_uuid)) {
                            $this->load->model('extension/payment/wonderpayment');
                            // 安全调用模型方法，避免代理对象问题
                            if (isset($this->model_extension_payment_wonderpayment) && method_exists($this->model_extension_payment_wonderpayment, 'updateTransactionUuid')) {
                                $this->model_extension_payment_wonderpayment->updateTransactionUuid($order_id, $transaction_uuid);
                                $log->write('checkPaymentStatus: transaction_uuid已更新到数据库，订单ID: ' . $order_id . ', UUID: ' . $transaction_uuid);
                            } else {
                                $log->write('checkPaymentStatus: 模型或方法不存在，无法更新transaction_uuid');
                            }
                        } else {
                            $log->write('checkPaymentStatus: 未能从API响应中获取到transaction_uuid');
                        }
                    } catch (Exception $e) {
                        $log->write('checkPaymentStatus: 查询transaction_uuid时出错: ' . $e->getMessage());
                        $log->write('checkPaymentStatus: 错误详情: ' . $e->getTraceAsString());
                    }

                    // 检查是否是AJAX请求
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        // AJAX请求，返回JSON指示跳转
                        $json['success'] = true;
                        $json['redirect'] = $this->url->link('checkout/success', '', true);
                        $this->response->addHeader('Content-Type: application/json');
                        $this->response->setOutput(json_encode($json));
                        return;
                    } else {
                        // 普通请求，执行重定向
                        $this->response->redirect($this->url->link('checkout/success', '', true));
                    }
                }

                // 如果订单未支付，生成新的支付链接并在新标签页打开
                $log->write('订单 #' . $order_id . ' 未支付，生成新的支付链接');

                // 构建回调 URL
                $callback_url = $this->url->link('extension/payment/wonderpayment/callback', '', true);
                $redirect_url = $this->url->link('extension/payment/wonderpayment/checkPaymentStatus', '', true);

                // 更新配置
                $config['callback_url'] = $callback_url;
                $config['redirect_url'] = $redirect_url;

                // 初始化 WonderPayment 适配器
                $wonderpayment = new WonderPayment($config, $this->config->get('payment_wonderpayment_debug') ? $log : null);

                // 生成新的 reference_number：orderid-count-timestamp
                $count = $orderRecord ? ($orderRecord['count'] + 1) : 1;
                $timestamp = time();
                $referenceNumber = $order_info['order_id'] . '-' . $count . '-' . $timestamp;
                $log->write('生成新的 reference_number: ' . $referenceNumber);

                $orderData = $this->buildOrderData($order_info, $order_info['order_id'], $referenceNumber, $callback_url, $redirect_url, $log);

                // 添加客户信息 - 暂时注释掉
                /*
                $orderData['customer'] = array(
                    'name' => $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'],
                    'email' => $order_info['email'],
                    'phone' => $order_info['telephone']
                );
                */

                // 创建支付
                $response = $wonderpayment->createPayment($orderData);

                // 检查响应
                if (isset($response['data']) && isset($response['data']['payment_link']) && !empty($response['data']['payment_link'])) {
                    $payment_link = $response['data']['payment_link'];
                    $order_number = isset($response['data']['order']['number']) ? $response['data']['order']['number'] : '';

                    // 保存订单记录到数据库
                    // 获取交易信息
                    $transaction_uuid = null;
                    if (isset($response['data']['order']['transactions']) && !empty($response['data']['order']['transactions'])) {
                        $transaction_uuid = $response['data']['order']['transactions'][0]['uuid'];
                    }

                    $this->model_extension_payment_wonderpayment->addOrderRecord(
                        $order_info['order_id'],
                        $referenceNumber,
                        $order_number,
                        $transaction_uuid
                    );

                    $log->write('保存订单记录: 订单 #' . $order_info['order_id'] . ', reference_number: ' . $referenceNumber . ', count: ' . $count . ', transaction_uuid: ' . $transaction_uuid);

                    // 返回 JSON，包含新的支付链接
                    $json['redirect'] = $payment_link;
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json));
                    return;
                } else {
                    $log->write('WonderPayment 错误：未返回支付链接');
                    $this->logJson($log, '响应: ', $response);
                    $json['error'] = $this->language->get('error_create_payment_failed');
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json));
                    return;
                }
            } else {
                $log->write('WonderPayment 订单状态查询失败: 未返回订单数据');
                $json['error'] = $this->language->get('error_query_order_failed');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

        } catch (Exception $e) {
            $json['error'] = $this->language->get('error_query_order_exception') . '：' . $e->getMessage();
            $log->write('WonderPayment 订单状态查询异常: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }

    /**
     * 退款功能
     * 用户在订单详情页面点击退款按钮后调用
     */
    public function refund() {
        $this->load->language('extension/payment/wonderpayment');
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/wonderpayment');

        $json = array();

        // 初始化日志
        $log = new Log('wonderpayment.log');
        $log->write('=== WonderPayment 退款请求开始 ===');

        if(!isset($this->request->post['order_id'])) {
            $log->write('退款失败：缺少订单ID参数');
            $json['error'] = $this->language->get('error_order_id');
        } else {
            $order_id = (int)$this->request->post['order_id'];
            $log->write('退款订单ID: ' . $order_id);

            $order_info = $this->model_checkout_order->getOrder($order_id);

            if (!$order_info) {
                $log->write('退款失败：订单不存在，订单ID: ' . $order_id);
                $json['error'] = $this->language->get('error_order_not_found');
            } elseif ($order_info['customer_id'] != $this->customer->getId()) {
                $log->write('退款失败：权限不足，订单客户ID: ' . $order_info['customer_id'] . ', 当前用户ID: ' . $this->customer->getId());
                $json['error'] = $this->language->get('error_permission');
            } else {
                // 检查订单是否使用 WonderPayment 支付
                if ($order_info['payment_code'] != 'wonderpayment') {
                    $log->write('退款失败：订单支付方式不是WonderPayment，支付方式: ' . $order_info['payment_code']);
                    $json['error'] = $this->language->get('error_payment_method');
                } else {
                    // 获取订单记录
                    $orderRecord = $this->model_extension_payment_wonderpayment->getOrderRecord($order_id);

                    if (!$orderRecord || empty($orderRecord['transaction_uuid'])) {
                        $log->write('退款失败：未找到交易信息，订单ID: ' . $order_id);
                        $json['error'] = $this->language->get('error_transaction_not_found');
                    } else {
                        $this->logJson($log, '订单记录: ', $orderRecord);

                        // 加载 WonderPayment 库
                        $lib_path = $this->resolveWonderpaymentLibraryPath();
                        if (!$lib_path) {
                            $log->write('退款失败：WonderPayment库文件不存在');
                            $json['error'] = $this->language->get('error_library_missing');
                            $this->response->addHeader('Content-Type: application/json');
                            $this->response->setOutput(json_encode($json));
                            return;
                        }

                        require_once $lib_path;

                        // 构建配置
                        $config = array(
                            'appid' => $this->config->get('payment_wonderpayment_appid'),
                            'signaturePrivateKey' => $this->config->get('payment_wonderpayment_private_key'),
                            'webhookVerifyPublicKey' => $this->config->get('payment_wonderpayment_public_key'),
                            'callback_url' => '',
                            'redirect_url' => '',
                            'environment' => $this->config->get('payment_wonderpayment_environment') ? $this->config->get('payment_wonderpayment_environment') : 'stg'
                        );

                        try {
                            $wonderpayment = new WonderPayment($config, $this->config->get('payment_wonderpayment_debug') ? $log : null);
                            $log->write('WonderPayment SDK 初始化成功');

                            // 获取订单状态，检查是否允许退款
                            $referenceNumber = $orderRecord['reference_number'];
                            $orderNumber = $orderRecord['order_number'];
                            $transactionUuid = $orderRecord['transaction_uuid'];

                            $log->write('查询订单状态以检查是否允许退款，reference_number: ' . $referenceNumber);
                            $response = $wonderpayment->queryOrder($referenceNumber, $orderNumber, $transactionUuid);
                            $this->logJson($log, '退款前查询订单状态响应: ', $response);

                            // 检查订单状态
                            if (isset($response['data']['order']['transactions']) && !empty($response['data']['order']['transactions'])) {
                                $transactions = $response['data']['order']['transactions'];
                                $transaction = null;

                                // 找到允许退款的交易
                                foreach ($transactions as $t) {
                                    if (isset($t['allow_refund']) && $t['allow_refund'] === true) {
                                        $transaction = $t;
                                        break;
                                    }
                                }

                                if (!$transaction) {
                                    $log->write('退款失败：没有允许退款的交易');
                                    $json['error'] = $this->language->get('error_cannot_refund');
                                } else {
                                    $this->logJson($log, '找到可退款交易: ', $transaction);

                                    $originalCurrency = $order_info['currency_code'];
                                    $settlementCurrency = $originalCurrency;

                                    // 获取退款金额（原始货币）
                                    if (isset($this->request->post['amount'])) {
                                        $refundAmount = (float)$this->request->post['amount'];
                                    } else {
                                    $refundAmount = (float)$transaction['amount'];
                                    }
                                    
                                    // 验证退款金额不能超过可退金额（原始货币）
                                    $maxRefundableAmount = (float)$transaction['amount'];
                                    $alreadyRefundedAmount = 0;
                                    
                                    // 检查已退款金额
                                    $existingRefunds = $this->model_extension_payment_wonderpayment->getRefundsByOrderId($order_id);
                                    foreach ($existingRefunds as $refund) {
                                        if ($refund['status'] == 'success') {
                                            $refundData = json_decode($refund['refund_data'], true);
                                            if (isset($refundData['original_amount']) && isset($refundData['original_currency']) && $refundData['original_currency'] === $originalCurrency) {
                                                $alreadyRefundedAmount += (float)$refundData['original_amount'];
                                            } else {
                                                $amount = (float)$refund['refund_amount'];
                                                // Refund currency equals original currency; no conversion needed.
                                                $alreadyRefundedAmount += $amount;
                                            }
                                        }
                                    }
                                    
                                    $availableRefund = $maxRefundableAmount - $alreadyRefundedAmount;
                                    
                                    if ($refundAmount > $availableRefund) {
                                        $log->write('退款金额超出可退金额: 请求金额=' . $refundAmount . ', 可退金额=' . $availableRefund);
                                        $json['error'] = $this->language->get('error_refund_amount_exceeded');
                                        $this->response->addHeader('Content-Type: application/json');
                                        $this->response->setOutput(json_encode($json));
                                        return;
                                    }
                                    
                                    if ($refundAmount <= 0) {
                                        $log->write('退款金额必须大于0: ' . $refundAmount);
                                        $json['error'] = $this->language->get('text_invalid_refund_amount');
                                        $this->response->addHeader('Content-Type: application/json');
                                        $this->response->setOutput(json_encode($json));
                                        return;
                                    }
                                    
                                    $refundNote = isset($this->request->post['note']) ? $this->request->post['note'] : '';

                                    $refundAmountSettlement = $refundAmount;
                                    $log->write('发起退款请求，reference_number: ' . $referenceNumber . ', transaction_uuid: ' . $transaction['uuid'] . ', refund_amount: ' . $refundAmountSettlement . ' ' . $settlementCurrency . ' (原始: ' . $refundAmount . ' ' . $originalCurrency . ')');

                                    // 调用退款接口
                                    $refundResponse = $wonderpayment->refundTransaction(
                                        $referenceNumber,
                                        $transaction['uuid'],
                                        $refundAmountSettlement
                                    );

                                    $this->logJson($log, '退款响应: ', $refundResponse);

                                    if (isset($refundResponse['code']) && $refundResponse['code'] == 200) {
                                        $log->write('退款成功，订单ID: ' . $order_id . ', 退款金额: ' . $refundAmountSettlement . ' ' . $settlementCurrency . ' (原始: ' . $refundAmount . ' ' . $originalCurrency . ')');
                                        $refundResponse['original_amount'] = $refundAmount;
                                        $refundResponse['original_currency'] = $originalCurrency;
                                        $refundResponse['settlement_amount'] = $refundAmountSettlement;
                                        $refundResponse['settlement_currency'] = $settlementCurrency;
                                        
                                        // 保存退款记录到数据库
                                        $this->model_extension_payment_wonderpayment->addRefundRecord(
                                            $order_id,
                                            $referenceNumber,
                                            $transaction['uuid'],
                                            $refundAmountSettlement,
                                            $settlementCurrency,
                                            $refundResponse,
                                            'success'
                                        );
                                        $log->write('退款记录已保存到数据库');

                                        // 更新订单历史记录
                                        $order_status_id = $this->config->get('payment_wonderpayment_refund_order_status_id');
                                        if ($order_status_id) {
                                            $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, '退款成功，退款金额: ' . $refundAmount . ' ' . $originalCurrency . ' (' . $refundAmountSettlement . ' ' . $settlementCurrency . ')');
                                            $log->write('订单状态已更新');
                                        }

                                        $json['success'] = $this->language->get('text_refund_success');
                                    } else {
                                        $log->write('退款失败，响应代码: ' . (isset($refundResponse['code']) ? $refundResponse['code'] : 'N/A'));
                                        if ($this->isInvalidCurrencyResponse($refundResponse)) {
                                            $json['error'] = $this->language->get('error_invalid_currency');
                                        } else {
                                            $json['error'] = $this->language->get('error_refund_failed');
                                        }
                                    }
                                }
                            } else {
                                $log->write('退款失败：订单没有交易记录');
                                $json['error'] = $this->language->get('error_cannot_refund');
                            }

                        } catch (Exception $e) {
                            $log->write('退款异常: ' . $e->getMessage());
                            $log->write('异常堆栈: ' . $e->getTraceAsString());
                            $json['error'] = $this->language->get('error_refund_exception') . ': ' . $e->getMessage();
                        }
                    }
                }
            }
        }

        $log->write('=== WonderPayment 退款请求结束 ===');
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
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

    protected function resolveWonderpaymentLibraryPath() {
        $paths = array(
            DIR_SYSTEM . 'library/wonderpayment.php',
            DIR_APPLICATION . '../system/library/wonderpayment.php',
            DIR_STORAGE . '../upload/system/library/wonderpayment.php'
        );

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
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

    protected function buildOrderData($order_info, $order_id, $reference_number, $callback_url, $redirect_url, $log) {
        $originalCurrency = $order_info['currency_code'];
        $originalTotal = $this->currency->format($order_info['total'], $originalCurrency, $order_info['currency_value'], false);
        $totalSettlement = $originalTotal;

        $this->load->model('checkout/order');
        $order_products = $this->model_checkout_order->getOrderProducts($order_id);
        $order_totals = $this->model_checkout_order->getOrderTotals($order_id);

        $line_items = array();
        $line_total = 0.0;

        foreach ($order_products as $product) {
            $item_price = (float)$product['price'];
            $item_total = (float)$product['total'];
            $item_price_settlement = $item_price;
            $item_total_settlement = $item_total;

            $line_items[] = array(
                'label' => html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8'),
                'purchasable_type' => 'Listing',
                'purchase_id' => (string)$product['product_id'],
                'price' => number_format($item_price_settlement, 2, '.', ''),
                'quantity' => (string)$product['quantity'],
                'total' => number_format($item_total_settlement, 2, '.', '')
            );
            $line_total += $item_total_settlement;
        }

        foreach ($order_totals as $total_row) {
            if ($total_row['code'] === 'total' || $total_row['code'] === 'sub_total') {
                continue;
            }

            $row_value = (float)$total_row['value'];
            if ($row_value == 0.0) {
                continue;
            }

            $converted = $row_value;
            $line_items[] = array(
                'label' => strip_tags(html_entity_decode($total_row['title'], ENT_QUOTES, 'UTF-8')),
                'purchasable_type' => 'Listing',
                'purchase_id' => (string)$total_row['code'],
                'price' => number_format($converted, 2, '.', ''),
                'quantity' => '1',
                'total' => number_format($converted, 2, '.', '')
            );
            $line_total += $converted;
        }

        if (empty($line_items)) {
            $line_items[] = array(
                'label' => 'Order #' . $order_id,
                'purchasable_type' => 'Listing',
                'purchase_id' => '0',
                'price' => number_format($totalSettlement, 2, '.', ''),
                'quantity' => '1',
                'total' => number_format($totalSettlement, 2, '.', '')
            );
            $line_total = $totalSettlement;
        }

        $delta = $totalSettlement - $line_total;
        if (abs($delta) >= 0.01) {
            $line_items[] = array(
                'label' => 'Adjustment',
                'purchasable_type' => 'Listing',
                'purchase_id' => 'adjustment',
                'price' => number_format($delta, 2, '.', ''),
                'quantity' => '1',
                'total' => number_format($delta, 2, '.', '')
            );
            $line_total += $delta;
        }

        $log->write('货币使用订单币种: ' . $originalTotal . ' ' . $originalCurrency);

        return array(
            'reference_number' => $reference_number,
            'charge_fee' => '0.00',
            'currency' => $originalCurrency,
            'note' => $this->config->get('config_name') . ' - 订单 #' . $order_id . ' (原始货币: ' . $originalCurrency . ')',
            'callback_url' => $callback_url,
            'redirect_url' => $redirect_url,
            'language' => $this->config->get('payment_wonderpayment_language') ? $this->config->get('payment_wonderpayment_language') : 'en-gb',
            'line_items' => $line_items
        );
    }
}
