# Lo que vive en la plataforma, no en `uploads/`

Esta carpeta existe porque una parte del proyecto estaba solo en el servidor y
no se podía leer ni revisar. Lo que hay acá es **una copia tomada del sitio**,
para tenerlo versionado.

## `gd-mapa-progreso/`

Plugin propio, activo en `soyloregonzalez.com.ar/experienciaconproposito`.
Instalado en `wp-content/plugins/gd-mapa-progreso/`. Hace tres cosas:

1. El shortcode `[mapa_progreso]`, que dibuja el mapa en la página del curso
   con el avance real del alumno. Es el contenido de la página del curso.
2. El completado automático: escucha el mensaje `gdFin` que manda el capítulo
   al terminar, marca la lección como completada en LearnDash y lleva al mapa.
3. La barra lateral de las lecciones con la estética Galderma, incluidos los
   textos en español que reemplaza por JavaScript.

### Las imágenes del mapa

El plugin las lee de `wp-content/uploads/academia/mapa/`, en **WebP**:

    mapa-portada.webp     los seis capítulos a color (fuera de un curso)
    mapa-1.webp … mapa-6.webp   capítulos 1..N a color, el resto en gris

Son el mismo arte que las láminas de `fluido/mapa-*.jpg`. Se regeneran con
`fluido/scripts/webp-mapa.py`, que las convierte con los nombres que el plugin
espera. **Si se tocan las láminas, hay que regenerar los WebP y subirlos**, o
el mapa del curso queda con el arte viejo mientras el resto cambia.

### Cuidado

El archivo de acá es una copia. La versión que corre es la del servidor.
Antes de pisar el servidor con esta copia, compará.
