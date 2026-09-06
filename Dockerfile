FROM php:8.2-apache

RUN a2enmod rewrite

RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY . /var/www/html/

# Donner les permissions nécessaires pour le dossier des commentaires et sitemap
RUN mkdir -p /var/www/html/asset/uploads \
    && chown -R www-data:www-data /var/www/html/page/comments \
    && chown -R www-data:www-data /var/www/html/asset/uploads \
    && chown www-data:www-data /var/www/html/sitemap.xml \
    && chmod -R 755 /var/www/html/page/comments \
    && chmod -R 755 /var/www/html/asset/uploads

EXPOSE 80
