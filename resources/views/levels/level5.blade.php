@if($userLevel == $level)

@section('content')

<style>
    body{
         background: #960612;
    }
    #level-wrapper {
        background: #EB191E;
        /* box-shadow: inset 0 0 1000px 0px #960612; */
        overflow: hidden;
        width: 100%;
        height: 100%;
        min-height: 100svh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        font-family: Arial, sans-serif;
        color: white;
        touch-action: none;
        position: relative;
    }

    .title {
        position: absolute;
        top: 20px;
        left: 0;
        width: 100%;
        text-align: center;
        color: white;
        font-size: 2.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        font-family: 'Arial Black', sans-serif;
        letter-spacing: 2px;
        margin-bottom: 20px;
        z-index: 10;
    }

    .instructions {
        position: absolute;
        bottom: 20px;
        text-align: center;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 1.2rem;
        z-index: 10;
    }

    .coca-cola-container {
        flex-shrink: 0;
        perspective: 800px;
        width: 100vw;
        height: 70vh;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: grab;
        user-select: none;
        transform: scale(1);
        transition: transform 0.1s ease;
        touch-action: none;
    }

    .coca-cola-container:active {
        cursor: grabbing;
    }

    .coca-cola-container .coca-cola,
    .coca-cola-container .tin,
    .coca-cola-container .top,
    .coca-cola-container .bottom {
        transform-style: preserve-3d;
    }

    .coca-cola {
        transform-origin: center center -51px;
        transform: rotateX(-20deg);
        width: 15px;
        height: 210px;
        margin: auto;
        transition: transform 0.1s;
    }

    .tin {
        width: 100%;
        height: 100%;
        transform: translateX(99.5%) rotateY(17.14deg);
        transform-origin: left;
        position: relative;
        left: 0;
        top: 0;
        background-color: #be0709;
        background-repeat: no-repeat;
        background-size: auto 100%;
    }

    @for($i = 1; $i <= 21; $i++)
    .tin--{{ $i }} { background-position: {{ ($i - 1) * 5 }}% top; }
    @endfor

    .top {
        background-color: #86919a;
        background-repeat: no-repeat;
        background-size: auto 100%;
        width: 100px;
        height: 100px;
        position: absolute;
        transform: rotateX(-90deg) translateX(-50%) translateY(-0%);
        transform-origin: top;
        left: 50%;
        border-radius: 50%;
    }

    .bottom {
        box-shadow: 0px 0 39px -5px black;
        width: 100px;
        height: 100px;
        position: absolute;
        top: 100%;
        transform: rotateX(-90deg) translateX(-50%) translateY(0%);
        transform-origin: top;
        left: 50%;
        border-radius: 50%;
    }

    @media (max-width: 768px) {
        .title {
            font-size: 1.5rem;
        }
    }
</style>

<div id="level-wrapper">

    <div class="title">დაასახელე მწარმოებelი კომპანიის patron-ი</div>

    <div class="coca-cola-container" id="canContainer">
        <div class="coca-cola" id="cocaCola">
            <div class="tin tin--1">
                <div class="top"></div>
                <div class="bottom"></div>
                <div class="tin tin--2">
                    <div class="tin tin--3">
                        <div class="tin tin--4">
                            <div class="tin tin--5">
                                <div class="tin tin--6">
                                    <div class="tin tin--7">
                                        <div class="tin tin--8">
                                            <div class="tin tin--9">
                                                <div class="tin tin--10">
                                                    <div class="tin tin--11">
                                                        <div class="tin tin--12">
                                                            <div class="tin tin--13">
                                                                <div class="tin tin--14">
                                                                    <div class="tin tin--15">
                                                                        <div class="tin tin--16">
                                                                            <div class="tin tin--17">
                                                                                <div class="tin tin--18">
                                                                                    <div class="tin tin--19">
                                                                                        <div class="tin tin--20">
                                                                                            <div class="tin tin--21">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="instructions"></div>

    <form id="answerForm" class="d-flex justify-content-center gap-2 mb-4">
    @csrf
    <input type="text" class="form-control w-auto" id="answer" name="answer">
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

