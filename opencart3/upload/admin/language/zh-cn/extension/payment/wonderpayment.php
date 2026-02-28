<?php
/**
 * WonderPayment 后台语言文件 - 简体中文
 * 兼容 OpenCart 1.x, 2.x, 3.x, 4.x
 */

// Heading
$_['heading_title']    = 'WonderPayment';

// Text
$_['text_home']        = '首页';
$_['text_extension']   = '扩展';
$_['text_success']     = '成功：WonderPayment 已修改！';
$_['text_edit']        = '编辑 WonderPayment';
$_['text_enabled']     = '启用';
$_['text_disabled']    = '禁用';
$_['text_sandbox']     = '沙箱环境（测试）';
$_['text_live']        = '生产环境（正式）';
$_['text_all_zones']   = '所有区域';
$_['text_zh_cn']       = '简体中文';
$_['text_zh_hk']       = '繁體中文';
$_['text_zh_tw']       = '繁體中文';
$_['text_en_gb']       = '英语';

// Entry
$_['entry_appid']      = '应用 ID';
$_['entry_private_key']= '商户私钥';
$_['entry_public_key'] = 'Webhook 验证公钥';
$_['entry_environment']= '环境';
$_['entry_admin_language'] = '插件界面语言';
$_['entry_language']    = '语言';
$_['entry_total']      = '订单总额';
$_['entry_order_status']= '订单状态';
$_['entry_refund_order_status'] = '退款订单状态';
$_['entry_geo_zone']   = '地理区域';
$_['entry_status']     = '状态';
$_['entry_sort_order'] = '排序';
$_['entry_debug']      = '调试模式';

// Help
$_['help_environment'] = '选择支付环境：沙箱环境用于测试，生产环境用于正式交易。';
$_['help_admin_language'] = '选择插件配置界面的显示语言。选择后会自动更新系统语言设置。';
$_['help_total']       = '支付方式可用的最小订单金额。';
$_['help_debug']       = '启用调试模式以记录详细的支付日志。';
$_['help_setup']       = '<strong>配置说明：</strong><br>1. 在 WonderPayment 后台获取应用 ID、商户私钥和 Webhook 验证公钥<br>2. 将回调 URL 设置为: ' . HTTPS_CATALOG . 'payment_callback/wonderpayment<br>3. 将返回 URL 设置为: ' . HTTPS_CATALOG . 'checkout/success<br>4. 根据需要选择测试或生产环境';

// Error
$_['error_permission'] = '警告：您没有权限修改 WonderPayment！';
$_['error_appid']      = '请输入应用 ID！';
$_['error_private_key']= '请输入商户私钥！';
$_['error_public_key'] = '请输入 Webhook 验证公钥！';
$_['error_order_id'] = '订单ID不能为空';
$_['error_order_not_found'] = '订单不存在';
$_['error_permission_refund'] = '您没有权限执行退款操作';
$_['error_payment_method'] = '此订单不是使用WonderPayment支付的';
$_['error_transaction_not_found'] = '未找到交易信息';
$_['error_cannot_refund'] = '此订单不允许退款';
$_['error_refund_failed'] = '退款失败';
$_['error_refund_success'] = '退款成功';
$_['error_refund_exception'] = '退款异常';
$_['error_unknown'] = '未知错误';
$_['error_missing_parameters'] = '缺少必要的参数';
$_['error_return_not_found'] = '退货记录不存在';
$_['error_return_already_refunded'] = '该退货记录已经退款，不能重复退款';
$_['error_order_not_paid'] = '该订单未支付，无法退款';
$_['error_payment_info_incomplete'] = '该订单没有支付信息或支付信息不完整';
$_['error_payment_method_not_supported'] = '该订单的支付方式不是WonderPayment，无法进行退款操作';
$_['error_invalid_transaction_uuid'] = '退款失败: 无法获取有效的交易UUID。请确保该订单有完整的支付信息。';
$_['error_transaction_uuid_empty'] = '退款失败: 交易UUID为空，无法进行退款操作。请确保该订单有完整的支付信息。';
$_['error_order_fully_refunded'] = '该订单已经全额退款';
$_['error_config_missing'] = '支付配置错误：缺少必要的配置项';
$_['error_library_missing'] = '支付系统配置错误：库文件不存在';
$_['error_refund_amount_required'] = '退款金额不能为空';
$_['error_refund_amount_exceeded'] = '退款金额超过可退款金额';
$_['error_database_record_failed'] = '数据库记录失败';
$_['error_invalid_currency'] = '商家后台未配置该币种';

