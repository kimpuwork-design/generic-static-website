<?php
class ProviderAPI {
    private Database $db;
    private array $config;

    public function __construct(Database $db, array $config) {
        $this->db = $db;
        $this->config = $config;
    }

    public function listProviders(): array {
        return $this->db->fetchAll("SELECT * FROM providers WHERE active = 1 ORDER BY id DESC");
    }

    public function getProvider(int $providerId): ?array {
        return $this->db->fetch("SELECT * FROM providers WHERE id = ?", [$providerId]);
    }

    public function addProvider(string $name, string $baseUrl, string $apiKey, float $markupPercent): void {
        $this->db->query("INSERT INTO providers (name, base_url, api_key, markup_percent, active, created_at) VALUES (?, ?, ?, ?, 1, NOW())", [
            $name, $baseUrl, $apiKey, $markupPercent
        ]);
    }

    public function toggleProvider(int $id, bool $active): void {
        $this->db->query("UPDATE providers SET active = ? WHERE id = ?", [$active ? 1 : 0, $id]);
    }

    public function updateProvider(int $id, string $name, string $baseUrl, string $apiKey, float $markupPercent, bool $active): void {
        $this->db->query(
            "UPDATE providers SET name=?, base_url=?, api_key=?, markup_percent=?, active=? WHERE id=?",
            [$name, $baseUrl, $apiKey, $markupPercent, $active ? 1 : 0, $id]
        );
    }

    public function deleteProvider(int $id): void {
        // Cascades will remove services via FK
        $this->db->query("DELETE FROM providers WHERE id=?", [$id]);
    }

    public function pingProvider(int $id): array {
        $provider = $this->getProvider($id);
        if (!$provider) throw new RuntimeException("Provider not found");
        $data = $this->providerRequest($provider, ['action' => 'services']);
        $services = [];
        if (isset($data['data'])) $services = $data['data'];
        elseif (isset($data['services'])) $services = $data['services'];
        elseif (is_array($data)) $services = $data;
        $count = is_array($services) ? count($services) : 0;
        return ['ok' => true, 'services' => $count];
    }

    // SMM API v2 helpers
    public function providerRequest(array $provider, array $payload): array {
        $payload['key'] = $provider['api_key'];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $provider['base_url'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resp = curl_exec($ch);
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("Provider request failed: " . $err);
        }
        curl_close($ch);
        $json = json_decode($resp, true);
        if (!is_array($json)) {
            throw new RuntimeException("Invalid provider response: " . substr($resp, 0, 200));
        }
        return $json;
    }

    public function syncServices(int $providerId): int {
        $provider = $this->getProvider($providerId);
        if (!$provider) throw new RuntimeException("Provider not found");
        $data = $this->providerRequest($provider, ['action' => 'services']);
        if (!isset($data['data']) && !isset($data[0])) {
            // Some providers return plain array; normalize
            $services = isset($data['services']) ? $data['services'] : $data;
        } else {
            $services = $data['data'] ?? $data;
        }
        $count = 0;
        foreach ($services as $svc) {
            $externalId = (string)($svc['service'] ?? $svc['id'] ?? '');
            if (!$externalId) continue;
            $name = $svc['name'] ?? 'Service ' . $externalId;
            $category = $svc['category'] ?? ($svc['type'] ?? 'General');
            $rate = isset($svc['rate']) ? (float)$svc['rate'] : 0.0;
            $min = isset($svc['min']) ? (int)$svc['min'] : 0;
            $max = isset($svc['max']) ? (int)$svc['max'] : 0;
            $type = $svc['type'] ?? 'default';

            $exists = $this->db->fetch("SELECT id FROM services WHERE provider_id=? AND external_service_id=?", [$providerId, $externalId]);
            if ($exists) {
                $this->db->query("UPDATE services SET name=?, category=?, rate=?, min=?, max=?, type=?, active=1 WHERE id=?", [
                    $name, $category, $rate, $min, $max, $type, $exists['id']
                ]);
            } else {
                $this->db->query("INSERT INTO services (provider_id, external_service_id, name, category, rate, min, max, type, active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())", [
                    $providerId, $externalId, $name, $category, $rate, $min, $max, $type
                ]);
            }
            $count++;
        }
        return $count;
    }

