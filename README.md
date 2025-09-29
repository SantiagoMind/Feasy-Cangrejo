# Proyecto Cangrejo

Plugin modular para construir formularios din�micos con condicionales en WordPress.

## Instalaci�n r�pida
1. Copia el directorio del plugin dentro de `wp-content/plugins/`.
2. Activa **Proyecto Cangrejo** desde el panel de administraci�n de WordPress.

## Configuraci�n inicial
### Clave secreta para HMAC
El envi� de formularios puede firmar los datos con un HMAC para ser verificados en tu
punto final de Google Apps Script.

1. Edita `wp-config.php` y a�ade una constante con tu clave secreta:

``php
define('FEASY_HMAC_SECRET', 'mi-clave-super-secreta');
```

El c�digo del plugin lee esta constante para generar el token y la firma
correspondientes.
Tambi�n puedes definir esta misma clave como variable de entorno `FEASY_HMAC_SECRET`.

### Puntos finales de Google Apps Script
Cada formulario cuenta con un archivo de configuraci�n en `includes/form-config-*.php`.
En estos archivos se especifica la URL `endpoint` a la que se enviar�n los datos.
Modifica ese valor para apuntar a tu propio script de GAS.

### Actualizaci�n autom�tica de librer�as
El plugin descarga la librer�a **SortableJS** de manera peri�dica mediante el
sistema de tareas programadas de WordPress. Aseg�rate de que `WP-Cron` est� activo
en tu instalaci�n.

Para dar de alta una nueva librer�a que se actualice autom�ticamente:

1. Abra el archivo `includes/updater-config.php` .
2. Agrega un nuevo elemento al arreglo de `Feasy_Library_Updater::register()` con los datos del repositorio, nombre de archivo y URL de descarga.
3. El archivo descargado se guardar� en `assets/vendor/<slug>/`. Crea esa carpeta si no existe.
4. El actualizador comprobar� cada semana la versi�n m�s reciente en GitHub y la descargar� cuando sea necesario.

## Uso b�sico
- Los formularios se insertan mediante shortcodes de la forma `[feasy_form_ID]`,
  donde `ID` corresponde al sufijo del archivo de configuraci�n
  (por ejemplo `sip_f_005`).
- Puedes generar y editar formularios visualmente con el shortcode
  `[feasy_form_editor]` (disponible para usuarios con permisos de administraci�n).

## Requisitos
- WordPress 5.xo superior
- PHP 7.4 o superior

Para m�s detalles revisa los archivos dentro del directorio `includes/` y las
plantillas en `templates/`.