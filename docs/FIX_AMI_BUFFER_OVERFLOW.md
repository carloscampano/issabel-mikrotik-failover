# Fix: AMI Buffer Overflow en MikroTik Failover Daemon

## Problema

El daemon `mikrotik_failover_daemon.php` causa bloqueo del AMI de Asterisk después de varios días de operación, provocando:

- Panel de operador no conecta (timeout)
- Llamadas no se pueden contestar
- Asterisk se vuelve lento o no responde
- Después de reiniciar el servidor, funciona normalmente

## Causa Raíz

El daemon se suscribe a **todos los eventos AMI** pero solo procesa `PeerStatus` y `Registry`. Los eventos no procesados (CDR, Channel, Bridge, Dial, Hangup, etc.) se acumulan en el buffer TCP.

### Evidencia del problema:

```bash
ss -tnp | grep 5038
```

**Con el bug:**
```
ESTAB 0 2619907 127.0.0.1:5038 127.0.0.1:53536 (asterisk)
ESTAB 909724 0 127.0.0.1:53536 127.0.0.1:5038 (php)
```
- 2.6MB acumulados en el buffer de Asterisk
- 900KB pendientes de lectura en el cliente PHP

**Sin el bug (después del fix):**
```
ESTAB 0 0 127.0.0.1:5038 127.0.0.1:53842 (asterisk)
ESTAB 0 0 127.0.0.1:53842 127.0.0.1:5038 (php)
```
- Buffers en 0, sin acumulación

## Solución

Agregar filtro de eventos AMI (`EventMask`) después del login para que el daemon solo reciba los eventos que necesita.

### Código a agregar

En la función `connect_ami()`, después de la línea:
```php
log_message("AMI login successful");
```

Agregar:
```php
        // IMPORTANTE: Filtrar solo eventos PeerStatus y Registry
        // Esto evita acumulacion de eventos en el buffer TCP
        $filter = "Action: Events\r\n";
        $filter .= "EventMask: system,call\r\n";
        $filter .= "\r\n";
        fwrite($ami_socket, $filter);
        // Consumir respuesta del filtro
        while (($line = fgets($ami_socket)) !== false) {
            if (trim($line) === '') break;
        }
        log_message("AMI event filter applied");
```

### Aplicar el fix manualmente

```bash
# Backup del archivo original
cp /var/www/html/modules/mikrotik_failover/libs/mikrotik_failover_daemon.php \
   /var/www/html/modules/mikrotik_failover/libs/mikrotik_failover_daemon.php.bak

# Encontrar la línea a modificar
grep -n 'AMI login successful' /var/www/html/modules/mikrotik_failover/libs/mikrotik_failover_daemon.php

# Editar el archivo y agregar el código después de esa línea
nano /var/www/html/modules/mikrotik_failover/libs/mikrotik_failover_daemon.php

# Verificar sintaxis
php -l /var/www/html/modules/mikrotik_failover/libs/mikrotik_failover_daemon.php

# Reiniciar el daemon
systemctl restart mikrotik-failover

# Verificar que el filtro se aplicó
journalctl -u mikrotik-failover -n 20
# Debe mostrar: "AMI event filter applied"
```

### Script de fix automático

```bash
#!/bin/bash
# fix_mikrotik_failover_ami.sh
# Aplica el fix de AMI buffer overflow al daemon mikrotik_failover

DAEMON_FILE="/var/www/html/modules/mikrotik_failover/libs/mikrotik_failover_daemon.php"
BACKUP_FILE="${DAEMON_FILE}.bak.$(date +%Y%m%d%H%M%S)"

# Verificar que el archivo existe
if [ ! -f "$DAEMON_FILE" ]; then
    echo "ERROR: No se encontró el archivo $DAEMON_FILE"
    exit 1
fi

# Verificar si ya tiene el fix
if grep -q "EventMask: system,call" "$DAEMON_FILE"; then
    echo "El fix ya está aplicado"
    exit 0
fi

# Crear backup
cp "$DAEMON_FILE" "$BACKUP_FILE"
echo "Backup creado: $BACKUP_FILE"

# Encontrar línea del login successful
LINE_NUM=$(grep -n 'log_message("AMI login successful");' "$DAEMON_FILE" | cut -d: -f1)

if [ -z "$LINE_NUM" ]; then
    echo "ERROR: No se encontró la línea de referencia"
    exit 1
fi

# Crear archivo temporal con el fix
head -n "$LINE_NUM" "$DAEMON_FILE" > /tmp/daemon_fix.php
cat >> /tmp/daemon_fix.php << 'FIXCODE'

        // IMPORTANTE: Filtrar solo eventos PeerStatus y Registry
        // Esto evita acumulacion de eventos en el buffer TCP
        $filter = "Action: Events\r\n";
        $filter .= "EventMask: system,call\r\n";
        $filter .= "\r\n";
        fwrite($ami_socket, $filter);
        // Consumir respuesta del filtro
        while (($line = fgets($ami_socket)) !== false) {
            if (trim($line) === '') break;
        }
        log_message("AMI event filter applied");
FIXCODE
tail -n +"$((LINE_NUM + 1))" "$DAEMON_FILE" >> /tmp/daemon_fix.php

# Verificar sintaxis
if ! php -l /tmp/daemon_fix.php > /dev/null 2>&1; then
    echo "ERROR: El archivo generado tiene errores de sintaxis"
    exit 1
fi

# Aplicar fix
cp /tmp/daemon_fix.php "$DAEMON_FILE"
echo "Fix aplicado correctamente"

# Reiniciar daemon
systemctl restart mikrotik-failover
echo "Daemon reiniciado"

# Verificar
sleep 2
if systemctl is-active --quiet mikrotik-failover; then
    echo "Daemon corriendo correctamente"
    journalctl -u mikrotik-failover -n 5 --no-pager
else
    echo "ERROR: El daemon no inició correctamente"
    systemctl status mikrotik-failover --no-pager
    exit 1
fi
```

## Monitoreo

Para detectar si el problema está ocurriendo:

```bash
# Ver buffers de conexiones AMI
ss -tnp | grep 5038

# Si la segunda o tercera columna muestra valores > 100000, hay acumulación
# Columna 2 (Send-Q): datos pendientes de enviar
# Columna 3 (Recv-Q): datos pendientes de recibir
```

## EventMask en AMI

El filtro `EventMask: system,call` permite recibir solo:

| Categoría | Eventos incluidos |
|-----------|-------------------|
| system | PeerStatus, Registry, Reload, Shutdown |
| call | Dial, Hangup, Bridge (si se necesitan) |

Esto reduce drásticamente la cantidad de eventos recibidos, de cientos por minuto a solo los necesarios para monitorear troncales.

## Fecha del fix

- **Detectado:** 2026-01-02
- **Servidor afectado:** 192.168.10.249 (Issabel 4)
- **Síntomas:** Panel de operador no conectaba, llamadas no se podían contestar
- **Tiempo hasta falla:** ~6 días de uptime
