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
.cb-fox { display: flex; align-items: flex-end; gap: 8px; margin: 0 0 2px; }
.cb-fox-ic { width: 48px; height: 48px; flex-shrink: 0; }
.cb-fox-ic svg { width: 100%; height: 100%; display: block; }
.cb-fox.new .cb-fox-ic { animation: cbFox .5s cubic-bezier(.3, 1.6, .5, 1); }
@keyframes cbFox { from { transform: translateY(14px) scale(.6); opacity: 0; } to { transform: none; opacity: 1; } }
.cb-fox-b { position: relative; flex: 1; margin: 0 0 10px; padding: 8px 12px; background: #fff; border: 2.5px solid var(--ink); border-radius: 18px; font-size: 14px; font-weight: 800; line-height: 1.35; color: var(--ink); box-shadow: 0 3px 0 rgba(0, 0, 0, .12); }
.cb-fox-b::before { content: ''; position: absolute; left: -9px; bottom: 6px; width: 14px; height: 14px; background: #fff; border-left: 2.5px solid var(--ink); border-bottom: 2.5px solid var(--ink); transform: rotate(45deg); border-radius: 0 0 0 4px; }
.cb-fox.say .cb-fox-b { background: #FFF3EA; }
.cb-fox.say .cb-fox-b::before { background: #FFF3EA; }
.cb-btn[disabled] { opacity: .55; cursor: default; }
@media (max-width: 520px) { .cb-fox-ic { width: 38px; height: 38px; } .cb-fox-b { font-size: 13px; padding: 6px 10px; margin-bottom: 6px; } .cbx .cb-sign { display: none; } }
.cb-msg { margin: 0; min-height: 18px; text-align: center; font-size: 13px; font-weight: 800; color: var(--bad); }
.cb-acts { display: flex; gap: 10px; }
.cb-btn { height: 52px; border: none; border-radius: 16px; font-size: 16px; font-weight: 900; padding: 0 18px; }
.cb-btn.main { flex: 1; background: var(--o); color: #fff !important; font-size: 17px; }
.cb-btn.main:hover { background: var(--o2); }
.cb-btn.soft { background: var(--t1); color: var(--o2) !important; }
.cb-coin { position: fixed; left: 50%; top: 84px; transform: translate(-50%, -12px); opacity: 0; z-index: 1300; background: #FFC93C; color: #2B2622; border: 2.5px solid #2B2622; border-radius: 16px; padding: 10px 18px; font-family: 'Noto Sans Georgian', sans-serif; font-size: 16px; font-weight: 900; box-shadow: 0 8px 24px rgba(0,0,0,.2); transition: opacity .3s, transform .3s; }
.cb-coin.show { opacity: 1; transform: translate(-50%, 0); }
@media (max-height: 720px) { .cb-win { height: 40px; font-size: 24px; } .cb-arr { height: 22px; } .cb-btn { height: 46px; } .cb-clue { padding: 7px 10px; font-size: 14px; } }
/* დიდი კარი: სცენის თითქმის მთელ ზომაზე, საკეტი — დიდი ციფრებით */
.cbx .cb-scene { padding: 14px 16px 22px; }
.cbx .cb-scene .cb-frame { width: 94%; height: 100%; max-height: none; max-width: none; aspect-ratio: auto; box-sizing: border-box; }
.cbx .cb-plate { width: min(420px, 90%); gap: 8px; padding: 8px; border-radius: 16px; }
.cbx .cb-slot { gap: 5px; }
.cbx .cb-win { height: clamp(52px, 9vh, 74px); font-size: clamp(30px, 5vh, 42px); border-radius: 12px; }
.cbx .cb-arr { height: clamp(32px, 5vh, 40px); font-size: 15px; border-radius: 10px; }
@media (max-height: 720px) { .cbx .cb-win { height: 48px; font-size: 30px; } .cbx .cb-arr { height: 30px; } }
/* საკეტი მარცხნივ, კარი მარჯვნივ; კარის გაღებისას საკეტი ქრება და კარი მთელ სცენაზე იშლება */
.cbx .cb-scene { justify-content: space-between; align-items: flex-end; gap: 14px; }
.cbx .cb-scene > .cb-plate { flex: 1 1 auto; width: auto; max-width: 440px; align-self: center; margin: 0 auto; }
.cbx .cb-scene .cb-frame { flex: 0 0 36%; width: 36%; }
@media (max-width: 520px) {
    .cbx .cb-scene { gap: 8px; padding: 12px 10px 22px; }
    .cbx .cb-scene .cb-frame { flex-basis: 34%; width: 34%; }
    .cbx .cb-scene > .cb-plate { gap: 5px; padding: 6px; }
    .cbx .cb-win { font-size: 28px; border-width: 2px; }
    .cbx .cb-arr { height: 32px; }
}
/* ── ანიმაცია: ციფრის ტრიალი, ურდულები, კარის გაღება, განძი ── */
.cb-win .dg { display: inline-block; }
.cb-win.roll-up .dg { animation: cbRollUp .2s ease-out; }
.cb-win.roll-dn .dg { animation: cbRollDn .2s ease-out; }
.cb-win.roll-pop .dg { animation: cbPop .22s ease-out; }
@keyframes cbRollUp { from { transform: translateY(70%); opacity: 0; } to { transform: none; opacity: 1; } }
@keyframes cbRollDn { from { transform: translateY(-70%); opacity: 0; } to { transform: none; opacity: 1; } }
@keyframes cbPop { from { transform: scale(.5); opacity: .2; } to { transform: none; opacity: 1; } }
.cbx.busy .cb-plate, .cbx.busy .cb-acts { pointer-events: none; }
.cbx.busy .cb-acts { opacity: .6; }
.cb-slot.ok .cb-win { background: #E9FBE9 !important; border-color: #1E8A4C; color: #1E6B3C; animation: cbBolt .28s ease-out; }
.cb-slot.ok .cb-arr { opacity: .35; }
@keyframes cbBolt { 0% { transform: scale(1); } 50% { transform: scale(1.14); } 100% { transform: scale(1); } }
.cb-scene.unlatch .cb-plate { border-color: #1E8A4C; box-shadow: 0 0 0 4px rgba(30, 138, 76, .25), 0 3px 0 rgba(0, 0, 0, .2); }
.cb-scene.unlatch .cb-knob { animation: cbKnob .35s ease-in-out; }
.cb-scene.unlatch .cb-leaf { animation: cbUnlatch .3s ease-in-out; }
@keyframes cbKnob { 0% { transform: rotate(0); } 50% { transform: rotate(-70deg) scale(1.15); } 100% { transform: rotate(-70deg); } }
@keyframes cbUnlatch { 50% { transform: translateX(3px); } }
.cbx .cb-scene .cb-frame { transition: flex-basis .35s ease, width .35s ease; perspective: 1100px; }
.cbx .cb-leaf { transform-origin: 0 50%; backface-visibility: hidden; }
.cbx .cb-scene.open .cb-leaf { transform: rotateY(-104deg); opacity: 0; visibility: hidden; pointer-events: none; transition: transform .8s cubic-bezier(.35, .7, .3, 1), opacity .3s .6s, visibility 0s .9s; }
.cbx .cb-way { overflow: hidden; }
.cb-glow { position: absolute; left: 50%; top: 42%; width: 260%; aspect-ratio: 1; transform: translate(-50%, -50%); background: repeating-conic-gradient(rgba(255, 255, 255, .55) 0 10deg, transparent 10deg 30deg); border-radius: 50%; opacity: 0; transition: opacity .6s .4s; pointer-events: none; }
.cb-scene.open .cb-glow { opacity: .7; animation: cbSpin 14s linear infinite; }
@keyframes cbSpin { to { transform: translate(-50%, -50%) rotate(360deg); } }
.cbx .cb-way > .cb-glow { transform: translate(-50%, -50%); }
.cbx .cb-scene.open .cb-way > .cb-glow { transform: translate(-50%, -50%); }
.cb-way > :not(.cb-glow) { position: relative; z-index: 1; }
.cbx .cb-scene.enter .cb-room .cb-prize { animation: cbPrize .75s .75s cubic-bezier(.3, 1.6, .5, 1) both; }
@keyframes cbPrize { from { transform: scale(.2) rotate(-20deg); opacity: 0; } to { transform: none; opacity: 1; } }
.cbx .cb-prize { font-size: clamp(48px, 9vh, 76px); filter: drop-shadow(0 4px 0 rgba(43, 38, 34, .18)); }
.cb-found { font-size: 17px; font-weight: 900; color: #2B2622; }
.cb-cheer { margin: 0; max-width: 300px; font-size: 14px; font-weight: 800; line-height: 1.4; color: #7A3A0A; }
.cbx .cb-scene.open .cb-way > :nth-child(3) { transition-delay: .7s; }
.cbx .cb-scene.open .cb-way > :nth-child(4) { transition-delay: .85s; }
.cbx .cb-scene.open .cb-way > :nth-child(5) { transition-delay: 1s; }
.cbx .cb-scene.wide > .cb-plate { display: none; }
.cbx .cb-scene.wide .cb-frame { flex-basis: 94%; width: 94%; margin: 0 auto; }
.cb-cf { position: absolute; top: -12px; width: 9px; height: 14px; border-radius: 2px; z-index: 5; pointer-events: none; animation: cbFall 1.4s ease-in forwards; }
@keyframes cbFall { from { transform: translate(0, 0) rotate(0); opacity: 1; } to { transform: translate(var(--x), 105vh) rotate(var(--r)); opacity: .9; } }
.cb-scene { overflow: hidden; }
.cb-snd { width: 38px; height: 38px; border-radius: 50%; border: 2px solid var(--line); background: #fff; font-size: 17px; line-height: 1; display: inline-flex; align-items: center; justify-content: center; padding: 0; flex-shrink: 0; }
.cb-snd:hover { border-color: var(--ink); }
.cb-total .cb-snd { margin-right: 8px; vertical-align: middle; }
.cb-scene.shake .cb-plate { animation: cbBad .5s; }
@keyframes cbBad { 0%, 100% { background: #fff; } 30% { background: #FFE1E1; border-color: #C2372B; transform: translateX(-6px); } 60% { transform: translateX(5px); } }
/* ── ოთახი კარს მიღმა ── */
.cbx .cb-scene .cb-frame { transition: transform .8s cubic-bezier(.55, 0, .8, .2), opacity .3s .55s; transform-origin: 50% 55%; }
.cbx .cb-scene.enter .cb-frame { transform: scale(3.4); opacity: 0; }
.cbx .cb-scene > .cb-plate { transition: opacity .35s, transform .5s; }
.cbx .cb-scene.enter > .cb-plate { opacity: 0; transform: translateX(-40px) scale(.9); }
.cb-room { position: absolute; inset: 0; z-index: 4; display: flex; flex-direction: column; align-items: center; justify-content: space-between; padding: 12px 12px 0; box-sizing: border-box; overflow: hidden;
    opacity: 0; visibility: hidden; transform: scale(1.18);
    background: linear-gradient(180deg, rgba(255, 255, 255, .4), rgba(255, 255, 255, 0) 55%), var(--wall); }
.cb-scene.enter .cb-room { opacity: 1; visibility: visible; transform: none; transition: opacity .45s .45s, transform .8s .45s cubic-bezier(.2, .8, .3, 1), visibility 0s .45s; }
.cb-scene.dark .cb-room { background: linear-gradient(180deg, rgba(255, 255, 255, .12), rgba(255, 255, 255, 0) 55%), var(--wall); }
.cb-room::before { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 30%; border-top: 3px solid var(--ink);
    background: repeating-linear-gradient(90deg, rgba(0, 0, 0, .1) 0 2px, transparent 2px 58px), linear-gradient(var(--door), var(--door2)); }
.cb-room::after { content: ''; position: absolute; left: 0; right: 0; bottom: 30%; height: 10px; background: rgba(0, 0, 0, .08); }
.cb-rwin { position: absolute; top: 16%; left: 6%; width: 17%; max-width: 92px; aspect-ratio: 3 / 4; border: 3px solid var(--ink); border-radius: 10px 10px 6px 6px; box-sizing: border-box;
    background: linear-gradient(var(--ink), var(--ink)) 50% 0 / 3px 100% no-repeat, linear-gradient(var(--ink), var(--ink)) 0 50% / 100% 3px no-repeat, linear-gradient(#DDF4FF, #9ED8FF); }
.cb-rpic { position: absolute; top: 20%; right: 7%; width: 14%; max-width: 76px; aspect-ratio: 1; border: 3px solid var(--ink); border-radius: 6px; box-sizing: border-box;
    background: radial-gradient(circle at 34% 36%, #FFC93C 16%, transparent 17%), linear-gradient(160deg, #CFEFFF 52%, #8FD19A 52%); box-shadow: 0 0 0 4px #E0A800; }
.cb-rrug { position: absolute; left: 50%; bottom: 5%; width: 66%; height: 14%; transform: translateX(-50%); border-radius: 50%; background: #FFE3CC; border: 3px dashed rgba(194, 74, 10, .45); z-index: 1; }
.cb-room.boss .cb-rrug { background: #FFF1B8; border-color: rgba(224, 168, 0, .6); }
.cb-rcard { position: relative; z-index: 3; max-width: min(54%, 360px); background: rgba(255, 255, 255, .94); border: 2.5px solid var(--ink); border-radius: 16px; padding: 8px 14px 10px; text-align: center;
    box-shadow: 0 3px 0 rgba(0, 0, 0, .15); display: flex; flex-direction: column; align-items: center; gap: 2px; opacity: 0; transform: translateY(-10px); }
.cb-scene.enter .cb-rcard { opacity: 1; transform: none; transition: opacity .4s 1.25s, transform .4s 1.25s; }
.cb-rcard .cb-wst { font-size: 22px; }
.cb-rstage { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; margin-bottom: 11%; }
.cb-rglow { position: absolute; left: 50%; top: 38%; width: 230px; height: 230px; transform: translate(-50%, -50%); border-radius: 50%; background: radial-gradient(circle, rgba(255, 238, 170, .95), rgba(255, 238, 170, 0) 66%); pointer-events: none; }
.cb-room.boss .cb-rglow { width: 300px; height: 300px; background: radial-gradient(circle, rgba(255, 214, 90, .95), rgba(255, 214, 90, 0) 66%); }
.cbx .cb-room .cb-prize { position: relative; font-size: clamp(56px, 12vh, 100px); line-height: 1; margin-bottom: -4px; filter: drop-shadow(0 6px 0 rgba(43, 38, 34, .22)); }
.cb-rtable { position: relative; width: clamp(110px, 30vw, 190px); max-width: 52%; min-width: 110px; height: 16px; background: #A9713F; border: 3px solid var(--ink); border-radius: 8px; box-sizing: content-box; }
.cb-rtable::before, .cb-rtable::after { content: ''; position: absolute; top: 100%; width: 10px; height: 26px; background: #7A4E2A; border: 3px solid var(--ink); border-top: 0; border-radius: 0 0 4px 4px; }
.cb-rtable::before { left: 12px; } .cb-rtable::after { right: 12px; }
.cb-room.boss .cb-rtable { background: #FFC93C; }
.cb-room.boss .cb-rtable::before, .cb-room.boss .cb-rtable::after { background: #E0A800; }
@media (max-width: 440px) { .cb-bar .cb-dots { display: none; } .cb-bar .cb-snd { margin-left: auto; } }
@media (max-width: 520px) {
    .cb-rwin, .cb-rpic { display: none; }
    .cb-rcard { max-width: 92%; padding: 6px 10px 8px; }
    .cb-rcard .cb-wst { font-size: 19px; }
    .cb-found { font-size: 15px; }
    .cb-cheer { font-size: 13px; }
}
@media (prefers-reduced-motion: reduce) { .cb-room, .cb-rcard, .cbx .cb-scene .cb-frame, .cbx .cb-scene > .cb-plate { transition: none !important; } }
/* ── თოჯინების სახლი ── */
.cb-hp { display: grid; gap: 14px; align-items: start; }
@media (min-width: 900px) { .cb-hp { grid-template-columns: minmax(0, 1fr) minmax(0, 470px); } .cb-hp .cb-intro { position: sticky; top: 0; } }
.cb-hnote { margin: 10px 0 0 !important; font-size: 14px !important; color: #4F473F; }
.cb-hnote b { color: var(--o2); }
.cb-intro .cb-reward { text-align: left; }
.cb-dh { width: min(100%, 470px); margin: 4px auto 0; padding: 0 16px 6px; box-sizing: border-box; }
.cb-dh-roof { position: relative; height: clamp(92px, 26vw, 128px); margin: 0 -16px; }
.cb-dh-tri { position: absolute; inset: 0; clip-path: polygon(50% 0, 100% 100%, 0 100%); background: var(--ink); }
.cb-dh-tri i { position: absolute; inset: 5px 7px 0; clip-path: polygon(50% 0, 100% 100%, 0 100%); background: radial-gradient(circle at 50% 0, var(--roof) 9px, var(--roof2) 10px, var(--roof) 11px) 0 0 / 22px 16px; }
.cb-dh-chim { position: absolute; right: 22%; top: 16%; width: 24px; height: 44%; background: #C1814A; border: 3px solid var(--ink); border-radius: 3px; }
.cb-attic { position: absolute; left: 50%; bottom: 0; transform: translateX(-50%); width: 50%; height: 64%; padding: 0; border: 0; background: #FFF1B8; clip-path: polygon(50% 0, 100% 100%, 0 100%); cursor: pointer; }
.cb-attic svg { position: absolute; left: 14%; top: 8%; width: 72%; height: 92%; }
.cb-attic .lb { position: absolute; left: 50%; bottom: 6px; transform: translateX(-50%); }
.cb-attic .it { position: absolute; left: 50%; top: 34%; transform: translateX(-50%); font-size: 22px; }
.cb-attic.lock::before { content: '🔒'; position: absolute; inset: 0; z-index: 4; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 14%; font-size: 18px; background: rgba(36, 28, 48, .78); }
.cb-attic.cur { background: #FFE7A3; animation: cbAtt 1.6s ease-in-out infinite; }
@keyframes cbAtt { 50% { background: #FFD36B; } }
.cb-dh-body { display: grid; grid-template-columns: 1fr 1fr; gap: 3.5px; background: var(--ink); border: 3.5px solid var(--ink); border-top-width: 0; border-radius: 0 0 8px 8px; }
.cb-rm { position: relative; aspect-ratio: 16 / 10; padding: 0; border: 0; border-radius: 0; background: var(--wall); overflow: hidden; cursor: pointer; display: block; }
.cb-rm.wide { grid-column: 1 / -1; aspect-ratio: 32 / 10; }
.cb-rm svg { position: absolute; inset: 0 0 0 0; width: 100%; height: 100%; z-index: 1; }
.cb-rm.wide svg { left: 25%; width: 50%; }
.cb-rm::after, .cb-rart::before { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 16%; background: repeating-linear-gradient(90deg, rgba(0, 0, 0, .13) 0 2px, transparent 2px 26px), #B9824F; border-top: 2.5px solid var(--ink); }
.cb-dh.dark .cb-rm::after, .cb-room.dark .cb-rart::before { background: repeating-linear-gradient(90deg, rgba(0, 0, 0, .2) 0 2px, transparent 2px 26px), #6B4A2E; }
.cb-rm.wp2, .cb-room.wp2, .cb-scene.wp2 { background-image: repeating-linear-gradient(90deg, transparent 0 14px, rgba(255, 255, 255, .32) 14px 19px); }
.cb-rm.wp3, .cb-room.wp3, .cb-scene.wp3 { background-image: radial-gradient(circle, rgba(255, 255, 255, .42) 2px, transparent 3px); background-size: 14px 14px; }
.cb-rm .lb, .cb-attic .lb { z-index: 3; background: #fff; border: 2px solid var(--ink); border-radius: 10px; padding: 0 7px; font-size: 11px; font-weight: 900; line-height: 18px; color: var(--ink); }
.cb-rm .lb { position: absolute; left: 6px; top: 6px; }
.cb-rm .st { position: absolute; right: 5px; top: 5px; z-index: 3; font-size: 11px; color: #E0A800; font-weight: 900; background: rgba(255, 255, 255, .92); border-radius: 8px; padding: 0 5px; letter-spacing: 1px; }
.cb-rm .it { position: absolute; right: 8px; bottom: 12%; z-index: 3; font-size: clamp(18px, 5vw, 26px); filter: drop-shadow(0 2px 0 rgba(0, 0, 0, .2)); }
.cb-rm .go { position: absolute; left: 50%; bottom: 22%; transform: translateX(-50%); z-index: 3; background: var(--o); color: #fff; font-size: 12px; font-weight: 900; border-radius: 12px; padding: 2px 10px; border: 2px solid var(--ink); }
.cb-rm.lock::before { content: '🔒'; position: absolute; inset: 0; z-index: 4; display: flex; align-items: center; justify-content: center; font-size: 20px; background: rgba(36, 28, 48, .78); }
.cb-rm.lock, .cb-attic.lock { cursor: not-allowed; }
.cb-rm.cur { outline: 4px solid var(--o); outline-offset: -4px; z-index: 2; }
.cb-rm.cur .lb, .cb-attic.cur .lb { background: var(--o); color: #fff; border-color: var(--o); }
.cb-rm.done:hover, .cb-rm.cur:hover { filter: brightness(1.05); }
.cb-rm.fresh .it, .cb-attic.fresh .it { animation: cbPrize .7s .2s cubic-bezier(.3, 1.6, .5, 1) both; }
.cb-dh-ground { height: 14px; margin: 0 -16px; background: #8FD19A; border: 3px solid var(--ink); border-radius: 8px; }
/* რუკის პატარა სახლი */
.cb-mh { width: 50px; height: 60px; display: flex; flex-direction: column; align-items: center; flex-shrink: 0; }
.cb-mh .r { display: block; width: 54px; height: 20px; clip-path: polygon(50% 0, 100% 100%, 0 100%); background: var(--roof); }
.cb-mh .b { width: 42px; flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 2px; padding: 2px; background: var(--ink); border-radius: 0 0 4px 4px; box-sizing: border-box; }
.cb-mh .b i { background: #5E5468; border-radius: 1px; }
.cb-mh .b i.on { background: #FFE7A3; }
.cb-mh .b i.w { grid-column: 1 / -1; }
.cb-mh.off { filter: grayscale(1); opacity: .6; }
/* კარის სცენა — სახლის სტილში: შპალერი, ხის იატაკი, ოთახის აბრა */
.cbx .cb-scene::after { height: 26px; background: repeating-linear-gradient(90deg, rgba(0, 0, 0, .13) 0 2px, transparent 2px 44px), #B9824F; }
.cbx .cb-scene.dark::after { background: repeating-linear-gradient(90deg, rgba(0, 0, 0, .2) 0 2px, transparent 2px 44px), #6B4A2E; }
.cbx .cb-scene { padding-bottom: 26px; }
.cb-sign { position: absolute; z-index: 3; left: 14px; top: 12px; max-width: 56%; background: #FFF8EE; border: 2.5px solid var(--ink); border-radius: 10px; padding: 3px 10px; font-size: 13px; font-weight: 900; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; box-shadow: 0 3px 0 rgba(0, 0, 0, .15); }
.cb-scene.enter .cb-sign { opacity: 0; transition: opacity .3s; }
/* ოთახი კარს მიღმა — ოთახის ტიპის მიხედვით */
.cb-room::before, .cb-room::after { content: none !important; }
.cb-rart { position: absolute; left: 0; right: 0; bottom: 0; aspect-ratio: 16 / 10; z-index: 1; }
.cb-rart svg { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1; }
.cb-rart .cb-rglow { left: 50%; top: auto; bottom: -8%; transform: translateX(-50%); z-index: 2; }
.cbx .cb-rart .cb-prize { position: absolute; left: 50%; bottom: 9%; z-index: 3; margin-left: calc(clamp(56px, 12vh, 100px) / -2); width: clamp(56px, 12vh, 100px); text-align: center; }
/* სახლის გარემო */
.cb-dh.g-snow .cb-dh-ground { background: #F4FBFF; height: 18px; }
.cb-dh.g-snow .cb-dh-tri::after { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 26%; background: #fff; clip-path: polygon(50% 0, 100% 100%, 0 100%); }
.cb-dh.g-sand .cb-dh-ground { background: #F5D99A; height: 16px; box-shadow: inset 0 -5px 0 #6EC6F0; }
.cb-dh.g-rock .cb-dh-ground { background: #8D8079; height: 18px; border-radius: 14px 14px 8px 8px; }
.cb-dh.g-trunk .cb-dh-ground { width: 70px; height: 54px; margin: 0 auto; border-top: 0; border-radius: 0 0 6px 6px; background: repeating-linear-gradient(90deg, #8A5A2E 0 8px, #7A4E26 8px 14px); }
.cb-dh.g-trunk .cb-dh-roof::before { content: ''; position: absolute; left: -8%; right: -8%; top: 10%; bottom: -18%; z-index: -1; border-radius: 50%; background: radial-gradient(circle at 20% 60%, #74C69D 0 28%, transparent 29%), radial-gradient(circle at 80% 60%, #74C69D 0 28%, transparent 29%), radial-gradient(circle at 50% 30%, #52B788 0 34%, transparent 35%); }
.cb-dh-roof { z-index: 0; }
/* თემატური შპალერი */
.cb-rm.wp1, .cb-room.wp1, .cb-scene.wp1 { background-image: var(--motif); background-size: 52px 52px; }
.cb-rm.wp1 { background-size: 40px 40px; }
/* კარის სტიკერი */
.cb-stk { position: absolute; left: 50%; top: 60%; z-index: 2; width: 56%; max-width: 120px; aspect-ratio: 1; transform: translate(-50%, -50%) rotate(-6deg); filter: drop-shadow(0 3px 0 rgba(0, 0, 0, .22)); pointer-events: none; }
.cb-stk.r { transform: translate(-50%, -50%) rotate(5deg); }
.cb-stk svg { width: 100%; height: 100%; overflow: visible; }
.cb-stk.low { top: 74%; width: 42%; }
/* თემატური დეკორი */
.cb-rm .pr { position: absolute; left: 5%; bottom: 13%; z-index: 2; font-size: clamp(15px, 4.2vw, 22px); line-height: 1; filter: drop-shadow(0 2px 0 rgba(0, 0, 0, .18)); }
.cb-rm.wide .pr { left: 8%; }
.cb-dh-flag { position: absolute; left: 50%; top: -30px; width: 3px; height: 36px; background: var(--ink); z-index: 3; }
.cb-dh-flag i { position: absolute; left: 3px; top: 0; font-style: normal; font-size: 16px; line-height: 22px; padding: 0 6px; background: #fff; border: 2.5px solid var(--ink); border-left: 0; border-radius: 0 8px 8px 0; }
.cb-dh { padding-top: 30px; }
.cb-mh { position: relative; }
.cb-mh .f { position: absolute; right: -8px; top: -6px; font-size: 15px; line-height: 1; z-index: 2; }
.cb-sprop { position: absolute; z-index: 2; left: 16px; bottom: 30px; font-size: clamp(26px, 5vh, 38px); line-height: 1; filter: drop-shadow(0 3px 0 rgba(0, 0, 0, .18)); }
.cb-scene.enter .cb-sprop { opacity: 0; transition: opacity .3s; }
.cb-rposter { position: absolute; z-index: 1; left: 7%; top: 30%; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; font-size: 36px; background: #fff; border: 3px solid var(--ink); border-radius: 6px; box-shadow: 0 0 0 5px #C1814A, 0 4px 0 6px rgba(0, 0, 0, .12); }
.cb-rprop { position: absolute; left: 6%; bottom: 10%; z-index: 3; font-size: clamp(34px, 7vh, 54px); line-height: 1; filter: drop-shadow(0 4px 0 rgba(0, 0, 0, .2)); }
@media (max-width: 520px) { .cb-rposter { display: none; } }
/* ოთახების კარების დეტალები */
.cb-dx-port { position: absolute; left: 50%; top: 14%; width: 38%; aspect-ratio: 1; transform: translateX(-50%); border-radius: 50%; background: radial-gradient(circle at 35% 35%, #fff 0 12%, #BFE6FF 14%); border: 3px solid var(--ink); box-shadow: inset 0 0 0 4px #E6E6E6; }
.cb-dx-frost { position: absolute; left: 22%; right: 22%; top: 10%; height: 30%; background: linear-gradient(135deg, #F2FAFF, #C9E8FA); border: 3px solid var(--ink); border-radius: 8px; }
.cb-dx-panes { position: absolute; left: 14%; right: 14%; top: 9%; height: 46%; border: 3px solid var(--ink); border-radius: 4px; background: linear-gradient(var(--ink), var(--ink)) 50% 0 / 3px 100% no-repeat, linear-gradient(var(--ink), var(--ink)) 0 50% / 100% 3px no-repeat, linear-gradient(160deg, #EEF8FF, #BFE6FF); }
.cb-dx-star { position: absolute; left: 50%; top: 20%; transform: translateX(-50%); font-size: clamp(34px, 7vh, 54px); line-height: 1; color: #FFC93C; -webkit-text-stroke: 2.5px #2B2622; }
/* კოლექცია */
.cb-coll { margin-top: 12px; background: #fff; border-radius: 22px; padding: 12px 14px 14px; box-shadow: 0 2px 0 var(--line); }
.cb-coll-h { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-bottom: 8px; font-size: 14px; }
.cb-coll-h b { font-weight: 900; }
.cb-coll-h span { font-weight: 900; color: var(--o2); }
.cb-coll-row { display: flex; flex-wrap: wrap; gap: 8px; padding: 10px; background: linear-gradient(#FFF8EE, #FFF1DE); border-radius: 14px 14px 4px 4px; border-bottom: 6px solid #C9A27A; }
.cb-it { width: 46px; height: 46px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(255, 255, 255, .7); border: 2px dashed #E3D6C6; }
.cb-it i { font-style: normal; font-size: 27px; line-height: 1; filter: brightness(0); opacity: .16; }
.cb-it.got { background: #fff; border: 2px solid #FFC49A; }
.cb-it.got i { filter: none; opacity: 1; }
.cb-it.boss { width: 56px; border-color: #E0A800; background: #FFF6D6; }
.cb-it.fresh i { animation: cbPrize .7s .15s cubic-bezier(.3, 1.6, .5, 1) both; }
@media (prefers-reduced-motion: reduce) { .cb-win .dg, .cb-slot.ok .cb-win, .cb-scene.unlatch .cb-knob, .cb-scene.unlatch .cb-leaf, .cb-scene.open .cb-glow, .cbx .cb-scene.enter .cb-room .cb-prize, .cb-it.fresh i, .cb-scene.shake .cb-plate { animation: none !important; } .cbx .cb-scene .cb-frame, .cbx .cb-scene.open .cb-leaf { transition: none !important; } }
@media (prefers-reduced-motion: reduce) { .cb-leaf, .cb-way > * { transition: none; } .cb-scene.shake .cb-leaf { animation: none; } }
</style>

<div class="cbx v-map" id="cbx" aria-live="polite"></div>

<script>
window.CB_CFG = {
    progress: @json($progress),
    hints: @json($hints ?? (object) []),
    saveUrl: @json(route('games.code-breaker.save')),
    hintUrl: @json(route('games.code-breaker.hint')),
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
        { name: 'მათემატიკის მასწავლებლის სახლი',      t: 4,  prize: '📐', th: { wall: '#FFF6E5', door: '#E9C79A', door2: '#D4A86E', frame: '#6D4C41', shape: 'rect', tex: 'plain', deco: 'teacher' } },
        { name: 'კონდიტერის სახლი',     t: 5,  prize: '🍭', th: { wall: '#FFF0F6', door: '#FF8FAB', door2: '#FFE0EA', frame: '#FB6F92', shape: 'rect', tex: 'stripes' } },
        { name: 'ზოოლოგის სახლი',      t: 6,  prize: '🦁', th: { wall: '#FEFAE0', door: '#7BA05B', door2: '#5A7F3E', frame: '#588157', shape: 'arch', tex: 'planks' } },
        { name: 'ფერმერის სახლი',          t: 7,  prize: '🐮', th: { wall: '#D9ED92', door: '#C1121F', door2: '#8D0801', frame: '#FDF0D5', shape: 'rect', tex: 'barn' } },
        { name: 'სახლი ხეზე',             t: 8,  prize: '🐿️', th: { wall: '#CAF0F8', door: '#BC6C25', door2: '#8A4F1B', frame: '#606C38', shape: 'arch', tex: 'planks' } },
        { name: 'მუსიკოსის სახლი',      t: 'count',  prize: '🎸', th: { wall: '#EDE7FF', door: '#8B5CF6', door2: '#6D3FD8', frame: '#FFC93C', shape: 'arch', tex: 'stars' } },
        { name: 'ჯამბაზის სახლი',          t: 'either', prize: '🎪', th: { wall: '#FDF0D5', door: '#E63946', door2: '#FFFDF5', frame: '#FFB703', shape: 'arch', tex: 'stripes' } },
        { name: 'თოვლის ბაბუას სახლი',        t: 9,  prize: '⛄', th: { wall: '#E0FBFC', door: '#A9E5F2', door2: '#48CAE4', frame: '#FFFFFF', shape: 'arch', tex: 'ice' } },
        { name: 'დეტექტივის სახლი',         t: 10, prize: '🕯️', th: { wall: '#4A3B66', door: '#8A5A2E', door2: '#5A3B22', frame: '#5A3B22', shape: 'rect', tex: 'books', dark: true } },
        { name: 'მემანქანის სახლი',      t: 11, prize: '🚂', th: { wall: '#E9EDC9', door: '#D62828', door2: '#A4161A', frame: '#003049', shape: 'rect', tex: 'metal' } },
        { name: 'მეზღვაურის სახლი',                 t: 12, prize: '🗼', th: { wall: '#CDEBFF', door: '#FFFFFF', door2: '#E63946', frame: '#E63946', shape: 'arch', tex: 'hstripes' } },
        { name: 'გამომგონებლის სახლი',      t: 13, prize: '🤖', th: { wall: '#E5E5E5', door: '#ADB5BD', door2: '#6C757D', frame: '#FFB703', shape: 'rect', tex: 'metal' } },
        { name: 'სუპერგმირების სახლი',    t: 14, prize: '🦸', th: { wall: '#FFE3E3', door: '#2F4BC4', door2: '#C2372B', frame: '#FFC93C', shape: 'rect', tex: 'hero' } },
        { name: 'მეკობრის სახლი',         t: 15, prize: '💰', th: { wall: '#F6E7C1', door: '#A0522D', door2: '#7B3F1D', frame: '#E0A800', shape: 'rect', tex: 'bands' } },
        { name: 'მეზღაპრის სახლი',   t: 16, prize: '📚', th: { wall: '#F1E3D3', door: '#6F4518', door2: '#4A2E12', frame: '#A68A64', shape: 'arch', tex: 'panels' } },
        { name: 'ჯადოქრის სახლი',         t: 17, prize: '🔮', th: { wall: '#3C096C', door: '#7B2CBF', door2: '#5A189A', frame: '#C77DFF', shape: 'arch', tex: 'stars', dark: true } },
        { name: 'დრაკონის სახლი',   t: 18, prize: '🐉', th: { wall: '#4A3530', door: '#9D0208', door2: '#6A040F', frame: '#7F5539', shape: 'arch', tex: 'hplanks', dark: true } },
        { name: 'დირექტორის სახლი',       t: 'lock',   prize: '🎓', th: { wall: '#F3EDE2', door: '#5A2E1A', door2: '#3E1F11', frame: '#E0A800', shape: 'arch', tex: 'gold', deco: 'director' } },
    ];
    var DOORS = 10, AUTO_HOUSES = 6;
    // კარების რაოდენობა: 1–6 სახლი → 6, 7–14 → 8, 15–22 → 10 (ბოლო ყოველთვის ბოსია; ბოსის ამოცანა = ძველი მე-10 კარი)
    function nd(h) { return h < 6 ? 6 : (h < 14 ? 8 : 10); }   // 1–6 სახლში შესრულებული პირობა თავისით ინიშნება (ბოსის გარდა)

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
        var g = d === nd(h) - 1 ? DOORS - 1 : d, k = h + '-' + g;
        if (!CACHE[k]) CACHE[k] = handmade(h, g) || generate(h, g, (typeof SEEDS !== 'undefined' && SEEDS[k]) || 0);
        return CACHE[k];
    }

    return { generate: generate, HOUSES: HOUSES, DOORS: DOORS, nd: nd, AUTO_HOUSES: AUTO_HOUSES, door: door, topicInfo: topicInfo, ALL: ALL, odd: odd, ev: ev, UP: UP, pairEq: pairEq };
})();


// ═══════════ კოდის გამტეხი — ეკრანები (რუკა, სახლი, კარი) ═══════════
(function () {
    var CFG = window.CB_CFG, P = CFG.progress || {}, HINTS = CFG.hints || {};
    var H = CB.HOUSES, nd = CB.nd, POS = ['პირველი', 'მეორე', 'მესამე', 'მეოთხე'];
    var TOTAL = 0; for (var hh = 0; hh < H.length; hh++) TOTAL += nd(hh);
    var RM = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;

    var FRESH = null;

    // ── თოჯინების სახლი: ყოველი კარი ერთი ოთახია (ქვემოდან ზევით), ბოსი სხვენშია ──
    var INK = '#2B2622';
    function sv(a) {return '<g stroke="'+INK+'" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round">'+a+'</g>';}
    var ROOMS = {
 hall:function(c){return sv(
  '<rect x="96" y="30" width="34" height="54" rx="3" fill="'+c.door+'"/><circle cx="124" cy="58" r="2.2" fill="#FFC93C"/>'+
  '<path d="M96 30h34" />'+
  '<line x1="30" y1="34" x2="30" y2="84"/><line x1="20" y1="84" x2="40" y2="84"/><path d="M22 40h16M26 34l-6 8M34 34l6 8"/>'+
  '<path d="M44 44c-4 6-4 14 0 20h10c4-6 4-14 0-20z" fill="#8FD19A"/>'+
  '<rect x="92" y="84" width="42" height="5" rx="2" fill="#E2590F"/>'+
  '<rect x="56" y="36" width="22" height="28" rx="11" fill="#DDF4FF"/>');},
 living:function(c){return sv(
  '<rect x="34" y="58" width="76" height="22" rx="8" fill="#F48FB1"/><rect x="40" y="48" width="64" height="16" rx="7" fill="#F8BBD0"/>'+
  '<rect x="28" y="56" width="12" height="24" rx="5" fill="#EC6F9A"/><rect x="104" y="56" width="12" height="24" rx="5" fill="#EC6F9A"/>'+
  '<line x1="40" y1="80" x2="40" y2="84"/><line x1="104" y1="80" x2="104" y2="84"/>'+
  '<rect x="56" y="18" width="30" height="22" rx="2" fill="#CFEFFF"/><path d="M58 38l9-10 7 7 5-5 6 8z" fill="#8FD19A"/>'+
  '<path d="M134 84v-30" /><path d="M126 54h16l-4-14h-8z" fill="#FFC93C"/>'+
  '<path d="M14 84h12l-2-14h-8z" fill="#C1814A"/><path d="M20 70c-8-6-8-16 0-20c8 4 8 14 0 20z" fill="#8FD19A"/>');},
 kitchen:function(c){return sv(
  '<rect x="18" y="44" width="40" height="40" rx="3" fill="#F1EFEB"/><rect x="24" y="56" width="28" height="20" rx="3" fill="#6B625A"/><circle cx="28" cy="49" r="2" fill="'+INK+'"/><circle cx="38" cy="49" r="2" fill="'+INK+'"/>'+
  '<path d="M26 44v-6h10v6" fill="#B0B0B0"/>'+
  '<rect x="64" y="58" width="58" height="26" rx="2" fill="'+c.door+'"/><line x1="93" y1="58" x2="93" y2="84"/><path d="M60 58h66"/>'+
  '<line x1="78" y1="22" x2="140" y2="22"/><rect x="84" y="10" width="10" height="12" rx="2" fill="#FFC93C"/><rect x="100" y="12" width="9" height="10" rx="2" fill="#E2590F"/><rect x="116" y="8" width="11" height="14" rx="3" fill="#8FD19A"/>'+
  '<rect x="130" y="40" width="20" height="16" rx="2" fill="#DDF4FF"/>');},
 bedroom:function(c){return sv(
  '<rect x="30" y="56" width="86" height="20" rx="5" fill="#FFFFFF"/><rect x="26" y="40" width="10" height="44" rx="3" fill="'+c.door+'"/><rect x="110" y="50" width="10" height="34" rx="3" fill="'+c.door+'"/>'+
  '<rect x="38" y="48" width="22" height="12" rx="5" fill="#FFE3CC"/><path d="M62 56h50v14H62z" fill="#9FC9F0"/>'+
  '<rect x="128" y="62" width="20" height="22" rx="2" fill="#C1814A"/><path d="M132 62v-12h12v12" fill="none"/><path d="M130 50h16l-3-10h-10z" fill="#FFC93C"/>'+
  '<rect x="60" y="12" width="32" height="26" rx="3" fill="#DDF4FF"/><line x1="76" y1="12" x2="76" y2="38"/><path d="M56 10c4 10 4 20 0 30M96 10c-4 10-4 20 0 30" fill="none" stroke="#E2590F"/>');},
 dining:function(c){return sv(
  '<path d="M80 6v10"/><path d="M66 24h28l-6-8h-16z" fill="#FFC93C"/>'+
  '<rect x="44" y="54" width="72" height="8" rx="3" fill="#C1814A"/><line x1="52" y1="62" x2="52" y2="84"/><line x1="108" y1="62" x2="108" y2="84"/>'+
  '<path d="M30 84V46M30 64h14v20" fill="none"/><path d="M130 84V46M130 64h-14v20" fill="none"/>'+
  '<path d="M68 54c0-8 24-8 24 0" fill="#FFF"/><circle cx="72" cy="48" r="4" fill="#E2590F"/><circle cx="84" cy="46" r="4" fill="#FFC93C"/>'+
  '<path d="M60 54v-8M100 54v-8" />');},
 bath:function(c){return sv(
  '<path d="M24 58h96v10c0 10-8 16-18 16H42c-10 0-18-6-18-16z" fill="#FFFFFF"/><path d="M30 58V36c0-6 10-6 10 0" fill="none"/>'+
  '<circle cx="54" cy="52" r="5" fill="#DDF4FF"/><circle cx="66" cy="49" r="4" fill="#DDF4FF"/><circle cx="78" cy="53" r="5" fill="#DDF4FF"/>'+
  '<rect x="126" y="20" width="22" height="30" rx="11" fill="#CFEFFF"/><rect x="128" y="58" width="18" height="26" rx="3" fill="'+c.door+'"/>'+
  '<path d="M96 52c0-6 8-6 8 0z" fill="#FFC93C"/>');},
 library:function(c){
  var b='';var cols=['#C2372B','#2F7BF5','#1E8A4C','#FFC93C','#8B5CF6','#E2590F'];
  for(var r=0;r<3;r++)for(var i=0;i<6;i++){b+='<rect x="'+(34+i*9+(r%2)*2)+'" y="'+(22+r*20+((i*3+r)%4))+'" width="7" height="'+(16-((i*3+r)%4))+'" fill="'+cols[(i+r*2)%6]+'"/>';}
  return sv('<rect x="28" y="14" width="66" height="70" rx="2" fill="#8A5A2E"/>'+b+'<path d="M28 38h66M28 58h66"/>'+
  '<path d="M110 84V60h28v24" fill="#E2590F"/><rect x="106" y="52" width="36" height="10" rx="4" fill="#F48A4A"/><path d="M126 30v16" /><path d="M118 30h16l-3-10h-10z" fill="#FFC93C"/>');},
 play:function(c){return sv(
  '<ellipse cx="80" cy="84" rx="58" ry="6" fill="#FFE3CC"/>'+
  '<rect x="26" y="62" width="18" height="18" fill="#E2590F"/><rect x="44" y="62" width="18" height="18" fill="#2F7BF5"/><rect x="35" y="44" width="18" height="18" fill="#FFC93C"/>'+
  '<circle cx="96" cy="70" r="12" fill="#F48FB1"/><path d="M84 70h24M96 58c-6 8-6 16 0 24" fill="none"/>'+
  '<path d="M122 84l6-26 12 26z" fill="#8FD19A"/><path d="M126 40l10-16 4 18z" fill="#FF8FAB"/><path d="M134 42l-8 40" fill="none"/>');},
 work:function(c){return sv(
  '<rect x="24" y="56" width="90" height="8" rx="2" fill="#C1814A"/><line x1="32" y1="64" x2="32" y2="84"/><line x1="106" y1="64" x2="106" y2="84"/>'+
  '<rect x="30" y="16" width="80" height="30" rx="2" fill="#E8D5B5"/><path d="M44 22v18M54 22l6 16M70 24h12v6H70zM90 22v18" fill="none"/><circle cx="96" cy="30" r="5" fill="#ADB5BD"/>'+
  '<circle cx="134" cy="70" r="13" fill="#ADB5BD"/><circle cx="134" cy="70" r="4" fill="#FFC93C"/><path d="M134 54v-4M150 70h4M118 70h-4M134 86" />'+
  '<rect x="56" y="46" width="22" height="10" rx="2" fill="#E63946"/>');},
 attic:function(c){return sv(
  '<path d="M10 30L80 4l70 26" fill="none" stroke="#8A5A2E" stroke-width="4"/><path d="M40 18v66M120 18v66" stroke="#8A5A2E" stroke-width="4"/>'+
  '<circle cx="80" cy="30" r="12" fill="#DDF4FF"/><path d="M68 30h24M80 18v24" />'+
  '<rect x="54" y="58" width="52" height="26" rx="3" fill="#B5651D"/><path d="M54 58c0-14 52-14 52 0z" fill="#C97A2B"/><rect x="74" y="56" width="12" height="12" rx="2" fill="#FFC93C"/>'+
  '<path d="M54 66h52" stroke="#E0A800"/>');}
};

    var ROOM_NAME = { hall: 'შესასვლელი', living: 'მისაღები', kitchen: 'სამზარეულო', bedroom: 'საძინებელი', dining: 'სასადილო', bath: 'აბაზანა', library: 'ბიბლიოთეკა', play: 'სათამაშო ოთახი', work: 'სახელოსნო', attic: 'სხვენი' };
    var ROOM_ORDER = ['living', 'kitchen', 'bedroom', 'dining', 'bath', 'library', 'play', 'work'];
    // სახურავი [ფერი, ჩრდილი] და გარემო: grass | snow | sand | trunk | rock | water
    var EXT = [
        ['#C2372B', '#9E2A20', 'grass'], ['#B5651D', '#8A4B12', 'grass'], ['#5C3B1E', '#3E2712', 'grass'], ['#2F7BF5', '#1F5FCC', 'grass'],
        ['#1E8A4C', '#146B38', 'grass'], ['#FF8FAB', '#E26C8B', 'grass'], ['#588157', '#3A5A40', 'grass'], ['#C1121F', '#8D0801', 'grass'],
        ['#6B8E23', '#4F6B16', 'trunk'], ['#8B5CF6', '#6D3FD8', 'grass'], ['#E63946', '#FFC93C', 'grass'], ['#FFFFFF', '#CFE7F2', 'snow'],
        ['#4A3B66', '#2E2440', 'grass'], ['#003049', '#001D2E', 'grass'], ['#2F7BF5', '#FFFFFF', 'sand'], ['#6C757D', '#495057', 'grass'],
        ['#C2372B', '#2F4BC4', 'grass'], ['#2B2622', '#4A3B30', 'sand'], ['#7B2CBF', '#5A189A', 'grass'], ['#3C096C', '#240046', 'grass'],
        ['#6A040F', '#9D0208', 'rock'], ['#E0A800', '#B58700', 'grass']
    ];
    // სახლის თემა: დეკორი ოთახებში, დროშა სახურავზე
    var PROPS = [
        ['⚽', '📚', '🚲', '🦸', '🎮', '🛹'], ['🐓', '🌻', '🧺', '🍇', '🐐', '🥚'], ['🍄', '🦉', '🌲', '🦊', '🌰', '🦔'],
        ['🏆', '⚽', '👟', '🥅', '🎽', '🏅'], ['📐', '🧮', '✏️', '📏', '🔢', '📓'], ['🧁', '🎂', '🍩', '🍪', '🍰', '🍭'],
        ['🦁', '🐘', '🦒', '🐒', '🦓', '🐧'], ['🐮', '🐷', '🚜', '🌽', '🐑', '🥕'], ['🐿️', '🪺', '🍂', '🐦', '🌳', '🔭'],
        ['🎸', '🎹', '🥁', '🎺', '🎻', '🎤'], ['🤡', '🎪', '🤹', '🎩', '🎈', '🎠'], ['🎅', '🎄', '🎁', '❄️', '🦌', '⛄'],
        ['🔍', '🕵️', '🗝️', '📜', '👣', '🕯️'], ['🚂', '🎫', '🧳', '🛤️', '🚦', '🔔'], ['⚓', '⛵', '🐚', '🦀', '🧭', '🛟'],
        ['💡', '⚙️', '🔧', '🤖', '🔋', '🧲'], ['🦸', '🛡️', '⚡', '🦇', '🏙️', '💥'], ['🏴‍☠️', '🦜', '💰', '🗺️', '⚓', '🗡️'],
        ['📚', '🧚', '🦄', '🏰', '🐸', '👑'], ['🔮', '🪄', '🧹', '🌙', '🐈‍⬛', '⚗️'], ['🐉', '🔥', '💎', '🪨', '⚔️', '🌋'],
        ['🎓', '🏫', '🛎️', '📚', '🌍', '🏅']
    ];
    function wallProp(h, d) { var p = PROPS[h]; return p[(d * 2) % p.length]; }
    function floorProp(h, d) { var p = PROPS[h]; return p[(d * 2 + 1) % p.length]; }
    // ოთახების კარები — ოთახის ტიპის მიხედვით (შესასვლელი და სხვენი სახლის თემის კარია)
    var ROOM_DOOR = {
        living:  { shape: 'rect', tex: 'panels', door: '#7A5230', door2: '#5C3D22' },
        kitchen: { shape: 'rect', tex: 'plain',  door: '#8FD19A', door2: '#5FAE6E', x: 'port' },
        bedroom: { shape: 'arch', tex: 'plain',  door: '#9FC9F0', door2: '#6FA6DD', x: 'star' },
        dining:  { shape: 'rect', tex: 'panels', door: '#C1814A', door2: '#8A5A2E', x: 'panes' },
        bath:    { shape: 'rect', tex: 'plain',  door: '#FFFFFF', door2: '#DCD6CE', x: 'frost' },
        library: { shape: 'arch', tex: 'books',  door: '#6F4518', door2: '#4A2E12' },
        play:    { shape: 'arch', tex: 'stripes', door: '#FFC93C', door2: '#FF8FAB' },
        work:    { shape: 'rect', tex: 'metal',  door: '#ADB5BD', door2: '#6C757D' }
    };
    function doorTheme(h, d) {
        var base = H[h].th, t = roomType(h, d), r = ROOM_DOOR[t];
        if (!r) return base;
        return Object.assign({}, base, { shape: r.shape, tex: r.tex, door: r.door, door2: r.door2, x: r.x });
    }
    // ── თემატური ოთახები (viewBox 0 0 160 100, იატაკი y=84) ──
    var TROOMS = {
        locker: function () { // გასახდელი: კარადები მაისურებით, სკამი, ბურთი
            var l = '';
            for (var i = 0; i < 3; i++) { var x = 14 + i * 30; l += '<rect x="' + x + '" y="16" width="28" height="68" rx="2" fill="#6FA8DC"/><path d="M' + (x + 6) + ' 24h16M' + (x + 6) + ' 28h16"/><circle cx="' + (x + 22) + '" cy="52" r="1.8" fill="' + INK + '"/>'; }
            return sv(l + '<rect x="46" y="16" width="28" height="68" rx="2" fill="#2B2622"/>' + JERSEY(48, 30, 24, '#E63946', '7')
                + '<rect x="100" y="64" width="50" height="7" rx="3" fill="#C1814A"/><path d="M106 71v13M144 71v13"/>'
                + BALL(118, 50, 9) + JERSEY(126, 14, 26, '#2F7BF5', '10'));
        },
        trophy: function () { // თასების ოთახი
            return sv('<rect x="20" y="22" width="120" height="6" rx="2" fill="#C1814A"/><rect x="20" y="52" width="120" height="6" rx="2" fill="#C1814A"/>'
                + CUP(30, 4, 18) + CUP(66, 0, 24) + CUP(108, 6, 16) + MEDAL(40, 32) + MEDAL(80, 32) + MEDAL(120, 32)
                + '<rect x="24" y="60" width="28" height="24" rx="2" fill="#FFFFFF"/><path d="M28 66h20M28 72h14"/>' + BALL(128, 74, 9));
        },
        music: function () { // მუსიკის ოთახი: პიანინო, გიტარა, ნოტები
            var k = ''; for (var i = 0; i < 9; i++) k += '<rect x="' + (22 + i * 7) + '" y="58" width="7" height="12" fill="#fff"/>';
            for (var j = 0; j < 8; j++) if (j % 7 !== 2 && j % 7 !== 6) k += '<rect x="' + (27 + j * 7) + '" y="58" width="4" height="7" fill="' + INK + '"/>';
            return sv('<rect x="16" y="34" width="76" height="24" rx="3" fill="#3C2A1E"/>' + k + '<rect x="16" y="70" width="76" height="14" rx="2" fill="#3C2A1E"/><path d="M22 70v14M86 70v14"/>'
                + '<path d="M118 84l6-22M138 84l-6-22" /><ellipse cx="128" cy="56" rx="10" ry="12" fill="#E2590F"/><ellipse cx="128" cy="40" rx="7" ry="8" fill="#E2590F"/><rect x="126" y="12" width="4" height="30" fill="#8A5A2E"/><circle cx="128" cy="54" r="3" fill="' + INK + '"/>'
                + NOTE(40, 10) + NOTE(62, 18) + NOTE(100, 8));
        },
        cabin: function () { // კაიუტა: ილუმინატორები, საჭე, ჰამაკი
            return sv('<circle cx="34" cy="28" r="11" fill="#9ED8FF"/><circle cx="34" cy="28" r="14" fill="none" stroke="#ADB5BD" stroke-width="4"/>'
                + '<circle cx="78" cy="28" r="11" fill="#9ED8FF"/><circle cx="78" cy="28" r="14" fill="none" stroke="#ADB5BD" stroke-width="4"/>'
                + WHEEL(128, 30, 16) + '<path d="M14 54c20 18 60 18 80 0" fill="#F4D58D"/><path d="M14 54L8 46M94 54l6-8"/>'
                + RING(124, 66, 11) + '<rect x="108" y="76" width="34" height="8" rx="2" fill="#8A5A2E"/>');
        },
        treasure: function () { // განძის ოთახი: ზარდახშა, რუკა, კასრი
            return sv('<rect x="20" y="12" width="44" height="32" rx="2" fill="#F4E1B0"/><path d="M28 22c6 6 14-4 22 4M44 34l6-4" fill="none" stroke-dasharray="3 3"/><path d="M48 26l4 4M52 26l-4 4" stroke="#C2372B"/>'
                + '<rect x="50" y="58" width="56" height="26" rx="3" fill="#8A5A2E"/><path d="M50 58c0-16 56-16 56 0z" fill="#A0652D"/><circle cx="66" cy="50" r="4" fill="#FFC93C"/><circle cx="78" cy="47" r="4" fill="#FFC93C"/><circle cx="90" cy="50" r="4" fill="#FFC93C"/><rect x="72" y="62" width="12" height="10" rx="2" fill="#FFC93C"/>'
                + '<ellipse cx="130" cy="84" rx="14" ry="3" fill="#6B4226"/><path d="M116 84V54c0-4 28-4 28 0v30" fill="#A0652D"/><path d="M116 62h28M116 76h28" stroke="#ADB5BD" stroke-width="3"/>'
                + FLAG(120, 10));
        },
        xmas: function () { // საახალწლო: ნაძვის ხე, საჩუქრები, ბუხარი
            return sv('<path d="M44 12l22 30H54l16 22H18l16-22H22z" fill="#2D6A4F"/><rect x="40" y="64" width="8" height="10" fill="#8A5A2E"/><path d="M44 6l2 4 4 .5-3 3 1 4-4-2-4 2 1-4-3-3 4-.5z" fill="#FFC93C"/>'
                + '<circle cx="38" cy="40" r="2.6" fill="#E63946"/><circle cx="52" cy="50" r="2.6" fill="#2F7BF5"/><circle cx="34" cy="58" r="2.6" fill="#FFC93C"/><circle cx="56" cy="34" r="2.6" fill="#FFC93C"/>'
                + GIFT(22, 70, 14, '#E63946') + GIFT(56, 72, 12, '#2F7BF5')
                + '<rect x="94" y="34" width="56" height="50" rx="2" fill="#C2372B"/><path d="M94 46h56M94 58h56M94 70h56M110 34v12M130 46v12M110 58v12M130 70v14" stroke="#9E2A20"/><rect x="108" y="56" width="28" height="28" rx="3" fill="#2B2622"/><path d="M114 84c0-10 8-14 8-20c4 6 8 10 8 20z" fill="#FF9F1C"/>'
                + '<rect x="90" y="30" width="64" height="6" rx="2" fill="#8A5A2E"/><path d="M104 36v8h6l-2 4" fill="#E63946"/><path d="M138 36v8h6l-2 4" fill="#2F7BF5"/>');
        },
        office: function () { // დეტექტივის ოფისი: დაფა ძაფებით, მაგიდა, ლამპა
            return sv('<rect x="18" y="10" width="64" height="40" rx="2" fill="#C9A27A"/><rect x="24" y="16" width="14" height="12" fill="#fff"/><rect x="58" y="14" width="16" height="12" fill="#fff"/><rect x="40" y="34" width="14" height="12" fill="#fff"/>'
                + '<path d="M31 22l35-2M31 22l16 18M66 20L47 40" stroke="#E63946" stroke-width="1.6"/><circle cx="31" cy="17" r="1.8" fill="#E63946"/><circle cx="66" cy="15" r="1.8" fill="#E63946"/><circle cx="47" cy="35" r="1.8" fill="#E63946"/>'
                + '<rect x="94" y="56" width="60" height="7" rx="2" fill="#6B4226"/><path d="M100 63v21M148 63v21"/><path d="M104 56l4-14h8l-6-4"/><path d="M100 42h14l-3-8h-8z" fill="#2D6A4F"/>'
                + MAGNI(132, 46, 8) + '<path d="M22 84c0-2 4-4 6-2M36 84c0-2 4-4 6-2M50 84c0-2 4-4 6-2" stroke="#8D8079"/>');
        },
        bakery: function () { // საცხობი: ღუმელი, ტორტი, ნამცხვრები
            return sv('<rect x="14" y="30" width="52" height="54" rx="4" fill="#E9ECEF"/><rect x="20" y="48" width="40" height="28" rx="12" fill="#2B2622"/><path d="M26 70c4-8 8-8 14-4c4-6 10-6 14 2" fill="#FF9F1C" stroke="none"/><path d="M24 38h32"/>'
                + '<rect x="78" y="58" width="40" height="26" rx="2" fill="#FFFFFF"/><path d="M80 58h36"/>'
                + CAKE(98, 30, 28) + '<rect x="122" y="30" width="34" height="5" rx="2" fill="#C1814A"/>' + CUPC(128, 16, 10, '#FF8FAB') + CUPC(142, 16, 10, '#8FD19A')
                + '<rect x="122" y="56" width="34" height="5" rx="2" fill="#C1814A"/>' + CUPC(128, 42, 10, '#FFC93C') + CUPC(142, 42, 10, '#9FC9F0'));
        },
        barn: function () { // ბეღელი: თივა, ფოცხი, ვედრო, წიწილები
            return sv('<rect x="14" y="56" width="40" height="28" rx="6" fill="#F4D58D"/><path d="M18 64h32M18 72h32M18 80h32" stroke="#C9A24A"/><rect x="26" y="34" width="34" height="22" rx="6" fill="#F4D58D"/><path d="M30 42h26M30 50h26" stroke="#C9A24A"/>'
                + '<path d="M80 84L92 22"/><path d="M86 22h12M88 22l-2-8M92 22v-8M96 22l2-8"/>'
                + '<path d="M106 66h22l-3 18h-16z" fill="#ADB5BD"/><path d="M106 66c0-10 22-10 22 0" fill="none"/>'
                + '<circle cx="142" cy="78" r="6" fill="#FFD166"/><circle cx="146" cy="72" r="4" fill="#FFD166"/><path d="M149 72l3 1-3 1" fill="#FF9F1C"/>'
                + '<rect x="98" y="12" width="44" height="26" rx="2" fill="#9ED8FF"/><path d="M98 12l44 26M142 12L98 38" stroke="#8A5A2E" stroke-width="3"/>');
        },
        lab: function () { // ლაბორატორია: კოლბები, რობოტი, კბილანები
            return sv('<rect x="14" y="56" width="70" height="7" rx="2" fill="#ADB5BD"/><path d="M20 63v21M78 63v21"/>'
                + '<path d="M24 56V44l-6-10h16l-6 10v12z" fill="#8FD19A"/><path d="M44 56v-22h8v22z" fill="#9FC9F0"/><circle cx="66" cy="48" r="8" fill="#FF8FAB"/><path d="M62 40h8v-8h-8z" fill="none"/>'
                + GEAR(40, 18, 10) + GEAR(62, 22, 7)
                + '<rect x="106" y="40" width="30" height="26" rx="5" fill="#ADB5BD"/><rect x="112" y="24" width="18" height="14" rx="3" fill="#ADB5BD"/><circle cx="117" cy="31" r="2.2" fill="#2F7BF5"/><circle cx="125" cy="31" r="2.2" fill="#2F7BF5"/><path d="M121 24v-6"/><circle cx="121" cy="16" r="2.4" fill="#E63946"/><path d="M112 66v18M130 66v18M106 48h-8M136 48h8"/><rect x="112" y="46" width="18" height="8" rx="2" fill="#FFC93C"/>');
        },
        potion: function () { // ჯადოსნური ოთახი: ქვაბი, წამლები, ცოცხი
            return sv('<path d="M36 60c0 16 44 16 44 0z" fill="#2B2622"/><path d="M34 58h48" stroke-width="4"/><ellipse cx="58" cy="56" rx="22" ry="4" fill="#8FD19A"/><circle cx="50" cy="48" r="4" fill="#B8F2C2"/><circle cx="62" cy="42" r="3" fill="#B8F2C2"/><circle cx="68" cy="50" r="2.5" fill="#B8F2C2"/><path d="M44 76l-4 8M72 76l4 8"/>'
                + '<rect x="96" y="22" width="54" height="5" rx="2" fill="#8A5A2E"/><path d="M102 22v-8h6v8z" fill="#8B5CF6"/><circle cx="118" cy="16" r="6" fill="#E63946"/><path d="M130 22l2-12h6l2 12z" fill="#2F7BF5"/><circle cx="144" cy="17" r="5" fill="#FFC93C"/>'
                + '<path d="M128 84L146 36" stroke="#8A5A2E" stroke-width="3"/><path d="M120 84c2-8 6-12 10-12c4 2 4 8 2 12z" fill="#F4D58D"/>'
                + '<path d="M20 20l2 5 5 1-4 3 1 5-4-3-4 3 1-5-4-3 5-1z" fill="#FFC93C"/><path d="M86 34a8 8 0 1 0 6-10a6 6 0 1 1-6 10z" fill="#FFE7A3"/>');
        },
        classroom: function () { // საკლასო: დაფა მაგალითებით, გლობუსი, მერხი
            return sv('<rect x="22" y="12" width="78" height="42" rx="2" fill="#2D6A4F"/><text x="30" y="30" font-size="10" fill="#fff" stroke="none" font-family="sans-serif">2+3=5</text><text x="56" y="46" font-size="10" fill="#fff" stroke="none" font-family="sans-serif">7×2=14</text><path d="M22 58h78" stroke="#8A5A2E" stroke-width="4"/>'
                + '<circle cx="128" cy="36" r="13" fill="#9ED8FF"/><path d="M120 30c6 2 8 8 4 12M132 26c-2 6 4 10 8 8" fill="#8FD19A"/><path d="M128 49v12M118 64h20"/><path d="M114 36a14 14 0 0 1 28 0" fill="none"/>'
                + '<rect x="40" y="64" width="44" height="6" rx="2" fill="#C1814A"/><path d="M46 70v14M78 70v14"/><path d="M50 64l8-6 8 6" fill="#FFFFFF"/>');
        },
        hearth: function () { // ბუხრიანი ოთახი (ტყის ქოხი): ბუხარი, შეშა, რქები
            return sv('<rect x="40" y="30" width="80" height="54" rx="2" fill="#8D8079"/><path d="M40 44h80M40 58h80M40 72h80M60 30v14M90 44v14M60 58v14M100 72v12" stroke="#6E625C"/><rect x="58" y="54" width="44" height="30" rx="3" fill="#2B2622"/><path d="M66 84c0-12 8-14 10-22c6 6 10 12 10 22z" fill="#FF9F1C"/><path d="M74 84c0-6 4-8 6-12c2 4 4 6 4 12z" fill="#FFD166"/>'
                + '<rect x="36" y="26" width="88" height="6" rx="2" fill="#6B4226"/><path d="M70 14c-4-6-10-6-12 0M90 14c4-6 10-6 12 0M80 20v6" stroke="#8A5A2E" stroke-width="3" fill="none"/>'
                + '<rect x="128" y="66" width="24" height="7" rx="3.5" fill="#A0652D"/><rect x="132" y="76" width="24" height="7" rx="3.5" fill="#A0652D"/><rect x="10" y="72" width="22" height="12" rx="4" fill="#C2372B"/>');
        },
        garden: function () { // ზამთრის ბაღი (სოფელი): ქოთნები, მორწყვა, მზესუმზირა
            return sv('<rect x="16" y="66" width="18" height="18" rx="2" fill="#C1814A"/><path d="M25 66V40" stroke="#2D6A4F"/><circle cx="25" cy="34" r="8" fill="#FFC93C"/><circle cx="25" cy="34" r="3.5" fill="#8A5A2E"/>'
                + '<rect x="44" y="70" width="16" height="14" rx="2" fill="#C1814A"/><path d="M52 70c-8-10-4-18 0-22c4 4 8 12 0 22z" fill="#8FD19A"/>'
                + '<path d="M78 66h20l-2 18H80z" fill="#2F7BF5"/><path d="M98 70l14-8M78 70c-6 0-6-8 0-8" fill="none"/>'
                + '<rect x="112" y="14" width="40" height="34" rx="2" fill="#DDF4FF"/><path d="M132 14v34M112 31h40"/><path d="M112 48h40" stroke-width="4"/><rect x="118" y="56" width="30" height="5" rx="2" fill="#C1814A"/><circle cx="126" cy="51" r="4" fill="#E63946"/><circle cx="138" cy="51" r="4" fill="#8FD19A"/>');
        },
        pets: function () { // ზოოლოგის ოთახი: აკვარიუმი, ჩიტის გალია, ძვალი
            return sv('<rect x="14" y="40" width="54" height="34" rx="3" fill="#9ED8FF"/><path d="M14 46h54"/><path d="M26 60c4-4 10-4 12 0c-2 4-8 4-12 0zM38 60l4-3v6z" fill="#FF9F1C"/><path d="M50 66c3-3 7-3 9 0c-2 3-6 3-9 0z" fill="#E63946"/><path d="M20 74v10h42V74" fill="#8A5A2E"/>'
                + '<path d="M96 54V26a16 10 0 0 1 32 0v28z" fill="none"/><path d="M104 54V22M112 54V18M120 54V22" stroke-width="1.6"/><path d="M92 54h40" stroke-width="4"/><path d="M112 18V8"/><circle cx="110" cy="40" r="5" fill="#FFD166"/><path d="M114 40l3 1-3 1" fill="#FF9F1C"/>'
                + '<path d="M140 84v-16M132 68h16" /><ellipse cx="80" cy="82" rx="10" ry="3" fill="#E9ECEF"/>');
        },
        hq: function () { // გმირის შტაბი: ეკრანი ქალაქით, მოსასხამი, ფარი
            return sv('<rect x="16" y="12" width="76" height="44" rx="4" fill="#003049"/><path d="M22 52V40h8v-8h8v20M44 52V30h10v22M60 52V36h8v-6h8v22" fill="#2F7BF5"/><circle cx="80" cy="22" r="4" fill="#FFC93C"/>'
                + '<rect x="40" y="60" width="28" height="6" rx="2" fill="#ADB5BD"/><path d="M54 66v18"/>'
                + '<path d="M118 20h20" /><path d="M120 20c-4 20-6 40-2 60h24c4-20 2-40-2-60z" fill="#E63946"/><path d="M122 20c4 6 12 6 16 0" fill="#C2372B"/>'
                + SHIELD(140, 44, 14) );
        },
        hoard: function () { // დრაკონის განძი: ოქროს გროვა, კვერცხები
            return sv('<path d="M10 84c10-24 50-30 70-18c14-10 40-6 60 18z" fill="#FFC93C"/><circle cx="36" cy="72" r="4" fill="#FFE066"/><circle cx="60" cy="66" r="4" fill="#FFE066"/><circle cx="100" cy="72" r="4" fill="#FFE066"/><circle cx="120" cy="76" r="4" fill="#FFE066"/>'
                + '<ellipse cx="78" cy="56" rx="10" ry="13" fill="#8FD19A"/><path d="M72 50l4 4 4-4 4 4" fill="none" stroke="#2D6A4F"/><ellipse cx="100" cy="60" rx="8" ry="10" fill="#C77DFF"/>'
                + '<path d="M24 66l6-14 8 10z" fill="#9ED8FF"/><path d="M128 60l10-10 6 12z" fill="#E63946"/>'
                + '<path d="M120 14l10 6-10 6M120 14v26" fill="#E63946"/>');
        },
        reading: function () { // მეზღაპრის ოთახი: სავარძელი, დიდი წიგნი, ციხის სურათი
            return sv('<path d="M30 84V52c0-12 40-12 40 0v32" fill="#8B5CF6"/><rect x="24" y="60" width="12" height="24" rx="5" fill="#6D3FD8"/><rect x="64" y="60" width="12" height="24" rx="5" fill="#6D3FD8"/><rect x="36" y="64" width="28" height="12" rx="3" fill="#A78BFA"/>'
                + '<path d="M92 70l20-6 20 6v14l-20-6-20 6z" fill="#FFFFFF"/><path d="M112 64v20"/><path d="M92 70v-4l20-6 20 6v4" fill="#E2590F"/>'
                + '<rect x="96" y="10" width="44" height="34" rx="2" fill="#DDF4FF"/><path d="M104 44V28h6v-4h4v4h8v-4h4v4h6v16" fill="#ADB5BD"/><path d="M114 44v-8h8v8" fill="#8A5A2E"/>'
                + '<path d="M14 24l2 5 5 1-4 3 1 5-4-3-4 3 1-5-4-3 5-1z" fill="#FFC93C"/>');
        },
        station: function () { // მემანქანის ოთახი: მატარებლის მოდელი, საათი, ქუდი
            return sv('<circle cx="80" cy="24" r="12" fill="#FFFFFF"/><path d="M80 24v-7M80 24l5 3"/><path d="M80 12V6"/>'
                + '<path d="M14 80h132" stroke-width="2"/><path d="M14 84h132" stroke-dasharray="4 6"/>'
                + '<rect x="24" y="56" width="34" height="20" rx="3" fill="#C2372B"/><rect x="44" y="44" width="14" height="14" rx="2" fill="#C2372B"/><rect x="28" y="48" width="6" height="8" fill="#2B2622"/><circle cx="32" cy="78" r="4" fill="' + INK + '"/><circle cx="50" cy="78" r="4" fill="' + INK + '"/><path d="M24 64h-6l2-6" fill="#FFC93C"/>'
                + '<rect x="64" y="60" width="28" height="16" rx="2" fill="#2F7BF5"/><circle cx="70" cy="78" r="4" fill="' + INK + '"/><circle cx="86" cy="78" r="4" fill="' + INK + '"/><rect x="96" y="60" width="28" height="16" rx="2" fill="#1E8A4C"/><circle cx="102" cy="78" r="4" fill="' + INK + '"/><circle cx="118" cy="78" r="4" fill="' + INK + '"/>'
                + '<path d="M128 40h20l-2-8h-16z" fill="#003049"/><path d="M126 40h24" stroke-width="3"/>');
        },
        nest: function () { // სახლი ხეზე: თოკის კიბე, ტელესკოპი, სახლაკი ჩიტებისთვის
            return sv('<path d="M20 10v74M36 10v74"/><path d="M20 22h16M20 36h16M20 50h16M20 64h16M20 78h16" stroke="#8A5A2E" stroke-width="3"/>'
                + '<rect x="58" y="14" width="40" height="30" rx="2" fill="#9ED8FF"/><path d="M78 14v30M58 29h40"/><path d="M60 38c6-4 12-4 18 0" stroke="#74C69D" fill="none"/>'
                + '<path d="M108 84l10-30M128 84l-10-30"/><rect x="104" y="40" width="34" height="9" rx="4" fill="#ADB5BD" transform="rotate(-20 121 44)"/>'
                + '<path d="M136 26l10-8 10 8v14h-20z" fill="#E2590F"/><circle cx="146" cy="31" r="3" fill="' + INK + '"/><path d="M146 40v8"/>');
        },
        principal: function () { // დირექტორის კაბინეტი: მაგიდა, დიპლომები, ზარი
            return sv('<rect x="40" y="54" width="80" height="30" rx="2" fill="#6B4226"/><path d="M40 62h80"/><rect x="56" y="44" width="22" height="10" rx="1" fill="#FFFFFF"/><path d="M98 54c0-6 8-6 8 0z" fill="#FFC93C"/><path d="M102 48v-3"/>'
                + '<rect x="20" y="12" width="26" height="20" rx="1" fill="#FFF8E1"/><path d="M26 20h14M26 25h10"/><circle cx="40" cy="28" r="2.5" fill="#E63946"/>'
                + '<rect x="114" y="12" width="26" height="20" rx="1" fill="#FFF8E1"/><path d="M120 20h14M120 25h10"/><circle cx="134" cy="28" r="2.5" fill="#E63946"/>'
                + '<path d="M70 14l10-6 10 6-10 6z" fill="#2B2622"/><path d="M90 14v8"/>'
                + '<path d="M14 84V58h16v26" fill="#2D6A4F"/><path d="M22 58c-6-8-2-16 0-18c2 2 6 10 0 18z" fill="#8FD19A"/>');
        },
        ring: function () { // ჯამბაზის ოთახი: არენა, ბურთი, სკამი, ბუშტები
            return sv('<ellipse cx="80" cy="80" rx="62" ry="8" fill="#E63946"/><ellipse cx="80" cy="79" rx="52" ry="5" fill="#FFC93C"/>'
                + '<circle cx="56" cy="62" r="12" fill="#2F7BF5"/><path d="M44 62h24M56 50c6 6 6 18 0 24" stroke="#fff" fill="none"/>'
                + '<path d="M96 74V58h22v16" fill="#8B5CF6"/><path d="M94 58h26" stroke-width="4"/>'
                + '<path d="M130 40v-14M138 44V24M146 40V28"/><circle cx="130" cy="20" r="7" fill="#E63946"/><circle cx="138" cy="16" r="7" fill="#FFC93C"/><circle cx="146" cy="22" r="7" fill="#8FD19A"/>'
                + '<path d="M14 12l20 10 20-10 20 10 20-10 20 10 20-10" fill="none" stroke="#E63946"/>');
        }
    };
    // პატარა დახატული ნივთები (პლაკატებისა და იატაკისთვის)
    function BALL(x, y, r) { return '<circle cx="' + x + '" cy="' + y + '" r="' + r + '" fill="#fff"/><path d="M' + x + ' ' + (y - r * .45) + 'l' + (r * .42) + ' ' + (r * .3) + 'l-' + (r * .16) + ' ' + (r * .5) + 'h-' + (r * .52) + 'l-' + (r * .16) + '-' + (r * .5) + 'z" fill="' + INK + '"/>'; }
    function JERSEY(x, y, w, col, num) { var h = w * 1.05; return '<path d="M' + (x + w * .3) + ' ' + y + 'l-' + (w * .3) + ' ' + (w * .18) + 'l' + (w * .1) + ' ' + (w * .26) + 'l' + (w * .12) + '-' + (w * .06) + 'v' + (h * .62) + 'h' + (w * .56) + 'v-' + (h * .62) + 'l' + (w * .12) + ' ' + (w * .06) + 'l' + (w * .1) + '-' + (w * .26) + 'l-' + (w * .3) + '-' + (w * .18) + 'c-' + (w * .06) + ' ' + (w * .12) + '-' + (w * .34) + ' ' + (w * .12) + '-' + (w * .4) + ' 0z" fill="' + col + '"/><text x="' + (x + w / 2) + '" y="' + (y + h * .62) + '" font-size="' + (w * .34) + '" text-anchor="middle" fill="#fff" stroke="none" font-family="sans-serif" font-weight="900">' + num + '</text>'; }
    function CUP(x, y, w) { return '<path d="M' + x + ' ' + (y + 2) + 'h' + w + 'v' + (w * .4) + 'c0 ' + (w * .4) + '-' + w + ' ' + (w * .4) + '-' + w + ' 0z" fill="#FFC93C"/><path d="M' + (x + w * .4) + ' ' + (y + w * .78) + 'h' + (w * .2) + 'v' + (w * .2) + 'h-' + (w * .2) + 'zM' + (x + w * .2) + ' ' + (y + w * .98) + 'h' + (w * .6) + 'v' + (w * .12) + 'h-' + (w * .6) + 'z" fill="#E0A800"/>'; }
    function MEDAL(x, y) { return '<path d="M' + (x - 4) + ' ' + y + 'l4 8 4-8" fill="none" stroke="#E63946"/><circle cx="' + x + '" cy="' + (y + 12) + '" r="5" fill="#FFC93C"/>'; }
    function NOTE(x, y) { return '<ellipse cx="' + x + '" cy="' + (y + 14) + '" rx="4" ry="3" fill="' + INK + '"/><path d="M' + (x + 4) + ' ' + (y + 14) + 'V' + y + 'l8 3"/>'; }
    function WHEEL(x, y, r) { var o = '<circle cx="' + x + '" cy="' + y + '" r="' + r + '" fill="none" stroke="#8A5A2E" stroke-width="4"/><circle cx="' + x + '" cy="' + y + '" r="4" fill="#8A5A2E"/>'; for (var i = 0; i < 8; i++) { var a = i * Math.PI / 4; o += '<path d="M' + x + ' ' + y + 'L' + (x + Math.cos(a) * (r + 6)).toFixed(1) + ' ' + (y + Math.sin(a) * (r + 6)).toFixed(1) + '" stroke="#8A5A2E" stroke-width="3"/>'; } return o; }
    function RING(x, y, r) { return '<circle cx="' + x + '" cy="' + y + '" r="' + r + '" fill="none" stroke="#E63946" stroke-width="6"/><path d="M' + (x - r) + ' ' + y + 'h6M' + (x + r - 6) + ' ' + y + 'h6M' + x + ' ' + (y - r) + 'v6M' + x + ' ' + (y + r - 6) + 'v6" stroke="#fff" stroke-width="5"/>'; }
    function FLAG(x, y) { return '<path d="M' + x + ' ' + (y + 40) + 'V' + y + '"/><path d="M' + x + ' ' + y + 'h22v14h-22z" fill="' + INK + '"/><circle cx="' + (x + 11) + '" cy="' + (y + 6) + '" r="3" fill="#fff" stroke="none"/>'; }
    function GIFT(x, y, w, col) { return '<rect x="' + x + '" y="' + y + '" width="' + w + '" height="' + w + '" rx="1.5" fill="' + col + '"/><path d="M' + (x + w / 2) + ' ' + y + 'v' + w + 'M' + x + ' ' + (y + w / 2) + 'h' + w + '" stroke="#FFC93C" stroke-width="2.4"/>'; }
    function MAGNI(x, y, r) { return '<circle cx="' + x + '" cy="' + y + '" r="' + r + '" fill="#DDF4FF"/><path d="M' + (x + r * .7) + ' ' + (y + r * .7) + 'l' + r + ' ' + r + '" stroke-width="4"/>'; }
    function CAKE(x, y, w) { return '<rect x="' + (x - w / 2) + '" y="' + (y + 8) + '" width="' + w + '" height="12" rx="3" fill="#FF8FAB"/><rect x="' + (x - w / 3) + '" y="' + y + '" width="' + (w * 2 / 3) + '" height="9" rx="3" fill="#FFF"/><path d="M' + x + ' ' + y + 'v-6"/><circle cx="' + x + '" cy="' + (y - 8) + '" r="2" fill="#FF9F1C"/><path d="M' + (x - w / 2 - 4) + ' ' + (y + 20) + 'h' + (w + 8) + 'M' + x + ' ' + (y + 20) + 'v8" />'; }
    function CUPC(x, y, w, col) { return '<path d="M' + (x - w / 2) + ' ' + (y + 6) + 'h' + w + 'l-2 ' + (w * .7) + 'h-' + (w - 4) + 'z" fill="#C1814A"/><path d="M' + (x - w / 2 - 1) + ' ' + (y + 6) + 'c0-8 ' + (w + 2) + '-8 ' + (w + 2) + ' 0z" fill="' + col + '"/>'; }
    function GEAR(x, y, r) { var o = ''; for (var i = 0; i < 8; i++) { var a = i * Math.PI / 4; o += '<rect x="' + (x - 2.5) + '" y="' + (y - r - 3) + '" width="5" height="6" fill="#ADB5BD" transform="rotate(' + (i * 45) + ' ' + x + ' ' + y + ')"/>'; } return o + '<circle cx="' + x + '" cy="' + y + '" r="' + r + '" fill="#ADB5BD"/><circle cx="' + x + '" cy="' + y + '" r="' + (r * .35) + '" fill="#fff"/>'; }
    function SHIELD(x, y, w) { return '<path d="M' + (x - w / 2) + ' ' + (y - w / 2) + 'h' + w + 'v' + (w * .5) + 'c0 ' + (w * .5) + '-' + (w / 2) + ' ' + (w * .7) + '-' + (w / 2) + ' ' + (w * .7) + 's-' + (w / 2) + '-' + (w * .2) + '-' + (w / 2) + '-' + (w * .7) + 'z" fill="#2F7BF5"/><path d="M' + x + ' ' + (y - w * .3) + 'l2 4 4 .5-3 3 1 4-4-2-4 2 1-4-3-3 4-.5z" fill="#FFC93C"/>'; }

    var TNAME = { locker: 'გასახდელი', trophy: 'თასების ოთახი', music: 'მუსიკის ოთახი', cabin: 'კაიუტა', treasure: 'განძის ოთახი', xmas: 'საახალწლო ოთახი', office: 'დეტექტივის ოფისი', bakery: 'საცხობი', barn: 'ბეღელი', lab: 'ლაბორატორია', potion: 'ჯადოსნური ოთახი', classroom: 'საკლასო ოთახი', hearth: 'ბუხრიანი ოთახი', garden: 'ზამთრის ბაღი', pets: 'ცხოველების ოთახი', hq: 'შტაბი', hoard: 'განძის გროვა', reading: 'ზღაპრების ოთახი', station: 'მატარებლების ოთახი', nest: 'სათვალთვალო', principal: 'კაბინეტი', ring: 'არენა' };
    for (var tn in TNAME) ROOM_NAME[tn] = TNAME[tn];
    // სახლების თემები: თემატური ოთახები, შპალერის ნიშანი, პლაკატები და იატაკზე დაგდებული ნივთები
    var HT = [
        { r: ['play'], m: '⚽', p: ['e:🦸', 'e:🎮', 'e:🎨'], f: ['ball', 'e:🚲', 'e:📚', 'e:🛹'] },
        { r: ['garden', 'hearth'], m: '🌻', p: ['e:🐓', 'e:🌻'], f: ['e:🧺', 'e:🐈'] },
        { r: ['hearth'], m: '🍄', p: ['e:🦌', 'e:🌲'], f: ['e:🍄', 'e:🪵'] },
        { r: ['locker', 'trophy'], m: '⚽', p: ['jersey:#E63946:7', 'jersey:#2F7BF5:10', 'cup'], f: ['ball', 'e:👟'] },
        { r: ['classroom'], m: '➕', p: ['e:📐', 'e:🔢'], f: ['e:🎒', 'e:📚'] },
        { r: ['bakery'], m: '🧁', p: ['cupc:#FF8FAB', 'cake'], f: ['e:🍩', 'e:🍪'] },
        { r: ['pets'], m: '🐾', p: ['e:🦁', 'e:🦒'], f: ['e:🐢', 'e:🐕'] },
        { r: ['barn', 'garden'], m: '🌾', p: ['e:🐮', 'e:🚜'], f: ['e:🥕', 'e:🐔'] },
        { r: ['nest'], m: '🍃', p: ['e:🐿️', 'e:🦉'], f: ['e:🌰', 'e:🪺'] },
        { r: ['music'], m: '🎵', p: ['note', 'e:🎸'], f: ['e:🥁', 'e:🎺'] },
        { r: ['ring'], m: '🎈', p: ['e:🤡', 'e:🎪'], f: ['e:🤹', 'e:🎩'] },
        { r: ['xmas'], m: '❄️', p: ['gift:#E63946', 'e:🦌'], f: ['gift:#2F7BF5', 'e:⛄'] },
        { r: ['office'], m: '👣', p: ['magni', 'e:🕵️'], f: ['e:🧳', 'e:🗝️'] },
        { r: ['station'], m: '🚂', p: ['e:🚂', 'e:🗺️'], f: ['e:🧳', 'e:🚦'] },
        { r: ['cabin'], m: '⚓', p: ['wheel', 'e:⛵'], f: ['ring', 'e:🐚'] },
        { r: ['lab'], m: '⚙️', p: ['gear', 'e:💡'], f: ['e:🤖', 'e:🔋'] },
        { r: ['hq'], m: '⚡', p: ['shield', 'e:🦸'], f: ['e:💥', 'e:🥽'] },
        { r: ['treasure', 'cabin'], m: '☠️', p: ['e:🗺️', 'e:🦜'], f: ['e:💰', 'e:🗡️'] },
        { r: ['reading'], m: '⭐', p: ['e:🏰', 'e:🦄'], f: ['e:📖', 'e:🐸'] },
        { r: ['potion'], m: '✨', p: ['e:🔮', 'e:🌙'], f: ['e:🧹', 'e:🐈‍⬛'] },
        { r: ['hoard'], m: '🔥', p: ['e:🐉', 'e:⚔️'], f: ['e:💎', 'e:🪨'] },
        { r: ['principal', 'classroom'], m: '🎓', p: ['cup', 'e:🏫'], f: ['e:🛎️', 'e:📚'] }
    ];
    function roomType(h, d) {
        if (d === 0) return 'hall';
        if (d === nd(h) - 1) return 'attic';
        var r = h % ROOM_ORDER.length, th = HT[h].r, mid = [], gen = ROOM_ORDER.slice(r).concat(ROOM_ORDER.slice(0, r)).filter(function (x) { return th.indexOf(x) < 0; });
        // თემატური ოთახები ერევა ჩვეულებრივებს: თ, ჩ, თ, ჩ…
        for (var i = 0, gi = 0, ti = 0; mid.length < nd(h) - 2; i++) mid.push(i % 2 === 0 && ti < th.length ? th[ti++] : gen[gi++]);
        return mid[d - 1];
    }
    // ნივთი 40×40 ყუთში: დახატული ან emoji
    function icon(k) {
        var a = k.split(':');
        switch (a[0]) {
            case 'ball': return BALL(20, 20, 15);
            case 'jersey': return JERSEY(4, 3, 32, a[1], a[2]);
            case 'cup': return CUP(6, 2, 28);
            case 'note': return NOTE(8, 6) + NOTE(22, 10);
            case 'wheel': return WHEEL(20, 20, 11);
            case 'ring': return RING(20, 20, 13);
            case 'gift': return GIFT(6, 8, 28, a[1]);
            case 'magni': return MAGNI(16, 16, 11);
            case 'cake': return CAKE(20, 8, 30);
            case 'cupc': return CUPC(20, 8, 26, a[1]);
            case 'gear': return GEAR(20, 20, 12);
            case 'shield': return SHIELD(20, 20, 30);
            case 'medal': return '<path d="M11 3l9 15 9-15" fill="none" stroke="#E63946" stroke-width="4"/><circle cx="20" cy="26" r="10" fill="#FFC93C"/><path d="M20 20l2 4 4 .5-3 3 1 4-4-2-4 2 1-4-3-3 4-.5z" fill="#E0A800" stroke="none"/>';
        }
        return '';
    }
    // კარის სტიკერები — ყოველ ოთახს თავისი (სახლის თემით)
    var STK = [
        ['ball', 'e:📚', 'e:🚲', 'e:🦸', 'e:🎮', 'e:🛹', 'e:🎨', 'e:🪁'], ['e:🐓', 'e:🌻', 'e:🍇', 'e:🐐', 'e:🧺', 'e:🍎'], ['e:🍄', 'e:🦉', 'e:🦊', 'e:🌲', 'e:🦔', 'e:🌰'],
        ['jersey:#E63946:7', 'medal', 'cup', 'ball', 'jersey:#2F7BF5:10', 'e:👟', 'e:🥅', 'e:📣'], ['e:📐', 'e:➕', 'e:🧮', 'e:✏️', 'e:🔢', 'e:📏'],
        ['cupc:#FF8FAB', 'cake', 'e:🍩', 'e:🍪', 'cupc:#8FD19A', 'e:🍭'], ['e:🦁', 'e:🐘', 'e:🦒', 'e:🐒', 'e:🦓', 'e:🐧'], ['e:🐮', 'e:🐷', 'e:🚜', 'e:🥕', 'e:🐑', 'e:🌽'],
        ['e:🐿️', 'e:🦉', 'e:🍂', 'e:🐦', 'e:🪺', 'e:🌰'], ['note', 'e:🎸', 'e:🥁', 'e:🎺', 'e:🎹', 'e:🎻', 'e:🎤'], ['e:🤡', 'e:🎪', 'e:🤹', 'e:🎈', 'e:🎩', 'e:🎠'],
        ['gift:#E63946', 'e:🎄', 'e:🦌', 'e:⛄', 'gift:#2F7BF5', 'e:🔔'], ['magni', 'e:👣', 'e:🗝️', 'e:🕵️', 'e:📜', 'e:🔦'], ['e:🚂', 'e:🎫', 'e:🚦', 'e:🧳', 'e:🛤️', 'e:⏰'],
        ['wheel', 'ring', 'e:⚓', 'e:⛵', 'e:🐚', 'e:🦀'], ['gear', 'e:💡', 'e:🤖', 'e:🔋', 'e:🧲', 'e:🔧'], ['shield', 'e:⚡', 'e:🦸', 'e:💥', 'e:🦇', 'e:🏙️'],
        ['e:🏴‍☠️', 'e:🦜', 'e:🗺️', 'e:💰', 'e:🗡️', 'e:⚓'], ['e:🏰', 'e:🦄', 'e:🧚', 'e:🐸', 'e:👑', 'e:📖'], ['e:🔮', 'e:🪄', 'e:🌙', 'e:🧹', 'e:⭐', 'e:🐈‍⬛'],
        ['e:🐉', 'e:🔥', 'e:💎', 'e:⚔️', 'e:🛡️', 'e:🥚'], ['e:🎓', 'cup', 'medal', 'e:🛎️', 'e:📚', 'e:🌍']
    ];
    function sticker(h, d) {
        var low = ['port', 'frost', 'panes'].indexOf(doorTheme(h, d).x) >= 0;
        var k = STK[h][d % STK[h].length];
        return '<span class="cb-stk' + (d % 2 ? ' r' : '') + (low ? ' low' : '') + '" aria-hidden="true"><svg viewBox="0 0 40 40">' + sv(prop(k, 0, 0, 40)) + '</svg></span>';
    }
    function prop(k, x, y, sz) {
        if (k.indexOf('e:') === 0) return '<text x="' + (x + sz / 2) + '" y="' + (y + sz * .84) + '" font-size="' + (sz * .86) + '" text-anchor="middle" stroke="none">' + k.slice(2) + '</text>';
        return '<g transform="translate(' + x + ' ' + y + ') scale(' + (sz / 40) + ')">' + icon(k) + '</g>';
    }
    function poster(k, x, y, sz) { return '<rect x="' + (x - 3) + '" y="' + (y - 3) + '" width="' + (sz + 6) + '" height="' + (sz + 6) + '" rx="2" fill="#FFFDF7"/>' + prop(k, x, y, sz); }
    // სად ეკიდება პლაკატი და სად დევს ნივთი თითოეულ ჩვეულებრივ ოთახში
    var SLOT = { hall: [[6, 6, 22], [62, 66, 18]], living: [[98, 10, 22], [108, 66, 18]], kitchen: [[64, 26, 20], [126, 66, 18]], bedroom: [[104, 8, 22], [3, 66, 18]],
        dining: [[124, 8, 22], [3, 66, 18]], bath: [[86, 10, 22], [3, 66, 18]], library: [[136, 2, 18], [3, 66, 18]], play: [[60, 8, 22], [64, 66, 18]], work: [[122, 6, 22], [3, 66, 18]], attic: [null, [124, 64, 18]] };
    function roomSvg(h, d) {
        var t = roomType(h, d), T = HT[h], art;
        if (TROOMS[t]) art = TROOMS[t]();
        else {
            art = ROOMS[t]({ door: H[h].th.door });
            var sl = SLOT[t] || [null, null], deco = '';
            if (sl[0]) deco += poster(T.p[d % T.p.length], sl[0][0], sl[0][1], sl[0][2]);
            if (sl[1]) deco += prop(T.f[d % T.f.length], sl[1][0], sl[1][1], sl[1][2]);
            art += sv(deco);
        }
        return '<svg viewBox="0 0 160 100" preserveAspectRatio="xMidYMax meet" aria-hidden="true">' + art + '</svg>';
    }
    // შპალერი სახლის ნიშნით
    function motif(h) {
        var e = HT[h].m, svg = '<svg xmlns="http://www.w3.org/2000/svg" width="52" height="52"><text x="4" y="18" font-size="13" opacity=".2">' + e + '</text><text x="30" y="44" font-size="13" opacity=".2">' + e + '</text></svg>';
        return "url('data:image/svg+xml," + encodeURIComponent(svg) + "')";
    }
    function houseVars(h) { var th = H[h].th, r = EXT[h]; return themeVars(th) + ';--roof:' + r[0] + ';--roof2:' + r[1] + ';--motif:' + motif(h); }
    // რუკისთვის: პატარა სახლი, გავლილი ოთახები ანათებს
    function miniHouse(h, lockd) {
        var n = nd(h), cells = '';
        var fl = []; for (var d2 = 1; d2 <= n - 2; d2 += 2) fl.push(d2); fl.reverse();
        fl.forEach(function (a) { cells += '<i class="' + (stars(h, a) ? 'on' : '') + '"></i><i class="' + (stars(h, a + 1) ? 'on' : '') + '"></i>'; });
        cells += '<i class="w' + (stars(h, 0) ? ' on' : '') + '"></i>';
        return '<span class="cb-mh' + (lockd ? ' off' : '') + (houseDone(h) ? ' done' : '') + '" style="' + houseVars(h) + '" aria-hidden="true"><i class="r"></i><span class="b">' + cells + '</span></span>';
    }

    // ── გამამხნევებელი სიტყვები ──
    var CHEER = {
        first: ['ერთი ცდით! ნამდვილი დეტექტივი ხარ! 🕵️', 'ვაუ, პირველივე ცდით! 🔥', 'შენი ტვინი ყველა საკეტზე ძლიერია! 🧠', 'ბრწყინვალე ლოგიკა! ⭐', 'ზუსტი გათვლა — როგორც ნამდვილ ოსტატს! 🎯'],
        retry: ['არ დანებდი და გამოგივიდა! 💪', 'ყოჩაღ! შეცდომიდან ისწავლე და გახსენი! 🎉', 'მოთმინება = გამარჯვება! 🏁', 'ძალიან კარგი! ყოველი ცდა გაძლიერებს 💡'],
        hint: ['ერთად გავაკეთეთ — შემდეგს თვითონ გახსნი! 🤝', 'კარგი მუშაობა! შემდეგ ჯერზე მინიშნების გარეშე სცადე 😉', 'გამოგივიდა! მინიშნება მხოლოდ ბიძგი იყო 🚀'],
        boss: ['მთელი სახლი გახსენი! შენ ჩემპიონი ხარ! 🏆', 'სახლი დაპყრობილია! 👑', 'ბოლო საკეტიც გახსენი! 🎊'],
        oops1: ['თითქმის!', 'ახლოს ხარ!', 'კიდევ ცოტა!'],
        oopsN: ['არაუშავს!', 'კიდევ სცადე!', 'ნუ ნერვიულობ!']
    };
    var pickOne = function (a) { return a[Math.floor(Math.random() * a.length)]; };

    // ── ხმები (ბრაუზერში იქმნება, ფაილები არ სჭირდება) ──
    var SND = (function () {
        var ctx = null, on = true;
        try { on = localStorage.getItem('cb-sound') !== '0'; } catch (e) {}
        function ac() {
            if (!on) return null;
            if (!ctx) { var A = window.AudioContext || window.webkitAudioContext; if (!A) return null; try { ctx = new A(); } catch (e) { return null; } }
            if (ctx.state === 'suspended') ctx.resume();
            return ctx;
        }
        function tone(f, t0, dur, type, vol, f2) {
            var c = ac(); if (!c) return;
            var o = c.createOscillator(), g = c.createGain(), t = c.currentTime + (t0 || 0);
            o.type = type || 'sine'; o.frequency.setValueAtTime(f, t);
            if (f2) o.frequency.exponentialRampToValueAtTime(f2, t + dur);
            g.gain.setValueAtTime(0.0001, t); g.gain.exponentialRampToValueAtTime(vol || .2, t + .01); g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
            o.connect(g); g.connect(c.destination); o.start(t); o.stop(t + dur + .03);
        }
        function noise(t0, dur, vol, freq) {
            var c = ac(); if (!c) return;
            var n = Math.floor(c.sampleRate * dur), b = c.createBuffer(1, n, c.sampleRate), d = b.getChannelData(0);
            for (var i = 0; i < n; i++) d[i] = (Math.random() * 2 - 1) * (1 - i / n);
            var src = c.createBufferSource(), fl = c.createBiquadFilter(), g = c.createGain();
            src.buffer = b; fl.type = 'bandpass'; fl.frequency.value = freq || 2000; fl.Q.value = 1.2; g.gain.value = vol || .3;
            src.connect(fl); fl.connect(g); g.connect(c.destination); src.start(c.currentTime + (t0 || 0));
        }
        return {
            tick: function () { noise(0, .03, .35, 3800); tone(1900, 0, .025, 'square', .03); },
            bolt: function () { noise(0, .05, .55, 1500); tone(430, 0, .07, 'square', .07, 250); noise(.07, .04, .35, 2600); },
            clunk: function () { tone(150, 0, .28, 'sine', .4, 70); noise(0, .14, .55, 380); },
            creak: function () {
                var c = ac(); if (!c) return;
                var t = c.currentTime, o = c.createOscillator(), g = c.createGain(), lf = c.createOscillator(), lg = c.createGain(), fl = c.createBiquadFilter();
                o.type = 'sawtooth'; o.frequency.setValueAtTime(200, t); o.frequency.linearRampToValueAtTime(115, t + .75);
                lf.frequency.value = 24; lg.gain.value = 20; lf.connect(lg); lg.connect(o.frequency);
                fl.type = 'bandpass'; fl.frequency.value = 950; fl.Q.value = 4;
                g.gain.setValueAtTime(.0001, t); g.gain.exponentialRampToValueAtTime(.1, t + .08); g.gain.exponentialRampToValueAtTime(.0001, t + .8);
                o.connect(fl); fl.connect(g); g.connect(c.destination); o.start(t); lf.start(t); o.stop(t + .85); lf.stop(t + .85);
            },
            win: function (big) {
                var n = big ? [523, 659, 784, 1047, 1319] : [523, 659, 784, 1047];
                n.forEach(function (f, i) { tone(f, i * .11, .38, 'triangle', .17); });
                if (big) { tone(1568, .62, .7, 'triangle', .14); tone(784, .62, .7, 'triangle', .1); }
            },
            wrong: function () { tone(240, 0, .16, 'square', .06, 200); tone(170, .17, .26, 'square', .06, 140); },
            isOn: function () { return on; },
            toggle: function () { on = !on; try { localStorage.setItem('cb-sound', on ? '1' : '0'); } catch (e) {} if (on) this.tick(); return on; }
        };
    })();
    function sndBtn() { return '<button type="button" class="cb-snd" id="cbSnd" aria-pressed="' + SND.isOn() + '" aria-label="' + (SND.isOn() ? 'ხმის გამორთვა' : 'ხმის ჩართვა') + '">' + (SND.isOn() ? '🔊' : '🔇') + '</button>'; }
    function bindSnd() { var b = document.getElementById('cbSnd'); if (b) b.onclick = function () { SND.toggle(); b.outerHTML = sndBtn(); bindSnd(); }; }
    var root = document.getElementById('cbx');
    var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    var key = function (h, d) { return h + '-' + d; };
    var stars = function (h, d) { return P[key(h, d)] || 0; };
    var houseDone = function (h) { return stars(h, nd(h) - 1) > 0; };
    var houseOpen = function (h) { return h === 0 || houseDone(h - 1); };
    var doorOpen = function (h, d) { return houseOpen(h) && (d === 0 || stars(h, d - 1) > 0); };
    var doneCount = function (h) { var n = 0; for (var d = 0; d < nd(h); d++) if (stars(h, d)) n++; return n; };
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
            var st = done ? '<span class="cb-badge ok">' + n + ' / ' + nd(h) + ' ✓</span>' : (open ? '<span class="cb-badge cur">' + n + ' / ' + nd(h) + (h === cur ? ' · აქ ხარ' : '') + '</span>' : '<span class="cb-badge">დაკეტილია</span>');
            html += '<button type="button" class="cb-house ' + (done ? 'done' : (open ? 'cur' : 'off')) + ' ' + (h % 2 ? 'r' : 'l') + '" data-h="' + h + '"' + (open ? '' : ' aria-disabled="true"') + '>'
                + miniHouse(h, !open) + '<span class="cb-hx"><small>სახლი ' + (h + 1) + '</small><b>' + esc(hs.name) + '</b><span>' + esc(CB.topicInfo(h).title) + '</span>' + st + '</span></button>';
        });
        root.innerHTML = '<div class="cb-head"><div><h1>კოდის გამტეხი</h1><span>' + H.length + ' სახლი · ' + TOTAL + ' კარი</span></div><span class="cb-total">' + sndBtn() + '★ ' + totalStars() + '</span></div>'
            + '<div class="cb-prog"><span style="width:' + Math.min(100, Math.round(Object.keys(P).length / TOTAL * 100)) + '%"></span></div>'
            + '<div class="cb-scroll" id="cbScroll"><div class="cb-path">' + html + '</div></div>';
        root.querySelectorAll('.cb-house').forEach(function (b) { b.onclick = function () { var h = +b.dataset.h; if (houseOpen(h)) go({ name: 'house', h: h }); }; });
        bindSnd();
        var c = root.querySelector('.cb-house.cur'); if (c) setTimeout(function () { c.scrollIntoView({ block: 'center' }); }, 30);
    }

    // ═════ სახლი ═════
    function house() {
        var h = view.h, hs = H[h], ti = CB.topicInfo(h), n = nd(h), curD = -1;
        for (var q = 0; q < n; q++) if (doorOpen(h, q) && !stars(h, q)) { curD = q; break; }
        function rm(d, wide) {
            var o = doorOpen(h, d), st = stars(h, d), t = roomType(h, d);
            var cls = 'cb-rm' + (wide ? ' wide' : '') + ' wp' + (d % 3 + 1) + (st ? ' done' : (o ? (d === curD ? ' cur' : ' open') : ' lock')) + (FRESH === key(h, d) ? ' fresh' : '');
            return '<button type="button" class="' + cls + '" data-d="' + d + '" aria-label="ოთახი ' + (d + 1) + ' — ' + ROOM_NAME[t] + (st ? ', ' + st + ' ვარსკვლავი' : (o ? ', ახალი' : ', დაკეტილია')) + '"' + (o ? '' : ' aria-disabled="true"') + '>'
                + roomSvg(h, d) + '<span class="lb">' + (d + 1) + '</span>' + (st ? '<span class="st">' + starStr(st) + '</span>' : '') + (d === curD ? '<span class="go">ახალი</span>' : '') + '</button>';
        }
        var fl = []; for (var d = 1; d <= n - 2; d += 2) fl.push(d); fl.reverse();
        var body = fl.map(function (a) { return rm(a) + rm(a + 1); }).join('') + rm(0, true);
        var b = n - 1, bo = doorOpen(h, b), bs = stars(h, b);
        var attic = '<button type="button" class="cb-attic' + (bs ? ' done' : (bo ? (b === curD ? ' cur' : ' open') : ' lock')) + (FRESH === key(h, b) ? ' fresh' : '') + '" data-d="' + b + '" aria-label="ოთახი ' + (b + 1) + ' — სხვენი' + (bs ? ', ' + bs + ' ვარსკვლავი' : (bo ? ', ახალი' : ', დაკეტილია')) + '"' + (bo ? '' : ' aria-disabled="true"') + '>'
            + roomSvg(h, b) + '<span class="lb">' + (b + 1) + (bs ? ' ' + starStr(bs) : '') + '</span></button>';
        var got = doneCount(h);
        root.innerHTML = '<div class="cb-bar"><button type="button" class="cb-back" id="cbBack"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>სახლები</button><span class="cb-total">★ ' + totalStars() + '</span></div>'
            + '<div class="cb-scroll"><div class="cb-hp"><section class="cb-intro"><div><small>სახლი ' + (h + 1) + ' · ' + esc(ti.title) + '</small><h2>' + esc(hs.name) + '</h2><p>' + esc(ti.explain) + '</p><div class="cb-ex">მაგალითი: ' + esc(ti.example) + '</div>'
            + '<p class="cb-hnote"><b>' + got + ' / ' + n + '</b> ოთახი გახსნილია. ბოლო ოთახი სხვენშია!</p>'
            + '<p class="cb-reward">' + (houseDone(h) ? 'სახლი დასრულებულია ✓ — ჯილდო უკვე მიღებული გაქვს' : 'სახლის დასრულება (ბოლო ოთახი) — <b>+10 მონეტა</b>') + '</p></div></section>'
            + '<div class="cb-dh g-' + EXT[h][2] + (hs.th.dark ? ' dark' : '') + '" style="' + houseVars(h) + '"><div class="cb-dh-roof"><span class="cb-dh-flag" aria-hidden="true"><i>' + PROPS[h][0] + '</i></span><span class="cb-dh-chim" aria-hidden="true"></span><div class="cb-dh-tri" aria-hidden="true"><i></i></div>' + attic + '</div>'
            + '<div class="cb-dh-body">' + body + '</div><div class="cb-dh-ground" aria-hidden="true"></div></div></div></div>';
        FRESH = null;
        document.getElementById('cbBack').onclick = function () { go({ name: 'map' }); };
        root.querySelectorAll('.cb-rm, .cb-attic').forEach(function (el) { el.onclick = function () { var d = +el.dataset.d; if (stars(h, d)) visit(h, d); else if (doorOpen(h, d)) start(h, d); }; });
        var c = root.querySelector('.cb-rm.cur, .cb-attic.cur'); if (c && c.scrollIntoView) setTimeout(function () { c.scrollIntoView({ block: 'nearest' }); }, 30);
    }

    // ═════ კარი (თამაში) ═════
    function start(h, d) {
        var v = { name: 'play', h: h, d: d, code: [null, null, null, null], sel: 0, tries: 0, hints: 0, locked: [false, false, false, false], fails: [], checked: [], won: false, msg: '' };
        // ამ კარზე მინიშნება უკვე აღებულია → ისევ ჩანს და მეორედ აღარ მიეცემა
        var hn = HINTS[key(h, d)];
        if (hn) {
            v.code[hn[0]] = hn[1]; v.locked[hn[0]] = true; v.hints = 1;
            v.fox = FOX_SAY[hn[2] % FOX_SAY.length].replace('{p}', POSL[hn[0]]).replace('{n}', hn[1]);
            v.sel = hn[0] === 0 ? 1 : 0;
        }
        go(v);
    }
    // უკვე გაღებული ოთახი: საკეტის გარეშე პირდაპირ ოთახში შევდივართ
    function visit(h, d) {
        var sol = CB.door(h, d).sols[0].split('').map(Number);
        go({ name: 'play', h: h, d: d, code: sol, sel: -1, tries: 0, hints: 0, locked: [true, true, true, true], fails: [], checked: [], won: true, visit: true, earned: stars(h, d), msg: '' });
    }
    function auto() { return view.h < CB.AUTO_HOUSES && view.d < nd(view.h) - 1; }
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
    function doorX(th) {
        if (leafExtra(th)) return '';
        if (th.x === 'port') return '<span class="cb-dx-port" aria-hidden="true"></span>';
        if (th.x === 'frost') return '<span class="cb-dx-frost" aria-hidden="true"></span>';
        if (th.x === 'panes') return '<span class="cb-dx-panes" aria-hidden="true"></span>';
        return '';
    }
    function knob(th) {
        if (th.shape === 'goal' || th.tex === 'books' || th.deco === 'director') return '';
        if (th.deco === 'football') return '<svg class="cb-knob ball" viewBox="0 0 26 26" aria-hidden="true"><circle cx="13" cy="13" r="11" fill="#fff" stroke="#2B2622" stroke-width="2.5"/><path d="M13 8l4 3-1.5 4.5h-5L9 11z" fill="#2B2622"/></svg>';
        return '<span class="cb-knob" aria-hidden="true"></span>';
    }
    function play() {
        var h = view.h, d = view.d, hs = H[h], th = doorTheme(h, d), D = CB.door(h, d), boss = d === nd(h) - 1, a = auto();
        if (a) view.checked = autoTicks(D);
        var ti = CB.topicInfo(h);
        var clues = D.clues.map(function (c, i) {
            var f = view.fails.indexOf(i) >= 0, ok = view.checked[i] && !f;
            return '<li><button type="button" class="cb-clue' + (ok ? ' done' : '') + (f ? ' fail' : '') + (a ? ' auto' : '') + '" data-i="' + i + '"' + (a ? ' aria-disabled="true"' : ' aria-pressed="' + !!view.checked[i] + '"') + '>' + (f ? '<span class="cb-failtag">არ სრულდება</span>' : '') + '<span class="box">' + (ok ? '✓' : '') + '</span><span class="tx">' + markup(c.t) + '</span></button></li>';
        }).join('');
        var dots = ''; for (var k = 0; k < nd(h); k++) dots += '<i class="' + (stars(h, k) ? 'on' : (k === d ? 'cur' : '')) + '"></i>';
        var lock = '<div class="cb-plate" role="group" aria-label="საკეტი">' + [0, 1, 2, 3].map(function (i) {
            return '<div class="cb-slot s' + (i + 1) + (view.won ? ' ok' : (view.sel === i ? ' sel' : '')) + '"><button type="button" class="cb-arr" data-up="' + i + '" aria-label="' + POS[i] + ' ციფრი, ერთით მეტი">▲</button>'
                + '<div class="cb-win' + (view.code[i] === null ? ' empty' : '') + (view.locked[i] ? ' lk' : '') + (view.roll && view.roll.i === i ? ' roll-' + view.roll.dir : '') + '" data-w="' + i + '" tabindex="0" role="button" aria-label="' + POS[i] + ' ციფრი"><span class="dg">' + (view.code[i] === null ? '?' : view.code[i]) + '</span></div>'
                + '<button type="button" class="cb-arr" data-dn="' + i + '" aria-label="' + POS[i] + ' ციფრი, ერთით ნაკლები">▼</button></div>';
        }).join('') + '</div>';
        var win = '', room = '';
        if (view.won) {
            var mine = view.code.join(''), nx = nextRef();
            win = '<span class="cb-glow" aria-hidden="true"></span>';
            room = '<div class="cb-room wp' + (d % 3 + 1) + (boss ? ' boss' : '') + (th.dark ? ' dark' : '') + '">'
                + '<div class="cb-rcard"><b class="cb-found">' + (view.visit ? 'ოთახი ' + (d + 1) + ' · ' + ROOM_NAME[roomType(h, d)] : (boss ? 'სახლი დასრულდა! 🏆' : 'ოთახი გაიღო!')) + '</b>'
                + '<div class="cb-wst" aria-label="' + view.earned + ' ვარსკვლავი">' + starStr(view.earned) + '</div>'
                + '<p class="cb-cheer">' + esc(view.visit ? 'ეს ოთახი უკვე გახსნილი გაქვს. „თავიდან“ — თუ კიდევ გინდა ამოხსნა' : (view.cheer || '')) + '</p></div>'
                + '<div class="cb-rart">' + roomSvg(h, d) + '</div></div>';
        }
        root.innerHTML = '<div class="cb-bar"><button type="button" class="cb-back" id="cbBack"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>' + esc(hs.name) + '</button>'
            + '<span class="cb-where">' + ('კარი ' + (d + 1) + ' / ' + nd(h)) + '</span><span class="cb-dots" aria-hidden="true">' + dots + '</span>' + sndBtn() + '</div>'
            + '<div class="cb-play' + (boss ? ' is-boss' : '') + '">'
            + '<section class="cb-left">'
            + '<details class="cb-recall"><summary>გაიხსენე: ' + esc(ti['new']) + '</summary><p>' + esc(ti.explain) + '</p></details>'
            + (D.story ? '<div class="cb-story"><b>ამბავი</b>' + esc(D.story) + '</div>' : '')
            + '<ul class="cb-clues">' + clues + '</ul>'
            + '<p class="cb-tip">' + (a ? 'შესრულებული პირობა თავისით მოინიშნება ✓' : 'დააჭირე პირობას, როცა გამოიყენებ — მოინიშნება ✓') + '</p>'
            + '</section>'
            + '<section class="cb-right">'
            + '<div class="cb-scene sh-' + th.shape + ' tx-' + th.tex + (th.dark ? ' dark' : '') + ' wp' + (d % 3 + 1) + (view.won ? ' won open enter' : '') + '" id="cbScene" style="' + themeVars(th) + ';--motif:' + motif(h) + '">' + deco(th) + '<span class="cb-sprop" aria-hidden="true">' + floorProp(h, d) + '</span><span class="cb-sign">' + ('ოთახი ' + (d + 1) + ' · ' + ROOM_NAME[roomType(h, d)]) + '</span>' + lock
            + '<div class="cb-frame"><div class="cb-way">' + win + '</div><div class="cb-leaf">' + leafExtra(Object.assign({}, th, { deco: null })) + doorX(Object.assign({}, th, { deco: null })) + sticker(h, d) + knob(th) + '</div></div>' + room + '</div>'
            + '<div class="cb-panel">' + (view.won
                ? '<div class="cb-acts"><button type="button" class="cb-btn main" id="cbNext">' + (nextRef() ? (boss ? 'შემდეგი სახლი →' : 'შემდეგი კარი →') : 'ყველა სახლი დასრულდა!') + '</button><button type="button" class="cb-btn soft" id="cbAgain">თავიდან</button></div>'
                : foxBox() + '<p class="cb-msg" role="status">' + esc(view.msg || '') + '</p><div class="cb-acts"><button type="button" class="cb-btn main" id="cbOpen">გააღე</button>'
                    + (view.hints ? '<button type="button" class="cb-btn soft" id="cbHint" disabled aria-disabled="true">მინიშნება ✓</button>' : '<button type="button" class="cb-btn soft" id="cbHint">🦊 მინიშნება</button>') + '</div>')
            + '</div></section></div>';

        view.roll = null; view.foxNew = false;
        root.classList.toggle('busy', !!view.busy);
        bindSnd();
        document.getElementById('cbBack').onclick = function () { if (!view.busy) go({ name: 'house', h: h }); };
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
    function nextRef() { if (view.d + 1 < nd(view.h)) return { h: view.h, d: view.d + 1 }; if (view.h + 1 < H.length) return { h: view.h + 1, d: 0 }; return null; }
    function spin(i, dir) { if (view.won || view.busy || view.locked[i]) return; var v = view.code[i]; view.code[i] = v === null ? (dir > 0 ? 0 : 9) : (v + dir + 10) % 10; view.sel = i; view.fails = []; view.msg = ''; view.roll = { i: i, dir: dir > 0 ? 'up' : 'dn' }; SND.tick(); play(); }
    function typeD(dg) {
        if (view.won || view.busy) return;
        if (dg !== 'x' && !view.locked[view.sel]) { view.roll = { i: view.sel, dir: 'pop' }; SND.tick(); }
        if (dg === 'x') { var i = view.sel; if (view.code[i] === null && i > 0) i--; if (!view.locked[i]) { view.code[i] = null; view.sel = i; } }
        else { var j = view.sel; if (!view.locked[j]) view.code[j] = +dg; var k = j; do { k++; } while (k < 4 && view.locked[k]); view.sel = k < 4 ? k : j; }
        view.fails = []; view.msg = ''; play();
    }
    function check() {
        if (view.won || view.busy) return;
        if (view.code.some(function (x) { return x === null; })) { view.msg = 'ჯერ ოთხივე ციფრი ჩაწერე'; play(); return; }
        var D = CB.door(view.h, view.d);
        var f = D.clues.map(function (c, i) { return c.f(view.code) ? -1 : i; }).filter(function (i) { return i >= 0; });
        if (!f.length) { openDoor(); return; }
        view.tries++; view.fails = f;
        view.msg = (f.length === 1 ? pickOne(CHEER.oops1) + ' ერთი პირობა არ სრულდება — წითლადაა მონიშნული' : pickOne(CHEER.oopsN) + ' ' + f.length + ' პირობა არ სრულდება — წითლადაა მონიშნული');
        play(); SND.wrong();
        var s = document.getElementById('cbScene'); s.classList.remove('shake'); void s.offsetWidth; s.classList.add('shake');
    }
    // ── სწორი კოდი: ურდულები → სახელური → კარი იღება → განძი ──
    function openDoor() {
        var h = view.h, d = view.d, boss = d === nd(h) - 1;
        view.earned = Math.max(1, 3 - (view.tries >= 1 ? 1 : 0) - (view.tries >= 3 ? 1 : 0) - view.hints);
        view.cheer = pickOne(boss ? CHEER.boss : (view.hints ? CHEER.hint : (view.tries ? CHEER.retry : CHEER.first)));
        view.busy = true; view.fails = []; view.msg = '';
        play();
        var T = RM ? 0 : 1, step = 230 * T;
        [0, 1, 2, 3].forEach(function (i) {
            setTimeout(function () { var el = root.querySelector('.cb-slot.s' + (i + 1)); if (el) el.classList.add('ok'); SND.bolt(); }, step * (i + 1));
        });
        setTimeout(function () {
            var sc = document.getElementById('cbScene'); if (sc) sc.classList.add('unlatch');
            SND.clunk();
        }, step * 5);
        setTimeout(function () {
            view.won = true; view.busy = false;
            var k = key(h, d); P[k] = Math.max(P[k] || 0, view.earned); delete HINTS[k];
            FRESH = k;
            play();
            var sc = document.getElementById('cbScene');
            // კარი იმავე ზომით იღება → „შევდივართ“ ოთახში → შიგნით ნივთი დაგვხვდება
            if (!RM && sc) { sc.classList.remove('open', 'enter'); void sc.offsetWidth; }
            setTimeout(function () {
                if (sc) sc.classList.add('open');
                SND.creak();
            }, 40 * T);
            setTimeout(function () { if (sc) sc.classList.add('enter'); }, 900 * T);
            setTimeout(function () { SND.win(boss); if (!RM && sc) confetti(sc, boss); }, 1550 * T);
            save(h, d, view.earned);
            setTimeout(function () { var n = document.getElementById('cbNext'); if (n) n.focus({ preventScroll: true }); }, 1600 * T + 60);
        }, step * 5 + 380 * T);
    }
    function confetti(sc, big) {
        var cols = ['#E2590F', '#FFC93C', '#2F7BF5', '#1E8A4C', '#8B5CF6', '#FF8FAB'], n = big ? 46 : 28;
        for (var i = 0; i < n; i++) {
            var c = document.createElement('i');
            c.className = 'cb-cf';
            c.style.left = (Math.random() * 100) + '%';
            c.style.background = cols[i % cols.length];
            c.style.animationDelay = (Math.random() * .35) + 's';
            c.style.animationDuration = (1.1 + Math.random() * .9) + 's';
            c.style.setProperty('--x', (Math.random() * 80 - 40) + 'px');
            c.style.setProperty('--r', (Math.random() * 720 - 360) + 'deg');
            sc.appendChild(c);
        }
        setTimeout(function () { sc.querySelectorAll('.cb-cf').forEach(function (c) { c.remove(); }); }, 2600);
    }
    // ── მელა-დამხმარე: ერთ კარზე ერთი მინიშნება, ღრუბელში ──
    var FOX_SVG = '<svg viewBox="0 0 120 120" aria-hidden="true"><path d="M22 18 L48 40 L26 56 Z" fill="#F26B1D"/><path d="M98 18 L72 40 L94 56 Z" fill="#F26B1D"/><path d="M28 26 L42 40 L30 48 Z" fill="#FFD9BF"/><path d="M92 26 L78 40 L90 48 Z" fill="#FFD9BF"/><ellipse cx="60" cy="66" rx="40" ry="36" fill="#F26B1D"/><path d="M20 70 Q40 66 60 86 Q80 66 100 70 Q96 100 60 104 Q24 100 20 70 Z" fill="#FFFFFF"/><circle cx="45" cy="62" r="6" fill="#1D1A17"/><circle cx="75" cy="62" r="6" fill="#1D1A17"/><circle cx="47" cy="60" r="2" fill="#FFFFFF"/><circle cx="77" cy="60" r="2" fill="#FFFFFF"/><ellipse cx="60" cy="82" rx="6" ry="4.5" fill="#1D1A17"/><path d="M52 92 Q60 98 68 92" stroke="#1D1A17" stroke-width="3" fill="none" stroke-linecap="round"/></svg>';
    var FOX_SAY = [
        'ვფიქრობ, რომ {p} ციფრი არის {n} 🤔',
        'ჩემი დაკვირვებით, {p} ციფრი {n}-ია 🔍',
        'მგონი, {p} ადგილზე {n} დგას!',
        'ჩუ… საიდუმლოს გეტყვი: {p} ციფრი — {n} 🤫',
        'ჩემი ცხვირი მეუბნება, რომ {p} ციფრი {n}-ია 👃',
        'კუდზე გეფიცები — {p} ციფრი {n}-ია! 🦊'
    ];
    var POSL = ['პირველი', 'მეორე', 'მესამე', 'მეოთხე'];
    function foxBox() {
        var t = view.fox || (view.tries >= 2 && !view.hints ? 'გაჭირდა? დააჭირე „მინიშნებას“ — ერთ ციფრს გეტყვი!' : '');
        if (!t) return '';
        return '<div class="cb-fox' + (view.fox ? ' say' : '') + (view.foxNew ? ' new' : '') + '" role="status"><span class="cb-fox-ic">' + FOX_SVG + '</span><p class="cb-fox-b">' + esc(t) + '</p></div>';
    }
    function hint() {
        if (view.won || view.busy || view.hints >= 1) return;
        var D = CB.door(view.h, view.d);
        var fits = function (s) { return view.locked.every(function (lk, i) { return !lk || +s[i] === view.code[i]; }); };
        var sc = function (s) { return s.split('').reduce(function (a, ch, i) { return a + (view.code[i] === +ch ? 1 : 0); }, 0); };
        var best = D.sols.filter(fits).sort(function (x, y) { return sc(y) - sc(x); })[0];
        if (!best) { view.msg = 'მინიშნება აქ ვერ დაგეხმარება'; play(); return; }
        var i = [0, 1, 2, 3].find(function (i) { return !view.locked[i] && view.code[i] !== +best[i]; });
        if (i === undefined) { view.msg = 'კოდი უკვე სწორია — დააჭირე „გააღე“'; play(); return; }
        view.code[i] = +best[i]; view.locked[i] = true; view.hints++; view.fails = [];
        view.msg = '';
        view.foxNew = true;
        var say = Math.floor(Math.random() * FOX_SAY.length);
        view.fox = FOX_SAY[say].replace('{p}', POSL[i]).replace('{n}', best[i]);
        HINTS[key(view.h, view.d)] = [i, +best[i], say];
        saveHint(view.h, view.d, i, +best[i], say);
        view.roll = { i: i, dir: 'pop' };
        var j = 0; while (j < 4 && (view.locked[j] || view.code[j] !== null)) j++; view.sel = j < 4 ? j : view.sel;
        play();
    }
    function saveHint(h, d, i, n, say) {
        if (CFG.demo) { try { var x = JSON.parse(localStorage.getItem('kidsmart-codebreaker-demo') || '{}'); x.hints = HINTS; localStorage.setItem('kidsmart-codebreaker-demo', JSON.stringify(x)); } catch (e) {} return; }
        var fd = new FormData(); fd.append('house', h); fd.append('door', d); fd.append('pos', i); fd.append('digit', n); fd.append('say', say);
        fetch(CFG.hintUrl, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CFG.csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }).catch(function () {});
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
        if (view.name !== 'play' || view.won || view.busy) return;
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
