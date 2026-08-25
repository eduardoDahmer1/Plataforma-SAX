# Cola de preparación masiva con IA

Después de desplegar el código, ejecutar:

```bash
php artisan migrate
```

La generación masiva utiliza la conexión `product-ai` y la tabla aislada
`product_ai_jobs`. En producción deben mantenerse dos procesos administrados
por Supervisor (o por el gestor equivalente del servidor):

```bash
php artisan queue:work product-ai --queue=product-ai --sleep=2 --tries=3 --timeout=150
```

El valor `retry_after` de esta conexión es 210 segundos, deliberadamente mayor
que el timeout del worker, para evitar que un mismo producto se procese dos veces.

Después de cada despliegue que modifique jobs o servicios, reiniciar los workers:

```bash
php artisan queue:restart
```

La IA nunca cambia un producto de inactivo a activo. La activación continúa
dependiendo del guardado administrativo y de las reglas de fotografía, precio,
stock y descripción existentes.
