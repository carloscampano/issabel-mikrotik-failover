<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | MikroTik Failover Logs Module for Issabel                            |
  +----------------------------------------------------------------------+
*/

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    include_once "modules/$module_name/configs/default.conf.php";
    // Use the class from main module
    include_once "modules/mikrotik_failover/libs/paloSantoMikrotikFailover.class.php";

    load_language_module($module_name);

    global $arrConf;

    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir = (isset($arrConf['templates_dir'])) ? $arrConf['templates_dir'] : 'themes';
    $theme = (isset($arrConf['theme']) && !empty($arrConf['theme'])) ? $arrConf['theme'] : 'default';
    $local_templates_dir = "$base_dir/modules/$module_name/" . $templates_dir . '/' . $theme;

    $pDB = new paloDB($arrConf['dsn_conn_database']);
    $pMikrotik = new paloSantoMikrotikFailover($pDB);

    $action = getAction();
    $content = "";

    switch ($action) {
        case 'clear_logs':
            $content = clearEventLogs($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
        default:
            $content = viewEventLogs($smarty, $module_name, $local_templates_dir, $pMikrotik);
            break;
    }
    return $content;
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
    $limit = 30;
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

function getAction()
{
    if (getParameter("clear_logs"))
        return "clear_logs";
    return "view_logs";
}
?>
