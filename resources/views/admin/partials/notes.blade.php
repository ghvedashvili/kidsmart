{{-- ადმინის პირადი „ჩანაწერები“ — ყველა ადმინის გვერდზე, ქვედა მარჯვენა კუთხეში.
     ნაგულისხმევად ჩაკეცილია; გაშლილი/ჩაკეცილი მდგომარეობა და ტექსტი ბაზაში ინახება (admin_notes). --}}
@php
    $__note = \Illuminate\Support\Facades\Schema::hasTable('admin_notes') ? \App\Models\AdminNote::for(auth()->id()) : null;
@endphp
@if($__note)
<div class="ksn {{ $__note->is_open ? 'open' : '' }}" id="ksNotes">
    <button type="button" class="ksn-tab" id="ksnOpen" aria-expanded="{{ $__note->is_open ? 'true' : 'false' }}" aria-controls="ksnPanel">
        <i class="bi bi-journal-text"></i> ჩანაწერები
        @if(trim((string) $__note->body) !== '')<span class="ksn-dot" aria-hidden="true"></span>@endif
    </button>
    <section class="ksn-panel" id="ksnPanel" aria-label="ჩანაწერები">
        <header class="ksn-head">
            <b><i class="bi bi-journal-text"></i> ჩანაწერები</b>
            <span class="ksn-status" id="ksnStatus" aria-live="polite">{{ $__note->updated_at ? 'შენახულია ' . $__note->updated_at->format('d.m H:i') : '' }}</span>
            <button type="button" class="ksn-x" id="ksnClose" aria-label="ჩაკეცვა" title="ჩაკეცვა"><i class="bi bi-chevron-down"></i></button>
        </header>
        <textarea id="ksnBody" class="ksn-body" spellcheck="false" placeholder="ჩაწერე აქ, რაც გინდა — ავტომატურად შეინახება.">{{ $__note->body }}</textarea>
        <footer class="ksn-foot">
            <span>ინახება ავტომატურად · Ctrl+S</span>
            <button type="button" class="ksn-save" id="ksnSave"><i class="bi bi-check2"></i> შენახვა</button>
        </footer>
    </section>
</div>
<style>
    .ksn { position: fixed; right: 18px; bottom: 18px; z-index: 1040; font-family: var(--ks-font, inherit); }
    .ksn-tab { display: inline-flex; align-items: center; gap: 8px; border: 0; border-radius: 999px; padding: 10px 16px; background: #2B2622; color: #fff; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 6px 20px rgba(43, 38, 34, .25); position: relative; }
    .ksn-tab:hover { background: #E2590F; }
    .ksn-tab:focus-visible { outline: 3px solid #FFC49A; outline-offset: 2px; }
    .ksn-dot { position: absolute; top: 6px; right: 8px; width: 8px; height: 8px; border-radius: 50%; background: #E2590F; }
    .ksn-panel { display: none; width: min(400px, calc(100vw - 24px)); height: min(60vh, 560px); background: #fff; border: 1px solid #ECE9E4; border-radius: 18px; box-shadow: 0 16px 48px rgba(43, 38, 34, .22); flex-direction: column; overflow: hidden; }
    .ksn.open .ksn-panel { display: flex; }
    .ksn.open .ksn-tab { display: none; }
    .ksn-head { display: flex; align-items: center; gap: 8px; padding: 10px 12px 10px 16px; border-bottom: 1px solid #F1EFEB; }
    .ksn-head b { font-size: 15px; color: #2B2622; display: flex; align-items: center; gap: 6px; }
    .ksn-head b i { color: #E2590F; }
    .ksn-status { flex: 1; text-align: right; font-size: 12px; color: #8A8178; }
    .ksn-status.dirty { color: #C24A0A; } .ksn-status.err { color: #A12626; font-weight: 700; }
    .ksn-x { border: 0; background: #F4F2EE; width: 30px; height: 30px; border-radius: 10px; cursor: pointer; color: #4F473F; }
    .ksn-x:hover { background: #FFE3CC; color: #C24A0A; }
    .ksn-body { flex: 1; width: 100%; border: 0; resize: none; padding: 14px 16px; font-size: 14px; line-height: 1.6; color: #2B2622; outline: none; font-family: inherit; }
    .ksn-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 12px; border-top: 1px solid #F1EFEB; font-size: 12px; color: #8A8178; }
    .ksn-save { border: 0; border-radius: 10px; padding: 7px 12px; background: #E2590F; color: #fff; font-weight: 800; font-size: 13px; cursor: pointer; }
    .ksn-save:hover { background: #C24A0A; }
    @media (max-width: 600px) { .ksn { right: 12px; bottom: 12px; } }
    @media print { .ksn { display: none; } }
</style>
<script>
(function () {
    var box = document.getElementById('ksNotes'), body = document.getElementById('ksnBody'), status = document.getElementById('ksnStatus');
    var openBtn = document.getElementById('ksnOpen'), token = @json(csrf_token());
    var saved = body.value, timer = null, saving = false;
    function post(url, data, keepalive) {
        var fd = new FormData(); fd.append('_token', token);
        Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        return fetch(url, { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, keepalive: !!keepalive });
    }
    function setOpen(open) {
        box.classList.toggle('open', open);
        openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        post(@json(route('admin.notes.toggle')), { open: open ? 1 : 0 });
        if (open) setTimeout(function () { body.focus(); }, 30); else openBtn.focus();
    }
    function save(keepalive) {
        if (body.value === saved || saving) return;
        var text = body.value; saving = true;
        status.className = 'ksn-status'; status.textContent = 'ინახება…';
        post(@json(route('admin.notes.save')), { body: text }, keepalive)
            .then(function (r) { return r.json(); })
            .then(function (j) { saved = text; status.textContent = 'შენახულია ' + (j.saved_at || ''); })
            .catch(function () { status.className = 'ksn-status err'; status.textContent = 'ვერ შეინახა — სცადე თავიდან'; })
            .finally(function () { saving = false; if (body.value !== saved) schedule(); });
    }
    function schedule() { status.className = 'ksn-status dirty'; status.textContent = 'შეუნახავი…'; clearTimeout(timer); timer = setTimeout(save, 1200); }
    openBtn.addEventListener('click', function () { setOpen(true); });
    document.getElementById('ksnClose').addEventListener('click', function () { save(); setOpen(false); });
    document.getElementById('ksnSave').addEventListener('click', function () { clearTimeout(timer); save(); });
    body.addEventListener('input', schedule);
    body.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); clearTimeout(timer); save(); }
        if (e.key === 'Escape') { save(); setOpen(false); }
    });
    // გვერდიდან გასვლისას შეუნახავი ტექსტი არ დაიკარგოს
    window.addEventListener('pagehide', function () { save(true); });
})();
</script>
@endif
