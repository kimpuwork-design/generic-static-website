<?php
class OAuth {
    private Database $db;
    private array $config;
    private Auth $auth;

    public function __construct(Database $db, array $config, Auth $auth) {
        $this->db = $db;
        $this->config = $config;
        $this->auth = $auth;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function listProviders(): array {
        $rows = $this->db->fetchAll("SELECT * FROM oauth_providers ORDER BY provider ASC");
        $out = [];
        foreach ($rows as $r) {
            $out[$r['provider']] = $r;
        }
        return $out;
    }

    public function getProvider(string $provider): ?array {
        return $this->db->fetch("SELECT * FROM oauth_providers WHERE provider = ?", [$provider]);
    }

    public function setProvider(string $provider, int $enabled, ?string $clientId, ?string $clientSecret, ?string $redirectUri): void {
        $exists = $this->getProvider($provider);
        if ($exists) {
            $this->db->query("UPDATE oauth_providers SET enabled=?, client_id=?, client_secret=?, redirect_uri=?, updated_at=NOW() WHERE provider=?",
                [$enabled ? 1 : 0, $clientId, $clientSecret, $redirectUri, $provider]);
        } else {
            $this->db->query("INSERT INTO oauth_providers (provider, enabled, client_id, client_secret, redirect_uri, updated_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$provider, $enabled ? 1 : 0, $clientId, $clientSecret, $redirectUri]);
        }
    }

    public function ensureSeed(): void {
        $existing = $this->listProviders();
        $defaults = ['google','github','facebook'];
        foreach ($defaults as $p) {
            if (!isset($existing[$p])) {
                $this->setProvider($p, 0, null, null, null);
            }
        }
    }

    public function start(string $provider): void {
        $prov = $this->getProvider($provider);
        if (!$prov || !$prov['enabled']) {
            http_response_code(400);
            echo "Provider disabled.";
            return;
        }
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_provider'] = $provider;

        $clientId = $prov['client_id'];
        $redirectUri = $prov['redirect_uri'];

        switch ($provider) {
            case 'google':
                $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                    'client_id' => $clientId,
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => 'openid email profile',
                    'state' => $state,
                    'access_type' => 'online',
                    'prompt' => 'select_account',
                ]);
                header('Location: ' . $authUrl);
                exit;
            case 'github':
                $authUrl = 'https://github.com/login/oauth/authorize?' . http_build_query([
                    'client_id' => $clientId,
                    'redirect_uri' => $redirectUri,
                    'scope' => 'user:email',
                    'state' => $state,
                ]);
                header('Location: ' . $authUrl);
                exit;
            case 'facebook':
                $authUrl = 'https://www.facebook.com/v17.0/dialog/oauth?' . http_build_query([
                    'client_id' => $clientId,
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => 'email',
                    'state' => $state,
                ]);
                header('Location: ' . $authUrl);
                exit;
            default:
                http_response_code(400);
                echo "Unknown provider.";
                return;
        }
    }

    public function callback(): void {
        $provider = $_SESSION['oauth_provider'] ?? '';
        $state = $_GET['state'] ?? '';
        $code = $_GET['code'] ?? '';
        if (!$provider || !$code || !$state || !hash_equals($_SESSION['oauth_state'] ?? '', $state)) {
            http_response_code(400);
            echo "Invalid state or missing code.";
            return;
        }
        $prov = $this->getProvider($provider);
        if (!$prov) { http_response_code(400); echo "Provider not configured."; return; }

        $clientId = $prov['client_id'];
        $clientSecret = $prov['client_secret'];
        $redirectUri = $prov['redirect_uri'];

        $email = null;

        if ($provider === 'google') {
            // Exchange code for token
            $token = $this->postJson('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);
            $access = $token['access_token'] ?? null;
            if (!$access) { $this->fail("token"); return; }
            $user = $this->getJson('https://www.googleapis.com/oauth2/v3/userinfo', ['Authorization: Bearer ' . $access]);
            $email = $user['email'] ?? null;
        } elseif ($provider === 'github') {
            $token = $this->postForm('https://github.com/login/oauth/access_token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
            ], ['Accept: application/json']);
            $access = $token['access_token'] ?? null;
            if (!$access) { $this->fail("token"); return; }
            $user = $this->getJson('https://api.github.com/user', [
                'Authorization: Bearer ' . $access,
                'User-Agent: SMM-Panel',
                'Accept: application/vnd.github+json'
            ]);
            $email = $user['email'] ?? null;
            if (!$email) {
                $emails = $this->getJson('https://api.github.com/user/emails', [
                    'Authorization: Bearer ' . $access,
                    'User-Agent: SMM-Panel',
                    'Accept: application/vnd.github+json'
                ]);
                if (is_array($emails)) {
                    foreach ($emails as $e) {
                        if (!empty($e['primary'])) { $email = $e['email']; break; }
                    }
                    if (!$email && isset($emails[0]['email'])) $email = $emails[0]['email'];
                }
            }
        } elseif ($provider === 'facebook') {
            $token = $this->getJson('https://graph.facebook.com/v17.0/oauth/access_token?' . http_build_query([
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]));
            $access = $token['access_token'] ?? null;
            if (!$access) { $this->fail("token"); return; }
            $user = $this->getJson('https://graph.facebook.com/me?fields=id,name,email&access_token=' . urlencode($access));
            $email = $user['email'] ?? null;
        } else {
            $this->fail("provider");
            return;
        }

        if (!$email) {
            $this->fail("email");
            return;
        }

        // Login or register
        $existing = $this->db->fetch("SELECT * FROM users WHERE email=?", [$email]);
        if ($existing) {
            $_SESSION['user_id'] = $existing['id'];
            $_SESSION['role'] = $existing['role'];
        } else {
            $password = bin2hex(random_bytes(8)); // random
            $this->auth->register($email, $password);
            $user = $this->db->fetch("SELECT * FROM users WHERE email=?", [$email]);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
        }
        unset($_SESSION['oauth_state'], $_SESSION['oauth_provider']);
        header('Location: index.php?route=dashboard');
        exit;
    }

    private function fail(string $reason): void {
        unset($_SESSION['oauth_state'], $_SESSION['oauth_provider']);
        http_response_code(400);
        echo "OAuth failed: " . htmlspecialchars($reason);
    }

    private function postJson(string $url, array $data, array $headers = []): array {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        if ($headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $resp = curl_exec($ch);
        if ($resp === false) { $err = curl_error($ch); curl_close($ch); return []; }
        curl_close($ch);
        $json = json_decode($resp, true);
        return is_array($json) ? $json : [];
    }

    private function postForm(string $url, array $data, array $headers = []): array {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        if ($headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $resp = curl_exec($ch);
        if ($resp === false) { $err = curl_error($ch); curl_close($ch); return []; }
        curl_close($ch);
        $json = json_decode($resp, true);
        return is_array($json) ? $json : [];
    }

    private function getJson(string $url, array $headers = []): array {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        if ($headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $resp = curl_exec($ch);
        if ($resp === false) { $err = curl_error($ch); curl_close($ch); return []; }
        curl_close($ch);
        $json = json_decode($resp, true);
        return is_array($json) ? $json : [];
    }
}