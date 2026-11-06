#!/usr/bin/env python3.13
"""
Convierte las laminas del mapa a los WebP que espera el plugin gd-mapa-progreso.

El plugin las lee de wp-content/uploads/academia/mapa/ con ESTOS nombres:

    mapa-portada.webp           los seis capitulos a color
    mapa-1.webp .. mapa-6.webp  capitulos 1..N a color, el resto en gris

Nuestras laminas se llaman distinto (mapa-inicio.jpg, mapa-N.jpg), asi que la
conversion tambien renombra. Si se tocan las laminas hay que correr esto y
subir el resultado, o el mapa del curso queda con el arte viejo.

    python3.13 fluido/scripts/webp-mapa.py

Deja los archivos en fluido/mapa-webp/.
"""
import os
import sys

from PIL import Image

RAIZ = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SALIDA = os.path.join(RAIZ, "mapa-webp")

PARES = [("mapa-inicio.jpg", "mapa-portada.webp")] + [
    ("mapa-%d.jpg" % n, "mapa-%d.webp" % n) for n in range(1, 7)
]

# El plugin posiciona las zonas clickeables en % de 1920x1080: si la lamina
# viene en otra medida, los porcentajes siguen cerrando, pero conviene saberlo.
MEDIDA = (1920, 1080)


def main():
    os.makedirs(SALIDA, exist_ok=True)
    faltan = [o for o, _ in PARES if not os.path.exists(os.path.join(RAIZ, o))]
    if faltan:
        sys.exit("faltan laminas en %s: %s" % (RAIZ, ", ".join(faltan)))

    for origen, destino in PARES:
        im = Image.open(os.path.join(RAIZ, origen)).convert("RGB")
        if im.size != MEDIDA:
            print("  aviso: %s mide %dx%d, se esperaba %dx%d" % ((origen,) + im.size + MEDIDA))
        ruta = os.path.join(SALIDA, destino)
        im.save(ruta, "WEBP", quality=86, method=6)
        print(
            "%-18s -> %-20s %4d KB"
            % (origen, destino, os.path.getsize(ruta) // 1024)
        )

    print("\nListo en %s" % SALIDA)
    print("Van a wp-content/uploads/academia/mapa/, pisando las que estan.")


if __name__ == "__main__":
    main()
