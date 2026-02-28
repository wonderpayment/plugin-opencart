<?php
/**
 * WonderPayment 後台語言文件 - 繁體中文
 * 兼容 OpenCart 1.x, 2.x, 3.x, 4.x
 */

// Heading
$_['heading_title']    = 'WonderPayment';

// Text
$_['text_home']        = '首頁';
$_['text_extension']   = '擴展功能';
$_['text_success']     = '成功：WonderPayment 已修改！';
$_['text_edit']        = '編輯 WonderPayment';
$_['text_enabled']     = '啟用';
$_['text_disabled']    = '禁用';
$_['text_sandbox']     = '沙箱環境（測試）';
$_['text_live']        = '生產環境（正式）';
$_['text_all_zones']   = '所有區域';
$_['text_zh_cn']       = '簡體中文';
$_['text_zh_hk']       = '繁體中文';
$_['text_zh_tw']       = '繁體中文';
$_['text_en_gb']       = '英語';

// Entry
$_['entry_appid']      = '應用 ID';
$_['entry_private_key']= '商戶私鑰';
$_['entry_public_key'] = 'Webhook 驗證公鑰';
$_['entry_environment']= '環境';
$_['entry_admin_language'] = '插件界面語言';
$_['entry_language']    = '語言';
$_['entry_total']      = '訂單總額';
$_['entry_order_status']= '訂單狀態';
$_['entry_refund_order_status'] = '退款訂單狀態';
$_['entry_geo_zone']   = '地理區域';
$_['entry_status']     = '狀態';
$_['entry_sort_order'] = '排序';
$_['entry_debug']      = '調試模式';

// Help
$_['help_environment'] = '選擇支付環境：沙箱環境用於測試，生產環境用於正式交易。';
$_['help_admin_language'] = '選擇插件配置界面的顯示語言。選擇後會自動更新系統語言設置。';
$_['help_total']       = '支付方式可用的最小訂單金額。';
$_['help_debug']       = '啟用調試模式以記錄詳細的支付日誌。';
$_['help_setup']       = '<strong>配置說明：</strong><br>1. 在 WonderPayment 後台獲取應用 ID、商戶私鑰和 Webhook 驗證公鑰<br>2. 將回調 URL 設置為: ' . HTTPS_CATALOG . 'payment_callback/wonderpayment<br>3. 將返回 URL 設置為: ' . HTTPS_CATALOG . 'checkout/success<br>4. 根據需要選擇測試或生產環境';

// Error
$_['error_permission'] = '警告：您沒有權限修改 WonderPayment！';
$_['error_appid']      = '請輸入應用 ID！';
$_['error_private_key']= '請輸入商戶私鑰！';
$_['error_public_key'] = '請輸入 Webhook 驗證公鑰！';
$_['error_order_id'] = '訂單ID不能為空';
$_['error_order_not_found'] = '訂單不存在';
$_['error_permission_refund'] = '您沒有權限執行退款操作';
$_['error_payment_method'] = '此訂單不是使用WonderPayment支付的';
$_['error_transaction_not_found'] = '未找到交易信息';
$_['error_cannot_refund'] = '此訂單不允許退款';
$_['error_refund_failed'] = '退款失敗';
$_['error_refund_success'] = '退款成功';
$_['error_refund_exception'] = '退款異常';
$_['error_unknown'] = '未知錯誤';
$_['error_missing_parameters'] = '缺少必要的參數';
$_['error_return_not_found'] = '退貨記錄不存在';
$_['error_return_already_refunded'] = '該退貨記錄已經退款，不能重複退款';
$_['error_order_not_paid'] = '該訂單未支付，無法退款';
$_['error_payment_info_incomplete'] = '該訂單沒有支付信息或支付信息不完整';
$_['error_payment_method_not_supported'] = '該訂單的支付方式不是WonderPayment，無法進行退款操作';
$_['error_invalid_transaction_uuid'] = '退款失敗: 無法獲取有效的交易UUID。請確保該訂單有完整的支付信息。';
$_['error_transaction_uuid_empty'] = '退款失敗: 交易UUID為空，無法進行退款操作。請確保該訂單有完整的支付信息。';
$_['error_order_fully_refunded'] = '該訂單已經全額退款';
$_['error_config_missing'] = '支付配置錯誤：缺少必要的配置項';
$_['error_library_missing'] = '支付系統配置錯誤：庫文件不存在';
$_['error_refund_amount_required'] = '退款金額不能為空';
$_['error_refund_amount_exceeded'] = '退款金額超過可退款金額';
$_['error_database_record_failed'] = '數據庫記錄失敗';
$_['error_invalid_currency'] = '商家後台未配置該幣種';

