-- ── init.sql – se ejecuta automáticamente al crear el contenedor ─────────────

-- Tabla principal de usuarios
CREATE TABLE IF NOT EXISTS users (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(255) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,          -- hash bcrypt
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índice en email para búsquedas rápidas y chequeo de duplicados
CREATE INDEX IF NOT EXISTS idx_users_email ON users (email);

-- (Opcional) tabla de intentos de registro para auditoría / rate-limiting futuro
CREATE TABLE IF NOT EXISTS register_attempts (
    id         SERIAL PRIMARY KEY,
    ip         INET,
    email      VARCHAR(255),
    success    BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);