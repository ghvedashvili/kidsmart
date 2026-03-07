@if($userLevel == $level)
<!-- @if($question->type == 'action')

<form id="answerForm">
    @csrf
    <button class="btn btn-success w-100">
        Continue →
    </button>
</form>

@else

<form id="answerForm">
    @csrf
    <input type="text" class="form-control mb-2" id="answer">
    <button class="btn btn-primary">Submit</button>
</form>

@endif -->


<style>

@import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');

body{
    margin:0;
    height:100vh;
    background:#f7f7f7;
      overflow:hidden; /* სკროლის გამორთვა */
}

#continue-btn{
    display:none;
    margin-top:30px;
    padding:15px 25px;
    border:2px solid #000;
    background:#fff;
    cursor:pointer;
    font-family:'Press Start 2P', monospace;
    font-size:14px;
}

#status{
    margin-top:20px;
    font-size:12px;
    font-family:'Press Start 2P', monospace;
}

.wrapper{
    /* height:100vh; */
    display:flex;
    justify-content:center;
    align-items:center;
}

.game-box{
    text-align:center;
}

#dino-img{
    width:300px;
    height:auto;
    display:none;
}

#continue-btn{
    display:none;
    margin-top:30px;
    padding:15px 25px;
    border:2px solid #000;
    background:#fff;
    cursor:pointer;
    font-family:'Press Start 2P', monospace;
    font-size:14px;
}

#continue-btn:hover{
    background:#000;
    color:#fff;
}

#status{
    margin-top:20px;
    font-size:12px;
}
.disabled-btn{
    opacity:0.4;
    cursor:not-allowed;
}
</style>


<div class="wrapper">

<div class="game-box">

<img id="dino-img" src="" alt="Dino">

@if($question->type == 'action')

<form id="answerForm">
@csrf
<button id="continue-btn" >
Continue →
</button>
</form>

@else

<form id="answerForm">
@csrf

<input type="text" class="form-control mb-2" id="answer">

<button type="submit">
შემდეგი ეტაპი ->
</button>

</form>

@endif

<div id="status"></div>

</div>

</div>

<script>
const dinoImg = document.getElementById("dino-img");
const continueBtn = document.getElementById("continue-btn");
const statusText = document.getElementById("status");

const onlineGif = "/img/Dino_on.gif";
const offlineGif = "/img/Dino_off.gif";

let offlineTriggered = false;
let internetAvailable = navigator.onLine;
// preload
new Image().src = onlineGif;
new Image().src = offlineGif;

function showOnline(){

    if(offlineTriggered){
        continueBtn.disabled = false;
        continueBtn.style.display = "inline-block";
         continueBtn.classList.remove("disabled-btn");

        statusText.innerText = "ინტერნეტი აღდგენილია";
        return;
    }

    dinoImg.src = onlineGif;
    dinoImg.style.display = "block";
continueBtn.classList.add("disabled-btn");

    continueBtn.disabled = false;
    continueBtn.style.display = "none";

    statusText.innerText = "T-rex მიგიყვანს შემდეგ ეტაპამდე";
}

function showOffline(){

    offlineTriggered = true;

    dinoImg.src = offlineGif;
    dinoImg.style.display = "block";

    //continueBtn.disabled = true;
    continueBtn.style.display = "inline-block";

    statusText.innerText = "⚠️ გთხოვთ აღადგინოთ ინტერნეტი";

}

if(navigator.onLine){
    showOnline();
}else{
    showOffline();
}

window.addEventListener("online", showOnline);
window.addEventListener("offline", showOffline);
continueBtn.addEventListener("click", function(e){

    if(!internetAvailable){

        e.preventDefault();

        Swal.fire({
            icon: "warning",
            title: "ინტერნეტი არ არის",
            text: "გთხოვთ ჩართოთ ინტერნეტი რომ გააგრძელოთ",
            confirmButtonText: "გასაგებია"
        });

    }

});
</script>



@else

<div class="container mt-5 pt-5">
<div class="row justify-content-center">
<div class="col-md-6">

<div class="card">
<div class="card-body text-center">

<h5 class="card-title">level {{ $level }}</h5>

@if($userLevel > $level)

<div class="alert alert-success">
თქვენ გაიარეთ ეს ტური წარმატებით
</div>

@else

<div class="alert alert-warning">
⚠️ ეს დონე ჯერ არ არის ხელმისაწვდომი
</div>

@endif

<a href="{{ route('levels.show',['level'=>$userLevel]) }}" class="btn btn-primary">
გადადით მიმდინარე დონეზე
</a>

</div>
</div>

</div>
</div>
</div>

@endif