// Text
$_['text_total'] = '訂單總額';
$_['text_total_refunded'] = '已退款總額';
$_['text_total_refunded_settlement'] = '已退款總額（結算幣種）';
$_['text_available_refund'] = '可退款金額';
$_['text_available_refund_settlement'] = '可退款金額（結算幣種）';
$_['text_refund_success'] = '退款成功';
$_['text_refund_amount'] = '退款金額';
$_['text_response_code'] = '響應代碼';
$_['text_refund_type'] = '退款類型';
$_['text_full_refund'] = '全額退款';
$_['text_partial_refund'] = '部分退款';
$_['text_max_refund_amount'] = '最大可退金額';
$_['text_invalid_refund_amount'] = '無效的退款金額';
$_['text_confirm_refund_amount'] = '確認退款 {amount} {currency} ({wonder_amount} {wonder_currency}) 嗎？';
$_['text_confirm_refund_amount_same'] = '確認退款 {amount} {currency} 嗎？';
$_['text_refund_amount_range'] = '退款金額必須在 {min} 到 {max} {currency} 之間';
$_['text_refund_fetch_failed'] = '獲取訂單信息失敗';
$_['text_refund_unknown_error'] = '退款失敗: 未知錯誤';

// Button
$_['button_save']      = '保存';
$_['button_cancel']    = '取消';
$_['button_refund']     = '退款';

// QR Code Config
$_['text_qrcode_config'] = '掃碼配置';
$_['text_qrcode_help'] = '掃描二維碼登錄 Wonder 賬號，自動獲取配置信息。';
$_['text_config_process_title'] = '配置流程說明';
$_['text_config_process_step1'] = '掃碼登錄：使用 Wonder App 掃描二維碼登錄您的 Wonder 賬號';
$_['text_config_process_step2'] = '選擇商家：從您的賬號下選擇要配置的商家店鋪';
$_['text_config_process_step3'] = '其他配置：配置 Webhook 公鑰等信息';
$_['text_config_notice_title'] = '注意事項';
$_['text_config_notice_item1'] = '請確保您已安裝並登錄 Wonder App';
$_['text_config_notice_item2'] = '配置過程中請勿關閉瀏覽器窗口';
$_['text_config_notice_item3'] = '配置完成後請點擊"保存"按鈕保存配置';
$_['text_config_notice_item4'] = '如需修改配置，可隨時在頁面下方手動編輯';
$_['text_operation_instructions'] = '操作說明';
$_['text_operation_step1'] = '打開手機上的 Wonder App';
$_['text_operation_step2'] = '在 App 中找到"掃一掃"功能';
$_['text_operation_step3'] = '掃描下方二維碼進行登錄';
$_['text_operation_step4'] = '掃碼後請在 App 中確認登錄';
$_['text_operation_select_step1'] = '從下方列表中選擇您要配置的商家店鋪';
$_['text_operation_select_step2'] = '點擊商家卡片即可選中（選中後卡片會高亮顯示）';
$_['text_operation_select_step3'] = '確認選擇後點擊"確認選擇"按鈕';
$_['text_operation_select_step4'] = '系統將為您生成該商家的支付配置';
$_['text_operation_config_step1'] = '系統已自動為您生成應用 ID 和商戶私鑰';
$_['text_operation_config_step2'] = '請配置 Webhook 驗證公鑰，用於驗證 Wonder 回調通知的簽名';
$_['text_operation_config_step3'] = '配置其他必要的選項（環境、訂單狀態等）';
$_['text_operation_config_step4'] = '點擊"保存"按鈕確認使用此配置';
$_['button_generate_qrcode'] = '掃碼自動配置';
$_['text_loading'] = '正在生成二維碼...';
$_['text_scan_qrcode'] = '請使用 Wonder App 掃描二維碼登錄';
$_['text_waiting_scan'] = '等待掃碼登錄...';
$_['text_login_success'] = '登錄成功！';
$_['text_qrcode_expired'] = '二維碼已過期';
$_['text_qrcode_expired_hint'] = '二維碼已過期，請點擊刷新按鈕重新生成';
$_['text_refresh'] = '刷新二維碼';
$_['text_qrcode_cancelled'] = '二維碼已取消，請重新生成';
$_['text_select_business'] = '選擇商家';
$_['text_select_business_placeholder'] = '請選擇商家';
$_['button_confirm_business'] = '確認商家';
$_['text_generating'] = '正在生成配置...';
$_['text_config_filled'] = '配置信息已自動填充！';
$_['text_select_business_error'] = '請選擇一個商家';

