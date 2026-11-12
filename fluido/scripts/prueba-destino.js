const { chromium } = require('playwright');
// El boton final tiene que llevar a /courses/patient-journey-en-accion/ EN EL
// MISMO ORIGEN desde el que se sirve el capitulo. Asi, servido desde la
// plataforma, lleva a la plataforma; y si el sitio se muda, se muda con el.
const BASE = 'http://127.0.0.1:8901';
const RUTA = '/courses/patient-journey-en-accion/';
(async () => {
  const b = await chromium.launch();
  let mal = 0;
  for (let c = 1; c <= 6; c++) {
    const p = await b.newPage({ viewport: { width: 1280, height: 860 } });
    let destino = null, navego = null;
    p.on('console', m => { const t = m.text(); if (t.startsWith('GDFIN ')) destino = t.slice(6); });
    p.on('framenavigated', f => { if (f === p.mainFrame()) navego = f.url(); });
    await p.goto(`${BASE}/capitulo${c}-fluido.html`, { waitUntil: 'domcontentloaded' });
    await p.waitForTimeout(400);
    await p.evaluate(() => {
      const orig = window.parent.postMessage.bind(window.parent);
      window.parent.postMessage = (m, o) => { if (m && m.gdFin) console.log('GDFIN ' + m.destino); return orig(m, o); };
      const n = document.querySelectorAll('.scr').length;
      document.getElementById('menu').click();
      document.querySelector(`[data-ir="${n}"]`).click();
    });
    await p.waitForTimeout(400);
    await p.evaluate(() => document.getElementById('next').click());
    await p.waitForTimeout(1800);
    const ok = destino === BASE + RUTA && navego === BASE + RUTA;
    if (!ok) mal++;
    console.log(`cap${c}: gdFin.destino=${destino} | navego=${navego} ${ok ? 'OK' : '<-- MAL'}`);
    await p.close();
  }
  console.log(mal ? `\n${mal} con problema` : `\nLos 6 llevan a ${RUTA} en el mismo origen que sirve el capitulo`);
  await b.close();
})();
