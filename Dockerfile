FROM php:8.1-cli

WORKDIR /app

# Install curl
RUN apt-get update && apt-get install -y curl

# Copy files
COPY . .

# Install composer dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

# Expose port
EXPOSE 8080

# Start server
CMD ["php", "-S", "0.0.0.0:8080"]
