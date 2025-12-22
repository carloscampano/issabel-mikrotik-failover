# MikroTik Failover Module for Issabel PBX

Module that monitors SIP trunk health and automatically executes recovery commands on MikroTik routers when specific failure conditions are detected.

## Features

- **Intelligent trunk monitoring**: Detects when SIP trunks become unreachable via AMI events
- **Smart failover logic**: Only executes MikroTik commands when ping succeeds but SIP fails (NAT/conntrack issue)
- **Automatic script generation**: Creates `del_conn` script on MikroTik to clear connection tracking
- **Email notifications**: Alerts on trunk failures and recoveries
- **Cooldown protection**: Prevents command flooding
- **Web interface**: Full configuration through Issabel GUI
- **Event logging**: Complete history of all events and actions

## Compatibility

| Issabel Version | OS | PHP | RPM Suffix |
|-----------------|-----|-----|------------|
| Issabel 4 | CentOS 7 | 5.4+ | `.el7` |
| Issabel 5 | Rocky Linux 8 | 7.4 | `.el8` |

## Installation

### Issabel 4 (CentOS 7)

```bash
# Download RPM from GitHub releases
wget https://github.com/carloscampano/issabel-mikrotik-failover/releases/download/v1.0.34/issabel-mikrotik-failover-1.0.34-1.el7.noarch.rpm

# Install
yum install -y ./issabel-mikrotik-failover-1.0.34-1.el7.noarch.rpm
```

### Issabel 5 (Rocky Linux 8)

```bash
# Download RPM from GitHub releases
wget https://github.com/carloscampano/issabel-mikrotik-failover/releases/download/v1.0.34/issabel-mikrotik-failover-1.0.34-1.el8.noarch.rpm

# Install
yum install -y ./issabel-mikrotik-failover-1.0.34-1.el8.noarch.rpm
```

The module will be available at: **PBX > PBX Configuration > MikroTik Failover**

## Dependencies

### Issabel 4
- `issabel-framework >= 4.0`
- `issabel-pbx`
- `php >= 5.4`
- `php-pecl-ssh2`
- `php-PHPMailer`
- `sqlite`

### Issabel 5
- `issabel-framework >= 5.0`
- `issabel-pbx`
- `php >= 7.4`
- `php-pecl-ssh2` (from remi repository)
- `php-PHPMailer`
- `sqlite`

## Configuration

After installation, access the module at:

**PBX > PBX Configuration > MikroTik Failover**

### 1. MikroTik Configuration

Configure SSH access to your MikroTik router:
- **IP Address**: Router management IP
- **SSH Port**: Usually 22
- **Username**: Admin user with script execution permissions
- **Password**: User password

Click "Test Connection" to verify.

### 2. SMTP Configuration (Optional)

Configure email notifications:
- **SMTP Server**: Your mail server
- **Port**: 25, 465 (SSL), or 587 (TLS)
- **Username/Password**: SMTP authentication
- **From/To Email**: Sender and recipient addresses

### 3. Monitoring Configuration

- **Delay before action**: Seconds to wait before checking ping (default: 10)
- **Retry mode**: Single or multiple attempts
- **Cooldown period**: Minimum seconds between actions per trunk (default: 300)
- **Enabled**: Must be checked for monitoring to work

### 4. Add Monitored Trunks

Select SIP trunks to monitor from the dropdown list.

### 5. Generate Script

Click "Generate Script" to:
1. Create `del_conn` script on MikroTik
2. Configure command to run: `/system script run del_conn`

The script clears connection tracking entries for PBX and trunk IPs.

## How It Works

```
Trunk UNREACHABLE detected (via AMI)
         |
         v
    Wait delay period
         |
         v
    Ping trunk IP ---------> FAILED --> Log event, send email
         |                              (Network down - no action)
         |
      SUCCESS
         |
         v
    Execute MikroTik commands
    (/system script run del_conn)
         |
         v
    Log event, send email, update cooldown
```

### Event Types Monitored

- **PeerStatus**: For SIP peers without registration
- **Registry**: For SIP trunks with registration (Registered, Request Sent, etc.)

## Directory Structure

```
/var/www/html/modules/mikrotik_failover/     # Main module
/var/www/html/modules/mikrotik_failover_logs/ # Logs module
/var/www/db/mikrotik_failover.db             # SQLite database
/var/log/mikrotik_failover.log               # Daemon log
/etc/systemd/system/mikrotik-failover.service # Systemd service
/usr/share/issabel/privileged/mikrotikfailover # Privileged helper
```

## Daemon Control

