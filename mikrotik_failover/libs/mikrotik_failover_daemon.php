#!/usr/bin/php
<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | MikroTik Failover Daemon - AMI Monitor                               |
  +----------------------------------------------------------------------+
  | Daemon que escucha eventos AMI y ejecuta acciones en MikroTik        |
  | cuando un troncal está unreachable pero responde a ping              |
  +----------------------------------------------------------------------+
*/

// Configuración
define('DB_PATH', '/var/www/db/mikrotik_failover.db');
define('PID_FILE', '/var/run/mikrotik_failover.pid');
define('LOG_FILE', '/var/log/mikrotik_failover.log');

// Set timezone to avoid warnings
date_default_timezone_set('America/Santiago');

// Variables globales
$running = true;
$db = null;
$ami_socket = null;

// Manejo de señales
declare(ticks = 1);
pcntl_signal(SIGTERM, 'signal_handler');
pcntl_signal(SIGINT, 'signal_handler');
pcntl_signal(SIGHUP, 'signal_handler');

function signal_handler($signo) {
    global $running;
    log_message("Received signal $signo, shutting down...");
    $running = false;
}

function log_message($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND);
    echo $log_entry;
}

function get_db() {
    global $db;
    if ($db === null) {
        try {
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            log_message("Database error: " . $e->getMessage());
            return null;
        }
    }
    return $db;
}

