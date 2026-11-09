# Generación de IA desde el editor

La revisión del 6 de noviembre de 2026 encontró en sax_plataforma:

- OPENAI_TIMEOUT=100 y fastcgi_read_timeout=30s en el sitio Nginx de saxdepartment.com.
- OPENAI_REASONING_EFFORT=low, pero el catálogo utilizaba el valor independiente
  predeterminado medium porque OPENAI_CATALOG_REASONING_EFFORT estaba ausente.
- OPENAI_MAX_OUTPUT_TOKENS=10000. Los logs del 2 al 4 de noviembre contienen
  respuestas incompletas, pero el código de esas fechas no registraba la razón
  devuelta por OpenAI. No se puede atribuir cada una de ellas a tokens o filtros.

El editor ahora inicia un trabajo en la conexión y cola product-ai ya existentes
y consulta su estado con peticiones cortas. Incluye los datos sin guardar del
formulario. La consulta de estado está vinculada al usuario y producto. La misma
petición o una repetida con el mismo contexto en curso reutiliza el trabajo.
El navegador recupera cortes temporales al consultar el estado sin volver a
generar. Los resultados y su estado expiran de la caché después de 30 minutos.
Un trabajo que no empieza en diez minutos deja de ser válido; el editor informa
la demora para que se revise la cola. Los lotes grandes pueden demorar el editor
porque comparten los workers existentes.

OpenAIResponseClient permite hasta tres intentos dentro de un presupuesto total
de 120 segundos, limitado a 125 para dejar margen al worker de 150 segundos.
Reintenta errores de conexión, HTTP temporales y respuestas incompletas cuyo
motivo sea max_output_tokens. Solo en este último caso amplía el límite de salida
hasta OPENAI_RETRY_MAX_OUTPUT_TOKENS (30000 por defecto). No aplica respuestas
parciales ni reintenta cuota agotada, autenticación o filtros de contenido.
Registra motivo, identificadores del proveedor, tokens y duración sin claves,
prompts ni contenido comercial. El catálogo respeta OPENAI_REASONING_EFFORT
cuando no existe una configuración específica de catálogo.

La generación completa conserva la preparación administrativa. Nombres,
descripciones y asignaciones permanecen pendientes del guardado del formulario.
Los endpoints sin async mantienen la respuesta síncrona para compatibilidad.

## Publicación por el operador

Los cambios se implementaron únicamente en sax_stage. Después de publicar el
commit mediante el flujo habitual y hacer git pull en producción, el operador
debe reconstruir las cachés que use ese despliegue y reiniciar los workers para
que carguen la clase nueva y la configuración. Desde sax_plataforma:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan queue:restart
```

Si el despliegue usa config:cache o route:cache, volver a construirlas después.
Supervisor debe mantener activos sax-product-ai-worker; su comando existente
queue:work product-ai --queue=product-ai ya procesa los trabajos del editor.
El servidor web y los workers deben usar la misma caché persistente. La
configuración existente usa caché de archivos en el mismo proyecto.
No se requieren cambios de Nginx ni migraciones para este flujo.

## Validación

Las pruebas PHP simulan cortes por tokens, errores HTTP y conexión, agotamiento
de cuota, límite de intentos, cola, deduplicación, autorización del estado,
contexto sin guardar y conservación del producto. Las pruebas JavaScript cubren
inicio, sondeo, reconexión con el mismo identificador y fallos definitivos.

```bash
php vendor/bin/phpunit --do-not-cache-result tests/Unit/OpenAIResponseClientTest.php tests/Unit/OpenAICatalogServiceTest.php tests/Feature/ProductAiAsyncGenerationTest.php tests/Feature/ProductAiCompletionTest.php tests/Feature/ProductAiEditorContextTest.php tests/Feature/CatalogAttributesTest.php
node --test tests/js/product-ai-request.test.cjs tests/js/catalog-attributes-global.test.cjs
```

Referencia del límite compartido entre razonamiento y salida:
https://developers.openai.com/api/docs/guides/reasoning#allocating-space-for-reasoning
