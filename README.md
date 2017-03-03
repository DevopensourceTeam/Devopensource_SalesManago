# Devopensource_SalesManago
## Características

- Evento success
- Evento cart
- Creacion de customer en login de Magento
- Creacion de customer en registro de Magento
- Actualización de cuenta y direccciones del cliente.
- Tracking js de cliente
- Evento Whislist
- Tracking y generador de popups en frontend
- Añadido envío de tags y mejora de configuración en backend.
- Añadido modo debug.
- Añadido la exportación de productos desde el grid de productos en el admin
- Customizacion de tags por tipo de cliente.
- Exportación productos para SM-
- Creacion de Tags por paginas y categorias visitadas
- Creacion de customer en registro newsletter y añadir opt-in
- Al darse de baja de la newsletter opt-out

## Instalación y configuración

1º Instalar modulo en el Magento, subiendo cada fichero a su correspondiente fichero.
2º Sistema > configuración > Devopensource_SalesManago
3º Campo habilitar a "si" para un funcionamiento basico necesario insertar valores en campos "Client id", "Api secret", "Endpoint" (valor por defecto), con esto tendremos el funcionamiento basico de las tags.
4º Si queremos otras funcionalidades las habilitamos, "Tracking js", "Popup JS"
5º despues configuramos las tags en cada seccion "Cart", "Favoritos", etc...
6º Para habilitar la funcionalidad de exportar los productos automáticos simplemente tenemos que habilitar "Si" y meter "Token" un valor generado aleatoriamente, despues al partner de SM hay que pasarle la url "https://www.dominio.com/salesmanago/export/products/token/{valor del token indicando previamente}"
Con esta url SM podrá acceder a la url de los últimos productos.