// Text
$_['text_total'] = '订单总额';
$_['text_total_refunded'] = '已退款总额';
$_['text_total_refunded_settlement'] = '已退款总额（结算币种）';
$_['text_available_refund'] = '可退款金额';
$_['text_available_refund_settlement'] = '可退款金额（结算币种）';
$_['text_refund_success'] = '退款成功';
$_['text_refund_amount'] = '退款金额';
$_['text_response_code'] = '响应代码';
$_['text_refund_type'] = '退款类型';
$_['text_full_refund'] = '全额退款';
$_['text_partial_refund'] = '部分退款';
$_['text_max_refund_amount'] = '最大可退金额';
$_['text_invalid_refund_amount'] = '无效的退款金额';
$_['text_confirm_refund_amount'] = '确认退款 {amount} {currency} ({wonder_amount} {wonder_currency}) 吗？';
$_['text_confirm_refund_amount_same'] = '确认退款 {amount} {currency} 吗？';
$_['text_refund_amount_range'] = '退款金额必须在 {min} 到 {max} {currency} 之间';
$_['text_refund_fetch_failed'] = '获取订单信息失败';
$_['text_refund_unknown_error'] = '退款失败: 未知错误';

// Button
$_['button_save']      = '保存';
$_['button_cancel']    = '取消';
$_['button_refund']     = '退款';

// QR Code Config
$_['text_qrcode_config'] = '扫码配置';
$_['text_qrcode_help'] = '扫描二维码登录 Wonder 账号，自动获取配置信息。';
$_['text_config_process_title'] = '配置流程说明';
$_['text_config_process_step1'] = '扫码登录：使用 Wonder App 扫描二维码登录您的 Wonder 账号';
$_['text_config_process_step2'] = '选择商家：从您的账号下选择要配置的商家店铺';
$_['text_config_process_step3'] = '其他配置：配置 Webhook 公钥等信息';
$_['text_config_notice_title'] = '注意事项';
$_['text_config_notice_item1'] = '请确保您已安装并登录 Wonder App';
$_['text_config_notice_item2'] = '配置过程中请勿关闭浏览器窗口';
$_['text_config_notice_item3'] = '配置完成后请点击"保存"按钮保存配置';
$_['text_config_notice_item4'] = '如需修改配置，可随时在页面下方手动编辑';
$_['text_operation_instructions'] = '操作说明';
$_['text_operation_step1'] = '打开手机上的 Wonder App';
$_['text_operation_step2'] = '在 App 中找到"扫一扫"功能';
$_['text_operation_step3'] = '扫描下方二维码进行登录';
$_['text_operation_step4'] = '扫码后请在 App 中确认登录';
$_['text_operation_select_step1'] = '从下方列表中选择您要配置的商家店铺';
$_['text_operation_select_step2'] = '点击商家卡片即可选中（选中后卡片会高亮显示）';
$_['text_operation_select_step3'] = '确认选择后点击"确认选择"按钮';
$_['text_operation_select_step4'] = '系统将为您生成该商家的支付配置';
$_['text_operation_config_step1'] = '系统已自动为您生成应用 ID 和商户私钥';
$_['text_operation_config_step2'] = '请配置 Webhook 验证公钥，用于验证 Wonder 回调通知的签名';
$_['text_operation_config_step3'] = '配置其他必要的选项（环境、订单状态等）';
$_['text_operation_config_step4'] = '点击"保存"按钮确认使用此配置';
$_['button_generate_qrcode'] = '扫码自动配置';
$_['text_loading'] = '正在生成二维码...';
$_['text_scan_qrcode'] = '请使用 Wonder App 扫描二维码登录';
$_['text_waiting_scan'] = '等待扫码登录...';
$_['text_login_success'] = '登录成功！';
$_['text_qrcode_expired'] = '二维码已过期，请重新生成';
$_['text_qrcode_expired_hint'] = '二维码已过期，请点击刷新按钮重新生成二维码';
$_['text_refresh'] = '刷新二维码';
$_['text_qrcode_cancelled'] = '二维码已取消，请重新生成';
$_['text_select_business'] = '选择商家';
$_['text_select_business_placeholder'] = '请选择商家';
$_['button_confirm_business'] = '确认商家';
$_['text_generating'] = '正在生成配置...';
$_['text_config_filled'] = '配置信息已自动填充！';
$_['text_select_business_error'] = '请选择一个商家';

