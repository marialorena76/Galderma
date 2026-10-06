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
| Visitante sin sesión | `mapa-portada` | La portada a color, sin progreso |
| 0 completos | `mapa-1` | Pin "Empezá acá" en el 01 |
| 2 completos | `mapa-3` | ✓ en 01 y 02 · pin en 03 · 04-06 grises con candado |
| 6 completos | `mapa-6` | ✓ en los seis |

## 1. Subir las imágenes
hPanel → Administrador de archivos → crear `public_html/experienciaconproposito/wp-content/uploads/academia/mapa/`
y subir los 7 `.webp` de la carpeta `img/` (≈130 KB cada uno).

Probá que abra: `https://soyloregonzalez.com.ar/experienciaconproposito/wp-content/uploads/academia/mapa/mapa-1.webp`

## 2. Agregar el snippet
Code Snippets → Agregar nuevo → pegar todo `mapa-progreso.php` **sin la primera línea `<?php`**
→ "Ejecutar en todo el sitio" → Guardar y activar.

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
