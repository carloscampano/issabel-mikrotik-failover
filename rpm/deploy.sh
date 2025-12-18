#!/bin/bash
# Script para desplegar el RPM al servidor de pruebas
# Uso: ./deploy.sh <server_ip> [user] [password]

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
OUTPUT_DIR="$SCRIPT_DIR/output/noarch"

# Parámetros
SERVER_IP="${1:-192.168.25.111}"
SERVER_USER="${2:-root}"
SERVER_PASS="${3:-}"

# Buscar el RPM más reciente
RPM_FILE=$(ls -t "$OUTPUT_DIR"/*.rpm 2>/dev/null | head -1)

if [ -z "$RPM_FILE" ]; then
    echo "Error: No RPM file found in $OUTPUT_DIR"
    echo "Run ./build.sh first to create the RPM package."
    exit 1
fi

RPM_NAME=$(basename "$RPM_FILE")

echo "========================================"
echo "Deploying to Issabel Server"
echo "========================================"
echo ""
echo "Server: $SERVER_USER@$SERVER_IP"
echo "Package: $RPM_NAME"
echo ""

# Verificar si sshpass está instalado
if [ -n "$SERVER_PASS" ] && ! command -v sshpass &> /dev/null; then
    echo "Warning: sshpass not installed. You'll be prompted for password."
    echo "Install with: brew install hudochenkov/sshpass/sshpass (macOS)"
    echo ""
fi

# Función para ejecutar comando SSH
ssh_cmd() {
    if [ -n "$SERVER_PASS" ] && command -v sshpass &> /dev/null; then
        sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_IP" "$1"
    else
        ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_IP" "$1"
    fi
}

# Función para copiar archivo SCP
scp_cmd() {
    if [ -n "$SERVER_PASS" ] && command -v sshpass &> /dev/null; then
        sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no "$1" "$SERVER_USER@$SERVER_IP:$2"
    else
        scp -o StrictHostKeyChecking=no "$1" "$SERVER_USER@$SERVER_IP:$2"
    fi
}

echo "[1/4] Copying RPM to server..."
scp_cmd "$RPM_FILE" "/tmp/"

echo "[2/4] Stopping daemon if running..."
ssh_cmd "systemctl stop mikrotik-failover 2>/dev/null || true"

echo "[3/4] Installing RPM..."
ssh_cmd "yum localinstall -y /tmp/$RPM_NAME"

echo "[4/4] Cleaning up..."
ssh_cmd "rm -f /tmp/$RPM_NAME"

echo ""
echo "========================================"
echo "DEPLOYMENT COMPLETE!"
echo "========================================"
echo ""
echo "Access the module at:"
echo "  https://$SERVER_IP/index.php?menu=mikrotik_failover"
echo ""
echo "Or navigate to: PBX > PBX Configuration > MikroTik Failover"
echo ""
