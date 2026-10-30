// Arma capituloN.html: toma capituloN-fluido.tpl.html y embebe cada asset una sola vez.
//   node build.js 2        -> solo el capitulo 2
//   node build.js mapa     -> el mapa del curso (mapa-fluido.tpl.html)
//   node build.js          -> los seis capitulos y el mapa
// Un asset se nombra por su basename (se busca en assets/capN y despues en assets)
// o con carpeta, cap3/ilus_portada, cuando hace falta el de otro capitulo.
const fs = require('fs');
const SERVE = '/tmp/claude-0/-home-user-Galderma/e9a3e0b2-0d2e-54d7-9e29-30d638297a63/scratchpad/serve';

function construir(n) {
  const esCap = /^[1-6]$/.test(String(n));
  const base = esCap ? `capitulo${n}` : String(n);
  const tpl = `${base}-fluido.tpl.html`;
  let h = fs.readFileSync(tpl, 'utf8');
  const dirs = esCap ? [`assets/cap${n}`, 'assets'] : ['assets'];
  const names = new Set();
  h.replace(/data-i="([a-z0-9_/]+)"/g, (m, x) => { names.add(x); return m; });
  h.replace(/(?:ic|ill):"([a-z0-9_/]+)"/g, (m, x) => { names.add(x); return m; });
  const map = {}, faltan = []; let bytes = 0;
  for (const name of names) {
    let found = null;
    // con carpeta se busca tal cual bajo assets/; si no, en el capitulo y despues en el comun
    for (const d of name.includes('/') ? ['assets'] : dirs) { for (const ext of ['png', 'jpg']) {
      const p = `${d}/${name}.${ext}`;
      if (fs.existsSync(p)) { found = { p, ext }; break; }
    } if (found) break; }
    if (!found) { faltan.push(name); continue; }
    const buf = fs.readFileSync(found.p); bytes += buf.length;
    map[name] = `data:image/${found.ext === 'jpg' ? 'jpeg' : 'png'};base64,${buf.toString('base64')}`;
  }
  if (faltan.length) console.log(`  ${base} FALTAN:`, faltan.join(', '));
  h = h.replace('__IMGS__', JSON.stringify(map));
  h = h.replace(/__([a-z0-9_]+)__/g, (m, x) => map[x] || m);
  fs.writeFileSync(`${base}.html`, h);
  fs.writeFileSync(`${base}-fluido.html`, h);
  try { fs.mkdirSync(SERVE, { recursive: true }); fs.writeFileSync(`${SERVE}/${base}-fluido.html`, h); } catch (e) {}
  console.log(`${base}: ${Object.keys(map).length} assets | ${Math.round(bytes/1024)} KB crudos | ${Math.round(h.length/1024)} KB final`);
}

const arg = process.argv[2];
(arg ? [arg] : [1,2,3,4,5,6,'mapa']).forEach(construir);
