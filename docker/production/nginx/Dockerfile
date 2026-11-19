# Stage 1: Build assets
FROM node:22-bookworm-slim AS builder

RUN apt-get update && apt-get upgrade -y \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY . /var/www

RUN npm install --global npm@12.2.2 --ignore-scripts \
    && npm ci --ignore-scripts && npm run build

# Stage 2: Nginx production image
FROM nginx:1.30.7-alpine

RUN apk upgrade --no-cache

COPY ./docker/production/nginx/nginx.conf /etc/nginx/nginx.conf

# Only public/ is served; the PHP code lives in the php-fpm image
COPY --from=builder /var/www/public /var/www/public

WORKDIR /var/www/public

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
