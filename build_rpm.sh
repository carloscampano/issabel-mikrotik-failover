#!/bin/bash
# =============================================================================
# MikroTik Failover Module - RPM Build Script
# Supports both Issabel 4 (CentOS 7) and Issabel 5 (Rocky Linux 8)
# =============================================================================

set -e

VERSION="1.0.34"
PACKAGE="issabel-mikrotik-failover"
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

usage() {
    echo "Usage: $0 [issabel4|issabel5|both]"
    echo ""
    echo "Options:"
    echo "  issabel4  - Build RPM for Issabel 4 (CentOS 7, PHP 5.x)"
    echo "  issabel5  - Build RPM for Issabel 5 (Rocky Linux 8, PHP 7.4)"
    echo "  both      - Build RPMs for both versions"
    echo ""
    exit 1
}

create_tarball() {
    local version=$1
    local build_dir="/tmp/rpm_build_${version}"

    echo -e "${YELLOW}Creating source tarball for $PACKAGE-$VERSION...${NC}" >&2

    rm -rf "$build_dir"
    mkdir -p "$build_dir/$PACKAGE-$VERSION"

    # Copy module files
    mkdir -p "$build_dir/$PACKAGE-$VERSION/module"
    cp -r "$SCRIPT_DIR/mikrotik_failover/"* "$build_dir/$PACKAGE-$VERSION/module/"
    rm -rf "$build_dir/$PACKAGE-$VERSION/module/SOURCES"
    rm -f "$build_dir/$PACKAGE-$VERSION/module/test_script_upload"*.php

    # Copy logs module
    mkdir -p "$build_dir/$PACKAGE-$VERSION/module_logs"
    cp -r "$SCRIPT_DIR/mikrotik_failover_logs/"* "$build_dir/$PACKAGE-$VERSION/module_logs/"

    # Copy privileged helper
    mkdir -p "$build_dir/$PACKAGE-$VERSION/privileged"
    cp "$SCRIPT_DIR/privileged/mikrotikfailover" "$build_dir/$PACKAGE-$VERSION/privileged/"

    # Copy module installer
    mkdir -p "$build_dir/$PACKAGE-$VERSION/module_installer/mikrotik_failover"
    cp -r "$SCRIPT_DIR/module_installer/mikrotik_failover/"* "$build_dir/$PACKAGE-$VERSION/module_installer/mikrotik_failover/"

    # Copy systemd service
    mkdir -p "$build_dir/$PACKAGE-$VERSION/systemd"
    cp "$SCRIPT_DIR/systemd/mikrotik-failover.service" "$build_dir/$PACKAGE-$VERSION/systemd/"

    # Create tarball (without macOS extended attributes)
    cd "$build_dir"
    tar --no-xattrs --no-mac-metadata -czf "$PACKAGE-$VERSION.tar.gz" "$PACKAGE-$VERSION" 2>/dev/null || \
    tar -czf "$PACKAGE-$VERSION.tar.gz" "$PACKAGE-$VERSION"

    echo "$build_dir/$PACKAGE-$VERSION.tar.gz"
}

build_issabel4() {
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}Building RPM for Issabel 4 (CentOS 7)${NC}"
    echo -e "${GREEN}========================================${NC}"

    local tarball=$(create_tarball "issabel4")
    local output_dir="$SCRIPT_DIR/rpm/output/noarch"

    mkdir -p "$output_dir"
    cp "$tarball" "$SCRIPT_DIR/rpm/SOURCES/"

    # Build using Docker
    cd "$SCRIPT_DIR/rpm"
    if ! docker images | grep -q rpm-builder-issabel; then
        echo -e "${YELLOW}Building Docker image...${NC}"
        docker build -t rpm-builder-issabel .
    fi

    docker run --rm \
        -v "$SCRIPT_DIR/rpm/SOURCES:/root/rpmbuild/SOURCES" \
        -v "$SCRIPT_DIR/rpm/SPECS:/root/rpmbuild/SPECS" \
        -v "$output_dir:/root/rpmbuild/RPMS/noarch" \
        rpm-builder-issabel

    echo -e "${GREEN}Issabel 4 RPM created: $output_dir/${NC}"
    ls -la "$output_dir/"*.rpm 2>/dev/null | tail -1
}

build_issabel5() {
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}Building RPM for Issabel 5 (Rocky 8)${NC}"
    echo -e "${GREEN}========================================${NC}"

    local tarball=$(create_tarball "issabel5")
    local output_dir="$SCRIPT_DIR/rpm_issabel5/output"
    local spec_file="$SCRIPT_DIR/rpm_issabel5/SPECS/issabel-mikrotik-failover.spec"

    mkdir -p "$output_dir"

    echo -e "${YELLOW}Note: Issabel 5 RPM requires building on Rocky Linux 8 or compatible system${NC}"
    echo -e "${YELLOW}Tarball created at: $tarball${NC}"
    echo -e "${YELLOW}Spec file: $spec_file${NC}"
    echo ""
    echo "To build on an Issabel 5 server, run:"
    echo "  1. Copy tarball to ~/rpmbuild/SOURCES/"
    echo "  2. Copy spec to ~/rpmbuild/SPECS/"
    echo "  3. rpmbuild -ba ~/rpmbuild/SPECS/issabel-mikrotik-failover.spec"

    # Check if we can build locally (Rocky 8 or similar)
    if [ -f /etc/rocky-release ] || [ -f /etc/redhat-release ]; then
        if grep -q "release 8" /etc/redhat-release 2>/dev/null; then
            echo -e "${GREEN}Detected Rocky/RHEL 8, building locally...${NC}"
            mkdir -p ~/rpmbuild/{SOURCES,SPECS,RPMS}
            cp "$tarball" ~/rpmbuild/SOURCES/
            cp "$spec_file" ~/rpmbuild/SPECS/
            rpmbuild -ba ~/rpmbuild/SPECS/issabel-mikrotik-failover.spec
            cp ~/rpmbuild/RPMS/noarch/*.rpm "$output_dir/"
            echo -e "${GREEN}Issabel 5 RPM created: $output_dir/${NC}"
            ls -la "$output_dir/"*.rpm 2>/dev/null | tail -1
        fi
    fi
}

# Main
case "${1:-both}" in
    issabel4)
        build_issabel4
        ;;
    issabel5)
        build_issabel5
        ;;
    both)
        build_issabel4
        echo ""
        build_issabel5
        ;;
    *)
        usage
        ;;
esac

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Build complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "RPM locations:"
echo "  Issabel 4: $SCRIPT_DIR/rpm/output/noarch/"
echo "  Issabel 5: $SCRIPT_DIR/rpm_issabel5/output/"
