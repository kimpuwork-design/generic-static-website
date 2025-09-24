<?php
class PayPalIPN {
    private Database $db;
    private array $config;

    public function __construct(Database $db, array $config) {
        $this->db = $db;
        $this->config = $config;
    }

    public function verifyIPN(array $postData): bool {
        $verifyUrl = $this->config['payments']['paypal']['ipn_verify_url'] ?? 'https://ipnpb.paypal.com/cgi-bin/webscr';

        $payload = 'cmd=_notify-validate';
        foreach ($postData as $key => $value) {
            $payload .= '&' . $key . '=' . urlencode($value);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $verifyUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Connection: Close', 'User-Agent: SMM-Panel-IPN-Verify'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        if ($resp === false) {
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        return trim($resp) === 'VERIFIED';
    }

    public function handle(array $postData): void {
        // Basic checks
        if (!$this->verifyIPN($postData)) {
            return;
        }

        $paymentStatus = $postData['payment_status'] ?? '';
        $receiverEmail = $postData['receiver_email'] ?? '';
        $gross = (float)($postData['mc_gross'] ?? 0);
        $currency = $postData['mc_currency'] ?? '';
        $txnId = $postData['txn_id'] ?? '';
        $custom = $postData['custom'] ?? '';

        $pp = $this->config['payments']['paypal'] ?? [];
        if (($pp['enabled'] ?? false) !== true) return;
        if (strtolower($receiverEmail) !== strtolower($pp['business_email'] ?? '')) return;
        if ($currency !== ($pp['currency'] ?? 'USD')) return;
        if ($paymentStatus !== 'Completed') return;

        // Custom payload: we expect "user_id:123"
        $userId = null;
        if ($custom) {
            if (preg_match('/user_id:(\d+)/', $custom, $m)) {
                $userId = (int)$m[1];
            }
        }
        if (!$userId) return;

        // Check if txn already processed
        $exists = $this->db->fetch("SELECT id FROM deposits WHERE txn_id=?", [$txnId]);
        if ($exists) return;

        // Create deposit and credit balance
        $this->db->begin();
        try {
            $this->db->query("INSERT INTO deposits (user_id, amount, method, status, txn_id, raw_payload, created_at, updated_at) VALUES (?, ?, 'paypal', 'completed', ?, ?, NOW(), NOW())", [
                $userId, $gross, $txnId, json_encode($postData),
            ]);

            $user = $this->db->fetch("SELECT id, balance FROM users WHERE id=?", [$userId]);
            if (!$user) {
                $this->db->rollback();
                return;
            }
            $newBalance = round($user['balance'] + $gross, 2);
            $this->db->query("UPDATE users SET balance=? WHERE id=?", [$newBalance, $userId]);
            $this->db->query("INSERT INTO transactions (user_id, type, amount, balance_after, meta, created_at) VALUES (?, 'deposit', ?, ?, ?, NOW())", [
                $userId, $gross, $newBalance, json_encode(['gateway' => 'paypal', 'txn_id' => $txnId]),
            ]);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
        }
    }
}