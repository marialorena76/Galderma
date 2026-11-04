# El mapa del curso

`mapa.html` es la portada del recorrido. Una sola página, siete estados.

## Cómo elige el estado

Por la URL. El mapa **acumula**: al terminar el capítulo N quedan a color del
1 al N.

| Dónde va | src | Qué se ve |
|---|---|---|
| Portada del curso | `mapa.html` | Los seis a color |
| Al terminar el capítulo 1 | `mapa.html?cap=1` | El 01 a color |
| Al terminar el capítulo 3 | `mapa.html?cap=3` | 01, 02 y 03 a color |
| … | … | … |
| Al terminar el capítulo 6 | `mapa.html?cap=6` | Los seis a color |

## Dos maneras de verlo

**En pantalla ancha** muestra la lámina del Canva de Julieth tal cual, una
imagen por estado. Son archivos sueltos al lado de `mapa.html`, no van
embebidos: la página carga solo la que necesita.

**En celular** (menos de 860 px) muestra las seis fichas en grilla, con la
misma lógica de encendido. Una lámina apaisada de 16:9 en un teléfono deja
los rótulos ilegibles.

Si las láminas todavía no están subidas, la grilla aparece en cualquier
ancho. La página nunca queda vacía.

## Las láminas

Viven en `fluido/`, al lado de `mapa.html`. Son 1920x1080, JPEG de calidad 86,
unos 230 KB cada una (en PNG pesaban 1,4 MB). Se suben tal cual.

Son siete: `mapa-inicio.jpg` y `mapa-1.jpg` … `mapa-6.jpg`, las siete páginas
del Canva.

### Si alguna vez se quiere el otro criterio

Se evaluó mostrar a color **solo** el capítulo recién terminado, con el resto
en gris. Se descartó: el Canva está armado acumulativo y así se había
definido. Pero las seis láminas de ese criterio se pueden regenerar sin
molestar a Julieth, con `scripts/laminas-solo.py` parado en `fluido/`. Escribe
`mapa-solo-1.jpg` … `mapa-solo-6.jpg` a partir de las siete originales.
Habría que volver a enganchar esos nombres en `mapa-fluido.tpl.html`.

Para volver a exportar: Canva → Compartir → Descargar → PNG, tamaño **2x**,
las 7 páginas. Vienen numeradas 1 a 7. Renombrar así:

| Página del Canva | Archivo |
|---|---|
| 1 (todo a color) | `mapa-inicio.jpg` |
| 2 (solo 01) | `mapa-1.jpg` |
| 3 (01 y 02) | `mapa-2.jpg` |
| 4 (01 a 03) | `mapa-3.jpg` |
| 5 (01 a 04) | `mapa-4.jpg` |
| 6 (01 a 05) | `mapa-5.jpg` |
| 7 (los seis) | `mapa-6.jpg` |

**La página 1 no es `mapa-1`.** Es `mapa-inicio`. El corrimiento es porque
hay siete estados para seis capítulos.

Para pasar los PNG del Canva a JPEG:

```
convert 1.png -background white -flatten -quality 86 -strip mapa-inicio.jpg
```

La página busca primero `.jpg` y después `.png`, así que cualquiera de los
dos sirve.

Van a `/wp-content/uploads/academia/`, junto a `mapa.html`.

## Recompilar

`node build.js mapa` toma `mapa-fluido.tpl.html` y escribe `mapa.html`.
Las láminas no se embeben ahí: son archivos sueltos y se suben aparte.

## Al subir: no anidar la carpeta

`galderma-para-subir.zip` trae `academia/` adentro. Si se descomprime *dentro*
de `wp-content/uploads/academia/`, queda `uploads/academia/academia/` y todos
los iframes dan 404, porque apuntan a `uploads/academia/capituloN.html`.

En la ruta tiene que haber **una sola** `academia`.

- Descomprimir el paquete completo en `wp-content/uploads/` → correcto.
- Descomprimir dentro de `uploads/academia/` → usar `academia-contenido.zip`,
  que trae los 14 archivos sueltos, sin carpeta.

Los dos los arma `fluido/scripts/paquete.sh`.
