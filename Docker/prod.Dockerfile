FROM php:8.3-fpm-bookworm

# Set working directory
WORKDIR /var/www/

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    libonig-dev \
    libzip-dev \
    libpq-dev \
    jpegoptim optipng pngquant gifsicle \
    ca-certificates \
    vim \
    tmux \
    unzip \
    git \
    curl \
    supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_pgsql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/
RUN docker-php-ext-install gd
RUN pecl install -o -f redis &&  rm -rf /tmp/pear && docker-php-ext-enable redis

ENV HOME=/tmp

RUN mkdir -p /tmp/.config/libreoffice && \
    chown -R www-data:www-data /tmp/.config && \
    chmod -R 700 /tmp/.config
# ENV HOME=/tmp

# for word to pdf
RUN apt-get update && apt-get install -y --no-install-recommends \
    imagemagick \
    libmagickwand-dev \
    libreoffice-core \
    libreoffice-writer \
    libreoffice-common \
    fonts-dejavu \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# RUN libreoffice --headless --invisible --nodefault --nofirststartwizard --nologo

RUN pecl install imagick && docker-php-ext-enable imagick

#setup php settings
RUN echo "upload_max_filesize = 20M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 25M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/custom-timeout.ini \
    && echo "request_terminate_timeout = 60" >> /usr/local/etc/php-fpm.d/www.conf

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create necessary directories
RUN mkdir -p /var/www/app/Modules

# Copy project files into the container
COPY . /var/www/

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache \
    && chmod -R 755 /var/www/public

RUN chown -R www-data:www-data /var/log/supervisor

# Install dependencies with composer
RUN composer install --ignore-platform-reqs
RUN composer update --ignore-platform-reqs

RUN php artisan storage:link

# # Expose port 8000
# EXPOSE 8000

COPY supervisor/ /etc/

# Copy entrypoint script and give execution permission
COPY prod-docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/prod-docker-entrypoint.sh

USER www-data
# Run entrypoint script
ENTRYPOINT ["prod-docker-entrypoint.sh"]