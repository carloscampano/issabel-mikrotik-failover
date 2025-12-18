# Dockerfile para construir RPM de MikroTik Failover Module
# Basado en CentOS 7 (compatible con Issabel 4)

FROM centos:7

# Configurar repositorios (CentOS 7 EOL workaround)
RUN sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-*.repo && \
    sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-*.repo

# Instalar herramientas de desarrollo RPM
RUN yum install -y \
    rpm-build \
    rpmdevtools \
    make \
    && yum clean all

# Crear estructura de directorios rpmbuild
RUN rpmdev-setuptree

# Directorio de trabajo
WORKDIR /root/rpmbuild

# Copiar archivos fuente
COPY SOURCES/ /root/rpmbuild/SOURCES/
COPY SPECS/ /root/rpmbuild/SPECS/

# Comando por defecto: construir el RPM
CMD ["rpmbuild", "-ba", "/root/rpmbuild/SPECS/issabel-mikrotik-failover.spec"]
