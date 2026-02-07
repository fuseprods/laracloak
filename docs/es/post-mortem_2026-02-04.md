# Post-Mortem: Bloqueo de Dashboards en Producción (2026-02-04)

## Resumen
El 4 de febrero de 2026, se reportó que los dashboards en el entorno de producción estaban "congelados", mostrando indicadores de carga persistentes en lugar de renderizar los datos de los widgets. Los datos llegaban al navegador (visibles en la pestaña Network de DevTools), pero la interfaz no se actualizaba.

## Síntomas
- Los widgets se quedaban bloqueados en el estado "Esperando datos...".
- Las DevTools del navegador mostraban respuestas 200 OK con cargas JSON válidas.
- El problema no era reproducible en el entorno de desarrollo local.

## Causas Raíz

### 1. Validación Estricta de Content-Type
En `front.js`, el código verificaba una coincidencia exacta de `application/json`.
- **Entorno local**: Las cabeceras eran `application/json` limpio.
- **Entorno de producción**: El servidor/proxy añadía información de charset (ej: `application/json; charset=UTF-8`), lo que causaba que la verificación fallara y la carga fuera ignorada.

### 2. Variaciones en la Estructura de Datos Upstream
El servicio upstream (n8n) a menudo devuelve datos envueltos en un array `[ { "clave": "valor" } ]`.
- **Fallo**: La implementación inicial de `getNestedValue` esperaba el objeto en la raíz. Al recibir un array, fallaba al encontrar las claves, dejando a los widgets sin datos.

### 3. Caché agresiva del Navegador
Tras desplegar las correcciones iniciales, el entorno de producción seguía fallando porque los navegadores servían una versión cacheada de `front.js` anterior a los arreglos.

### 4. Falta de Aislamiento de Errores
Un fallo en la lógica de renderizado de un solo widget (o un desajuste de datos) podía potencialmente detener todo el bucle de renderizado, sin proporcionar feedback al usuario o desarrollador.

## Resolución

### Resiliencia del Frontend (`front.js`)
- **Cabeceras Robustas**: Se cambió `contentType.includes('application/json')` para que sea insensible a mayúsculas y permita parámetros extra.
- **Pathing Inteligente**: Se añadió lógica para "desenvolver" automáticamente arrays de un solo elemento (estilo n8n) al resolver claves de datos.
- **Aislamiento de Errores**: Se envolvió cada llamada de renderizado de widget en un bloque `try/catch`. Si un widget falla, los demás continúan cargando.
- **Logs de Depuración**: Se añadieron grupos de consola y logs de inicio para proporcionar visibilidad inmediata sobre el estado del motor y los datos recibidos.

### Estabilización del Backend
- **Cabeceras Explícitas**: Se forzó la cabecera `Content-Type: application/json` en `FrontController` al devolver cargas sanitizadas.

### Busting de Caché
- **Estrategia de Despliegue**: Se añadió una versión basada en timestamp a la etiqueta script en `page.blade.php` para asegurar que siempre se cargue el código más reciente:
  ```html
  <script src="{{ asset('js/front.js') }}?v={{ time() }}"></script>
  ```

## Lecciones Aprendidas
- Usar siempre `.includes()` y `.toLowerCase()` al verificar cabeceras HTTP.
- Diseñar parsers de datos para ser "indulgentes" con arrays raíz vs. objetos, especialmente al actuar como proxy de servicios de terceros como n8n.
- Implementar cache-busting para activos críticos de frontend desde el primer día.
- Proporcionar estados de UI de respaldo (como "--") en lugar de spinners infinitos cuando la resolución de datos falla.
