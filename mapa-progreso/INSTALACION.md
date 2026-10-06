# Mapa de progreso — cómo instalarlo

El alumno abre el curso y ve el recorrido con **su** avance: los capítulos hechos a color
con tilde, el actual con el pin "Estás acá" y el resto en gris con candado.
Debajo va la barra "Vas por el Capítulo 03 · 2/6 · Continuar →" (para sacarla: `[mapa_progreso barra="no"]`).

**Modo "solo el mapa" (por defecto):** al alumno con sesión la página del curso le muestra únicamente
el encabezado del sitio, el mapa con su barra y el pie. Oculta el banner del curso, la tarjeta lateral
"Completado / Curso Includes", las barras "100% Complete" y los listados "Contenido del Curso".
Al visitante sin sesión no le oculta nada, para que pueda inscribirse.
Para ver la página completa otra vez: `[mapa_progreso solo="no"]`. El progreso sale de LearnDash, así que se ve
igual en el celular, en la compu o en otra sesión.

| Situación | Imagen | Qué se ve |
|---|---|---|
| Visitante sin sesión (curso abierto) | `mapa-1` … `mapa-6` | Igual que un alumno; el avance queda guardado en su navegador |
| 0 completos | `mapa-1` | Pin "Empezá acá" en el 01 |
| 2 completos | `mapa-3` | ✓ en 01 y 02 · pin en 03 · 04-06 grises con candado |
| 6 completos | `mapa-6` | ✓ en los seis |

## 1. Subir las imágenes
hPanel → Administrador de archivos → crear `public_html/experienciaconproposito/wp-content/uploads/academia/mapa/`
y subir los 7 `.webp` de la carpeta `img/` (≈130 KB cada uno).

Probá que abra: `https://soyloregonzalez.com.ar/experienciaconproposito/wp-content/uploads/academia/mapa/mapa-1.webp`

## 2. Instalar el plugin (recomendado)
Comprimir `mapa-progreso.php` dentro de una carpeta `gd-mapa-progreso/` (como `gd-mapa-progreso.php`) y subir
el `.zip` en Plugins → Añadir nuevo → Subir plugin → Activar. Para actualizarlo, subir el `.zip` nuevo y elegir
"Reemplazar el actual por el subido".

Alternativa: Code Snippets, pegando todo `mapa-progreso.php` sin la primera línea `<?php`. Ojo: si se pega
incompleto, Code Snippets lo desactiva y el shortcode aparece como texto. Usar uno de los dos, no ambos.

**Caché:** el sitio usa **SpeedyCache**. Después de cada cambio: SpeedyCache → borrar caché.

## 3. Ponerlo en el curso
En la página del curso (`/courses/patient-journey-en-accion/`), arriba de la lista de lecciones:
widget **Shortcode** de Elementor con:

```
[mapa_progreso]
```

Si lo ponés en otra página que no es el curso (por ejemplo, el dashboard de BuddyBoss),
indicá el ID del curso: `[mapa_progreso curso="123"]`.

## Cómo decide el avance
- Toma las lecciones del curso **en el orden del constructor de LearnDash**: la 1ra = sala 01, y así.
- Un capítulo cuenta como hecho cuando LearnDash marca la lección como completada. Hoy eso
  pasa con el botón "Marcar como completado". Si el alumno no lo toca, el mapa no avanza.
- Si el curso tiene progresión lineal, las salas futuras muestran el candado y
  "Se desbloquea al terminar el capítulo X". Si es libre, son clickeables.

## Antes de publicar
- [ ] **LiteSpeed Cache:** confirmar que "Cachear usuarios con sesión iniciada" esté **apagado**
      (es lo que viene por defecto). Si está prendido, todos verían el mapa del primer alumno que cargó la página.
- [ ] Probar con un usuario de prueba: completar el capítulo 1, volver al curso y ver que el pin pase al 02.

## Cambiar algo
- Textos de las salas y posición de pines/tildes: `gd_mapa_salas()`, al principio del snippet (en % de la imagen).
- Probarlo local sin WordPress: `php test/render.php 1,2 > test/out/12.html` (simula los capítulos 1 y 2 completos).

## Completado automático de los capítulos
El mismo snippet hace que cada capítulo se marque como completado solo, al tocar el botón de la
última pantalla del capítulo, y lleve al siguiente (el 6 vuelve a la página del curso, con el mapa completo).
En las lecciones con un capítulo embebido se ocultan el botón "Marcar como completado" y la cabecera de
la lección (migas "Curso > Capítulo 1", "Lección 1 of 6" y las flechas): el capítulo trae su propia navegación.

Requiere los `capituloN.html` actualizados de la carpeta `fluido/` (suben a `wp-content/uploads/academia/`,
reemplazando los anteriores). Si el snippet se desactiva, los capítulos navegan igual que antes.

Probarlo localmente: `php test/leccion.php > test/out/sitio/leccion.html` (simula una lección con el capítulo adentro).

## Barra lateral de las lecciones con estética Galderma
El snippet también restiliza la barra lateral de BuddyBoss en lecciones, temas y quizzes: magenta en
lugar del azul del tema (barra de avance, tildes, botón "Volver al curso"), Poppins, capítulo actual
resaltado con fondo rosado y línea magenta, sin tachado en los completos, y los textos que BuddyBoss
deja en inglés pasados a español. El azul se detecta por color, así que cubre también elementos con
otros nombres de clase. Réplica para probar: `php test/sidebar.php > test/out/sidebar.html`.

## Visitantes sin sesión (curso abierto)
Si el curso está como **Abierto** en LearnDash, el visitante ve lo mismo que el alumno: solo el mapa, con su
barra, y la página limpia. Como LearnDash no guarda avance sin sesión, el avance queda en el navegador del
visitante: al terminar cada capítulo se anota y el mapa del curso lo muestra. Si el curso no es abierto, el
visitante ve la página completa para poder inscribirse.

**Importante:** LiteSpeed guarda una copia de las páginas para los visitantes. Después de cada cambio del
snippet hay que **purgar la caché** (LiteSpeed Cache → Purgar todo); si no, el visitante sigue viendo la copia vieja.