// QR Code Modal
$_['text_step_scan'] = '扫码登录';
$_['text_step_select'] = '选择商家';
$_['text_step_other_config'] = '其他配置';
$_['text_step_complete'] = '完成';
$_['text_generating_keys'] = '正在生成密钥对...';
$_['text_please_wait'] = '请稍候...';
$_['text_config_success'] = '配置生成成功！';
$_['text_config_ready'] = '配置已生成';
$_['text_click_to_save'] = '请继续到下一步完成其他配置';
$_['text_other_config'] = '其他配置';
$_['text_appid'] = '应用 ID (App ID)';
$_['text_private_key'] = '商户私钥';
$_['text_private_key_warning'] = '⚠️ 请妥善保管私钥，不要泄露给他人。私钥用于签名支付请求，泄露后可能导致资金损失。';
$_['text_public_key'] = 'Webhook 验证公钥';
$_['text_copy'] = '复制';
$_['text_copied'] = '已复制';
$_['text_close'] = '关闭';
$_['text_auto_fill'] = '自动填充到表单';
$_['text_back'] = '返回';
$_['text_confirm_business'] = '确认选择';
$_['text_select_business'] = '选择商家';
$_['text_no_business_selected'] = '请先选择一个商家';
$_['text_already_logged_in'] = '已登录，跳转到商家选择';
$_['text_no_login'] = '未登录，请先扫码登录';
$_['text_step_not_available'] = '该步骤暂不可用，请先完成前面的步骤';
$_['text_save'] = '保存';
$_['text_config_saved'] = '配置已保存！';
$_['text_confirm_switch_account'] = '是否要更换账户？';
$_['text_confirm_use_config'] = '是否使用此配置？';
$_['text_confirm_change_language'] = '要更改后台语言,请前往"系统 > 本地化 > 语言"页面进行设置。是否现在跳转?';

// Business status
$_['text_business_status_active'] = '活跃';
$_['text_business_status_inactive'] = '未激活';

// Business field labels
$_['text_business_type'] = '类型';
$_['text_business_country'] = '国家';
$_['text_business_currency'] = '货币';
$_['text_business_role'] = '角色';

// Entry labels for config items
$_['entry_webhook_public_key'] = 'Webhook 公钥';
$_['entry_webhook_public_key_placeholder'] = '请输入 Wonder 提供的 Webhook 公钥';

// Help texts for config items
$_['help_order_status'] = '支付成功后的订单状态，例如：已付款';
$_['help_refund_order_status'] = '退款成功后的订单状态，例如：已退款';
$_['help_geo_zone'] = '支付方式适用的地区，选择所有地区或特定地区';
$_['help_status'] = '启用或禁用此支付方式';
$_['help_sort_order'] = '支付方式在前端的显示顺序，数字越小越靠前';
$_['help_debug'] = '启用调试日志，用于排查问题';
$_['help_webhook_public_key'] = 'Wonder 提供的公钥，用于验证 Wonder 回调通知的签名。请从 Wonder 后台获取并填写。注意：这不是商户密钥对的公钥。';

// Date
$_['date_format_short'] = 'Y-m-d';
