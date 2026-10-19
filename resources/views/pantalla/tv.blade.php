<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Turnos</title>
    <style>
        {{-- Colores institucionales: se administran en /admin/institucional --}}
        {!! \App\Support\Institucional::css() !!}
        :root { --texto: #FFFFFF; }

        html { font-size: min(1.05vw, 1.9vh); }    /* escala con el menor entre ancho y alto: nunca se desborda */
        * { box-sizing: border-box; }
        body { margin: 0; height: 100vh; overflow: hidden; font-family: system-ui, "Segoe UI", sans-serif; background: #F3EBDD; color: var(--guinda-osc);
               display: grid; grid-template-rows: 8rem minmax(0, 1fr) 8.5rem; gap: .6rem; }

        /* ---------- Encabezado ---------- */
        header { background: linear-gradient(180deg, var(--guinda), var(--guinda-osc)); color: var(--texto);
                 display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; border-bottom: .3rem solid var(--dorado); }
        .marca { display: flex; align-items: center; gap: 1.5rem; }
        .marca img { height: 6.8rem; }
        .marca h1 { margin: 0; font-size: 2.8rem; font-weight: 800; letter-spacing: .03em; text-transform: uppercase; }
        .marca p { margin: .1rem 0 0; font-size: 1.5rem; font-style: italic; color: var(--dorado-claro); text-transform: uppercase; }
        .derecha { display: flex; align-items: center; gap: 2.2rem; }
        .logo-v2 { height: 6.8rem; width: auto; max-width: 22rem; object-fit: contain; }
        .fecha { text-align: right; font-size: 1.6rem; line-height: 1.25; }
        .fecha b { font-size: 2.2rem; display: block; }

        /* ---------- Centro ---------- */
        .centro { display: grid; grid-template-columns: 2.1fr 1fr; gap: .8rem; padding: 0 .8rem; min-height: 0; }
        .actual { background: linear-gradient(160deg, var(--crema-1), var(--crema-2)); border: .2rem solid var(--dorado); border-radius: 1.2rem;
                  display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: .8rem; min-height: 0; overflow: hidden; }
        .actual .etq { font-size: 3.2rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--guinda); }
        .actual .folio { font-size: min(14rem, 25vh); font-weight: 900; line-height: 1; letter-spacing: .03em; color: var(--guinda-osc); margin: .4rem 0; }
        .actual .destino { font-size: 3.2rem; font-weight: 700; color: var(--guinda-osc); }
        .actual .destino span { color: var(--guinda); }
        .actual .vacio { font-size: 3.4rem; color: var(--guinda); opacity: .7; }
        .actual.nuevo { animation: parpadeo 1s ease-in-out 4; }
        @keyframes parpadeo { 50% { background: var(--dorado-claro); } }

        .lateral { background: var(--crema-1); border: .2rem solid var(--dorado); border-radius: 1.2rem; padding: 1rem 1.2rem; min-height: 0; overflow: hidden;
                   display: flex; flex-direction: column; }
        .lateral h2 { margin: 0 0 .5rem; font-size: 2.2rem; color: var(--guinda-osc); }
        #ultimos { flex: 1; min-height: 0; display: grid; grid-template-rows: repeat(4, minmax(0, 1fr)); gap: .5rem; }
        .fila { background: linear-gradient(180deg, var(--guinda), var(--guinda-osc)); color: var(--texto); border-radius: .8rem; min-height: 0; overflow: hidden;
                padding: .3rem 1rem; display: flex; flex-wrap: wrap; align-items: center; align-content: center; column-gap: 1rem; }
        .fila b { font-size: 2.4rem; letter-spacing: .03em; white-space: nowrap; }
        .fila span { font-size: 1.5rem; white-space: nowrap; }
        .fila small { flex-basis: 100%; font-size: 1.15rem; line-height: 1.1; color: var(--dorado-claro); text-align: left; }

        /* ---------- Banners inferiores ---------- */
        .banners { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; padding: 0 .8rem .8rem; min-height: 0; }
        .banner { position: relative; border-radius: 1rem; overflow: hidden; border: .2rem solid var(--dorado);
                  background: linear-gradient(120deg, var(--guinda-osc), var(--guinda)); background-size: cover; background-position: center; background-repeat: no-repeat; }
        .banner.contener { background-color: var(--guinda-osc); background-size: contain; }
        .banner .texto { position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; padding: .6rem 1.6rem; overflow: hidden;
                         background: linear-gradient(90deg, rgba(var(--guinda-osc-rgb), .94) 0%, rgba(var(--guinda-osc-rgb), .8) 55%, transparent 90%); color: var(--texto); text-shadow: 0 .1rem .3rem #000a; transition: opacity .6s; }
        .banner .texto b { font-size: 2rem; line-height: 1.1; }
        .banner .texto span { font-size: 1.35rem; line-height: 1.2; margin-top: .25rem; color: var(--dorado-claro); }

        #activar { position: fixed; inset: 0; background: #000c; color: var(--dorado-claro); font-size: 2.4rem; border: 0; cursor: pointer; z-index: 10; }
    </style>
</head>
<body>
    <button id="activar">🔊 Clic aquí para activar el sonido</button>

    <header>
        <div class="marca">
            {{-- Escudo, nombre y dependencia: se administran en /admin/institucional --}}
            <img src="{{ \App\Support\Institucional::logo('logo_escudo') }}" alt="" onerror="this.style.display='none'">
            <div>
                <h1>{{ \App\Support\Institucional::get('inst_nombre') }}</h1>
                <p>{{ \App\Support\Institucional::get('inst_subtitulo') }}</p>
            </div>
        </div>
        <div class="derecha">
            <img class="logo-v2" src="{{ \App\Support\Institucional::logo('logo_secundario') }}" alt="" onerror="this.style.display='none'">
            <div class="fecha"><b id="hora"></b><span id="fecha"></span></div>
        </div>
    </header>

    <section class="centro">
        <div class="actual" id="actual"><div class="vacio">Esperando turnos…</div></div>
        <aside class="lateral">
            <h2>Últimos llamados</h2>
            <div id="ultimos"></div>
        </aside>
    </section>

    <section class="banners">
        <div class="banner" id="banner1"></div>
        <div class="banner" id="banner2"></div>
    </section>

    <script>
        const URL_DATOS = "{{ route('pantalla.datos') }}";
        const URL_BANNERS = "{{ route('pantalla.banners') }}";
        const URL_INST = "{{ route('pantalla.institucional') }}";
        const VERSION_INST = "{{ \App\Support\Institucional::version() }}";

        // ------------------------------------------------------------------
        // BANNERS INFERIORES (se alternan de dos en dos cada 8 s)
        // Los banners se administran en /admin/banners; esta lista es solo de respaldo
        // si el servidor no responde la primera vez.
        // ------------------------------------------------------------------
        const IMG = "{{ asset('img') }}/";
        let CONTENIDOS = [
            { imagen: 'banner1.png', titulo: 'Servicios Públicos',          subtitulo: 'Trabajamos por una ciudad limpia y segura',          ajuste: 'cubrir', ancho: 60 },
            { imagen: 'banner2.png', titulo: 'Estación de Transferencia',    subtitulo: 'Separa tus residuos y deposítalos donde corresponde', ajuste: 'cubrir', ancho: 60 },
            { imagen: 'banner3.jpg', titulo: 'App La Paz',                   subtitulo: 'Reporta desde tu celular: alumbrado, fugas de agua, basura y más', ajuste: 'cubrir', ancho: 72 },
            { imagen: 'banner4.jpg', titulo: 'Visor Urbano',                 subtitulo: 'Catastro y adeudo predial en visorurbano.lapaz.gob.mx', ajuste: 'cubrir', ancho: 58 },
            { imagen: 'banner5.jpg', titulo: 'Tiburón Urbano',               subtitulo: 'Tu transporte público en La Paz',                    ajuste: 'cubrir', ancho: 66 },
            { imagen: 'banner6.jpg', titulo: 'Espacios públicos',            subtitulo: 'Parques y canchas',                                  ajuste: 'cubrir', ancho: 40 },
        ];

        let sonido = false, ctx = null, tokenPrevio = null, primera = true, indice = 0;

        document.getElementById('activar').addEventListener('click', () => {
            sonido = true;
            ctx = new (window.AudioContext || window.webkitAudioContext)();
            document.getElementById('activar').remove();
            pitido();
        });

        // En la CPU de la sala (Chrome con --autoplay-policy=no-user-gesture-required) el sonido arranca solo.
        // En cualquier otro navegador queda el botón "Clic aquí para activar el sonido".
        (function sonidoAutomatico() {
            try {
                const prueba = new (window.AudioContext || window.webkitAudioContext)();
                if (prueba.state === 'running') {
                    ctx = prueba; sonido = true;
                    document.getElementById('activar')?.remove();
                } else {
                    prueba.close();
                }
            } catch (e) { /* se queda el botón */ }
        })();

        function pitido() {
            if (!ctx) return;
            const o = ctx.createOscillator(), g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            o.frequency.value = 880;
            g.gain.setValueAtTime(0.4, ctx.currentTime);
            g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.9);
            o.start(); o.stop(ctx.currentTime + 0.9);
        }

        function hablar(t) {
            if (!('speechSynthesis' in window)) return;
            const u = new SpeechSynthesisUtterance(`Turno ${t.folio.replace('-', ' ')}, pase al área de ${t.area}, ${t.ventanilla}`);
            u.lang = 'es-MX'; u.rate = 0.9;
            speechSynthesis.cancel(); speechSynthesis.speak(u);
        }

        function el(tag, clase, texto) {
            const e = document.createElement(tag);
            if (clase) e.className = clase;
            if (texto !== undefined) e.textContent = texto;
            return e;
        }

        function pintar(d) {
            const cont = document.getElementById('actual');
            cont.replaceChildren();
            if (!d.actual) {
                cont.append(el('div', 'vacio', 'Esperando turnos…'));
            } else {
                const destino = el('div', 'destino');
                destino.append(document.createTextNode(d.actual.area + ' · '), el('span', '', d.actual.ventanilla));
                cont.append(el('div', 'etq', 'Turno'), el('div', 'folio', d.actual.folio), destino);
            }

            const lista = document.getElementById('ultimos');
            lista.replaceChildren();
            d.ultimos.slice(0, 4).forEach(t => {
                const fila = el('div', 'fila');
                fila.append(el('b', '', t.folio), el('span', '', t.ventanilla), el('small', '', t.area));
                lista.append(fila);
            });
        }

        async function cargar() {
            try {
                const r = await fetch(URL_DATOS, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                if (!r.ok) return;
                const d = await r.json();
                pintar(d);

                const token = d.actual ? d.actual.token : null;
                if (!primera && sonido && token && token !== tokenPrevio) {
                    const a = document.getElementById('actual');
                    a.classList.add('nuevo');
                    setTimeout(() => a.classList.remove('nuevo'), 4200);
                    pitido(); setTimeout(() => hablar(d.actual), 700);
                }
                tokenPrevio = token; primera = false;
            } catch (e) { /* sin red: reintenta en el siguiente ciclo */ }
        }

        function banner(id, c) {
            const b = document.getElementById(id);
            b.replaceChildren();
            b.classList.toggle('contener', c.ajuste === 'contener');
            b.style.backgroundImage = '';

            if (c.imagen || c.url) {
                // Solo se muestra si el archivo existe; si falta, queda el degradado
                const url = c.url || (IMG + c.imagen);
                const probe = new Image();
                probe.onload = () => { b.style.backgroundImage = `url("${url}")`; };
                probe.src = url;
            }

            // Texto encima: solo si se escribió un título (las imágenes oficiales suelen traer el suyo)
            if (c.titulo) {
                const t = el('div', 'texto'); const a = c.ancho || 60;
                t.style.paddingRight = (100 - a) + '%';
                // El oscurecido termina donde empieza la foto, para no apagarla
                t.style.background = `linear-gradient(90deg, rgba(var(--guinda-osc-rgb), .94) 0%, rgba(var(--guinda-osc-rgb), .8) ${a - 8}%, transparent ${a + 4}%)`;
                t.append(el('b', '', c.titulo));
                if (c.subtitulo) t.append(el('span', '', c.subtitulo));
                b.append(t);
            }
        }

        // Trae los banners activos de /admin/banners
        async function cargarBanners() {
            try {
                const r = await fetch(URL_BANNERS, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                if (!r.ok) return;
                const lista = await r.json();
                if (Array.isArray(lista)) CONTENIDOS = lista;
            } catch (e) { /* sin red: se conserva la lista actual */ }
        }

        // Si cambia la identidad institucional (colores, logos, nombre), la pantalla se recarga sola
        async function revisarIdentidad() {
            try {
                const r = await fetch(URL_INST, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                if (!r.ok) return;
                const d = await r.json();
                if (d.version && d.version !== VERSION_INST) location.reload();
            } catch (e) { /* sin red: se revisa en el siguiente ciclo */ }
        }

        function rotar() {
            if (!CONTENIDOS.length) {
                ['banner1', 'banner2'].forEach(id => { const b = document.getElementById(id); b.replaceChildren(); b.style.backgroundImage = ''; });
                return;
            }
            banner('banner1', CONTENIDOS[indice % CONTENIDOS.length]);
            banner('banner2', CONTENIDOS[(indice + 1) % CONTENIDOS.length]);
            indice += 2;
        }

        function reloj() {
            const n = new Date();
            document.getElementById('hora').textContent = n.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
            document.getElementById('fecha').textContent = n.toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        reloj(); setInterval(reloj, 1000);
        cargarBanners().finally(rotar); setInterval(rotar, 8000); setInterval(cargarBanners, 60000);
        setInterval(revisarIdentidad, 60000);
        cargar(); setInterval(cargar, 3000);
    </script>
</body>
</html>
