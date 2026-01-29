<?php
/**
 * WonderPayment SDK 适配层
 * 兼容 OpenCart 1.x, 2.x, 3.x, 4.x
 */

// 防止重复加载

if (!defined('WONDERPAYMENT_SDK_LOADED')) {
    // 优先尝试加载 Composer autoload（若存在）
    $autoloadPath = DIR_SYSTEM . '../vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }

    // 如果 Composer 已经加载了 PaymentSDK 类，则不需要再次加载
    // 如果没有加载，则尝试直接加载 PaymentSDK.php 文件
    if (!class_exists('PaymentSDK') && !class_exists('WonderPayment\\PaymentSDK')) {
        // 尝试多个可能的路径
        $possiblePaths = [
            DIR_SYSTEM . 'storage/vendor/wonderpayment/src/PaymentSDK.php',
            DIR_SYSTEM . '../storage/vendor/wonderpayment/src/PaymentSDK.php',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                break;
            }
        }
    }

    // 如果 SDK 是命名空间版本，提供别名以兼容旧调用
    if (!class_exists('PaymentSDK') && class_exists('WonderPayment\\PaymentSDK')) {
        class_alias('WonderPayment\\PaymentSDK', 'PaymentSDK');
    }

    define('WONDERPAYMENT_SDK_LOADED', true);
}

/**
 * WonderPayment 适配器类
 * 封装 SDK 并提供 OpenCart 友好的接口
 */
class WonderPayment
{
    private $sdk;
    private $config;
    private $log;

