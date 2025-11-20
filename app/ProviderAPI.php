<?php
class ProviderAPI {
    private Database $db;
    private array $config;

    public function __construct(Database $db, array $config) {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * List all providers with full info (including health fields).
     * Use this in admin; for user-facing lists, filter active only in SQL.
     */
    public function listProviders(): array {
        return $this->db->fetchAll("SELECT * FROM providers ORDER BY id DESC");
    }

    /**
     * List only active providers (for user services).
     */
    public function listActiveProviders(): array {
        return $this->db->fetchAll("SELECT * FROM providers WHERE active = 1 ORDER BY id DESC");
    }

    public function getProvider(int $providerId): ?array {
        return $this->db->fetch("SELECT * FROM providers WHERE id = ?", [$providerId]);
    }

    public function addProvider(string $name, string $baseUrl, string $apiKey, float $markupPercent, ?string $options = null): void {
        $this->db->query("INSERT INTO providers (name, base_url, api_key, markup_percent, active, options, created_at) VALUES (?, ?, ?, ?, 1, ?, NOW())", [
            $name, $baseUrl, $apiKey, $markupPercent, $options
        ]);
    }

    public function toggleProvider(int $id, bool $active): void {
        $this->db->query("UPDATE providers SET active = ? WHERE id = ?", [$active ? 1 : 0, $id]);
    }

    public function updateProvider(int $id, string $name, string $baseUrl, string $apiKey, float $markupPercent, bool $active, ?string $options = null): void {
        $this->db->query(
            "UPDATE providers SET name=?, base_url=?, api_key=?, markup_percent=?, active=?, options=? WHERE id=?",
            [$name, $baseUrl, $apiKey, $markupPercent, $active ? 1 : 0, $options, $id]
        );
    }

    public function deleteProvider(int $id): void {
        $this->db->query("DELETE FROM providers WHERE id=?", [$id]);
    }

    private function opts(array $provider): array {
        $raw = $provider['options'] ?? null;
        if (!$raw) return [];
        $o = json_decode($raw, true);
        return is_array($o) ? $o : [];
    }

    private function paramKeys(array $opt): array {
        $pk = $opt['param_keys'] ?? [];
        return array_merge([
            'service' => 'service',
            'link' => 'link',
            'quantity' => 'quantity',
            'order' => 'order',
            'key' => 'key',
            'action' => 'action',
        ], $pk);
    }

    private function actionName(array $opt, string $name): string {
        $map = $opt['actions'] ?? [];
        $def = ['services' => 'services', 'add' => 'add', 'status' => 'status'];
        return $map[$name] ?? $def[$name] ?? $name;
    }

    private function endpoint(array $provider, array $opt, string $name): string {
        $base = $provider['base_url'];
        $end = $opt['endpoint'] ?? [];
        return $end[$name] ?? $base;
    }

    private function method(array $opt, string $name): string {
        $methods = $opt['methods'] ?? [];
        $m = $methods[$name] ?? ($opt['method'] ?? 'POST');
        return strtoupper($m);
    }

    private function servicesPath(array $opt): ?string {
        $resp = $opt['response'] ?? [];
        $path = $resp['services_path'] ?? null;
        return is_string($path) && $path !== '' ? $path : null;
    }

    private function respKey(array $opt, string $key, string $default): string {
        $r = $opt['response'] ?? [];
        return is_string($r[$key] ?? '') ? ($r[$key] ?: $default) : $default;
    }

    private function getPath(array $arr, string $path) {
        $parts = explode('.', $path);
        $cur = $arr;
        foreach ($parts as $p) {
            if (is_array($cur) && array_key_exists($p, $cur)) {
                $cur = $cur[$p];
            } else {
                return null;
            }
        }
        return $cur;
    }

    // Flexible provider request
    public function providerRequest(array $provider, array $payload, string $name): array {
        $opt = $this->opts($provider);
        $pk = $this->paramKeys($opt);
        $act = $this->actionName($opt, $name);
        $endpoint = $this->endpoint($provider, $opt, $name);
        $method = $this->method($opt, $name);
        $timeout = isset($opt['timeout']) ? (int)$opt['timeout'] : 30;
        $sslVerify = array_key_exists('ssl_verify', $opt) ? (bool)$opt['ssl_verify'] : true;
        $payloadStyle = ($opt['payload_style'] ?? 'form') === 'json' ? 'json' : 'form';
        $headers = (array)($opt['headers'] ?? []);
        $extra = (array)($opt['extra_params'] ?? []);

        // Attach key/action using configured names
        if ($pk['key'] !== '') {
            $payload[$pk['key']] = $provider['api_key'];
        }
        if ($pk['action'] !== '' && $act !== '') {
            $payload[$pk['action']] = $act;
        }
        foreach ($extra as $k => $v) {
            if (!array_key_exists($k, $payload)) $payload[$k] = $v;
        }

        $ch = curl_init();
        $opts = [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => $sslVerify,
        ];

        if ($method === 'GET') {
            $query = http_build_query($payload);
            $opts[CURLOPT_URL] = strpos($endpoint, '?') === false ? ($endpoint . '?' . $query) : ($endpoint . '&' . $query);
        } else {
            $opts[CURLOPT_POST] = true;
            if ($payloadStyle === 'json') {
                $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
                $headers[] = 'Content-Type: application/json';
            } else {
                $opts[CURLOPT_POSTFIELDS] = $payload;
            }
        }
        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        curl_setopt_array($ch, $opts);
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

        $opt = $this->opts($provider);
        $count = 0;

        try {
            $data = $this->providerRequest($provider, [], 'services');

            // Locate services list
            $services = null;
            $path = $this->servicesPath($opt);
            if ($path) {
                $services = $this->getPath($data, $path);
            }
            if ($services === null) {
                if (isset($data['data'])) $services = $data['data'];
                elseif (isset($data['services'])) $services = $data['services'];
                else $services = $data;
            }
            if (!is_array($services)) {
                throw new RuntimeException("Provider services not found");
            }

            // Mapping
            $map = (array)($opt['service_map'] ?? []);
            foreach ($services as $svc) {
                if (!is_array($svc)) continue;
                $externalId = (string)$this->firstValue($svc, $map['id'] ?? ['service','id']);
                if (!$externalId) continue;

                $name = (string)$this->firstValue($svc, $map['name'] ?? 'name');
                if ($name === '') $name = 'Service ' . $externalId;

                $category = (string)$this->firstValue($svc, $map['category'] ?? ['category','type']);
                if ($category === '') $category = 'General';

                $rate = (float)$this->firstValue($svc, $map['rate'] ?? 'rate');
                $min = (int)$this->firstValue($svc, $map['min'] ?? 'min');
                $max = (int)$this->firstValue($svc, $map['max'] ?? 'max');
                $type = (string)$this->firstValue($svc, $map['type'] ?? 'type');
                if ($type === '') $type = 'default';

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

            // Update provider health on success
            $this->db->query(
                "UPDATE providers SET last_sync_at=NOW(), last_sync_count=?, last_error=NULL WHERE id=?",
                [$count, $providerId]
            );

            return $count;
        } catch (Throwable $e) {
            // Record error for admin health view
            $msg = substr($e->getMessage(), 0, 500);
            $this->db->query(
                "UPDATE providers SET last_sync_at=NOW(), last_error=? WHERE id=?",
                [$msg, $providerId]
            );
            throw $e;
        }
    }

    private function firstValue(array $svc, $keySpec) {
        if (is_array($keySpec)) {
            foreach ($keySpec as $k) {
                if (isset($svc[$k])) return $svc[$k];
            }
            return null;
        }
        return $svc[$keySpec] ?? null;
    }

    public function placeOrder(int $userId, int $serviceId, string $link, int $quantity): int {
        $service = $this->db->fetch("SELECT s.*, p.markup_percent, p.options FROM services s JOIN providers p ON p.id=s.provider_id WHERE s.id=?", [$serviceId]);
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

        $opt = $this->opts($provider);
        $pk = $this->paramKeys($opt);

        $payload = [
            $pk['service'] => $service['external_service_id'],
            $pk['link'] => $link,
            $pk['quantity'] => $quantity,
        ];

        $resp = $this->providerRequest($provider, $payload, 'add');

        $orderKey = $this->respKey($opt, 'order_id_key', 'order');
        if (!isset($resp[$orderKey])) {
            $msg = isset($resp['error']) ? $resp['error'] : 'Unknown error';
            throw new RuntimeException("Provider order failed: " . $msg);
        }

        $providerOrderId = (string)$resp[$orderKey];

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

        $opt = $this->opts($provider);
        $pk = $this->paramKeys($opt);

        $resp = $this->providerRequest($provider, [
            $pk['order'] => $order['provider_order_id'],
        ], 'status');

        $statusKey = $this->respKey($opt, 'status_key', 'status');
        $chargeKey = $this->respKey($opt, 'charge_key', 'charge');
        $remainsKey = $this->respKey($opt, 'remains_key', 'remains');

        $status = $resp[$statusKey] ?? $order['status'];
        $remains = $resp[$remainsKey] ?? null;
        $charge = isset($resp[$chargeKey]) ? (float)$resp[$chargeKey] : null;

        $this->db->query("UPDATE orders SET status=?, updated_at=NOW() WHERE id=?", [$status, $orderId]);

        if ($status === 'partial' && $charge !== null) {
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

    public function pingProvider(int $id): array {
        $provider = $this->getProvider($id);
        if (!$provider) throw new RuntimeException("Provider not found");
        try {
            $data = $this->providerRequest($provider, [], 'services');
            $opt = $this->opts($provider);
            $services = null;
            $path = $this->servicesPath($opt);
            if ($path) $services = $this->getPath($data, $path);
            if ($services === null) {
                if (isset($data['data'])) $services = $data['data'];
                elseif (isset($data['services'])) $services = $data['services'];
                else $services = $data;
            }
            $count = is_array($services) ? count($services) : 0;

            $this->db->query(
                "UPDATE providers SET last_ping_at=NOW(), last_ping_ok=1, last_error=NULL WHERE id=?",
                [$id]
            );

            return ['ok' => true, 'services' => $count];
        } catch (Throwable $e) {
            $msg = substr($e->getMessage(), 0, 500);
            $this->db->query(
                "UPDATE providers SET last_ping_at=NOW(), last_ping_ok=0, last_error=? WHERE id=?",
                [$msg, $id]
            );
            throw $e;
        }
    }
}