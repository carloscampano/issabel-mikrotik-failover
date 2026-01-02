# Optimizaciones para Issabel 4

## Descripción General

Este documento detalla las optimizaciones y correcciones de bugs identificadas y aplicables a instalaciones de Issabel 4 (basado en CentOS 7, Asterisk 16, PHP 5.4).

**Fecha de documentación:** 2025-12-22
**Versión probada:** Issabel 4 con Asterisk 16.16.1

---

## Problemas Identificados

### 1. Índices Faltantes en Base de Datos

**Síntomas:**
- Reportes de CDR lentos
- Panel del operador tarda en cargar
- Reportes de Call Center lentos
- Alto uso de CPU en MySQL durante consultas

**Causa:**
Las tablas principales no tienen índices en las columnas de fecha/tiempo, causando full table scans en cada consulta.

**Tablas afectadas:**

| Tabla | Columna | Índice Faltante |
|-------|---------|-----------------|
| `cdr` | `calldate` | `idx_calldate` |
| `queue_log` | `time` | `idx_time` |
| `cel` | `eventtime` | `idx_eventtime` |

**Solución - Índices básicos:**
```sql
CREATE INDEX idx_calldate ON cdr(calldate);
CREATE INDEX idx_time ON queue_log(time);
CREATE INDEX idx_eventtime ON cel(eventtime);
```

**Solución - Índices adicionales para reportes (recomendados):**
```sql
-- Mejoran significativamente las consultas del panel de estadísticas
CREATE INDEX idx_src ON cdr(src);
CREATE INDEX idx_dst ON cdr(dst);
CREATE INDEX idx_calldate_src ON cdr(calldate, src);
CREATE INDEX idx_calldate_dst ON cdr(calldate, dst);

-- queue_log usa VARCHAR para time, created es TIMESTAMP (más eficiente)
CREATE INDEX idx_created ON queue_log(created);
```

**Mejora de rendimiento observada:**
| Consulta | Sin índices | Con índices |
|----------|-------------|-------------|
| Panel estadísticas extensiones | 2.99s | <0.5s |
| Rows examinados | 94,989 | 5,280 |

---

### 2. Bug: Undefined index "Bridgestate" en Panel del Operador

**Síntomas:**
- Panel del operador no carga o carga lento
- Errores en `/var/log/httpd/ssl_error_log`:
  ```
  PHP Notice: Undefined index: Bridgestate in
  /var/www/html/modules/control_panel/libs/paloControlPanelStatus.class.php on line 1190
  ```

**Causa:**
El código PHP accede al índice `Bridgestate` sin verificar si existe. En Asterisk 16+, algunos eventos AMI no incluyen este campo.

**Archivo afectado:**
`/var/www/html/modules/control_panel/libs/paloControlPanelStatus.class.php`

**Líneas afectadas:** 1190 y 1193

**Solución:**

```diff
Línea 1190:
- if ($params['Bridgestate'] == 'Link') {
+ if (isset($params['Bridgestate']) && $params['Bridgestate'] == 'Link') {

Línea 1193:
- } elseif ($params['Bridgestate'] == 'Unlink') {
+ } elseif (isset($params['Bridgestate']) && $params['Bridgestate'] == 'Unlink') {
```

**Comando para aplicar:**
```bash
sed -i "s/if (\$params\['Bridgestate'\] == 'Link')/if (isset(\$params['Bridgestate']) \&\& \$params['Bridgestate'] == 'Link')/g" \
    /var/www/html/modules/control_panel/libs/paloControlPanelStatus.class.php

sed -i "s/elseif (\$params\['Bridgestate'\] == 'Unlink')/elseif (isset(\$params['Bridgestate']) \&\& \$params['Bridgestate'] == 'Unlink')/g" \
    /var/www/html/modules/control_panel/libs/paloControlPanelStatus.class.php
```

---

### 3. Bug: Undefined offset en Address Book

**Síntomas:**
- Errores en logs al navegar por el panel:
  ```
  PHP Notice: Undefined offset: 0 in
  /var/www/html/modules/address_book/libs/paloSantoAdressBook.class.php on line 144
  ```

**Causa:**
El código asume que `$matriz[0]` siempre existe, pero cuando la función `getDeviceFreePBX_Completed()` retorna un array vacío, el índice no existe.

