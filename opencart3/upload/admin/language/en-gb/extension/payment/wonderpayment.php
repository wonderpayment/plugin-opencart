<?php
/**
 * WonderPayment Admin Language File - English
 * Compatible with OpenCart 1.x, 2.x, 3.x, 4.x
 */

// Heading
$_['heading_title']    = 'WonderPayment';

// Text
$_['text_extension']   = 'Extensions';
$_['text_success']     = 'Success: WonderPayment has been modified!';
$_['text_edit']        = 'Edit WonderPayment';
$_['text_enabled']     = 'Enabled';
$_['text_disabled']    = 'Disabled';
$_['text_sandbox']     = 'Sandbox (Testing)';
$_['text_live']        = 'Live (Production)';
$_['text_all_zones']   = 'All Zones';
$_['text_zh_cn']       = 'Simplified Chinese (简体中文)';
$_['text_zh_hk']       = 'Traditional Chinese (繁體中文)';
$_['text_en_gb']       = 'English';

// Entry
$_['entry_appid']      = 'App ID';
$_['entry_private_key']= 'Merchant Private Key';
$_['entry_public_key'] = 'Webhook Public Key';
$_['entry_environment']= 'Environment';
$_['entry_admin_language'] = 'Plugin Interface Language';
$_['entry_language']    = 'Language';
$_['entry_total']      = 'Total';
$_['entry_order_status']= 'Order Status';
$_['entry_refund_order_status'] = 'Refund Order Status';
$_['entry_geo_zone']   = 'Geo Zone';
$_['entry_status']     = 'Status';
$_['entry_sort_order'] = 'Sort Order';
$_['entry_debug']      = 'Debug Mode';

// Help
$_['help_environment'] = 'Select the payment environment: Sandbox for testing, Live for production transactions.';
$_['help_admin_language'] = 'Select the display language for the plugin configuration interface. This will automatically update the system language setting.';
$_['help_total']       = 'Minimum order amount required for this payment method to be available.';
$_['help_debug']       = 'Enable debug mode to log detailed payment information.';
$_['help_setup']       = '<strong>Setup Instructions:</strong><br>1. Get App ID, Merchant Private Key and Webhook Public Key from WonderPayment dashboard<br>2. Set Callback URL to: ' . HTTPS_CATALOG . 'payment_callback/wonderpayment<br>3. Set Return URL to: ' . HTTPS_CATALOG . 'checkout/success<br>4. Choose Sandbox or Live environment as needed';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify WonderPayment!';
$_['error_appid']      = 'Please enter App ID!';
$_['error_private_key']= 'Please enter Merchant Private Key!';
$_['error_public_key'] = 'Please enter Webhook Public Key!';
$_['error_order_id'] = 'Order ID is required';
$_['error_order_not_found'] = 'Order not found';
$_['error_permission_refund'] = 'You do not have permission to process refunds';
$_['error_payment_method'] = 'This order was not paid with WonderPayment';
$_['error_transaction_not_found'] = 'Transaction information not found';
$_['error_cannot_refund'] = 'This order cannot be refunded';
$_['error_refund_failed'] = 'Refund failed';
$_['error_refund_exception'] = 'Refund exception';
$_['error_unknown'] = 'Unknown error';
$_['error_missing_parameters'] = 'Missing required parameters';
$_['error_return_not_found'] = 'Return record not found';
$_['error_return_already_refunded'] = 'This return has already been refunded';
$_['error_order_not_paid'] = 'This order has not been paid';
$_['error_payment_info_incomplete'] = 'This order has no payment information or payment information is incomplete';
$_['error_payment_method_not_supported'] = 'This order was not paid with WonderPayment';
$_['error_invalid_transaction_uuid'] = 'Refund failed: Unable to get valid transaction UUID. Please ensure this order has complete payment information.';
$_['error_transaction_uuid_empty'] = 'Refund failed: Transaction UUID is empty. Please ensure this order has complete payment information.';
$_['error_order_fully_refunded'] = 'This order has been fully refunded';
$_['error_config_missing'] = 'Payment configuration error: Missing required configuration items';
$_['error_library_missing'] = 'Payment system configuration error: Library file does not exist';
$_['error_refund_amount_required'] = 'Refund amount is required';
$_['error_refund_amount_exceeded'] = 'Refund amount exceeds available refund amount';
$_['error_database_record_failed'] = 'Database record failed';
$_['error_invalid_currency'] = 'Currency is not enabled in the merchant backend';

