<?php
class Database {
    private PDO $pdo;

    public function __construct(array $config) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $config['host'], $config['name'], $config['charset'] ?? 'utf8mb4');
        $this->pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function pdo(): PDO {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array {
        $stmt = $this->query($sql, $params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function begin(): void {
        $this->pdo->beginTransaction();
    }

    public function commit(): void {
        $this->pdo->commit();
    }

    public function rollback(): void {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}