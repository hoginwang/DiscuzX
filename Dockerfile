FROM php:7.2-apache
WORKDIR /
RUN apt-get update
RUN apt-get install -y --allow-unauthenticated git
RUN git clone https://gitee.com/ComsenzDiscuz/DiscuzX.git
RUN rm -rf /var/www/html && mv /DiscuzX/upload /var/www/html && rm -rf /DiscuzX
RUN chmod 777 -R /var/www/html
RUN docker-php-ext-install mysqli

EXPOSE 80