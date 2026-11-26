{{-- გიდი „მელა“: მისალმება, შვილის დამატება, ბმულის გაზიარება, გვერდის ტური, დახმარების ღილაკი.
     მშობელი: dashboard + market.index; ბავშვი: მთავარი (dashboard). მდგომარეობა → users.guide_state (GuideController).
     ელემენტებს ტური პოულობს data-guide="..." ატრიბუტით (ჩანს მხოლოდ ხილული). --}}
@php
    $__gu = auth()->user();
    $__gRole = $__gu->role === 'child' ? 'child' : 'parent';
    $__gState = (array) ($__gu->guide_state ?? []);
    $__gHasCol = \Illuminate\Support\Facades\Schema::hasColumn('users', 'guide_state');
    $__gPage = request()->routeIs('dashboard') ? 'home' : (request()->routeIs('market.index') ? 'market' : 'other');
    $__gKids = collect();
    $__gNewest = null;
    if ($__gRole === 'parent') {
        $__gKids = $__gu->children()->withTimestamps()->orderByPivot('created_at', 'asc')->get();
        $__gNewest = $__gKids->last();
    }
    $__gRecent = $__gu->created_at && $__gu->created_at->gt(now()->subDays(14));
    $__gAuto = $__gHasCol && empty($__gState['welcome']) && (
        $__gRole === 'parent'
            ? ($__gu->role === 'parent' && ($__gKids->isEmpty() || $__gRecent))
            : ($__gRecent || ! $__gu->tests()->whereNotNull('completed_at')->exists())
    );
    $__gMarketKid = request()->route('child');
    $__gMarketKid = $__gMarketKid instanceof \Illuminate\Database\Eloquent\Model ? $__gMarketKid : ($__gKids->firstWhere('id', (int) request('kid')) ?? $__gKids->first());
    $__gCfg = [
        'role'   => $__gRole,
        'page'   => $__gPage,
        'state'  => (object) $__gState,
        'auto'   => $__gAuto,
        'enabled'=> $__gHasCol,
        'save'   => route('guide.save'),
        'csrf'   => csrf_token(),
        'param'  => request('guide'),
        'name'   => explode(' ', trim((string) $__gu->name))[0],
        'hasKids'=> $__gKids->isNotEmpty(),
        'kid'    => $__gMarketKid ? ['id' => $__gMarketKid->id, 'name' => $__gMarketKid->name] : null,
        'newest' => $__gNewest ? ['id' => $__gNewest->id, 'name' => $__gNewest->name, 'code' => $__gNewest->child_code,
                                  'link' => $__gNewest->child_code ? route('child.magic-login', $__gNewest->child_code) : null] : null,
        'urls'   => [
            'home'   => route('dashboard'),
            'market' => $__gMarketKid && \Illuminate\Support\Facades\Route::has('market.index') ? route('market.index', $__gMarketKid) : null,
            'stats'  => $__gMarketKid ? route('child.stats', $__gMarketKid) : null,
        ],
    ];
    $__gFab = $__gHasCol && ($__gRole === 'parent' ? in_array($__gPage, ['home', 'market'], true) : $__gPage === 'home');
@endphp

<div id="ksGuide" class="ksg" data-role="{{ $__gRole }}">
    {{-- დიმი + მონიშვნა --}}
    <div class="ksg-block" id="ksgBlock" hidden></div>
    <div class="ksg-ring" id="ksgRing" hidden></div>

    {{-- ბუშტი (ტურის ნაბიჯი) --}}
    <section class="ksg-bubble" id="ksgBubble" role="dialog" aria-modal="true" aria-labelledby="ksgBT" hidden>
        <span class="ksg-tail" id="ksgTail"></span>
        <div class="ksg-b-row">
            <span class="ksg-fox">@include('partials.ks-fox', ['size' => 44, 'smile' => true])</span>
            <div class="ksg-b-txt"><b id="ksgBT"></b><span id="ksgBX"></span></div>
        </div>
        <div class="ksg-dots" id="ksgDots"></div>
        <div class="ksg-acts">
            <button type="button" class="ksg-skip" id="ksgSkip">გამოტოვება</button>
            <button type="button" class="ksg-next" id="ksgNext"></button>
        </div>
    </section>

    {{-- ქვემოდან ამომავალი ფანჯარა: მისალმება / ბმულის გაზიარება / დასასრული --}}
    <div class="ksg-ov" id="ksgOv" hidden>
        <section class="ksg-sheet" role="dialog" aria-modal="true" aria-labelledby="ksgST" id="ksgSheet"></section>
    </div>

    @if($__gFab)
    <div class="ksg-fab-wrap" id="ksgFabWrap">
        <section class="ksg-menu" id="ksgMenu" aria-label="გიდის მენიუ" hidden>
            <b>რით დაგეხმარო?</b>
            @if($__gRole === 'parent')
            <button type="button" data-g="tour"><i class="bi bi-play-circle"></i>ტური თავიდან</button>
            <button type="button" data-g="add"><i class="bi bi-person-plus"></i>შვილის დამატება</button>
            @if($__gNewest)<button type="button" data-g="share"><i class="bi bi-link-45deg"></i>ბმულის გაზიარება</button>@endif
            @if($__gCfg['urls']['market'])<button type="button" data-g="market"><i class="bi bi-bag-plus"></i>ჯილდოს დამატება</button>@endif
            @if($__gCfg['urls']['stats'])<a href="{{ $__gCfg['urls']['stats'] }}"><i class="bi bi-bar-chart-line"></i>შედეგების ნახვა</a>@endif
            @else
            <button type="button" data-g="tour"><i class="bi bi-play-circle"></i>მაჩვენე ყველაფერი</button>
            @endif
        </section>
        <button type="button" class="ksg-fab" id="ksgFab" aria-label="გიდი — დახმარება" aria-expanded="false" aria-controls="ksgMenu">
            @include('partials.ks-fox', ['size' => 46, 'smile' => true])
        </button>
    </div>
    @endif
