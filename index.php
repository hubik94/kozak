<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Mirkorandom</title>
		<link rel="stylesheet" href="/main.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script>

//pobiera z losuj.php wpis z mirko
function checkid(){
  $("#waiter").html("pobieram dane...");
  $( "#step2" ).fadeOut();
  $( "#step3" ).fadeOut();
    
  $.ajax({
    url: "losuj.php?id="+$("#idwpisu").val()+"&step=1",
    cache: false
  }).done(function( html ) {
      $("#waiter").html("gotowy");
      $( "#step2" ).html( html );
      $( "#step2" ).fadeIn();
      $( "#step3" ).fadeOut();
  });
}

//losuje lub sprawdza wyniki losowania
function losuj(){
$("#waiter").html("pobieram dane...");
$( "#step3" ).fadeOut();
  
  data_form_od=encodeURIComponent($("#data_od").val());
  data_form_do=encodeURIComponent($("#data_do").val());
  $.ajax({
    url: "losuj.php?id="+$("#idwpisu").val()+"&data_od="+data_form_od+"&data_do="+data_form_do+"&step=2",
    cache: false
  }).done(function( html ) {
      $( "#waiter" ).html("gotowy");
      $( "#step3" ).html( html );
      $( "#step3" ).fadeIn();
  });

}

//otwieranie i pokazanie helpa
function help(div_help){
  $(div_help).children("p").show();
  $(div_help).children(".pytajnik").hide();
}
  
    </script>
	</head>
	<body>
<div id="step1">
  <h1>Witaj w maszynie losującej Mirkorandom ;) [trwa przerwa techniczna - 5 minutek ;)]</h1>
  <h3 id="waiter">gotowy</h3>
  <div onclick="help(this)" class="hintbox"><span class="pytajnik">Kliknij tutaj jeśli nie wiesz jeszcze jak działa Mirkorandom ani do czego służy!</span><p style="display:none" class="hint">Oto <b>Mirkorandom</b>, czyli Mirkoblogowy automat do losowania jednej osoby, spośród wszsytkich, którzy dali plusa. Aby losować nie musisz być autorem wpisu. Jeśli jednak jesteś autorem to nie musisz nic robić <b>przed</b> dodaniem wpisu na Mirko. Po prostu określ dokładnie do której godziny plusy zbierasz i po tej godzinie wejdź na tą stronę. Jeśli jednak chcesz wylosować teraz, zapraszam do <b>kroku 1</b>:</p></div>
  <h2><b>Krok 1.</b> Wpisz adres wpisu na mirko, lub jego liczbowy identyfikator.</h2>
  <p><input onchange="checkid()" type="text" id="idwpisu"> <a href="javascript:checkid()">sprawdź wpis</a></p>
</div>

<div style="visible:none" id="step2"></div>

<div style="visible:none" id="step3"></div>

	</body>
</html>