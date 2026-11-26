{{--
    iPhone / iPad: საიტის მთავარ ეკრანზე დამატება (Apple შეტყობინებებს მხოლოდ ასე უშვებს, iOS 16.4+).
    ავტომატური დამატება Apple-ზე შეუძლებელია, ამიტომ მელა ნაბიჯ-ნაბიჯ აჩვენებს:
      offer   — შეთავაზება სწორ მომენტში (მშობელი, შვილის პირველი დასრულებული ტესტის შემდეგ)
      steps   — 1) გაზიარების ღილაკი (ცოცხალი ისარი) 2) „Add to Home Screen“
      ready   — მზადაა, გახსენი მთავარი ეკრანიდან
      enable  — მთავარი ეკრანიდან გახსნისას: „ჩართე შეტყობინებები“ (iOS ნებართვას მხოლოდ შეხებით ითხოვს)
      old     — iOS 16.4-ზე ძველი: შეტყობინებები ვერ იმუშავებს, განაახლე
    $iosOffer (bool) — შეიძლება თუ არა თავისით შეთავაზება ამ გვერდზე.
--}}
<div class="ksi-ov" id="ksiOv" hidden>
    {{-- ისარი გაზიარების ღილაკისკენ: iPhone Safari — ქვემოთ შუაში; iPad / Chrome — ზემოთ მარჯვნივ --}}
    <svg class="ksi-arrow" id="ksiArrow" width="48" height="60" viewBox="0 0 44 56" fill="none" stroke="#E2590F" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M22 4v44M8 34l14 14 14-14"/></svg>

    {{-- ნაბიჯები 1–2: ზედა ბარათი (გაზიარების მენიუ ქვემოდან ამოდის და მას არ ფარავს) --}}
    <section class="ksi-card" id="ksiSteps" role="dialog" aria-modal="true" aria-labelledby="ksiStT" hidden>
        <div class="ksi-dots" aria-hidden="true"><span class="on wide"></span><span class="on"></span><span></span></div>
        <div class="ksi-row">
            <span class="ksi-fox">@include('partials.ks-fox', ['size' => 48, 'smile' => true])</span>
            <div>
                <span class="ksi-kick">2 ნაბიჯი</span>
                <h2 id="ksiStT">დაამატე KidSmart მთავარ ეკრანზე</h2>
            </div>
        </div>
        <ol class="ksi-list">
            <li>
                <span class="ksi-n">1</span>
                <span class="ksi-t">დააჭირე გაზიარების ღილაკს <b id="ksiWhere">ქვემოთ</b></span>
                <span class="ksi-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2F7BF5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M8 7l4-4 4 4M5 11v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8"/></svg></span>
            </li>
            <li>
                <span class="ksi-n">2</span>
                <span class="ksi-t">ჩამოსქროლე და აირჩიე <b>„Add to Home Screen“</b><small>ქართულად: „მთავარ ეკრანზე დამატება“, მერე — „Add“</small></span>
                <span class="ksi-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#E2590F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M12 8v8M8 12h8"/></svg></span>
            </li>
        </ol>
        <div class="ksi-acts">
            <button type="button" class="ksi-ghost" data-ksi-close>დახურვა</button>
            <button type="button" class="ksi-main" data-ksi-go="ready">დავამატე</button>
        </div>
    </section>

    {{-- ქვედა ფურცელი: offer / ready / enable / old --}}
    <section class="ksi-sheet" id="ksiSheet" role="dialog" aria-modal="true" aria-labelledby="ksiSheetT" hidden>
        <div class="ksi-badge">
            @include('partials.ks-fox', ['size' => 92, 'smile' => true])
            <span class="ksi-bell" id="ksiBell"><i class="bi bi-bell-fill"></i></span>
        </div>

        <div data-ksi-view="offer" hidden>
            <h2 id="ksiSheetT">გინდა გაიგო, როცა შვილი<br>ტესტს დაასრულებს?</h2>
            <p>დაამატე KidSmart ტელეფონის მთავარ ეკრანზე — iPhone შეტყობინებებს მხოლოდ ასე აგზავნის. 30 წამი სჭირდება.</p>
            <ul class="ksi-why">
                <li><i class="bi bi-check-lg"></i>ტესტის შედეგი მაშინვე</li>
                <li><i class="bi bi-check-lg"></i>როცა შვილი ჯილდოს მოითხოვს</li>
                <li><i class="bi bi-check-lg"></i>აპლიკაციასავით — ერთი შეხებით</li>
            </ul>
            <button type="button" class="ksi-main" data-ksi-go="steps">მაჩვენე როგორ</button>
            <button type="button" class="ksi-ghost" data-ksi-later>მოგვიანებით</button>
        </div>

        <div data-ksi-view="ready" hidden>
            <div class="ksi-dots" aria-hidden="true"><span class="on"></span><span class="on"></span><span class="on wide"></span></div>
            <h2>მზადაა! გახსენი KidSmart<br>მთავარი ეკრანიდან</h2>
            <p>იპოვე მელას ხატულა ტელეფონის ეკრანზე და გახსენი. იქ მელა შეგახსენებს შეტყობინებების ჩართვას.</p>
            <div class="ksi-push">
                <span class="ksi-push-ic">@include('partials.ks-fox', ['size' => 30, 'smile' => true])</span>
                <span><b>KidSmart</b> <em>· ახლა</em><br>შვილმა დღის ტესტი დაასრულა — 9 / 10</span>
            </div>
            <small class="ksi-cap">ასე მოგივა შეტყობინება</small>
            <button type="button" class="ksi-main" data-ksi-close>გასაგებია</button>
        </div>

        <div data-ksi-view="enable" hidden>
            <h2>ჩართე შეტყობინებები</h2>
            <p>ახლა KidSmart აპლიკაციასავით გაქვს. დააჭირე ღილაკს და iPhone-ის ფანჯარაში აირჩიე „Allow“.</p>
            <button type="button" class="ksi-main" data-ksi-enable><i class="bi bi-bell"></i> შეტყობინებების ჩართვა</button>
            <button type="button" class="ksi-ghost" data-ksi-later>მოგვიანებით</button>
        </div>

        <div data-ksi-view="old" hidden>
            <h2>შეტყობინებებისთვის განაახლე iOS</h2>
            <p>შენს iPhone-ზე საიტის შეტყობინებები ჯერ არ მუშაობს — საჭიროა iOS 16.4 ან უფრო ახალი. განახლება: Settings → General → Software Update.</p>
            <button type="button" class="ksi-main" data-ksi-close>გასაგებია</button>
        </div>
    </section>
