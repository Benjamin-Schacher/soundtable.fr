FROM php:8.2-apache

# Activer le module rewrite d'Apache pour le routage personnalisé
RUN a2enmod rewrite

# Autoriser le fichier .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf


# Copier les fichiers de l'application dans le conteneur
COPY . /var/www/html/

# Donner les permissions nécessaires pour le dossier des commentaires et sitemap
RUN chown -R www-data:www-data /var/www/html/page/comments \
    && chown www-data:www-data /var/www/html/sitemap.xml \
    && chmod -R 755 /var/www/html/page/comments

# Exposer le port 80
EXPOSE 80
