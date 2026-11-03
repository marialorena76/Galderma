# Arma las seis laminas "solo el capitulo N a color" a partir de las siete que
# exporto Julieth, sin pedirle nada nuevo.
#
# Tres cosas que hubo que resolver:
#
# 1. Las laminas no estan alineadas entre si: entre pagina y pagina el dibujo
#    se corre hasta 9 px. Si se componen sin corregir, los numeros salen
#    duplicados. Se alinea todo contra el bloque del titulo, que es identico
#    en las siete.
#
# 2. En mapa-1 (solo el 01 a color) los capitulos 2 a 6 ya estan en el gris
#    autentico del Canva. Lo unico que hay que inventar es el 01 en gris, y la
#    formula se calibro contra los capitulos 2 a 6, donde se tiene el mismo
#    dibujo en color y en gris: luminancia 601, error mediano de 6 sobre 255.
#
# 3. El titulo "con proposito" esta en magenta en las siete laminas: no es de
#    ningun capitulo y nunca se apaga.
#
# Uso, parado en fluido/:
#     python3 scripts/laminas-solo.py
# Lee mapa-inicio.jpg y mapa-1..6.jpg y escribe mapa-solo-1..6.jpg.
# Necesita Pillow.
import os
from PIL import Image, ImageChops, ImageOps, ImageFilter
from collections import deque

ENTRADA = ['mapa-inicio.jpg'] + [f'mapa-{n}.jpg' for n in range(1, 7)]
CRUDAS = {n: Image.open(ENTRADA[n]).convert('RGB') for n in range(7)}
W, H = CRUDAS[1].size
CAJA_TITULO = (60, 115, 540, 225)
ALTO_TITULO = 240          # el magenta del titulo llega hasta y=210
REF = 1                    # mapa-1 es el marco de referencia

# ---------- 1. alinear ----------
def desvio(a, b, dx, dy):
    x0, y0, x1, y1 = CAJA_TITULO
    pa, pb = a.load(), b.load()
    s = n = 0
    for y in range(y0, y1):
        for x in range(x0, x1, 2):
            xx, yy = x + dx, y + dy
            if not (0 <= xx < W and 0 <= yy < H): continue
            s += abs(pa[x, y][0] - pb[xx, yy][0]); n += 1
    return s / n

def alinear(im, ref, alcance=16):
    if im is ref: return im
    e, dx, dy = min((desvio(ref, im, dx, dy), dx, dy)
                    for dx in range(-alcance, alcance + 1)
                    for dy in range(-alcance, alcance + 1))
    print(f"    corrimiento ({dx:3d},{dy:3d})  error {e:5.2f}")
    return im.transform((W, H), Image.AFFINE, (1, 0, dx, 0, 1, dy),
                        resample=Image.BICUBIC, fillcolor=(255, 255, 255))

print("alineando:")
P = {}
for n in range(1, 7):
    print(f"  p{n}", end=" ")
    P[n] = alinear(CRUDAS[n], CRUDAS[REF]) if n != REF else CRUDAS[REF]
    if n == REF: print("referencia")

# ---------- 2. de que capitulo es cada pixel ----------
def saturacion(im):
    r, g, b = im.split()
    mx = ImageChops.lighter(ImageChops.lighter(r, g), b)
    mn = ImageChops.darker(ImageChops.darker(r, g), b)
    return ImageChops.difference(mx, mn)

SAT = {n: saturacion(P[n]) for n in range(1, 7)}

def umbral(im, t, arriba=True):
    return im.point(lambda v: 255 if (v > t) == arriba else 0, mode='L')

def solo_lo_gordo(m, margen=90, minimo=250):
    """Se queda con la mancha grande del capitulo y con lo que la acompana.

    Entre export y export hay pixeles sueltos que cambian de saturacion en
    capitulos que ya estaban a color; sin filtro, la mascara del 4 arrastra
    restos del 1, 2 y 3. Pero el numero "04" tambien es una mancha chica y ese
    si tiene que entrar: se conserva todo lo que caiga cerca, en x, de la
    mancha mayor.
    """
    px = m.load()
    visto = [[False] * W for _ in range(H)]
    manchas = []
    for y0 in range(H):
        for x0 in range(W):
            if not px[x0, y0] or visto[y0][x0]: continue
            q = deque([(x0, y0)]); visto[y0][x0] = True; pts = []
            while q:
                x, y = q.popleft(); pts.append((x, y))
                for dx in (-1, 0, 1):
                    for dy in (-1, 0, 1):
                        nx, ny = x + dx, y + dy
                        if 0 <= nx < W and 0 <= ny < H and px[nx, ny] and not visto[ny][nx]:
                            visto[ny][nx] = True; q.append((nx, ny))
            manchas.append(pts)
    if not manchas: return m
    mayor = max(manchas, key=len)
    xs = [p[0] for p in mayor]
    lim0, lim1 = min(xs) - margen, max(xs) + margen
    salida = Image.new('L', (W, H), 0); ps = salida.load()
    for pts in manchas:
        if len(pts) < minimo: continue
        cx = sum(p[0] for p in pts) / len(pts)
        if not (lim0 <= cx <= lim1): continue
        for x, y in pts: ps[x, y] = 255
    return salida

def mascara(n):
    """Los pixeles que llevan color en el capitulo n.

    El gris del Canva es neutro exacto, saturacion 0, mientras que hasta el
    crema mas palido del piso mide 6 para arriba. Por eso el umbral puede ser
    bajisimo y entra todo el dibujo, no solo lo vistoso. Lo que queda afuera
    por ser neutro (las paredes blancas, el mobiliario) se ve igual en color
    que en gris, asi que no hace falta copiarlo.
    """
    m = ImageChops.multiply(umbral(SAT[n], 5), umbral(SAT[n - 1], 3, arriba=False))
    m = m.filter(ImageFilter.MaxFilter(3)).filter(ImageFilter.MinFilter(3))
    m = solo_lo_gordo(m, minimo=120)
    recorte = m.load()
    for y in range(ALTO_TITULO):
        for x in range(W): recorte[x, y] = 0
    return m

print("mascaras:")
M = {}
for n in range(2, 7):
    M[n] = mascara(n)
    print(f"  cap{n}: {M[n].getbbox()}")

# ---------- 3. componer ----------
# El fondo gris sale de griscar mapa-1 entero. Los capitulos 2 a 6 ya estaban
# en el gris del Canva y griscar un gris no lo mueve, asi que esos quedan
# intactos; el unico que se apaga de verdad es el 1, que de otro modo queda
# tibio porque su piso casi no tiene saturacion y una mascara por color no lo
# agarra. Se devuelve la franja del titulo, que va en magenta siempre.
gris = ImageOps.grayscale(P[1]).convert('RGB')
gris.paste(P[1].crop((0, 0, W, ALTO_TITULO)), (0, 0))
def guardar(im, nombre):
    im.save(nombre, quality=86, optimize=True)
    print(f"  {nombre}  {os.path.getsize(nombre) // 1024} KB")

# solo el capitulo 1 a color ya existe tal cual: es mapa-1
guardar(P[1], 'mapa-solo-1.jpg')
for n in range(2, 7):
    guardar(Image.composite(P[n], gris, M[n]), f'mapa-solo-{n}.jpg')
