@extends('layouts.app')
{{-- კოდის გამტეხი: 22 სახლი × 10 კარი. პირობებით გამოიცანი 4-ციფრა კოდი და გააღე კარი.
     დონეები: პირველი კარები ხელით დაწერილია (DATA), დანარჩენი — გენერატორით (CB.door, დეტერმინისტული, SEEDS).
     პროგრესი ინახება game_sessions.state.p-ში ("სახლი-კარი" => ვარსკვლავები). --}}
@section('content')
@include('partials.achievement-toast')
<style>
.cbx { --o: #E2590F; --o2: #C24A0A; --t1: #FFF3EA; --t2: #FFE3CC; --ink: #2B2622; --mut: #6B625A; --line: #ECE9E4; --ok: #1E8A4C; --bad: #C2372B;
       --p1: #E2590F; --p2: #1E8A4C; --p3: #2F7BF5; --p4: #8B5CF6;
       max-width: 1040px; margin: 0 auto; padding: 10px 14px 12px; box-sizing: border-box; display: flex; flex-direction: column; gap: 10px; color: var(--ink); font-family: 'Noto Sans Georgian', system-ui, sans-serif; user-select: none; -webkit-user-select: none; }
.cbx * { box-sizing: border-box; }
.cbx button { font-family: inherit; color: inherit; cursor: pointer; }
.cbx button:focus-visible, .cbx [tabindex]:focus-visible { outline: 3px solid rgba(226, 89, 15, .45); outline-offset: 2px; }
.cb-scroll { flex: 1 1 auto; min-height: 0; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; padding-bottom: 8px; }