</div>

<style>
.ksi-ov { position: fixed; inset: 0; z-index: 100000; background: rgba(29, 26, 23, .58); font-family: 'Noto Sans Georgian', sans-serif; color: #2B2622; }
.ksi-ov[hidden], .ksi-ov [hidden] { display: none !important; }
.ksi-ov.clear { background: rgba(29, 26, 23, .45); }
.ksi-sheet { position: absolute; left: 0; right: 0; bottom: 0; max-width: 520px; margin: 0 auto; background: #fff; border-radius: 30px 30px 0 0; padding: 24px 22px calc(26px + env(safe-area-inset-bottom)); display: flex; flex-direction: column; align-items: center; text-align: center; animation: ksiUp .3s cubic-bezier(.2, .9, .3, 1.1); }
.ksi-sheet > div[data-ksi-view] { display: flex; flex-direction: column; align-items: center; gap: 12px; width: 100%; }
.ksi-badge { width: 108px; height: 108px; border-radius: 32px; background: #FFF3EA; display: flex; align-items: center; justify-content: center; margin-top: -80px; margin-bottom: 12px; box-shadow: 0 8px 24px rgba(226, 89, 15, .25); position: relative; }
.ksi-bell { position: absolute; right: -6px; top: -6px; width: 38px; height: 38px; border-radius: 50%; background: #E2590F; color: #fff; display: flex; align-items: center; justify-content: center; border: 3px solid #fff; font-size: 16px; }
.ksi-ov h2 { margin: 0; font-size: 22px; font-weight: 900; line-height: 1.3; color: #2B2622; }
.ksi-ov p { margin: 0; font-size: 15px; line-height: 1.55; color: #6B625A; }
.ksi-why { list-style: none; margin: 0; padding: 0; width: 100%; display: flex; flex-direction: column; gap: 8px; text-align: left; }
.ksi-why li { display: flex; align-items: center; gap: 10px; background: #F7F6F3; border-radius: 14px; padding: 10px 12px; font-size: 14px; font-weight: 700; }
.ksi-why i { width: 24px; height: 24px; border-radius: 50%; background: #DFF5E3; color: #14612F; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.ksi-main { width: 100%; min-height: 54px; border: none; border-radius: 16px; background: #E2590F; color: #fff; font-family: inherit; font-weight: 900; font-size: 17px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
.ksi-main:hover { background: #C24A0A; }
.ksi-ghost { min-height: 44px; border: none; background: transparent; color: #6B625A; font-family: inherit; font-weight: 800; font-size: 14px; cursor: pointer; padding: 0 14px; }
.ksi-main:focus-visible, .ksi-ghost:focus-visible { outline: 3px solid rgba(226, 89, 15, .4); outline-offset: 2px; }
.ksi-dots { display: flex; gap: 6px; justify-content: center; }
.ksi-dots span { width: 10px; height: 10px; border-radius: 99px; background: #FFE3CC; }
.ksi-dots span.on { background: #E2590F; }
.ksi-dots span.wide { width: 28px; }
.ksi-push { width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 10px; background: #F7F6F3; border-radius: 18px; padding: 12px; text-align: left; font-size: 13px; color: #4F473F; line-height: 1.45; }
.ksi-push b { color: #2B2622; }
.ksi-push em { font-style: normal; color: #6B625A; }
.ksi-push-ic { width: 40px; height: 40px; border-radius: 10px; background: #FFF3EA; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ksi-cap { font-size: 12px; color: #6B625A; margin-top: -6px; }

.ksi-card { position: absolute; left: 14px; right: 14px; top: calc(14px + env(safe-area-inset-top)); max-width: 480px; margin: 0 auto; background: #fff; border-radius: 26px; padding: 18px; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 16px 40px rgba(0, 0, 0, .28); animation: ksiDown .3s ease; }
.ksi-card.low { top: auto; bottom: calc(170px + env(safe-area-inset-bottom)); }
.ksi-row { display: flex; align-items: center; gap: 12px; }
.ksi-row h2 { font-size: 18px; }
.ksi-fox { width: 58px; height: 58px; border-radius: 18px; background: #FFF3EA; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ksi-kick { font-size: 12px; font-weight: 800; color: #C24A0A; display: block; }
.ksi-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.ksi-list li { display: flex; align-items: center; gap: 10px; background: #F7F6F3; border-radius: 14px; padding: 10px 12px; }
.ksi-n { width: 28px; height: 28px; border-radius: 50%; background: #E2590F; color: #fff; font-weight: 900; font-size: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ksi-t { flex: 1; font-size: 14px; font-weight: 700; line-height: 1.45; }
.ksi-t small { display: block; font-weight: 500; font-size: 12px; color: #6B625A; }
.ksi-ic { width: 38px; height: 38px; border-radius: 11px; background: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ksi-acts { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
.ksi-acts .ksi-main { width: auto; flex: 1; min-height: 48px; font-size: 16px; }

.ksi-arrow { position: absolute; z-index: 2; filter: drop-shadow(0 2px 6px rgba(0, 0, 0, .35)); }
.ksi-arrow.down { left: 50%; margin-left: -24px; bottom: calc(64px + env(safe-area-inset-bottom)); animation: ksiBobD 1.1s ease-in-out infinite; }
.ksi-arrow.up { right: 18px; top: 4px; animation: ksiBobU 1.1s ease-in-out infinite; }
@keyframes ksiBobD { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(12px); } }
@keyframes ksiBobU { 0%, 100% { transform: rotate(180deg) translateY(0); } 50% { transform: rotate(180deg) translateY(12px); } }
@keyframes ksiUp { from { transform: translateY(40px); opacity: 0; } to { transform: none; opacity: 1; } }
@keyframes ksiDown { from { transform: translateY(-20px); opacity: 0; } to { transform: none; opacity: 1; } }
@media (prefers-reduced-motion: reduce) { .ksi-arrow, .ksi-sheet, .ksi-card { animation: none !important; } .ksi-arrow.up { transform: rotate(180deg); } }
</style>

<script>
(function () {
    const ua = navigator.userAgent;
    const isIpad = /ipad/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const isIos = /iphone|ipod/i.test(ua) || isIpad;
    if (!isIos) return;

    const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
    const otherBrowser = /CriOS|FxiOS|EdgiOS|OPiOS/i.test(ua);       // Chrome / Firefox / Edge iPhone-ზე — გაზიარება მისამართის ზოლშია, ზემოთ
    const m = ua.match(/OS (\d+)_(\d+)/);
    const ver = m ? [+m[1], +m[2]] : [99, 0];
    const oldIos = ver[0] < 16 || (ver[0] === 16 && ver[1] < 4);
    const LATER_KEY = 'ks_ios_later', ENABLE_KEY = 'ks_ios_enable_later', WEEK = 7 * 864e5;

    const ov = document.getElementById('ksiOv');
    const sheet = document.getElementById('ksiSheet');
    const steps = document.getElementById('ksiSteps');
    const arrow = document.getElementById('ksiArrow');
    let lastFocus = null;

    function store(k, v) { try { v === null ? localStorage.removeItem(k) : localStorage.setItem(k, v); } catch (e) {} }
    function read(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
    function snoozed(k) { const t = +read(k); return t && Date.now() - t < WEEK; }

    function show(view) {
        lastFocus = lastFocus || document.activeElement;
        ov.hidden = false;
        const isSteps = view === 'steps';
        ov.classList.toggle('clear', isSteps);
        steps.hidden = !isSteps;
        sheet.hidden = isSteps;
        arrow.hidden = !isSteps;
        if (isSteps) {
            const up = isIpad || otherBrowser;
            arrow.classList.toggle('up', up);
            arrow.classList.toggle('down', !up);
            steps.classList.toggle('low', up);    // ისარი ზემოთაა — ბარათი ქვემოთ ჩავიდეს
            document.getElementById('ksiWhere').textContent = up ? 'ზემოთ, მარჯვნივ' : 'ქვემოთ';
            setTimeout(() => steps.querySelector('.ksi-main').focus(), 50);
        } else {
            sheet.querySelectorAll('[data-ksi-view]').forEach(v => { v.hidden = v.dataset.ksiView !== view; });
            document.getElementById('ksiBell').hidden = view === 'ready';
            const f = sheet.querySelector('[data-ksi-view="' + view + '"] .ksi-main');
            if (f) setTimeout(() => f.focus(), 50);
        }
    }
    function close() {
        ov.hidden = true;
        if (lastFocus && lastFocus.focus) lastFocus.focus();
        lastFocus = null;
    }

    ov.addEventListener('click', function (e) {
        const go = e.target.closest('[data-ksi-go]');
        if (go) { show(go.dataset.ksiGo); return; }
        if (e.target.closest('[data-ksi-close]')) { close(); return; }
        if (e.target.closest('[data-ksi-later]')) {
            store(sheet.querySelector('[data-ksi-view="enable"]').hidden ? LATER_KEY : ENABLE_KEY, String(Date.now()));
            close(); return;
        }
        if (e.target.closest('[data-ksi-enable]')) {
            close();
            if (typeof window.enablePushNotifications === 'function') { window._notifToast = true; window.enablePushNotifications(); }
            return;
        }
        if (e.target === ov && !steps.hidden) close();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !ov.hidden) close(); });

    /**
     * ზარის ღილაკიდან (ან სხვაგან): რა აჩვენოს, როცა ბრაუზერი შეტყობინებებს ვერ აგზავნის.
     * ბრუნდება true, თუ რამე აჩვენა.
     */
    window.KsIos = {
        isIos: true,
        standalone: standalone,
        explain: function () {
            if (oldIos) { show('old'); return true; }
            if (!standalone) { show('steps'); return true; }
            show('old'); return true;   // მთავარი ეკრანიდანაა, მაგრამ მხარდაჭერა არ ჩანს — ძველი სისტემა
        },
        open: show,
        close: close,
    };

    const nearLoad = () => !document.querySelector('.modal-overlay.open, .swal2-container')
        && document.getElementById('ksgOv')?.hidden !== false
        && document.getElementById('ksgBubble')?.hidden !== false;

    // მთავარი ეკრანიდან გახსნა: ერთხელ შევახსენოთ შეტყობინებების ჩართვა (ნებართვას iOS მხოლოდ შეხებით ითხოვს)
    window.addEventListener('load', function () {
        setTimeout(function () {
            if (!nearLoad()) return;
            if (standalone) {
                if (typeof Notification !== 'undefined' && Notification.permission === 'default' && !snoozed(ENABLE_KEY)) show('enable');
                return;
            }
            @if(!empty($iosOffer))
            if (!oldIos && !snoozed(LATER_KEY)) show('offer');
            @endif
        }, 2500);
    });
})();
</script>
