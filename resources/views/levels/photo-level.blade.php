@if($userLevel == $level)

<style>
    .photo-level-wrap {
        min-height: calc(100vh - var(--nav-h, 56px));
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px 16px 32px;
        gap: 24px;
    }
    .photo-level-img {
        max-width: min(560px, 100%);
        width: 100%;
        max-height: 55vh;
        object-fit: contain;
        border-radius: 4px;
        display: block;
    }
    .photo-level-question {
        font-family: 'Goldman', monospace;
        font-size: clamp(0.8rem, 2.5vw, 1rem);
        color: #444;
        text-align: center;
        letter-spacing: 0.04em;
        max-width: 520px;
    }
    .photo-level-form {
        display: flex;
        gap: 8px;
        width: 100%;
        max-width: 420px;
    }
    .photo-level-form input {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        outline: none;
        transition: border-color 0.15s;
        background: #fff;
        color: #111;
    }
    .photo-level-form input:focus { border-color: #888; }
    .photo-level-form button {
        padding: 10px 22px;
        background: #111;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-family: 'Goldman', monospace;
        font-size: 0.82rem;
        letter-spacing: 0.06em;
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.15s;
    }
    .photo-level-form button:hover { background: #333; }
</style>

<div class="photo-level-wrap">
    <img
        src="{{ asset('img/levels/level' . $level . '.jpg') }}"
        alt="Level {{ $level }}"
        class="photo-level-img"
        onerror="this.style.display='none'"
    >
    @if($question->question)
    <div class="photo-level-question">{{ $question->question }}</div>
    @endif
    <form id="answerForm" class="photo-level-form">
        @csrf
        <input type="text" id="answer" autocomplete="off" placeholder="პასუხი...">
        <button type="submit">Submit</button>
    </form>
</div>

@else
    @include('levels.levelcomplete', ['level' => $level, 'userLevel' => auth()->user()->level])
@endif
