# Atributos de productos SAX

Implementación en `/var/www/html/sax_stage`. El diccionario es global para todos
los productos, incluso sin categoría. `CATALOG_ATTRIBUTES_ENABLED=false`
deshabilita el sistema. No se agregaron filtros ni presentación pública.

## Modelo de cuatro tablas

| Tabla | Responsabilidad |
| --- | --- |
| `catalog_attributes` | Código estable, tipo, unidad, activación, herencia y referencia a `languages`. |
| `catalog_attribute_options` | Opciones normalizadas y referencia a `languages`. |
| `product_attribute_values` | Valor propio por producto/atributo, origen, evidencia y modo de herencia. |
| `product_attribute_value_options` | Selecciones simples/múltiples normalizadas. |

La migración original `2026_10_19_120000_create_catalog_product_attributes.php`
crea únicamente estas cuatro tablas. Editarla **no actualiza** un esquema donde
ya se ejecutó. El procedimiento para Stage está en
[stage-attributes-global-update.md](stage-attributes-global-update.md).
La migración de modo de lotes `2026_10_19_120100` se conserva sin cambios.

## Editor y traducciones

El dropdown muestra los atributos activos no seleccionados, ordenados por
nombre. Busca por código y etiquetas ES/PT/EN, sin consultar categorías ni
subcategorías. Crear/editar atributos y opciones y gestionar el diccionario
continúa disponible en el modal del editor y en `/admin/catalog-attributes`.
Master y Editor conservan el acceso.

Definiciones y opciones se guardan inmediatamente; sus asignaciones esperan
al guardado del producto. Las etiquetas usan `languages` (`es`, `pt`, `en`),
con claves protegidas y actualización de revisión/caché. Las asignaciones sin
cambios no se reescriben. Los valores existentes de atributos desactivados
continúan visibles y se conserva la validación de opciones históricas.

El editor del producto presenta los atributos en una cuadrícula responsive. El
valor se edita directamente; la selección múltiple usa un menú cerrado con
búsqueda, casillas y botón **Listo**, que también se cierra con Escape o al pulsar
fuera. El menú **⋯** de cada atributo reúne la edición del diccionario, sus
opciones seleccionadas y las decisiones de herencia. La etiqueta discreta
**IA** conserva la misma procedencia; abrir o cerrar menús no cambia valores ni
comprobantes. El resumen de valores se muestra al plegar la sección.

La prueba de navegador `tests/js/catalog-attributes-editor.browser.cjs` recibe
un HTML del editor renderizado desde STAGE mediante `SAX_ATTRIBUTE_EDITOR_HTML`.
Requiere Playwright (o `SAX_PLAYWRIGHT_MODULE`) y un Chromium disponible
(`SAX_CHROMIUM_PATH`, si no se usa el de Playwright). Intercepta todas las
peticiones: no guarda productos, definiciones ni traducciones. Comprueba los
cuatro tipos de valor, selecciones vacías, navegación por teclado, cierre de
menús, herencia, validación del diccionario, procedencia y comprobantes IA,
serialización sin reescribir valores intactos y anchos de 320, 390, 768 y 1440 px.

## Herencia e IA

`CatalogAttributeService::effective()` resuelve solo `parent_id`, con protección
contra ciclos. `inherit_from_parent` determina qué atributos se heredan.
El valor propio prevalece; `block` impide heredar y `inherit` restablece la
herencia. Se conservan las filas de restablecimiento para sincronización.
`color_parent_id` no participa.

`missing()` considera todos los atributos activos del diccionario y excluye
valores efectivos y decisiones manuales. `aiContext()` entrega
`missing_attribute_ids`, diccionario activo, valores efectivos y `protected_ids`.
La IA debe omitir atributos que no correspondan al producto, sin crear códigos
ni opciones y sin deducir características por categoría. Se mantienen:

- Confianza alta y evidencia textual con URL de una fuente consultada.
- Coincidencia de SKU y variante exactos, validación de tipo y unidad.
- Protección de valores propios, heredados, bloqueos y decisiones manuales.
- Aplicación solo al SKU investigado, sin propagar medidas a otras tallas.

El botón del editor devuelve propuestas al formulario. El modo de lotes
**Solo completar atributos faltantes** conserva nombres, descripciones y
preparaciones. Omite productos sin atributos activos faltantes o con sistema
no disponible. Un diccionario global puede ofrecer atributos irrelevantes para
ciertos productos: la IA debe omitirlos, por lo que un faltante no implica que
el producto esté incompleto ni garantiza que la investigación produzca valores.

## Propuestas IA en el editor

Las fuentes visibles se limitan a tres. El servicio conserva todas las fuentes
consultadas en `research.validation_sources` para validar la evidencia; los dos
endpoints del editor las consumen en Laravel y no envían esa lista al navegador.
`attribute_review` informa cantidades y motivos por propuesta. Las reglas son las
mismas para el editor y los lotes: evidencia textual, URL consultada, confianza
alta, SKU y variante exactos, opciones activas y ausencia de decisiones previas.

