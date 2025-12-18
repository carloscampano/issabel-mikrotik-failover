<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | MikroTik Failover Module Installer for Issabel                       |
  +----------------------------------------------------------------------+
*/

// Ruta de la base de datos SQLite
$db_path = '/var/www/db/mikrotik_failover.db';

// Crear la base de datos SQLite si no existe
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tabla de configuración MikroTik
    $db->exec("
        CREATE TABLE IF NOT EXISTS mikrotik_config (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip VARCHAR(50) NOT NULL,
            port INTEGER DEFAULT 22,
            user VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL
        )
    ");

    // Tabla de configuración SMTP
    $db->exec("
        CREATE TABLE IF NOT EXISTS smtp_config (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            server VARCHAR(255) NOT NULL,
            port INTEGER DEFAULT 587,
            user VARCHAR(255),
            password VARCHAR(255),
            from_email VARCHAR(255) NOT NULL,
            to_email VARCHAR(255) NOT NULL,
            enabled INTEGER DEFAULT 1
        )
    ");

    // Tabla de configuración de monitoreo
    $db->exec("
        CREATE TABLE IF NOT EXISTS monitoring_config (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            delay_before_action INTEGER DEFAULT 10,
            retry_mode VARCHAR(20) DEFAULT 'single',
            retry_count INTEGER DEFAULT 3,
            retry_interval INTEGER DEFAULT 30,
            cooldown_period INTEGER DEFAULT 300,
            enabled INTEGER DEFAULT 1
        )
    ");

    // Tabla de comandos
    $db->exec("
        CREATE TABLE IF NOT EXISTS commands (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            command TEXT NOT NULL,
            description VARCHAR(255),
            order_num INTEGER DEFAULT 0,
            enabled INTEGER DEFAULT 1
        )
    ");

    // Tabla de troncales monitoreadas
    $db->exec("
        CREATE TABLE IF NOT EXISTS monitored_trunks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            trunk_name VARCHAR(100) NOT NULL UNIQUE,
            enabled INTEGER DEFAULT 1
        )
    ");

    // Tabla de log de eventos
    $db->exec("
        CREATE TABLE IF NOT EXISTS event_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            trunk_name VARCHAR(100) NOT NULL,
            event_type VARCHAR(50) NOT NULL,
            trunk_ip VARCHAR(50),
            ping_result VARCHAR(20),
            action_taken TEXT,
            command_output TEXT,
            status VARCHAR(50),
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Tabla de control de cooldown por troncal
    $db->exec("
        CREATE TABLE IF NOT EXISTS trunk_cooldown (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            trunk_name VARCHAR(100) NOT NULL UNIQUE,
            last_action_time DATETIME
        )
    ");

    // Tabla de estado del daemon
    $db->exec("
        CREATE TABLE IF NOT EXISTS daemon_status (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            status VARCHAR(20) DEFAULT 'stopped',
            pid INTEGER,
            last_update DATETIME
        )
    ");

    // Insertar configuración inicial de monitoreo si no existe
    $stmt = $db->query("SELECT COUNT(*) FROM monitoring_config");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO monitoring_config (delay_before_action, retry_mode, retry_count, retry_interval, cooldown_period, enabled) VALUES (10, 'single', 3, 30, 300, 1)");
    }

    // Insertar estado inicial del daemon si no existe
    $stmt = $db->query("SELECT COUNT(*) FROM daemon_status");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO daemon_status (status, last_update) VALUES ('stopped', datetime('now', 'localtime'))");
    }

    echo "Database and tables created successfully.\n";

} catch (PDOException $e) {
    echo "Error creating database: " . $e->getMessage() . "\n";
    exit(1);
}

// Establecer permisos correctos
chmod($db_path, 0666);
chown($db_path, 'asterisk');
chgrp($db_path, 'asterisk');

echo "MikroTik Failover module installed successfully.\n";
?>
