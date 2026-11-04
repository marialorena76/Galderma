#!/usr/bin/env bash
# Arma los dos ZIP de entrega a partir de entrega/.
#
#   galderma-para-subir.zip   el paquete completo: academia/ + embeds + docs.
#   academia-contenido.zip    SOLO los 14 archivos, sueltos, sin carpeta adentro.
#
# Los dos existen por una razon: si el paquete completo se descomprime DENTRO
# de uploads/academia/ queda uploads/academia/academia/ y los iframes dan 404.
# Para ese caso va el segundo, que se descomprime ahi mismo sin anidar.
set -euo pipefail
raiz="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$raiz"
[ -d entrega/academia ] || { echo "falta entrega/academia: corre primero node fluido/build.js"; exit 1; }

rm -f galderma-para-subir.zip academia-contenido.zip
( cd entrega && zip -rq ../galderma-para-subir.zip . )
( cd entrega/academia && zip -rq ../../academia-contenido.zip . )

for z in galderma-para-subir.zip academia-contenido.zip; do
  printf '%-26s %s  %s archivos\n' "$z" "$(du -h "$z" | cut -f1)" "$(unzip -l "$z" | tail -1 | awk '{print $2}')"
done
