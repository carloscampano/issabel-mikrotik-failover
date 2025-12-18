#!/bin/bash
# MikroTik Failover Module Installer for Issabel 4
# Run this script on the Issabel server as root

set -e

echo "=========================================="
echo "MikroTik Failover Module Installer"
echo "=========================================="

# Verificar que se ejecuta como root
if [ "$EUID" -ne 0 ]; then
    echo "Please run as root"
    exit 1
fi

# Directorio base
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
MODULE_NAME="mikrotik_failover"

echo ""
echo "[1/7] Installing PHP SSH2 extension..."
yum install -y php-pecl-ssh2 2>/dev/null || echo "SSH2 extension may already be installed or unavailable"

echo ""
echo "[2/7] Copying module files..."
cp -r "$SCRIPT_DIR/$MODULE_NAME" /var/www/html/modules/
chown -R asterisk:asterisk /var/www/html/modules/$MODULE_NAME
chmod -R 755 /var/www/html/modules/$MODULE_NAME
chmod +x /var/www/html/modules/$MODULE_NAME/libs/mikrotik_failover_daemon.php

echo ""
echo "[3/7] Installing privileged helper script..."
cp "$SCRIPT_DIR/privileged/mikrotikfailover" /usr/share/issabel/privileged/
chmod 755 /usr/share/issabel/privileged/mikrotikfailover

echo ""
echo "[4/7] Creating database and tables..."
php "$SCRIPT_DIR/module_installer/$MODULE_NAME/setup/installer.php"

echo ""
echo "[5/7] Registering module in Issabel menu..."
# Usar el instalador de módulos de Issabel si está disponible
if [ -f /usr/bin/issabel-menumerge ]; then
    /usr/bin/issabel-menumerge "$SCRIPT_DIR/module_installer/$MODULE_NAME/menu.xml"
else
    # Instalación manual del menú
    sqlite3 /var/www/db/menu.db "INSERT OR REPLACE INTO menu (id, IdParent, Link, Name, Type, order_no) VALUES ('mikrotik_failover', 'pbxconfig', '', 'MikroTik Failover', 'module', 50);"
fi

echo ""
echo "[6/7] Installing systemd service..."
cp "$SCRIPT_DIR/systemd/mikrotik-failover.service" /etc/systemd/system/
systemctl daemon-reload

echo ""
echo "[7/7] Setting permissions..."
touch /var/log/mikrotik_failover.log
chmod 666 /var/log/mikrotik_failover.log
chown asterisk:asterisk /var/www/db/mikrotik_failover.db
chmod 666 /var/www/db/mikrotik_failover.db

echo ""
echo "=========================================="
echo "Installation complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Log into Issabel web interface"
echo "2. Go to PBX > PBX Configuration > MikroTik Failover"
echo "3. Configure your MikroTik router settings"
echo "4. Add commands to execute"
echo "5. Select trunks to monitor"
echo "6. Enable monitoring and start the daemon"
echo ""
echo "To start the daemon manually:"
echo "  systemctl start mikrotik-failover"
echo ""
echo "To enable daemon on boot:"
echo "  systemctl enable mikrotik-failover"
echo ""
echo "Log file: /var/log/mikrotik_failover.log"
echo ""
