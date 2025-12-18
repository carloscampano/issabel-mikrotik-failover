# MikroTik Failover Module for Issabel PBX

Module for Issabel PBX that monitors SIP trunks and executes commands on a MikroTik router when a trunk becomes unreachable while the network is still operational.

## Features

- **Real-time trunk monitoring** via Asterisk AMI events
- **Intelligent failover detection**: Only triggers when trunk is unreachable but ping succeeds (network is up, SIP is down)
- **MikroTik SSH integration**: Execute custom commands on your MikroTik router
- **Email notifications**: Get alerts when trunks fail or recover
- **Configurable retry logic**: Single or multiple retry attempts with intervals
- **Cooldown period**: Prevent command flooding
- **Event logging**: Full history of all events and actions
- **Auto-start daemon**: Daemon starts automatically on boot
- **Web-based configuration**: Easy setup through Issabel web interface

## Requirements

- Issabel PBX 4.x (CentOS 7)
- PHP 5.4+ with SSH2 extension (installed automatically)
- MikroTik router with SSH access enabled

## Installation

### From RPM (Recommended)

```bash
# Download the latest RPM
wget https://github.com/campano/issabel-mikrotik-failover/releases/latest/download/issabel-mikrotik-failover-1.0.26-1.el7.noarch.rpm

# Install
yum localinstall -y issabel-mikrotik-failover-1.0.26-1.el7.noarch.rpm

# Restart Apache
systemctl restart httpd
```

The module will be available at: **PBX > PBX Configuration > MikroTik Failover**

### What gets installed

- Module files in `/var/www/html/modules/mikrotik_failover/`
- Logs module in `/var/www/html/modules/mikrotik_failover_logs/`
- Privileged helper in `/usr/share/issabel/privileged/mikrotikfailover`
- Systemd service in `/etc/systemd/system/mikrotik-failover.service`
- SQLite database in `/var/www/db/mikrotik_failover.db`

## Configuration

### 1. MikroTik Configuration

Enter your MikroTik router credentials:
- **IP Address**: Router IP
- **SSH Port**: Usually 22
- **Username**: SSH user with command execution permissions
- **Password**: SSH password

Use "Test Connection" to verify connectivity.

### 2. SMTP Configuration (Optional)

Configure email notifications:
- **SMTP Server**: Your mail server
- **SMTP Port**: Usually 465 (SSL) or 587 (TLS)
- **Username/Password**: SMTP credentials
- **From/To Email**: Notification addresses
- **Enabled**: Check to enable notifications

### 3. Monitoring Configuration

- **Delay before action**: Seconds to wait before checking ping (default: 10)
- **Retry Mode**: Single or Multiple attempts
- **Retry Count**: Number of retries (if multiple mode)
- **Retry Interval**: Seconds between retries
- **Cooldown Period**: Minimum seconds between actions for same trunk
- **Enabled**: Must be checked for monitoring to work

### 4. Commands to Execute

Add MikroTik commands to execute when failover triggers:

```
/interface pppoe-client disable pppoe-out1
/interface pppoe-client enable pppoe-out1
```

Or log messages:
```
:log error message="Trunk failover triggered from Issabel"
```

### 5. Monitored Trunks

Select which SIP trunks to monitor from the dropdown list.

## How It Works

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│   Asterisk      │────▶│  Failover Daemon │────▶│    MikroTik     │
│   (AMI Events)  │     │  (monitors SIP)  │     │   (SSH cmds)    │
└─────────────────┘     └──────────────────┘     └─────────────────┘
                               │
                               ▼
                        ┌──────────────────┐
                        │  Decision Logic  │
                        ├──────────────────┤
                        │ Trunk UNREACHABLE│
                        │ + Ping FAILS     │──▶ No action (network down)
                        │                  │
                        │ Trunk UNREACHABLE│
                        │ + Ping OK        │──▶ Execute MikroTik commands
                        └──────────────────┘
```

1. Daemon listens for AMI `PeerStatus` events
2. When a monitored trunk becomes `Unreachable`:
   - Waits configured delay
   - Pings the trunk IP
   - If ping succeeds (network OK, SIP down) → Execute MikroTik commands
   - If ping fails (network down) → Log event, no action
3. Logs all events to database
4. Sends email notifications (if enabled)

## Daemon Control

### Via Web Interface
Use the Start/Stop buttons in the "Monitoring Daemon" section.

### Via Command Line
```bash
# Start daemon
systemctl start mikrotik-failover

# Stop daemon
systemctl stop mikrotik-failover

# Check status
systemctl status mikrotik-failover

# Enable on boot
systemctl enable mikrotik-failover

# Disable on boot
systemctl disable mikrotik-failover
```

### Via issabel-helper
```bash
/usr/bin/issabel-helper mikrotikfailover --start
/usr/bin/issabel-helper mikrotikfailover --stop
/usr/bin/issabel-helper mikrotikfailover --status
/usr/bin/issabel-helper mikrotikfailover --restart
/usr/bin/issabel-helper mikrotikfailover --enable
/usr/bin/issabel-helper mikrotikfailover --disable
```

## Logs

### File Log
```bash
tail -f /var/log/mikrotik_failover.log
```

### Database Log
Access via web interface: "View Logs" button

## Troubleshooting

### Daemon won't start
```bash
# Check log for errors
tail -50 /var/log/mikrotik_failover.log

# Check if AMI is accessible
asterisk -rx "manager show connected"
```

### Commands not executing
1. Verify "Enabled" checkbox in Monitoring Configuration is checked
2. Verify trunk is in Monitored Trunks list
3. Check that ping to trunk IP succeeds when trunk is unreachable
4. Verify MikroTik credentials with "Test Connection"

### No events detected
1. Ensure daemon is running: `systemctl status mikrotik-failover`
2. Verify trunk is monitored and enabled
3. Check AMI connection in log file

## Building from Source

### Requirements
- Docker
- macOS or Linux

### Build RPM
```bash
cd rpm
./build.sh 1.0.26
```

RPM will be created in `rpm/output/noarch/`

## Version History

### v1.0.26 (2024-12-18)
- Monitoring enabled by default on fresh install
- Daemon auto-starts and enables on boot after installation
- Fixed module installer path structure
- Title color fixed for better visibility

### v1.0.25
- Fixed title visibility on gray background

### v1.0.24
- Added "Auto-start on boot" checkbox option

### v1.0.23
- Fixed SMTP port label
- Changed default SMTP port to 465

### v1.0.22
- Fixed module_installer nested directory bug

### v1.0.21
- Improved daemon start/stop button styling

### v1.0.20
- Fixed button icon styling issues

## License

GPLv3+

## Author

Developed for Issabel PBX

## Support

For issues and feature requests, please use the GitHub issue tracker.
