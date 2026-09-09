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

## Configuración de IA por sitio

El administrador principal puede entrar en **Sistema → Configuración de IA**
(`/admin/configuracion-ia`) para activar o desactivar la generación individual
y por lote. Cada instalación (SAX, Óptica o stage) guarda sus propios valores
en su base de datos; cambiar el perfil visual no copia claves entre sitios.

Se puede elegir la clave del servidor (`OPENAI_API_KEY`) o guardar una clave
desde el panel. La clave del panel se cifra con `APP_KEY`, no se devuelve al
navegador ni se conserva en la sesión después de errores de validación.
Un campo vacío conserva la clave anterior; existe una opción para eliminarla.
La migración conserva el funcionamiento previo usando la clave del servidor.

Al desactivar la IA, se bloquean nuevas generaciones y los jobs pendientes
se liberan durante 60 segundos, sin marcar productos como fallidos. Al volver
a activarla, continúan con la configuración actual. Las solicitudes que ya
se enviaron al proveedor pueden terminar. Los fallos de ejecución mantienen
un límite de tres excepciones, independiente de las pausas.

En el primer despliegue de estos controles, dejar terminar los lotes antiguos
antes de reiniciar los workers: sus jobs serializados conservan el límite
anterior de tres intentos. Aplicar la migración y reiniciar los workers en
cada instalación al publicar. Durante desarrollo, hacerlo solo en stage.

## Filtros del selector

Además de búsqueda, marca, categoría, subcategoría y preparación con IA,
**Más filtros** permite combinar publicación, disponibilidad, cantidades de
stock, rango de precios en USD, presencia de descripción o referencia,
outlet, relación de tallas y fechas de creación (ambos extremos incluidos).
También se puede ordenar por fecha, precio, nombre o stock y mostrar 20,
30, 50 o 100 productos por página. Limpiar filtros no borra la selección.
