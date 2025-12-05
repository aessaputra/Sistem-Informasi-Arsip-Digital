# =============================================================================
# FrankenPHP Dockerfile for Laravel Application
# PHP 8.3 with Alpine Linux
# Supports: development, production targets
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1: Base with PHP extensions
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:php8.3-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    # Required for zip extension (maatwebsite/excel)
    libzip-dev \
    zip \
    unzip \
    # Required for GD extension (dompdf, excel)
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    # Required for intl extension
    icu-dev \
    # Required for xml extension (excel)
    libxml2-dev \
    # Database clients
    mysql-client \
    # Common utilities
    curl \
    bash \
    git

# Install PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    pcntl \
    exif

# Set working directory
WORKDIR /app

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------------------------------
# Stage 2: Development
# -----------------------------------------------------------------------------
FROM base AS development

# Install development dependencies and Xdebug
RUN apk add --no-cache linux-headers $PHPIZE_DEPS \
    && install-php-extensions xdebug

# Copy development PHP configuration
COPY docker/php/php.dev.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Copy Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Development: Disable HTTPS, use HTTP only
ENV SERVER_NAME=":80"
ENV CADDY_GLOBAL_OPTIONS="auto_https off"

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

# -----------------------------------------------------------------------------
# Stage 3: Builder (for production)
# -----------------------------------------------------------------------------
FROM base AS builder

# Copy composer files first for caching
COPY composer.json composer.lock ./

# Install dependencies (no dev)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

# Copy application source
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# -----------------------------------------------------------------------------
# Stage 4: Node builder for frontend assets
# -----------------------------------------------------------------------------
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy source files needed for build
COPY resources ./resources
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY public ./public

# Build frontend assets
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 5: Production (Security Hardened)
# -----------------------------------------------------------------------------
FROM base AS production

# Production PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Copy application from builder
COPY --from=builder /app /app

# Copy built frontend assets
COPY --from=node-builder /app/public/build /app/public/build

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# -----------------------------------------------------------------------------
# Security: Run as non-root user (Best Practice)
# Reference: https://frankenphp.dev/docs/docker/
# -----------------------------------------------------------------------------
ARG USER=www-data

# Setup non-root user with required capabilities
RUN set -eux; \
    # Add capability to bind to privileged ports (80/443) as non-root
    apk add --no-cache libcap; \
    setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
    # Create storage directories with proper ownership
    mkdir -p storage/framework/{cache,sessions,views}; \
    mkdir -p storage/logs; \
    mkdir -p bootstrap/cache; \
    # Set ownership for application directories
    chown -R ${USER}:${USER} /app; \
    chown -R ${USER}:${USER} /data/caddy; \
    chown -R ${USER}:${USER} /config/caddy; \
    # Set proper permissions
    chmod -R 775 storage bootstrap/cache; \
    chmod +x /usr/local/bin/entrypoint.sh

# Switch to non-root user
USER ${USER}

# Production settings
ENV APP_ENV=production
ENV SERVER_NAME=":80"

# Enable worker mode for production (keeps PHP in memory)
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

EXPOSE 80 443

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

