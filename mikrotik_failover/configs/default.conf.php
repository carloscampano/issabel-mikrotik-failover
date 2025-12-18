<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | MikroTik Failover Module for Issabel                                 |
  +----------------------------------------------------------------------+
  | Copyright (c) 2024                                                   |
  +----------------------------------------------------------------------+
  | Monitorea troncales SIP y ejecuta comandos en MikroTik cuando        |
  | el troncal está unreachable pero la IP responde a ping               |
  +----------------------------------------------------------------------+
*/

global $arrConfModule;
$arrConfModule['module_name'] = 'mikrotik_failover';
$arrConfModule['templates_dir'] = 'themes';

// DSN para la base de datos del módulo
$arrConfModule['dsn_conn_database'] = "sqlite3:////var/www/db/mikrotik_failover.db";

?>
