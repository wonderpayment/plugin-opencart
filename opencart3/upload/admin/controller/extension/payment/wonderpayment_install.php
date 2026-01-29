<?php

/**
 * WonderPayment 数据库安装脚本
 */
class ControllerExtensionPaymentWonderpaymentInstall extends Controller
{
    public function index()
    {
        $this->load->model('extension/payment/wonderpayment');
        $this->model_extension_payment_wonderpayment->install();
    }
}
