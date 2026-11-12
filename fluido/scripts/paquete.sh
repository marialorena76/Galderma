#!/usr/bin/env bash
# Arma los dos ZIP de entrega.
#
#   galderma-para-subir.zip   el paquete completo: academia/ + embeds + docs.
#   academia-contenido.zip    SOLO los 14 archivos, sueltos, sin carpeta adentro.
#
# Los dos existen por una razon: si el paquete completo se descomprime DENTRO
# de uploads/academia/ queda uploads/academia/academia/ y los iframes dan 404.
# Para ese caso va el segundo, que se descomprime ahi mismo sin anidar.
#
# El 12/11 este script entrego capitulos viejos: zipeaba entrega/academia/ sin
# sincronizarla con fluido/, que es donde escribe build.js, y esa carpeta habia
# quedado congelada dos dias atras. Lore subio el paquete sin que nada fallara y
# el servidor quedo con el build anterior. Por eso ahora el script CONSTRUYE,
# SINCRONIZA y despues VERIFICA byte a byte que lo que entra al ZIP sea lo mismo
# que hay en fluido/. Si algo no coincide, aborta.
set -euo pipefail
raiz="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$raiz"

echo "== 1. construyendo los seis capitulos =="
# build.js lee las plantillas por ruta relativa, asi que corre desde fluido/.
for n in 1 2 3 4 5 6; do ( cd fluido && node build.js "$n" >/dev/null ); done
echo "   ok"

echo "== 2. sincronizando entrega/ con fluido/ =="
mkdir -p entrega/academia
# fuente -> destino. Si se agrega un archivo a la entrega, va aca.
copias=(
  "fluido/capitulo1.html:entrega/academia/capitulo1.html"
  "fluido/capitulo2.html:entrega/academia/capitulo2.html"
  "fluido/capitulo3.html:entrega/academia/capitulo3.html"
  "fluido/capitulo4.html:entrega/academia/capitulo4.html"
  "fluido/capitulo5.html:entrega/academia/capitulo5.html"
  "fluido/capitulo6.html:entrega/academia/capitulo6.html"
  "fluido/mapa.html:entrega/academia/mapa.html"
  "fluido/mapa-inicio.jpg:entrega/academia/mapa-inicio.jpg"
  "fluido/mapa-1.jpg:entrega/academia/mapa-1.jpg"
  "fluido/mapa-2.jpg:entrega/academia/mapa-2.jpg"
  "fluido/mapa-3.jpg:entrega/academia/mapa-3.jpg"
  "fluido/mapa-4.jpg:entrega/academia/mapa-4.jpg"
  "fluido/mapa-5.jpg:entrega/academia/mapa-5.jpg"
  "fluido/mapa-6.jpg:entrega/academia/mapa-6.jpg"
  "fluido/embed-cap1.html:entrega/embed-cap1.html"
  "fluido/embed-cap2.html:entrega/embed-cap2.html"
  "fluido/embed-cap3.html:entrega/embed-cap3.html"
  "fluido/embed-cap4.html:entrega/embed-cap4.html"
  "fluido/embed-cap5.html:entrega/embed-cap5.html"
  "fluido/embed-cap6.html:entrega/embed-cap6.html"
  "fluido/embed-mapa.html:entrega/embed-mapa.html"
)
cambiados=0
for par in "${copias[@]}"; do
  src="${par%%:*}"; dst="${par#*:}"
  [ -f "$src" ] || { echo "   FALTA $src"; exit 1; }
  if ! cmp -s "$src" "$dst" 2>/dev/null; then
    cp -p "$src" "$dst"
    echo "   actualizado  $dst"
    cambiados=$((cambiados + 1))
  fi
done
[ "$cambiados" -eq 0 ] && echo "   ya estaba todo al dia"

echo "== 3. armando los ZIP =="
rm -f galderma-para-subir.zip academia-contenido.zip
( cd entrega && zip -rq ../galderma-para-subir.zip . )
( cd entrega/academia && zip -rq ../../academia-contenido.zip . )

echo "== 4. verificando que el ZIP traiga lo mismo que fluido/ =="
tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT
unzip -qo academia-contenido.zip -d "$tmp"
fallas=0
for n in 1 2 3 4 5 6; do
  if cmp -s "fluido/capitulo$n.html" "$tmp/capitulo$n.html"; then
    printf '   capitulo%s.html  %8s bytes  ok\n' "$n" "$(stat -c%s "$tmp/capitulo$n.html")"
  else
    printf '   capitulo%s.html  NO COINCIDE con fluido/\n' "$n"
    fallas=$((fallas + 1))
  fi
done
cmp -s fluido/mapa.html "$tmp/mapa.html" || { echo "   mapa.html NO COINCIDE"; fallas=$((fallas + 1)); }
if [ "$fallas" -ne 0 ]; then
  echo
  echo "ABORTADO: $fallas archivo(s) del ZIP no coinciden con fluido/. No subas esto."
  exit 1
fi

echo
for z in galderma-para-subir.zip academia-contenido.zip; do
  printf '%-26s %s  %s archivos\n' "$z" "$(du -h "$z" | cut -f1)" "$(unzip -l "$z" | tail -1 | awk '{print $2}')"
done
echo
echo "Verificado: los seis capitulos del ZIP son identicos a los de fluido/."