```bash
# Status
systemctl status mikrotik-failover

# Start/Stop/Restart
systemctl start mikrotik-failover
systemctl stop mikrotik-failover
systemctl restart mikrotik-failover

# Enable/Disable auto-start
systemctl enable mikrotik-failover
systemctl disable mikrotik-failover

# View logs
tail -f /var/log/mikrotik_failover.log
```

## Building from Source

### Prerequisites

- Docker (for Issabel 4 builds on macOS/Linux)
- Rocky Linux 8 system or Issabel 5 server (for Issabel 5 builds)

### Build Commands

```bash
# Build for both versions
./build_rpm.sh both

# Build only for Issabel 4
./build_rpm.sh issabel4

# Build only for Issabel 5
./build_rpm.sh issabel5
```

### Output Locations

- **Issabel 4**: `rpm/output/noarch/issabel-mikrotik-failover-*.el7.noarch.rpm`
- **Issabel 5**: `rpm_issabel5/output/issabel-mikrotik-failover-*.el8.noarch.rpm`

### Project Structure

```
issabel_actions/
├── mikrotik_failover/           # Main module source
│   ├── index.php
│   ├── libs/
│   │   ├── paloSantoMikrotikFailover.class.php
│   │   └── mikrotik_failover_daemon.php
│   ├── themes/
│   ├── lang/
│   └── configs/
├── mikrotik_failover_logs/      # Logs module source
├── module_installer/            # Menu registration
├── privileged/                  # Helper script
├── systemd/                     # Service file
├── rpm/                         # Issabel 4 build
│   ├── SPECS/
│   ├── SOURCES/
│   ├── output/
│   ├── Dockerfile
│   └── build.sh
├── rpm_issabel5/                # Issabel 5 build
│   ├── SPECS/
│   └── output/
├── build_rpm.sh                 # Unified build script
└── README.md
```

## Issabel 5 Specific Notes

### Apache Permissions

The Issabel 5 RPM automatically configures:
1. Adds `apache` user to `asterisk` group
2. Creates systemd drop-in to set socket permissions
3. Configures `/var/run/asterisk/asterisk.ctl` access

### Trunk Name Detection

Supports both:
- Traditional trunk names (e.g., `MyProvider`)
- Numeric trunk names with >6 digits (e.g., `56413290350/56413290350`)

## Troubleshooting

### Module not showing trunks (Issabel 5)

Check Apache access to Asterisk:
```bash
sudo -u apache asterisk -rx "sip show peers"
```

If "Permission denied", verify socket permissions:
```bash
ls -la /var/run/asterisk/asterisk.ctl
# Should be: srw-rw---- asterisk apache
```

Fix manually if needed:
```bash
chgrp apache /var/run/asterisk/asterisk.ctl
chmod 660 /var/run/asterisk/asterisk.ctl
```

### SSH2 extension not loaded

```bash
# Check if loaded
php -m | grep ssh2

# Issabel 4: Should be installed automatically
yum install php-pecl-ssh2

# Issabel 5: Install from remi
yum install php-pecl-ssh2

# Restart Apache
systemctl restart httpd
```

### Daemon not detecting events

Check AMI connection:
```bash
tail -f /var/log/mikrotik_failover.log
```

Verify AMI credentials in `/etc/issabel.conf`.

### Test Connection fails

1. Verify MikroTik SSH is enabled
2. Check firewall allows port 22
3. Verify username/password
4. Test manually: `ssh user@mikrotik-ip`

## Version History

### 1.0.34 (2024-12-22)
- **Issabel 5 support** (Rocky Linux 8, PHP 7.4)
- Fixed Apache user permissions for Asterisk socket access
- Added systemd drop-in for persistent socket permissions
- Fixed detection of numeric trunk names (>6 digits)
- Fixed IP detection with port suffix (192.168.1.1:5060)
- Fixed hostname resolution for ToHost field
- Improved regex for variable SIP peer output format
- Updated code for PHP 7.4 compatibility

### 1.0.33 (2024-12-20)
- Added support for Registry AMI events (SIP trunks with registration)
- Detects both PeerStatus and Registry events
- Email notifications on trunk state changes

### 1.0.32 (2024-12-20)
- Fixed email notifications with PHPMailer

### 1.0.31 (2024-12-20)
- Apache restart in post-install for SSH2

### 1.0.30 (2024-12-18)
- Persistent del_conn script on MikroTik

### 1.0.29 (2024-12-18)
- Fixed script upload using SFTP method

## License

GPLv3+

## Author

Developed for Issabel PBX
