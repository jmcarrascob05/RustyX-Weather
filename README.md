# RustyX Weather – Documentación Técnica (PHP)

Aplicación web en **PHP** que consulta la API de **OpenWeatherMap** para mostrar el tiempo actual, la previsión por horas y por días de cualquier ciudad del mundo, almacenando todas las consultas en **MariaDB** y mostrando gráficos generados con HTML/CSS.

**Práctica:** Trabajo Final; Aplicación del clima 
**Alumno:** *Juan Manuel Carrasco Benítez*  
**Curso:** ASIR II  
**Fecha:** *12/03/2026*  
**URL de la aplicación:** *[http://juanma-clima.ddns.net/](http://juanma-clima.ddns.net/)*
---

## 1. Flujo general de la aplicación

La navegación se basa en un flujo sencillo: el usuario busca una ciudad en la portada y, a partir de ahí, accede a diferentes vistas que reutilizan las coordenadas (latitud y longitud) obtenidas a través de la API de geocodificación.

1. **Búsqueda inicial**
   - La página `index.php` muestra un formulario donde el usuario escribe el nombre de la ciudad.
   - Con ese nombre se llama a la API de geocodificación de OpenWeatherMap para obtener:
     - Latitud (`lat`)
     - Longitud (`lon`)
     - Nombre normalizado
     - Código de país

2. **Gestión del resultado**
   - Si no se encuentra ninguna ciudad, se muestra un mensaje de error en la propia página de inicio.
   - Si hay resultados, se listan las ciudades, y desde cada tarjeta se puede acceder a:
     - Tiempo **actual**
     - Previsión **por horas**
     - Previsión **semanal**

3. **Navegación entre vistas meteorológicas**
   - Las páginas `actual.php`, `horas.php` y `semana.php` reciben ciudad, país y coordenadas mediante `POST`.
   - Cada vista:
     - Consume la API correspondiente de OpenWeatherMap.
     - Guarda la consulta en la base de datos a través de un DAO.
     - Renderiza los datos de forma visual y estructurada.

4. **Historial de consultas**
   - La página `historial.php` consulta la base de datos y muestra una tabla con:
     - Ciudad
     - País
     - Tipo de consulta (actual, horas, semana)
     - Fecha
     - IP del usuario

---

## 2. Estructura del proyecto

### Raíz del proyecto

- **`index.php`**
  - Punto de entrada de la aplicación.
  - Procesa el formulario de búsqueda de ciudad.
  - Llama a la API de geocodificación y construye la lista de resultados.
  - Carga el encabezado y el pie comunes (`layout_header.php`, `layout_footer.php`).

- **`config.php`**
  - Archivo de configuración de la aplicación.
  - Centraliza datos como:
    - Parámetros de conexión a la base de datos.
    - Clave de la API de OpenWeatherMap.
    - Ajustes generales usados por el resto de scripts.

### Vistas meteorológicas

- **`actual.php`**
  - Recibe `lat`, `lon`, ciudad y país.
  - Consulta el **tiempo actual** en la API de OpenWeatherMap.
  - Si la respuesta es correcta, registra la consulta en la base de datos como tipo `actual`.
  - Muestra:
    - Temperatura principal.
    - Icono y descripción del estado del cielo.
    - Datos complementarios (sensación térmica, humedad, presión, viento, etc.).

- **`horas.php`**
  - Utiliza la latitud y longitud para obtener la **previsión por horas**.
  - Guarda la consulta como tipo `horas`.
  - Presenta una lista de franjas horarias con:
    - Hora local.
    - Icono.
    - Temperatura.
    - Descripción del tiempo.

- **`semana.php`**
  - Recupera la **previsión de varios días** desde la API.
  - Agrupa los datos de OpenWeatherMap por fecha.
  - Para cada día calcula:
    - Temperatura mínima.
    - Temperatura máxima.
    - Icono representativo.
    - Descripción del tiempo.
  - Registra la consulta como tipo `semana`.
  - Muestra un listado de días con sus datos resumidos.

- **`historial.php`**
  - Crea una instancia del DAO de consultas.
  - Obtiene un número limitado de registros recientes y, opcionalmente, estadísticas (por ejemplo, número de consultas por tipo).
  - Dibuja una tabla con:
    - ID
    - Ciudad y país
    - Tipo de consulta (Actual, Por horas, Semanal)
    - Fecha de la consulta
    - IP del usuario
  - Utiliza pequeñas insignias visuales para diferenciar los tipos de consulta.

### Layout y recursos

- **`layout_header.php`**
  - Define la cabecera HTML común:
    - Título de la aplicación.
    - Navegación principal.
    - Inicio del contenedor de contenido.

- **`layout_footer.php`**
  - Cierra el contenido principal.
  - Incluye el pie de página.
  - Inserta scripts compartidos (por ejemplo, `app.js`).

- **`style.css`**
  - Hoja de estilos principal.
  - Define:
    - Paleta de colores y tipografía.
    - Diseño responsive.
    - Estilos de:
      - Tarjetas de información meteorológica.
      - Listas de días y horas.
      - Tablas del historial.
      - Insignias y alertas.

- **`app.js`**
  - Añade efectos visuales ligeros.
  - Ejemplo: animación de entrada en los valores de temperatura y en las tarjetas al cargar la página.

---

## 3. Uso de la API de OpenWeatherMap

La aplicación se integra con varios endpoints de OpenWeatherMap:

1. **Geocoding API**
   - Convierte el nombre de ciudad en latitud y longitud.
   - Se utiliza en `index.php` al procesar el formulario de búsqueda.

2. **Tiempo actual**
   - Endpoints para obtener la situación meteorológica en el momento actual.
   - Consumido en `actual.php`.

3. **Pronóstico**
   - Endpoints de previsión (por horas y para varios días).
   - Utilizados en `horas.php` y `semana.php`.

Los datos devueltos por la API (temperaturas, iconos, descripciones, fechas…) se normalizan y se preparan antes de mostrarlos al usuario o guardarlos en la base de datos.

---

## 4. Base de datos y capa DAO

Se emplea **MariaDB** para registrar el histórico de consultas realizadas a la aplicación.

- Se define una tabla de consultas con campos como:
  - ID (clave primaria).
  - Nombre de la ciudad.
  - Código de país.
  - Latitud y longitud.
  - Tipo de consulta (`actual`, `horas`, `semana`).
  - Fecha y hora de la consulta.
  - IP del usuario.

- La lógica de acceso se encapsula en una clase DAO (por ejemplo, `ConsultaDAO`), encargada de:
  - Insertar nuevas consultas.
  - Listar las últimas consultas para la vista de historial.
  - Obtener estadísticas de uso si se necesitan.

Este enfoque mantiene el acceso a datos separado de la lógica de presentación y favorece las buenas prácticas de organización del código.

---

## 5. Gráficas de temperatura sin librerías externas

Para cumplir el requisito de mostrar la información en formato gráfico, se ha optado por generar las “gráficas” usando HTML y CSS, sin depender de librerías externas como Chart.js.

Proceso general:

1. PHP obtiene el conjunto de temperaturas (por horas o por días).
2. Se calcula la temperatura máxima del conjunto.
3. Para cada valor, se calcula un porcentaje en función de ese máximo.
4. Ese porcentaje se aplica como `width: X%` a barras horizontales dentro de contenedores específicos.
5. La apariencia (colores, gradientes, radios, animaciones) se controla desde `style.css`.

Ventajas de este enfoque:

- Carga rápida y sin dependencias de terceros.
- Integración directa con el diseño de la aplicación.
- Funciona en cualquier navegador moderno sin configuración adicional.

---

## 6. Despliegue en AWS y Docker

La aplicación está preparada para ejecutarse en un servidor remoto utilizando contenedores, cumpliendo el requisito de despliegue en la nube:

1. **Infraestructura**
   - Instancia en AWS (por ejemplo, EC2 con Ubuntu Server).
   - Puertos 80/443 abiertos para acceso HTTP/HTTPS.

2. **Código fuente**
   - El proyecto se aloja en un repositorio de GitHub.
   - En el servidor se clona el repositorio y se configura la clave de la API de OpenWeatherMap.

3. **Contenedores**
   - Se utiliza Docker (y, en su caso, `docker-compose`) para levantar:
     - Un contenedor con el servidor web (PHP + servidor HTTP).
     - Un contenedor con la base de datos MariaDB.
   - Esto permite reproducir fácilmente el entorno de desarrollo en producción.

4. **Acceso**
   - La aplicación queda accesible desde el exterior mediante:
     - La URL configurada (DNS) o
     - La IP pública de la máquina.
     - **URL de la aplicación:** *[http://juanma-clima.ddns.net/](http://juanma-clima.ddns.net/)*
