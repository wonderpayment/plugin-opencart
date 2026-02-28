<?php
/**
 * WonderPayment 前台模型
 */

class ModelExtensionPaymentWonderpayment extends Model {
    /**
     * 获取支付方式
     * 判断支付方式是否可用
     */
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/wonderpayment');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_wonderpayment_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('payment_wonderpayment_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payment_wonderpayment_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'wonderpayment',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_wonderpayment_sort_order')
            );
        }

        return $method_data;
    }

    /**
     * 添加或更新订单记录
     */
    public function addOrderRecord($order_id, $reference_number, $order_number = '', $transaction_uuid = null) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "wonderpayment_order`
            WHERE order_id = '" . (int)$order_id . "'
        ");

        if ($query->num_rows) {
            // 更新现有记录
            $count = $query->row['count'] + 1;
            $updateFields = array(
                "count = '" . (int)$count . "'",
                "reference_number = '" . $this->db->escape($reference_number) . "'",
                "order_number = '" . $this->db->escape($order_number) . "'",
                "date_modified = NOW()"
            );

            if ($transaction_uuid) {
                $updateFields[] = "transaction_uuid = '" . $this->db->escape($transaction_uuid) . "'";
            }

            $this->db->query("
                UPDATE `" . DB_PREFIX . "wonderpayment_order`
                SET " . implode(', ', $updateFields) . "
                WHERE order_id = '" . (int)$order_id . "'
            ");
        } else {
            // 插入新记录
            $insertFields = array(
                "order_id = '" . (int)$order_id . "'",
                "count = 1",
                "reference_number = '" . $this->db->escape($reference_number) . "'",
                "order_number = '" . $this->db->escape($order_number) . "'",
                "status = 'pending'",
                "date_added = NOW()",
                "date_modified = NOW()"
            );

            if ($transaction_uuid) {
                $insertFields[] = "transaction_uuid = '" . $this->db->escape($transaction_uuid) . "'";
            }

            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "wonderpayment_order`
                SET " . implode(', ', $insertFields) . "
            ");
        }
    }

    /**
     * 获取订单记录
     */
    public function getOrderRecord($order_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "wonderpayment_order`
            WHERE order_id = '" . (int)$order_id . "'
        ");

        return $query->row;
    }

    /**
     * 通过 reference_number 获取订单记录
     */
    public function getOrderRecordByReferenceNumber($reference_number) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "wonderpayment_order`
            WHERE reference_number = '" . $this->db->escape($reference_number) . "'
            ORDER BY date_modified DESC
            LIMIT 1
        ");

        return $query->row;
    }

    /**
 * 更新支付状态
     */
    public function updatePaymentStatus($order_id, $status) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "wonderpayment_order`
            SET status = '" . $this->db->escape($status) . "',
                date_modified = NOW()
            WHERE order_id = '" . (int)$order_id . "'
        ");
    }

    /**
     * 更新交易 UUID
     */
    public function updateTransactionUuid($order_id, $transaction_uuid) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "wonderpayment_order`
            SET transaction_uuid = '" . $this->db->escape($transaction_uuid) . "',
                date_modified = NOW()
            WHERE order_id = '" . (int)$order_id . "'
        ");
    }

    /**
     * 获取订单支付信息（包括额外字段）
     */
    public function getOrderPaymentInfo($order_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "wonderpayment_order`
            WHERE order_id = '" . (int)$order_id . "'
        ");

        return $query->row;
    }

    /**
     * 获取退款记录
     */
    public function getRefundsByOrderId($order_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "wonderpayment_refund`
            WHERE order_id = '" . (int)$order_id . "'
            ORDER BY date_added DESC
        ");

        return $query->rows;
    }

    /**
     * 获取已退款总额
     */
    public function getTotalRefundedAmount($order_id) {
        $query = $this->db->query("
            SELECT SUM(refund_amount) as total
            FROM `" . DB_PREFIX . "wonderpayment_refund`
            WHERE order_id = '" . (int)$order_id . "' AND status = 'success'
        ");

        return $query->row['total'] ?? 0;
    }

    /**
     * 添加退款记录
     */
    public function addRefundRecord($order_id, $reference_number, $transaction_uuid, $refund_amount, $refund_currency, $refund_data, $status = 'success') {
        $this->ensureRefundSchema();
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "wonderpayment_refund`
            SET order_id = '" . (int)$order_id . "',
                reference_number = '" . $this->db->escape($reference_number) . "',
                transaction_uuid = '" . $this->db->escape($transaction_uuid) . "',
                refund_amount = '" . (float)$refund_amount . "',
                refund_currency = '" . $this->db->escape($refund_currency) . "',
                original_amount = " . (isset($refund_data['original_amount']) ? "'" . (float)$refund_data['original_amount'] . "'" : "NULL") . ",
                original_currency = " . (isset($refund_data['original_currency']) ? "'" . $this->db->escape($refund_data['original_currency']) . "'" : "NULL") . ",
                refund_data = '" . $this->db->escape(json_encode($refund_data)) . "',
                status = '" . $this->db->escape($status) . "',
                date_added = NOW()
        ");

        return $this->db->getLastId();
    }

    public function ensureRefundSchema() {
        $columns = array();
        $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "wonderpayment_refund`");
        foreach ($query->rows as $row) {
            $columns[$row['Field']] = true;
        }

        if (!isset($columns['original_amount'])) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "wonderpayment_refund` ADD `original_amount` decimal(10,2) DEFAULT NULL");
        }
        if (!isset($columns['original_currency'])) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "wonderpayment_refund` ADD `original_currency` varchar(3) DEFAULT NULL");
        }
    }
}
