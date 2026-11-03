# El mapa del curso

`mapa.html` es la portada del recorrido. Una sola página, siete estados.

## Cómo elige el estado

Por la URL:

| Dónde va | src | Qué se ve |
|---|---|---|
| Portada del curso | `mapa.html` | Los seis a color |
| Capítulo 1 | `mapa.html?cap=1` | Solo el 01 a color |
| Capítulo 2 | `mapa.html?cap=2` | 01 y 02 a color |
| … | … | … |
| Capítulo 6 | `mapa.html?cap=6` | Los seis a color |

## Dos maneras de verlo

**En pantalla ancha** muestra la lámina del Canva de Julieth tal cual, una
imagen por estado. Son archivos sueltos al lado de `mapa.html`, no van
embebidos: la página carga solo la que necesita.

**En celular** (menos de 860 px) muestra las seis fichas en grilla, con la
misma lógica de encendido. Una lámina apaisada de 16:9 en un teléfono deja
los rótulos ilegibles.

Si las láminas todavía no están subidas, la grilla aparece en cualquier
ancho. La página nunca queda vacía.

## Exportar las láminas del Canva

Canva → Compartir → Descargar → PNG, tamaño **2x**, las 7 páginas.
Vienen numeradas 1 a 7. Renombrar así antes de subirlas:

| Página del Canva | Archivo |
|---|---|
| 1 (todo a color) | `mapa-inicio.png` |
| 2 (solo 01) | `mapa-1.png` |
| 3 (01 y 02) | `mapa-2.png` |
| 4 (01 a 03) | `mapa-3.png` |
| 5 (01 a 04) | `mapa-4.png` |
| 6 (01 a 05) | `mapa-5.png` |
| 7 (los seis) | `mapa-6.png` |

**La página 1 no es `mapa-1.png`.** Es `mapa-inicio.png`. El corrimiento es
porque hay siete estados para seis capítulos.

Sirve `.jpg` con el mismo nombre si pesan mucho en PNG.

Van a `/wp-content/uploads/academia/`, junto a `mapa.html`.

## Recompilar

`node build.js mapa` toma `mapa-fluido.tpl.html` y escribe `mapa.html`.
Las láminas no entran ahí: se suben aparte.
