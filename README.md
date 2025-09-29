# Proyecto Cangrejo

Plugin modular para construir formularios dinámicos con condicionales en WordPress.

## Instalación rápida
1. Copia el directorio del plugin dentro de `wp-content/plugins/`.
2. Activa **Proyecto Cangrejo** desde el panel de administración de WordPress.

## Configuración inicial
### Clave secreta para HMAC
El envió de formularios puede firmar los datos con un HMAC para ser verificados en tu
punto final de Google Apps Script.

1. Edita `wp-config.php` y añade una constante con tu clave secreta:

``php
define('FEASY_HMAC_SECRET', 'mi-clave-super-secreta');
```

El código del plugin lee esta constante para generar el token y la firma
correspondientes.
También puedes definir esta misma clave como variable de entorno `FEASY_HMAC_SECRET`.

### Puntos finales de Google Apps Script
Cada formulario cuenta con un archivo de configuración en `includes/form-config-*.php`.
En estos archivos se especifica la URL `endpoint` a la que se enviarán los datos.
Modifica ese valor para apuntar a tu propio script de GAS.

### Actualización automática de librerías
El plugin descarga la librería **SortableJS** de manera periódica mediante el
sistema de tareas programadas de WordPress. Asegúrate de que `WP-Cron` esté activo
en tu instalación.

Para dar de alta una nueva librería que se actualice automáticamente:

1. Abra el archivo `includes/updater-config.php` .
2. Agrega un nuevo elemento al arreglo de `Feasy_Library_Updater::register()` con los datos del repositorio, nombre de archivo y URL de descarga.
3. El archivo descargado se guardará en `assets/vendor/<slug>/`. Crea esa carpeta si no existe.
4. El actualizador comprobará cada semana la versión más reciente en GitHub y la descargará cuando sea necesario.

## Uso básico
- Los formularios se insertan mediante shortcodes de la forma `[feasy_form_ID]`,
  donde `ID` corresponde al sufijo del archivo de configuración
  (por ejemplo `sip_f_005`).
- Puedes generar y editar formularios visualmente con el shortcode
  `[feasy_form_editor]` (disponible para usuarios con permisos de administración).

## Requisitos
- WordPress 5.xo superior
- PHP 7.4 o superior

Para más detalles revisa los archivos dentro del directorio `includes/` y las
plantillas en `templates/`.