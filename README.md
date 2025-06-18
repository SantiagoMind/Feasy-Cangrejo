# Proyecto Cangrejo

Este plugin de WordPress permite construir formularios dinámicos que envían sus respuestas a un Google Apps Script. Incluye un editor visual, lógicas condicionales y soporte para actualización automática de librerías.

## Instalación

1. Copia la carpeta del plugin en `wp-content/plugins/proyecto-cangrejo`.
2. Actívalo desde el panel de administración de WordPress.

El plugin requiere WordPress 5.5 o superior.

## Configuración de la clave HMAC

Para firmar las peticiones al Google Apps Script debes definir la constante `FEASY_HMAC_SECRET` en tu `wp-config.php`:

```php
define( 'FEASY_HMAC_SECRET', 'tu_clave_secreta' );
```

Esa misma clave debe guardarse como propiedad de script en Google Apps Script con el nombre `FEASY_HMAC_SECRET`.

## Enviar datos al Apps Script

El manejador `includes/form-handler.php` añade un token temporal y una firma HMAC a los datos antes de enviarlos. El Apps Script debe validar ambas cosas para aceptar la petición.

Al verificar la firma desde Apps Script, usa el cuerpo crudo de la petición antes de añadir `__signature`. Puedes remover ese campo con una expresión regular y calcular el HMAC sobre el JSON resultante:

```javascript
const raw = e.postData.contents;
const withoutSig = raw.replace(/,"__signature":"[^"]+"}/, '}');
const expected = Utilities.computeHmacSha256Signature(withoutSig, secret);
if (hmacToHex(expected) !== receivedSig) throw new Error('Firma inválida');
```

## Desarrollo

El repositorio incluye tareas automáticas para actualizar librerías y un editor de formularios. Revisa los archivos dentro de `includes/` y `assets/` para personalizar el comportamiento.