// QR Code Modal
$_['text_step_scan'] = '掃碼登錄';
$_['text_step_select'] = '選擇商家';
$_['text_step_other_config'] = '其他配置';
$_['text_step_complete'] = '完成';
$_['text_generating_keys'] = '正在生成密鑰對...';
$_['text_please_wait'] = '請稍候...';
$_['text_config_success'] = '配置生成成功！';
$_['text_config_ready'] = '配置已生成';
$_['text_click_to_save'] = '請繼續到下一步完成其他配置';
$_['text_other_config'] = '其他配置';
$_['text_appid'] = '應用 ID (App ID)';
$_['text_private_key'] = '商戶私鑰';
$_['text_private_key_warning'] = '⚠️ 請妥善保管私鑰，不要泄露給他人。私鑰用於簽名支付請求，泄露後可能導致資金損失。';
$_['text_public_key'] = 'Webhook 驗證公鑰';
$_['text_copy'] = '複製';
$_['text_copied'] = '已複製';
$_['text_close'] = '關閉';
$_['text_auto_fill'] = '自動填充到表單';
$_['text_back'] = '返回';
$_['text_confirm_business'] = '確認選擇';
$_['text_no_business_selected'] = '請先選擇一個商家';
$_['text_already_logged_in'] = '已登錄，跳轉到商家選擇';
$_['text_no_login'] = '未登錄，請先掃碼登錄';
$_['text_step_not_available'] = '該步驟暫不可用，請先完成前面的步驟';
$_['text_save'] = '保存';
$_['text_config_saved'] = '配置已保存！';
$_['text_confirm_switch_account'] = '是否要更換賬戶？';
$_['text_confirm_use_config'] = '是否使用此配置？';
$_['text_confirm_change_language'] = '要更改後台語言，請前往「系統 > 本地化 > 語言」頁面進行設置。是否現在跳轉？';

// Business status
$_['text_business_status_active'] = '活躍';
$_['text_business_status_inactive'] = '未激活';

// Business field labels
$_['text_business_type'] = '類型';
$_['text_business_country'] = '國家';
$_['text_business_currency'] = '貨幣';
$_['text_business_role'] = '角色';

// Entry labels for config items
$_['entry_webhook_public_key'] = 'Webhook 公鑰';
$_['entry_webhook_public_key_placeholder'] = '請輸入 Wonder 提供的 Webhook 公鑰';

// Help texts for config items
$_['help_order_status'] = '支付成功後的訂單狀態，例如：已付款';
$_['help_refund_order_status'] = '退款成功後的訂單狀態，例如：已退款';
$_['help_geo_zone'] = '支付方式適用的地區，選擇所有地區或特定地區';
$_['help_status'] = '啟用或禁用此支付方式';
$_['help_sort_order'] = '支付方式在前端的顯示順序，數字越小越靠前';
$_['help_debug'] = '啟用調試日誌，用於排查問題';
$_['help_webhook_public_key'] = 'Wonder 提供的公鑰，用於驗證 Wonder 回調通知的簽名。請從 Wonder 後台獲取並填寫。注意：這不是商戶密鑰對的公鑰。';

// Date
$_['date_format_short'] = 'Y-m-d';
