<?php

  //napisal to mazaq1
  //ale jesli chcesz to mozesz to zmodyfikowac i wykorzystac
  //byle bys tylko nie zarabial na tym kasy
  //bylo by to dla mnie lekko wkurzajace ;)
  
  //zmienne globalne, ktore trzeba sobie ustawic
  $GLOBALS['WYKOP_API_KEY']='blahblah'; //api key, trzeba sobie taki uzyskac
  $GLOBALS['WYKOP_API_SEKRET']='blahblahblah'; //secret, rowniez trzeba uzyskac
  $GLOBALS['MYSQL_HOST']='mojafajnabaza';  //dane myslq
  $GLOBALS['MYSQL_USER']='user';
  $GLOBALS['MYSQL_PASS']='haslo';
  $GLOBALS['MYSQL_DB']='nazwabazy';
  
  //funkcja laczaca sie z api wykopu i pobierajaca jeden mirkowy wpis
  //razem z wszystkimi plusami i komentarzami
  //zwracany jest obiekt zawierajacy wszystkie dane wpisu
  //robie to zwyklym curl, api opiera sie o architekture REST
  //szczerze mowiac nie znam sie na tym, ale tak jak ponizej dziala ok
  function get_wpis($wpis_id) {
          $ch = curl_init();
          $adres="http://a.wykop.pl/entries/index/".$wpis_id."/appkey,".$GLOBALS['WYKOP_API_KEY']; 
          curl_setopt($ch, CURLOPT_URL, $adres); 
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
          $sekret=$GLOBALS['WYKOP_API_SEKRET'];
          $podpis=md5($sekret.$adres);
          $header[] = "apisign:".$podpis; 
          curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
          $output = json_decode(curl_exec($ch)); 
          curl_close($ch);  
  
          return  $output;
  }

  //laczenie sie z baza
  $baza=mysql_connect($GLOBALS['MYSQL_HOST'], $GLOBALS['MYSQL_USER'], $GLOBALS['MYSQL_PASS']);
  mysql_select_db($GLOBALS['MYSQL_DB']);
  
  //sprawdzanie czy w gecie podane sa odpowiednie zmienne
  if(isset($_GET['step']) && isset($_GET['id'])){
    //wyciaganie z adresu wpisu pierwszego ciagu liczb - czyli id wpisu
    preg_match_all('!\d+!', $_GET['id'], $id_numbers);
    $id_wpisu=$id_numbers[0][0];
    
    //pobranie calego wpisu do zmiennej wpis
    $wpis=get_wpis($id_wpisu);
    
    //step=1, czyli wykonuje sie tu pierwsza czesc programu
    //tzn. wyciaganie wpisu i sprawdzanie czy byly juz na
    //nim przeprowadzane jakies losowania
    if($_GET['step']==1){
      //jesli autor wpisu nie jest pusty to zakladam, ze taki wpis istnieje
      if($wpis->author!=null){
        echo '<p>Znalazłem taki wpis. Jego autorem jest <span class="login">'.$wpis->author.'</span>, a napisany został: <span class="data-od">'.$wpis->date.'</span></p>';
        $zap=mysql_query("select * from losowania where wpis=".$id_wpisu." order by losowanie_do");
    
        //sprawdzenie czy wykonalo sie poprawnie zapytanie o losowania w tym wpisie,
        //jesli tak, idziemy dalej
        if($zap){
          $ile_losowan=mysql_num_rows($zap);
          //sprawdzanie czy na tym wpisie odpbyly sie jakies losowania, wyswietlimy w obu przypadkach inne komunikaty
          if($ile_losowan>0){
            echo '<p class="hint">Widzę, że ktoś już sprawdzał wyniki losowań w tym wpisie. Sprawdź czy nie ma ich na liście poniżej. Zwróć jednak dokładnie uwagę na daty. Zazwyczaj praktyka jest taka, że data i godzina początkowa powinna być datą opublikowania wpisu a data i godzina końcowa ma być taka, jak zażyczył sobie autor wpisu. Najczęściej jest to równa godzina, np: 16:00:00. Uważaj jednak czy ktoś nie zrobił innego losowania z bardzo podobnym zakresem godzin, ale losowania nie będącego obowiązującym i zdeklarowanym przez autora! Przykładowo ktoś mógł ustawić którąś z dat o jedną sekundę później. Jeśli lista z losowaniami jest <b>naprawdę</b> długa to nie musisz jej ręcznie przeszukać. Wpisz po prostu zakres dat w formularzu poniżej listy!</p>';
            echo '<div style="width:700px; max-height:200px; overflow:scroll;">';
            //wyswietlenie listy zakonczonych losowan na danym wpisie
            while($r=mysql_fetch_assoc($zap)){
               echo '<p>Plusy od <span class="data-od">'.$r['losowanie_od'].'</span> do <span class="data-do">'.$r['losowanie_do'].'</span> wygral: <span class="login">'.$r['zwyciezca'].'</span></p>';
            }
            echo '</div>';
            echo '<h2><b>Krok 2.</b> Poniżej możesz sprawdzić wyniki losowania lub przeprowadzić nowe. Wpisz tylko zakres dat i godzin:</h2>';
            echo '<p class="hint">Jeśli jesteś autorem wpisu i widzisz, że ktoś już zrobił losowanie, które Cię interesuje wpisując poprawne dane - nie przejmuj się - losowanie takie jest w takim razie ważne i obowiązujące. Jeśli jednak nie ma ustawionego dobrego losowania z odpowiednim zakresem dat - utwórz je teraz! Jeśli w formularzu poniżej wpiszesz dane losowania, które się już odbyło, pamiętaj że <b>nie nadpisze się ono</b>, tylko podam Ci kto wygrał. Nie musisz się więc niczym przejmować!</p>';                                                                                                             
          }
          else{
            echo '<h2><b>Krok 2.</b>Nikt jeszcze nie przeprowadzał losowania na tym wpisie. Możesz zrobić to jako pierwszy.</h2>';
            echo '<p class="hint">A więc jeszcze ten wpis nie ma żadnego losowania. Wygląda na to, że Ty musisz je przeprowadzić. Pamiętaj tylko, żeby uważnie wpisać datę i godzinę zakończenia.</p>';
            
          }
          echo '<p class="hint">Daty i godziny wpisujemy w formacie: rok-miesiąc-dzień godzina:minuta:sekunda, przykładowo 2014-06-11 12:00:00. Data początkowa podstawiła sie automatycznie i jest datą dodania wpisu. Nie polecam jej zmieniać. Godzina zakończenia podstawiła się aktualna - trzeba ją zmodyfikować. Polecam ustawiać pełne godziny, np 16:00:00. <b>Jeśli zmodyfikujesz datę rozpoczęcia, będe uwzględniał tylko osoby, które dały plusa po danej godzinie!</b></p>';
          echo '<p>od: <input value="'.$wpis->date.'" type="text" size="17" id="data_od"> do: <input value="'.date("Y-m-d H:i:s").'" type="text" size="17" id="data_do"> <a href="javascript:losuj()">rozpocznij losowanie</a></p>';
        }
        //zapytanie o losowania w tym wpisie nie powiodlo sie, prawdopodobnie id
        //jest jakies nieprawidlowe, albo ktos sie glupio bawi aplikacja
        else{
          echo '<p>Niestety źle wpisałeś adres wpisu :/. Aby uzyskać adres wpisu, kliknij na znaczek #, który znajduje się obok liczby plusów.</p>';
        }
      }
      //wpis ma pustego autora, wiec prawdopodobnie nie istnieje wpis o takim id
      else{
        echo '<p>Niestety nie znalazłem takiego wpisu :/. Może autor go już usunął? Sprawdź jeszcze raz czy wkleiłeś poprawny adres!</p>';      }
      
    }
    //step=2, czyli wykonuje sie tu druga czesc wpisu, a wiec
    //losowanie lub sprawdzenie wynikow losowania
    else if($_GET['step']==2){
      //przekonwertowanie stringow z czasem z formularza na format czasu,
      //ktory mozna latwo porownac
      $czas_od=strtotime(urldecode($_GET['data_od']));
      $czas_do=strtotime(urldecode($_GET['data_do']));
      //nowa pusta tablica, gdzie bede wrzucal loginy plusujacych
      $nowatab=array();     
      echo '<p class="male">';
      //jesli obiekt wpis nie ma votersow, to znaczy ze nikt nie plusowal tego wpisu
      if($wpis->voters==null){
        echo "<p>Niestety nikt nie plusowal tego wpisu :/</p>";
      }
      else{
        //losowanie moze sie odbyc po czasie, ktory deklarujemy jako czas zakonczenia
        //dodaje do tego margines 2 minuty, bo nas serwerach moze byc lekko inny czas      
        if((time()-120)<$czas_do){
          echo '<p>Musisz poczekać z losowaniem do godziny jego zakończenia. Jeśli nadeszła już ta godzina, odczekaj jeszcze około 2 minuty (ewentualna różnica czasu na serwerach dla bezpieczeństwa).</p>';
        }
        else{
          //petla po wszystkich plusach, za kazdym razem sprawdzam czy data plusa znajduje
          //sie w zdeklarowanym zakresie czasu
          //jesli tak bedzie to login wrzucam do tablicy nowatab, w ktorej bedziemy losowac zwyciezce
          foreach($wpis->voters as $ludz){
            $czas_koment=$time = strtotime($ludz->date);
            if(($czas_od<=$czas_koment)&&($czas_koment<=$czas_do)){
              $login="".$ludz->author;
              array_push($nowatab,$login);            
            }                              
          }
          echo '</p>';
          //losowanie - najprostszy sposob - mozna ewentualnie tu pofantazjowac
          //i dodac jakies seedy z id wpisu itd...
          $wylosowany=$nowatab[array_rand($nowatab)];
          //wylosowany wpis dodaje do bazy        
          $zap=mysql_query('insert into losowania set wpis='.$id_wpisu.', losowanie_od="'.urldecode($_GET['data_od']).'", losowanie_do="'.urldecode($_GET['data_do']).'", zwyciezca="'.$wylosowany.'"');
          //jesli zapytanie sie powiodlo to podajemy zwyciezce i koniec
          if($zap){
            echo '<p class="result">Zwyciezca tego losowania jest: <span class="winner">'.$wylosowany.'</span></p>';
          }
          //jesli zapytanie sie nie powiodlo to zapewne znaczy, ze takie losowanie juz
          //jest w bazie (klucze unique w tabeli zablokowaly inserta)
          //w takim wypadku odczytuje wylosowanego wczesniej zwyciezce i go podaje
          else{
            $zap=mysql_query('select * from losowania where wpis='.$id_wpisu.' and losowanie_od="'.urldecode($_GET['data_od']).'" and losowanie_do="'.urldecode($_GET['data_do']).'"');
            if($zap){
              $r=mysql_fetch_assoc($zap);
              echo '<p>Zwyciezca tego losowania jest: <span class="winner">'.$r['zwyciezca'].'</span></p>';
            }
            //nie mam pojecia czemu ten select mialby sie nie udac, ale na wszelki wypadek...
            else{
              echo '<p>Cos poszlo nie tak :(</p>';
            }
            
          }
        }
      }
    }
  
  } 
  //rozlaczenie z baza
  mysql_close($baza);
?>