// Text
$_['text_total'] = 'Order Total';
$_['text_total_refunded'] = 'Total Refunded';
$_['text_total_refunded_settlement'] = 'Total Refunded (Settlement Currency)';
$_['text_available_refund'] = 'Available Refund';
$_['text_available_refund_settlement'] = 'Available Refund (Settlement Currency)';
$_['text_refund_success'] = 'Refund successful';
$_['text_refund_amount'] = 'Refund Amount';
$_['text_response_code'] = 'Response Code';
$_['text_refund_type'] = 'Refund Type';
$_['text_full_refund'] = 'Full Refund';
$_['text_partial_refund'] = 'Partial Refund';
$_['text_max_refund_amount'] = 'Maximum Refund Amount';
$_['text_invalid_refund_amount'] = 'Invalid refund amount';
$_['text_confirm_refund_amount'] = 'Confirm refund {amount} {currency} ({wonder_amount} {wonder_currency})?';
$_['text_confirm_refund_amount_same'] = 'Confirm refund {amount} {currency}?';
$_['text_refund_amount_range'] = 'Refund amount must be between {min} and {max} {currency}';
$_['text_refund_fetch_failed'] = 'Failed to fetch order info';
$_['text_refund_unknown_error'] = 'Refund failed: unknown error';

// Button
$_['button_save']      = 'Save';
$_['button_cancel']    = 'Cancel';
$_['button_refund']     = 'Refund';
// QR Code Config
$_['text_qrcode_config'] = 'QR Code Config';
$_['text_qrcode_help'] = 'Scan QR code to login to Wonder account and get configuration automatically.';
$_['text_config_process_title'] = 'Configuration Process';
$_['text_config_process_step1'] = 'Scan QR Code: Use Wonder App to scan QR code and login to your Wonder account';
$_['text_config_process_step2'] = 'Select Business: Select the business store you want to configure from your account';
$_['text_config_process_step3'] = 'Other Config: Configure webhook public key and other settings';
$_['text_config_notice_title'] = 'Important Notes';
$_['text_config_notice_item1'] = 'Please ensure you have installed and logged in to Wonder App';
$_['text_config_notice_item2'] = 'Do not close the browser window during configuration';
$_['text_config_notice_item3'] = 'Please click "Save" button to save configuration after completion';
$_['text_config_notice_item4'] = 'You can manually edit the configuration at any time if needed';
$_['text_operation_instructions'] = 'Operation Instructions';
$_['text_operation_step1'] = 'Open Wonder App on your phone';
$_['text_operation_step2'] = 'Find "Scan" feature in the App';
$_['text_operation_step3'] = 'Scan the QR code below to login';
$_['text_operation_step4'] = 'Confirm login in the App after scanning';
$_['text_operation_select_step1'] = 'Select the business store you want to configure from the list below';
$_['text_operation_select_step2'] = 'Click business card to select (selected card will be highlighted)';
$_['text_operation_select_step3'] = 'Click "Confirm Selection" button after confirming selection';
$_['text_operation_select_step4'] = 'System will generate payment configuration for this business';
$_['text_operation_config_step1'] = 'System has automatically generated App ID and Merchant Private Key for you';
$_['text_operation_config_step2'] = 'Please configure Webhook Public Key to verify Wonder callback notification signature';
$_['text_operation_config_step3'] = 'Configure other necessary options (environment, order status, etc.)';
$_['text_operation_config_step4'] = 'Click "Save" button to confirm using this configuration';
$_['button_generate_qrcode'] = 'Auto Config with QR Code';
$_['text_loading'] = 'Generating QR code...';
$_['text_scan_qrcode'] = 'Please scan QR code with Wonder App to login';
$_['text_waiting_scan'] = 'Waiting for scan...';
$_['text_login_success'] = 'Login successful!';
$_['text_qrcode_expired'] = 'QR code expired';
$_['text_qrcode_expired_hint'] = 'QR code has expired, please click refresh button to regenerate';
$_['text_refresh'] = 'Refresh QR Code';
$_['text_qrcode_cancelled'] = 'QR code cancelled, please regenerate';
$_['text_select_business'] = 'Select Business';
$_['text_select_business_placeholder'] = 'Please select business';
$_['button_confirm_business'] = 'Confirm Business';
$_['text_generating'] = 'Generating configuration...';
$_['text_config_filled'] = 'Configuration information has been automatically filled!';
$_['text_select_business_error'] = 'Please select a business';