/* რუკა */
.cb-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.cb-head h1 { margin: 0; font-size: 24px; font-weight: 900; }
.cb-head span { font-size: 13px; color: var(--mut); }
.cb-total { font-size: 15px; font-weight: 900; color: var(--o2) !important; background: var(--t1); border-radius: 99px; padding: 6px 12px; white-space: nowrap; }
.cb-prog { height: 10px; border-radius: 99px; background: var(--line); overflow: hidden; flex-shrink: 0; }
.cb-prog span { display: block; height: 100%; background: var(--o); border-radius: 99px; }
.cb-path { position: relative; display: flex; flex-direction: column; gap: 12px; padding: 6px 4px; max-width: 560px; margin: 0 auto; }
.cb-path::before { content: ''; position: absolute; left: 50%; top: 20px; bottom: 20px; border-left: 4px dashed #FFC49A; transform: translateX(-2px); }
.cb-house { position: relative; z-index: 1; width: 76%; display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 18px; background: #fff; border: 1.5px solid var(--line); text-align: left; }
.cb-house.l { align-self: flex-start; } .cb-house.r { align-self: flex-end; }
.cb-house.cur { background: var(--t1); border: 2.5px solid var(--o); box-shadow: 0 6px 18px rgba(226, 89, 15, .22); }
.cb-house.off { background: #F1EFEB; border-color: #E6E1DA; cursor: not-allowed; opacity: .85; }
.cb-hx { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.cb-hx small { font-size: 11px; font-weight: 800; color: var(--mut); }
.cb-hx b { font-size: 15px; font-weight: 900; line-height: 1.2; }
.cb-hx > span:not(.cb-badge) { font-size: 12px; color: var(--mut); }
.cb-badge { align-self: flex-start; margin-top: 2px; font-size: 12px; font-weight: 800; border-radius: 99px; padding: 2px 8px; background: #E6E1DA; color: var(--mut); }
.cb-badge.ok { background: #DFF5E3; color: #14612F; }
.cb-badge.cur { background: var(--o); color: #fff; }
.cb-mini { width: 42px; height: 54px; flex-shrink: 0; border: 2.5px solid var(--ink); background: var(--frame); padding: 4px; border-radius: 6px; }
.cb-mini i { display: block; width: 100%; height: 100%; border: 2px solid var(--ink); background: var(--door); border-radius: 3px; }
.cb-mini.sh-arch, .cb-mini.sh-arch i { border-radius: 20px 20px 4px 4px; }
.cb-mini.off { filter: grayscale(1) brightness(.95); opacity: .7; }
.cb-mini.big { width: 64px; height: 84px; padding: 6px; }
.cb-mini.big.sh-arch, .cb-mini.big.sh-arch i { border-radius: 32px 32px 6px 6px; }

/* სახლი */
.cb-bar { display: flex; align-items: center; gap: 10px; flex-shrink: 0; flex-wrap: wrap; }
.cb-back { height: 40px; padding: 0 14px; border-radius: 22px; border: 2px solid var(--line); background: #fff; font-size: 14px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; max-width: 60%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cb-back:hover { border-color: var(--ink); }
.cb-bar .cb-total { margin-left: auto; }
.cb-where { font-size: 14px; font-weight: 900; }
.cb-dots { display: flex; gap: 3px; margin-left: auto; }
.cb-dots i { width: 9px; height: 9px; border-radius: 50%; background: var(--line); }
.cb-dots i.on { background: var(--o); } .cb-dots i.cur { background: #FFC49A; }
.cb-intro { display: flex; gap: 14px; align-items: flex-start; background: #fff; border-radius: 22px; padding: 16px; box-shadow: 0 2px 0 var(--line); }
.cb-intro small { font-size: 12px; font-weight: 800; color: var(--o2); }
.cb-intro h2 { margin: 2px 0 6px; font-size: 21px; font-weight: 900; }
.cb-intro p { margin: 0 0 8px; font-size: 14px; line-height: 1.55; color: #4F473F; }
.cb-ex { background: var(--t1); border: 2px dashed #FFC49A; border-radius: 12px; padding: 8px 10px; font-size: 13px; font-weight: 700; }
.cb-doors { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; margin-top: 12px; }
.cb-door { min-height: 70px; border-radius: 18px; border: 2px solid var(--line); background: #fff; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: 4px; padding: 10px 14px; text-align: left; }
.cb-door b { font-size: 15px; font-weight: 900; }
.cb-door .st { font-size: 15px; letter-spacing: 1px; color: #E0A800; font-weight: 800; }
.cb-door:not(.done):not(.off) { border-color: var(--o); background: var(--t1); }
.cb-door:not(.done):not(.off) .st { color: var(--o2); letter-spacing: 0; font-size: 13px; }
.cb-door.off { background: #F1EFEB; cursor: not-allowed; opacity: .75; }
.cb-door.boss { grid-column: 1 / -1; background: linear-gradient(90deg, #FFE7A3, #FFF6D6); border: 2.5px solid #E0A800; }
.cb-reward { margin: 12px 0 0; text-align: center; font-size: 14px; color: var(--mut); }
.cb-reward b { color: var(--o2); }
.cb-door.boss.off { background: #F1EFEB; border-color: #E6E1DA; }

/* თამაში */
.cb-play { flex: 1 1 auto; min-height: 0; display: flex; flex-direction: column; gap: 8px; }
.cb-left { display: flex; flex-direction: column; gap: 6px; flex: 0 1 auto; min-height: 0; overflow-y: auto; max-height: 44%; }
.cb-right { flex: 1 1 auto; min-height: 0; display: flex; flex-direction: column; gap: 6px; }
@media (min-width: 900px) {
    .cb-play { flex-direction: row; gap: 22px; }
    .cb-left { flex: 1 1 0; max-height: none; }
    .cb-right { flex: 1 1 0; }
}
.cb-bossnote { display: flex; gap: 8px; align-items: center; background: linear-gradient(90deg, #FFE7A3, #FFF6D6); border: 2px solid #E0A800; border-radius: 14px; padding: 8px 10px; font-size: 13px; }
.cb-recall { background: #fff; border-radius: 12px; padding: 6px 12px; font-size: 13px; box-shadow: 0 1px 0 var(--line); }
.cb-recall summary { cursor: pointer; font-weight: 800; color: var(--o2); }
.cb-recall p { margin: 6px 0 2px; line-height: 1.5; color: #4F473F; }
.cb-story { background: #FFF8DC; border-radius: 14px; padding: 10px 12px; font-size: 15px; line-height: 1.5; }
.cb-story b { display: block; font-size: 12px; color: var(--o2); }
.cb-clues { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
.cb-clue { position: relative; width: 100%; display: flex; gap: 10px; align-items: center; text-align: left; padding: 9px 12px; background: #fff; border: 2px solid var(--line); border-radius: 14px; font-size: 15px; font-weight: 600; line-height: 1.35; }
.cb-clue.auto { cursor: default; }
.cb-clue .box { width: 22px; height: 22px; border-radius: 7px; border: 2.5px solid var(--ink); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900; }
.cb-clue.done { opacity: .62; }
.cb-clue.done .box { background: var(--ok); border-color: var(--ok); color: #fff; }
.cb-clue.done .tx { text-decoration: line-through; }
.cb-clue.fail { border-color: var(--bad); background: #FFF4F3; opacity: 1; }
.cb-clue.fail .box { border-color: var(--bad); }
.cb-failtag { position: absolute; top: -9px; right: 10px; background: var(--bad); color: #fff; font-size: 11px; font-weight: 800; padding: 0 8px; border-radius: 6px; }
.pw { padding: 0 2px; border-radius: 3px; }
.pw1 { box-shadow: inset 0 -4px 0 var(--p1); } .pw2 { box-shadow: inset 0 -4px 0 var(--p2); } .pw3 { box-shadow: inset 0 -4px 0 var(--p3); } .pw4 { box-shadow: inset 0 -4px 0 var(--p4); }
.cb-tip { margin: 0; font-size: 12px; color: var(--mut); }

/* კარის სცენა */
.cb-scene { position: relative; flex: 1 1 auto; min-height: 220px; border-radius: 22px; overflow: hidden; background: var(--wall); border: 3px solid var(--ink); display: flex; align-items: flex-end; justify-content: center; padding: 12px 12px 22px; }
.cb-scene::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 22px; background: rgba(0, 0, 0, .12); border-top: 3px solid var(--ink); z-index: 0; }
.cb-scene.dark::after { background: rgba(0, 0, 0, .3); }
.cb-frame { position: relative; z-index: 1; height: 100%; max-height: 380px; aspect-ratio: 3 / 4; max-width: 100%; background: var(--frame); border: 3.5px solid var(--ink); padding: 10px; border-radius: 10px; }
.sh-arch .cb-frame { border-radius: 999px 999px 12px 12px; }
.cb-way, .cb-leaf { position: absolute; inset: 10px; border: 3px solid var(--ink); border-radius: 7px; overflow: hidden; }
.sh-arch .cb-way, .sh-arch .cb-leaf { border-radius: 999px 999px 8px 8px; }
.cb-way { background: radial-gradient(circle at 50% 40%, #FFF9DC, #FFD987 55%, #F0A64A); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; text-align: center; padding: 10px; }
.cb-way > * { opacity: 0; transform: translateY(8px); transition: opacity .4s .25s, transform .4s .25s; }
.cb-scene.open .cb-way > * { opacity: 1; transform: none; }
.cb-prize { font-size: 54px; line-height: 1; }
.cb-wst { font-size: 26px; letter-spacing: 3px; color: #D49100; line-height: 1; }
.cb-wt { font-size: 19px; font-weight: 900; }
.cb-code { font-size: 22px; font-weight: 900; letter-spacing: 5px; }
.cb-leaf { z-index: 2; background: var(--door); display: flex; flex-direction: column; align-items: center; justify-content: center; transition: opacity .35s, transform .35s; }
.cb-scene.open .cb-leaf { opacity: 0; transform: scale(1.04); pointer-events: none; visibility: hidden; transition: opacity .35s, transform .35s, visibility 0s .35s; }
.cb-scene.shake .cb-leaf { animation: cbShake .45s; }
@keyframes cbShake { 20% { transform: translateX(-10px); } 40% { transform: translateX(9px); } 60% { transform: translateX(-6px); } 80% { transform: translateX(4px); } }
.cb-knob { position: absolute; right: 10px; top: 60%; width: 14px; height: 14px; border-radius: 50%; background: #FFC93C; border: 2.5px solid var(--ink); z-index: 3; }
.cb-knob.ball { width: 24px; height: 24px; background: none; border: 0; right: 6px; }

/* ტექსტურები */
.tx-panels .cb-leaf { background: linear-gradient(var(--door2), var(--door2)) 50% 10%/62% 4px no-repeat, linear-gradient(var(--door2), var(--door2)) 50% 90%/62% 4px no-repeat, var(--door); }
.tx-planks .cb-leaf { background: repeating-linear-gradient(90deg, var(--door) 0 30px, var(--door2) 30px 33px); }
.tx-hplanks .cb-leaf { background: repeating-linear-gradient(0deg, var(--door) 0 30px, var(--door2) 30px 33px); }
.tx-stripes .cb-leaf { background: repeating-linear-gradient(90deg, var(--door) 0 24px, var(--door2) 24px 48px); }
.tx-hstripes .cb-leaf { background: repeating-linear-gradient(0deg, var(--door) 0 34px, var(--door2) 34px 68px); }
.tx-bars .cb-leaf { background: repeating-linear-gradient(90deg, var(--door2) 0 10px, var(--door) 10px 40px); }
.tx-barn .cb-leaf { background: linear-gradient(to top right, transparent calc(50% - 5px), #FDF0D5 calc(50% - 5px) calc(50% + 5px), transparent calc(50% + 5px)), linear-gradient(to top left, transparent calc(50% - 5px), #FDF0D5 calc(50% - 5px) calc(50% + 5px), transparent calc(50% + 5px)), repeating-linear-gradient(90deg, var(--door) 0 28px, var(--door2) 28px 31px); }
.tx-metal .cb-leaf { background: radial-gradient(circle, var(--door2) 3px, transparent 4px) 0 0/28px 28px, linear-gradient(160deg, var(--door), var(--door2) 160%); }
.tx-ice .cb-leaf { background: linear-gradient(130deg, transparent 0 18%, rgba(255,255,255,.75) 18% 22%, transparent 22% 55%, rgba(255,255,255,.55) 55% 58%, transparent 58%), linear-gradient(180deg, var(--door), var(--door2)); }
.tx-stars .cb-leaf { background: radial-gradient(circle at 20% 30%, #fff 1.6px, transparent 2.6px) 0 0/36px 36px, radial-gradient(circle at 70% 70%, rgba(255,255,255,.6) 1.6px, transparent 2.6px) 0 0/50px 50px, var(--door); }
.tx-bands .cb-leaf { background: linear-gradient(var(--frame), var(--frame)) 0 22%/100% 10px no-repeat, linear-gradient(var(--frame), var(--frame)) 0 78%/100% 10px no-repeat, repeating-linear-gradient(0deg, var(--door) 0 28px, var(--door2) 28px 31px); }
.tx-hero .cb-leaf { background: linear-gradient(160deg, var(--door) 0 55%, var(--door2) 55%); }
.tx-gold .cb-leaf { background: linear-gradient(#E0A800, #E0A800) 50% 0/4px 100% no-repeat, linear-gradient(#E0A800, #E0A800) 0 18%/100% 4px no-repeat, linear-gradient(180deg, var(--door), var(--door2)); }
.tx-plain .cb-leaf { background: linear-gradient(180deg, var(--door), var(--door2)); }
.tx-net .cb-leaf { background: repeating-linear-gradient(45deg, rgba(255,255,255,.75) 0 2px, transparent 2px 14px), repeating-linear-gradient(-45deg, rgba(255,255,255,.75) 0 2px, transparent 2px 14px), linear-gradient(180deg, var(--door), var(--door2)); }
.sh-goal .cb-frame { aspect-ratio: 4 / 3; border: 3px solid var(--ink); background: #fff; padding: 8px 8px 0; border-radius: 6px 6px 0 0; }
.sh-goal .cb-way, .sh-goal .cb-leaf { inset: 8px 8px 0; border-radius: 0; border-bottom: 0; }
.sh-goal::after { background: repeating-linear-gradient(90deg, #6CC07A 0 30px, #5AAE68 30px 60px) !important; }
.tx-books .cb-leaf { background: #8A5A2E; }
.cb-books { position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: space-around; padding: 4px 0; }
.cb-shelf { display: flex; align-items: flex-end; gap: 2px; height: 17%; border-bottom: 4px solid #5A3B22; padding: 0 4px; }
.cb-shelf span { border: 1.5px solid var(--ink); border-radius: 2px; }
.tx-books .cb-plate { background: rgba(255, 255, 255, .96); }

/* დეკორაციები */
.cb-vine { position: absolute; top: 8px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; z-index: 2; }
.cb-vine i { width: 18px; height: 18px; border-radius: 50% 0; background: #7BC47F; border: 2px solid var(--ink); }
.cb-vine i:nth-child(even) { background: #5AA65F; transform: rotate(-40deg); }
.cb-chalk { position: absolute; top: 20px; font-size: 20px; font-weight: 900; color: rgba(46, 94, 69, .3); line-height: 1.5; }
.cb-chalk.l { left: 16px; } .cb-chalk.r { right: 16px; }
.cb-fans { position: absolute; top: 10px; left: 0; right: 0; display: flex; justify-content: space-around; }
.cb-fans i { width: 16px; height: 16px; border-radius: 50%; border: 2px solid var(--ink); background: #E2590F; }
.cb-fans i:nth-child(3n+2) { background: #2F7BF5; } .cb-fans i:nth-child(3n) { background: #FFC93C; }
.cb-jersey { position: absolute; top: 8%; width: 34%; }
.cb-glass { position: absolute; top: 7%; width: 66%; height: 22%; background: linear-gradient(135deg, #CFE8FF, #A9D4FA); border: 2.5px solid var(--ink); border-radius: 6px; }
.cb-board { position: absolute; bottom: 9%; width: 72%; padding: 3px 0; background: #2E5E45; border: 2.5px solid #8A5A2E; border-radius: 5px; color: #fff; font-size: 11px; font-weight: 800; text-align: center; }
.cb-cap { position: absolute; top: 12%; width: 22%; }

/* საკეტი */
.cb-plate { position: relative; z-index: 3; display: grid; grid-template-columns: repeat(4, 1fr); gap: 5px; width: min(240px, 88%); padding: 6px; background: #fff; border: 3px solid var(--ink); border-radius: 13px; box-shadow: 0 3px 0 rgba(0, 0, 0, .2); }
.is-boss .cb-plate { background: #FFF6D6; }
.cb-slot { display: flex; flex-direction: column; gap: 3px; }
.cb-arr { height: 26px; border: 2px solid var(--ink); border-radius: 7px; background: #fff; font-size: 11px; font-weight: 900; padding: 0; }
.cb-arr:active { background: #FFC93C; }
.cb-win { height: 46px; display: flex; align-items: center; justify-content: center; background: #fff; border: 2.5px solid var(--ink); border-radius: 9px; font-size: 28px; font-weight: 900; cursor: pointer; }
.cb-slot.s1 .cb-win { border-bottom: 6px solid var(--p1); } .cb-slot.s2 .cb-win { border-bottom: 6px solid var(--p2); }
.cb-slot.s3 .cb-win { border-bottom: 6px solid var(--p3); } .cb-slot.s4 .cb-win { border-bottom: 6px solid var(--p4); }
.cb-slot.sel .cb-win { background: #FFFBE3; outline: 3px solid var(--o); outline-offset: 1px; }
.cb-win.empty { color: #C9C3BB; } .cb-win.lk { background: #E9FBE9; }
.cb-panel { flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; }
.cb-msg { margin: 0; min-height: 18px; text-align: center; font-size: 13px; font-weight: 800; color: var(--bad); }
.cb-acts { display: flex; gap: 10px; }
.cb-btn { height: 52px; border: none; border-radius: 16px; font-size: 16px; font-weight: 900; padding: 0 18px; }
.cb-btn.main { flex: 1; background: var(--o); color: #fff !important; font-size: 17px; }
.cb-btn.main:hover { background: var(--o2); }
.cb-btn.soft { background: var(--t1); color: var(--o2) !important; }
.cb-coin { position: fixed; left: 50%; top: 84px; transform: translate(-50%, -12px); opacity: 0; z-index: 1300; background: #FFC93C; color: #2B2622; border: 2.5px solid #2B2622; border-radius: 16px; padding: 10px 18px; font-family: 'Noto Sans Georgian', sans-serif; font-size: 16px; font-weight: 900; box-shadow: 0 8px 24px rgba(0,0,0,.2); transition: opacity .3s, transform .3s; }
.cb-coin.show { opacity: 1; transform: translate(-50%, 0); }
@media (max-height: 720px) { .cb-win { height: 40px; font-size: 24px; } .cb-arr { height: 22px; } .cb-btn { height: 46px; } .cb-clue { padding: 7px 10px; font-size: 14px; } }
@media (prefers-reduced-motion: reduce) { .cb-leaf, .cb-way > * { transition: none; } .cb-scene.shake .cb-leaf { animation: none; } }
</style>

<div class="cbx v-map" id="cbx" aria-live="polite"></div>

<script>
window.CB_CFG = {
    progress: @json($progress),
    saveUrl: @json(route('games.code-breaker.save')),
    csrf: @json(csrf_token()),
};
</script>
@verbatim
<script>
const DATA=[{"title":"ციფრები და მათი ადგილები","new":"ბოლო, შუაში, ნაპირებში","explain":"კოდს 4 ციფრი აქვს: პირველი, მეორე, მესამე და მეოთხე. მეოთხე ციფრს „ბოლოსაც“ ვეძახით. მეორე და მესამე ციფრი შუაში დგას, პირველი და ბოლო კი ნაპირებში.","example":"„შუაში დგას 4 და 7“ → _ 4 7 _ ან _ 7 4 _","levels":[{"n":"ციფრები რიგით","c":["პირველი ციფრი არის 4","მეორე ციფრი არის 7","მესამე ციფრი არის 1","მეოთხე ციფრი არის 9"],"p":["((a===4))","((b===7))","((c===1))","((d===9))"],"s":null,"sols":["4719"],"uses":[]},{"n":"პირობები არეულია","c":["მესამე ციფრი არის 0","პირველი ციფრი არის 9","მეოთხე ციფრი არის 6","მეორე ციფრი არის 2"],"p":["((c===0))","((a===9))","((d===6))","((b===2))"],"s":null,"sols":["9206"],"uses":[]},{"n":"ახალი სიტყვა: ბოლო","c":["მეორე ციფრი არის 5","ბოლო ციფრი არის 3","პირველი ციფრი არის 8","მესამე ციფრი არის 1"],"p":["((b===5))","((d===3))","((a===8))","((c===1))"],"s":null,"sols":["8513"],"uses":[]},{"n":"ახალი სიტყვა: შუაში","c":["პირველი ციფრი არის 1","შუაში დგას ციფრები 4 და 7","ბოლო ციფრი არის 9","მეორე ციფრი არის 7"],"p":["((a===1))","pairEq([b,c],[4,7])","((d===9))","((b===7))"],"s":null,"sols":["1749"],"uses":[]},{"n":"ახალი სიტყვა: ნაპირებში","c":["ნაპირებში დგას ციფრები 2 და 8","მეორე ციფრი არის 5","მესამე ციფრი არის 3","ბოლო ციფრი არის 2"],"p":["pairEq([a,d],[2,8])","((b===5))","((c===3))","((d===2))"],"s":null,"sols":["8532"],"uses":[]},{"n":"ყველაფერი ერთად","c":["შუაში დგას ციფრები 5 და 0","ნაპირებში დგას ციფრები 6 და 3","მეორე ციფრი არის 0","ბოლო ციფრი არის 6"],"p":["pairEq([b,c],[5,0])","pairEq([a,d],[6,3])","((b===0))","((d===6))"],"s":null,"sols":["3056"],"uses":[]}]},{"title":"ტოლობა","new":"ტოლია, ერთნაირია","explain":"„ტოლია“ ნიშნავს, რომ ორი ციფრი ერთნაირია. „შუაში ორივე ციფრი ერთნაირია“ ნიშნავს, რომ მეორე და მესამე ერთი და იგივე ციფრია.","example":"„ბოლო ციფრი პირველის ტოლია“ → 3 _ _ 3","levels":[{"n":"ახალი: ტოლობა","uses":[],"c":["ბოლო ციფრი არის 4","მესამე ციფრი არის 5","პირველი ციფრი არის 2","მესამე ციფრი მეორის ტოლია"],"p":["d===4","c===5","a===2","c===b"],"s":null,"sols":["2554"]},{"n":"ტოლობა","uses":[],"c":["შუაში დგას ციფრები 1 და 8","მეორე ციფრი პირველის ტოლია","მეორე ციფრი არის 1","ბოლო ციფრი არის 8"],"p":["pairEq([b,c],[1,8])","b===a","b===1","d===8"],"s":null,"sols":["1188"]},{"n":"შუაში ორივე ერთნაირია","uses":[],"c":["ნაპირებში დგას ციფრები 0 და 1","პირველი ციფრი არის 1","შუაში ორივე ციფრი ერთნაირია","მეორე ციფრი არის 5"],"p":["pairEq([a,d],[0,1])","a===1","b===c","b===5"],"s":null,"sols":["1550"]},{"n":"ნაპირებში ორივე ერთნაირია","uses":[],"c":["შუაში დგას ციფრები 1 და 9","პირველი ციფრი არის 1","ნაპირებში ორივე ციფრი ერთნაირია","მესამე ციფრი არის 1"],"p":["pairEq([b,c],[1,9])","a===1","a===d","c===1"],"s":null,"sols":["1911"]},{"n":"ტოლობა","uses":[],"c":["მესამე ციფრი მეორის ტოლია","მეორე ციფრი არის 8","ბოლო ციფრი არის 0","მეორე ციფრი პირველის ტოლია"],"p":["c===b","b===8","d===0","b===a"],"s":null,"sols":["8880"]},{"n":"შუაში ორივე ერთნაირია + ნაპირებში ორივე ერთნაირია","uses":[],"c":["შუაში ორივე ციფრი ერთნაირია","პირველი ციფრი არის 4","მეორე ციფრი არის 3","ნაპირებში ორივე ციფრი ერთნაირია"],"p":["b===c","a===4","b===3","a===d"],"s":null,"sols":["4334"]}]},{"title":"მომდევნო და წინა","new":"მომდევნო, წინა, 2-ით მეტი","explain":"მომდევნო რიცხვი 1-ით მეტია: 5-ის მომდევნოა 6. წინა რიცხვი 1-ით ნაკლებია: 5-ის წინაა 4. „2-ით მეტი“ ნიშნავს, რომ ვუმატებთ 2-ს.","example":"„მეორე ციფრი პირველის მომდევნოა“ → 3 4 _ _","levels":[{"n":"ახალი: მომდევნო","uses":["ტოლობა"],"c":["მესამე ციფრი ბოლოს მომდევნო რიცხვია","ნაპირებში დგას ციფრები 6 და 8","ბოლო ციფრი მეორის ტოლია","მეორე ციფრი არის 6"],"p":["c===d+1","pairEq([a,d],[6,8])","d===b","b===6"],"s":null,"sols":["8676"]},{"n":"წინა","uses":["ტოლობა"],"c":["ბოლო ციფრი არის 8","მეორე ციფრი პირველის წინა რიცხვია","მესამე ციფრი პირველის ტოლია","ნაპირებში დგას ციფრები 5 და 8"],"p":["d===8","b===a-1","c===a","pairEq([a,d],[5,8])"],"s":null,"sols":["5458"]},{"n":"რამდენით მეტი","uses":[],"c":["მესამე ციფრი არის 4","შუაში დგას ციფრები 1 და 4","ბოლო ციფრი მესამეზე 2-ით მეტია","პირველი ციფრი არის 9"],"p":["c===4","pairEq([b,c],[1,4])","d===c+2","a===9"],"s":null,"sols":["9146"]},{"n":"რამდენით ნაკლები","uses":["ტოლობა"],"c":["მეორე ციფრი პირველის ტოლია","მესამე ციფრი არის 6","შუაში დგას ციფრები 5 და 6","მეორე ციფრი ბოლოზე 3-ით ნაკლებია"],"p":["b===a","c===6","pairEq([b,c],[5,6])","b===d-3"],"s":null,"sols":["5568"]},{"n":"მომდევნო + რამდენით მეტი","uses":[],"c":["მეორე ციფრი არის 5","მესამე ციფრი ბოლოს მომდევნო რიცხვია","ნაპირებში დგას ციფრები 2 და 7","ბოლო ციფრი მეორეზე 2-ით მეტია"],"p":["b===5","c===d+1","pairEq([a,d],[2,7])","d===b+2"],"s":null,"sols":["2587"]},{"n":"წინა + რამდენით ნაკლები","uses":[],"c":["მესამე ციფრი პირველის წინა რიცხვია","პირველი ციფრი მეორეზე 2-ით ნაკლებია","ბოლო ციფრი არის 8","მესამე ციფრი არის 3"],"p":["c===a-1","a===b-2","d===8","c===3"],"s":null,"sols":["4638"]}]},{"title":"კენტი და ლუწი","new":"კენტი, ლუწი","explain":"კენტი ციფრებია 1, 3, 5, 7, 9. ლუწი ციფრებია 0, 2, 4, 6, 8. ყველაზე დიდი კენტი ციფრი 9-ია, ყველაზე პატარა ლუწი ციფრი კი 0.","example":"„შუაში დგას 3 და 8, მესამე ლუწია“ → _ 3 8 _","levels":[{"n":"ახალი: კენტი და ლუწი","uses":["ტოლობა","მომდევნო და წინა"],"c":["ბოლო ციფრი მესამის ტოლია","მეორე ციფრი ლუწია","მესამე ციფრი პირველზე 2-ით მეტია","მეორე ციფრი პირველის მომდევნო რიცხვია"],"p":["d===c","ev(b)","c===a+2","b===a+1"],"s":null,"sols":["1233","3455","5677","7899"]},{"n":"კენტი და ლუწი","uses":["მომდევნო და წინა"],"c":["მეორე ციფრი პირველზე 3-ით ნაკლებია","ნაპირებში დგას ციფრები 4 და 5","მეორე ციფრი ლუწია","მესამე ციფრი მეორეზე 2-ით მეტია"],"p":["b===a-3","pairEq([a,d],[4,5])","ev(b)","c===b+2"],"s":null,"sols":["5244"]},{"n":"ყველაზე დიდი და პატარა ციფრი","uses":["მომდევნო და წინა"],"c":["ბოლო ციფრი ყველაზე დიდი ლუწი ციფრია","პირველი ციფრი არის 8","მესამე ციფრი არის 2","პირველი ციფრი მეორეზე 2-ით მეტია"],"p":["d===8","a===8","c===2","a===b+2"],"s":null,"sols":["8628"]},{"n":"კენტი და ლუწი","uses":[],"c":["მეორე ციფრი ლუწია","ნაპირებში დგას ციფრები 5 და 8","ბოლო ციფრი კენტია","შუაში დგას ციფრები 4 და 5"],"p":["ev(b)","pairEq([a,d],[5,8])","odd(d)","pairEq([b,c],[4,5])"],"s":null,"sols":["8455"]},{"n":"ყველაზე დიდი და პატარა ციფრი + კენტი და ლუწი","uses":["მომდევნო და წინა"],"c":["მესამე ციფრი ყველაზე დიდი კენტი ციფრია","მეორე ციფრი ბოლოს მომდევნო რიცხვია","მეორე ციფრი კენტია","ნაპირებში დგას ციფრები 4 და 5"],"p":["c===9","b===d+1","odd(b)","pairEq([a,d],[4,5])"],"s":null,"sols":["5594"]},{"n":"კენტი და ლუწი","uses":[],"c":["ნაპირებში დგას ციფრები 2 და 3","შუაში დგას ციფრები 0 და 5","მეორე ციფრი კენტია","ბოლო ციფრი ლუწია"],"p":["pairEq([a,d],[2,3])","pairEq([b,c],[0,5])","odd(b)","ev(d)"],"s":null,"sols":["3502"]}]},{"title":"მეტია, ნაკლებია, შორის","new":"მეტია, ნაკლებია, შორის","explain":"„7-ზე მეტია“ ნიშნავს 8-ს ან 9-ს. „2-ზე ნაკლებია“ ნიშნავს 0-ს ან 1-ს. „2-სა და 4-ს შორის“ ნიშნავს 3-ს, თავად 2 და 4 არ ითვლება!","example":"„7-სა და 9-ს შორისაა“ → 8","levels":[{"n":"ახალი: მეტია","uses":["კენტი და ლუწი","მომდევნო და წინა"],"c":["შუაში დგას ციფრები 2 და 7","პირველი ციფრი ლუწია","მეორე ციფრი ბოლოზე 2-ით მეტია","მეორე ციფრი 2-ზე მეტია"],"p":["pairEq([b,c],[2,7])","ev(a)","b===d+2","b>2"],"s":null,"sols":["0725","2725","4725","6725","8725"]},{"n":"ნაკლებია","uses":[],"c":["მეორე ციფრი არის 8","შუაში დგას ციფრები 4 და 8","ნაპირებში დგას ციფრები 3 და 9","ბოლო ციფრი 4-ზე ნაკლებია"],"p":["b===8","pairEq([b,c],[4,8])","pairEq([a,d],[3,9])","d<4"],"s":null,"sols":["9843"]},{"n":"შორის","uses":["მომდევნო და წინა"],"c":["ბოლო ციფრი 3-სა და 7-ს შორისაა","მეორე ციფრი პირველის მომდევნო რიცხვია","მეორე ციფრი ბოლოზე 3-ით მეტია","შუაში დგას ციფრები 3 და 8"],"p":["(d>3&&d<7)","b===a+1","b===d+3","pairEq([b,c],[3,8])"],"s":null,"sols":["7835"]},{"n":"ციფრი ციფრზე მეტია","uses":["მომდევნო და წინა"],"c":["შუაში დგას ციფრები 0 და 9","ბოლო ციფრი მეორეზე მეტია","პირველი ციფრი არის 6","ბოლო ციფრი პირველზე 2-ით ნაკლებია"],"p":["pairEq([b,c],[0,9])","d>b","a===6","d===a-2"],"s":null,"sols":["6094"]},{"n":"ორ ციფრს შორის","uses":["ტოლობა","მომდევნო და წინა"],"c":["ნაპირებში ორივე ციფრი ერთნაირია","მესამე ციფრი პირველსა და მეორეს შორისაა","შუაში დგას ციფრები 7 და 8","მეორე ციფრი პირველზე 3-ით მეტია"],"p":["a===d","(c>Math.min(a,b)&&c<Math.max(a,b))","pairEq([b,c],[7,8])","b===a+3"],"s":null,"sols":["5875"]},{"n":"კენტი + შედარება + ციფრი ციფრზე ნაკლებია","uses":["მომდევნო და წინა"],"c":["პირველი ციფრი ლუწია და 6-ზე მეტია","მესამე ციფრი მეორეზე ნაკლებია","ბოლო ციფრი მეორის წინა რიცხვია","შუაში დგას ციფრები 1 და 6"],"p":["ev(a)&&a>6","c<b","d===b-1","pairEq([b,c],[1,6])"],"s":null,"sols":["8615"]}]},{"title":"„არ არის“","new":"არ არის","explain":"„არ არის“ გამორიცხავს. „არ არის კენტი“ ნიშნავს, რომ ლუწია. „არ არის 7“ ნიშნავს, რომ 7 ამოვშალეთ. „ტოლი არ არის“ ნიშნავს, რომ ციფრები განსხვავებულია.","example":"„6-სა და 9-ს შორისაა და არ არის 7“ → 8","levels":[{"n":"ახალი: არ არის კენტი/ლუწი","uses":["მეტი და ნაკლები","მომდევნო და წინა"],"c":["მესამე ციფრი არ არის კენტი","პირველი ციფრი 4-სა და 7-ს შორისაა","მეორე ციფრი ბოლოზე 2-ით მეტია","შუაში დგას ციფრები 3 და 6"],"p":["!odd(c)","(a>4&&a<7)","b===d+2","pairEq([b,c],[3,6])"],"s":null,"sols":["5361","6361"]},{"n":"არ არის","uses":["ტოლობა","მომდევნო და წინა"],"c":["პირველი ციფრი არ არის 5","ბოლო ციფრი მეორის ტოლია","შუაში დგას ციფრები 5 და 7","პირველი ციფრი ბოლოზე 2-ით ნაკლებია"],"p":["a!==5","d===b","pairEq([b,c],[5,7])","a===d-2"],"s":null,"sols":["3575"]},{"n":"ტოლი არ არის","uses":["მეტი და ნაკლები","მომდევნო და წინა"],"c":["შუაში დგას ციფრები 2 და 3","პირველი ციფრი 4-სა და 6-ს შორისაა","პირველი ციფრი ბოლოზე 3-ით მეტია","ბოლო ციფრი მეორის ტოლი არ არის"],"p":["pairEq([b,c],[2,3])","(a>4&&a<6)","a===d+3","d!==b"],"s":null,"sols":["5322"]},{"n":"„…და არ არის“","uses":["მომდევნო და წინა","მეტი და ნაკლები"],"c":["პირველი ციფრი 5-ზე ნაკლებია და არ არის 2","ბოლო ციფრი მეორის მომდევნო რიცხვია","მესამე ციფრი კენტია და 4-სა და 7-ს შორისაა","ნაპირებში დგას ციფრები 3 და 9"],"p":["a<5&&a!==2","d===b+1","odd(c)&&(c>4&&c<7)","pairEq([a,d],[3,9])"],"s":null,"sols":["3859"]},{"n":"ტოლი არ არის","uses":["მომდევნო და წინა","მეტი და ნაკლები"],"c":["შუაში დგას ციფრები 5 და 9","ბოლო ციფრი მესამის ტოლი არ არის","მეორე ციფრი პირველზე 2-ით ნაკლებია","ბოლო ციფრი პირველზე მეტია"],"p":["pairEq([b,c],[5,9])","d!==c","b===a-2","d>a"],"s":null,"sols":["7598"]},{"n":"„…და არ არის“ + არ არის კენტი/ლუწი","uses":["მეტი და ნაკლები"],"c":["მეორე ციფრი პირველსა და მესამეს შორისაა","მესამე ციფრი 3-ზე ნაკლებია და არ არის 1","ნაპირებში დგას ციფრები 2 და 3","პირველი ციფრი არ არის კენტი"],"p":["(b>Math.min(a,c)&&b<Math.max(a,c))","c<3&&c!==1","pairEq([a,d],[2,3])","!odd(a)"],"s":null,"sols":["2103"]}]},{"title":"ძნელი „არ არის“","new":"არც… არც…, არ არის ნაკლები","explain":"„არ არის 5-ზე ნაკლები“ ნიშნავს 5-ს ან მეტს (5, 6, 7, 8, 9), აქ 5-იც შედის! „არც 3-ია, არც 5“ ნიშნავს, რომ ორივე ამოვშალეთ. „არც მეტია, არც ნაკლებია 5-ზე“ ნიშნავს, რომ ზუსტად 5-ია.","example":"„არც 3-ია, არც 5, მაგრამ 2-სა და 6-ს შორისაა“ → 4","levels":[{"n":"ახალი: არ არის ნაკლები","uses":["„არ არის“","მომდევნო და წინა"],"c":["ბოლო ციფრი 6-ზე მეტია და არ არის 8","პირველი ციფრი არ არის 5-ზე ნაკლები","შუაში დგას ციფრები 3 და 9","მესამე ციფრი პირველის მომდევნო რიცხვია"],"p":["d>6&&d!==8","a>=5","pairEq([b,c],[3,9])","c===a+1"],"s":null,"sols":["8397","8399"]},{"n":"არ არის მეტი","uses":["მეტი და ნაკლები","მომდევნო და წინა"],"c":["მესამე ციფრი კენტია და 3-ზე ნაკლებია","ნაპირებში დგას ციფრები 1 და 4","მეორე ციფრი არ არის 3-ზე მეტი","პირველი ციფრი მეორის წინა რიცხვია"],"p":["odd(c)&&c<3","pairEq([a,d],[1,4])","b<=3","a===b-1"],"s":null,"sols":["1214"]},{"n":"არც მეტი, არც ნაკლები","uses":["კენტი და ლუწი","მეტი და ნაკლები","მომდევნო და წინა"],"c":["მესამე ციფრი არც მეტია, არც ნაკლებია 7-ზე","მეორე ციფრი ყველაზე პატარა კენტი ციფრია","პირველი ციფრი კენტია და 6-სა და 9-ს შორისაა","პირველი ციფრი ბოლოზე 2-ით ნაკლებია"],"p":["c===7","b===1","odd(a)&&(a>6&&a<9)","a===d-2"],"s":null,"sols":["7179"]},{"n":"არც… არც…","uses":["კენტი და ლუწი","მეტი და ნაკლები"],"c":["მესამე ციფრი ყველაზე დიდი კენტი ციფრია","პირველი ციფრი არც 4-ია, არც 9","მეორე ციფრი ლუწია და 5-სა და 8-ს შორისაა","ნაპირებში დგას ციფრები 4 და 7"],"p":["c===9","a!==4&&a!==9","ev(b)&&(b>5&&b<8)","pairEq([a,d],[4,7])"],"s":null,"sols":["7694"]},{"n":"არც… არც…, მაგრამ შორის","uses":["მეტი და ნაკლები","„არ არის“"],"c":["მესამე ციფრი არც 2-ია, არც 3, მაგრამ 1-სა და 5-ს შორისაა","მეორე ციფრი 5-სა და 7-ს შორისაა","ნაპირებში დგას ციფრები 0 და 5","ბოლო ციფრი 7-ზე ნაკლებია და არ არის 5"],"p":["c!==2&&c!==3&&c>1&&c<5","(b>5&&b<7)","pairEq([a,d],[0,5])","d<7&&d!==5"],"s":null,"sols":["5640"]},{"n":"არ არის მეტი","uses":["მეტი და ნაკლები","მომდევნო და წინა"],"c":["შუაში დგას ციფრები 2 და 5","მესამე ციფრი არ არის 3-ზე მეტი","ბოლო ციფრი კენტია და 3-სა და 7-ს შორისაა","ბოლო ციფრი პირველზე 2-ით მეტია"],"p":["pairEq([b,c],[2,5])","c<=3","odd(d)&&(d>3&&d<7)","d===a+2"],"s":null,"sols":["3525"]},{"n":"არც მეტი, არც ნაკლები ციფრზე + არც… არც…, მაგრამ შორის","uses":["მეტი და ნაკლები"],"c":["პირველი ციფრი არც მეტია, არც ნაკლებია ბოლოზე","შუაში დგას ციფრები 2 და 4","ბოლო ციფრი არც 2-ია, არც 5, მაგრამ 1-სა და 6-ს შორისაა","მეორე ციფრი პირველზე მეტია"],"p":["a===d","pairEq([b,c],[2,4])","d!==2&&d!==5&&d>1&&d<6","b>a"],"s":null,"sols":["3423"]}]},{"title":"ჯამი და სხვაობა","new":"ჯამი, სხვაობა","explain":"ჯამი: ორ რიცხვს ვკრებთ (3 და 4-ის ჯამი = 7). სხვაობა: დიდს ვაკლებთ პატარას (9 და 3-ის სხვაობა = 6).","example":"„მესამე პირველისა და მეორის ჯამია“ → 2 3 5 _","levels":[{"n":"ახალი: ჯამი","uses":["„არც… არც…“","მეტი და ნაკლები"],"c":["ბოლო ციფრი არც 3-ია, არც 6","პირველი ციფრი 5-ზე ნაკლებია","შუაში დგას ციფრები 0 და 9","პირველი ციფრი მეორისა და ბოლოს ჯამია"],"p":["d!==3&&d!==6","a<5","pairEq([b,c],[0,9])","a===b+d"],"s":null,"sols":["0090","1091","2092","4094"]},{"n":"სხვაობა","uses":["„არც… არც…“","მომდევნო და წინა"],"c":["მეორე ციფრი პირველისა და მესამის სხვაობაა","პირველი ციფრი არც 2-ია, არც 4, მაგრამ 1-სა და 6-ს შორისაა","ბოლო ციფრი მესამის წინა რიცხვია","ნაპირებში დგას ციფრები 0 და 3"],"p":["b===Math.abs(a-c)","a!==2&&a!==4&&a>1&&a<6","d===c-1","pairEq([a,d],[0,3])"],"s":null,"sols":["3210"]},{"n":"ჯამი რიცხვით","uses":["„არც… არც…“","„არ არის“"],"c":["მეორე ციფრი არც 3-ია, არც 7","პირველი ციფრი არ არის ლუწი","პირველისა და ბოლოს ჯამია 17","შუაში დგას ციფრები 3 და 9"],"p":["b!==3&&b!==7","!ev(a)","a+d===17","pairEq([b,c],[3,9])"],"s":null,"sols":["9938"]},{"n":"შუაში მდგომების ჯამი","uses":["მომდევნო და წინა","მეტი და ნაკლები","„არ არის“"],"c":["პირველი ციფრი ბოლოზე 2-ით ნაკლებია","მესამე ციფრი კენტია და 4-სა და 8-ს შორისაა","პირველი ციფრი 6-ზე მეტია და არ არის 8","შუაში მდგომი ციფრების ჯამია 16"],"p":["a===d-2","odd(c)&&(c>4&&c<8)","a>6&&a!==8","b+c===16"],"s":null,"sols":["7979"]},{"n":"ჯამი + სხვაობა რიცხვით","uses":["„არ არის“"],"c":["პირველი ციფრი მეორისა და მესამის ჯამია","შუაში დგას ციფრები 0 და 8","მესამისა და ბოლოს სხვაობაა 6","მესამე ციფრი 8-ზე ნაკლებია და არ არის 3"],"p":["a===b+c","pairEq([b,c],[0,8])","Math.abs(c-d)===6","c<8&&c!==3"],"s":null,"sols":["8806"]},{"n":"ნაპირების ჯამი + სხვაობა","uses":["„არც… არც…“","კენტი და ლუწი"],"c":["ნაპირებში მდგომი ციფრების ჯამია 14","ბოლო ციფრი არც 6-ია, არც 8, მაგრამ 4-სა და 9-ს შორისაა","პირველი ციფრი მეორისა და ბოლოს სხვაობაა","მესამე ციფრი ყველაზე დიდი ლუწი ციფრია"],"p":["a+d===14","d!==6&&d!==8&&d>4&&d<9","a===Math.abs(b-d)","c===8"],"s":null,"sols":["7087"]}]},{"title":"სიტყვა „ყველა“","new":"ყველა ციფრი, კოდში არ არის","explain":"„ყველა ციფრი“ ოთხივე ციფრზე ვრცელდება. „კოდში არ არის 0 და 5“ ნიშნავს, რომ 0 და 5 არცერთ ადგილზე არ დგას.","example":"„ყველა ციფრი 5-ზე მეტია“ → მხოლოდ 6, 7, 8, 9","levels":[{"n":"ახალი: ყველა ლუწი ან კენტი","uses":["„არც… არც…“","„არ არის“"],"c":["ბოლო ციფრი არც 3-ია, არც 4","ნაპირებში დგას ციფრები 4 და 6","ყველა ციფრი ლუწია","მეორე ციფრი 4-ზე ნაკლებია და არ არის 0"],"p":["d!==3&&d!==4","pairEq([a,d],[4,6])","[a,b,c,d].every(ev)","b<4&&b!==0"],"s":null,"sols":["4206","4226","4246","4266","4286"]},{"n":"ყველა მეტია","uses":["ჯამი და სხვაობა","მეტი და ნაკლები","„არც… არც…“"],"c":["პირველისა და ბოლოს სხვაობაა 3","პირველი ციფრი მეორესა და მესამეს შორისაა","ყველა ციფრი 4-ზე მეტია","მეორე ციფრი არც 5-ია, არც 7, მაგრამ 3-სა და 8-ს შორისაა"],"p":["Math.abs(a-d)===3","(a>Math.min(b,c)&&a<Math.max(b,c))","Math.min(a,b,c,d)>4","b!==5&&b!==7&&b>3&&b<8"],"s":null,"sols":["8695"]},{"n":"ყველა ნაკლებია","uses":["ჯამი და სხვაობა","„არც… არც…“","მეტი და ნაკლები"],"c":["ყველა ციფრი 4-ზე ნაკლებია","მეორე ციფრი პირველისა და მესამის ჯამია","პირველი ციფრი არც 2-ია, არც 4, მაგრამ 1-სა და 6-ს შორისაა","ბოლო ციფრი ლუწია და 1-სა და 4-ს შორისაა"],"p":["Math.max(a,b,c,d)<4","b===a+c","a!==2&&a!==4&&a>1&&a<6","ev(d)&&(d>1&&d<4)"],"s":null,"sols":["3302"]},{"n":"კოდში არ არის","uses":["„არც… არც…“","ჯამი და სხვაობა"],"c":["ნაპირებში დგას ციფრები 6 და 8","კოდში არ არის ციფრი 0","მესამე ციფრი არ არის 1-ზე მეტი","მეორისა და ბოლოს ჯამია 17"],"p":["pairEq([a,d],[6,8])","![a,b,c,d].includes(0)","c<=1","b+d===17"],"s":null,"sols":["6918"]},{"n":"ყველა ლუწი ან კენტი + კოდში არ არის ორი ციფრი","uses":["ჯამი და სხვაობა","მეტი და ნაკლები"],"c":["ყველა ციფრი კენტია","პირველისა და მეორის ჯამია 2","მესამე ციფრი პირველსა და ბოლოს შორისაა","კოდში არ არის ციფრები 3 და 7"],"p":["[a,b,c,d].every(odd)","a+b===2","(c>Math.min(a,d)&&c<Math.max(a,d))","![a,b,c,d].includes(3)&&![a,b,c,d].includes(7)"],"s":null,"sols":["1159"]},{"n":"კოდში არ არის + ყველა მეტია","uses":["მომდევნო და წინა","ჯამი და სხვაობა"],"c":["პირველი ციფრი მეორის მომდევნო რიცხვია","მესამისა და ბოლოს ჯამია 12","კოდში არ არის ციფრი 7","ყველა ციფრი 5-ზე მეტია"],"p":["a===b+1","c+d===12","![a,b,c,d].includes(7)","Math.min(a,b,c,d)>5"],"s":null,"sols":["9866"]}]},{"title":"სიტყვა „გარდა“","new":"გარდა","explain":"„გარდა“ გამონაკლისს ნიშნავს. „ყველა ციფრი ლუწია, გარდა მესამისა“: პირველი, მეორე და ბოლო ლუწია, მესამე კი კენტია.","example":"„ყველა 5-ზე მეტია, გარდა ბოლოსი“ → ბოლო 5 ან ნაკლებია","levels":[{"n":"ახალი: კენტი/ლუწი, გარდა ერთისა","uses":["მეტი და ნაკლები","ჯამი და სხვაობა","„ყველა“"],"c":["მესამე ციფრი კენტია და 6-სა და 9-ს შორისაა","მეორისა და ბოლოს ჯამია 3","კოდში არ არის ციფრები 1 და 5","ყველა ციფრი კენტია, გარდა ბოლოსი"],"p":["odd(c)&&(c>6&&c<9)","b+d===3","![a,b,c,d].includes(1)&&![a,b,c,d].includes(5)","odd(a)&&odd(b)&&odd(c)&&!(odd(d))"],"s":null,"sols":["3370","7370","9370"]},{"n":"მეტი, გარდა ერთისა","uses":["მეტი და ნაკლები","„არც… არც…“"],"c":["მეორე ციფრი ლუწია და 6-ზე ნაკლებია","ყველა ციფრი 3-ზე მეტია, გარდა ბოლოსი","მესამე ციფრი არც 3-ია, არც 5, მაგრამ 1-სა და 6-ს შორისაა","ნაპირებში დგას ციფრები 1 და 7"],"p":["ev(b)&&b<6","a>3&&b>3&&c>3&&!(d>3)","c!==3&&c!==5&&c>1&&c<6","pairEq([a,d],[1,7])"],"s":null,"sols":["7441"]},{"n":"ნაკლები, გარდა ერთისა","uses":["„არც… არც…“","ჯამი და სხვაობა"],"c":["შუაში დგას ციფრები 0 და 4","ბოლო ციფრი არც 1-ია, არც 2","პირველისა და მესამის ჯამია 5","ყველა ციფრი 3-ზე ნაკლებია, გარდა მესამისა"],"p":["pairEq([b,c],[0,4])","d!==1&&d!==2","a+c===5","a<3&&b<3&&d<3&&!(c<3)"],"s":null,"sols":["1040"]},{"n":"ტოლი, გარდა ერთისა","uses":["„ყველა“","მეტი და ნაკლები","ჯამი და სხვაობა"],"c":["კოდში არ არის ციფრები 0 და 6","ყველა ციფრი ტოლია, გარდა მესამისა","მეორე ციფრი კენტია და 2-ზე მეტია","პირველისა და მესამის ჯამია 5"],"p":["![a,b,c,d].includes(0)&&![a,b,c,d].includes(6)","a===b&&b===d&&c!==a","odd(b)&&b>2","a+c===5"],"s":null,"sols":["3323"]},{"n":"კენტი/ლუწი, გარდა ერთისა","uses":["„არც… არც…“","ჯამი და სხვაობა"],"c":["პირველი ციფრი არც 3-ია, არც 4, მაგრამ 2-სა და 7-ს შორისაა","მეორისა და ბოლოს ჯამია 10","შუაში დგას ციფრები 0 და 5","ყველა ციფრი კენტია, გარდა მესამისა"],"p":["a!==3&&a!==4&&a>2&&a<7","b+d===10","pairEq([b,c],[0,5])","odd(a)&&odd(b)&&odd(d)&&!(odd(c))"],"s":null,"sols":["5505"]},{"n":"მეტი, გარდა ერთისა","uses":["ჯამი და სხვაობა","„არ არის“"],"c":["ნაპირებში მდგომი ციფრების ჯამია 16","ყველა ციფრი 6-ზე მეტია, გარდა მესამისა","ბოლო ციფრი 4-სა და 8-ს შორისაა და არ არის 6","შუაში დგას ციფრები 1 და 9"],"p":["a+d===16","a>6&&b>6&&d>6&&!(c>6)","(d>4&&d<8)&&d!==6","pairEq([b,c],[1,9])"],"s":null,"sols":["9917"]}]},{"title":"განმეორება","new":"არ მეორდება, სარკული კოდი","explain":"„არცერთი ციფრი არ მეორდება“: ყველა ციფრი სხვადასხვაა. „მხოლოდ 2 განსხვავებული ციფრი“: კოდი ორი ციფრისგან შედგება (მაგ. 3388). სარკული კოდი წინიდან და უკნიდან ერთნაირად იკითხება (მაგ. 2442).","example":"„სარკულია, პირველი 3, მეორე 6“ → 3663","levels":[{"n":"ახალი: არ მეორდება","uses":["„გარდა“","ჯამი და სხვაობა","„არ არის“"],"c":["არცერთი ციფრი არ მეორდება","ყველა ციფრი 3-ზე ნაკლებია, გარდა ბოლოსი","მესამისა და ბოლოს სხვაობაა 8","მესამე ციფრი კენტია და არ არის 5"],"p":["new Set([a,b,c,d]).size===4","a<3&&b<3&&c<3&&!(d<3)","Math.abs(c-d)===8","odd(c)&&c!==5"],"s":null,"sols":["0219","2019"]},{"n":"მხოლოდ 2 ციფრი","uses":["„არც… არც…“","„არ არის“","ჯამი და სხვაობა"],"c":["მესამე ციფრი არც 4-ია, არც 5, მაგრამ 1-სა და 6-ს შორისაა","კოდში მხოლოდ 2 განსხვავებული ციფრია","ბოლო ციფრი 6-ზე ნაკლებია და არ არის 3","პირველისა და მეორის ჯამია 12"],"p":["c!==4&&c!==5&&c>1&&c<6","new Set([a,b,c,d]).size===2","d<6&&d!==3","a+b===12"],"s":null,"sols":["6622"]},{"n":"სარკული კოდი","uses":["ჯამი და სხვაობა","„ყველა“","„არც… არც…“"],"c":["მესამისა და ბოლოს ჯამია 9","კოდი წინიდან და უკნიდან ერთნაირად იკითხება","კოდში არ არის ციფრები 5 და 7","პირველი ციფრი არც 2-ია, არც 3, მაგრამ 0-სა და 5-ს შორისაა"],"p":["c+d===9","a===d&&b===c","![a,b,c,d].includes(5)&&![a,b,c,d].includes(7)","a!==2&&a!==3&&a>0&&a<5"],"s":null,"sols":["1881"]},{"n":"ციფრი ორჯერ","uses":["ჯამი და სხვაობა","მეტი და ნაკლები","მომდევნო და წინა"],"c":["მეორისა და მესამის ჯამია 10","მესამე ციფრი ლუწია და 2-სა და 5-ს შორისაა","პირველი ციფრი მეორის წინა რიცხვია","ციფრი 6 კოდში ზუსტად ორჯერ გვხვდება"],"p":["b+c===10","ev(c)&&(c>2&&c<5)","a===b-1","[a,b,c,d].filter(v=>v===6).length===2"],"s":null,"sols":["5646"]},{"n":"სარკული კოდი + მხოლოდ 2 ციფრი","uses":["მეტი და ნაკლები","ჯამი და სხვაობა"],"c":["კოდი წინიდან და უკნიდან ერთნაირად იკითხება","კოდში მხოლოდ 2 განსხვავებული ციფრია","მესამე ციფრი 5-სა და 8-ს შორისაა","პირველისა და მესამის ჯამია 14"],"p":["a===d&&b===c","new Set([a,b,c,d]).size===2","(c>5&&c<8)","a+c===14"],"s":null,"sols":["8668"]},{"n":"მხოლოდ 2 ციფრი + ციფრი ორჯერ","uses":["„ყველა“","ჯამი და სხვაობა"],"c":["კოდში მხოლოდ 2 განსხვავებული ციფრია","კოდში არ არის ციფრები 1 და 6","პირველისა და მესამის ჯამია 6","ციფრი 0 კოდში ზუსტად ორჯერ გვხვდება"],"p":["new Set([a,b,c,d]).size===2","![a,b,c,d].includes(1)&&![a,b,c,d].includes(6)","a+c===6","[a,b,c,d].filter(v=>v===0).length===2"],"s":null,"sols":["3030"]}]},{"title":"ძნელი ადგილის სიტყვები","new":"ბოლოსწინა, გვერდით, ლუწი ადგილი","explain":"ბოლოსწინა და ბოლოდან მეორე ერთი და იგივეა, მესამე ციფრი. „მეორე ციფრის გვერდით“ დგას პირველი და მესამე. ლუწ ადგილებზე დგას მე-2 და მე-4 ციფრი, კენტ ადგილებზე 1-ლი და მე-3. ფრთხილად: ლუწი ადგილი და ლუწი ციფრი სხვადასხვა რამაა!","example":"„ბოლოსწინა ციფრი არის 4“ → _ _ 4 _","levels":[{"n":"ახალი: ბოლოსწინა","uses":["ჯამი და სხვაობა","„გარდა“","„არც… არც…“"],"c":["ნაპირებში მდგომი ციფრების ჯამია 17","ბოლოსწინა ციფრი 4-სა და 8-ს შორისაა","ყველა ციფრი 5-ზე მეტია, გარდა მესამისა","მეორე ციფრი არც 1-ია, არც 9"],"p":["a+d===17","(c>4&&c<8)","a>5&&b>5&&d>5&&!(c>5)","b!==1&&b!==9"],"s":null,"sols":["8659","8759","8859","9658","9758","9858"]},{"n":"ლუწი და კენტი ადგილები","uses":["მომდევნო და წინა","„არც… არც…“"],"c":["ბოლო ციფრი მესამეზე 3-ით მეტია","ნაპირებში დგას ციფრები 4 და 9","ლუწ ადგილებზე მდგომი ციფრები ტოლია","მეორე ციფრი არც 3-ია, არც 6, მაგრამ 2-სა და 7-ს შორისაა"],"p":["d===c+3","pairEq([a,d],[4,9])","b===d","b!==3&&b!==6&&b>2&&b<7"],"s":null,"sols":["9414"]},{"n":"გვერდით მდგომი","uses":["„ყველა“","მეტი და ნაკლები","ჯამი და სხვაობა"],"c":["კოდში არ არის ციფრები 0 და 7","პირველი ციფრი კენტია და 6-ზე მეტია","მესამე ციფრის გვერდით დგას ციფრები 2 და 8","მეორისა და მესამის ჯამია 6"],"p":["![a,b,c,d].includes(0)&&![a,b,c,d].includes(7)","odd(a)&&a>6","pairEq([b,d],[2,8])","b+c===6"],"s":null,"sols":["9248"]},{"n":"ლუწი და კენტი ადგილები","uses":["მეტი და ნაკლები","განმეორება","„არ არის“"],"c":["ლუწ ადგილებზე მდგომი ციფრები ლუწია","პირველი ციფრი კენტია და 5-სა და 9-ს შორისაა","ციფრი 0 კოდში ზუსტად ორჯერ გვხვდება","მეორე ციფრი 4-ზე ნაკლებია და არ არის 0"],"p":["ev(b)&&ev(d)","odd(a)&&(a>5&&a<9)","[a,b,c,d].filter(v=>v===0).length===2","b<4&&b!==0"],"s":null,"sols":["7200"]},{"n":"ბოლოსწინა + გვერდით მდგომი","uses":["მომდევნო და წინა","„გარდა“"],"c":["მესამე ციფრი პირველზე 3-ით ნაკლებია","ყველა ციფრი 5-ზე ნაკლებია, გარდა ბოლოსი","ბოლოსწინა ციფრი 0-სა და 4-ს შორისაა","მესამე ციფრის გვერდით დგას ციფრები 0 და 7"],"p":["c===a-3","a<5&&b<5&&c<5&&!(d<5)","(c>0&&c<4)","pairEq([b,d],[0,7])"],"s":null,"sols":["4017"]},{"n":"გვერდით მდგომი + ლუწი და კენტი ადგილები","uses":["„ყველა“","ჯამი და სხვაობა"],"c":["კოდში არ არის ციფრები 3 და 8","მესამე ციფრის გვერდით დგას ციფრები 5 და 7","კენტ ადგილებზე მდგომი ციფრები ტოლია","პირველისა და მეორის ჯამია 8"],"p":["![a,b,c,d].includes(3)&&![a,b,c,d].includes(8)","pairEq([b,d],[5,7])","a===c","a+b===8"],"s":null,"sols":["1715"]}]},{"title":"კიბეები","new":"ზრდა, კლება, ნახტომები","explain":"„ციფრები იზრდება“: ყოველი შემდეგი ციფრი წინაზე დიდია (1 3 6 8). „ციფრები კლებულობს“: ყოველი შემდეგი პატარაა (9 6 4 1). „წინაზე 2-ით მეტია“: ყოველ ჯერზე +2 (1 3 5 7).","example":"„ყოველი ციფრი წინაზე 2-ით მეტია, პირველი 1“ → 1357","levels":[{"n":"ახალი: ზრდა","uses":["„არც… არც…“","მეტი და ნაკლები","ადგილის სიტყვები"],"c":["ყოველი შემდეგი ციფრი წინაზე დიდია","მეორე ციფრი არც 2-ია, არც 5","ბოლო ციფრი 3-სა და 7-ს შორისაა","მეორე ციფრის გვერდით დგას ციფრები 1 და 4"],"p":["a<b&&b<c&&c<d","b!==2&&b!==5","(d>3&&d<7)","pairEq([a,c],[1,4])"],"s":null,"sols":["1345","1346"]},{"n":"კლება","uses":["„გარდა“","მეტი და ნაკლები","ადგილის სიტყვები"],"c":["ყველა ციფრი ლუწია, გარდა მესამისა","ყოველი შემდეგი ციფრი წინაზე პატარაა","მეორე ციფრი ლუწია და 4-სა და 8-ს შორისაა","ბოლოდან მეორე ციფრი 0-სა და 3-ს შორისაა"],"p":["ev(a)&&ev(b)&&ev(d)&&!(ev(c))","a>b&&b>c&&c>d","ev(b)&&(b>4&&b<8)","(c>0&&c<3)"],"s":null,"sols":["8610"]},{"n":"ერთნაირი ნაბიჯი ზემოთ","uses":["ადგილის სიტყვები","მეტი და ნაკლები","„ყველა“"],"c":["ბოლოდან მეორე ციფრი 6-ზე ნაკლებია","ყოველი შემდეგი ციფრი წინაზე 1-ით მეტია","პირველი ციფრი ლუწია და 6-ზე ნაკლებია","კოდში არ არის ციფრები 0 და 9"],"p":["c<6","b===a+1&&c===b+1&&d===c+1","ev(a)&&a<6","![a,b,c,d].includes(0)&&![a,b,c,d].includes(9)"],"s":null,"sols":["2345"]},{"n":"ერთნაირი ნაბიჯი ქვემოთ","uses":["ადგილის სიტყვები","„არ არის“","„არც… არც…“"],"c":["ყოველი შემდეგი ციფრი წინაზე 1-ით ნაკლებია","ბოლოდან მეორე ციფრი 7-ზე ნაკლებია","მეორე ციფრი ლუწია და არ არის 6","პირველი ციფრი არც 3-ია, არც 7"],"p":["b===a-1&&c===b-1&&d===c-1","c<7","ev(b)&&b!==6","a!==3&&a!==7"],"s":null,"sols":["5432"]},{"n":"ზრდა","uses":["ადგილის სიტყვები","„არც… არც…“","ჯამი და სხვაობა"],"c":["მესამე ციფრის გვერდით დგას ციფრები 3 და 7","მესამე ციფრი არც 0-ია, არც 5","ბოლო ციფრი პირველისა და მესამის ჯამია","ყოველი შემდეგი ციფრი წინაზე დიდია"],"p":["pairEq([b,d],[3,7])","c!==0&&c!==5","d===a+c","a<b&&b<c&&c<d"],"s":null,"sols":["1367"]},{"n":"კლება","uses":["„ყველა“","ჯამი და სხვაობა","ადგილის სიტყვები"],"c":["ყოველი შემდეგი ციფრი წინაზე პატარაა","კოდში არ არის ციფრი 8","პირველისა და მესამის სხვაობაა 5","მესამე ციფრის გვერდით დგას ციფრები 1 და 4"],"p":["a>b&&b>c&&c>d","![a,b,c,d].includes(8)","Math.abs(a-c)===5","pairEq([b,d],[1,4])"],"s":null,"sols":["7421"]}]},{"title":"ორმაგი, ნახევარი, ნამრავლი","new":"2-ჯერ მეტი, ნახევარი, ნამრავლი","explain":"„2-ჯერ მეტი“ ნიშნავს, რომ ვამრავლებთ 2-ზე (3 → 6). „ნახევარი“ ნიშნავს, რომ ვყოფთ 2-ზე (8 → 4). „ნამრავლი“ ორი რიცხვის გამრავლების შედეგია (3 და 4-ის ნამრავლი = 12).","example":"„მეორე პირველზე 2-ჯერ მეტია, პირველი 3“ → 3 6 _ _","levels":[{"n":"ახალი: 2-ჯერ მეტი","uses":["ჯამი და სხვაობა","„ყველა“","„არც… არც…“"],"c":["მესამისა და ბოლოს ჯამია 13","კოდში არ არის ციფრები 3 და 9","ბოლო ციფრი პირველზე 2-ჯერ მეტია","მეორე ციფრი არ არის 5-ზე მეტი"],"p":["c+d===13","![a,b,c,d].includes(3)&&![a,b,c,d].includes(9)","d===2*a","b<=5"],"s":null,"sols":["4058","4158","4258","4458","4558"]},{"n":"ნახევარი","uses":["„არ არის“","ჯამი და სხვაობა","მომდევნო და წინა"],"c":["მეორე ციფრი არ არის 2","პირველისა და მესამის ჯამია 16","ბოლო ციფრი მესამეზე 3-ით ნაკლებია","მეორე ციფრი ბოლოს ნახევარია"],"p":["b!==2","a+c===16","d===c-3","2*b===d"],"s":null,"sols":["7396"]},{"n":"ნამრავლი რიცხვით","uses":["მეტი და ნაკლები","ადგილის სიტყვები"],"c":["შუაში დგას ციფრები 3 და 8","პირველისა და ბოლოს ნამრავლია 27","პირველი ციფრი კენტია და 1-სა და 5-ს შორისაა","ბოლოდან მეორე ციფრი ლუწია"],"p":["pairEq([b,c],[3,8])","a*d===27","odd(a)&&(a>1&&a<5)","ev(c)"],"s":null,"sols":["3389"]},{"n":"ნამრავლი ციფრად","uses":["მომდევნო და წინა","„ყველა“","ადგილის სიტყვები"],"c":["ბოლო ციფრი პირველზე 2-ით მეტია","მეორე ციფრი პირველისა და ბოლოს ნამრავლია","კოდში არ არის ციფრები 0 და 8","ბოლოდან მეორე ციფრი 2-ზე ნაკლებია"],"p":["d===a+2","b===a*d","![a,b,c,d].includes(0)&&![a,b,c,d].includes(8)","c<2"],"s":null,"sols":["1313"]},{"n":"2-ჯერ მეტი + ნახევარი","uses":["ადგილის სიტყვები","ჯამი და სხვაობა"],"c":["ბოლოსწინა ციფრი 0-სა და 4-ს შორისაა","პირველი ციფრი მესამეზე 2-ჯერ მეტია","ბოლო ციფრი მეორის ნახევარია","შუაში მდგომი ციფრების ჯამია 2"],"p":["(c>0&&c<4)","a===2*c","2*d===b","b+c===2"],"s":null,"sols":["4020"]},{"n":"ნახევარი + ნამრავლი რიცხვით","uses":["განმეორება","„არ არის“"],"c":["პირველი ციფრი მესამის ნახევარია","პირველისა და ბოლოს ნამრავლია 5","არცერთი ციფრი არ მეორდება","მეორე ციფრი 4-სა და 8-ს შორისაა და არ არის 7"],"p":["2*a===c","a*d===5","new Set([a,b,c,d]).size===4","(b>4&&b<8)&&b!==7"],"s":null,"sols":["1625"]}]},{"title":"უდიდესი და უმცირესი","new":"ყველაზე დიდი / პატარა კოდში","explain":"„კოდში ყველაზე დიდია“ ნიშნავს, რომ ეს ციფრი დანარჩენ სამ ციფრზე დიდია. „ყველაზე პატარა“ ნიშნავს, რომ დანარჩენ სამზე პატარაა. ჯერ იპოვე ცნობილი ციფრები და მერე შეადარე!","example":"4 2 _ 7 და მესამე ყველაზე დიდია → მესამე 8-ია ან 9","levels":[{"n":"ახალი: ყველაზე დიდი","uses":["მეტი და ნაკლები","ორმაგი და ნამრავლი","„არც… არც…“"],"c":["მეორე ციფრი კენტია და 4-სა და 7-ს შორისაა","პირველისა და ბოლოს ნამრავლია 6","პირველი ციფრი არც 2-ია, არც 3","მესამე ციფრი კოდში ყველაზე დიდია"],"p":["odd(b)&&(b>4&&b<7)","a*d===6","a!==2&&a!==3","c>Math.max(a,b,d)"],"s":null,"sols":["1576","1586","1596","6571","6581","6591"]},{"n":"ყველაზე პატარა","uses":["„არც… არც…“","მეტი და ნაკლები","ადგილის სიტყვები"],"c":["მეორე ციფრი არ არის 3-ზე მეტი","მეორე ციფრი ბოლოზე მეტია","პირველი ციფრი კოდში ყველაზე პატარაა","მეორე ციფრის გვერდით დგას ციფრები 1 და 2"],"p":["b<=3","b>d","a<Math.min(b,c,d)","pairEq([a,c],[1,2])"],"s":null,"sols":["1322"]},{"n":"ყველაზე პატარა + ყველაზე დიდი","uses":["ორმაგი და ნამრავლი","ადგილის სიტყვები"],"c":["ბოლო ციფრი პირველის ნახევარია","ბოლოსწინა ციფრი 1-სა და 4-ს შორისაა","ბოლო ციფრი კოდში ყველაზე პატარაა","პირველი ციფრი კოდში ყველაზე დიდია"],"p":["2*d===a","(c>1&&c<4)","d<Math.min(a,b,c)","a>Math.max(b,c,d)"],"s":null,"sols":["4332"]},{"n":"ყველაზე დიდი","uses":["ადგილის სიტყვები","მომდევნო და წინა","ჯამი და სხვაობა"],"c":["მეორე ციფრი კოდში ყველაზე დიდია","ბოლოდან მეორე ციფრი კენტია","ბოლო ციფრი მესამეზე 2-ით ნაკლებია","პირველისა და მესამის ჯამია 15"],"p":["b>Math.max(a,c,d)","odd(c)","d===c-2","a+c===15"],"s":null,"sols":["8975"]},{"n":"ყველაზე პატარა","uses":["ადგილის სიტყვები","ჯამი და სხვაობა","მეტი და ნაკლები"],"c":["ბოლოსწინა ციფრი 4-სა და 6-ს შორისაა","ბოლო ციფრი კოდში ყველაზე პატარაა","პირველისა და ბოლოს ჯამია 4","მეორე ციფრი 2-ზე ნაკლებია"],"p":["(c>4&&c<6)","d<Math.min(a,b,c)","a+d===4","b<2"],"s":null,"sols":["4150"]},{"n":"ყველაზე პატარა + ყველაზე დიდი","uses":["ორმაგი და ნამრავლი"],"c":["პირველისა და ბოლოს ნამრავლია 18","პირველი ციფრი კოდში ყველაზე პატარაა","შუაში დგას ციფრები 5 და 9","მესამე ციფრი კოდში ყველაზე დიდია"],"p":["a*d===18","a<Math.min(b,c,d)","pairEq([b,c],[5,9])","c>Math.max(a,b,d)"],"s":null,"sols":["3596"]}]},{"title":"დიდი ჯამები","new":"სამი და ოთხი ციფრის ჯამი","explain":"„ოთხივე ციფრის ჯამი“ ნიშნავს: პირველი + მეორე + მესამე + მეოთხე. „პირველი ორის ჯამი ბოლო ორის ჯამის ტოლია“: 2 + 5 = 3 + 4.","example":"ჯამი 10, ცნობილია 2, 4, 1 → ბოლო 10 − 7 = 3","levels":[{"n":"ახალი: ოთხის ჯამი","uses":["„არც… არც…“","მეტი და ნაკლები","განმეორება"],"c":["პირველი ციფრი არც 1-ია, არც 2, მაგრამ 0-სა და 5-ს შორისაა","ოთხივე ციფრის ჯამია 4","მესამე ციფრი ლუწია და 7-ზე ნაკლებია","ციფრი 0 კოდში ზუსტად ორჯერ გვხვდება"],"p":["a!==1&&a!==2&&a>0&&a<5","a+b+c+d===4","ev(c)&&c<7","[a,b,c,d].filter(v=>v===0).length===2"],"s":null,"sols":["3001","3100"]},{"n":"პირველი სამის ჯამი","uses":["მეტი და ნაკლები","ადგილის სიტყვები","„არც… არც…“"],"c":["პირველი ციფრი 4-სა და 6-ს შორისაა","მესამე ციფრის გვერდით დგას ციფრები 7 და 9","პირველი სამი ციფრის ჯამია 20","ბოლო ციფრი არც 5-ია, არც 9"],"p":["(a>4&&a<6)","pairEq([b,d],[7,9])","a+b+c===20","d!==5&&d!==9"],"s":null,"sols":["5967"]},{"n":"ბოლო სამის ჯამი","uses":["„ყველა“","უდიდესი და უმცირესი","ჯამი და სხვაობა"],"c":["კოდში არ არის ციფრები 0 და 7","მეორე ციფრი კოდში ყველაზე პატარაა","პირველისა და მეორის ჯამია 13","ბოლო სამი ციფრის ჯამია 23"],"p":["![a,b,c,d].includes(0)&&![a,b,c,d].includes(7)","b<Math.min(a,c,d)","a+b===13","b+c+d===23"],"s":null,"sols":["8599"]},{"n":"წყვილების ტოლობა","uses":["ორმაგი და ნამრავლი","ჯამი და სხვაობა","„არც… არც…“"],"c":["პირველი ორი ციფრის ჯამი ბოლო ორის ჯამის ტოლია","მესამისა და ბოლოს ნამრავლია 24","პირველისა და მესამის ჯამია 8","პირველი ციფრი არც 5-ია, არც 7, მაგრამ 3-სა და 8-ს შორისაა"],"p":["a+b===c+d","c*d===24","a+c===8","a!==5&&a!==7&&a>3&&a<8"],"s":null,"sols":["4646"]},{"n":"ოთხის ჯამი + პირველი სამის ჯამი","uses":["უდიდესი და უმცირესი","„არც… არც…“"],"c":["ოთხივე ციფრის ჯამია 18","პირველი ციფრი კოდში ყველაზე დიდია","პირველი სამი ციფრის ჯამია 12","მეორე ციფრი არც 4-ია, არც 6, მაგრამ 3-სა და 7-ს შორისაა"],"p":["a+b+c+d===18","a>Math.max(b,c,d)","a+b+c===12","b!==4&&b!==6&&b>3&&b<7"],"s":null,"sols":["7506"]},{"n":"ბოლო სამის ჯამი + წყვილების ტოლობა","uses":["„არც… არც…“","ორმაგი და ნამრავლი"],"c":["ბოლო სამი ციფრის ჯამია 9","მესამე ციფრი არც 3-ია, არც 4, მაგრამ 1-სა და 5-ს შორისაა","პირველი ორი ციფრის ჯამი ბოლო ორის ჯამის ტოლია","პირველისა და მეორის ნამრავლია 0"],"p":["b+c+d===9","c!==3&&c!==4&&c>1&&c<5","a+b===c+d","a*b===0"],"s":null,"sols":["9027"]}]},{"title":"ამბავი","new":"წაიკითხე და გაიგე","explain":"ჯერ ყურადღებით წაიკითხე ამბავი, მერე კი კითხვებს უპასუხე. ფრთხილად: ამბავში შეიძლება ზედმეტი რიცხვებიც ერიოს!","example":"„3 ვაშლი იყიდა, მერე კიდევ 4“ → სულ 7","levels":[{"n":"დაითვალე","c":["პირველი ციფრი: რამდენი ვაშლი იყიდა ნიკამ დილით?","მეორე ციფრი: სულ რამდენი ვაშლი იყიდა?","მესამე ციფრი: რამდენი ვაშლი აჩუქა დას?","მეოთხე ციფრი: რამდენი ვაშლი დარჩა ნიკას?"],"p":["((a===3))","((b===7))","((c===2))","((d===5))"],"s":"ნიკამ დილით 3 ვაშლი იყიდა. საღამოს კიდევ 4 ვაშლი იყიდა. 2 ვაშლი დას აჩუქა.","sols":["3725"],"uses":[]},{"n":"თანაბრად გაყოფა","c":["პირველი ციფრი: რომელ საათზე გაიღვიძა მარიამმა?","მეორე ციფრი: რამდენი გაკვეთილი ჰქონდა?","მესამე ციფრი: რამდენი კანფეტი მიიღო თითო მეგობარმა?","მეოთხე ციფრი: სულ რამდენი ფეხი აქვს კატებს?"],"p":["((a===7))","((b===3))","((c===3))","((d===8))"],"s":"მარიამმა დილის 7 საათზე გაიღვიძა. სკოლაში 3 გაკვეთილი ჰქონდა. დასვენებაზე 6 კანფეტი ორ მეგობარს თანაბრად გაუნაწილა. სახლში 2 კატა დახვდა.","sols":["7338"],"uses":[]},{"n":"ზედმეტი რიცხვი","c":["პირველი ციფრი: რამდენი ძაღლი ჰყავს ლუკას?","მეორე ციფრი: კიდევ რამდენი წიგნი დარჩა წასაკითხი?","მესამე ციფრი: სულ რამდენი ცხოველი ჰყავს ლუკას?","მეოთხე ციფრი: სულ რამდენი ფეხი აქვს ლუკას ძაღლებს?"],"p":["((a===2))","((b===1))","((c===3))","((d===8))"],"s":"ლუკა 9 წლისაა. მას ჰყავს 2 ძაღლი და 1 თუთიყუში. ბებიამ ლუკას 5 წიგნი აჩუქა. ლუკამ 4 წიგნი უკვე წაიკითხა.","sols":["2138"],"uses":[]},{"n":"ავიდა და ჩავიდა","c":["პირველი ციფრი: რამდენი ბავშვი იჯდა ავტობუსში თავიდან?","მეორე ციფრი: რამდენი დარჩა პირველი გაჩერების შემდეგ?","მესამე ციფრი: რამდენი იყო მეორე გაჩერების შემდეგ?","მეოთხე ციფრი: რამდენი დარჩა ბოლო გაჩერების შემდეგ?"],"p":["((a===6))","((b===4))","((c===7))","((d===0))"],"s":"ავტობუსში 6 ბავშვი იჯდა. პირველ გაჩერებაზე 2 ბავშვი ჩავიდა. მეორე გაჩერებაზე 3 ბავშვი ამოვიდა. ბოლო გაჩერებაზე ყველა ჩავიდა.","sols":["6470"],"uses":[]},{"n":"„დანარჩენი“","c":["პირველი ციფრი: რამდენი ლურჯი ბუშტი იყიდა ანამ?","მეორე ციფრი: რამდენი წითელი ბუშტი დარჩა?","მესამე ციფრი: რამდენი ბუშტი გასკდა?","მეოთხე ციფრი: სულ რამდენი ბუშტი დარჩა?"],"p":["((a===5))","((b===3))","((c===1))","((d===8))"],"s":"ანამ 9 ბუშტი იყიდა: 4 წითელი, დანარჩენი კი ლურჯი. ერთი წითელი ბუშტი გასკდა.","sols":["5318"],"uses":[]},{"n":"„რამდენით მეტი“","c":["პირველი ციფრი: რამდენი სპილო ნახეს ბავშვებმა?","მეორე ციფრი: რამდენი ბანანი შეხვდა თითო მაიმუნს?","მესამე ციფრი: სულ რამდენი ცხოველი ნახეს?","მეოთხე ციფრი: რამდენით მეტი ჟირაფი იყო, ვიდრე სპილო?"],"p":["((a===2))","((b===2))","((c===9))","((d===1))"],"s":"ზოოპარკში ბავშვებმა 3 ჟირაფი, 2 სპილო და 4 მაიმუნი ნახეს. მაიმუნებს 8 ბანანი მისცეს და მათ ბანანები თანაბრად გაინაწილეს.","sols":["2291"],"uses":[]},{"n":"ფერები და რაოდენობა","c":["პირველი ციფრი: რამდენი მწვანე ფანქარი აქვს თამარს?","მეორე ციფრი: რამდენი წითელი ფანქარი დარჩა?","მესამე ციფრი: სულ რამდენი ფანქარი აქვს ახლა?","მეოთხე ციფრი: რამდენი სხვადასხვა ფერის ფანქარი აქვს?"],"p":["((a===3))","((b===3))","((c===7))","((d===3))"],"s":"თამარს ჰქონდა 5 წითელი და 3 მწვანე ფანქარი. 2 წითელი ფანქარი გატყდა და თამარმა გადააგდო. ძმამ კი 1 ლურჯი ფანქარი აჩუქა.","sols":["3373"],"uses":[]}]},{"title":"თუ… მაშინ…","new":"თუ… თუ არა და…","explain":"„თუ“ პირობას ნიშნავს. ჯერ შეამოწმე, მართალია თუ არა ის, რაც „თუ“-ს შემდეგ წერია. თუ მართალია, აიღე პირველი პასუხი. თუ არა, აიღე მეორე.","example":"„თუ პირველი ლუწია, მეორე 1-ია, თუ არა და 9“. პირველი 4 → მეორე 1","levels":[{"n":"ახალი: თუ… მაშინ…","uses":["დიდი ჯამები","„არც… არც…“","მეტი და ნაკლები"],"c":["ბოლო სამი ციფრის ჯამია 8","ბოლო ციფრი არც 0-ია, არც 6","მეორე ციფრი 4-ზე მეტია","თუ მესამე ციფრი 6-ზე მეტია, პირველი ციფრი არის 0, თუ არა და 3"],"p":["b+c+d===8","d!==0&&d!==6","b>4","a===(c>6?0:3)"],"s":null,"sols":["3503","3512","3521","3602","3611","3701"]},{"n":"თუ… მაშინ…","uses":["დიდი ჯამები","„გარდა“","„არ არის“"],"c":["ბოლო სამი ციფრის ჯამია 20","ყველა ციფრი 3-ზე მეტია, გარდა მესამისა","თუ მეორე ციფრი კენტია, პირველი ციფრი არის 8, თუ არა და 0","ბოლო ციფრი პირველის ტოლი არ არის"],"p":["b+c+d===20","a>3&&b>3&&d>3&&!(c>3)","a===(odd(b)?8:0)","d!==a"],"s":null,"sols":["8929"]},{"n":"თუ… მაშინ…","uses":["დიდი ჯამები","„არც… არც…“","„ყველა“"],"c":["პირველი სამი ციფრის ჯამია 17","პირველი ციფრი არც 4-ია, არც 6, მაგრამ 2-სა და 7-ს შორისაა","ყველა ციფრი 8-ზე ნაკლებია","თუ ბოლო ციფრი 6-ზე მეტია, პირველი ციფრი არის 3, თუ არა და 4"],"p":["a+b+c===17","a!==4&&a!==6&&a>2&&a<7","Math.max(a,b,c,d)<8","a===(d>6?3:4)"],"s":null,"sols":["3777"]},{"n":"თუ… მაშინ…","uses":["„არც… არც…“","დიდი ჯამები"],"c":["პირველი ციფრი არც 2-ია, არც 5, მაგრამ 1-სა და 6-ს შორისაა","თუ მესამე ციფრი 6-ზე მეტია, მეორე ციფრი არის 6, თუ არა და 7","თუ მესამე ციფრი 6-ზე მეტია, ბოლო ციფრი არის 3, თუ არა და 2","პირველი სამი ციფრის ჯამია 19"],"p":["a!==2&&a!==5&&a>1&&a<6","b===(c>6?6:7)","d===(c>6?3:2)","a+b+c===19"],"s":null,"sols":["4693"]},{"n":"თუ… მაშინ…","uses":["დიდი ჯამები","მომდევნო და წინა"],"c":["პირველი სამი ციფრის ჯამია 17","მეორე ციფრი მესამეზე 2-ით ნაკლებია","თუ პირველი ციფრი 4-ზე მეტია, ბოლო ციფრი არის 0, თუ არა და 2","თუ ბოლო ციფრი 4-ზე მეტია, მეორე ციფრი არის 5, თუ არა და 6"],"p":["a+b+c===17","b===c-2","d===(a>4?0:2)","b===(d>4?5:6)"],"s":null,"sols":["3682"]},{"n":"თუ… მაშინ…","uses":["დიდი ჯამები","„არც… არც…“"],"c":["პირველი სამი ციფრის ჯამია 18","თუ ბოლო ციფრი 3-ზე მეტია, მეორე ციფრი არის 3, თუ არა და 7","ბოლო ციფრი არც 4-ია, არც 5, მაგრამ 2-სა და 7-ს შორისაა","თუ პირველი ციფრი 4-ზე მეტია, მესამე ციფრი არის 2, თუ არა და 3"],"p":["a+b+c===18","b===(d>3?3:7)","d!==4&&d!==5&&d>2&&d<7","c===(a>4?2:3)"],"s":null,"sols":["9723"]}]},{"title":"ციფრების ფორმა","new":"თავდაყირა ციფრი","explain":"თუ ციფრს თავდაყირა დააყენებ, 6 იქცევა 9-ად, 9 კი 6-ად. 0, 1 და 8 იგივე რჩება. 8 ორი წრისგან შედგება, 0 მთლიანად მრგვალია, 1 კი ჯოხს ჰგავს.","example":"6 თავდაყირა → 9","levels":[{"n":"ახალი: თავდაყირა ციფრი","uses":["თუ… მაშინ…","„არც… არც…“","ორმაგი და ნამრავლი"],"c":["თუ მესამე ციფრი 6-ზე მეტია, მეორე ციფრი არის 6, თუ არა და 8","პირველი ციფრი მეორე ციფრია, ოღონდ თავდაყირა","მესამე ციფრი არც 3-ია, არც 8","ბოლო ციფრი პირველის ნახევარია"],"p":["b===(c>6?6:8)","a===(UP[b]??-1)","c!==3&&c!==8","2*d===a"],"s":null,"sols":["8804","8814","8824","8844","8854","8864"]},{"n":"თავდაყირა რომ დააყენო","uses":["ორმაგი და ნამრავლი","თუ… მაშინ…","„არც… არც…“"],"c":["მესამისა და ბოლოს ნამრავლია 36","თუ მესამე ციფრი 6-ზე მეტია, პირველი ციფრი არის 9, თუ არა და 0","პირველი ციფრი არც 0-ია, არც 4","მეორე ციფრი თავდაყირა რომ დააყენო, 6 გამოვა"],"p":["c*d===36","a===(c>6?9:0)","a!==0&&a!==4","(UP[b]??-1)===6"],"s":null,"sols":["9994"]},{"n":"ციფრის ფორმა","uses":["თუ… მაშინ…","„არ არის“","ჯამი და სხვაობა"],"c":["თუ ბოლო ციფრი 5-ზე მეტია, მეორე ციფრი არის 1, თუ არა და 5","ბოლო ციფრი მთლიანად მრგვალია","მესამე ციფრი 8-ზე ნაკლებია და არ არის 7","პირველისა და მესამის ჯამია 15"],"p":["b===(d>5?1:5)","d===0","c<8&&c!==7","a+c===15"],"s":null,"sols":["9560"]},{"n":"თავდაყირაც იგივე","uses":["მეტი და ნაკლები","მომდევნო და წინა","თუ… მაშინ…"],"c":["მესამე ციფრი თავდაყირაც იგივე რჩება","მესამე ციფრი მეორესა და ბოლოს შორისაა","ბოლო ციფრი პირველზე 3-ით ნაკლებია","თუ მესამე ციფრი 5-ზე მეტია, მეორე ციფრი არის 2, თუ არა და 3"],"p":["(UP[c]??-1)===c","(c>Math.min(b,d)&&c<Math.max(b,d))","d===a-3","b===(c>5?2:3)"],"s":null,"sols":["3310"]},{"n":"ციფრის ფორმა + თავდაყირა ციფრი","uses":["მეტი და ნაკლები","თუ… მაშინ…"],"c":["მესამე ციფრი მთლიანად მრგვალია","მეორე ციფრი პირველი ციფრია, ოღონდ თავდაყირა","ბოლო ციფრი 7-სა და 9-ს შორისაა","თუ პირველი ციფრი ლუწია, მეორე ციფრი არის 2, თუ არა და 6"],"p":["c===0","b===(UP[a]??-1)","(d>7&&d<9)","b===(ev(a)?2:6)"],"s":null,"sols":["9608"]},{"n":"თავდაყირა რომ დააყენო + თავდაყირაც იგივე","uses":["ადგილის სიტყვები","თუ… მაშინ…"],"c":["მეორე ციფრის გვერდით დგას ციფრები 7 და 8","ბოლო ციფრი თავდაყირა რომ დააყენო, 6 გამოვა","თუ მესამე ციფრი ლუწია, მეორე ციფრი არის 8, თუ არა და 5","მეორე ციფრი თავდაყირაც იგივე რჩება"],"p":["pairEq([a,c],[7,8])","(UP[d]??-1)===6","b===(ev(c)?8:5)","(UP[b]??-1)===b"],"s":null,"sols":["7889"]}]}];
var SEEDS = {"0-2":0,"0-3":0,"0-4":0,"0-5":0,"0-6":0,"0-7":0,"0-8":1,"0-9":0,"1-2":13,"1-3":22,"1-4":20,"1-5":5,"1-6":5,"1-7":13,"1-8":2,"1-9":5,"2-2":0,"2-3":2,"2-4":3,"2-5":4,"2-6":0,"2-7":6,"2-8":0,"2-9":2,"3-2":8,"3-3":20,"3-4":3,"3-5":8,"3-6":15,"3-7":2,"3-8":6,"3-9":45,"4-2":14,"4-3":11,"4-4":10,"4-5":95,"4-6":48,"4-7":70,"4-8":18,"4-9":127,"5-2":373,"5-3":159,"5-4":197,"5-5":81,"5-6":7,"5-7":88,"5-8":38,"5-9":55,"6-2":61,"6-3":16,"6-4":25,"6-5":63,"6-6":90,"6-7":32,"6-8":20,"6-9":40,"7-2":0,"7-3":2,"7-4":22,"7-5":3,"7-6":0,"7-7":7,"7-8":67,"7-9":18,"8-2":135,"8-3":24,"8-4":41,"8-5":20,"8-6":16,"8-7":55,"8-8":1,"8-9":4,"9-0":4,"9-1":17,"9-2":112,"9-3":34,"9-4":75,"9-5":109,"9-6":63,"9-7":42,"9-8":64,"9-9":18,"10-0":23,"10-1":56,"10-2":26,"10-3":28,"10-4":61,"10-5":114,"10-6":39,"10-7":48,"10-8":56,"10-9":17,"11-2":33,"11-3":12,"11-4":2,"11-5":13,"11-6":13,"11-7":16,"11-8":22,"11-9":25,"12-2":9,"12-3":35,"12-4":17,"12-5":23,"12-6":13,"12-7":13,"12-8":33,"12-9":15,"13-2":8,"13-3":0,"13-4":12,"13-5":32,"13-6":31,"13-7":0,"13-8":5,"13-9":7,"14-2":11,"14-3":61,"14-4":6,"14-5":12,"14-6":14,"14-7":9,"14-8":0,"14-9":153,"15-2":1,"15-3":0,"15-4":11,"15-5":0,"15-6":12,"15-7":7,"15-8":0,"15-9":18,"16-2":63,"16-3":4,"16-4":43,"16-5":39,"16-6":34,"16-7":13,"16-8":14,"16-9":11,"17-2":9,"17-3":9,"17-4":5,"17-5":27,"17-6":0,"17-7":6,"17-8":3,"17-9":17,"18-7":1,"18-8":25,"18-9":3,"19-2":17,"19-3":31,"19-4":8,"19-5":6,"19-6":17,"19-7":4,"19-8":9,"19-9":43,"20-2":11,"20-3":4,"20-4":0,"20-5":0,"20-6":2,"20-7":9,"20-8":3,"20-9":34,"21-0":2,"21-1":21,"21-2":17,"21-3":15,"21-4":14,"21-5":16,"21-6":16,"21-7":21,"21-8":2,"21-9":10};
// ═══════════ კოდის გამტეხი — ძრავი (სახლები, ოპერატორები, კარების გენერატორი) ═══════════
// DATA (ხელით დაწერილი დონეები) მოდის ცალკე. ყველაფერი დეტერმინისტულია: ერთი და იგივე კარი ყოველთვის ერთნაირია.
var CB = (function () {
    var odd = function (x) { return x % 2 === 1; }, ev = function (x) { return x % 2 === 0; };
    var UP = { 6: 9, 9: 6, 0: 0, 1: 1, 8: 8 };
    var pairEq = function (p, q) { p = p.slice().sort(); q = q.slice().sort(); return p[0] === q[0] && p[1] === q[1]; };

    // ── სახლები: [სახელი, ორიგინალი თემის ინდექსი DATA-ში ან null] ──
    var HOUSES = [
        { name: 'ჩემი სახლი',            t: 0,  prize: '🧸', th: { wall: '#FFE8D6', door: '#E63946', door2: '#B52A35', frame: '#FFFDF5', shape: 'rect', tex: 'panels' } },
        { name: 'სოფლის სახლი',          t: 1,  prize: '🐓', th: { wall: '#EFE6D6', door: '#B07A47', door2: '#8A5A2E', frame: '#8D6E4E', shape: 'arch', tex: 'planks', deco: 'village' } },
        { name: 'ტყის ქოხი',             t: 2,  prize: '🍄', th: { wall: '#D8F3DC', door: '#9C6644', door2: '#6B4226', frame: '#5C3B1E', shape: 'arch', tex: 'planks' } },
        { name: 'ფეხბურთელის სახლი',     t: 3,  prize: '⚽', th: { wall: '#DDF2E1', door: '#2F7BF5', door2: '#1F5FCC', frame: '#FFFFFF', shape: 'rect', tex: 'plain', deco: 'football' } },
        { name: 'მათემატიკის კლასი',      t: 4,  prize: '📐', th: { wall: '#FFF6E5', door: '#E9C79A', door2: '#D4A86E', frame: '#6D4C41', shape: 'rect', tex: 'plain', deco: 'teacher' } },
        { name: 'ტკბილეულის მაღაზია',     t: 5,  prize: '🍭', th: { wall: '#FFF0F6', door: '#FF8FAB', door2: '#FFE0EA', frame: '#FB6F92', shape: 'rect', tex: 'stripes' } },
        { name: 'ზოოპარკის ჭიშკარი',      t: 6,  prize: '🦁', th: { wall: '#FEFAE0', door: '#E9EDC9', door2: '#3A5A40', frame: '#588157', shape: 'rect', tex: 'bars' } },
        { name: 'ფერმის ბეღელი',          t: 7,  prize: '🐮', th: { wall: '#D9ED92', door: '#C1121F', door2: '#8D0801', frame: '#FDF0D5', shape: 'rect', tex: 'barn' } },
        { name: 'სახლი ხეზე',             t: 8,  prize: '🐿️', th: { wall: '#CAF0F8', door: '#BC6C25', door2: '#8A4F1B', frame: '#606C38', shape: 'arch', tex: 'planks' } },
        { name: 'ფეხბურთის სტადიონი',     t: 'count',  prize: '🏆', th: { wall: '#CFE6FF', door: '#8FD19A', door2: '#63B26F', frame: '#FFFFFF', shape: 'goal', tex: 'net', deco: 'stadium' } },
        { name: 'ცირკის კარავი',          t: 'either', prize: '🎪', th: { wall: '#FDF0D5', door: '#E63946', door2: '#FFFDF5', frame: '#FFB703', shape: 'arch', tex: 'stripes' } },
        { name: 'ყინულის სასახლე',        t: 9,  prize: '⛄', th: { wall: '#E0FBFC', door: '#A9E5F2', door2: '#48CAE4', frame: '#FFFFFF', shape: 'arch', tex: 'ice' } },
        { name: 'საიდუმლო ოთახი',         t: 10, prize: '🕯️', th: { wall: '#4A3B66', door: '#8A5A2E', door2: '#5A3B22', frame: '#5A3B22', shape: 'rect', tex: 'books', dark: true } },
        { name: 'მატარებლის ვაგონი',      t: 11, prize: '🚂', th: { wall: '#E9EDC9', door: '#D62828', door2: '#A4161A', frame: '#003049', shape: 'rect', tex: 'metal' } },
        { name: 'შუქურა',                 t: 12, prize: '🗼', th: { wall: '#CDEBFF', door: '#FFFFFF', door2: '#E63946', frame: '#E63946', shape: 'arch', tex: 'hstripes' } },
        { name: 'რობოტების ქარხანა',      t: 13, prize: '🤖', th: { wall: '#E5E5E5', door: '#ADB5BD', door2: '#6C757D', frame: '#FFB703', shape: 'rect', tex: 'metal' } },
        { name: 'სუპერგმირების სახლი',    t: 14, prize: '🦸', th: { wall: '#FFE3E3', door: '#2F4BC4', door2: '#C2372B', frame: '#FFC93C', shape: 'rect', tex: 'hero' } },
        { name: 'მეკობრის სახლი',         t: 15, prize: '💰', th: { wall: '#F6E7C1', door: '#A0522D', door2: '#7B3F1D', frame: '#E0A800', shape: 'rect', tex: 'bands' } },
        { name: 'ზღაპრების ბიბლიოთეკა',   t: 16, prize: '📚', th: { wall: '#F1E3D3', door: '#6F4518', door2: '#4A2E12', frame: '#A68A64', shape: 'arch', tex: 'panels' } },
        { name: 'ჯადოქრის კოშკი',         t: 17, prize: '🔮', th: { wall: '#3C096C', door: '#7B2CBF', door2: '#5A189A', frame: '#C77DFF', shape: 'arch', tex: 'stars', dark: true } },
        { name: 'დრაკონის გამოქვაბული',   t: 18, prize: '🐉', th: { wall: '#4A3530', door: '#9D0208', door2: '#6A040F', frame: '#7F5539', shape: 'arch', tex: 'hplanks', dark: true } },
        { name: 'დირექტორის სახლი',       t: 'lock',   prize: '🎓', th: { wall: '#F3EDE2', door: '#5A2E1A', door2: '#3E1F11', frame: '#E0A800', shape: 'arch', tex: 'gold', deco: 'director' } },
    ];
    var DOORS = 10, AUTO_HOUSES = 6;   // 1–6 სახლში შესრულებული პირობა თავისით ინიშნება (ბოსის გარდა)

    var NEW_TOPICS = {
        count:  { title: 'დათვლა', 'new': 'ზუსტად რამდენი', explain: '„ზუსტად ორი ლუწი ციფრია“ ნიშნავს, რომ ოთხი ციფრიდან ორი ლუწია, დანარჩენი ორი კი კენტი. „ზუსტად ერთი ციფრია 5-ზე მეტი“ — მხოლოდ ერთი, არც მეტი, არც ნაკლები.', example: '„ზუსტად ორი ლუწი ციფრია“ → 2 7 4 1' },
        either: { title: '„ან… ან…“', 'new': 'ან… ან…, ორივე ერთად არა', explain: '„ან 2-ია, ან 7“ ნიშნავს, რომ ციფრი ამ ორიდან ერთ-ერთია. „ან მეორეა ლუწი, ან მესამე“ ნიშნავს, რომ ზუსტად ერთი მათგანია ლუწი — ორივე ერთად არა!', example: '„პირველი ციფრი ან 2-ია, ან 7“ → 2 _ _ _ ან 7 _ _ _' },
        lock:   { title: 'საკეტის კლასიკა', 'new': 'სწორ ადგილზე / სხვა ადგილზე', explain: 'ვიღაცამ უკვე სცადა კოდები. „1 ციფრი სწორ ადგილზეა“ ნიშნავს, რომ ერთი ციფრი ზუსტად იქ დგას. „1 ციფრი კოდშია, მაგრამ სხვა ადგილზე“ — ციფრი სწორია, ადგილი არა. „არცერთი ციფრი არ არის სწორი“ — ამ ციფრებს ამოშლი.', example: '„5 2 7 1 — არცერთი ციფრი არ არის სწორი“ → 5, 2, 7 და 1 კოდში არ არის' },
    };
    function topicInfo(h) {
        var t = HOUSES[h].t;
        if (typeof t === 'number') { var D = DATA[t]; return { title: D.title, 'new': D['new'], explain: D.explain, example: D.example }; }
        return NEW_TOPICS[t];
    }

    // ── RNG ──
    function rng(seed) { var a = seed >>> 0; return function () { a = (a + 0x6D2B79F5) >>> 0; var t = a; t = Math.imul(t ^ (t >>> 15), t | 1); t ^= t + Math.imul(t ^ (t >>> 7), t | 61); return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }
    function ri(R, a, b) { return a + Math.floor(R() * (b - a + 1)); }
    function pick(R, arr) { return arr[Math.floor(R() * arr.length)]; }
    function shuf(R, a) { a = a.slice(); for (var i = a.length - 1; i > 0; i--) { var j = Math.floor(R() * (i + 1)); var t = a[i]; a[i] = a[j]; a[j] = t; } return a; }

    // ── ქართული ფორმები ──
    var NOM = ['პირველი', 'მეორე', 'მესამე', 'ბოლო'], GEN = ['პირველის', 'მეორის', 'მესამის', 'ბოლოს'],
        DAT = ['პირველზე', 'მეორეზე', 'მესამეზე', 'ბოლოზე'], GSA = ['პირველისა', 'მეორისა', 'მესამისა', 'ბოლოსი'];
    var CNT = ['არცერთი', 'ერთი', 'ორი', 'სამი', 'ოთხი'];
    var cap = function (s) { return s; };   // ქართულში დიდი ასო (მთავრული) არ გვინდა
    function two(R) { var i = ri(R, 0, 3), j; do { j = ri(R, 0, 3); } while (j === i); return [i, j]; }
    function twoSorted(R) { var p = two(R); return p[0] < p[1] ? p : [p[1], p[0]]; }

    // ── ოპერატორები: h = რომელ სახლში ჩნდება; mk(q, R) → {t, f} ან null ──
    var OPS = [];
    function op(h, id, mk, o) { OPS.push(Object.assign({ h: h, id: id, mk: mk, max: 1 }, o || {})); }
    // 0 ადგილები
    op(0, 'pos', function (q, R) { var i = ri(R, 0, 3), v = q[i]; return { t: NOM[i] + ' ციფრი არის ' + v, f: function (x) { return x[i] === v; } }; }, { simple: true, max: 3 });
    op(0, 'mid', function (q, R) { var p = shuf(R, [q[1], q[2]]); return { t: 'შუაში დგას ციფრები ' + p[0] + ' და ' + p[1], f: function (x) { return pairEq([x[1], x[2]], p); } }; }, { simple: true });
    op(0, 'edge', function (q, R) { var p = shuf(R, [q[0], q[3]]); return { t: 'ნაპირებში დგას ციფრები ' + p[0] + ' და ' + p[1], f: function (x) { return pairEq([x[0], x[3]], p); } }; }, { simple: true });
    // 1 ტოლობა
    op(1, 'eqpos', function (q, R) { var p = two(R), i = p[0], j = p[1]; if (q[i] !== q[j]) return null; return { t: NOM[i] + ' ციფრი ' + GEN[j] + ' ტოლია', f: function (x) { return x[i] === x[j]; } }; });
    op(1, 'midsame', function (q) { if (q[1] !== q[2]) return null; return { t: 'შუაში ორივე ციფრი ერთნაირია', f: function (x) { return x[1] === x[2]; } }; });
    op(1, 'edgesame', function (q) { if (q[0] !== q[3]) return null; return { t: 'ნაპირებში ორივე ციფრი ერთნაირია', f: function (x) { return x[0] === x[3]; } }; });
    // 2 მომდევნო / წინა / k-ით
    op(2, 'next', function (q, R) { var p = two(R), i = p[0], j = p[1]; if (q[i] === q[j] + 1) return { t: NOM[i] + ' ციფრი ' + GEN[j] + ' მომდევნო რიცხვია', f: function (x) { return x[i] === x[j] + 1; } }; if (q[i] === q[j] - 1) return { t: NOM[i] + ' ციფრი ' + GEN[j] + ' წინა რიცხვია', f: function (x) { return x[i] === x[j] - 1; } }; return null; });
    op(2, 'kdiff', function (q, R) { var p = two(R), i = p[0], j = p[1], k = q[i] - q[j]; if (Math.abs(k) < 2) return null; if (k > 0) return { t: NOM[i] + ' ციფრი ' + DAT[j] + ' ' + k + '-ით მეტია', f: function (x) { return x[i] === x[j] + k; } }; k = -k; return { t: NOM[i] + ' ციფრი ' + DAT[j] + ' ' + k + '-ით ნაკლებია', f: function (x) { return x[i] === x[j] - k; } }; });
    // 3 კენტი / ლუწი
    op(3, 'par', function (q, R) { var i = ri(R, 0, 3), e = ev(q[i]); return { t: NOM[i] + ' ციფრი ' + (e ? 'ლუწია' : 'კენტია'), f: function (x) { return ev(x[i]) === e; } }; }, { max: 2, simple: true });
    // 4 მეტი / ნაკლები / შორის
    op(4, 'gt', function (q, R) { var i = ri(R, 0, 3); if (q[i] < 1) return null; var n = Math.max(0, q[i] - ri(R, 1, 3)); return { t: NOM[i] + ' ციფრი ' + n + '-ზე მეტია', f: function (x) { return x[i] > n; } }; });
    op(4, 'lt', function (q, R) { var i = ri(R, 0, 3); if (q[i] > 8) return null; var n = Math.min(9, q[i] + ri(R, 1, 3)); return { t: NOM[i] + ' ციფრი ' + n + '-ზე ნაკლებია', f: function (x) { return x[i] < n; } }; });
    op(4, 'betw', function (q, R) { var i = ri(R, 0, 3), lo = q[i] - ri(R, 1, 2), hi = q[i] + ri(R, 1, 2); if (lo < 0 || hi > 9) return null; return { t: NOM[i] + ' ციფრი ' + lo + '-სა და ' + hi + '-ს შორისაა', f: function (x) { return x[i] > lo && x[i] < hi; } }; });
    op(4, 'posgt', function (q, R) { var p = two(R), i = p[0], j = p[1]; if (q[i] === q[j]) return null; var g = q[i] > q[j]; return { t: NOM[i] + ' ციფრი ' + DAT[j] + ' ' + (g ? 'მეტია' : 'ნაკლებია'), f: function (x) { return g ? x[i] > x[j] : x[i] < x[j]; } }; });
    // 5 „არ არის“
    op(5, 'neq', function (q, R) { var i = ri(R, 0, 3), n; do { n = ri(R, 0, 9); } while (n === q[i]); return { t: NOM[i] + ' ციფრი არ არის ' + n, f: function (x) { return x[i] !== n; } }; });
    op(5, 'notpar', function (q, R) { var i = ri(R, 0, 3), e = ev(q[i]); return { t: NOM[i] + ' ციფრი არ არის ' + (e ? 'კენტი' : 'ლუწი'), f: function (x) { return ev(x[i]) === e; } }; });
    op(5, 'neqpos', function (q, R) { var p = two(R), i = p[0], j = p[1]; if (q[i] === q[j]) return null; return { t: NOM[i] + ' ციფრი ' + GEN[j] + ' ტოლი არ არის', f: function (x) { return x[i] !== x[j]; } }; });
    op(5, 'nonepar', function (q) { if (q.every(odd)) return { t: 'არცერთი ციფრი არ არის ლუწი', f: function (x) { return x.every(odd); } }; if (q.every(ev)) return { t: 'არცერთი ციფრი არ არის კენტი', f: function (x) { return x.every(ev); } }; return null; });
    // 6 ძნელი „არ არის“
    op(6, 'notless', function (q, R) { var i = ri(R, 0, 3), n = q[i] - ri(R, 0, 2); if (n < 1) return null; return { t: NOM[i] + ' ციფრი არ არის ' + n + '-ზე ნაკლები', f: function (x) { return x[i] >= n; } }; });
    op(6, 'notmore', function (q, R) { var i = ri(R, 0, 3), n = q[i] + ri(R, 0, 2); if (n > 8) return null; return { t: NOM[i] + ' ციფრი არ არის ' + n + '-ზე მეტი', f: function (x) { return x[i] <= n; } }; });
    op(6, 'neither', function (q, R) { var i = ri(R, 0, 3), lo = q[i] - ri(R, 1, 3), hi = q[i] + ri(R, 1, 3); if (lo < 0 || hi > 9 || hi - lo < 3) return null; var opts = []; for (var v = lo + 1; v < hi; v++) if (v !== q[i]) opts.push(v); if (opts.length < 2) return null; var p = shuf(R, opts).slice(0, 2).sort(); return { t: NOM[i] + ' ციფრი არც ' + p[0] + '-ია, არც ' + p[1] + ', მაგრამ ' + lo + '-სა და ' + hi + '-ს შორისაა', f: function (x) { return x[i] > lo && x[i] < hi && x[i] !== p[0] && x[i] !== p[1]; } }; });
    // 7 ჯამი / სხვაობა
    op(7, 'sum2', function (q, R) { var p = twoSorted(R), i = p[0], j = p[1], s = q[i] + q[j]; return { t: cap(GSA[i]) + ' და ' + GEN[j] + ' ჯამია ' + s, f: function (x) { return x[i] + x[j] === s; } }; });
    op(7, 'diff2', function (q, R) { var p = twoSorted(R), i = p[0], j = p[1], s = Math.abs(q[i] - q[j]); if (s < 2) return null; return { t: cap(GSA[i]) + ' და ' + GEN[j] + ' სხვაობაა ' + s, f: function (x) { return Math.abs(x[i] - x[j]) === s; } }; });
    op(7, 'issum', function (q, R) { var i = ri(R, 0, 3), r = [0, 1, 2, 3].filter(function (k) { return k !== i; }), p = shuf(R, r).slice(0, 2).sort(), j = p[0], k = p[1]; if (q[i] !== q[j] + q[k]) return null; return { t: NOM[i] + ' ციფრი ' + GSA[j] + ' და ' + GEN[k] + ' ჯამია', f: function (x) { return x[i] === x[j] + x[k]; } }; });
    // 8 „ყველა“
    op(8, 'allpar', function (q) { if (q.every(ev)) return { t: 'ყველა ციფრი ლუწია', f: function (x) { return x.every(ev); } }; if (q.every(odd)) return { t: 'ყველა ციფრი კენტია', f: function (x) { return x.every(odd); } }; return null; });
    op(8, 'allgt', function (q, R) { var m = Math.min.apply(null, q) - ri(R, 1, 2); if (m < 1) return null; return { t: 'ყველა ციფრი ' + m + '-ზე მეტია', f: function (x) { return Math.min.apply(null, x) > m; } }; });
    op(8, 'alllt', function (q, R) { var m = Math.max.apply(null, q) + ri(R, 1, 2); if (m > 8) return null; return { t: 'ყველა ციფრი ' + m + '-ზე ნაკლებია', f: function (x) { return Math.max.apply(null, x) < m; } }; });
    op(8, 'notin', function (q, R) { var out = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9].filter(function (v) { return q.indexOf(v) < 0; }); var p = shuf(R, out).slice(0, ri(R, 1, 2)).sort(); if (!p.length) return null; return { t: p.length === 1 ? 'კოდში არ არის ციფრი ' + p[0] : 'კოდში არ არის ციფრები ' + p[0] + ' და ' + p[1], f: function (x) { return p.every(function (v) { return x.indexOf(v) < 0; }); } }; });
    // 9 დათვლა (ახალი)
    op(9, 'cntpar', function (q, R) { var e = R() < .5, k = q.filter(e ? ev : odd).length; if (k < 1 || k > 3) return null; return { t: 'კოდში ზუსტად ' + CNT[k] + ' ' + (e ? 'ლუწი' : 'კენტი') + ' ციფრია', f: function (x) { return x.filter(e ? ev : odd).length === k; } }; });
    op(9, 'cntgt', function (q, R) { var n = ri(R, 2, 7), k = q.filter(function (v) { return v > n; }).length; if (k < 1 || k > 3) return null; return { t: 'კოდში ზუსტად ' + CNT[k] + ' ციფრია ' + n + '-ზე მეტი', f: function (x) { return x.filter(function (v) { return v > n; }).length === k; } }; });
    op(9, 'cntlt', function (q, R) { var n = ri(R, 2, 7), k = q.filter(function (v) { return v < n; }).length; if (k < 1 || k > 3) return null; return { t: 'კოდში ზუსტად ' + CNT[k] + ' ციფრია ' + n + '-ზე ნაკლები', f: function (x) { return x.filter(function (v) { return v < n; }).length === k; } }; });
    // 10 „ან… ან…“ (ახალი)
    op(10, 'eitherv', function (q, R) { var i = ri(R, 0, 3), o; do { o = ri(R, 0, 9); } while (o === q[i]); var p = shuf(R, [q[i], o]); return { t: NOM[i] + ' ციფრი ან ' + p[0] + '-ია, ან ' + p[1], f: function (x) { return x[i] === p[0] || x[i] === p[1]; } }; }, { max: 2 });
    op(10, 'xorpar', function (q, R) { var p = twoSorted(R), i = p[0], j = p[1]; if (ev(q[i]) === ev(q[j])) return null; var e = R() < .5, w = e ? 'ლუწი' : 'კენტი'; var tst = e ? ev : odd; return { t: 'ან ' + NOM[i] + ' ციფრია ' + w + ', ან ' + NOM[j] + ' — ორივე ერთად არა', f: function (x) { return tst(x[i]) !== tst(x[j]); } }; });
    // 11 „გარდა“
    op(11, 'parexc', function (q, R) { var i = ri(R, 0, 3), e = ev(q[(i + 1) % 4]); for (var k = 0; k < 4; k++) if (k !== i && ev(q[k]) !== e) return null; if (ev(q[i]) === e) return null; return { t: 'ყველა ციფრი ' + (e ? 'ლუწია' : 'კენტია') + ', გარდა ' + GSA[i], f: function (x) { for (var k = 0; k < 4; k++) { if (k === i ? ev(x[k]) === e : ev(x[k]) !== e) return false; } return true; } }; });
    op(11, 'gtexc', function (q, R) { var i = ri(R, 0, 3), others = q.filter(function (v, k) { return k !== i; }), n = Math.min.apply(null, others) - ri(R, 1, 2); if (n < 1 || q[i] > n) return null; return { t: 'ყველა ციფრი ' + n + '-ზე მეტია, გარდა ' + GSA[i], f: function (x) { for (var k = 0; k < 4; k++) { if (k === i ? x[k] > n : x[k] <= n) return false; } return true; } }; });
    // 12 განმეორება
    op(12, 'distinct', function (q) { if (new Set(q).size !== 4) return null; return { t: 'არცერთი ციფრი არ მეორდება', f: function (x) { return new Set(x).size === 4; } }; });
    op(12, 'twokinds', function (q) { if (new Set(q).size !== 2) return null; return { t: 'კოდში მხოლოდ 2 განსხვავებული ციფრია', f: function (x) { return new Set(x).size === 2; } }; });
    op(12, 'palin', function (q) { if (q[0] !== q[3] || q[1] !== q[2]) return null; return { t: 'კოდი წინიდან და უკნიდან ერთნაირად იკითხება', f: function (x) { return x[0] === x[3] && x[1] === x[2]; } }; });
    op(12, 'twice', function (q, R) { var v = pick(R, q); if (q.filter(function (z) { return z === v; }).length !== 2) return null; return { t: 'ციფრი ' + v + ' კოდში ზუსტად ორჯერ გვხვდება', f: function (x) { return x.filter(function (z) { return z === v; }).length === 2; } }; });
    // 13 ძნელი ადგილები
    op(13, 'penult', function (q, R) { var lo = q[2] - ri(R, 1, 2), hi = q[2] + ri(R, 1, 2); if (lo < 0 || hi > 9) return null; return { t: 'ბოლოსწინა ციფრი ' + lo + '-სა და ' + hi + '-ს შორისაა', f: function (x) { return x[2] > lo && x[2] < hi; } }; });
    op(13, 'evenpl', function (q, R) { if (R() < .5) { if (q[1] !== q[3]) return null; return { t: 'ლუწ ადგილებზე მდგომი ციფრები ტოლია', f: function (x) { return x[1] === x[3]; } }; } if (q[0] !== q[2]) return null; return { t: 'კენტ ადგილებზე მდგომი ციფრები ტოლია', f: function (x) { return x[0] === x[2]; } }; });
    op(13, 'evenpar', function (q) { if (!(ev(q[1]) && ev(q[3]))) return null; return { t: 'ლუწ ადგილებზე მდგომი ციფრები ლუწია', f: function (x) { return ev(x[1]) && ev(x[3]); } }; });
    op(13, 'neigh', function (q, R) { if (R() < .5) { var p = shuf(R, [q[0], q[2]]); return { t: 'მეორე ციფრის გვერდით დგას ციფრები ' + p[0] + ' და ' + p[1], f: function (x) { return pairEq([x[0], x[2]], p); } }; } var s = shuf(R, [q[1], q[3]]); return { t: 'მესამე ციფრის გვერდით დგას ციფრები ' + s[0] + ' და ' + s[1], f: function (x) { return pairEq([x[1], x[3]], s); } }; });
    // 14 კიბეები
    op(14, 'inc', function (q) { if (q[0] < q[1] && q[1] < q[2] && q[2] < q[3]) return { t: 'ყოველი შემდეგი ციფრი წინაზე დიდია', f: function (x) { return x[0] < x[1] && x[1] < x[2] && x[2] < x[3]; } }; if (q[0] > q[1] && q[1] > q[2] && q[2] > q[3]) return { t: 'ყოველი შემდეგი ციფრი წინაზე პატარაა', f: function (x) { return x[0] > x[1] && x[1] > x[2] && x[2] > x[3]; } }; return null; });
    op(14, 'step', function (q) { var s = q[1] - q[0]; if (Math.abs(s) !== 1 && Math.abs(s) !== 2) return null; if (q[2] - q[1] !== s || q[3] - q[2] !== s) return null; return { t: 'ყოველი შემდეგი ციფრი წინაზე ' + Math.abs(s) + '-ით ' + (s > 0 ? 'მეტია' : 'ნაკლებია'), f: function (x) { return x[1] - x[0] === s && x[2] - x[1] === s && x[3] - x[2] === s; } }; });
    // 15 ორმაგი / ნახევარი / ნამრავლი
    op(15, 'dbl', function (q, R) { var p = two(R), i = p[0], j = p[1]; if (q[j] === 0 || q[i] !== 2 * q[j]) return null; return R() < .5 ? { t: NOM[i] + ' ციფრი ' + DAT[j] + ' 2-ჯერ მეტია', f: function (x) { return x[i] === 2 * x[j]; } } : { t: NOM[j] + ' ციფრი ' + GEN[i] + ' ნახევარია', f: function (x) { return 2 * x[j] === x[i]; } }; });
    op(15, 'prod', function (q, R) { var p = twoSorted(R), i = p[0], j = p[1], s = q[i] * q[j]; if (s < 2) return null; return { t: cap(GSA[i]) + ' და ' + GEN[j] + ' ნამრავლია ' + s, f: function (x) { return x[i] * x[j] === s; } }; });
    // 16 უდიდესი / უმცირესი
    op(16, 'maxmin', function (q, R) { var i = ri(R, 0, 3), o = q.filter(function (v, k) { return k !== i; }); if (q[i] > Math.max.apply(null, o)) return { t: NOM[i] + ' ციფრი კოდში ყველაზე დიდია', f: function (x) { return x[i] > Math.max.apply(null, x.filter(function (v, k) { return k !== i; })); } }; if (q[i] < Math.min.apply(null, o)) return { t: NOM[i] + ' ციფრი კოდში ყველაზე პატარაა', f: function (x) { return x[i] < Math.min.apply(null, x.filter(function (v, k) { return k !== i; })); } }; return null; }, { max: 2 });
    // 17 დიდი ჯამები
    op(17, 'bigsum', function (q, R) { var m = ri(R, 0, 3); if (m === 0) { var s = q[0] + q[1] + q[2] + q[3]; return { t: 'ოთხივე ციფრის ჯამია ' + s, f: function (x) { return x[0] + x[1] + x[2] + x[3] === s; } }; } if (m === 1) { var a = q[0] + q[1] + q[2]; return { t: 'პირველი სამი ციფრის ჯამია ' + a, f: function (x) { return x[0] + x[1] + x[2] === a; } }; } if (m === 2) { var b = q[1] + q[2] + q[3]; return { t: 'ბოლო სამი ციფრის ჯამია ' + b, f: function (x) { return x[1] + x[2] + x[3] === b; } }; } if (q[0] + q[1] !== q[2] + q[3]) return null; return { t: 'პირველი ორი ციფრის ჯამი ბოლო ორის ჯამის ტოლია', f: function (x) { return x[0] + x[1] === x[2] + x[3]; } }; });
    // 18 ამბავი — მხოლოდ ხელით დაწერილი
    // 19 „თუ… მაშინ…“
    op(19, 'ifthen', function (q, R) { var p = two(R), i = p[0], j = p[1], n = ri(R, 2, 7), c = q[i] > n, o; do { o = ri(R, 0, 9); } while (o === q[j]); var X = c ? q[j] : o, Y = c ? o : q[j]; return { t: 'თუ ' + NOM[i] + ' ციფრი ' + n + '-ზე მეტია, ' + NOM[j] + ' ციფრი არის ' + X + ', თუ არა და ' + Y, f: function (x) { return x[j] === (x[i] > n ? X : Y); } }; });
    // 20 ციფრების ფორმა
    op(20, 'upsame', function (q, R) { var i = ri(R, 0, 3); if (!(q[i] in UP) || UP[q[i]] !== q[i]) return null; return { t: NOM[i] + ' ციფრი თავდაყირაც იგივე რჩება', f: function (x) { return x[i] in UP && UP[x[i]] === x[i]; } }; });
    op(20, 'upflip', function (q, R) { var p = two(R), i = p[0], j = p[1]; if (!(q[j] === 6 || q[j] === 9) || q[i] !== UP[q[j]]) return null; return { t: NOM[i] + ' ციფრი ' + NOM[j] + ' ციფრია, ოღონდ თავდაყირა', f: function (x) { return x[j] in UP && x[i] === UP[x[j]]; } }; });
    op(20, 'round', function (q, R) { var i = ri(R, 0, 3); if (q[i] !== 0) return null; return { t: NOM[i] + ' ციფრი მთლიანად მრგვალია', f: function (x) { return x[i] === 0; } }; });
    // 21 საკეტის კლასიკა (ახალი)
    function score(g, x) { var ex = 0, cg = {}, cx = {}; for (var k = 0; k < 4; k++) { if (g[k] === x[k]) ex++; cg[g[k]] = (cg[g[k]] || 0) + 1; cx[x[k]] = (cx[x[k]] || 0) + 1; } var com = 0; for (var d in cg) com += Math.min(cg[d], cx[d] || 0); return [ex, com - ex]; }
    function lockText(g, e, m) {
        var s = g.join(' ') + ' — ';
        if (!e && !m) return s + 'არცერთი ციფრი არ არის სწორი';
        var parts = [];
        if (e) parts.push(CNT[e] + ' ციფრი სწორ ადგილზეა');
        if (m) parts.push(CNT[m] + ' ციფრი კოდშია, მაგრამ სხვა ადგილზე');
        return s + parts.join('; ');
    }
    op(21, 'guess', function (q, R) {
        var g = shuf(R, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]).slice(0, 4);
        if (R() < .6) { var k = ri(R, 0, 3); g[k] = q[k]; if (new Set(g).size !== 4) return null; }
        if (g.join('') === q.join('')) return null;
        var sc = score(g, q), e = sc[0], m = sc[1];
        if (e + m > 2) return null;
        return { t: lockText(g, e, m), f: function (x) { var r = score(g, x); return r[0] === e && r[1] === m; } };
    }, { max: 5, lock: true });

    // ── ყველა კოდი ──
    var ALL = []; for (var n = 0; n < 10000; n++) ALL.push([Math.floor(n / 1000), Math.floor(n / 100) % 10, Math.floor(n / 10) % 10, n % 10]);

    // ── კარის აწყობა ──
    function handmade(h, d) {
        var t = HOUSES[h].t;
        if (typeof t !== 'number') return null;
        var L = DATA[t].levels, story = t === 16;
        var idx = story ? (d < 7 ? d : -1) : (d < 2 ? d : -1);
        if (idx < 0 || !L[idx]) return null;
        var lv = L[idx];
        var fns = lv.p.map(function (p) { return new Function('a', 'b', 'c', 'd', 'odd', 'ev', 'UP', 'pairEq', 'return ' + p); });
        return { clues: lv.c.map(function (c, i) { return { t: c, f: (function (fn) { return function (x) { return !!fn(x[0], x[1], x[2], x[3], odd, ev, UP, pairEq); }; })(fns[i]) }; }), story: lv.s, note: lv.n, sols: lv.sols, hand: true };
    }

    function genCode(h, R) {
        var q = [ri(R, 0, 9), ri(R, 0, 9), ri(R, 0, 9), ri(R, 0, 9)];
        var r = R();
        // ზოგ თემას სჭირდება კონკრეტული ფორმის კოდი — დროდადრო ვაიძულებთ
        if (h === 1 || h === 13) { if (r < .5) { var p = two(R); q[p[0]] = q[p[1]]; } }
        if (h === 12) { if (r < .25) { q[3] = q[0]; q[2] = q[1]; } else if (r < .5) { q = shuf(R, [q[0], q[0], q[1], q[1]]); } else if (r < .7) { var s = shuf(R, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]); q = s.slice(0, 4); } }
        if (h === 14) { var st = pick(R, [1, -1, 2, -2]), b = st > 0 ? ri(R, 0, 9 - 3 * st) : ri(R, -3 * st, 9); if (r < .5) q = [b, b + st, b + 2 * st, b + 3 * st]; else { var u = shuf(R, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]).slice(0, 4).sort(function (x, y) { return x - y; }); q = R() < .5 ? u : u.reverse(); } }
        if (h === 5 || h === 8) { if (r < .3) q = q.map(function (v) { return v - (v % 2); }); else if (r < .5) q = q.map(function (v) { return v % 2 ? v : Math.min(9, v + 1); }); }
        if (h === 15) { if (r < .6) { var p2 = two(R), base = ri(R, 1, 4); q[p2[1]] = base; q[p2[0]] = 2 * base; } }
        if (h === 20) { if (r < .7) { var p3 = two(R); q[p3[1]] = pick(R, [6, 9]); q[p3[0]] = UP[q[p3[1]]]; } if (R() < .5) q[ri(R, 0, 3)] = pick(R, [0, 1, 8]); }
        if (h === 21) { q = shuf(R, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]).slice(0, 4); }
        if (h === 11) { if (r < .7) { var e = R() < .5 ? 0 : 1, ex = ri(R, 0, 3); q = q.map(function (v, k) { var want = k === ex ? 1 - e : e; return v % 2 === want ? v : (v === 9 ? 8 : v + 1); }); } }
        return q;
    }

    function countSols(fs, limit) { var n = 0, last = null; for (var k = 0; k < ALL.length; k++) { var x = ALL[k], ok = true; for (var i = 0; i < fs.length; i++) if (!fs[i](x)) { ok = false; break; } if (ok) { n++; last = x; if (limit && n > limit) return n; } } return n; }

    function generate(h, d, start) {
        var boss = d === DOORS - 1;
        var nClues = h === 21 ? (boss ? 6 : 5) : (boss && h >= 4 ? 5 : 4), maxClues = nClues + (h === 21 ? 2 : 1);
        var pool = OPS.filter(function (o) { return o.h <= h && (o.h !== 21 || h === 21); });
        var own = OPS.filter(function (o) { return o.h === h; });
        if (!own.length) own = pool.filter(function (o) { return o.h === h - 1; });   // ამბის სახლი: წინა თემა
        for (var attempt = start || 0; attempt < (start || 0) + 3000; attempt++) {
            var R = rng(h * 1009 + d * 101 + attempt * 7919 + 13);
            var q = genCode(h, R);
            var cand = ALL.filter(function () { return true; });
            var clues = [], used = {}, needOwn = h === 21 ? (boss ? 3 : 2) : ((boss || (d >= 2 && d <= 3)) && attempt % 2 === 0 ? 2 : 1);
            var tries = 0;
            while ((clues.length < nClues || cand.length > 1) && clues.length < maxClues && tries < 120) {
                tries++;
                var src;
                if (clues.filter(function (c) { return c.own; }).length < needOwn) src = own;
                else {
                    // ძველი ოპერატორები: დასაწყისში მარტივები, ბოლოს — ახლობლები
                    var w = pool.map(function (o) { var age = h - o.h; var base = d < 4 ? (o.simple ? 1.6 : 1) / (1 + age * .15) : 1 + Math.max(0, 6 - age) * .5; return o.h === h ? base * 1.5 : base; });
                    var tot = w.reduce(function (a, b) { return a + b; }, 0), r = R() * tot, k = 0;
                    while (r > w[k]) { r -= w[k]; k++; }
                    src = [pool[Math.min(k, pool.length - 1)]];
                }
                var o = pick(R, src);
                if ((used[o.id] || 0) >= o.max) continue;
                if (o.id === 'pos' && h > 0 && clues.some(function (c) { return c.id === 'pos'; })) continue;
                if (o.id === 'pos' && h > 0 && (used.pos || 0) >= 1) continue;
                var c = o.mk(q, R);
                if (!c || clues.some(function (z) { return z.t === c.t; })) continue;
                var nc = cand.filter(c.f);
                if (nc.length === cand.length) continue;          // არაფერს ამატებს
                if (clues.length < nClues - 1 && nc.length === 1) continue;   // ზედმეტად ადრე ხსნის
                cand = nc; used[o.id] = (used[o.id] || 0) + 1;
                clues.push({ t: c.t, f: c.f, id: o.id, own: o.h === h || src === own });
            }
            if (clues.length < nClues || cand.length !== 1) continue;
            // ყოველი პირობა საჭიროა
            var fs = clues.map(function (c) { return c.f; }), need = true;
            for (var i = 0; i < fs.length && need; i++) { var rest = fs.filter(function (_, k) { return k !== i; }); if (countSols(rest, 1) <= 1) need = false; }
            if (!need) continue;
            return { clues: shuf(R, clues), sols: [cand[0].join('')], hand: false, seed: attempt };
        }
        return null;
    }

    var CACHE = {};
    function door(h, d) {
        var k = h + '-' + d;
        if (!CACHE[k]) CACHE[k] = handmade(h, d) || generate(h, d, (typeof SEEDS !== 'undefined' && SEEDS[k]) || 0);
        return CACHE[k];
    }

    return { generate: generate, HOUSES: HOUSES, DOORS: DOORS, AUTO_HOUSES: AUTO_HOUSES, door: door, topicInfo: topicInfo, ALL: ALL, odd: odd, ev: ev, UP: UP, pairEq: pairEq };
})();


// ═══════════ კოდის გამტეხი — ეკრანები (რუკა, სახლი, კარი) ═══════════
(function () {
    var CFG = window.CB_CFG, P = CFG.progress || {};
    var H = CB.HOUSES, ND = CB.DOORS, POS = ['პირველი', 'მეორე', 'მესამე', 'მეოთხე'];
    var root = document.getElementById('cbx');
    var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    var key = function (h, d) { return h + '-' + d; };
    var stars = function (h, d) { return P[key(h, d)] || 0; };
    var houseDone = function (h) { return stars(h, ND - 1) > 0; };
    var houseOpen = function (h) { return h === 0 || houseDone(h - 1); };
    var doorOpen = function (h, d) { return houseOpen(h) && (d === 0 || stars(h, d - 1) > 0); };
    var doneCount = function (h) { var n = 0; for (var d = 0; d < ND; d++) if (stars(h, d)) n++; return n; };
    var totalStars = function () { var t = 0; for (var k in P) t += P[k]; return t; };
    var starStr = function (n) { return '★'.repeat(n) + '☆'.repeat(3 - n); };
    var themeVars = function (th) { return '--wall:' + th.wall + ';--door:' + th.door + ';--door2:' + th.door2 + ';--frame:' + th.frame; };
    var curHouse = function () { for (var h = 0; h < H.length; h++) if (!houseDone(h)) return h; return H.length - 1; };
    var view = { name: 'map' };

    // ── ზომა: გვერდი არ სქროლდება, შიგნით სქროლავს მხოლოდ სია ──
    function fit() {
        var top = root.getBoundingClientRect().top + window.scrollY;
        root.style.height = Math.max(440, window.innerHeight - top) + 'px';
    }
    addEventListener('resize', fit);

    // ── ადგილის სიტყვები ფერადად (ემთხვევა საკეტის ფანჯრებს) ──
    function markup(t) {
        t = esc(t);
        return t.replace(/(ბოლოსწინა|ბოლოდან მეორე|ბოლო[ა-ჰ]*|პირველ[ა-ჰ]*|მეორ[ა-ჰ]*|მესამ[ა-ჰ]*|მეოთხ[ა-ჰ]*)(?![ა-ჰ])(?! (?:ორ|სამ)[ა-ჰ]*)/g, function (m) {
            var p = /^ბოლოსწინა|^ბოლოდან/.test(m) ? 3 : /^ბოლო/.test(m) ? 4 : /^პირველ/.test(m) ? 1 : /^მეორ/.test(m) ? 2 : /^მესამ/.test(m) ? 3 : 4;
            return '<span class="pw pw' + p + '">' + m + '</span>';
        });
    }
    function mini(th, lockd, big) {
        return '<span class="cb-mini ' + (big ? 'big ' : '') + 'sh-' + (th.shape === 'goal' ? 'rect' : th.shape) + (lockd ? ' off' : '') + '" style="' + themeVars(th) + '" aria-hidden="true"><i></i></span>';
    }
    function go(v) { view = v; render(); }

    // ═════ რუკა ═════
    function map() {
        var cur = curHouse(), html = '';
        H.forEach(function (hs, h) {
            var open = houseOpen(h), n = doneCount(h), done = houseDone(h);
            var st = done ? '<span class="cb-badge ok">' + n + ' / ' + ND + ' ✓</span>' : (open ? '<span class="cb-badge cur">' + n + ' / ' + ND + (h === cur ? ' · აქ ხარ' : '') + '</span>' : '<span class="cb-badge">დაკეტილია</span>');
            html += '<button type="button" class="cb-house ' + (done ? 'done' : (open ? 'cur' : 'off')) + ' ' + (h % 2 ? 'r' : 'l') + '" data-h="' + h + '"' + (open ? '' : ' aria-disabled="true"') + '>'
                + mini(hs.th, !open) + '<span class="cb-hx"><small>სახლი ' + (h + 1) + '</small><b>' + esc(hs.name) + '</b><span>' + esc(CB.topicInfo(h).title) + '</span>' + st + '</span></button>';
        });
        root.innerHTML = '<div class="cb-head"><div><h1>კოდის გამტეხი</h1><span>' + H.length + ' სახლი · ' + (H.length * ND) + ' კარი</span></div><span class="cb-total">★ ' + totalStars() + '</span></div>'
            + '<div class="cb-prog"><span style="width:' + Math.round(Object.keys(P).length / (H.length * ND) * 100) + '%"></span></div>'
            + '<div class="cb-scroll" id="cbScroll"><div class="cb-path">' + html + '</div></div>';
        root.querySelectorAll('.cb-house').forEach(function (b) { b.onclick = function () { var h = +b.dataset.h; if (houseOpen(h)) go({ name: 'house', h: h }); }; });
        var c = root.querySelector('.cb-house.cur'); if (c) setTimeout(function () { c.scrollIntoView({ block: 'center' }); }, 30);
    }

    // ═════ სახლი ═════
    function house() {
        var h = view.h, hs = H[h], ti = CB.topicInfo(h), doors = '';
        for (var d = 0; d < ND; d++) {
            var o = doorOpen(h, d), s = stars(h, d), boss = d === ND - 1;
            doors += '<button type="button" class="cb-door' + (boss ? ' boss' : '') + (o ? '' : ' off') + (s ? ' done' : '') + '" data-d="' + d + '"' + (o ? '' : ' aria-disabled="true"') + '>'
                + '<b>' + (boss ? 'ბოსის კარი' : 'კარი ' + (d + 1)) + '</b><span class="st">' + (o ? (s ? starStr(s) : 'ახალი') : '🔒') + '</span></button>';
        }
        root.innerHTML = '<div class="cb-bar"><button type="button" class="cb-back" id="cbBack"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>სახლები</button><span class="cb-total">★ ' + totalStars() + '</span></div>'
            + '<div class="cb-scroll"><section class="cb-intro">' + mini(hs.th, false, true) + '<div><small>სახლი ' + (h + 1) + ' · ' + esc(ti.title) + '</small><h2>' + esc(hs.name) + '</h2><p>' + esc(ti.explain) + '</p><div class="cb-ex">მაგალითი: ' + esc(ti.example) + '</div></div></section>'
            + '<div class="cb-doors">' + doors + '</div><p class="cb-reward">' + (houseDone(h) ? 'სახლი დასრულებულია ✓ — ჯილდო უკვე მიღებული გაქვს' : 'სახლის დასრულება (ბოსის კარი) — <b>+10 მონეტა</b>') + '</p></div>';
        document.getElementById('cbBack').onclick = function () { go({ name: 'map' }); };
        root.querySelectorAll('.cb-door').forEach(function (b) { b.onclick = function () { var d = +b.dataset.d; if (doorOpen(h, d)) start(h, d); }; });
    }

    // ═════ კარი (თამაში) ═════
    function start(h, d) {
        go({ name: 'play', h: h, d: d, code: [null, null, null, null], sel: 0, tries: 0, hints: 0, locked: [false, false, false, false], fails: [], checked: [], won: false, msg: '' });
    }
    function auto() { return view.h < CB.AUTO_HOUSES && view.d < ND - 1; }
    function autoTicks(D) {
        var U = [0, 1, 2, 3].filter(function (i) { return view.code[i] === null; });
        if (U.length === 4) return D.clues.map(function () { return false; });
        var total = Math.pow(10, U.length);
        return D.clues.map(function (c) {
            for (var n = 0; n < total; n++) { var q = view.code.slice(), m = n; U.forEach(function (i) { q[i] = m % 10; m = Math.floor(m / 10); }); if (!c.f(q)) return false; }
            return true;
        });
    }
    function deco(th) {
        var d = th.deco, o = '';
        if (d === 'village') { o += '<span class="cb-vine" aria-hidden="true">' + '<i></i>'.repeat(7) + '</span>'; }
        if (d === 'teacher') o += '<span class="cb-chalk l" aria-hidden="true">7<br>+<br>3</span><span class="cb-chalk r" aria-hidden="true">×<br>9<br>=</span>';
        if (d === 'stadium') o += '<span class="cb-fans" aria-hidden="true">' + '<i></i>'.repeat(7) + '</span>';
        return o;
    }
    function leafExtra(th) {
        if (th.deco === 'football') return '<svg class="cb-jersey" viewBox="0 0 78 62" aria-hidden="true"><path d="M20 4l10-3h18l10 3 18 12-8 12-8-5v38H18V23l-8 5L2 16z" fill="#fff" stroke="#2B2622" stroke-width="2.5" stroke-linejoin="round"/><text x="39" y="44" text-anchor="middle" font-family="Noto Sans Georgian, sans-serif" font-weight="900" font-size="22" fill="#2F7BF5">10</text></svg>';
        if (th.deco === 'teacher') return '<span class="cb-glass" aria-hidden="true"></span><span class="cb-board" aria-hidden="true">მათემატიკა</span>';
        if (th.deco === 'director') return '<svg class="cb-cap" viewBox="0 0 54 40" aria-hidden="true"><path d="M27 4L2 15l25 11 25-11z" fill="#FFC93C"/><path d="M12 20v9c0 4 7 7 15 7s15-3 15-7v-9l-15 7z" fill="#FFC93C"/></svg>';
        if (th.tex === 'books') { var r = '', cols = ['#C2372B', '#2F7BF5', '#1E8A4C', '#FFC93C', '#8B5CF6', '#E2590F', '#6D4C41', '#2B9C8E']; for (var row = 0; row < 5; row++) { r += '<div class="cb-shelf">'; for (var i = 0; i < 8; i++) r += '<span style="height:' + (60 + (i * 7 + row * 13) % 35) + '%;width:' + (8 + (i * 5 + row * 3) % 6) + '%;background:' + cols[(i + row * 3) % 8] + '"></span>'; r += '</div>'; } return '<div class="cb-books" aria-hidden="true">' + r + '</div>'; }
        return '';
    }
    function knob(th) {
        if (th.shape === 'goal' || th.tex === 'books' || th.deco === 'director') return '';
        if (th.deco === 'football') return '<svg class="cb-knob ball" viewBox="0 0 26 26" aria-hidden="true"><circle cx="13" cy="13" r="11" fill="#fff" stroke="#2B2622" stroke-width="2.5"/><path d="M13 8l4 3-1.5 4.5h-5L9 11z" fill="#2B2622"/></svg>';
        return '<span class="cb-knob" aria-hidden="true"></span>';
    }
    function play() {
        var h = view.h, d = view.d, hs = H[h], th = hs.th, D = CB.door(h, d), boss = d === ND - 1, a = auto();
        if (a) view.checked = autoTicks(D);
        var ti = CB.topicInfo(h);
        var clues = D.clues.map(function (c, i) {
            var f = view.fails.indexOf(i) >= 0, ok = view.checked[i] && !f;
            return '<li><button type="button" class="cb-clue' + (ok ? ' done' : '') + (f ? ' fail' : '') + (a ? ' auto' : '') + '" data-i="' + i + '"' + (a ? ' aria-disabled="true"' : ' aria-pressed="' + !!view.checked[i] + '"') + '>' + (f ? '<span class="cb-failtag">არ სრულდება</span>' : '') + '<span class="box">' + (ok ? '✓' : '') + '</span><span class="tx">' + markup(c.t) + '</span></button></li>';
        }).join('');
        var dots = ''; for (var k = 0; k < ND; k++) dots += '<i class="' + (stars(h, k) ? 'on' : (k === d ? 'cur' : '')) + '"></i>';
        var lock = '<div class="cb-plate" role="group" aria-label="საკეტი">' + [0, 1, 2, 3].map(function (i) {
            return '<div class="cb-slot s' + (i + 1) + (view.sel === i ? ' sel' : '') + '"><button type="button" class="cb-arr" data-up="' + i + '" aria-label="' + POS[i] + ' ციფრი, ერთით მეტი">▲</button>'
                + '<div class="cb-win' + (view.code[i] === null ? ' empty' : '') + (view.locked[i] ? ' lk' : '') + '" data-w="' + i + '" tabindex="0" role="button" aria-label="' + POS[i] + ' ციფრი">' + (view.code[i] === null ? '?' : view.code[i]) + '</div>'
                + '<button type="button" class="cb-arr" data-dn="' + i + '" aria-label="' + POS[i] + ' ციფრი, ერთით ნაკლები">▼</button></div>';
        }).join('') + '</div>';
        var win = '';
        if (view.won) {
            var mine = view.code.join(''), nx = nextRef();
            win = '<span class="cb-prize" aria-hidden="true">' + hs.prize + '</span><div class="cb-wst" aria-label="' + view.earned + ' ვარსკვლავი">' + starStr(view.earned) + '</div><b class="cb-wt">' + (boss ? 'სახლი დასრულდა!' : 'კარი გაიღო!') + '</b><div class="cb-code">' + mine + '</div>';
        }
        root.innerHTML = '<div class="cb-bar"><button type="button" class="cb-back" id="cbBack"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>' + esc(hs.name) + '</button>'
            + '<span class="cb-where">' + (boss ? 'ბოსის კარი' : 'კარი ' + (d + 1) + ' / ' + ND) + '</span><span class="cb-dots" aria-hidden="true">' + dots + '</span></div>'
            + '<div class="cb-play' + (boss ? ' is-boss' : '') + '">'
            + '<section class="cb-left">'
            + (boss ? '<div class="cb-bossnote"><svg width="24" height="24" viewBox="0 0 24 24" fill="#E0A800" stroke="#2B2622" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M3 8l4 3 5-7 5 7 4-3-2 11H5z"/></svg><span><b>ბოსის კარი</b> — ყველა ნასწავლი ერთად. პირობა თავისით არ მოინიშნება.</span></div>' : '')
            + '<details class="cb-recall"><summary>გაიხსენე: ' + esc(ti['new']) + '</summary><p>' + esc(ti.explain) + '</p></details>'
            + (D.story ? '<div class="cb-story"><b>ამბავი</b>' + esc(D.story) + '</div>' : '')
            + '<ul class="cb-clues">' + clues + '</ul>'
            + '<p class="cb-tip">' + (a ? 'შესრულებული პირობა თავისით მოინიშნება ✓' : 'დააჭირე პირობას, როცა გამოიყენებ — მოინიშნება ✓') + '</p>'
            + '</section>'
            + '<section class="cb-right">'
            + '<div class="cb-scene sh-' + th.shape + ' tx-' + th.tex + (th.dark ? ' dark' : '') + (view.won ? ' open' : '') + '" id="cbScene" style="' + themeVars(th) + '">' + deco(th)
            + '<div class="cb-frame"><div class="cb-way">' + win + '</div><div class="cb-leaf">' + leafExtra(th) + lock + knob(th) + '</div></div></div>'
            + '<div class="cb-panel">' + (view.won
                ? '<div class="cb-acts"><button type="button" class="cb-btn main" id="cbNext">' + (nextRef() ? (boss ? 'შემდეგი სახლი →' : 'შემდეგი კარი →') : 'ყველა სახლი დასრულდა!') + '</button><button type="button" class="cb-btn soft" id="cbAgain">თავიდან</button></div>'
                : '<p class="cb-msg" role="status">' + esc(view.msg || '') + '</p><div class="cb-acts"><button type="button" class="cb-btn main" id="cbOpen">გააღე</button><button type="button" class="cb-btn soft" id="cbHint">მინიშნება</button></div>')
            + '</div></section></div>';

        document.getElementById('cbBack').onclick = function () { go({ name: 'house', h: h }); };
        if (!a) root.querySelectorAll('.cb-clue').forEach(function (b) { b.onclick = function () { var i = +b.dataset.i; view.checked[i] = !view.checked[i]; play(); }; });
        root.querySelectorAll('[data-w]').forEach(function (w) { w.onclick = function () { view.sel = +w.dataset.w; play(); }; });
        root.querySelectorAll('[data-up]').forEach(function (b) { b.onclick = function () { spin(+b.dataset.up, 1); }; });
        root.querySelectorAll('[data-dn]').forEach(function (b) { b.onclick = function () { spin(+b.dataset.dn, -1); }; });
        if (view.won) {
            document.getElementById('cbNext').onclick = function () { var n = nextRef(); if (!n) return go({ name: 'map' }); if (n.h !== h) go({ name: 'house', h: n.h }); else start(n.h, n.d); };
            document.getElementById('cbAgain').onclick = function () { start(h, d); };
        } else {
            document.getElementById('cbOpen').onclick = check;
            document.getElementById('cbHint').onclick = hint;
        }
    }
    function nextRef() { if (view.d + 1 < ND) return { h: view.h, d: view.d + 1 }; if (view.h + 1 < H.length) return { h: view.h + 1, d: 0 }; return null; }
    function spin(i, dir) { if (view.won || view.locked[i]) return; var v = view.code[i]; view.code[i] = v === null ? (dir > 0 ? 0 : 9) : (v + dir + 10) % 10; view.sel = i; view.fails = []; view.msg = ''; play(); }
    function typeD(dg) {
        if (view.won) return;
        if (dg === 'x') { var i = view.sel; if (view.code[i] === null && i > 0) i--; if (!view.locked[i]) { view.code[i] = null; view.sel = i; } }
        else { var j = view.sel; if (!view.locked[j]) view.code[j] = +dg; var k = j; do { k++; } while (k < 4 && view.locked[k]); view.sel = k < 4 ? k : j; }
        view.fails = []; view.msg = ''; play();
    }
    function check() {
        if (view.won) return;
        if (view.code.some(function (x) { return x === null; })) { view.msg = 'ჯერ ოთხივე ციფრი ჩაწერე'; play(); return; }
        var D = CB.door(view.h, view.d);
        var f = D.clues.map(function (c, i) { return c.f(view.code) ? -1 : i; }).filter(function (i) { return i >= 0; });
        if (!f.length) {
            view.earned = Math.max(1, 3 - (view.tries >= 1 ? 1 : 0) - (view.tries >= 3 ? 1 : 0) - view.hints);
            view.won = true;
            var k = key(view.h, view.d); P[k] = Math.max(P[k] || 0, view.earned);
            play(); save(view.h, view.d, view.earned);
            setTimeout(function () { var n = document.getElementById('cbNext'); if (n) n.focus(); }, 60);
            return;
        }
        view.tries++; view.fails = f;
        view.msg = f.length === 1 ? 'ერთი პირობა არ სრულდება — წითლადაა მონიშნული' : f.length + ' პირობა არ სრულდება — წითლადაა მონიშნული';
        play();
        var s = document.getElementById('cbScene'); s.classList.remove('shake'); void s.offsetWidth; s.classList.add('shake');
    }
    function hint() {
        if (view.won) return;
        var D = CB.door(view.h, view.d);
        var fits = function (s) { return view.locked.every(function (lk, i) { return !lk || +s[i] === view.code[i]; }); };
        var sc = function (s) { return s.split('').reduce(function (a, ch, i) { return a + (view.code[i] === +ch ? 1 : 0); }, 0); };
        var best = D.sols.filter(fits).sort(function (x, y) { return sc(y) - sc(x); })[0];
        if (!best) { view.msg = 'მინიშნება აქ ვერ დაგეხმარება'; play(); return; }
        var i = [0, 1, 2, 3].find(function (i) { return !view.locked[i] && view.code[i] !== +best[i]; });
        if (i === undefined) { view.msg = 'კოდი უკვე სწორია — დააჭირე „გააღე“'; play(); return; }
        view.code[i] = +best[i]; view.locked[i] = true; view.hints++; view.fails = [];
        view.msg = 'მინიშნება: ' + POS[i] + ' ციფრი არის ' + best[i];
        var j = 0; while (j < 4 && (view.locked[j] || view.code[j] !== null)) j++; view.sel = j < 4 ? j : view.sel;
        play();
    }
    function coinNote(n) {
        var el = document.createElement('div');
        el.className = 'cb-coin'; el.setAttribute('role', 'status');
        el.textContent = '+' + n + ' მონეტა — სახლი დასრულდა!';
        document.body.appendChild(el);
        setTimeout(function () { el.classList.add('show'); }, 20);
        setTimeout(function () { el.classList.remove('show'); setTimeout(function () { el.remove(); }, 400); }, 3200);
    }
    function save(h, d, st) {
        var fd = new FormData(); fd.append('house', h); fd.append('door', d); fd.append('stars', st);
        fetch(CFG.saveUrl, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CFG.csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j.coins) coinNote(j.coins); if (j.new_achievements && j.new_achievements.length && window.showAchievementToasts) window.showAchievementToasts(j.new_achievements); })
            .catch(function () {});
    }
    document.addEventListener('keydown', function (e) {
        if (view.name !== 'play' || view.won) return;
        if (/^[0-9]$/.test(e.key)) { typeD(e.key); e.preventDefault(); }
        else if (e.key === 'Backspace') { typeD('x'); e.preventDefault(); }
        else if (e.key === 'Enter' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'SUMMARY') { check(); e.preventDefault(); }
        else if (e.key === 'ArrowLeft') { view.sel = Math.max(0, view.sel - 1); play(); }
        else if (e.key === 'ArrowRight') { view.sel = Math.min(3, view.sel + 1); play(); }
        else if (e.key === 'ArrowUp') { spin(view.sel, 1); e.preventDefault(); }
        else if (e.key === 'ArrowDown') { spin(view.sel, -1); e.preventDefault(); }
    });

    function render() { root.className = 'cbx v-' + view.name; if (view.name === 'map') map(); else if (view.name === 'house') house(); else play(); }
    fit(); render(); setTimeout(fit, 300);
})();

</script>
@endverbatim
@endsection
