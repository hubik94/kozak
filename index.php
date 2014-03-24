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
            $("#waiter").html("gotowy");
            $( "#step3" ).html( html );
            $( "#step3" ).fadeIn();
        });

      }
    </script>
	</head>
	<body>
<div id="step1">
<h1>Witaj w maszynie losującej Mirkorandom ;)</h1>
<h3 id="waiter">gotowy</h3>
<p class="hint">Jeśli jesteś autorem wpisu, który chce coś losować, pamiętaj, że losujemy <b>po</b> ustalonej godzinie zakończenia. Więc jeśli zbierasz jeszcze plusy, wróć tutaj później ;)</p>
<h2><b>Krok 1.</b> Wpisz adres wpisu na mirko, lub jego liczbowy identyfikator.</h2>

<p><input type="text" id="idwpisu"> <a href="javascript:checkid()">sprawdź wpis</a></p>
</div>
<div style="visible:none" id="step2">

</div>
<div style="visible:none" id="step3">

</div>
	</body>
</html>