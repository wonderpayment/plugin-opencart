<?php
/**
 * WonderPayment 退款控制器
 * 在订单详情页面显示退款信息
 */

class ControllerExtensionPaymentWonderpaymentRefund extends Controller {
    public function index() {
        $this->load->language('extension/payment/wonderpayment');
        $this->load->model('extension/payment/wonderpayment');
        $this->load->model('sale/order');

        $data['heading_title'] = $this->language->get('heading_title');

        if (isset($this->request->get['order_id'])) {
            $order_id = (int)$this->request->get['order_id'];
        } else {
            $order_id = 0;
        }

        $data['order_id'] = $order_id;

        // 获取订单信息
        $order_info = $this->model_sale_order->getOrder($order_id);

        if ($order_info) {
            $data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], true);
            $data['currency_code'] = $order_info['currency_code'];

            // 获取支付信息
            $payment_info = $this->model_extension_payment_wonderpayment->getOrderPaymentInfo($order_id);

            if ($payment_info) {
                $data['payment_info'] = $payment_info;
                $data['reference_number'] = $payment_info['reference_number'];
                $data['payment_status'] = $payment_info['status'];

                // 获取退款记录
                $this->model_extension_payment_wonderpayment->ensureRefundSchema();
                $refunds = $this->model_extension_payment_wonderpayment->getRefundsByOrderId($order_id);
                $data['refunds'] = array();
                $total_refunded_original = 0;
                $originalCurrency = $order_info['currency_code'];
                $settlementCurrency = $originalCurrency;
                $rate = 1;
                $summary = $this->model_extension_payment_wonderpayment->getRefundSummary($order_id, $originalCurrency, $settlementCurrency, $rate);
                $data['refund_rate'] = $rate;
                $data['settlement_currency'] = $settlementCurrency;

                foreach ($refunds as $refund) {
                    $refund_data = json_decode($refund['refund_data'], true);
                    if (isset($refund['original_amount']) && isset($refund['original_currency'])) {
                        $original_amount = (float)$refund['original_amount'];
                        $original_currency = $refund['original_currency'];
                    } elseif (isset($refund_data['original_amount']) && isset($refund_data['original_currency'])) {
                        $original_amount = (float)$refund_data['original_amount'];
                        $original_currency = $refund_data['original_currency'];
                    } else {
                        $original_amount = (float)$refund['refund_amount'];
                        $original_currency = $refund['refund_currency'];
                        // Refund currency equals original currency; no conversion needed.
                    }

                    if ($refund['status'] == 'success') {
                        $total_refunded_original += $original_amount;
                    }

                    $data['refunds'][] = array(
                        'refund_id' => $refund['refund_id'],
                        'amount' => $this->currency->format($original_amount, $original_currency, 1, true),
                        'transaction_uuid' => $refund['transaction_uuid'],
                        'status' => $refund['status'],
                        'date_added' => date($this->language->get('date_format_short'), strtotime($refund['date_added']))
                    );
                }

                $data['total_refunded'] = $this->currency->format($total_refunded_original, $originalCurrency, 1, true);
                $data['total_refunded_settlement'] = $this->currency->format($summary['total_settlement'], $settlementCurrency, 1, true);

                // 计算可退款金额
                $original_total = $order_info['total'];
                $data['available_refund'] = $this->currency->format(max(0, $original_total - $total_refunded_original), $originalCurrency, 1, true);
                $data['available_refund_value'] = max(0, $original_total - $total_refunded_original);
                $data['available_refund_settlement'] = $this->currency->format(max(0, ($original_total - $total_refunded_original) * $rate), $settlementCurrency, 1, true);

                // 获取交易UUID（这里需要从SDK响应中获取，暂时使用reference_number）
                // 实际使用时需要从支付响应中获取transaction_uuid
                $data['transaction_uuid'] = ''; // 需要从支付记录中获取

            } else {
                $data['payment_info'] = null;
                $data['refunds'] = array();
                $data['total_refunded'] = $this->currency->format(0, $order_info['currency_code'], 1, true);
                $data['available_refund'] = $this->currency->format(0, $order_info['currency_code'], 1, true);
                $data['available_refund_value'] = 0;
                $data['settlement_currency'] = $order_info['currency_code'];
                $data['refund_rate'] = 1;
                $data['total_refunded_settlement'] = $this->currency->format(0, $data['settlement_currency'], 1, true);
                $data['available_refund_settlement'] = $this->currency->format(0, $data['settlement_currency'], 1, true);
            }
        } else {
            $data['payment_info'] = null;
            $data['refunds'] = array();
            $data['total_refunded'] = $this->currency->format(0, 'USD', 1, true);
            $data['available_refund'] = $this->currency->format(0, 'USD', 1, true);
            $data['available_refund_value'] = 0;
            $data['settlement_currency'] = $this->config->get('config_currency');
            $data['refund_rate'] = 1;
            $data['total_refunded_settlement'] = $this->currency->format(0, $data['settlement_currency'], 1, true);
            $data['available_refund_settlement'] = $this->currency->format(0, $data['settlement_currency'], 1, true);
        }

        return $this->load->view('extension/payment/wonderpayment_refund', $data);
    }
}
