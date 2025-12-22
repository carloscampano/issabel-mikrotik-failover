<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | MikroTik Failover Module for Issabel                                 |
  +----------------------------------------------------------------------+
*/

class paloSantoMikrotikFailover {
    private $_DB;
    public $errMsg;

    function __construct(&$pDB)
    {
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);
            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
            }
        }
    }

    // ==================== MIKROTIK CONFIG ====================

    function getMikrotikConfig()
    {
        $query = "SELECT * FROM mikrotik_config WHERE id = 1";
        $result = $this->_DB->getFirstRowQuery($query, true);
        if ($result === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function saveMikrotikConfig($ip, $port, $user, $password)
    {
        $existing = $this->getMikrotikConfig();
        if ($existing) {
            $query = "UPDATE mikrotik_config SET ip = ?, port = ?, user = ?, password = ? WHERE id = 1";
        } else {
            $query = "INSERT INTO mikrotik_config (ip, port, user, password) VALUES (?, ?, ?, ?)";
        }
        $result = $this->_DB->genQuery($query, array($ip, $port, $user, $password));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    // ==================== SMTP CONFIG ====================

    function getSmtpConfig()
    {
        $query = "SELECT * FROM smtp_config WHERE id = 1";
        $result = $this->_DB->getFirstRowQuery($query, true);
        if ($result === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function saveSmtpConfig($server, $port, $user, $password, $from_email, $to_email, $enabled)
    {
        $existing = $this->getSmtpConfig();
        if ($existing) {
            $query = "UPDATE smtp_config SET server = ?, port = ?, user = ?, password = ?, from_email = ?, to_email = ?, enabled = ? WHERE id = 1";
        } else {
            $query = "INSERT INTO smtp_config (server, port, user, password, from_email, to_email, enabled) VALUES (?, ?, ?, ?, ?, ?, ?)";
        }
        $result = $this->_DB->genQuery($query, array($server, $port, $user, $password, $from_email, $to_email, $enabled));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    // ==================== MONITORING CONFIG ====================

    function getMonitoringConfig()
    {
        $query = "SELECT * FROM monitoring_config WHERE id = 1";
        $result = $this->_DB->getFirstRowQuery($query, true);
        if ($result === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function saveMonitoringConfig($delay_before_action, $retry_mode, $retry_count, $retry_interval, $cooldown_period, $enabled)
    {
        $existing = $this->getMonitoringConfig();
        if ($existing) {
            $query = "UPDATE monitoring_config SET delay_before_action = ?, retry_mode = ?, retry_count = ?, retry_interval = ?, cooldown_period = ?, enabled = ? WHERE id = 1";
        } else {
            $query = "INSERT INTO monitoring_config (delay_before_action, retry_mode, retry_count, retry_interval, cooldown_period, enabled) VALUES (?, ?, ?, ?, ?, ?)";
        }
        $result = $this->_DB->genQuery($query, array($delay_before_action, $retry_mode, $retry_count, $retry_interval, $cooldown_period, $enabled));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    // ==================== COMMANDS ====================

    function getCommands()
    {
        $query = "SELECT * FROM commands ORDER BY order_num ASC";
        $result = $this->_DB->fetchTable($query, true);
        if ($result === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getCommandById($id)
    {
        $query = "SELECT * FROM commands WHERE id = ?";
        $result = $this->_DB->getFirstRowQuery($query, true, array($id));
        if ($result === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function addCommand($command, $description, $order_num)
    {
        $query = "INSERT INTO commands (command, description, order_num, enabled) VALUES (?, ?, ?, 1)";
        $result = $this->_DB->genQuery($query, array($command, $description, $order_num));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function updateCommand($id, $command, $description, $order_num, $enabled)
    {
        $query = "UPDATE commands SET command = ?, description = ?, order_num = ?, enabled = ? WHERE id = ?";
        $result = $this->_DB->genQuery($query, array($command, $description, $order_num, $enabled, $id));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function deleteCommand($id)
    {
        $query = "DELETE FROM commands WHERE id = ?";
        $result = $this->_DB->genQuery($query, array($id));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    // ==================== TRUNKS ====================

    function getMonitoredTrunks()
    {
        $query = "SELECT * FROM monitored_trunks ORDER BY trunk_name ASC";
        $result = $this->_DB->fetchTable($query, true);
        if ($result === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getTrunkById($id)
    {
        $query = "SELECT * FROM monitored_trunks WHERE id = ?";
        $result = $this->_DB->getFirstRowQuery($query, true, array($id));
        if ($result === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getTrunkByName($trunk_name)
    {
        $query = "SELECT * FROM monitored_trunks WHERE trunk_name = ?";
        $result = $this->_DB->getFirstRowQuery($query, true, array($trunk_name));
        if ($result === FALSE) {
            return null;
        }
        return $result;
    }

    function addTrunk($trunk_name, $enabled = 1)
    {
        $query = "INSERT INTO monitored_trunks (trunk_name, enabled) VALUES (?, ?)";
        $result = $this->_DB->genQuery($query, array($trunk_name, $enabled));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function updateTrunk($id, $trunk_name, $enabled)
    {
        $query = "UPDATE monitored_trunks SET trunk_name = ?, enabled = ? WHERE id = ?";
        $result = $this->_DB->genQuery($query, array($trunk_name, $enabled, $id));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function deleteTrunk($id)
    {
        $query = "DELETE FROM monitored_trunks WHERE id = ?";
        $result = $this->_DB->genQuery($query, array($id));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    // ==================== EVENT LOG ====================

    function getNumEventLogs($filter_field = null, $filter_value = null)
    {
        $where = "";
        $arrParam = null;
        if (!empty($filter_field) && !empty($filter_value)) {
            $where = "WHERE $filter_field LIKE ?";
            $arrParam = array("%$filter_value%");
        }
        $query = "SELECT COUNT(*) FROM event_log $where";
        $result = $this->_DB->getFirstRowQuery($query, false, $arrParam);
        if ($result === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getEventLogs($limit, $offset, $filter_field = null, $filter_value = null)
    {
        $where = "";
        $arrParam = null;
        if (!empty($filter_field) && !empty($filter_value)) {
            $where = "WHERE $filter_field LIKE ?";
            $arrParam = array("%$filter_value%");
        }
        $query = "SELECT * FROM event_log $where ORDER BY timestamp DESC LIMIT $limit OFFSET $offset";
        $result = $this->_DB->fetchTable($query, true, $arrParam);
        if ($result === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function addEventLog($trunk_name, $event_type, $trunk_ip, $ping_result, $action_taken, $command_output, $status)
    {
        $query = "INSERT INTO event_log (trunk_name, event_type, trunk_ip, ping_result, action_taken, command_output, status, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'))";
        $result = $this->_DB->genQuery($query, array($trunk_name, $event_type, $trunk_ip, $ping_result, $action_taken, $command_output, $status));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function clearEventLogs()
    {
        $query = "DELETE FROM event_log";
        $result = $this->_DB->genQuery($query);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    // ==================== COOLDOWN TRACKING ====================

    function getLastActionTime($trunk_name)
    {
        $query = "SELECT last_action_time FROM trunk_cooldown WHERE trunk_name = ?";
        $result = $this->_DB->getFirstRowQuery($query, true, array($trunk_name));
        if ($result === FALSE || empty($result)) {
            return null;
        }
        return $result['last_action_time'];
    }

    function updateLastActionTime($trunk_name)
    {
        $existing = $this->getLastActionTime($trunk_name);
        if ($existing !== null) {
            $query = "UPDATE trunk_cooldown SET last_action_time = datetime('now', 'localtime') WHERE trunk_name = ?";
        } else {
            $query = "INSERT INTO trunk_cooldown (trunk_name, last_action_time) VALUES (?, datetime('now', 'localtime'))";
        }
        $result = $this->_DB->genQuery($query, array($trunk_name));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    // ==================== DAEMON STATUS ====================

    function getDaemonStatus()
    {
        $query = "SELECT * FROM daemon_status WHERE id = 1";
        $result = $this->_DB->getFirstRowQuery($query, true);
        if ($result === FALSE) {
            return null;
        }
        return $result;
    }

    function updateDaemonStatus($status, $pid = null)
    {
        $existing = $this->getDaemonStatus();
        if ($existing) {
            $query = "UPDATE daemon_status SET status = ?, pid = ?, last_update = datetime('now', 'localtime') WHERE id = 1";
        } else {
            $query = "INSERT INTO daemon_status (status, pid, last_update) VALUES (?, ?, datetime('now', 'localtime'))";
        }
        $result = $this->_DB->genQuery($query, array($status, $pid));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    // ==================== UTILITY FUNCTIONS ====================

    function getSipTrunksFromAsterisk()
    {
        $trunks = array();
        $output = array();
        exec("/usr/sbin/asterisk -rx 'sip show peers' 2>/dev/null", $output);

        foreach ($output as $line) {
            // Parse lines that look like trunk entries (not headers or summary)
            if (preg_match('/^(\S+)\s+(\d+\.\d+\.\d+\.\d+|\(Unspecified\))\s+/', $line, $matches)) {
                $name = $matches[1];
                // Skip short numeric entries (extensions typically 3-5 digits)
                // Keep longer numeric entries (trunks with phone numbers) and non-numeric names
                $baseName = explode('/', $name)[0];
                if (preg_match('/^\d+$/', $baseName)) {
                    // It's numeric - only include if longer than 6 digits (likely a trunk)
                    if (strlen($baseName) > 6) {
                        $trunks[] = $name;
                    }
                } else {
                    // Non-numeric name - include it (traditional trunk name)
                    $trunks[] = $name;
                }
            }
        }
        return $trunks;
    }

    function testMikrotikConnection($ip, $port, $user, $password)
    {
        // Check if ssh2 extension is loaded
        if (!function_exists('ssh2_connect')) {
            return array('success' => false, 'message' => 'Extension ssh2 no esta instalada');
        }

        // Validate parameters
        if (empty($ip) || empty($port) || empty($user)) {
            return array('success' => false, 'message' => 'Parametros incompletos');
        }

        // Attempt connection with timeout
        $connection = @ssh2_connect($ip, intval($port));
        if (!$connection) {
            return array('success' => false, 'message' => 'No se pudo conectar al host ' . $ip . ':' . $port);
        }

        if (!@ssh2_auth_password($connection, $user, $password)) {
            return array('success' => false, 'message' => 'Autenticacion fallida para usuario ' . $user);
        }

        $stream = @ssh2_exec($connection, '/system identity print');
        if (!$stream) {
            return array('success' => false, 'message' => 'No se pudo ejecutar comando');
        }

        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);
        fclose($stream);

        // Clean output
        $output = trim($output);

        return array('success' => true, 'message' => 'Conexion exitosa', 'output' => $output);
    }

    function testSmtpConnection($server, $port, $user, $password, $from_email, $to_email)
    {
        // Validate parameters
        if (empty($server) || empty($port) || empty($from_email) || empty($to_email)) {
            return array('success' => false, 'message' => 'Parametros incompletos. Configure servidor, puerto y correos.');
        }

        // Try to find PHPMailer in different locations
        $phpmailer_paths = array(
            '/usr/share/php/PHPMailer/class.phpmailer.php',
            '/usr/share/PHPMailer/class.phpmailer.php',
            '/var/www/html/libs/phpmailer/class.phpmailer.php',
            '/var/www/html/libs/PHPMailer/class.phpmailer.php',
            'libs/phpmailer/class.phpmailer.php',
            dirname(__FILE__) . '/phpmailer/class.phpmailer.php'
        );

        $phpmailer_found = false;
        foreach ($phpmailer_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                $phpmailer_found = true;
                break;
            }
        }

        if (!$phpmailer_found) {
            return array('success' => false, 'message' => 'PHPMailer no encontrado. Verifique la instalacion.');
        }

        if (!class_exists('PHPMailer')) {
            return array('success' => false, 'message' => 'Clase PHPMailer no disponible.');
        }

        try {
            $mail = new PHPMailer(true); // Enable exceptions
            $mail->IsSMTP();
            $mail->Host = $server;
            $mail->Port = intval($port);
            $mail->Timeout = 15; // 15 second timeout

            // Authentication
            if (!empty($user) && !empty($password)) {
                $mail->SMTPAuth = true;
                $mail->Username = $user;
                $mail->Password = $password;
            } else {
                $mail->SMTPAuth = false;
            }

            // Set encryption based on port
            if ($port == 465) {
                $mail->SMTPSecure = 'ssl';
            } elseif ($port == 587) {
                $mail->SMTPSecure = 'tls';
            }
            // Port 25 typically doesn't use encryption

            // For servers with self-signed or problematic certificates
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->SMTPDebug = 0; // Disable debug output

            $mail->SetFrom($from_email, 'MikroTik Failover');
            $mail->AddAddress($to_email);
            $mail->Subject = 'Test de conexion SMTP - MikroTik Failover';
            $mail->Body = 'Este es un correo de prueba del modulo MikroTik Failover de Issabel. Fecha: ' . date('Y-m-d H:i:s');
            $mail->CharSet = 'UTF-8';

            if ($mail->Send()) {
                return array('success' => true, 'message' => 'Correo de prueba enviado correctamente a ' . $to_email);
            } else {
                return array('success' => false, 'message' => 'Error enviando: ' . $mail->ErrorInfo);
            }
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error SMTP: ' . $e->getMessage());
        }
    }

    // ==================== REAL-TIME TRUNK STATUS ====================

    /**
     * Get real-time status of all SIP trunks from Asterisk
     * Returns array with trunk name, IP, status, and response time
     */
    function getTrunkStatusRealtime()
    {
        $trunks = array();
        $output = array();
        exec("/usr/sbin/asterisk -rx 'sip show peers' 2>/dev/null", $output);

        foreach ($output as $line) {
            // Parse format: Name/username  Host   Dyn Forcerport Comedia  ACL Port Status  Description
            // Example: 56413290350/56413290350   190.196.211.162    No  Yes    5060  OK (11 ms)
            // More flexible regex to handle variable spacing
            if (preg_match('/^(\S+)\s+(\d+\.\d+\.\d+\.\d+|\(Unspecified\))\s+.*?\s+(\d+)\s+(OK\s*\(\d+\s*ms\)|UNREACHABLE|LAGGED|UNKNOWN|Unmonitored)/', $line, $matches)) {
                $name = $matches[1];
                $ip = $matches[2];
                $port = $matches[3];
                $status_raw = trim($matches[4]);

                // Skip short numeric entries (extensions typically 3-5 digits)
                // Keep longer numeric entries (trunks) and non-numeric names
                $baseName = explode('/', $name)[0];
                if (preg_match('/^\d+$/', $baseName) && strlen($baseName) <= 6) {
                    continue;
                }

                // Parse status
                $status = 'Unknown';
                $latency = '';
                if (preg_match('/^OK\s*\((\d+)\s*ms\)/', $status_raw, $sm)) {
                    $status = 'OK';
                    $latency = $sm[1] . ' ms';
                } elseif (stripos($status_raw, 'UNREACHABLE') !== false) {
                    $status = 'UNREACHABLE';
                } elseif (stripos($status_raw, 'LAGGED') !== false) {
                    $status = 'LAGGED';
                    if (preg_match('/\((\d+)\s*ms\)/', $status_raw, $sm)) {
                        $latency = $sm[1] . ' ms';
                    }
                } elseif (stripos($status_raw, 'UNKNOWN') !== false) {
                    $status = 'UNKNOWN';
                } elseif (stripos($status_raw, 'Unmonitored') !== false) {
                    $status = 'Unmonitored';
                }

                $trunks[] = array(
                    'name' => $name,
                    'ip' => $ip,
                    'port' => $port,
                    'status' => $status,
                    'latency' => $latency,
                    'status_raw' => $status_raw
                );
            }
        }
        return $trunks;
    }

    /**
     * Get status of only the monitored trunks
     */
    function getMonitoredTrunksStatus()
    {
        $monitored = $this->getMonitoredTrunks();
        $all_status = $this->getTrunkStatusRealtime();

        $result = array();
        foreach ($monitored as $trunk) {
            $trunk_name = $trunk['trunk_name'];
            $found = false;

            foreach ($all_status as $status) {
                if ($status['name'] == $trunk_name) {
                    $result[] = array(
                        'id' => $trunk['id'],
                        'name' => $trunk_name,
                        'enabled' => $trunk['enabled'],
                        'ip' => $status['ip'],
                        'port' => $status['port'],
                        'status' => $status['status'],
                        'latency' => $status['latency'],
                        'status_raw' => $status['status_raw']
                    );
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $result[] = array(
                    'id' => $trunk['id'],
                    'name' => $trunk_name,
                    'enabled' => $trunk['enabled'],
                    'ip' => 'N/A',
                    'port' => '',
                    'status' => 'NOT_FOUND',
                    'latency' => '',
                    'status_raw' => 'Trunk not found in Asterisk'
                );
            }
        }

        return $result;
    }

    /**
     * Get local PBX IP address (primary interface)
     */
    function getLocalPbxIp()
    {
        // Try to get IP from hostname
        $output = array();
        exec("hostname -I 2>/dev/null | awk '{print $1}'", $output);
        if (!empty($output[0])) {
            return trim($output[0]);
        }

        // Fallback: get from default route interface
        $output = array();
        exec("ip route get 8.8.8.8 2>/dev/null | grep -oP 'src \\K[0-9.]+'", $output);
        if (!empty($output[0])) {
            return trim($output[0]);
        }

        return null;
    }

    /**
     * Get trunk IP from Asterisk
     */
    function getTrunkIp($trunk_name)
    {
        // Remove /username suffix if present
        $peer_name = explode('/', $trunk_name)[0];

        $output = array();
        exec("/usr/sbin/asterisk -rx 'sip show peer " . escapeshellarg($peer_name) . "' 2>/dev/null", $output);

        $tohost = null;
        foreach ($output as $line) {
            // Addr->IP may include port: 190.196.211.162:5060
            if (preg_match('/^\s*Addr->IP\s*:\s*(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
                return $matches[1];
            }
            // ToHost can be IP or hostname
            if (preg_match('/^\s*ToHost\s*:\s*(\S+)/i', $line, $matches)) {
                $tohost = $matches[1];
            }
        }

        // If we found a ToHost, resolve it if it's a hostname
        if ($tohost) {
            // Check if it's already an IP
            if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $tohost)) {
                return $tohost;
            }
            // Try to resolve hostname
            $ip = gethostbyname($tohost);
            if ($ip !== $tohost) {
                return $ip;
            }
            // Return hostname if can't resolve (better than null)
            return $tohost;
        }

        // Try from sip show peers
        $output = array();
        exec("/usr/sbin/asterisk -rx 'sip show peers' 2>/dev/null", $output);
        foreach ($output as $line) {
            if (preg_match('/^' . preg_quote($trunk_name, '/') . '\s+(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
                return $matches[1];
            }
            // Also try with just the peer name
            if (preg_match('/^' . preg_quote($peer_name, '/') . '\/\S+\s+(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Generate MikroTik script content to clear connection tracking for trunk IPs
     */
    function generateMikrotikScriptContent()
    {
        $ips = array();

        // Get local PBX IP
        $localIp = $this->getLocalPbxIp();
        if ($localIp) {
            $ips[] = $localIp;
        }

        // Get IPs of all monitored trunks
        $trunks = $this->getMonitoredTrunks();
        foreach ($trunks as $trunk) {
            $ip = $this->getTrunkIp($trunk['trunk_name']);
            if ($ip && !in_array($ip, $ips)) {
                $ips[] = $ip;
            }
        }

        if (empty($ips)) {
            return null;
        }

        // Build the IP array string for MikroTik
        $ipArrayStr = '{"' . implode('";"', $ips) . '"}';

        // Generate the script
        $script = ':local ips ' . $ipArrayStr . '

:foreach ip in=$ips do={
    :log warning "=========================================="
    :log warning ("Procesando conexiones para IP: " . $ip)
    :log warning "=========================================="

    :log info ("--- Conexiones donde " . $ip . " es ORIGEN ---")
    :local countSrc 0
    :foreach conn in=[/ip/firewall/connection find src-address~$ip] do={
        :do {
            :local connData [/ip/firewall/connection get $conn]
            :local srcAddr ($connData->"src-address")
            :local dstAddr ($connData->"dst-address")
            :local protocol ($connData->"protocol")
            :local connState ($connData->"connection-state")
            :log info ("ELIMINANDO - SRC: " . $srcAddr . " -> DST: " . $dstAddr . " | Proto: " . $protocol . " | Estado: " . $connState)
            /ip/firewall/connection remove $conn
            :set countSrc ($countSrc + 1)
        } on-error={
            :log info ("Conexion ya no existe (expiro o fue eliminada)")
        }
    }
    :if ($countSrc > 0) do={
        :log warning ("Eliminadas " . $countSrc . " conexiones como origen")
    } else={
        :log info "No se encontraron conexiones como origen"
    }

    :log info ("--- Conexiones donde " . $ip . " es DESTINO ---")
    :local countDst 0
    :foreach conn in=[/ip/firewall/connection find dst-address~$ip] do={
        :do {
            :local connData [/ip/firewall/connection get $conn]
            :local srcAddr ($connData->"src-address")
            :local dstAddr ($connData->"dst-address")
            :local protocol ($connData->"protocol")
            :local connState ($connData->"connection-state")
            :log info ("ELIMINANDO - SRC: " . $srcAddr . " -> DST: " . $dstAddr . " | Proto: " . $protocol . " | Estado: " . $connState)
            /ip/firewall/connection remove $conn
            :set countDst ($countDst + 1)
        } on-error={
            :log info ("Conexion ya no existe (expiro o fue eliminada)")
        }
    }
    :if ($countDst > 0) do={
        :log warning ("Eliminadas " . $countDst . " conexiones como destino")
    } else={
        :log info "No se encontraron conexiones como destino"
    }
}

:log warning "=========================================="
:log warning "Proceso de limpieza finalizado"
:log warning "=========================================="';

        return array(
            'script' => $script,
            'ips' => $ips
        );
    }

    /**
     * Upload script to MikroTik using SFTP and create persistent script del_conn
     * 1. Uploads script content as .rsc file via SFTP
     * 2. Creates/updates del_conn script in MikroTik from file content
     */
    function uploadScriptToMikrotik()
    {
        $mikrotikConfig = $this->getMikrotikConfig();
        if (!$mikrotikConfig) {
            return array('success' => false, 'message' => 'MikroTik not configured');
        }

        $scriptData = $this->generateMikrotikScriptContent();
        if ($scriptData === null) {
            return array('success' => false, 'message' => 'No IPs found. Add monitored trunks first.');
        }

        $ip = $mikrotikConfig['ip'];
        $port = $mikrotikConfig['port'];
        $user = $mikrotikConfig['user'];
        $password = $mikrotikConfig['password'];

        // Check if ssh2 extension is loaded
        if (!function_exists('ssh2_connect')) {
            return array('success' => false, 'message' => 'SSH2 extension not installed');
        }

        // Connect to MikroTik
        $connection = @ssh2_connect($ip, intval($port));
        if (!$connection) {
            return array('success' => false, 'message' => 'Cannot connect to MikroTik at ' . $ip . ':' . $port);
        }

        if (!@ssh2_auth_password($connection, $user, $password)) {
            return array('success' => false, 'message' => 'Authentication failed');
        }

        // Initialize SFTP
        $sftp = @ssh2_sftp($connection);
        if (!$sftp) {
            return array('success' => false, 'message' => 'Could not initialize SFTP subsystem');
        }

        // Upload script content as .rsc file
        $scriptContent = $scriptData['script'];
        $remoteFile = "ssh2.sftp://" . intval($sftp) . "/del_conn_source.rsc";

        $result = @file_put_contents($remoteFile, $scriptContent);
        if ($result === false) {
            return array('success' => false, 'message' => 'Failed to upload script file to MikroTik');
        }

        // Wait for file to be written
        sleep(1);

        // Remove existing del_conn script if exists
        $stream = @ssh2_exec($connection, '/system script remove [find name=del_conn]');
        if ($stream) {
            stream_set_blocking($stream, true);
            stream_get_contents($stream);
            fclose($stream);
        }
        usleep(500000);

        // Create del_conn script from file content
        // RouterOS 7 syntax: read file content and use as script source
        $stream = @ssh2_exec($connection, '/system script add name=del_conn source=[/file get del_conn_source.rsc contents]');
        if (!$stream) {
            return array('success' => false, 'message' => 'Failed to create script on MikroTik');
        }
        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);
        fclose($stream);

        // Wait and verify script was created
        sleep(1);
        $stream = @ssh2_exec($connection, '/system script print where name=del_conn');
        if ($stream) {
            stream_set_blocking($stream, true);
            $verifyOutput = stream_get_contents($stream);
            fclose($stream);

            if (strpos($verifyOutput, 'del_conn') === false) {
                return array('success' => false, 'message' => 'Script del_conn creation could not be verified. Output: ' . $output);
            }
        }

        return array(
            'success' => true,
            'message' => 'Script del_conn created successfully',
            'ips' => $scriptData['ips']
        );
    }

    /**
     * Get daemon status with process verification
     */
    function getDaemonStatusRealtime()
    {
        $db_status = $this->getDaemonStatus();

        // Verify if process is actually running
        $pid_file = '/var/run/mikrotik_failover.pid';
        $is_running = false;
        $pid = null;

        if (file_exists($pid_file)) {
            $pid = trim(file_get_contents($pid_file));
            if (!empty($pid) && file_exists("/proc/$pid")) {
                $is_running = true;
            }
        }

        return array(
            'status' => $is_running ? 'running' : 'stopped',
            'pid' => $is_running ? $pid : null,
            'last_update' => isset($db_status['last_update']) ? $db_status['last_update'] : null
        );
    }
}
?>
