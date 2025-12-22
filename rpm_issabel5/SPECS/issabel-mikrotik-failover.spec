%define modname mikrotik_failover
%define modname_logs mikrotik_failover_logs

Name:           issabel-mikrotik-failover
Version:        1.0.34
Release:        1%{?dist}
Summary:        MikroTik Failover Module for Issabel PBX
License:        GPLv3+
Group:          Applications/Communications
URL:            https://github.com/campano/issabel-mikrotik-failover
Source0:        %{name}-%{version}.tar.gz
BuildArch:      noarch
BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)

# Dependencies for Issabel 5 / Rocky Linux 8 / PHP 7.4
Requires:       issabel-framework >= 5.0
Requires:       issabel-pbx
Requires:       php >= 7.4
Requires:       php-pecl-ssh2
Requires:       php-PHPMailer
Requires:       sqlite

%description
MikroTik Failover Module for Issabel PBX.
This module monitors SIP trunk health and automatically executes
recovery commands on MikroTik routers when specific failure conditions
are detected.

Features:
- Intelligent detection of trunk failures
- Automatic execution of MikroTik commands via SSH
- Email notifications
- Event logging and cooldown protection
- Web-based configuration interface

Compatible with Issabel 5 on Rocky Linux 8 with PHP 7.4.

%prep
%setup -q -n %{name}-%{version}

%build
# No build needed for PHP module

%install
rm -rf %{buildroot}

# Module directory
mkdir -p %{buildroot}/var/www/html/modules/%{modname}
cp -r module/* %{buildroot}/var/www/html/modules/%{modname}/

# Logs module directory
mkdir -p %{buildroot}/var/www/html/modules/%{modname_logs}
cp -r module_logs/* %{buildroot}/var/www/html/modules/%{modname_logs}/

# Privileged helper
mkdir -p %{buildroot}/usr/share/issabel/privileged
install -m 755 privileged/mikrotikfailover %{buildroot}/usr/share/issabel/privileged/

# Module installer (for menu registration)
mkdir -p %{buildroot}/usr/share/issabel/module_installer/%{modname}/setup
cp -r module_installer/%{modname}/. %{buildroot}/usr/share/issabel/module_installer/%{modname}/

# Systemd service
mkdir -p %{buildroot}/etc/systemd/system
install -m 644 systemd/mikrotik-failover.service %{buildroot}/etc/systemd/system/

# Database directory
mkdir -p %{buildroot}/var/www/db

# Log directory placeholder
mkdir -p %{buildroot}/var/log

%clean
rm -rf %{buildroot}

%pre
# Pre-installation script

%post
# Post-installation script
echo "Configuring MikroTik Failover Module..."

# Set permissions on module directories
chown -R asterisk:asterisk /var/www/html/modules/%{modname}
chmod -R 755 /var/www/html/modules/%{modname}
chmod +x /var/www/html/modules/%{modname}/libs/mikrotik_failover_daemon.php

chown -R asterisk:asterisk /var/www/html/modules/%{modname_logs}
chmod -R 755 /var/www/html/modules/%{modname_logs}

# Create database if not exists
if [ ! -f /var/www/db/mikrotik_failover.db ]; then
    php /usr/share/issabel/module_installer/%{modname}/setup/installer.php
fi

# Set database permissions
if [ -f /var/www/db/mikrotik_failover.db ]; then
    chown asterisk:asterisk /var/www/db/mikrotik_failover.db
    chmod 666 /var/www/db/mikrotik_failover.db
fi

# Register module in menu
if [ -x /usr/bin/issabel-menumerge ]; then
    /usr/bin/issabel-menumerge /usr/share/issabel/module_installer/%{modname}/menu.xml 2>/dev/null || true
fi

# Create log file
touch /var/log/mikrotik_failover.log
chmod 666 /var/log/mikrotik_failover.log
chown asterisk:asterisk /var/log/mikrotik_failover.log

# Reload systemd and enable/start daemon
systemctl daemon-reload
systemctl enable mikrotik-failover 2>/dev/null || true
systemctl start mikrotik-failover 2>/dev/null || true

# Issabel 5 specific: Configure Apache access to Asterisk socket
usermod -a -G asterisk apache 2>/dev/null || true

# Create systemd drop-in for Asterisk socket permissions
mkdir -p /etc/systemd/system/asterisk.service.d/
cat > /etc/systemd/system/asterisk.service.d/socket-perms.conf << 'EOFDRP'
[Service]
ExecStartPost=/bin/bash -c "sleep 2 && chgrp apache /var/run/asterisk/asterisk.ctl 2>/dev/null && chmod 660 /var/run/asterisk/asterisk.ctl 2>/dev/null || true"
EOFDRP
systemctl daemon-reload

# Apply socket permissions now if Asterisk is running
if [ -S /var/run/asterisk/asterisk.ctl ]; then
    chgrp apache /var/run/asterisk/asterisk.ctl 2>/dev/null || true
    chmod 660 /var/run/asterisk/asterisk.ctl 2>/dev/null || true
fi

# Restart Apache to load SSH2 extension and apply group membership
systemctl restart httpd 2>/dev/null || true

echo ""
echo "============================================"
echo "MikroTik Failover Module installed!"
echo "============================================"
echo ""
echo "Access: PBX > PBX Configuration > MikroTik Failover"
echo ""
echo "Daemon enabled and started automatically."
echo ""

%preun
# Pre-uninstallation script
if [ $1 -eq 0 ]; then
    # Complete uninstall (not upgrade)
    systemctl stop mikrotik-failover 2>/dev/null || true
    systemctl disable mikrotik-failover 2>/dev/null || true
fi

%postun
# Post-uninstallation script
if [ $1 -eq 0 ]; then
    # Complete uninstall (not upgrade)
    systemctl daemon-reload

    # Remove menu entry
    if [ -x /usr/bin/issabel-menuremove ]; then
        /usr/bin/issabel-menuremove %{modname} 2>/dev/null || true
    fi

    echo "MikroTik Failover Module removed."
    echo "Database preserved at /var/www/db/mikrotik_failover.db"
    echo "Log preserved at /var/log/mikrotik_failover.log"
fi

%files
%defattr(-,root,root,-)
/var/www/html/modules/%{modname}/
/var/www/html/modules/%{modname_logs}/
/usr/share/issabel/privileged/mikrotikfailover
/usr/share/issabel/module_installer/%{modname}/
/etc/systemd/system/mikrotik-failover.service

%changelog
* Sun Dec 22 2024 Developer <dev@example.com> - 1.0.34-1
- Issabel 5 / Rocky Linux 8 / PHP 7.4 compatible version
- Fixed Apache user permissions for Asterisk socket access
- Added systemd drop-in for persistent socket permissions
- Fixed detection of numeric trunk names (>6 digits)
- Fixed IP detection with port suffix (192.168.1.1:5060)
- Fixed hostname resolution for ToHost field
- Improved regex for variable SIP peer output format
- Updated code for PHP 7.4 compatibility (public vs var)

* Sun Dec 22 2024 Developer <dev@example.com> - 1.0.33-1
- Added support for Registry AMI events (SIP trunks with registration)
- Now detects both PeerStatus and Registry events for trunk monitoring
- Emails sent on trunk down (Request Sent/Unregistered) and restored (Registered)