    public function __construct($config, $log = null)
    {
        $this->config = $config;
        $this->log = $log;

        // 调试日志 - 记录传入的配置
        $this->logInfo('WonderPayment Constructor called with config keys: ' . json_encode(array_keys($config)));
        $this->logInfo('Config values: appid=' . (isset($config['appid']) ? 'SET' : 'NOT SET') .
            ', jwtToken=' . (isset($config['jwtToken']) ? 'SET' : 'NOT SET') .
            ', userAccessToken=' . (isset($config['userAccessToken']) ? 'SET' : 'NOT SET'));

        // 初始化 SDK
        $sdkConfig = array(
            'appid' => $config['appid'],
            'signaturePrivateKey' => $config['signaturePrivateKey'],
            'webhookVerifyPublicKey' => $config['webhookVerifyPublicKey'],
            'callback_url' => $config['callback_url'],
            'redirect_url' => $config['redirect_url'],
            'environment' => isset($config['environment']) ? $config['environment'] : 'stg'
        );

        if (isset($config['skip_signature'])) {
            $sdkConfig['skip_signature'] = $config['skip_signature'];
        }

        if (isset($config['request_id'])) {
            $sdkConfig['request_id'] = $config['request_id'];
        }

        // 添加二维码登录相关参数
        if (isset($config['jwtToken'])) {
            $sdkConfig['jwtToken'] = $config['jwtToken'];
        }

        if (isset($config['userAccessToken'])) {
            $sdkConfig['userAccessToken'] = $config['userAccessToken'];
        }

        if (isset($config['language'])) {
            $sdkConfig['language'] = $config['language'];
        }

        // 调试日志
        $this->logInfo('Config: jwtToken = ' . (isset($config['jwtToken']) ? 'SET' : 'NOT SET') . ', userAccessToken = ' . (isset($config['userAccessToken']) ? 'SET' : 'NOT SET'));

        try {
            if (!class_exists('PaymentSDK')) {
                throw new Exception('PaymentSDK class is not available');
            }

            $this->sdk = new PaymentSDK($sdkConfig);
            $this->logInfo('SDK initialized successfully');
        } catch (Exception $e) {
            $this->logError('SDK initialization failed: ' . $e->getMessage());
            throw $e;
        } catch (Error $e) {
            $this->logError('SDK initialization error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 创建支付链接
     *
     * @param array $orderData 订单数据
     * @return array 支付链接和订单信息
     */
    public function createPayment($orderData)
    {
        try {
            $this->logInfo('Creating payment: ' . json_encode($orderData));

            $params = array(
                'order' => $orderData
            );

            $response = $this->sdk->createPaymentLink($params);

            $this->logInfo('Payment creation response: ' . json_encode($response));

            return $response;
        } catch (Exception $e) {
            $this->logError('Create payment failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 查询订单
     *
     * @param string $referenceNumber 订单参考号
     * @param string $orderNumber 订单号 (可选，来自创建支付响应的 data.order.number)
     * @param string $transactionUuid 交易UUID (可选，来自创建支付响应的 data.order.transaction.uuid)
     * @return array 订单信息
     */
    public function queryOrder($referenceNumber, $orderNumber = null, $transactionUuid = null)
    {
        try {
            $this->logInfo('Querying order: reference_number=' . $referenceNumber .
                ', order_number=' . (is_array($orderNumber) ? json_encode($orderNumber) : $orderNumber) .
                ', transaction_uuid=' . (is_array($transactionUuid) ? json_encode($transactionUuid) : $transactionUuid));

            $params = array(
                'order' => array()
            );

            // 根据可用参数构建订单查询条件
            if ($referenceNumber) {
                $params['order']['reference_number'] = $referenceNumber;
            }

            if ($orderNumber) {
                $params['order']['number'] = $orderNumber;
            }

            // 如果有交易UUID，添加到请求中
            if ($transactionUuid) {
                $params['transaction'] = array(
                    'uuid' => $transactionUuid
                );
            }

            // 至少需要提供一个标识符
            if (empty($params['order'])) {
                throw new Exception('查询订单时必须提供至少一个标识符 (reference_number, number 或 transaction.uuid)');
            }

            $response = $this->sdk->queryOrder($params);

            $this->logInfo('Order query response: ' . json_encode($response));

            return $response;
        } catch (Exception $e) {
            $this->logError('Query order failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 验证 Webhook 签名
     *
     * @param array $postData POST 数据
     * @return bool 验证结果
     */
    public function verifyWebhook($postData)
    {
        try {
            $this->logInfo('Verifying Webhook: ' . json_encode($postData));

            // SDK 目前未实现完整的 Webhook 验证
            // 这里可以添加自定义验证逻辑
            // TODO: 等待 SDK 更新或实现自定义验证

            return true;
        } catch (Exception $e) {
            $this->logError('Webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 作废交易
     *
     * @param string $referenceNumber 订单参考号
     * @param string $transactionUuid 交易 UUID
     * @return array 作废结果
     */
    public function voidTransaction($referenceNumber, $transactionUuid)
    {
        try {
            $this->logInfo('Voiding transaction: ' . $referenceNumber . ' - ' . (is_array($transactionUuid) ? json_encode($transactionUuid) : $transactionUuid));

            $params = array(
                'order' => array(
                    'reference_number' => $referenceNumber
                ),
                'transaction' => array(
                    'uuid' => $transactionUuid
                )
            );

            $response = $this->sdk->voidTransaction($params);

            $this->logInfo('Transaction void response: ' . json_encode($response));

            return $response;
        } catch (Exception $e) {
            $this->logError('Void transaction failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 退款交易
     *
     * @param string $referenceNumber 订单参考号
     * @param string $transactionUuid 交易 UUID
     * @param float $refundAmount 退款金额
     * @param string|null $orderNumber 订单号 (可选)
     * @param string|null $refundNote 退款备注 (可选)
     * @return array 退款结果
     */
    public function refundTransaction($referenceNumber, $transactionUuid, $refundAmount, $orderNumber = null, $refundNote = null)
    {
        try {
            $this->logInfo('Refunding transaction: ' . $referenceNumber .
                ' - ' . (is_array($transactionUuid) ? json_encode($transactionUuid) : $transactionUuid) .
                ' - ' . $refundAmount .
                ' - order_number: ' . ($orderNumber ?: 'NULL') .
                ' - note: ' . ($refundNote ?: 'NULL'));

            $params = array(
                'order' => array(
                    'reference_number' => $referenceNumber
                ),
                'transaction' => array(
                    'uuid' => $transactionUuid
                ),
                'refund' => array(
                    'amount' => $refundAmount
                )
            );

            // 如果提供了订单号，添加到请求中
            if ($orderNumber) {
                $params['order']['number'] = $orderNumber;
            }

            // 如果提供了退款备注，添加到请求中
            if ($refundNote) {
                $params['refund']['note'] = $refundNote;
            }

            $response = $this->sdk->refundTransaction($params);

            $this->logInfo('Transaction refund response: ' . json_encode($response));

            return $response;
        } catch (Exception $e) {
            $this->logError('Refund transaction failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 作废订单
     *
     * @param string $referenceNumber 订单参考号
     * @return array 作废结果
     */
    public function voidOrder($referenceNumber)
    {
        try {
            $this->logInfo('Voiding order: ' . $referenceNumber);

            $params = array(
                'order' => array(
                    'reference_number' => $referenceNumber
                )
            );

            $response = $this->sdk->voidOrder($params);

            $this->logInfo('Order void response: ' . json_encode($response));

            return $response;
        } catch (Exception $e) {
            $this->logError('Void order failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 验证 SDK 配置
     *
     * @return array 验证结果
     */
    public function verifyConfig()
    {
        try {
            $this->logInfo('Verifying SDK config');

            $result = $this->sdk->verifySignature();

            $this->logInfo('Config verification result: ' . json_encode($result));

            return $result;
        } catch (Exception $e) {
            $this->logError('Config verification failed: ' . $e->getMessage());
            return array(
                'business' => null,
                'success' => false
            );
        }
    }

    /**
     * 记录信息日志
     */
    private function logInfo($message)
    {
        if ($this->log) {
            $this->log->write('WonderPayment INFO: ' . $message);
        }
    }

    /**
     * 记录错误日志
     */
    private function logError($message)
    {
        if ($this->log) {
            $this->log->write('WonderPayment ERROR: ' . $message);
        }
    }

    // ==================== QR Code Login Related Methods ====================

    /**
     * 创建二维码（完整流程）
     *
     * @return array 二维码信息
     * @throws \Exception
     */
    public function createQRCode()
    {
        try {
            $this->logInfo('Creating QR code');

            $response = $this->sdk->createQRCode();

            $this->logInfo('QR code created: ' . json_encode($response));

            return $response;
        } catch (Exception $e) {
            $this->logError('Create QR code failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取二维码状态
     *
     * @param string $uuid 二维码 UUID
     * @return array 状态信息
     * @throws \Exception
     */
    public function getQRCodeStatus($uuid)
    {
        try {
            $this->logInfo('Getting QR code status: ' . $uuid);

            $response = $this->sdk->getQRCodeStatus($uuid);

            $this->logInfo('QR code status: ' . json_encode($response));

            return $response;
        } catch (Exception $e) {
            $this->logError('Get QR code status failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取商家列表
     *
     * @return array 商家列表
     * @throws \Exception
     */
    public function getBusinesses()
    {
        try {
            $this->logInfo('Getting businesses list');

            $response = $this->sdk->getBusinesses();

            $this->logInfo('Businesses list: ' . json_encode($response));

            return $response;
        } catch (Exception $e) {
            $this->logError('Get businesses failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 生成 App ID
     *
     * @param string $businessId 商家 ID
     * @param string $publicKey 公钥
     * @return array App ID 信息
     * @throws \Exception
     */
    public function generateAppId($businessId, $publicKey)
    {
        try {
            $this->logInfo('Generating app_id for business: ' . $businessId);

            $response = $this->sdk->generateAppId($businessId, $publicKey);

            $this->logInfo('App ID generated: ' . json_encode($response));

            return $response;
        } catch (Exception $e) {
            $this->logError('Generate app_id failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 初始化 SDK 用于二维码登录
     *
     * @param string $environment 环境
     * @param string $jwtToken JWT Token
     * @param string $userAccessToken 用户访问令牌
     * @param string $language 语言
     * @return WonderPayment
     * @throws \Exception
     */
    public static function createForQRCode($environment, $jwtToken = '', $userAccessToken = '', $language = 'zh-CN')
    {
        // 如果没有提供 JWT Token，使用测试环境的默认值
        if (empty($jwtToken)) {
            if ($environment === 'stg') {
                $jwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcHBfa2V5IjoiMDJlYjMzNjItMWNjYi00MDYzLThmNWUtODI1ZmRlNzYxZWZiIiwiYXBwX2lkIjoiODBhOTg0ZTItNGVjNC00ZDA2LWFiYTktZTQzMDEwOTU2ZTEzIiwiaWF0IjoxNjgxMzkyMzkyLCJleHAiOjE5OTY3NTIzOTJ9.2UF7FOI-d344wJsZt5zVg7dC2r1DzqdmSV_bhSpdt-I';
            } else {
                $jwtToken = ''; // 生产环境的 JWT Token 需要配置
            }
        }

        $config = array(
            'appid' => '',
            'signaturePrivateKey' => '',
            'webhookVerifyPublicKey' => '',
            'callback_url' => '',
            'redirect_url' => '',
            'environment' => $environment,
            'jwtToken' => $jwtToken,
            'userAccessToken' => $userAccessToken,
            'language' => $language
        );

        // 调试日志
        $log = new Log('wonderpayment.log');
        $log->write('createForQRCode: environment = ' . $environment);
        $log->write('createForQRCode: jwtToken = ' . (empty($jwtToken) ? 'EMPTY' : 'SET (' . strlen($jwtToken) . ' chars)'));
        $log->write('createForQRCode: userAccessToken = ' . (empty($userAccessToken) ? 'EMPTY' : 'SET (' . strlen($userAccessToken) . ' chars)'));
        $log->write('createForQRCode: config = ' . json_encode(array_keys($config)));

        return new self($config);
    }
}
