const fs = require('fs');
let h = fs.readFileSync('cap1.tpl.html', 'utf8');
const dirs = ['assets/slide3', 'assets/cap1'];
// 1) juntar los assets referenciados por data-i
const names = new Set();
h.replace(/data-i="([a-z0-9_]+)"/g, (m, n) => { names.add(n); return m; });
h.replace(/(?:ic|ill):"([a-z0-9_]+)"/g, (m, n) => { names.add(n); return m; });
const map = {}; const faltan = [];
let bytes = 0;
for (const n of names) {
  let found = null;
  for (const d of dirs) for (const ext of ['png','jpg']) {
    const p = `${d}/${n}.${ext}`;
    if (fs.existsSync(p)) { found = { p, ext }; break; }
    }
  if (!found) { faltan.push(n); continue; }
  const buf = fs.readFileSync(found.p); bytes += buf.length;
  map[n] = `data:image/${found.ext === 'jpg' ? 'jpeg' : 'png'};base64,${buf.toString('base64')}`;
}
if (faltan.length) console.log('FALTAN:', faltan.join(', '));
h = h.replace('__IMGS__', JSON.stringify(map));
// 2) los que quedaron como __x__ sueltos (por si alguno se escapo)
h = h.replace(/__([a-z0-9_]+)__/g, (m, n) => map[n] || m);
fs.writeFileSync('/home/user/Galderma/fluido/capitulo1-fluido.html', h);
fs.writeFileSync('serve/capitulo1-fluido.html', h);
console.log('assets unicos:', Object.keys(map).length, '| crudos:', Math.round(bytes/1024)+' KB | archivo final:', Math.round(h.length/1024)+' KB');
