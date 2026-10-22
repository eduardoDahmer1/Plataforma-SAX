# Plan de pruebas CEC → SAX: sesión de 120 minutos

Estado: plan preparado a partir del código; pruebas y conexión real pendientes.

Objetivo: validar exactitud del catálogo, uso comercial, protección ante fallos y recuperación. Dos horas permiten cubrir todos estos frentes con una muestra funcional y una conciliación del catálogo completo; no equivalen a probar manualmente cada SKU ni a certificar estabilidad durante varios días.

## Alcance y condiciones

- Escrituras de la tienda exclusivamente en `sax_stage` y su base verificada. No ejecutar la configuración habitual del integrador sin revisar sus destinos: puede escribir el catálogo principal, espejar Ótica y notificar varios ambientes.
- Usar una ejecución aislada del integrador, con directorio de trabajo, configuración, logs y lock propios; destino principal stage, espejo óptico deshabilitado y heartbeat únicamente a stage. Verificar también que stage no propague cambios al peer de otra tienda. No modificar el proceso productivo ni sus archivos de configuración.
- Identificar el commit/cambios locales de la tienda y la versión real del binario del integrador. Preservar los cambios existentes. Respaldar los datos que se modificarán y acordar cómo restaurarlos sin pisar actividad ajena.
- Tener una persona con acceso al CEC para confirmar reglas y facilitar datos de referencia. Las modificaciones en el CEC requieren un entorno o SKUs de prueba acordados con su operador; si no existen, usar datos controlados en una API simulada local.
- Preparar cuenta cliente y administrador de stage, captura de correos de prueba y pasarelas de sandbox/simuladas. No enviar mensajes ni realizar cobros reales.
- Registrar si están activos el checkout, la pausa manual y la habilitación temporal de compras. Esta última debe estar desactivada al probar el bloqueo por integración.
- Tener una exportación/listado completo y respuestas API de referencia con hora de captura. Acordar cómo clasificar cambios concurrentes; una diferencia temporal debe volver a consultarse antes de considerarse error.
- Tener una corrida completa reciente y conciliable, o iniciarla durante el primer bloque. La documentación del integrador menciona aproximadamente 75 minutos, pero no es una medición actual. Si la estimación real supera la ventana, iniciarla antes de la sesión o dejar la validación de volumen pendiente, sin declararla aprobada.
- Preparar antes de la sesión un conjunto pequeño y aislado para repetir corridas e inyectar fallos. El flujo revisado recorre todo el catálogo; no asumir que existe un filtro por SKU. No provocar fallos ni cargar un catálogo reducido sobre el stage usado por la corrida completa: la limpieza podría afectar productos ajenos al conjunto.
- No ejecutar SQL de traducciones. Cualquier corrección necesaria se entrega al operador en texto, según `AGENTS.md`.

## Agenda

Los bloques suman 120 minutos. La corrida completa puede seguir trabajando mientras se revisan pantallas y se ejecutan pruebas sobre datos aislados. Esta simultaneidad requiere separar los destinos de escritura; no ejecutar dos integraciones que modifiquen el mismo catálogo.

| Minutos | Actividad | Evidencia de salida |
| --- | --- | --- |
| 00–15 | Verificar destinos, configuración efectiva, respaldo, controles de compra y valores iniciales. Iniciar corrida completa o identificar la reciente. | Entorno comprobado, versión, hora y `run_id`; muestra y referencia guardadas. |
| 15–40 | Revisar contrato CEC/API y exactitud de monedas, marcas, categorías y productos. | Comparación CEC → API → base, con discrepancias clasificadas. |
| 40–65 | Probar catálogo, variantes, carrito y checkout en stage con datos disponibles y anotar qué debe repetirse al terminar la corrida. | Evidencia web y de servidor, escritorio y móvil. |
| 65–90 | Ejecutar fallos, recuperación y segunda corrida sobre el conjunto aislado. | Logs, conteos antes/después y prueba de idempotencia. |
| 90–110 | Confirmar cierre de corrida completa, conciliar el catálogo y repetir casos afectados por cambios. | Totales reconciliados, diferencias por SKU y comprobaciones finales. |
| 110–120 | Reprobar incidencias corregidas si alcanza, restaurar controles y emitir resultado. | Aprobado / rechazado / incompleto, incidencias y pendientes. |

Si falta preparación o una falla crítica consume el tiempo, registrar los casos no ejecutados y cerrar como incompleto. No comprimirlos en una aprobación sin evidencia.

## Muestra funcional: 12–15 SKU identificados

Los perfiles pueden combinarse en un mismo SKU, siempre que quede explícito qué caso cubre.

