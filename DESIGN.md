# Diseño vigente de Pontelog

La identidad se basa en el logotipo PONTE: negro, blanco y grises, Raleway para títulos y botones, Source Sans Pro para lectura. Las fuentes se sirven localmente desde `assets/fonts/`.

- Hero negro, texto a la izquierda y fotografía en la mitad derecha, con esquinas rectas. En móvil se apila.
- Cabecera blanca, navegación de 18 px en escritorio y menú desplegable hasta 980 px.
- Ocho tarjetas de logotipos, sin títulos, plus ni descripción al frente. Giran al pasar el cursor; en pantalla táctil se activan al tocar. La información y el botón Consultar centrado están al reverso.
- Trazo oval diagonal inspirado en el aro del logo, centrado y proporcional en botones y tarjetas. Transición de 200 ms, sin bucles y con movimiento reducido.
- Empresa con misión y visión en vertical, título encima del texto, sin separadores horizontales.
- Contacto negro con formulario blanco y mapa visible. Footer negro y logo PONTE blanco, sin enlace de regreso al inicio.
- Fotografías de empresa y tarjetas con bordes redondeados; la imagen del hero permanece rectangular.
- Foco visible, navegación por teclado, Escape en tarjetas y respeto a `prefers-reduced-motion`.

No reintroducir buscador, filtros, recursos ni bloques de ayuda en portada. Los formularios de campaña y webinar mantienen sus rutas directas, campos y destinos originales.