</div>

<style>
    .ksg { --o: #E2590F; --o2: #C24A0A; --t1: #FFF3EA; --t2: #FFE3CC; --ink: #2B2622; --mut: #6B625A; --line: #ECE9E4; font-family: inherit; color: var(--ink); }
    .ksg [hidden] { display: none !important; }
    .ksg-block { position: fixed; inset: 0; z-index: 9990; background: transparent; }
    .ksg-block.dim { background: rgba(29, 26, 23, .62); }
    .ksg-ring { position: fixed; z-index: 9991; border-radius: 20px; box-shadow: 0 0 0 4px var(--o), 0 0 0 9999px rgba(29, 26, 23, .62); pointer-events: none; transition: all .25s ease; }
    .ksg-bubble { position: fixed; z-index: 9993; width: min(380px, calc(100vw - 28px)); background: #fff; border-radius: 22px; padding: 16px; box-shadow: 0 16px 40px rgba(29, 26, 23, .28); display: flex; flex-direction: column; gap: 10px; box-sizing: border-box; transition: top .25s ease, left .25s ease; }
    .ksg-tail { position: absolute; width: 18px; height: 18px; background: #fff; transform: rotate(45deg); border-radius: 3px; }
    .ksg-b-row { display: flex; gap: 12px; align-items: flex-start; }
    .ksg-fox { flex-shrink: 0; width: 52px; height: 52px; border-radius: 16px; background: var(--t1); display: flex; align-items: center; justify-content: center; }
    .ksg-b-txt { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
    .ksg-b-txt b { font-size: 16px; font-weight: 900; line-height: 1.3; }
    .ksg-b-txt span { font-size: 14px; line-height: 1.5; color: #4F473F; }
    .ksg-dots { display: flex; gap: 5px; align-items: center; }
    .ksg-dots i { width: 7px; height: 7px; border-radius: 99px; background: var(--line); }
    .ksg-dots i.on { background: var(--o); } .ksg-dots i.cur { width: 18px; background: var(--o); }
    .ksg-dots small { margin-left: 6px; font-size: 12px; font-weight: 800; color: var(--mut); }
    .ksg-acts { display: flex; gap: 8px; align-items: center; }
    .ksg-skip { height: 44px; padding: 0 12px; border: 0; background: transparent; color: var(--mut); font-family: inherit; font-weight: 800; font-size: 14px; cursor: pointer; }
    .ksg-next { margin-left: auto; height: 44px; padding: 0 18px; border: 0; border-radius: 14px; background: var(--o); color: #fff; font-family: inherit; font-weight: 800; font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
    .ksg-next:hover { background: var(--o2); }
    .ksg-next:focus-visible, .ksg-skip:focus-visible, .ksg-fab:focus-visible { outline: 3px solid #FFC49A; outline-offset: 2px; }
    .ksg-ov { position: fixed; inset: 0; z-index: 9995; background: rgba(29, 26, 23, .62); display: flex; align-items: flex-end; justify-content: center; }
    @media (min-width: 700px) { .ksg-ov { align-items: center; padding: 20px; } }
    .ksg-sheet { width: 100%; max-width: 440px; background: #fff; border-radius: 30px 30px 0 0; padding: 24px 22px 26px; box-sizing: border-box; display: flex; flex-direction: column; gap: 14px; max-height: 92vh; overflow-y: auto; animation: ksgUp .28s ease; }
    @media (min-width: 700px) { .ksg-sheet { border-radius: 30px; } }
    @keyframes ksgUp { from { transform: translateY(40px); opacity: 0; } to { transform: none; opacity: 1; } }
    .ksg-sheet.center { align-items: center; text-align: center; }
    .ksg-hero { width: 112px; height: 112px; border-radius: 34px; background: var(--t1); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(226, 89, 15, .25); }
    .ksg-sheet h2 { margin: 0; font-size: 22px; font-weight: 900; line-height: 1.3; }
    .ksg-sheet p { margin: 0; font-size: 15px; line-height: 1.55; color: var(--mut); }
    .ksg-steps { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; width: 100%; text-align: left; }
    .ksg-steps li { display: flex; align-items: center; gap: 12px; background: #F7F6F3; border-radius: 16px; padding: 10px 12px; font-weight: 800; font-size: 15px; }
    .ksg-steps li span { width: 30px; height: 30px; border-radius: 50%; background: var(--t2); color: var(--o2); font-weight: 900; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ksg-steps li:first-child span { background: var(--o); color: #fff; }
    .ksg-main { width: 100%; height: 54px; border: 0; border-radius: 16px; background: var(--o); color: #fff; font-family: inherit; font-weight: 900; font-size: 17px; cursor: pointer; }
    .ksg-main:hover { background: var(--o2); }
    .ksg-later { height: 40px; border: 0; background: transparent; color: var(--mut); font-family: inherit; font-weight: 800; font-size: 14px; cursor: pointer; }
    .ksg-sh-head { display: flex; gap: 12px; align-items: center; }
    .ksg-sh-head .ksg-fox { width: 56px; height: 56px; border-radius: 18px; }
    .ksg-sh-head small { display: block; font-size: 12px; font-weight: 800; color: var(--o2); }
    .ksg-sh-head b { font-size: 18px; font-weight: 900; line-height: 1.3; }
    .ksg-link { display: flex; align-items: center; gap: 8px; background: #F7F6F3; border: 1.5px dashed #FFC49A; border-radius: 14px; padding: 10px 12px; }
    .ksg-link span { flex: 1; min-width: 0; font-size: 14px; font-weight: 700; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .ksg-link button { height: 36px; padding: 0 12px; border: 0; border-radius: 10px; background: var(--o); color: #fff; font-family: inherit; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
    .ksg-apps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .ksg-apps a, .ksg-apps button { height: 64px; border: 1.5px solid var(--line); border-radius: 16px; background: #fff; font-family: inherit; font-weight: 800; font-size: 13px; color: var(--ink); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; cursor: pointer; text-decoration: none; }
    .ksg-apps a:hover, .ksg-apps button:hover { border-color: #FFC49A; }
    .ksg-apps i { font-size: 20px; }
    .ksg-row { display: flex; gap: 8px; align-items: center; }
    .ksg-row .ksg-main { width: auto; margin-left: auto; height: 48px; padding: 0 20px; font-size: 15px; }
    .ksg-fab-wrap { position: fixed; right: 18px; bottom: 22px; z-index: 1100; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
    @media (max-width: 900px) { .ksg-fab-wrap { bottom: 92px; right: 14px; } }
    .ksg-fab { width: 60px; height: 60px; border-radius: 50%; border: 3px solid #fff; background: var(--t1); box-shadow: 0 8px 20px rgba(226, 89, 15, .35); display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 0; transition: transform .15s; }
    .ksg-fab:hover { transform: scale(1.06); }
    .ksg-menu { width: 260px; background: #fff; border-radius: 20px; padding: 12px; box-shadow: 0 16px 40px rgba(29, 26, 23, .25); display: flex; flex-direction: column; gap: 2px; }
    .ksg-menu b { font-size: 14px; padding: 4px 8px 6px; }
    .ksg-menu button, .ksg-menu a { display: flex; align-items: center; gap: 10px; padding: 9px 8px; border-radius: 12px; border: 0; background: #fff; font-family: inherit; text-align: left; text-decoration: none; color: var(--ink); font-size: 14px; font-weight: 700; cursor: pointer; }
    .ksg-menu button:hover, .ksg-menu a:hover { background: var(--t1); color: var(--ink); }
    .ksg-menu i { color: var(--o); font-size: 17px; }
    .ksg[data-role="child"] .ksg-b-txt b { font-size: 18px; }
    .ksg[data-role="child"] .ksg-b-txt span { font-size: 15px; }
    @media print { .ksg { display: none; } }
</style>

<script>
(function () {
    var G = @json($__gCfg);
    if (!G.enabled) return;
    var FOX = document.querySelector('#ksgBubble .ksg-fox').innerHTML;
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); };
    var KID = (G.kid && G.kid.name) || 'შვილი';

    function save(patch) {
        Object.keys(patch).forEach(function (k) { if (patch[k] === null) delete G.state[k]; else G.state[k] = patch[k]; });
        try {
            fetch(G.save, { method: 'POST', keepalive: true, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': G.csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ state: patch }) });
        } catch (e) {}
    }

    // სხვა გვერდზე გადასვლა: შემდეგი ნაბიჯი მისამართში გადაეცემა (შენახვას არ ველოდებით)
    function go(url, step) { location.href = url + (url.indexOf('?') < 0 ? '?' : '&') + 'guide=' + step; }

    // პირველი ხილული ელემენტი data-guide-ით
    function find(key) {
        var els = document.querySelectorAll('[data-guide="' + key + '"]');
        for (var i = 0; i < els.length; i++) {
            var r = els[i].getBoundingClientRect(), cs = getComputedStyle(els[i]);
            if (r.width > 0 && r.height > 0 && cs.visibility !== 'hidden' && cs.display !== 'none') return els[i];
        }
        return null;
    }

    // ── ტური: რგოლი + ბუშტი ──
    var T = null;
    function place() {
        if (!T) return;
        var s = T.steps[T.i], el = s.el, ring = $('ksgRing'), b = $('ksgBubble'), tail = $('ksgTail');
        var r = el.getBoundingClientRect(), pad = 6, vw = innerWidth, vh = innerHeight;
        ring.style.left = (r.left - pad) + 'px'; ring.style.top = (r.top - pad) + 'px';
        ring.style.width = (r.width + pad * 2) + 'px'; ring.style.height = (r.height + pad * 2) + 'px';
        ring.style.borderRadius = Math.min(26, parseFloat(getComputedStyle(el).borderTopLeftRadius) + pad || 18) + 'px';
        var bw = b.offsetWidth, bh = b.offsetHeight, gap = 18;
        var below = r.bottom + gap + bh < vh - 8, above = r.top - gap - bh > 8;
        var top = below || !above ? Math.min(r.bottom + gap, vh - bh - 8) : r.top - gap - bh;
        var left = Math.max(14, Math.min(vw - bw - 14, r.left + r.width / 2 - bw / 2));
        b.style.top = Math.max(8, top) + 'px'; b.style.left = left + 'px';
        var tx = Math.max(18, Math.min(bw - 36, r.left + r.width / 2 - left - 9));
        tail.style.left = tx + 'px';
        if (below || !above) { tail.style.top = '-9px'; tail.style.bottom = ''; } else { tail.style.bottom = '-9px'; tail.style.top = ''; }
        tail.hidden = !(below || above);
    }
    function show() {
        var s = T.steps[T.i], n = T.steps.length;
        s.el.scrollIntoView({ block: 'center', behavior: 'smooth' });
        $('ksgBT').textContent = s.title; $('ksgBX').textContent = s.text;
        var d = '';
        if (n > 1) { for (var k = 0; k < n; k++) d += '<i class="' + (k === T.i ? 'cur' : (k < T.i ? 'on' : '')) + '"></i>'; d += '<small>' + (T.i + 1) + ' / ' + n + '</small>'; }
        $('ksgDots').innerHTML = d;
        var last = T.i === n - 1;
        $('ksgNext').innerHTML = esc(s.primary || (last ? (T.opts.lastLabel || 'დასრულება') : 'შემდეგი')) + ' <i class="bi bi-arrow-right"></i>';
        $('ksgSkip').hidden = T.opts.noSkip;
        $('ksgBlock').hidden = false; $('ksgRing').hidden = false; $('ksgBubble').hidden = false;
        place(); setTimeout(place, 350); setTimeout(place, 700);
        $('ksgNext').focus({ preventScroll: true });
    }
    function run(steps, opts) {
        steps = steps.map(function (s) { s.el = find(s.key); return s; }).filter(function (s) { return s.el; });
        if (!steps.length) { if (opts.onDone) opts.onDone(); return; }
        closeMenu();
        T = { steps: steps, i: 0, opts: opts || {} };
        show();
    }
    function endTour() {
        T = null;
        $('ksgBlock').hidden = true; $('ksgRing').hidden = true; $('ksgBubble').hidden = true;
    }
    $('ksgNext').onclick = function () {
        if (!T) return;
        var s = T.steps[T.i];
        if (s.action) { var o = T.opts; endTour(); s.action(s.el); if (o.afterAction) o.afterAction(); return; }
        if (T.i < T.steps.length - 1) { T.i++; show(); return; }
        var done = T.opts.onDone; endTour(); if (done) done();
    };
    $('ksgSkip').onclick = function () { var sk = T && T.opts.onSkip; endTour(); if (sk) sk(); };
    addEventListener('resize', place); addEventListener('scroll', place, true);
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (T) $('ksgSkip').click(); else if (!$('ksgOv').hidden) closeSheet();
    });

    // ── ფანჯარა ──
    var sheetClose = null;
    function sheet(html, center, onClose) {
        closeMenu();
        var sh = $('ksgSheet');
        sh.className = 'ksg-sheet' + (center ? ' center' : '');
        sh.innerHTML = html;
        $('ksgOv').hidden = false;
        sheetClose = onClose || null;
        var f = sh.querySelector('.ksg-main'); if (f) f.focus();
    }
    function closeSheet() { $('ksgOv').hidden = true; var c = sheetClose; sheetClose = null; if (c) c(); }
    $('ksgOv').addEventListener('click', function (e) { if (e.target === this) closeSheet(); });

    // ═══════════════════ მშობელი ═══════════════════
    function welcome() {
        sheet('<div class="ksg-hero">' + FOX.replace(/width="44" height="44"/, 'width="96" height="96"') + '</div>'
            + '<h2 id="ksgST">გამარჯობა, ' + esc(G.name) + '!<br>მე ვარ მელა — შენი გიდი</h2>'
            + '<p>3 მარტივ ნაბიჯში დავამატებთ შვილს, გავუგზავნით ბმულს და გაჩვენებ, სად რა არის.</p>'
            + '<ol class="ksg-steps"><li><span>1</span>შვილის დამატება' + (G.hasKids ? ' ✓' : '') + '</li><li><span>2</span>ბმულის გაგზავნა შვილისთვის</li><li><span>3</span>გვერდის დათვალიერება</li></ol>'
            + '<button type="button" class="ksg-main" data-a="go">დავიწყოთ</button>'
            + '<button type="button" class="ksg-later" data-a="later">მოგვიანებით</button>', true,
            function () { if (!G.state.welcome) save({ welcome: 'later' }); });
        $('ksgSheet').onclick = function (e) {
            var a = e.target.closest('[data-a]'); if (!a) return;
            if (a.dataset.a === 'later') { save({ welcome: 'later' }); closeSheet(); hint('კარგი! როცა მოგინდება, დააჭირე მელას კუთხეში.'); return; }
            save({ welcome: 'done' }); sheetClose = null; closeSheet();
            stepAdd();
        };
    }

    function stepAdd() {
        if (G.page !== 'home') { go(G.urls.home, 'add'); return; }
        save({ step: 'add' });
        if (G.hasKids) {
            // შვილი უკვე ჰყავს — ვაჩვენებთ ღილაკს და ვაგრძელებთ
            run([{ key: 'add-child', title: 'ნაბიჯი 1 — შვილი დამატებულია ✓', text: (G.newest ? G.newest.name + ' უკვე დამატებული გყავს. ' : '') + 'კიდევ ერთი შვილის დამატება ნებისმიერ დროს შეგიძლია ამ ღილაკით.', primary: 'შემდეგი', action: function () { stepShare(); } }],
                { onSkip: function () { save({ step: null }); } });
            return;
        }
        run([{ key: 'add-child', title: 'ნაბიჯი 1 — შვილის დამატება', text: 'დააჭირე და ჩაწერე შვილის სახელი, კლასი და სქესი. სულ 1 წუთი! შენახვის შემდეგ აქვე გავაგრძელებთ.', primary: 'დამატება', action: function (el) { el.click(); } }],
            { onSkip: function () { save({ step: null }); } });
    }

    function stepShare() {
        var k = G.newest;
        if (!k || !k.link) { stepTour(); return; }
        if (G.page !== 'home') { go(G.urls.home, 'share'); return; }
        save({ step: 'share' });
        var msg = k.name + ', შედი KidSmart-ში ამ ბმულით: ' + k.link;
        sheet('<div class="ksg-sh-head"><span class="ksg-fox">' + FOX + '</span><div><small>ნაბიჯი 2 — ბმულის გაგზავნა</small><b id="ksgST">' + esc(k.name) + ' დაემატა! ახლა ბმული გაუგზავნე</b></div></div>'
            + '<p>' + esc(k.name) + ' ამ ბმულით შევა თავის გვერდზე — პაროლი არ სჭირდება. გახსენი მის ტელეფონზე ან პლანშეტზე.' + (k.code ? ' ან შესვლისას ჩაწეროს კოდი: <b>' + esc(k.code) + '</b>' : '') + '</p>'
            + '<div class="ksg-link"><span>' + esc(k.link) + '</span><button type="button" data-a="copy"><i class="bi bi-clipboard"></i> კოპირება</button></div>'
            + '<div class="ksg-apps">'
            + '<a href="https://wa.me/?text=' + encodeURIComponent(msg) + '" target="_blank" rel="noopener" data-a="sent"><i class="bi bi-whatsapp" style="color:#1E8A4C"></i>WhatsApp</a>'
            + '<a href="viber://forward?text=' + encodeURIComponent(msg) + '" data-a="sent"><i class="bi bi-chat-dots" style="color:#5B4FD0"></i>Viber</a>'
            + '<button type="button" data-a="qr"><i class="bi bi-qr-code"></i>QR კოდი</button></div>'
            + '<div class="ksg-row"><button type="button" class="ksg-later" data-a="later">მერე გავუგზავნი</button><button type="button" class="ksg-main" data-a="done">გავუგზავნე <i class="bi bi-arrow-right"></i></button></div>', false,
            function () { if (G.state.step === 'share') save({ step: 'tour' }); });
        $('ksgSheet').onclick = function (e) {
            var a = e.target.closest('[data-a]'); if (!a) return;
            var act = a.dataset.a;
            if (act === 'copy') {
                var done = function () { a.innerHTML = '<i class="bi bi-check-lg"></i> დაკოპირდა'; };
                if (navigator.clipboard) navigator.clipboard.writeText(k.link).then(done, done); else done();
                save({ shared: true }); return;
            }
            if (act === 'sent') { save({ shared: true }); return; }
            if (act === 'qr') {
                save({ shared: true }); sheetClose = null; closeSheet(); save({ step: 'tour' });
                if (window.toggleShare && document.getElementById('shareModal' + k.id)) { toggleShare(k.id); if (window.ksShareTab) ksShareTab(k.id, 'qr'); }
                return;
            }
            if (act === 'done') save({ shared: true });
            sheetClose = null; closeSheet();
            stepTour();
        };
    }

    function stepTour() {
        if (G.page !== 'home') { go(G.urls.home, 'tour'); return; }
        save({ step: 'tour' });
        var steps = [
            { key: 'kid', title: 'ეს ' + KID + 'ს ზოლია', text: 'აქ ჩანს, დაწერა თუ არა დღეს ტესტი. ზემოთ შვილებს შორის გადართვაა.' },
            { key: 'share', title: 'შესვლის ბმული', text: 'ბმული, კოდი და QR ყოველთვის აქაა — თუ ' + KID + 'ს ხელახლა დასჭირდება.' },
            { key: 'settings', title: 'პარამეტრები', text: 'კლასი, ავატარი, სქესი და შეტყობინებები — ყველაფერი აქ იცვლება.' },
            { key: 'kpis', title: 'მთავარი ციფრები', text: 'რამდენი ტესტი დაწერა, საშუალო შედეგი, დონე, ზედიზედ დღეები და მონეტები.' },
            { key: 'week', title: 'კვირის შედეგები', text: 'ყოველი დღის ტესტის შედეგი. ნარინჯისფერი — 85% და მეტი.' },
            { key: 'topics', title: 'თემების მიხედვით', text: 'რომელი თემა გამოსდის კარგად და რომელი უჭირს — აქვე რჩევაც გამოჩნდება.' },
            { key: 'tests', title: 'ბოლო ტესტები', text: 'დააჭირე ნებისმიერ ტესტს და ნახავ ყველა კითხვას და პასუხს.' },
            { key: 'today', title: 'დღეს', text: 'დღის ტესტი და ვარჯიში. თუ დაავიწყდა — „შეხსენება“ და შეტყობინება მიუვა.' },
            { key: 'add-child', title: 'კიდევ ერთი შვილი', text: 'აქედან დაამატებ სხვა შვილსაც — თითოეულს თავისი გვერდი ექნება.' },
        ];
        if (G.urls.market) steps.push({ key: 'nav-market', title: 'ოჯახის მარკეტი', text: 'აქ ამატებ ჯილდოებს, რომლებსაც ' + KID + ' მონეტებით „იყიდის“ — ნაყინი, კინო, თამაშის დრო. შენ ადასტურებ.', primary: 'მარკეტში გადასვლა',
            action: function () { save({ step: 'market' }); go(G.urls.market, 'market'); } });
        run(steps, { lastLabel: 'დასრულება', onSkip: function () { save({ step: null, tour: 'done' }); }, onDone: finish });
    }

    function stepMarket() {
        if (G.page !== 'market') { if (G.urls.market) go(G.urls.market, 'market'); return; }
        save({ step: 'market' });
        run([
            { key: 'market-requests', title: 'მოთხოვნები', text: 'როცა ' + KID + ' ჯილდოს „იყიდის“, აქ გამოჩნდება — შენ დაადასტურებ ან გააუქმებ. მონეტები მხოლოდ დადასტურებისას ჩამოეჭრება.' },
            { key: 'market-add', title: 'ჯილდოს დამატება', text: 'აქედან ამატებ ჯილდოებს. მოდი, ნაბიჯ-ნაბიჯ გაჩვენებ!', primary: 'მაჩვენე როგორ',
              action: function () { rewardGuide(finish); } },
        ], { lastLabel: 'მაჩვენე როგორ', onSkip: function () { save({ step: null, tour: 'done' }); }, onDone: finish });
    }

    // ── ჯილდოს დამატების ინსტრუქცია (დამატების ფანჯრის შიგნით) ──
    function rewardGuide(after) {
        if (G.page !== 'market') { if (G.urls.market) go(G.urls.market, 'reward'); return; }
        if (typeof window.openAddItemModal !== 'function') return;
        window.openAddItemModal();
        var t = document.querySelector('[data-guide="reward-title"]');
        setTimeout(function () {
            run([
                { key: 'reward-tabs', title: 'ორი გზა', text: '„ხელით“ — შენ თვითონ წერ ჯილდოს. „შაბლონები“ — ირჩევ მზა ჯილდოს სიიდან.' },
                { key: 'reward-icon', title: '1. აირჩიე სურათი', text: 'დააჭირე და აირჩიე სმაილი, რომელიც ჯილდოს მოუხდება — მაგ. 🍦 ან 🎬. ' + KID + ' მარკეტში ამ სურათს დაინახავს.' },
                { key: 'reward-title', title: '2. დაწერე სახელი', text: 'მოკლედ და გასაგებად: „ნაყინი“, „კინოში წასვლა“, „30 წუთი თამაში“.' },
                { key: 'reward-cost', title: '3. მიუთითე ფასი', text: 'რამდენი მონეტა ღირს. პატარა ჯილდოს — ცოტა, დიდს — მეტი, რომ ' + KID + 'ს რამდენიმე დღე მოუწიოს დაგროვება.' },
                { key: 'reward-save', title: '4. დაამატე', text: 'დააჭირე — ჯილდო მაშინვე გამოჩნდება ' + KID + 'ს მარკეტში. შეცვლა ან გათიშვა ნებისმიერ დროს შეგიძლია.' },
                { key: 'reward-tpl', title: 'ან — მზა ჯილდოები', text: 'აქ ბევრი მზა ჯილდოა. დააჭირე რომელიმეს და მხოლოდ ფასს მიუთითებ.' },
            ], {
                lastLabel: 'გასაგებია, ვცდი!',
                onSkip: function () { if (after) after(); },
                onDone: function () { if (window.switchItemTab) switchItemTab('manual'); if (t) t.focus(); if (after) setTimeout(after, 50); },
            });
        }, 250);
    }

    function finish() {
        save({ step: null, tour: 'done', welcome: 'done' });
        sheet('<div class="ksg-hero">' + FOX.replace(/width="44" height="44"/, 'width="96" height="96"') + '</div>'
            + '<h2 id="ksgST">' + (G.role === 'child' ? 'მზად ხარ! წარმატებები!' : 'სულ ეს იყო!') + '</h2>'
            + '<p>' + (G.role === 'child' ? 'თუ რამე დაგავიწყდა, დააჭირე მელას კუთხეში.' : 'თუ რამე დაგავიწყდა, დააჭირე მელას კუთხეში — თავიდან გაჩვენებ.') + '</p>'
            + '<button type="button" class="ksg-main" data-a="ok">კარგი</button>', true);
        $('ksgSheet').onclick = function (e) { if (e.target.closest('[data-a]')) closeSheet(); };
    }

    // პატარა მინიშნება FAB-ის გვერდით
    function hint(text) {
        var b = $('ksgFab'); if (!b) return;
        run([{ key: '__fab', title: 'მე აქ ვარ!', text: text }], { lastLabel: 'კარგი', noSkip: true });
    }

    // ═══════════════════ ბავშვი ═══════════════════
    function kidWelcome() {
        sheet('<div class="ksg-hero" style="width:130px;height:130px;border-radius:40px;">' + FOX.replace(/width="44" height="44"/, 'width="112" height="112"') + '</div>'
            + '<h2 id="ksgST" style="font-size:26px;">სალამი, ' + esc(G.name) + '!</h2>'
            + '<p style="font-size:16px;color:#4F473F;">მე ვარ მელა. აქ ისწავლი მათემატიკას, ითამაშებ და მონეტებს დააგროვებ. მოდი, გაჩვენებ!</p>'
            + '<button type="button" class="ksg-main" data-a="go" style="height:56px;font-size:18px;">წავიდეთ!</button>'
            + '<button type="button" class="ksg-later" data-a="later">მე თვითონ ვიცი</button>', true,
            function () { if (!G.state.welcome) save({ welcome: 'done', tour: 'done' }); });
        $('ksgSheet').onclick = function (e) {
            var a = e.target.closest('[data-a]'); if (!a) return;
            save({ welcome: 'done' }); sheetClose = null; closeSheet();
            if (a.dataset.a === 'go') kidTour(); else save({ tour: 'done' });
        };
    }
    function kidTour() {
        run([
            { key: 'kid-mission', title: 'ყოველდღე — ერთი ტესტი', text: 'აქ წერ დღის ტესტს. ყოველ სწორ პასუხზე მონეტას მიიღებ!' },
            { key: 'kid-coins', title: 'ეს შენი მონეტებია', text: 'დააგროვე და მარკეტში გაცვალე ჯილდოში. ჯილდოებს მშობელი ამატებს.' },
            { key: 'kid-practice', title: 'ვარჯიში', text: 'აქ ვარჯიშობ ნებისმიერ თემაზე — რამდენიც გინდა.' },
            { key: 'kid-games', title: 'თამაშები', text: 'დაისვენე და ითამაშე — ზოგი თამაში მათემატიკასაც გავარჯიშებს!' },
            { key: 'kid-market', title: 'მარკეტი', text: 'აქ ჩანს ჯილდოები, რომლებსაც მონეტებით იყიდი.' },
            { key: 'nav-achievements', title: 'მედლები', text: 'რაც უფრო მეტს ისწავლი, მით მეტ მედალს მიიღებ.' },
        ], { lastLabel: 'მზად ვარ!', onSkip: function () { save({ tour: 'done' }); }, onDone: finish });
    }

    // ═══════════════════ FAB ═══════════════════
    var fab = $('ksgFab'), menu = $('ksgMenu');
    if (fab) fab.setAttribute('data-guide', '__fab');
    function closeMenu() { if (menu && !menu.hidden) { menu.hidden = true; fab.setAttribute('aria-expanded', 'false'); } }
    if (fab) {
        fab.onclick = function () { menu.hidden = !menu.hidden; fab.setAttribute('aria-expanded', menu.hidden ? 'false' : 'true'); };
        menu.onclick = function (e) {
            var b = e.target.closest('[data-g]'); if (!b) return;
            closeMenu();
            var g = b.dataset.g;
            if (G.role === 'child') { kidTour(); return; }
            if (g === 'tour') stepTour();
            else if (g === 'add') stepAdd();
            else if (g === 'share') stepShare();
            else if (g === 'market') rewardGuide();
        };
        document.addEventListener('click', function (e) { if (menu && !menu.hidden && !e.target.closest('#ksgFabWrap')) closeMenu(); });
    }

    // ═══════════════════ გაშვება ═══════════════════
    function boot() {
        var p = G.param, st = G.state.step;
        if (p && history.replaceState) {
            try { var u = new URL(location.href); u.searchParams.delete('guide'); history.replaceState(null, '', u.toString()); } catch (e) {}
        }
        if (G.role === 'child') {
            if (G.page !== 'home') return;
            if (p === 'tour') return kidTour();
            if (G.auto) return kidWelcome();
            return;
        }
        if (p === 'welcome') return welcome();
        if (p === 'add') return stepAdd();
        if (p === 'share') return stepShare();
        if (p === 'tour') return stepTour();
        if (p === 'market') return stepMarket();
        if (p === 'reward') return rewardGuide();
        if (G.page === 'home') {
            // შვილი ახლახან დაემატა → ბმულის გაზიარება
            if (st === 'add' && G.hasKids) return stepShare();
            if (st === 'share') return stepShare();
            if (st === 'tour') return stepTour();
            if (G.auto) return welcome();
        }
        if (G.page === 'market' && st === 'market') return stepMarket();
    }
    if (document.readyState === 'complete') setTimeout(boot, 300); else addEventListener('load', function () { setTimeout(boot, 300); });
})();
</script>