| Perfil | Qué debe comprobarse |
| --- | --- |
| Producto normal, disponible | Identidad, nombre, marca, categoría, precio y saldo. |
| Precio alto, decimales y separadores | Interpretación numérica y redondeo en la moneda correspondiente. |
| Con y sin precio web/promoción | Regla comercial confirmada para `pre` y `pwb`, y coherencia hasta el pedido. |
| Stock 0, stock 1 y varias unidades | Límites de compra y comportamiento cuando el saldo cambia. |
| SKU nuevo | Alta única, relaciones e idiomas configurados; el código lo crea inactivo, su publicación requiere validación administrativa. |
| SKU existente con edición administrativa | Fotos, miniatura, estado, descripciones y demás campos de la tienda sobreviven. |
| Nombre modificado en CEC | Nombre externo actualizado; verificar slug, enlaces y variantes según la regla acordada. |
| Familia con colores/tamaños | Relaciones intactas y stock/precio del SKU seleccionado. |
| Óptico y no óptico | Clasificación correcta en stage; no se realiza el espejo en la tienda Ótica real. |
| SKU bloqueado o retirado del origen | Diferenciar baja comercial de fallo técnico y verificar limpieza sin perder pedidos históricos. |
| Nombre con tildes, símbolos o codificación especial | Texto legible en base, buscador y ficha, en español y portugués. |
| Con ubicaciones de retiro | Confirmar si ahora llegan `cde`/`asu`/`pjc` y cómo se relacionan con `sal`. |

## Casos y resultado esperado

### A. Contrato y datos

- [ ] A01. Monedas, marcas y categorías responden y se relacionan correctamente; revisar tipo de cambio y moneda base antes de comparar importes.
- [ ] A02. Listado paginado y detalle devuelven los SKU esperados; comprobar primera/última página y ausencia de páginas omitidas o duplicadas.
- [ ] A03. Comprobar `estatus` dentro del JSON: un HTTP 200 por sí solo no acredita éxito. Probar número y cadena donde el contrato los admite.
- [ ] A04. Comparar `sku`/`ref_code`, `nom`, `pre`, `pwb`, `sal`, `mar`, `cat` con la base y la tienda. No comparar IDs internos con códigos externos como si fueran iguales.
- [ ] A05. Registrar la regla de precios con el responsable: el integrador carga `pre` en `price` y `pwb` en `promotion_price`; verificar qué importe debe usar realmente la venta. No dar por hecho que el precio promocional se aplica.
- [ ] A06. Confirmar qué depósitos integra `sal`. La documentación histórica dice que faltaba stock por local; verificar el payload actual. Campos ausentes deben conservar los retiros existentes, no escribir ceros inventados. Ausente y cero explícito son casos distintos.
- [ ] A07. Conciliar TODO el universo: SKU únicos del origen contra integrados; faltantes, duplicados, precios y saldos discrepantes, registros sin relaciones y bajas. Clasificar cada diferencia como regla válida, cambio concurrente confirmado o defecto. No usar solo el porcentaje `synced` para aprobar.
- [ ] A08. Alta nueva y actualización existente: conservar campos administrados por la tienda, relaciones de variantes y contenido enriquecido. El nombre proveniente del ERP sí puede actualizarse. Si faltan traducciones, documentar la corrección para ejecución manual del operador.

### B. Experiencia de compra

- [ ] B01. Buscar por SKU y nombre; filtrar por marca/categoría y abrir la ficha. Verificar la actualización del feed/cache según el scheduler y registrar la demora real.
- [ ] B02. Seleccionar variantes; ficha, carrito, checkout y pedido deben coincidir en identidad, cantidad, moneda y precio según las reglas acordadas.
- [ ] B03. Stock 0 no permite vender; stock 1 no permite cantidad 2. Intentar también una petición directa al servidor.
- [ ] B04. Cambiar precio/saldo entre carrito y checkout usando datos de prueba: impedir sobreventa y mostrar el importe vigente sin confiar en valores enviados por el navegador.
- [ ] B05. Completar un pedido de prueba y revisar sus ítems y totales; abrir cada método de pago habilitado en sandbox/simulación. Si no hay sandbox, dejar la confirmación de pago como pendiente.
- [ ] B06. Ante integración fallida, comprobar bloqueo de checkout y entradas a pago, incluyendo peticiones GET/POST directas. El carrito debe seguir permitiendo agregar/editar dentro del stock; ese es el comportamiento actual previsto.
- [ ] B07. Verificar que callbacks/webhooks de pedidos ya iniciados sigan procesándose correctamente cuando el catálogo esté bloqueado, con eventos de prueba autenticados y sin cobros reales.
- [ ] B08. Repetir ficha → carrito → checkout en móvil y escritorio, y validar avisos en los idiomas activos. Una pausa manual debe seguir respetándose tras recuperar la integración.

### C. Resiliencia, integridad y operación

