FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=America/Argentina/San_Juan

# Instala PHP 8.3 y extensiones comunes para Laravel, incluyendo SQLite
RUN apt-get update && \
    apt-get install -y software-properties-common tzdata && \
    add-apt-repository ppa:ondrej/php -y && \
    apt-get update && \
    apt-get install -y \
        php8.3 php8.3-cli php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip \
        php8.3-sqlite3 php8.3-pdo php8.3-pdo-mysql php8.3-gd php8.3-intl php8.3-readline \
        openssl curl git unzip composer

# Limpieza
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer (por si acaso)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer