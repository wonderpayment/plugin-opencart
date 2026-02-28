<?php
/**
 * WonderPayment 前台語言文件 - 繁體中文
 * 兼容 OpenCart 1.x, 2.x, 3.x, 4.x
 */

// Text
$_['text_title']     = 'WonderPayment';
$_['text_loading']   = '加載中...';
$_['text_unpaid_order'] = '您有一個未支付的訂單';
$_['text_order_id']  = '訂單號：';
$_['text_order_total'] = '訂單金額：';
$_['text_unpaid_alert'] = '您當前訂單未支付，現已重新打開付款頁面。如果已支付可以關閉此窗口重新檢查，請注意不要重複支付。';

// Button
$_['button_confirm'] = '確認支付';
$_['button_i_have_paid'] = '檢查支付';
$_['button_continue_payment'] = '繼續支付';
$_['button_cancel'] = '取消';
$_['button_refund'] = '退款';

// Error
$_['error_order_id'] = '訂單ID不能為空';
$_['error_order_not_found'] = '訂單不存在';
$_['error_permission'] = '您沒有權限執行此操作';
$_['error_payment_method'] = '此訂單不是使用WonderPayment支付的';
$_['error_transaction_not_found'] = '未找到交易信息';
$_['error_cannot_refund'] = '此訂單不允許退款';
$_['error_refund_failed'] = '退款失敗';
$_['error_refund_exception'] = '退款異常';
$_['error_refund_amount_exceeded'] = '退款金額超過可退款金額';
$_['error_order_info_missing'] = '訂單信息不存在';
$_['error_config_missing'] = '支付配置錯誤：缺少必要的配置項';
$_['error_library_missing'] = '支付系統配置錯誤：庫文件不存在';
$_['error_payment_link_missing'] = '支付建立失敗：未返回有效的支付連結';
$_['error_payment_exception'] = '支付異常';
$_['error_payment_error'] = '支付錯誤';
$_['error_order_id_missing'] = '訂單ID不存在';
$_['error_payment_system'] = '支付系統錯誤';
$_['error_payment_record_missing'] = '未找到訂單支付記錄';
$_['error_create_payment_failed'] = '建立支付失敗，請稍後重試';
$_['error_query_order_failed'] = '查詢訂單狀態失敗';
$_['error_query_order_exception'] = '訂單狀態查詢異常';
$_['error_invalid_currency'] = '商家後台未配置該幣種';

// Success
$_['text_refund_success'] = '退款成功';
$_['text_invalid_refund_amount'] = '無效的退款金額';
