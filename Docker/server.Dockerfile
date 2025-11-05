FROM nginx:alpine
COPY public /var/www/public
COPY storage /var/www/storage
ADD nginx/default.conf /etc/nginx/conf.d/default.conf
RUN sed -i '/http {/a\    client_max_body_size 10M;' /etc/nginx/nginx.conf
WORKDIR /var/www/