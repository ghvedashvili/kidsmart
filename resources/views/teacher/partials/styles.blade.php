{{-- მასწავლებლის გვერდების სტილი (ნარინჯისფერი + თეთრი, მსხვილი და მარტივი) --}}
@once
<style>
    .tq-page [hidden], .ks-teacher [hidden] { display: none !important; }
    .tq-page { display: flex; flex-direction: column; gap: 16px; padding-bottom: 90px; }
    .tq-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px; }
    .tq-head h1 { font-size: clamp(22px, 3.2vw, 30px); font-weight: 900; margin: 0; color: #2B2622; }
    .tq-head p { margin: 4px 0 0; color: #6B625A; font-size: 15px; }
    .tq-card { background: #fff; border-radius: 20px; padding: 18px; box-shadow: 0 1px 0 #ECE9E4; border: 1px solid #ECE9E4; }
    .tq-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 14px; padding: 12px 18px; font-weight: 800; font-size: 15px; border: 0; cursor: pointer; text-decoration: none; transition: background .15s, transform .1s; }
    .tq-btn:active { transform: translateY(1px); }
    .tq-btn-main { background: #E2590F; color: #fff; } .tq-btn-main:hover { background: #C24A0A; color: #fff; }
    .tq-btn-soft { background: #FFF3EA; color: #C24A0A; } .tq-btn-soft:hover { background: #FFE3CC; color: #A33E08; }
    .tq-btn-ghost { background: #fff; color: #4F473F; border: 1.5px solid #ECE9E4; } .tq-btn-ghost:hover { border-color: #FFC49A; color: #C24A0A; }
    .tq-btn-danger { background: #fff; color: #A12626; border: 1.5px solid #F5C2C2; } .tq-btn-danger:hover { background: #FDE2E2; }
    .tq-btn-sm { padding: 8px 12px; font-size: 13px; border-radius: 12px; }
    .tq-flash { border-radius: 16px; padding: 14px 16px; font-weight: 700; background: #DFF5E3; color: #14612F; }
    .tq-flash.err { background: #FDE2E2; color: #A12626; }
    .tq-new { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 4px 0 14px; padding: 12px; border-radius: 14px; background: #E2590F; color: #fff !important; font-weight: 800; text-decoration: none; }
    .tq-new:hover, .tq-new.on { background: #C24A0A; }
    .ks-pnav-badge.tq-badge-soft { background: #F1EFEB; color: #4F473F; }

    /* ბავშვის ხედვის ბარათი — იგივე, რაც ტესტის გვერდზე */
    .tq-kid { background: #F7F6F3; border-radius: 22px; padding: 22px 14px 14px; position: relative; }
    .tq-kid-lbl { position: absolute; top: -11px; left: 16px; background: #E2590F; color: #fff; font-weight: 900; font-size: 12px; padding: 3px 12px; border-radius: 99px; }
    .tq-kcard { background: #fff; border-radius: 22px; padding: 20px 16px 16px; box-shadow: 0 2px 0 #ECE9E4; }
    .tq-ktext { font-size: 17px; font-weight: 700; color: #2B2622; line-height: 1.6; white-space: pre-line; }
    .tq-khint { font-size: 13px; color: #6B625A; margin-top: 8px; border-left: 3px solid #FFE3CC; padding-left: 10px; }
    .tq-kopts { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 16px; }
    .tq-kopt { display: flex; align-items: center; justify-content: center; min-height: 54px; background: #F7F6F3; border: 2.5px solid #ECE9E4; border-radius: 16px; font-weight: 900; font-size: 18px; color: #2B2622; padding: 8px; text-align: center; }
    .tq-kopt.ok { border-color: #1E8A4C; background: #E5F6EC; color: #14612F; }
    .tq-kinput { display: flex; justify-content: center; margin-top: 16px; }
    .tq-kinput span { width: 150px; height: 56px; border-radius: 16px; border: 2.5px dashed #FFC49A; background: #FFF8F2; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 20px; color: #C24A0A; }
    .tq-kmeta { font-size: 12px; color: #6B625A; margin-top: 10px; text-align: center; }
    .tq-pill { display: inline-flex; align-items: center; gap: 5px; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 800; background: #F1EFEB; color: #4F473F; }
    .tq-pill.o { background: #FFF3EA; color: #C24A0A; } .tq-pill.g { background: #DFF5E3; color: #14612F; } .tq-pill.r { background: #FDE2E2; color: #A12626; } .tq-pill.y { background: #FFF1C2; color: #6B4A00; }
</style>
@endonce