function get_config($table) {
    $db = get_db();
    if (!$db) return null;

    try {
        $stmt = $db->query("SELECT * FROM $table WHERE id = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        log_message("Error getting config from $table: " . $e->getMessage());
        return null;
    }
}

function get_monitored_trunks() {
    $db = get_db();
    if (!$db) return array();

    try {
        $stmt = $db->query("SELECT trunk_name FROM monitored_trunks WHERE enabled = 1");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        log_message("Error getting monitored trunks: " . $e->getMessage());
        return array();
    }
}

function get_commands() {
    $db = get_db();
    if (!$db) return array();

    try {
        $stmt = $db->query("SELECT command FROM commands WHERE enabled = 1 ORDER BY order_num ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        log_message("Error getting commands: " . $e->getMessage());
        return array();
    }
}

function add_event_log($trunk_name, $event_type, $trunk_ip, $ping_result, $action_taken, $command_output, $status) {
    $db = get_db();
    if (!$db) return false;

    try {
        $stmt = $db->prepare("INSERT INTO event_log (trunk_name, event_type, trunk_ip, ping_result, action_taken, command_output, status, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'))");
        $stmt->execute(array($trunk_name, $event_type, $trunk_ip, $ping_result, $action_taken, $command_output, $status));
        return true;
    } catch (PDOException $e) {
        log_message("Error adding event log: " . $e->getMessage());
        return false;
    }
}

function check_cooldown($trunk_name) {
    $db = get_db();
    if (!$db) return false;

    $config = get_config('monitoring_config');
    $cooldown = isset($config['cooldown_period']) ? $config['cooldown_period'] : 300;

    try {
        $stmt = $db->prepare("SELECT last_action_time FROM trunk_cooldown WHERE trunk_name = ?");
        $stmt->execute(array($trunk_name));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) return true; // No previous action, can proceed

        $last_time = strtotime($result['last_action_time']);
        $now = time();

        return ($now - $last_time) >= $cooldown;
    } catch (PDOException $e) {
        log_message("Error checking cooldown: " . $e->getMessage());
        return false;
    }
}

function update_cooldown($trunk_name) {
    $db = get_db();
    if (!$db) return false;

    try {
        $stmt = $db->prepare("INSERT OR REPLACE INTO trunk_cooldown (trunk_name, last_action_time) VALUES (?, datetime('now', 'localtime'))");
        $stmt->execute(array($trunk_name));
        return true;
    } catch (PDOException $e) {
        log_message("Error updating cooldown: " . $e->getMessage());
        return false;
    }
}

function update_daemon_status($status, $pid = null) {
    $db = get_db();
    if (!$db) return false;

    try {
        $stmt = $db->prepare("UPDATE daemon_status SET status = ?, pid = ?, last_update = datetime('now', 'localtime') WHERE id = 1");
        $stmt->execute(array($status, $pid));
        return true;
    } catch (PDOException $e) {
        log_message("Error updating daemon status: " . $e->getMessage());
        return false;
    }
}

function ping_host($ip, $count = 3) {
    $output = array();
    $retval = 0;
    exec("ping -c $count -W 2 " . escapeshellarg($ip) . " 2>&1", $output, $retval);
    return $retval == 0;
}

function get_trunk_ip($trunk_name) {
    $output = array();
    exec("/usr/sbin/asterisk -rx 'sip show peer " . escapeshellarg($trunk_name) . "' 2>/dev/null", $output);

    foreach ($output as $line) {
        if (preg_match('/^\s*Addr->IP\s*:\s*(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
            return $matches[1];
        }
        // También buscar en el formato alternativo
        if (preg_match('/^\s*Tohost\s*:\s*(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
            return $matches[1];
        }
    }

    // Intentar obtener de sip show peers
    $output = array();
    exec("/usr/sbin/asterisk -rx 'sip show peers' 2>/dev/null", $output);
    foreach ($output as $line) {
        if (preg_match('/^' . preg_quote($trunk_name, '/') . '\s+(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
            return $matches[1];
        }
    }

    return null;
}

function execute_mikrotik_commands() {
    $mikrotik_config = get_config('mikrotik_config');
    if (!$mikrotik_config) {
        log_message("No MikroTik configuration found");
        return false;
    }

    $commands = get_commands();
    if (empty($commands)) {
        log_message("No commands configured");
        return false;
    }

    $ip = $mikrotik_config['ip'];
    $port = $mikrotik_config['port'];
    $user = $mikrotik_config['user'];
    $password = $mikrotik_config['password'];

    log_message("Connecting to MikroTik at $ip:$port");

    // Conectar via SSH
    $connection = @ssh2_connect($ip, $port);
    if (!$connection) {
        log_message("Failed to connect to MikroTik");
        return false;
    }

    if (!@ssh2_auth_password($connection, $user, $password)) {
        log_message("MikroTik authentication failed");
        return false;
    }

    $all_output = array();
    foreach ($commands as $command) {
        log_message("Executing command: $command");
        $stream = ssh2_exec($connection, $command);
        if ($stream) {
            stream_set_blocking($stream, true);
            $output = stream_get_contents($stream);
            fclose($stream);
            $all_output[] = "[$command]: $output";
            log_message("Command output: $output");
        } else {
            $all_output[] = "[$command]: Failed to execute";
            log_message("Failed to execute command: $command");
        }
    }

    return implode("\n", $all_output);
}

function send_email_notification($subject, $body) {
    $smtp_config = get_config('smtp_config');
    if (!$smtp_config || !$smtp_config['enabled']) {
        log_message("Email notifications disabled or not configured");
        return false;
    }

    // Verificar que PHPMailer esté disponible
    $phpmailer_paths = array(
        '/usr/share/php/PHPMailer/PHPMailerAutoload.php',
        '/usr/share/php/PHPMailer/src/PHPMailer.php',
        '/usr/share/php/libphp-phpmailer/class.phpmailer.php',
        '/usr/share/phpmailer/class.phpmailer.php'
    );

    $phpmailer_loaded = false;
    foreach ($phpmailer_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            // Cargar SMTP si es necesario
            $smtp_path = dirname($path) . '/class.smtp.php';
            if (file_exists($smtp_path)) {
                require_once($smtp_path);
            }
            $phpmailer_loaded = true;
            break;
        }
    }

    if (!$phpmailer_loaded) {
        log_message("PHPMailer not found, cannot send email");
        return false;
    }

    try {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $smtp_config['server'];
        $mail->Port = intval($smtp_config['port']);
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_config['user'];
        $mail->Password = $smtp_config['password'];

        // Configurar seguridad según el puerto
        $port = intval($smtp_config['port']);
        if ($port == 465) {
            $mail->SMTPSecure = 'ssl';
        } elseif ($port == 587) {
            $mail->SMTPSecure = 'tls';
        }

        $mail->setFrom($smtp_config['from_email'], 'MikroTik Failover');
        $mail->addAddress($smtp_config['to_email']);

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->CharSet = 'UTF-8';

        if ($mail->send()) {
            log_message("Email sent successfully to " . $smtp_config['to_email']);
            return true;
        } else {
            log_message("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    } catch (Exception $e) {
        log_message("Email exception: " . $e->getMessage());
        return false;
    }
}

function connect_ami() {
    global $ami_socket;

    // Leer configuración AMI
    $ami_host = '127.0.0.1';
    $ami_port = 5038;
    $ami_user = 'admin';
    $ami_secret = '';

    // Intentar leer el secret de /etc/issabel.conf
    if (file_exists('/etc/issabel.conf')) {
        $conf = parse_ini_file('/etc/issabel.conf');
        if (isset($conf['amiadminpwd'])) {
            $ami_secret = $conf['amiadminpwd'];
        }
    }

    // Fallback a manager.conf
    if (empty($ami_secret) && file_exists('/etc/asterisk/manager.conf')) {
        $manager_conf = file_get_contents('/etc/asterisk/manager.conf');
        if (preg_match('/secret\s*=\s*(.+)/', $manager_conf, $matches)) {
            $ami_secret = trim($matches[1]);
        }
    }

    log_message("Connecting to AMI at $ami_host:$ami_port");

    $ami_socket = @fsockopen($ami_host, $ami_port, $errno, $errstr, 10);
    if (!$ami_socket) {
        log_message("Failed to connect to AMI: $errstr ($errno)");
        return false;
    }

    stream_set_timeout($ami_socket, 1);

    // Leer banner
    $banner = fgets($ami_socket);
    log_message("AMI Banner: " . trim($banner));

    // Login
    $login = "Action: Login\r\n";
    $login .= "Username: $ami_user\r\n";
    $login .= "Secret: $ami_secret\r\n";
    $login .= "\r\n";

    fwrite($ami_socket, $login);

    // Leer respuesta
    $response = '';
    while (($line = fgets($ami_socket)) !== false) {
        $response .= $line;
        if (trim($line) === '') break;
    }

    if (strpos($response, 'Success') !== false) {
        log_message("AMI login successful");
        return true;
    } else {
        log_message("AMI login failed: $response");
        fclose($ami_socket);
        $ami_socket = null;
        return false;
    }
}

function read_ami_event() {
    global $ami_socket;

    if (!$ami_socket) return null;

    $event = array();
    $event_text = '';

    while (($line = @fgets($ami_socket)) !== false) {
        $event_text .= $line;
        $line = trim($line);

        if ($line === '') {
            break;
        }

        if (strpos($line, ':') !== false) {
            list($key, $value) = explode(':', $line, 2);
            $event[trim($key)] = trim($value);
        }
    }

    if (empty($event)) {
        return null;
    }

    return $event;
}

function handle_trunk_unreachable($trunk_name) {
    $monitoring_config = get_config('monitoring_config');
    if (!$monitoring_config || !$monitoring_config['enabled']) {
        log_message("Monitoring is disabled");
        return;
    }

    log_message("Trunk $trunk_name is UNREACHABLE - Starting failover procedure");

    // Verificar cooldown
    if (!check_cooldown($trunk_name)) {
        log_message("Trunk $trunk_name is in cooldown period, skipping action");
        add_event_log($trunk_name, 'UNREACHABLE', '', '', 'SKIPPED (cooldown)', '', 'cooldown');
        return;
    }

    // Obtener IP del troncal
    $trunk_ip = get_trunk_ip($trunk_name);
    if (!$trunk_ip) {
        log_message("Could not determine IP for trunk $trunk_name");
        add_event_log($trunk_name, 'UNREACHABLE', 'unknown', 'N/A', 'SKIPPED (no IP)', '', 'error');
        return;
    }

    log_message("Trunk IP: $trunk_ip");

    // Delay antes de la acción
    $delay = isset($monitoring_config['delay_before_action']) ? $monitoring_config['delay_before_action'] : 10;
    log_message("Waiting $delay seconds before checking ping...");
    sleep($delay);

    // Verificar ping
    $ping_ok = ping_host($trunk_ip);
    $ping_result = $ping_ok ? 'OK' : 'FAILED';
    log_message("Ping to $trunk_ip: $ping_result");

    if (!$ping_ok) {
        // Si no hay ping, es un problema de red real, no del router
        log_message("Ping failed - network issue, not executing MikroTik commands");
        add_event_log($trunk_name, 'UNREACHABLE', $trunk_ip, $ping_result, 'SKIPPED (no ping)', '', 'network_down');
        send_email_notification(
            "MikroTik Failover Alert - Network Down",
            "Trunk: $trunk_name\nIP: $trunk_ip\nStatus: UNREACHABLE\nPing: FAILED\n\nNo action taken - network appears to be down."
        );
        return;
    }

    // Hay ping pero el troncal está unreachable - ejecutar comandos
    log_message("Ping OK but trunk unreachable - executing MikroTik commands");

    $retry_mode = isset($monitoring_config['retry_mode']) ? $monitoring_config['retry_mode'] : 'single';
    $retry_count = $retry_mode == 'multiple' ? $monitoring_config['retry_count'] : 1;
    $retry_interval = isset($monitoring_config['retry_interval']) ? $monitoring_config['retry_interval'] : 30;

    for ($i = 1; $i <= $retry_count; $i++) {
        log_message("Attempt $i of $retry_count");

        $output = execute_mikrotik_commands();

        if ($output !== false) {
            log_message("Commands executed successfully");
            add_event_log($trunk_name, 'UNREACHABLE', $trunk_ip, $ping_result, 'COMMANDS_EXECUTED', $output, 'success');
            update_cooldown($trunk_name);

            send_email_notification(
                "MikroTik Failover Alert - Commands Executed",
                "Trunk: $trunk_name\nIP: $trunk_ip\nStatus: UNREACHABLE\nPing: OK\n\nCommands executed on MikroTik router.\n\nOutput:\n$output"
            );
            break;
        } else {
            log_message("Failed to execute commands");
            add_event_log($trunk_name, 'UNREACHABLE', $trunk_ip, $ping_result, 'COMMANDS_FAILED', '', 'error');

            if ($i < $retry_count) {
                log_message("Waiting $retry_interval seconds before retry...");
                sleep($retry_interval);
            }
        }
    }
}

function handle_trunk_reachable($trunk_name) {
    log_message("Trunk $trunk_name is now REACHABLE");

    $trunk_ip = get_trunk_ip($trunk_name);
    add_event_log($trunk_name, 'REACHABLE', $trunk_ip, 'N/A', 'NONE', '', 'restored');

    send_email_notification(
        "MikroTik Failover Alert - Trunk Restored",
        "Trunk: $trunk_name\nIP: $trunk_ip\nStatus: REACHABLE\n\nThe trunk has been restored."
    );
}

// ==================== MAIN ====================

// Verificar si ya hay un daemon corriendo
if (file_exists(PID_FILE)) {
    $old_pid = file_get_contents(PID_FILE);
    if (posix_kill($old_pid, 0)) {
        echo "Daemon already running with PID $old_pid\n";
        exit(1);
    }
}

// Guardar PID
$pid = getmypid();
file_put_contents(PID_FILE, $pid);

log_message("MikroTik Failover Daemon starting (PID: $pid)");
update_daemon_status('running', $pid);

// Conectar a AMI
$ami_connected = connect_ami();
$last_reconnect_attempt = time();
$trunk_states = array(); // Para trackear estados previos

while ($running) {
    // Reconectar AMI si es necesario
    if (!$ami_socket || feof($ami_socket)) {
        if (time() - $last_reconnect_attempt > 30) {
            log_message("AMI connection lost, reconnecting...");
            if ($ami_socket) fclose($ami_socket);
            $ami_socket = null;
            $ami_connected = connect_ami();
            $last_reconnect_attempt = time();
        }
        if (!$ami_connected) {
            sleep(5);
            continue;
        }
    }

    // Verificar si el monitoreo está habilitado
    $monitoring_config = get_config('monitoring_config');
    if (!$monitoring_config || !$monitoring_config['enabled']) {
        sleep(5);
        continue;
    }

    // Leer evento AMI
    $event = read_ami_event();

    if ($event && isset($event['Event'])) {
        // Buscar eventos de estado de peer
        if ($event['Event'] == 'PeerStatus') {
            $peer = isset($event['Peer']) ? $event['Peer'] : '';
            $peer_status = isset($event['PeerStatus']) ? $event['PeerStatus'] : '';

            // Extraer nombre del peer (puede venir como SIP/nombre)
            if (preg_match('/^SIP\/(.+)$/', $peer, $matches)) {
                $trunk_name = $matches[1];
            } else {
                $trunk_name = $peer;
            }

            // Verificar si este trunk está siendo monitoreado
            $monitored_trunks = get_monitored_trunks();

            if (in_array($trunk_name, $monitored_trunks)) {
                $prev_state = isset($trunk_states[$trunk_name]) ? $trunk_states[$trunk_name] : '';

                log_message("PeerStatus event: $trunk_name -> $peer_status (prev: $prev_state)");

                if ($peer_status == 'Unreachable' && $prev_state != 'Unreachable') {
                    handle_trunk_unreachable($trunk_name);
                } elseif ($peer_status == 'Reachable' && $prev_state == 'Unreachable') {
                    handle_trunk_reachable($trunk_name);
                }

                $trunk_states[$trunk_name] = $peer_status;
            }
        }
    }

    // Pequeña pausa para no saturar CPU
    usleep(100000); // 100ms
}

// Cleanup
log_message("Daemon shutting down");
if ($ami_socket) {
    fwrite($ami_socket, "Action: Logoff\r\n\r\n");
    fclose($ami_socket);
}
update_daemon_status('stopped', null);
unlink(PID_FILE);
log_message("Daemon stopped");
?>