// QR Code Modal
$_['text_step_scan'] = 'Scan QR Code';
$_['text_step_select'] = 'Select Business';
$_['text_step_other_config'] = 'Other Config';
$_['text_step_complete'] = 'Complete';
$_['text_generating_keys'] = 'Generating key pair...';
$_['text_please_wait'] = 'Please wait...';
$_['text_config_success'] = 'Configuration generated successfully!';
$_['text_config_ready'] = 'Configuration is ready';
$_['text_click_to_save'] = 'Please continue to next step to complete other configuration';
$_['text_other_config'] = 'Other Configuration';
$_['text_appid'] = 'App ID (App ID)';
$_['text_private_key'] = 'Merchant Private Key';
$_['text_private_key_warning'] = '⚠️ Please keep the private key safe and do not disclose it to others. The private key is used to sign payment requests, and disclosure may result in financial loss.';
$_['text_public_key'] = 'Webhook Public Key';
$_['text_copy'] = 'Copy';
$_['text_copied'] = 'Copied';
$_['text_close'] = 'Close';
$_['text_auto_fill'] = 'Auto-fill to form';
$_['text_back'] = 'Back';
$_['text_confirm_business'] = 'Confirm Selection';
$_['text_no_business_selected'] = 'Please select a business first';
$_['text_already_logged_in'] = 'Already logged in, skip to business selection';
$_['text_no_login'] = 'Not logged in, please scan QR code to login first';
$_['text_step_not_available'] = 'This step is not available yet, please complete previous steps first';
$_['text_save'] = 'Save';
$_['text_config_saved'] = 'Configuration saved!';
$_['text_confirm_switch_account'] = 'Do you want to switch account?';
$_['text_confirm_use_config'] = 'Do you want to use this configuration?';
$_['text_confirm_change_language'] = 'To change the admin language, please go to "System > Localisation > Languages" page. Do you want to go there now?';

// Business status
$_['text_business_status_active'] = 'Active';
$_['text_business_status_inactive'] = 'Inactive';

// Business field labels
$_['text_business_type'] = 'Type';
$_['text_business_country'] = 'Country';
$_['text_business_currency'] = 'Currency';
$_['text_business_role'] = 'Role';

// Entry labels for config items
$_['entry_webhook_public_key'] = 'Webhook Public Key';
$_['entry_webhook_public_key_placeholder'] = 'Please enter Webhook Public Key provided by Wonder';

// Help texts for config items
$_['help_order_status'] = 'Order status after successful payment, e.g., Paid';
$_['help_refund_order_status'] = 'Order status after successful refund, e.g., Refunded';
$_['help_geo_zone'] = 'Geographic zones where this payment method is available, select all zones or specific zones';
$_['help_status'] = 'Enable or disable this payment method';
$_['help_sort_order'] = 'Display order of this payment method on frontend, smaller number means higher priority';
$_['help_debug'] = 'Enable debug log for troubleshooting';
$_['help_webhook_public_key'] = 'Public key provided by Wonder, used to verify Wonder callback notification signature. Please get it from Wonder dashboard and fill it in. Note: This is not the merchant key pair public key.';
