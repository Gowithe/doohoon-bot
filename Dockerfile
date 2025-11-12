FROM php:8.1-cli

WORKDIR /app

RUN apt-get update && apt-get install -y curl

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && composer install

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080"]
