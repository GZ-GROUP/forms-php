<?php
// ── CONEXIÓN PDO (PostgreSQL) ─────────────────────────────────────────────────
// Variables de entorno configuradas en .env / Dokploy

function get_pdo(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host = $_ENV['DB_HOST']     ?? getenv('DB_HOST')     ?? 'db';
    $port = $_ENV['DB_PORT']     ?? getenv('DB_PORT')     ?? '5432';
    $db   = $_ENV['DB_NAME']     ?? getenv('DB_NAME')     ?? 'utp_forms';
    $user = $_ENV['DB_USER']     ?? getenv('DB_USER')     ?? 'postgres';
    $pass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? 'postgres';

    $dsn = "pgsql:host={$host};port={$port};dbname={$db};options='--client_encoding=UTF8'";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // En producción nunca exponer el mensaje completo
        error_log('DB connection error: ' . $e->getMessage());
        http_response_code(503);
        die(json_encode(['error' => 'No se pudo conectar a la base de datos.']));
    }

    return $pdo;
}