Ejecutar las inyecciones de fallos con API y base aisladas; los eventos que alcancen el monitor de stage deben programarse fuera del cierre de la corrida real y restaurarse al terminar.

- [ ] C01. Timeout, 429 y 5xx: reintentos limitados, pausas y recuperación; no aumentar la concurrencia para forzar que la corrida entre en dos horas.
- [ ] C02. JSON inválido, HTTP 200 con error, cero páginas y fallo de una página intermedia: la corrida debe reportar error y evitar limpieza basada en datos incompletos. Registrar cualquier actualización parcial previa.
- [ ] C03. Fallos en detalles de SKU: probar uno aislado, varios intercalados con éxitos y el umbral consecutivo. Comprobar que un error técnico no se convierta en stock cero o baja silenciosa. Un log de error con monitor verde requiere investigación.
- [ ] C04. Fallo de escritura/relación en base: comparar datos y estado reportado. El código revisado registra ciertos errores por producto sin propagarlos al resultado global; este caso es obligatorio para detectar un éxito incompleto.
- [ ] C05. Retorno inferior al umbral de limpieza: debe fallar y no ejecutar bajas/puesta a cero por limpieza. Probar debajo y justo en el umbral configurado sobre el conjunto controlado.
- [ ] C06. Baja real (`estatus=11`) frente a fallo técnico: en corrida válida verificar la política de stock cero/conservación/eliminación. Probar un producto activo, uno con imagen y uno inactivo sin imágenes. Comparar pedidos históricos antes/después.
- [ ] C07. Segunda ejecución con el mismo origen: sin duplicados de productos, traducciones, vínculos ni retiros; mismos valores comerciales y contenido administrativo preservado. Ignorar timestamps al comparar.
- [ ] C08. Dos procesos con el mismo lock de prueba: solo uno escribe. Comprobar el mecanismo de exclusión del scheduler/cron real sin detener procesos productivos.
- [ ] C09. Heartbeat autenticado: `started` → `succeeded`/`failed` con el mismo `run_id`, duración y error visibles. Token incorrecto debe rechazarse. Repetir eventos y probar eventos atrasados para detectar regresiones de estado.
- [ ] C10. Monitor vencido o nunca inicializado: checkout bloqueado incluso antes del próximo scheduler. Recuperación válida: se limpia el error y vuelve la disponibilidad según los controles comerciales; probar también qué ocurre con un nuevo `started` después de una falla.
- [ ] C11. Alertas y expiración: adelantar el reloj en pruebas aisladas. El código usa por defecto 24 horas para alertar/vencer y un mínimo configurable de 30 minutos; no esperar ese plazo durante la sesión. Capturar correos y comprobar que no se repiten indebidamente.
- [ ] C12. Medir duración, errores y progreso de la corrida real; revisar cron, última ejecución y logs. Un heartbeat verde sin conciliación satisfactoria no aprueba la integración.

## Cobertura existente y faltante

El repositorio del integrador ya contiene tests de conversión de payloads, precios/saldos, ubicaciones, reintentos, rate limit, circuit breaker y envío de heartbeat. La tienda contiene tests de pausa de checkout/carrito y alertas. Revisar y ejecutar los tests con base/correo aislados; no asumir que pasan ni que cubren C02–C08 de punta a punta. Hay pruebas opcionales de conexión real en el integrador: verificar su configuración antes de ejecutarlas.

No se ejecutaron tests para preparar este documento. Tampoco se verificó todavía la API real, la configuración efectiva ni la duración actual.

## Evidencia, aprobación y pendientes

Registrar cada caso como PASS / FAIL / BLOQUEADO / NO EJECUTADO, con: caso, SKU, hora, `run_id`, valor esperado, resultado observado y referencia a captura/log/diferencia. No guardar tokens, contraseñas ni URLs con credenciales en la evidencia.

Para aprobar:

1. Corrida completa terminada y conciliación sin diferencias comerciales inexplicadas.
2. Precio, stock e identidad correctos en todos los casos de muestra y hasta el pedido.
3. Sin pérdida de contenido administrativo, duplicados ni alteraciones indebidas de pedidos históricos.
4. Fallos parciales y de escritura detectados; protección de limpieza, bloqueo comercial y recuperación comprobados.
5. Segunda corrida controlada estable; destinos y controles de stage restaurados al cierre.

Precio incorrecto, sobreventa, pérdida de datos, escrituras en otro ambiente o éxito global con fallos comerciales ocultos impiden aprobar. Casos críticos bloqueados o no ejecutados dejan el resultado incompleto.

Dejar explícito fuera de la aprobación de dos horas: estabilidad durante varios ciclos del cron, segunda corrida completa si no entra en la ventana, carga prolongada y validación del espejo en Ótica real. El espejo puede probarse con bases aisladas de prueba previamente preparadas; la publicación y validación en los otros ambientes corresponden al operador después de validar stage.