</div>{{-- #level-wrapper --}}

<script>
document.addEventListener('DOMContentLoaded', function() {
    // სურათების URL დინამიურად — origin-დან, PWA და browser ორივეში მუშაობს
    const baseUrl = window.location.origin;
    const labelUrl = `${baseUrl}/img/coca-cola-label.png`;
    const topUrl   = `${baseUrl}/img/top-tin.png`;

    document.querySelectorAll('.tin').forEach(el => {
        el.style.backgroundImage = `url('${labelUrl}')`;
    });
    document.querySelectorAll('.top').forEach(el => {
        el.style.backgroundImage = `url('${topUrl}')`;
    });

    // navbar სიმაღლის ავტომატური გამოთვლა
    const navbar = document.querySelector('nav, .navbar, header');
    if (navbar) {
        const navH = navbar.offsetHeight;
        document.getElementById('level-wrapper').style.minHeight = `calc(100svh - ${navH}px)`;
        document.getElementById('level-wrapper').style.height = `calc(100svh - ${navH}px)`;
    }

    const canContainer = document.getElementById('canContainer');
    const cocaCola = document.getElementById('cocaCola');

    let isDragging = false;
    let isZooming = false;
    let previousMousePosition = { x: 0, y: 0 };
    let rotation = { x: -20, y: 180 };
    let currentZoom = 1;
    let initialDistance = null;

    // DESKTOP — drag to rotate
    canContainer.addEventListener('mousedown', function(e) {
        if (e.touches && e.touches.length >= 2) return;
        isDragging = true;
        previousMousePosition = { x: e.clientX, y: e.clientY };
        canContainer.style.cursor = 'grabbing';
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging || isZooming) return;

        const deltaX = e.clientX - previousMousePosition.x;
        const deltaY = e.clientY - previousMousePosition.y;

        rotation.y += deltaX * 0.5;
        rotation.x -= deltaY * 0.2;
        rotation.x = Math.max(-60, Math.min(10, rotation.x));

        updateCanRotation();
        previousMousePosition = { x: e.clientX, y: e.clientY };
    });

    document.addEventListener('mouseup', function() {
        isDragging = false;
        canContainer.style.cursor = 'grab';
    });

    // DESKTOP — scroll to zoom
    canContainer.addEventListener('wheel', function(e) {
        e.preventDefault();
        if (e.deltaY < 0) {
            if (currentZoom < 3) currentZoom += 0.1;
        } else {
            if (currentZoom > 0.5) currentZoom -= 0.1;
        }
        updateZoom();
    });

    // MOBILE — touch rotate + pinch zoom
    canContainer.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
            isDragging = true;
            previousMousePosition = {
                x: e.touches[0].clientX,
                y: e.touches[0].clientY
            };
        } else if (e.touches.length === 2) {
            isZooming = true;
            isDragging = false;
            initialDistance = getDistance(
                e.touches[0].clientX, e.touches[0].clientY,
                e.touches[1].clientX, e.touches[1].clientY
            );
        }
        e.preventDefault();
    });

    canContainer.addEventListener('touchmove', function(e) {
        if (e.touches.length === 1 && isDragging && !isZooming) {
            const deltaX = e.touches[0].clientX - previousMousePosition.x;
            const deltaY = e.touches[0].clientY - previousMousePosition.y;

            rotation.y += deltaX * 0.5;
            rotation.x -= deltaY * 0.2;
            rotation.x = Math.max(-60, Math.min(10, rotation.x));

            updateCanRotation();
            previousMousePosition = {
                x: e.touches[0].clientX,
                y: e.touches[0].clientY
            };
        } else if (e.touches.length === 2 && isZooming) {
            const currentDistance = getDistance(
                e.touches[0].clientX, e.touches[0].clientY,
                e.touches[1].clientX, e.touches[1].clientY
            );

            if (initialDistance !== null) {
                const zoomFactor = currentDistance / initialDistance;
                const newZoom = currentZoom * zoomFactor;

                if (newZoom >= 0.5 && newZoom <= 3) {
                    currentZoom = newZoom;
                    updateZoom();
                }

                initialDistance = currentDistance;
            }
        }
        e.preventDefault();
    });

    canContainer.addEventListener('touchend', function(e) {
        if (e.touches.length < 2) {
            isZooming = false;
            initialDistance = null;
        }
        if (e.touches.length === 0) {
            isDragging = false;
        }
    });

    function getDistance(x1, y1, x2, y2) {
        return Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
    }

    function updateCanRotation() {
        cocaCola.style.transform = `rotateX(${rotation.x}deg) rotateY(${rotation.y}deg)`;
    }

    function updateZoom() {
        canContainer.style.transform = `scale(${currentZoom})`;
    }

    updateCanRotation();
    updateZoom();
});
</script>

@endsection
@else
                       <div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">level{{ $level }}</h5>
                    @if($userLevel > $level)
                        <div class="alert alert-success">თქვენ გაიარეთ ეს ტური წარმატებით</div>
                    @else
                        <div class="alert alert-warning">⚠️ ეს დონე ჯერ არ არის ხელმისაწვდომი</div>
                    @endif
                    <a href="{{ route('levels.show', ['level' => $userLevel]) }}" class="btn btn-primary">გადადით მიმდინარე დონეზე</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