    public function placeOrder(int $userId, int $serviceId, string $link, int $quantity): int {
        $service = $this->db->fetch("SELECT s.*, p.markup_percent FROM services s JOIN providers p ON p.id=s.provider_id WHERE s.id=?", [$serviceId]);
        if (!$service) throw new RuntimeException("Service not found");

        // Compute price
        $baseRatePer1k = (float)$service['rate'];
        $markupProvider = (float)($service['markup_percent'] ?? 0);
        $markupGlobal = (float)($this->config['app']['global_markup_percent'] ?? 0);
        $rateWithMarkup = $baseRatePer1k * (1 + $markupProvider / 100.0) * (1 + $markupGlobal / 100.0);
        $charge = round(($rateWithMarkup / 1000.0) * $quantity, 4);

        // Check user balance
        $user = $this->db->fetch("SELECT id, balance FROM users WHERE id=?", [$userId]);
        if (!$user) throw new RuntimeException("User not found");
        if ($user['balance'] < $charge) throw new RuntimeException("Insufficient balance");

        $provider = $this->getProvider((int)$service['provider_id']);
        if (!$provider || !$provider['active']) throw new RuntimeException("Provider inactive");

        // Place provider order
        $resp = $this->providerRequest($provider, [
            'action' => 'add',
            'service' => $service['external_service_id'],
            'link' => $link,
            'quantity' => $quantity,
        ]);

        if (!isset($resp['order'])) {
            $msg = isset($resp['error']) ? $resp['error'] : 'Unknown error';
            throw new RuntimeException("Provider order failed: " . $msg);
        }

        $providerOrderId = (string)$resp['order'];

        // Deduct balance and create order
        $this->db->begin();
        try {
            $newBalance = round($user['balance'] - $charge, 2);
            $this->db->query("UPDATE users SET balance=? WHERE id=?", [$newBalance, $userId]);
            $this->db->query("INSERT INTO transactions (user_id, type, amount, balance_after, meta, created_at) VALUES (?, 'order_charge', ?, ?, ?, NOW())", [
                $userId, $charge, $newBalance, json_encode(['service_id' => $serviceId, 'provider_order_id' => $providerOrderId]),
            ]);
            $this->db->query("INSERT INTO orders (user_id, service_id, link, quantity, status, charge, provider_order_id, created_at) VALUES (?, ?, ?, ?, 'processing', ?, ?, NOW())", [
                $userId, $serviceId, $link, $quantity, $charge, $providerOrderId,
            ]);
            $orderId = (int)$this->db->pdo()->lastInsertId();
            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function updateOrderStatus(int $orderId): void {
        $order = $this->db->fetch("SELECT o.*, s.external_service_id, s.provider_id FROM orders o JOIN services s ON s.id=o.service_id WHERE o.id=?", [$orderId]);
        if (!$order) return;
        $provider = $this->getProvider((int)$order['provider_id']);
        if (!$provider) return;

        $resp = $this->providerRequest($provider, [
            'action' => 'status',
            'order' => $order['provider_order_id'],
        ]);

        $status = $resp['status'] ?? $order['status'];
        $remains = $resp['remains'] ?? null;
        $charge = isset($resp['charge']) ? (float)$resp['charge'] : null;

        // Update order status
        $this->db->query("UPDATE orders SET status=?, updated_at=NOW() WHERE id=?", [$status, $orderId]);

        // Handle partial refunds (optional)
        if ($status === 'partial' && $charge !== null) {
            // If provider charged less than what we charged the user, refund the difference to wallet
            $ourCharge = (float)$order['charge'];
            if ($charge < $ourCharge) {
                $diff = round($ourCharge - $charge, 2);
                $user = $this->db->fetch("SELECT id, balance FROM users WHERE id=?", [$order['user_id']]);
                $newBalance = round($user['balance'] + $diff, 2);
                $this->db->begin();
                try {
                    $this->db->query("UPDATE users SET balance=? WHERE id=?", [$newBalance, $order['user_id']]);
                    $this->db->query("INSERT INTO transactions (user_id, type, amount, balance_after, meta, created_at) VALUES (?, 'refund', ?, ?, ?, NOW())", [
                        $order['user_id'], $diff, $newBalance, json_encode(['order_id' => $orderId, 'remains' => $remains]),
                    ]);
                    $this->db->commit();
                } catch (Throwable $e) {
                    $this->db->rollback();
                }
            }
        }
    }
}