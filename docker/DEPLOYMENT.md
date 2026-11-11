# Despliegue del Entregable 2 con Docker

En una VM existente de Compute Engine con Docker Engine y Docker Compose v2, obtén el commit a desplegar. Ejecuta los comandos desde la raíz del repositorio.

1. Define `APP_URL` con la URL pública de la VM, `APP_PORT` con el puerto web (8080 por defecto), `APP_KEY` con una clave Laravel única y persistente, y `DB_PASSWORD` con una contraseña privada. Conserva estos valores en un archivo local fuera del repositorio, con permisos restringidos. Para generar la clave antes de iniciar la aplicación, ejecuta `docker run --rm php:8.4-cli php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'`. Para una instalación nueva puedes omitir `DB_DATABASE` y `DB_USERNAME`, que toman los valores `tienda_relojes` y `tienda`.
   Genera valores aleatorios distintos para `BLOCKCHAIN_PEER_TOKEN` y `BLOCKCHAIN_ISSUER_TOKEN`. El primero queda entre los nodos. Laravel y los emisores autorizados usan el segundo como `Authorization: Bearer <token>` al llamar a `/transactions/new` y `/mine` en el nodo principal. Usa TLS para emisores externos.
2. Valida la configuración, construye la imagen y migra la base. Sustituye `/ruta/tienda.env` por la ruta del archivo de variables.

   ~~~sh
   docker compose --env-file /ruta/tienda.env -f docker-compose.blockchain.yml -f docker-compose.laravel.yml config --quiet
   docker compose --env-file /ruta/tienda.env -f docker-compose.blockchain.yml -f docker-compose.laravel.yml up -d --build
   docker compose --env-file /ruta/tienda.env -f docker-compose.blockchain.yml -f docker-compose.laravel.yml exec -T app php artisan migrate --force
   ~~~

3. Comprueba `/up`, la portada, el carrito y un certificado. Conserva los volúmenes `mysql_data`, `blockchain_store`, `blockchain_auditor` y `blockchain_notary`, el nombre del proyecto Compose y la clave entre despliegues. La base se inicia vacía. El seeder de demostración crea cuentas con contraseñas conocidas y se reserva para un entorno aislado.
4. En GCP, la persona con acceso debe confirmar la regla de firewall de entrada para el puerto web y el acceso a los nodos según el acuerdo con el equipo consumidor. Limita el acceso público a los puertos requeridos. El Compose original publica los puertos de los tres nodos. Comparte con el docente la URL verificada y el enlace al repositorio.

Laravel accede a MySQL por la red interna. Los nodos se resuelven por los nombres de servicio de su Compose. Se puede indicar `PLANTS_API_URL` si el equipo aliado publica otra URL. La aplicación usa ExchangeRate-API por defecto y vuelve a mostrar COP si falta una tasa utilizable.

## Cadena histórica

Si hay nodos de la versión en memoria, restringe el acceso a sus puertos y pausa la emisión antes de reiniciarlos o sustituirlos. Confirma que las emisiones pendientes se hayan minado y exporta el JSON íntegro de `/chain` desde cada nodo mientras siga activo. Conserva las exportaciones originales en almacenamiento de acceso restringido porque pueden contener datos personales. Compara los hashes, la longitud y los certificados registrados con los de Laravel. Si hay cadenas divergentes, conserva las copias y resuelve la divergencia antes de desplegar.

Para cargar un respaldo validado en volúmenes nuevos, coloca la cadena original, incluyendo el génesis, en `/data/state.json` de cada nodo con la estructura `{"chain": [...], "pending": []}`. Usa `pending: []` cuando se haya confirmado que no quedan transacciones pendientes. Protege el archivo con permisos restrictivos y conserva el respaldo aparte. El nodo valida los hashes y los enlaces antes de servir la cadena. Si un archivo existente falla la validación, el arranque se detiene y se conserva el archivo. Si todos los nodos antiguos se apagaron sin un respaldo, los registros de Laravel conservan los certificados pero no contienen la cadena original completa.

`/verify/{hash}` y `/chain` sirven proyecciones públicas. La verificación de integridad usa los bloques originales almacenados. Para las transacciones históricas, las proyecciones omiten los datos del comprador y enmascaran las referencias a pedidos y líneas incrustadas en seriales de la tienda. El intercambio de bloques originales usa rutas autenticadas entre pares. Configura los pares autorizados en `PEERS`.
