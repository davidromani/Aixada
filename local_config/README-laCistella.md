Configuración usada en "La Cistella" y "Travalera"
==================================================

Nosotros usamos una configuración un poco distinta de la por defecto.

En `config.php.sample.laCistella` encontrareis los parámetros que hemos cambiado
y así podéis valorar si os conviene hacer los mismos cambios.

Cambios a destacar:
  * De impacto:
      * No usar compra directa.
      * Validar directamente al distribuir pedidos (la validación solo se usaría para productos de stock) (`$order_distribution_method = 'distribute_and_validate'`)
      * Enviar automáticamente  al proveedor el pedido resumido al cerrar el pedido (`$email_orders` y `$email_order_format`)
      * Usar cuentas de proveedores (`$accounts['use_providers'] = true`)
  * Detalles:
      * Orden ascendente de las id de las UF al revisar pedidos.
      * Usar la UF-1 para anotar errores del reparto al revisar pedido: cuando un producto ha llegado pero se ha estropeado o perdido, podemos pagarlo al proveedor y quedar como un coste para la coope (`$revision_fixed_uf = 1`) 
      * Uso método interno Aixada para la copia de seguridad de bd.
      * Fechas como `dd-mm-aa` en vez de `dd-mm-aaaa` en el dinero para no perder espacio en pantalla.
      * Usar `'Windows-1252'` en la importación de productos para conservar los acentos `ñ` y `ç`.