**Archivo afectado:**
`/var/www/html/modules/address_book/libs/paloSantoAdressBook.class.php`

**Línea afectada:** 144

**Solución:**

```diff
Línea 144:
- $result = $matriz[0];
+ $result = isset($matriz[0]) ? $matriz[0] : array();
```

**Comando para aplicar:**
```bash
sed -i 's/\$result = \$matriz\[0\];/$result = isset($matriz[0]) ? $matriz[0] : array();/g' \
    /var/www/html/modules/address_book/libs/paloSantoAdressBook.class.php
```

---

### 4. Tabla CEL Excesivamente Grande

**Síntomas:**
- Alto uso de disco
- Backups de base de datos muy grandes
- Consultas lentas

**Causa:**
CEL (Channel Event Logging) registra ~10-20 eventos por cada llamada. En sistemas con alto volumen, puede crecer a millones de registros.

**Diagnóstico:**
```sql
SELECT
    'cdr' as tabla, COUNT(*) as registros FROM cdr
UNION ALL
SELECT 'cel', COUNT(*) FROM cel
UNION ALL
SELECT 'queue_log', COUNT(*) FROM queue_log;
```

**Solución:**
1. Purgar datos antiguos (>90 días recomendado)
2. Considerar deshabilitar CEL si no se usa:
   ```ini
   # /etc/asterisk/cel.conf
   [general]
   enable=no
   ```

**Comando para purgar >90 días:**
```sql
-- Purgar CDR
DELETE FROM cdr WHERE calldate < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Purgar CEL (en batches para evitar bloqueos)
DELETE FROM cel WHERE eventtime < DATE_SUB(NOW(), INTERVAL 90 DAY) LIMIT 1000000;
-- Repetir hasta que no elimine más registros

-- Purgar queue_log
DELETE FROM queue_log WHERE time < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Optimizar tablas
OPTIMIZE TABLE cdr;
OPTIMIZE TABLE cel;
OPTIMIZE TABLE queue_log;
```

---

### 5. fail2ban Deshabilitado

**Síntomas:**
- Errores de retransmisión SIP en logs de Asterisk
- Intentos de registro desde IPs desconocidas
- Posibles ataques de fuerza bruta

**Diagnóstico:**
```bash
systemctl status fail2ban
```

**Solución:**
```bash
systemctl enable fail2ban
systemctl start fail2ban
```

---

## Script de Automatización

Se incluye un script que aplica todas estas optimizaciones automáticamente:

**Ubicación:** `scripts/issabel4_optimize.sh`

**Uso básico:**
```bash
chmod +x issabel4_optimize.sh
./issabel4_optimize.sh
```

**Opciones:**
```bash
./issabel4_optimize.sh --help

Opciones:
  --purge-days N    Días de retención de datos (default: 90)
  --skip-purge      Omitir purga de datos antiguos
  --skip-indexes    Omitir creación de índices
  --skip-fixes      Omitir correcciones de PHP
  --dry-run         Solo mostrar qué se haría, sin ejecutar
```

**Ejemplos:**
```bash
# Solo ver qué haría sin ejecutar
./issabel4_optimize.sh --dry-run

# Purgar datos de más de 180 días
./issabel4_optimize.sh --purge-days 180

# Solo crear índices, sin purgar ni aplicar fixes
./issabel4_optimize.sh --skip-purge --skip-fixes
```

---

## Notas de Seguridad

1. **Siempre hacer backup** antes de aplicar cambios
2. El script crea backups automáticos en `/root/issabel_optimize_backup_FECHA/`
3. Los fixes de PHP se pueden revertir copiando los archivos `.bak`
4. Los datos purgados se respaldan comprimidos antes de eliminar

---

## Verificación Post-Aplicación

### Verificar índices:
```sql
SHOW INDEX FROM cdr;
SHOW INDEX FROM queue_log;
SHOW INDEX FROM cel;
```

### Verificar errores de PHP:
```bash
tail -f /var/log/httpd/ssl_error_log | grep -i "bridgestate\|undefined"
```

### Verificar fail2ban:
```bash
fail2ban-client status
fail2ban-client status asterisk
```

---

## Historial de Cambios

| Fecha | Versión | Cambios |
|-------|---------|---------|
| 2025-12-22 | 1.0 | Documentación inicial |
