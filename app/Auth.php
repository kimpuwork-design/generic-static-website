<?php
class Auth {
    private Database $db;
    private array $config;

    public function __construct(Database $db, array $config) {
        $this->db = $db;
        $this->config = $config;
        $sessionName = $config['security']['session_name'] ?? 'smm_session';
        session_name($sessionName);
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params($config['security']['cookie_lifetime'] ?? 0);
            session_start();
        }
    }

    public function login(string $email, string $password): bool {
        $user = $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        if (!$user) return false;
        if (!password_verify($password, $user['password_hash'])) return false;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }

    public function register(string $email, string $password): bool {
        $exists = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($exists) return false;
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $apiKey = bin2hex(random_bytes(20));
        $this->db->query("INSERT INTO users (email, password_hash, api_key, role, balance, created_at) VALUES (?, ?, ?, 'user', 0.00, NOW())", [
            $email, $hash, $apiKey
        ]);
        return true;
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public function user(): ?array {
        if (!isset($_SESSION['user_id'])) return null;
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }

    public function requireLogin(): void {
        if (!$this->user()) {
            header('Location: index.php?route=login');
            exit;
        }
    }

    public function requireAdmin(): void {
        $user = $this->user();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo "Forbidden";
            exit;
        }
    }

    public function csrfToken(): string {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf'];
    }

    public function verifyCsrf(string $token): bool {
        return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
    }
}