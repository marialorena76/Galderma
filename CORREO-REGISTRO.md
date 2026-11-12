# Correo de registro · las tres correcciones de Fernanda

**De:** Lorena González · 12/11/2026

Fernanda se registró, quedó esperando aprobación y marcó tres cosas del mail
automático que recibe el que se registra. Las tres se arreglan desde wp-admin;
no hay nada que tocar en los capítulos.

El mail lo manda **Ultimate Member** (es la plantilla *Account Awaiting Admin Review*).

---

## Lo que dice hoy

> Hola Fernanda Grasso,
>
> Recibimos tu registro en **.** Estamos revisándolo y, cuando lo aprobemos, te va
> a llegar otro mail con los datos para ingresar.
>
> Gracias por tu paciencia.

---

## 1. "La primera oración está como cortada" — por qué pasa

No está cortada: le **falta el nombre del sitio**.

La plantilla dice `Recibimos tu registro en {site_name}.` y `{site_name}` sale del
**título del sitio** de WordPress, que **está vacío**.

Lo verifiqué de dos maneras:

| Dónde | Qué devuelve |
|---|---|
| Datos del sitio | `name: ""` |
| `<title>` de la home | `""` (vacío) |

O sea que el título vacío no sólo rompe este mail: **la pestaña del navegador
también sale en blanco**, y cualquier otro mail que use `{site_name}` va a tener
el mismo agujero — incluido el de aprobación que Fernanda va a recibir ahora, y el
de recuperar contraseña.

### Cómo se arregla

**Ajustes → Generales → Título del sitio** → escribir `Experiencia con Propósito`
y guardar.

> ⚠️ Un chequeo después de guardar: el sitio no tiene logo cargado
> (`custom_logo` está vacío). Algunos temas, cuando no hay logo, muestran el
> título del sitio como texto en la cabecera. Mirá la home después de guardar.
> Si aparece y no lo querés, se saca con CSS y te lo paso.

Por las dudas, en el texto nuevo de abajo escribí el nombre **a mano** en vez de
usar `{site_name}`. Así el mail queda bien aunque el título del sitio quede vacío.

---

## 2 y 3. El cierre

Fernanda dijo: *"no pondría gracias por tu paciencia"* y *"sino algo como nos
encontramos pronto"*.

---

## Texto nuevo, para pegar tal cual

**Dónde:** Ultimate Member → Settings → Email → **Account Awaiting Admin Review**

**Asunto:**

```
Recibimos tu registro en Experiencia con Propósito
```

**Cuerpo:**

```
Hola {display_name},

Recibimos tu registro en Experiencia con Propósito. Estamos revisándolo y,
cuando lo aprobemos, te va a llegar otro mail con los datos para ingresar.

¡Nos encontramos pronto!
```

---

## De paso, dos cosas que vi mientras buscaba

1. **La zona horaria del sitio está en UTC+0**, no en Buenos Aires (UTC−3). Las
   fechas de finalización de los capítulos y cualquier dato con hora van a salir
   corridos tres horas. Se cambia en **Ajustes → Generales → Zona horaria** →
   `Buenos Aires`.

2. **La caché activa es LiteSpeed, no SpeedyCache.** SpeedyCache está instalado
   pero desactivado. Cuando subas archivos nuevos hay que purgar **LiteSpeed**.
   (Te lo había dicho al revés en los mensajes anteriores.)
