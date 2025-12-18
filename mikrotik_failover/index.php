<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | MikroTik Failover Module for Issabel                                 |
  +----------------------------------------------------------------------+
*/

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoMikrotikFailover.class.php";

    load_language_module($module_name);

    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf, $arrConfModule);

    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir = (isset($arrConf['templates_dir'])) ? $arrConf['templates_dir'] : 'themes';
    $local_templates_dir = "$base_dir/modules/$module_name/" . $templates_dir . '/' . $arrConf['theme'];

    $pDB = new paloDB($arrConf['dsn_conn_database']);
    $pMikrotik = new paloSantoMikrotikFailover($pDB);

    $action = getAction();
    $content = "";

    // Handle AJAX requests for real-time status
    if ($action == 'get_status_ajax') {
        header('Content-Type: application/json');
        $response = array(
            'trunks' => $pMikrotik->getMonitoredTrunksStatus(),
            'daemon' => $pMikrotik->getDaemonStatusRealtime(),
            'timestamp' => date('Y-m-d H:i:s')
        );
        echo json_encode($response);
        exit;
    }

    switch ($action) {
        // MikroTik Config
        case 'save_mikrotik':
            $content = saveMikrotikConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
        case 'test_mikrotik':
            $content = testMikrotikConnection($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;

        // SMTP Config
        case 'save_smtp':
            $content = saveSmtpConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
        case 'test_smtp':
            $content = testSmtpConnection($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;

        // Monitoring Config
        case 'save_monitoring':
            $content = saveMonitoringConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;

        // Commands
        case 'add_command':
            $content = addCommand($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
        case 'edit_command':
            $content = editCommand($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
        case 'delete_command':
            $content = deleteCommand($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;

        // Trunks
        case 'add_trunk':
            $content = addTrunk($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
        case 'delete_trunk':
            $content = deleteTrunk($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
        case 'toggle_trunk':
            $content = toggleTrunk($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;

        // Event Log
        case 'view_logs':
            $content = viewEventLogs($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
        case 'clear_logs':
            $content = clearEventLogs($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;

        // Daemon Control
        case 'start_daemon':
            $content = startDaemon($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
        case 'stop_daemon':
            $content = stopDaemon($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
        case 'toggle_autostart':
            $content = toggleAutostart($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;

        // Default - Main config view
        default:
            $content = showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
    }
    return $content;
}

function showMainConfig($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    // Get all configurations
    $mikrotikConfig = $pMikrotik->getMikrotikConfig();
    $smtpConfig = $pMikrotik->getSmtpConfig();
    $monitoringConfig = $pMikrotik->getMonitoringConfig();
    $commands = $pMikrotik->getCommands();
    $monitoredTrunks = $pMikrotik->getMonitoredTrunks();
    $daemonStatus = $pMikrotik->getDaemonStatusRealtime();

    // Check if daemon is enabled on boot
    $daemonEnabled = trim(shell_exec('systemctl is-enabled mikrotik-failover 2>/dev/null')) === 'enabled';

    // Get available trunks from Asterisk
    $availableTrunks = $pMikrotik->getSipTrunksFromAsterisk();

    // Get real-time trunk status
    $trunkStatus = $pMikrotik->getMonitoredTrunksStatus();

    // Assign to Smarty
    $smarty->assign('MODULE_NAME', $module_name);
    $smarty->assign('MIKROTIK_CONFIG', $mikrotikConfig ? $mikrotikConfig : array());
    $smarty->assign('TRUNK_STATUS', $trunkStatus);
    $smarty->assign('SMTP_CONFIG', $smtpConfig ? $smtpConfig : array());
    $smarty->assign('MONITORING_CONFIG', $monitoringConfig ? $monitoringConfig : array());
    $smarty->assign('COMMANDS', $commands);
    $smarty->assign('MONITORED_TRUNKS', $monitoredTrunks);
    $smarty->assign('AVAILABLE_TRUNKS', $availableTrunks);
    $smarty->assign('DAEMON_STATUS', $daemonStatus);
    $smarty->assign('DAEMON_ENABLED', $daemonEnabled);

    // Labels
    $smarty->assign('LBL_MIKROTIK_CONFIG', _tr('MikroTik Configuration'));
    $smarty->assign('LBL_IP', _tr('IP Address'));
    $smarty->assign('LBL_PORT', _tr('SSH Port'));
    $smarty->assign('LBL_USER', _tr('Username'));
    $smarty->assign('LBL_PASSWORD', _tr('Password'));
    $smarty->assign('LBL_SAVE', _tr('Save'));
    $smarty->assign('LBL_TEST', _tr('Test Connection'));

    $smarty->assign('LBL_SMTP_CONFIG', _tr('SMTP Configuration'));
    $smarty->assign('LBL_SERVER', _tr('SMTP Server'));
    $smarty->assign('LBL_SMTP_PORT', _tr('SMTP Port'));
    $smarty->assign('LBL_FROM_EMAIL', _tr('From Email'));
    $smarty->assign('LBL_TO_EMAIL', _tr('To Email'));
    $smarty->assign('LBL_ENABLED', _tr('Enabled'));
    $smarty->assign('LBL_TEST_EMAIL', _tr('Send Test Email'));

    $smarty->assign('LBL_MONITORING_CONFIG', _tr('Monitoring Configuration'));
    $smarty->assign('LBL_DELAY_BEFORE_ACTION', _tr('Delay before action (seconds)'));
    $smarty->assign('LBL_RETRY_MODE', _tr('Retry Mode'));
    $smarty->assign('LBL_SINGLE', _tr('Single'));
    $smarty->assign('LBL_MULTIPLE', _tr('Multiple'));
    $smarty->assign('LBL_RETRY_COUNT', _tr('Retry Count'));
    $smarty->assign('LBL_RETRY_INTERVAL', _tr('Retry Interval (seconds)'));
    $smarty->assign('LBL_COOLDOWN', _tr('Cooldown Period (seconds)'));
    $smarty->assign('LBL_AUTOSTART', _tr('Auto-start on boot'));

    $smarty->assign('LBL_COMMANDS', _tr('Commands to Execute'));
    $smarty->assign('LBL_COMMAND', _tr('Command'));
    $smarty->assign('LBL_DESCRIPTION', _tr('Description'));
    $smarty->assign('LBL_ORDER', _tr('Order'));
    $smarty->assign('LBL_ADD', _tr('Add'));
    $smarty->assign('LBL_EDIT', _tr('Edit'));
    $smarty->assign('LBL_DELETE', _tr('Delete'));
    $smarty->assign('LBL_ACTIONS', _tr('Actions'));

    $smarty->assign('LBL_TRUNKS', _tr('Monitored Trunks'));
    $smarty->assign('LBL_TRUNK_NAME', _tr('Trunk Name'));
    $smarty->assign('LBL_SELECT_TRUNK', _tr('Select Trunk'));
    $smarty->assign('LBL_ADD_TRUNK', _tr('Add Trunk'));
    $smarty->assign('LBL_STATUS', _tr('Status'));

    $smarty->assign('LBL_DAEMON', _tr('Monitoring Daemon'));
    $smarty->assign('LBL_DAEMON_STATUS', _tr('Daemon Status'));
    $smarty->assign('LBL_START', _tr('Start'));
    $smarty->assign('LBL_STOP', _tr('Stop'));
    $smarty->assign('LBL_RUNNING', _tr('Running'));
    $smarty->assign('LBL_STOPPED', _tr('Stopped'));

    $smarty->assign('LBL_EVENT_LOG', _tr('Event Log'));
    $smarty->assign('LBL_VIEW_LOGS', _tr('View Logs'));
    $smarty->assign('LBL_CLEAR_LOGS', _tr('Clear Logs'));

    $smarty->assign('icon', "modules/$module_name/images/mikrotik_failover.png");

    return $smarty->fetch("$local_templates_dir/main.tpl");
}

function saveMikrotikConfig($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $ip = getParameter('mikrotik_ip');
    $port = getParameter('mikrotik_port');
    $user = getParameter('mikrotik_user');
    $password = getParameter('mikrotik_password');

    if ($pMikrotik->saveMikrotikConfig($ip, $port, $user, $password)) {
        $smarty->assign('mb_message', _tr('MikroTik configuration saved successfully'));
    } else {
        $smarty->assign('mb_message', _tr('Error saving MikroTik configuration') . ': ' . $pMikrotik->errMsg);
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function testMikrotikConnection($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $ip = getParameter('mikrotik_ip');
    $port = getParameter('mikrotik_port');
    $user = getParameter('mikrotik_user');
    $password = getParameter('mikrotik_password');

    // Validate inputs
    if (empty($ip) || empty($port) || empty($user)) {
        $smarty->assign('mb_message', _tr('Please fill all required fields'));
        return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
    }

    $result = $pMikrotik->testMikrotikConnection($ip, $port, $user, $password);

    if ($result && isset($result['success'])) {
        if ($result['success']) {
            $smarty->assign('mb_message', _tr('Connection successful') . ': ' . $result['output']);
        } else {
            $smarty->assign('mb_message', _tr('Connection failed') . ': ' . $result['message']);
        }
    } else {
        $smarty->assign('mb_message', _tr('Error testing connection'));
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function saveSmtpConfig($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $server = getParameter('smtp_server');
    $port = getParameter('smtp_port');
    $user = getParameter('smtp_user');
    $password = getParameter('smtp_password');
    $from_email = getParameter('smtp_from');
    $to_email = getParameter('smtp_to');
    $enabled = getParameter('smtp_enabled') ? 1 : 0;

    if ($pMikrotik->saveSmtpConfig($server, $port, $user, $password, $from_email, $to_email, $enabled)) {
        $smarty->assign('mb_message', _tr('SMTP configuration saved successfully'));
    } else {
        $smarty->assign('mb_message', _tr('Error saving SMTP configuration') . ': ' . $pMikrotik->errMsg);
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function testSmtpConnection($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $smtpConfig = $pMikrotik->getSmtpConfig();

    if (!$smtpConfig) {
        $smarty->assign('mb_message', _tr('Please save SMTP configuration first'));
        return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
    }

    $result = $pMikrotik->testSmtpConnection(
        $smtpConfig['server'],
        $smtpConfig['port'],
        $smtpConfig['user'],
        $smtpConfig['password'],
        $smtpConfig['from_email'],
        $smtpConfig['to_email']
    );

    if ($result['success']) {
        $smarty->assign('mb_message', $result['message']);
    } else {
        $smarty->assign('mb_message', $result['message']);
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function saveMonitoringConfig($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $delay = getParameter('delay_before_action');
    $retry_mode = getParameter('retry_mode');
    $retry_count = getParameter('retry_count');
    $retry_interval = getParameter('retry_interval');
    $cooldown = getParameter('cooldown_period');
    $enabled = getParameter('monitoring_enabled') ? 1 : 0;

    if ($pMikrotik->saveMonitoringConfig($delay, $retry_mode, $retry_count, $retry_interval, $cooldown, $enabled)) {
        $smarty->assign('mb_message', _tr('Monitoring configuration saved successfully'));
    } else {
        $smarty->assign('mb_message', _tr('Error saving monitoring configuration') . ': ' . $pMikrotik->errMsg);
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function addCommand($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $command = getParameter('new_command');
    $description = getParameter('new_command_desc');
    $order = getParameter('new_command_order');

    if (empty($command)) {
        $smarty->assign('mb_message', _tr('Command cannot be empty'));
    } elseif ($pMikrotik->addCommand($command, $description, $order)) {
        $smarty->assign('mb_message', _tr('Command added successfully'));
    } else {
        $smarty->assign('mb_message', _tr('Error adding command') . ': ' . $pMikrotik->errMsg);
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function editCommand($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $id = getParameter('command_id');
    $command = getParameter('edit_command');
    $description = getParameter('edit_command_desc');
    $order = getParameter('edit_command_order');
    $enabled = getParameter('edit_command_enabled') ? 1 : 0;

    if ($pMikrotik->updateCommand($id, $command, $description, $order, $enabled)) {
        $smarty->assign('mb_message', _tr('Command updated successfully'));
    } else {
        $smarty->assign('mb_message', _tr('Error updating command') . ': ' . $pMikrotik->errMsg);
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function deleteCommand($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $id = getParameter('command_id');

    if ($pMikrotik->deleteCommand($id)) {
        $smarty->assign('mb_message', _tr('Command deleted successfully'));
    } else {
        $smarty->assign('mb_message', _tr('Error deleting command') . ': ' . $pMikrotik->errMsg);
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function addTrunk($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $trunk_name = getParameter('trunk_select');

    if (empty($trunk_name)) {
        $smarty->assign('mb_message', _tr('Please select a trunk'));
    } elseif ($pMikrotik->getTrunkByName($trunk_name)) {
        $smarty->assign('mb_message', _tr('Trunk is already being monitored'));
    } elseif ($pMikrotik->addTrunk($trunk_name)) {
        $smarty->assign('mb_message', _tr('Trunk added successfully'));
    } else {
        $smarty->assign('mb_message', _tr('Error adding trunk') . ': ' . $pMikrotik->errMsg);
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function deleteTrunk($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $id = getParameter('trunk_id');

    if ($pMikrotik->deleteTrunk($id)) {
        $smarty->assign('mb_message', _tr('Trunk removed successfully'));
    } else {
        $smarty->assign('mb_message', _tr('Error removing trunk') . ': ' . $pMikrotik->errMsg);
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function toggleTrunk($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $id = getParameter('trunk_id');
    $trunk = $pMikrotik->getTrunkById($id);

    if ($trunk) {
        $newStatus = $trunk['enabled'] ? 0 : 1;
        if ($pMikrotik->updateTrunk($id, $trunk['trunk_name'], $newStatus)) {
            $smarty->assign('mb_message', _tr('Trunk status updated'));
        } else {
            $smarty->assign('mb_message', _tr('Error updating trunk') . ': ' . $pMikrotik->errMsg);
        }
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function viewEventLogs($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr("Event Log"));
    $oGrid->pagingShow(true);
    $oGrid->enableExport();
    $oGrid->setNameFile_Export(_tr("mikrotik_failover_logs"));

    $url = array(
        "menu" => $module_name,
        "action" => "view_logs",
        "filter_field" => $filter_field,
        "filter_value" => $filter_value
    );
    $oGrid->setURL($url);

    $arrColumns = array(
        _tr("Timestamp"),
        _tr("Trunk"),
        _tr("Event"),
        _tr("IP"),
        _tr("Ping"),
        _tr("Action"),
        _tr("Status")
    );
    $oGrid->setColumns($arrColumns);

    $total = $pMikrotik->getNumEventLogs($filter_field, $filter_value);
    $limit = 20;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();

    $arrResult = $pMikrotik->getEventLogs($limit, $offset, $filter_field, $filter_value);
    $arrData = array();

    if (is_array($arrResult) && $total > 0) {
        foreach ($arrResult as $row) {
            $arrData[] = array(
                $row['timestamp'],
                $row['trunk_name'],
                $row['event_type'],
                $row['trunk_ip'],
                $row['ping_result'],
                $row['action_taken'],
                $row['status']
            );
        }
    }
    $oGrid->setData($arrData);

    // Filter form
    $arrFilter = array(
        "trunk_name" => _tr("Trunk"),
        "event_type" => _tr("Event Type"),
        "status" => _tr("Status")
    );

    $arrFormElements = array(
        "filter_field" => array(
            "LABEL" => _tr("Search"),
            "REQUIRED" => "no",
            "INPUT_TYPE" => "SELECT",
            "INPUT_EXTRA_PARAM" => $arrFilter,
            "VALIDATION_TYPE" => "text",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
        "filter_value" => array(
            "LABEL" => "",
            "REQUIRED" => "no",
            "INPUT_TYPE" => "TEXT",
            "INPUT_EXTRA_PARAM" => "",
            "VALIDATION_TYPE" => "text",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
    );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", _tr("Show"));
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    $oGrid->showFilter(trim($htmlFilter));

    $oGrid->customAction("action=default", _tr("Back to Config"), "arrow-left");
    $oGrid->deleteList(_tr("Are you sure you want to clear all logs?"), "clear_logs", _tr("Clear Logs"));

    return $oGrid->fetchGrid();
}

function clearEventLogs($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    if ($pMikrotik->clearEventLogs()) {
        $smarty->assign('mb_message', _tr('Event logs cleared successfully'));
    } else {
        $smarty->assign('mb_message', _tr('Error clearing logs') . ': ' . $pMikrotik->errMsg);
    }

    return viewEventLogs($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function startDaemon($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $output = array();
    $retval = 0;
    exec('/usr/bin/issabel-helper mikrotikfailover --start 2>&1', $output, $retval);

    if ($retval == 0) {
        $smarty->assign('mb_message', _tr('Daemon started successfully'));
        $pMikrotik->updateDaemonStatus('running');
    } else {
        $smarty->assign('mb_message', _tr('Error starting daemon') . ': ' . implode("\n", $output));
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function stopDaemon($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $output = array();
    $retval = 0;
    exec('/usr/bin/issabel-helper mikrotikfailover --stop 2>&1', $output, $retval);

    if ($retval == 0) {
        $smarty->assign('mb_message', _tr('Daemon stopped successfully'));
        $pMikrotik->updateDaemonStatus('stopped');
    } else {
        $smarty->assign('mb_message', _tr('Error stopping daemon') . ': ' . implode("\n", $output));
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function toggleAutostart($smarty, $module_name, $local_templates_dir, &$pMikrotik)
{
    $enable = getParameter("autostart_enabled") ? true : false;
    $output = array();
    $retval = 0;

    if ($enable) {
        exec('/usr/bin/issabel-helper mikrotikfailover --enable 2>&1', $output, $retval);
    } else {
        exec('/usr/bin/issabel-helper mikrotikfailover --disable 2>&1', $output, $retval);
    }

    return showMainConfig($smarty, $module_name, $local_templates_dir, $pMikrotik);
}

function getAction()
{
    // AJAX request for real-time status
    if (getParameter("action") == "get_status_ajax")
        return "get_status_ajax";

    // Check form_action hidden field (for forms with multiple buttons)
    $formAction = getParameter("form_action");
    if (!empty($formAction)) {
        return $formAction;
    }

    // MikroTik Config
    if (getParameter("save_mikrotik"))
        return "save_mikrotik";
    if (getParameter("test_mikrotik"))
        return "test_mikrotik";

    // SMTP Config
    if (getParameter("save_smtp"))
        return "save_smtp";
    if (getParameter("test_smtp"))
        return "test_smtp";

    // Monitoring Config
    if (getParameter("save_monitoring"))
        return "save_monitoring";

    // Commands
    if (getParameter("add_command"))
        return "add_command";
    if (getParameter("edit_command"))
        return "edit_command";
    if (getParameter("delete_command"))
        return "delete_command";

    // Trunks
    if (getParameter("add_trunk"))
        return "add_trunk";
    if (getParameter("delete_trunk"))
        return "delete_trunk";
    if (getParameter("toggle_trunk"))
        return "toggle_trunk";

    // Daemon
    if (getParameter("start_daemon"))
        return "start_daemon";
    if (getParameter("stop_daemon"))
        return "stop_daemon";
    if (getParameter("toggle_autostart"))
        return "toggle_autostart";

    // Logs
    if (getParameter("action") == "view_logs")
        return "view_logs";
    if (getParameter("clear_logs"))
        return "clear_logs";

    return "default";
}
?>
