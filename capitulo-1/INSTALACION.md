# Cómo montar el Capítulo 1 en LearnDash

## La recomendación: archivo aparte + iframe (NO pegar los 99 KB en el widget)

### Por qué no pegarlo directo en el widget HTML de Elementor

| | Pegado en el widget | Archivo + iframe |
|---|---|---|
| Peso en la base de datos | ~100 KB por capítulo dentro de `_elementor_data` (postmeta), escapado como JSON. Por 6 capítulos, ~600 KB de markup en la DB | 3 líneas por lección |
| Editar una coma | Abrir Elementor, buscar el widget, volver a pegar los 99 KB, regenerar CSS | Reemplazar un archivo por FTP / Administrador de archivos |
| Editor de Elementor | Se pone pesado, el widget HTML con 1000 líneas es incómodo | Liviano |
| Conflictos de estilo | El CSS del tema, de BuddyBoss y de Elementor entran en la lección y pueden pisar estilos | Aislado: adentro del iframe no entra nada de afuera |
| Cache (LiteSpeed) | Se cachea junto con toda la página; al purgar la lección se regenera todo | El archivo estático se sirve directo, rapidísimo |
| Riesgo al guardar | Elementor y los sanitizadores pueden tocar el markup | El archivo no pasa por WordPress |
| Reusar en los 6 capítulos | Copiar y pegar 6 veces | 6 archivos, 6 iframes de 3 líneas |

El único punto a favor de pegarlo directo es que no necesitás subir archivos por FTP.
Si lo que querés es verlo funcionando hoy en un capítulo, pegalo y listo. Para los 6
capítulos y el mantenimiento a futuro, conviene el iframe.

---

## Pasos

### 1. Subir el archivo
En hPanel de Hostinger → **Administrador de archivos** (o por FTP), creá la carpeta:

```
public_html/wp-content/uploads/academia/
```

y subí ahí `capitulo1.html`.

> Ojo: la Biblioteca de Medios de WordPress **no** deja subir `.html` por defecto (lo bloquea
> por seguridad). Por eso va por Administrador de archivos / FTP, no por el uploader de WP.

Probá que abra solo, entrando a:
`https://TU-DOMINIO.com/wp-content/uploads/academia/capitulo1.html`

### 2. Pegar el embed en la lección
En el Topic/Lección de LearnDash, un **widget HTML de Elementor** con el contenido de
`embed-elementor.html` (son 20 líneas). Cambiá `TU-DOMINIO.com` por el dominio real.

### 3. Configurar la lección
- Plantilla de página a **ancho completo, sin sidebar**. El diseño es una diapositiva de
  1920×1080 escalada: cuanto más angosta la columna, más chico el texto.
- En un celular de 390 px la diapositiva queda de 294 px de alto y el texto muy chico.
  Para eso está el botón **"Pantalla completa"** de la barra inferior: es la forma de
  leerlo cómodo en celular y tablet. Conviene avisárselo al alumno en el texto de la lección.

### 4. Para los capítulos siguientes
Copiar `capitulo1.html` como `capitulo2.html` y cambiar **dos cosas**:
- en el archivo: `var ID = "cap1";` → `"cap2"`
- en el embed: los tres `cap1` → `cap2` (el `id` del iframe, el `ID` del script y el `src`)

---

## Antes de publicar
- [ ] En `capitulo1.html`, poner la URL real del Quiz de LearnDash en `quizUrl: "#"`.
      Si queda en `"#"`, el botón "Finalizar Capítulo" no hace nada.
- [ ] Reemplazar los dos `PLACEHOLDER — REEMPLAZAR POR FOTO FINAL` (pantallas 1 y 3).
- [ ] Confirmar que el video `youtube.com/embed/D6t7b_pTBDU` sea el correcto.
- [ ] Purgar la cache de LiteSpeed después de subir.

## Verificado
Probado en Chromium sirviendo el archivo por HTTP y embebido en una página con el iframe:
- Escritorio 1280 px: el iframe se ajusta solo a 689 px, sin scroll interno ni hueco (gap 5 px).
- Celular 390 px: el iframe se ajusta a 294 px (gap 4 px).
- Con Google Fonts caído/bloqueado: la lección igual renderiza y navega las 8 pantallas
  (la fuente se carga sin bloquear el render).
- Navegación 1→8, Ejercicio 1 por toque con validación y feedback, Ejercicio 2 marcando
  correcta/incorrecta. Cero errores de JavaScript.
