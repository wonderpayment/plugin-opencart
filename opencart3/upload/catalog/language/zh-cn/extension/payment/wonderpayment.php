<?php
/**
 * WonderPayment 前台语言文件 - 简体中文
 * 兼容 OpenCart 1.x, 2.x, 3.x, 4.x
 */

// Text
$_['text_title']     = 'WonderPayment';
$_['text_loading']   = '加载中...';
$_['text_unpaid_order'] = '您有一个未支付的订单';
$_['text_order_id']  = '订单号：';
$_['text_order_total'] = '订单金额：';
$_['text_unpaid_alert'] = '您当前订单未支付，现已重新打开付款页面。如果已支付可以关闭此窗口重新检查，请注意不要重复支付。';

// Button
$_['button_confirm'] = '确认支付';
$_['button_i_have_paid'] = '检查支付';
$_['button_continue_payment'] = '继续支付';
$_['button_cancel'] = '取消';
$_['button_refund'] = '退款';

// Error
$_['error_order_id'] = '订单ID不能为空';
$_['error_order_not_found'] = '订单不存在';
$_['error_order_info_missing'] = '订单信息不存在';
$_['error_permission'] = '您没有权限执行此操作';
$_['error_payment_method'] = '此订单不是使用WonderPayment支付的';
$_['error_transaction_not_found'] = '未找到交易信息';
$_['error_cannot_refund'] = '此订单不允许退款';
$_['error_refund_failed'] = '退款失败';
$_['error_refund_exception'] = '退款异常';
$_['error_refund_amount_exceeded'] = '退款金额超过可退款金额';
$_['error_config_missing'] = '支付配置错误：缺少必要的配置项';
$_['error_library_missing'] = '支付系统配置错误：库文件不存在';
$_['error_payment_link_missing'] = '支付创建失败：未返回有效的支付链接';
$_['error_payment_exception'] = '支付异常';
$_['error_payment_error'] = '支付错误';
$_['error_order_id_missing'] = '订单ID不存在';
$_['error_payment_system'] = '支付系统错误';
$_['error_payment_record_missing'] = '未找到订单支付记录';
$_['error_create_payment_failed'] = '创建支付失败，请稍后重试';
$_['error_query_order_failed'] = '查询订单状态失败';
$_['error_query_order_exception'] = '订单状态查询异常';
$_['error_invalid_currency'] = '商家后台未配置该币种';

// Text
$_['text_refund_success'] = '退款成功';
$_['text_invalid_refund_amount'] = '无效的退款金额';
