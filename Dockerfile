FROM php:8.3-cli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies for yaml extension
RUN apt-get update && apt-get install -y --no-install-recommends git zip unzip libyaml-dev && rm -rf /var/lib/apt/lists/*

# Install yaml extension
#RUN pecl install yaml && docker-php-ext-enable yaml

# Install and enable pcov for code coverage
RUN pecl install pcov && docker-php-ext-enable pcov

ENV DOG_CEO_CACHE_KEY=test-key

WORKDIR /app