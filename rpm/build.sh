#!/bin/bash
# Script para construir el RPM de MikroTik Failover Module
# Uso: ./build.sh [version]

set -e

# Configuración
VERSION="${1:-1.0.0}"
PACKAGE_NAME="issabel-mikrotik-failover"
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_DIR="$( cd "$SCRIPT_DIR/.." && pwd )"
BUILD_DIR="$SCRIPT_DIR/build"
OUTPUT_DIR="$SCRIPT_DIR/output"

echo "========================================"
echo "Building $PACKAGE_NAME-$VERSION RPM"
echo "========================================"
echo ""

# Limpiar builds anteriores
echo "[1/5] Cleaning previous builds..."
rm -rf "$BUILD_DIR"
rm -rf "$OUTPUT_DIR"
mkdir -p "$BUILD_DIR"
mkdir -p "$OUTPUT_DIR"
mkdir -p "$SCRIPT_DIR/SOURCES"

# Crear estructura de archivos fuente
echo "[2/5] Preparing source files..."
SOURCE_DIR="$BUILD_DIR/$PACKAGE_NAME-$VERSION"
mkdir -p "$SOURCE_DIR"

# Copiar módulo principal
mkdir -p "$SOURCE_DIR/module"
cp -r "$PROJECT_DIR/mikrotik_failover/"* "$SOURCE_DIR/module/"

# Copiar módulo de logs
mkdir -p "$SOURCE_DIR/module_logs"
cp -r "$PROJECT_DIR/mikrotik_failover_logs/"* "$SOURCE_DIR/module_logs/"

# Copiar privileged helper
mkdir -p "$SOURCE_DIR/privileged"
cp "$PROJECT_DIR/privileged/mikrotikfailover" "$SOURCE_DIR/privileged/"

# Copiar module installer
mkdir -p "$SOURCE_DIR/module_installer/mikrotik_failover"
cp -r "$PROJECT_DIR/module_installer/mikrotik_failover/"* "$SOURCE_DIR/module_installer/mikrotik_failover/"

# Copiar systemd service
mkdir -p "$SOURCE_DIR/systemd"
cp "$PROJECT_DIR/systemd/mikrotik-failover.service" "$SOURCE_DIR/systemd/"

# Crear tarball
echo "[3/5] Creating source tarball..."
cd "$BUILD_DIR"
tar -czf "$SCRIPT_DIR/SOURCES/$PACKAGE_NAME-$VERSION.tar.gz" "$PACKAGE_NAME-$VERSION"

# Actualizar versión en spec si es necesario
sed -i.bak "s/^Version:.*/Version:        $VERSION/" "$SCRIPT_DIR/SPECS/issabel-mikrotik-failover.spec"
rm -f "$SCRIPT_DIR/SPECS/issabel-mikrotik-failover.spec.bak"

# Construir imagen Docker
echo "[4/5] Building Docker image..."
cd "$SCRIPT_DIR"
docker build -t rpm-builder-issabel .

# Ejecutar build del RPM
echo "[5/5] Building RPM package..."
docker run --rm \
    -v "$SCRIPT_DIR/SOURCES:/root/rpmbuild/SOURCES" \
    -v "$SCRIPT_DIR/SPECS:/root/rpmbuild/SPECS" \
    -v "$OUTPUT_DIR:/root/rpmbuild/RPMS" \
    rpm-builder-issabel

# Verificar resultado
echo ""
echo "========================================"
if [ -f "$OUTPUT_DIR/noarch/$PACKAGE_NAME-$VERSION"*.rpm ]; then
    echo "BUILD SUCCESSFUL!"
    echo "========================================"
    echo ""
    echo "RPM package created:"
    ls -la "$OUTPUT_DIR/noarch/"*.rpm
    echo ""
    echo "To install on Issabel server:"
    echo "  scp $OUTPUT_DIR/noarch/$PACKAGE_NAME-$VERSION*.rpm root@<server>:/tmp/"
    echo "  ssh root@<server> 'yum localinstall -y /tmp/$PACKAGE_NAME-$VERSION*.rpm'"
else
    echo "BUILD FAILED!"
    echo "========================================"
    echo "Check the output above for errors."
    exit 1
fi
