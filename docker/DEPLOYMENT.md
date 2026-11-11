# Despliegue del Entregable 2 con Docker

En una VM existente de Compute Engine con Docker Engine y Docker Compose v2, obtén el commit a desplegar. Ejecuta los comandos desde la raíz del repositorio.

1. Define `APP_URL` con la URL pública de la VM, `APP_PORT` con el puerto web (8080 por defecto), `APP_KEY` con una clave Laravel única y persistente, y `DB_PASSWORD` con una contraseña privada. Conserva estos valores en un archivo local fuera del repositorio, con permisos restringidos. Para generar la clave antes de iniciar la aplicación, ejecuta `docker run --rm php:8.4-cli php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'`. Para una instalación nueva puedes omitir `DB_DATABASE` y `DB_USERNAME`, que toman los valores `tienda_relojes` y `tienda`.
2. Valida la configuración, construye la imagen y migra la base. Sustituye `/ruta/tienda.env` por la ruta del archivo de variables.

   ~~~sh
   docker compose --env-file /ruta/tienda.env -f docker-compose.blockchain.yml -f docker-compose.laravel.yml config --quiet
   docker compose --env-file /ruta/tienda.env -f docker-compose.blockchain.yml -f docker-compose.laravel.yml up -d --build
   docker compose --env-file /ruta/tienda.env -f docker-compose.blockchain.yml -f docker-compose.laravel.yml exec -T app php artisan migrate --force
   ~~~

3. Comprueba `/up`, la portada, el carrito y un certificado. Conserva el volumen `mysql_data` y la clave entre despliegues. La base se inicia vacía. El seeder de demostración crea cuentas con contraseñas conocidas y se reserva para un entorno aislado.
4. En GCP, la persona con acceso debe confirmar la regla de firewall de entrada para el puerto web y el acceso a los nodos según el acuerdo con el equipo consumidor. Limita el acceso público a los puertos requeridos. El Compose original publica los puertos de los tres nodos. Comparte con el docente la URL verificada y el enlace al repositorio.

Laravel accede a MySQL por la red interna. Los nodos se resuelven por los nombres de servicio de su Compose. Se puede indicar `PLANTS_API_URL` si el equipo aliado publica otra URL. La aplicación usa ExchangeRate-API por defecto y vuelve a mostrar COP si falta una tasa utilizable.