Los valores aceptados llevan `catalog_values[*][ai_proposal]`, un comprobante
cifrado y autenticado por Laravel, ligado al producto, su identidad, el valor y
la revisión del atributo, con vencimiento de 24 horas. Guardar el formulario
conserva `source=ai` y la evidencia cuando el valor coincide y sigue sin existir
una asignación protegida. Un comprobante alterado, vencido o de otra variante
produce un error de validación; no se convierte silenciosamente en manual.
Cambiar el valor o el modo en el editor elimina el comprobante y guarda esa
decisión como manual. No se agregan tablas, atributos ni opciones.

Ambos botones investigan con los datos actuales del formulario, aunque todavía
no se hayan guardado. Laravel valida ese contexto y lo aplica a una copia en
memoria del producto, sin guardarlo. Si cambian los datos investigados durante
la consulta, la respuesta no se aplica y se puede volver a generar sin guardar. La
sección de atributos se abre al recibir resultados y muestra sugeridos,
aceptados, rechazados y sus motivos, incluso cuando no se encontraron valores.

El prompt editorial solicita normalmente 100–160 palabras por idioma en dos o
tres párrafos cuando haya suficientes hechos verificados. Con evidencia escasa
permite menos texto, sin relleno ni características inventadas. Se mantienen el
modelo y los límites de la API.

## Seed, integración y sincronización

`catalog:seed-optical-attributes` conserva su nombre y vocabulario óptico inicial,
pero no requiere una categoría ERP ni guarda asociaciones con categorías.
Es aditivo y no asigna valores a productos.

`CatalogAttributeSyncService` comprueba únicamente las cuatro tablas. Resuelve
traducciones por `languages.key`, atributos/opciones por código y productos por
SKU. Ya no lee, valida ni replica asociaciones con categorías. Conserva
revisiones, timestamps, conflictos manual/IA y modos de herencia.

`OpticalProductSyncService` conserva su alcance óptico para la sincronización de
productos entre tiendas; esto es independiente del diccionario global.
`catalog:sync-attributes` conserva el scheduler existente. Stage debe mantener
`CATALOG_PEER_SYNC_ENABLED=false` durante esta actualización.
No se cambió el integrador ni sus upserts. No se reutiliza `products.attributes`.

## Validación de esta simplificación

Sin bootstrap de Laravel, conexiones PDO, SQL ejecutado ni llamadas externas:

```bash
php vendor/bin/phpunit --do-not-cache-result tests/Unit/CatalogAttributesGlobalTest.php
node --check public/js/catalog-attributes-admin.js
node --test tests/js/catalog-attributes-global.test.cjs
```

La prueba PHP verifica la definición y reversión de las cuatro tablas mediante
un doble de Schema, activación independiente de categoría, faltantes globales,
protecciones del contexto IA, herencia real del servicio (sobrescritura,
restablecimiento, bloqueo y aislamiento de color) y requisitos de sincronización
mediante dobles.
La prueba JavaScript ejecuta las funciones reales del dropdown con DOM simulado:
activación, exclusión de seleccionados, orden, selección, búsqueda traducida y
estado vacío sin elementos de categoría.

`tests/Feature/CatalogAttributesTest.php` se adaptó al esquema global. No se
ejecutó en esta tarea porque crea esquemas y escribe fixtures, aunque use SQLite
en memoria. Tampoco se ejecutaron Artisan, migraciones, consultas MySQL,
pruebas de guardado real, generaciones IA ni sincronización entre tiendas.
Las comprobaciones funcionales del operador figuran en el procedimiento manual.

## Archivos de esta simplificación

- Migración original: `database/migrations/2026_10_19_120000_create_catalog_product_attributes.php`.
- Modelo: `app/Models/CatalogAttribute.php`; eliminado `app/Models/CatalogAttributeRecommendation.php`.
- Servicios: `app/Services/CatalogAttributeService.php`, `CatalogAttributeSyncService.php`,
  `OpenAICatalogService.php`, `ProductAiBatchDraftBuilder.php`.
- Controlador: `app/Http/Controllers/Admin/CatalogAttributeController.php`.
- Seed: `app/Console/Commands/SeedOpticalAttributes.php`.
- Configuración: `config/catalog-attributes.php`.
- Editor: `public/js/catalog-attributes-admin.js` y las vistas
  `resources/views/admin/catalog-attributes/{index,modal,product}.blade.php`.
- Prueba adaptada: `tests/Feature/CatalogAttributesTest.php` (no ejecutada).
- Pruebas nuevas sin base de datos: `tests/Unit/CatalogAttributesGlobalTest.php`
  y `tests/js/catalog-attributes-global.test.cjs`.
- Documentación: este archivo y `docs/stage-attributes-global-update.md`.

Se preservaron los cambios previos del árbol de trabajo. No se modificaron
otros ambientes ni se crearon nuevas migraciones.
