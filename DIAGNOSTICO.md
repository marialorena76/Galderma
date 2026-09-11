# Capítulo 1 — Diagnóstico del HTML interactivo (LearnDash)

Archivo revisado: `capitulo-1/capitulo1-original-roto.html`
Archivo corregido: `capitulo-1/capitulo1-corregido.html`

Verificado renderizando el HTML en Chromium headless (Playwright), no a ojo.

---

## Bugs que rompían todo

### 1. 55 tags auto-cerrados que en HTML no existen (la causa principal)
El archivo trae `<div ... />` y `<span ... />` (sintaxis de JSX/XML). El parser de HTML
**ignora la barra** y deja el tag abierto para siempre, así que todo lo que viene después
queda anidado adentro.

Consecuencia medida en el DOM real:

| Pantalla | Dónde terminó | Ancho renderizado |
|---|---|---|
| 1 | `.gd-canvas-wrap` (correcto) | 1240 px |
| 2 | 5 niveles adentro de la pantalla 1 | 0 px |
| 3 | 10 niveles adentro | 0 px |
| 4 | 12 niveles adentro | 0 px |
| 5 | 16 niveles adentro | 0 px |
| 6 | 18 niveles adentro | 0 px |
| 7 | 20 niveles adentro | 0 px |
| 8 | 22 niveles adentro | 0 px |

Y lo peor: **la barra de navegación (`.gd-toolbar`) también quedó sepultada** 20 niveles
adentro de la pantalla 1, con tamaño 0×0. O sea: los botones "Anterior / Continuar"
existen en el HTML pero son invisibles e inclickeables.

Resultado para el alumno: ve la portada y nada más. Si de alguna forma avanza,
la pantalla 1 se oculta y con ella se ocultan las 7 restantes → caja en blanco.

También rompe cosas chicas: el separador `<div style="width:1px;height:24px">` del header
se tragó el texto "Descubrimiento y Primer Contacto", que quedó comprimido dentro de 1 px.

### 2. El atributo `class` quedó adentro del `style` (5 veces)
```html
<div style="padding:13px 24px;class="gd-next-arrow" style="cursor:pointer;...">
```
El `class="gd-next-arrow"` está **dentro del valor de style**, y después hay un segundo
`style` duplicado que el navegador descarta. Efecto: el botón verde "Continuar →" dibujado
dentro del diseño en las pantallas 3, 4, 5, 6 y 7 **no tiene la clase que el JS escucha**,
así que no hace nada, y además se ve gris y descolocado.

### 3. Las dos pantallas de ejercicios perdieron su contenedor con padding
En las pantallas 6 (Ejercicio 1) y 7 (Ejercicio 2) falta el `<div style="flex:1;...;padding:...">`
que sí tienen las otras seis. El contenido arrancaba pegado al borde izquierdo y se metía
debajo del footer negro.

### 4. La escala dependía de CSS que no es estándar
```css
transform: scale(calc(100cqw / 1920px));
```
Dividir una longitud por otra longitud dentro de `calc()` recién lo soportan los navegadores
muy nuevos (Chrome moderno sí; Safari/Firefox desactualizados no). Donde no lo soporta,
la declaración se descarta, el canvas queda en 1920 px reales y con `overflow:hidden`
sólo se ve la esquina superior izquierda.

### 5. El drag & drop no funciona en celular ni tablet
El Ejercicio 1 usa HTML5 Drag and Drop (`draggable`, `dragstart`, `drop`), que **no existe
en touch**. En iPad/celular el ejercicio es imposible de completar.

### 6. El canvas fijo de 1920×1080 es ilegible en la columna de LearnDash
Está pensado como diapositiva de 1920 px escalada al ancho disponible. Medido:

- Celular de 390 px de ancho → escala 0.18 → **el texto del cuerpo renderiza a 3,1 px** y la
  diapositiva entera mide 197 px de alto.
- Columna típica de lección LearnDash/BuddyBoss (~800 px) → escala 0.42 → texto a ~7 px.

Para que se lea, la lección tiene que ir a ancho completo (plantilla full width en Elementor,
sin sidebar) o abrirse en pantalla completa.

---

## Lo que falta de contenido (comparado con el PDF original de 11 slides)

| PDF | HTML |
|---|---|
| p1 Portada | ✅ pantalla 1 |
| p2 Video + objetivos | ✅ pantalla 2 |
| p3 ¿Qué siente el paciente? | ✅ pantalla 3 |
| p4 Canales de contacto | ⚠️ pantalla 4, **estática**: en el PDF hay solapas WhatsApp / Llamada / Email / Instagram, acá sólo se ve el ejemplo de WhatsApp |
| p5 Los otros 3 ejemplos de respuesta | ❌ falta |
| p6 "Del mensaje genérico al que conecta" | ❌ falta |
| p7 Fórmula de conexión | ✅ pantalla 5 |
| p8 Ejercicio 1 | ✅ pantalla 6 |
| p9 Ejercicio 2 | ✅ pantalla 7 |
| p10 Checkpoint final (3 preguntas) | ⚠️ pantalla 8, **decorativa**: las 3 preguntas no responden al click, el puntaje 0/3 no se mueve. No hay JS para eso |
| p11 Badge "Primera Impresión Pro" | ❌ falta |

Otros pendientes:
- `meta.quizUrl` está en `"#"` → el botón "Finalizar Capítulo" no lleva a ningún lado.
- Dos imágenes siguen como "PLACEHOLDER — REEMPLAZAR POR FOTO FINAL" (pantallas 1 y 3).
- El video apunta a `youtube.com/embed/D6t7b_pTBDU` — hay que confirmar que sea el video real.
- No hay integración con el progreso de LearnDash: terminar las 8 pantallas no marca nada,
  y al recargar la página se pierden todas las respuestas.

---

## Qué se corrigió en `capitulo1-corregido.html`

1. Los 55 `<div/>` y `<span/>` cerrados como corresponde → las 8 pantallas quedan hermanas
   dentro del wrapper y la toolbar vuelve a ser visible y clickeable.
2. Los 5 `class="gd-next-arrow"` rescatados de adentro del `style` → los botones "Continuar →"
   del diseño vuelven a navegar.
3. Contenedor con padding agregado en las pantallas 6 y 7.
4. La escala ahora la calcula el JS (`ResizeObserver`), no `calc()` → funciona en cualquier navegador.
5. Ejercicio 1: además del drag & drop, ahora funciona **tocando** el fragmento y después el
   casillero (y tocando un casillero lleno lo devuelve al pool). Usable en tablet y celular.
6. Botón "Pantalla completa" en la toolbar, para leerlo cuando la columna de la lección es angosta.

Verificado post-fix: navegación 1→8 con el botón real, validación del Ejercicio 1 con feedback,
selección de opciones del Ejercicio 2 con marca de correcta/incorrecta, 0 errores de JS.

---

## Cómo pegarlo en WordPress

Va en un **widget HTML de Elementor** dentro del Topic/Lección. No sirve pegarlo en el editor
clásico ni en un bloque de párrafo de Gutenberg: WordPress filtra los `<script>` y `wpautop`
te mete `<p>` y `<br>` que rompen el layout. Si no usás Elementor, usá un bloque
"HTML personalizado" y confirmá que el `<script>` sobreviva al guardar.

La lección debería estar con plantilla a ancho completo, sin sidebar.
