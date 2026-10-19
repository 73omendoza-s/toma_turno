<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Turnos · Sala de espera</title>
    <style>
        * { box-sizing:border-box; }
        body { margin:0; background:#0b1220; color:#fff; font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; min-height:100vh; display:flex; flex-direction:column; }
        header { padding:1rem 2rem; font-size:1.6rem; font-weight:700; display:flex; justify-content:space-between; align-items:center; }
        #btn-sonido { font-size:1rem; padding:.4rem .9rem; border-radius:8px; border:0; cursor:pointer; }
        #llamados { flex:1; padding:0 2rem; }
        .fila { display:flex; align-items:center; gap:2rem; padding:.9rem 1.2rem; border-bottom:1px solid #1e293b; font-size:2rem; }
        .fila .folio { font-weight:800; min-width:9ch; letter-spacing:.05em; }
        .fila .dest { color:#cbd5e1; }
        .fila.principal { background:#1d4ed8; border-radius:14px; font-size:3.6rem; margin-bottom:1rem; border:0; }
        .fila.principal .dest { color:#fff; }
        .parpadeo { animation:parp .6s ease-in-out 6; }
        @keyframes parp { 50% { background:#f59e0b; } }
        .vacio { color:#64748b; font-size:2rem; padding:2rem 0; }
        footer { padding:1rem 2rem; background:#111a2e; display:flex; gap:2rem; flex-wrap:wrap; font-size:1.1rem; }
        footer span b { color:#fbbf24; }
    </style>
</head>
<body>
<header>
    <span>Turnos llamados</span>
    <button id="btn-sonido" type="button">Activar sonido</button>
</header>
<section id="llamados"><div class="vacio">Esperando turnos…</div></section>
<footer id="espera"></footer>

<script>
const URL_DATOS = '{{ route('pantalla.datos') }}';
let ultimo;            // undefined = aún no se ha cargado nada
let sonido = false;

document.getElementById('btn-sonido').addEventListener('click', function () { sonido = true; this.remove(); });

function beep() {
    if (!sonido) return;
    try {
        const c = new (window.AudioContext || window.webkitAudioContext)();
        const o = c.createOscillator(), g = c.createGain();
        o.connect(g); g.connect(c.destination);
        o.frequency.value = 880; g.gain.value = 0.2;
        o.start(); setTimeout(function () { o.stop(); c.close(); }, 450);
    } catch (e) { /* sin audio disponible */ }
}

function el(tag, clase, texto) {
    const e = document.createElement(tag);
    if (clase) e.className = clase;
    if (texto !== undefined) e.textContent = texto;
    return e;
}

async function cargar() {
    try {
        const r = await fetch(URL_DATOS, { headers: { 'Accept': 'application/json' } });
        if (!r.ok) return;
        const d = await r.json();

        const lista = document.getElementById('llamados');
        lista.replaceChildren();
        if (d.llamados.length === 0) lista.append(el('div', 'vacio', 'Sin turnos en atención'));
        d.llamados.forEach(function (t, i) {
            const fila = el('div', 'fila' + (i === 0 ? ' principal' : ''));
            fila.append(el('span', 'folio', t.folio), el('span', 'dest', t.ventanilla + ' · ' + t.area));
            lista.append(fila);
        });

        const clave = d.llamados.length ? d.llamados[0].id + '-' + d.llamados[0].llamada : null;
        if (ultimo !== undefined && clave !== null && clave !== ultimo) {
            beep();
            if (lista.firstChild) lista.firstChild.classList.add('parpadeo');
        }
        ultimo = clave;

        const pie = document.getElementById('espera');
        pie.replaceChildren();
        d.espera.forEach(function (a) {
            const s = el('span', null, a.area + ': ');
            s.append(el('b', null, String(a.en_espera)));
            pie.append(s);
        });
    } catch (e) { /* se reintenta en el siguiente ciclo */ }
}
cargar();
setInterval(cargar, 3000);
</script>
</body>
</html>
