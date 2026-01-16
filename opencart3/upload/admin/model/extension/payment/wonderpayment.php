<?php
/**
 * WonderPayment 后台模型
 */

class ModelExtensionPaymentWonderpayment extends Model {
    /**
     * 插件安装 - 创建数据库表
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wonderpayment_order` (
                `order_id` int(11) NOT NULL,
                `count` int(11) NOT NULL DEFAULT 0,
                `reference_number` varchar(64) NOT NULL,
                `status` varchar(32) NOT NULL DEFAULT 'pending',
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                `transaction_uuid` varchar(64) DEFAULT NULL,
                `order_number` varchar(64) DEFAULT NULL,
                PRIMARY KEY (`order_id`),
                KEY `reference_number` (`reference_number`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wonderpayment_refund` (
                `refund_id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) NOT NULL,
                `reference_number` varchar(64) NOT NULL,
                `transaction_uuid` varchar(64) DEFAULT NULL,
                `refund_amount` decimal(10,2) NOT NULL,
                `refund_currency` varchar(3) NOT NULL,
                `original_amount` decimal(10,2) DEFAULT NULL,
                `original_currency` varchar(3) DEFAULT NULL,
                `refund_data` text,
                `status` varchar(32) NOT NULL DEFAULT 'pending',
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`refund_id`),
                KEY `order_id` (`order_id`),
                KEY `reference_number` (`reference_number`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");

        $this->ensureCurrencies();
    }

    /**
     * 插件卸载 - 删除数据库表
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wonderpayment_order`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wonderpayment_refund`");
    }

    protected function ensureCurrencies() {
        $currencies = array(
            array(
                'code' => 'HKD',
                'title' => 'Hong Kong Dollar',
                'symbol_left' => 'HK$',
                'symbol_right' => '',
                'decimal_place' => '2',
                'value' => 1.00000000,
                'status' => 1
            )
        );

        foreach ($currencies as $currency) {
            $code = $this->db->escape($currency['code']);
            $query = $this->db->query("SELECT currency_id FROM `" . DB_PREFIX . "currency` WHERE code = '" . $code . "' LIMIT 1");

            if (!$query->num_rows) {
                $this->db->query("
                    INSERT INTO `" . DB_PREFIX . "currency`
                    SET title = '" . $this->db->escape($currency['title']) . "',
                        code = '" . $code . "',
                        symbol_left = '" . $this->db->escape($currency['symbol_left']) . "',
                        symbol_right = '" . $this->db->escape($currency['symbol_right']) . "',
                        decimal_place = '" . $this->db->escape($currency['decimal_place']) . "',
                        value = '" . (float)$currency['value'] . "',
                        status = '" . (int)$currency['status'] . "',
                        date_modified = NOW()
                ");
            }
        }
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

    public function getRefundSummary($order_id, $originalCurrency, $settlementCurrency, $rate) {
        $refunds = $this->getRefundsByOrderId($order_id);
        $total_original = 0.0;
        $total_settlement = 0.0;

        foreach ($refunds as $refund) {
            if ($refund['status'] != 'success') {
                continue;
            }

            $original_amount = null;
            $original_currency = null;

            if (isset($refund['original_amount']) && isset($refund['original_currency'])) {
                $original_amount = (float)$refund['original_amount'];
                $original_currency = $refund['original_currency'];
            }

            if ($original_amount === null || $original_currency === null) {
                $refund_data = json_decode($refund['refund_data'], true);
                if (isset($refund_data['original_amount']) && isset($refund_data['original_currency'])) {
                    $original_amount = (float)$refund_data['original_amount'];
                    $original_currency = $refund_data['original_currency'];
                }
            }

            if ($original_amount === null || $original_currency === null) {
                $original_amount = (float)$refund['refund_amount'];
                $original_currency = $refund['refund_currency'];
                if ($original_currency === $settlementCurrency && $originalCurrency !== $settlementCurrency && $rate > 0) {
                    $original_amount = $original_amount / $rate;
                    $original_currency = $originalCurrency;
                }
            }

            if ($original_currency === $originalCurrency) {
                $total_original += $original_amount;
                if ($rate > 0) {
                    $total_settlement += $original_amount * $rate;
                }
            } else {
                $total_original += $original_amount;
            }

            if ($refund['refund_currency'] === $settlementCurrency) {
                $total_settlement += (float)$refund['refund_amount'];
            }
        }

        return array(
            'total_original' => $total_original,
            'total_settlement' => $total_settlement
        );
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
     * 更新交易UUID
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
     * 通过reference_number查询订单记录
     */
    public function getOrderRecordByReferenceNumber($reference_number) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "wonderpayment_order`
            WHERE reference_number = '" . $this->db->escape($reference_number) . "'
        ");

        return $query->row;
    }
}
