#!/bin/sh
set -e

# var/ vit sur un volume Docker nommé (voir docker-compose.yml) et est donc
# recréé avec les permissions par défaut (root) à chaque nouveau volume.
# php-fpm tourne en www-data : sans ce chown, Symfony ne peut pas écrire
# son cache/logs et toutes les requêtes échouent en 500.
if [ -d /var/www/html/var ]; then
    chown -R www-data:www-data /var/www/html/var
fi

exec "$@"
