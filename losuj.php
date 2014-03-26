<?php

  //napisal to mazaq1
  //ale jesli chcesz to mozesz to zmodyfikowac i wykorzystac
  //byle bys tylko nie zarabial na tym kasy
  //bylo by to dla mnie lekko wkurzajace ;)
  
  include("parametry.php");
  
  //funkcja laczaca sie z api wykopu i pobierajaca jeden mirkowy wpis
  //razem z wszystkimi plusami i komentarzami
  //zwracany jest obiekt zawierajacy wszystkie dane wpisu
  //robie to zwyklym curl, api opiera sie o architekture REST
  //szczerze mowiac nie znam sie na tym, ale tak jak ponizej dziala ok
  function get_wpis($wpis_id) {
    $adres="http://a.wykop.pl/entries/index/".$wpis_id."/appkey,".WYKOP_API_KEY;
              
    $podpis=md5(WYKOP_API_SEKRET.$adres);
    $header[] = "apisign:".$podpis;
    
    $curl_obiekt = curl_init();
    curl_setopt($curl_obiekt, CURLOPT_URL, $adres); 
    curl_setopt($curl_obiekt, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($curl_obiekt, CURLOPT_HTTPHEADER, $header); 
    $output = json_decode(curl_exec($curl_obiekt)); 
    curl_close($curl_obiekt);  

    return  $output;
  }

  //laczenie sie z baza
  $baza=mysql_connect(MYSQL_HOST,MYSQL_USER,MYSQL_PASS);
  mysql_select_db(MYSQL_DB);
  
  //sprawdzanie czy w gecie podane sa odpowiednie zmienne
  if(!isset($_GET['step']) || !isset($_GET['id'])){
     die('<p>niepoprawne wywolanie</p>');
  }

  //wyciaganie z adresu wpisu pierwszego ciagu liczb - czyli id wpisu
  preg_match_all('!\d+!', $_GET['id'], $id_numbers);
  $id_wpisu=$id_numbers[0][0];
  
  //pobranie calego wpisu do zmiennej wpis
  $wpis=get_wpis($id_wpisu);
  
  ////////////////////////////////////////////////////////////////////////
  /////// step=1, czyli wykonuje sie tu pierwsza czesc programu //////////            
  /////// tzn. wyciaganie wpisu i sprawdzanie czy byly juz na   //////////
  /////// nim przeprowadzane jakies losowania                   //////////
  ////////////////////////////////////////////////////////////////////////
  
  switch($_GET['step']){
  
    case '1':
    
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
            echo '<div onclick="help(this)" class="hintbox"><span class="pytajnik">Ktoś już przeprowadzał losowanie na tym wpisie. Co to oznacza?</span><p style="display:none" class="hint">Widzę, że ktoś już sprawdzał wyniki losowań w tym wpisie. Sprawdź czy nie ma ich na liście poniżej. Zwróć jednak dokładnie uwagę na daty. Zazwyczaj praktyka jest taka, że data i godzina początkowa powinna być datą opublikowania wpisu a data i godzina końcowa ma być taka, jak zażyczył sobie autor wpisu. Najczęściej jest to równa godzina, np: 16:00:00. Uważaj jednak czy ktoś nie zrobił innego losowania z bardzo podobnym zakresem godzin, ale losowania nie będącego obowiązującym i zdeklarowanym przez autora! Przykładowo ktoś mógł ustawić którąś z dat o jedną sekundę później. Jeśli lista z losowaniami jest <b>naprawdę</b> długa to nie musisz jej ręcznie przeszukać. Wpisz po prostu zakres dat w formularzu poniżej listy!</p></div>';
            echo '<div style="width:700px; max-height:200px; overflow-y:auto;">';
            
            //wyswietlenie listy zakonczonych losowan na danym wpisie
            while($r=mysql_fetch_assoc($zap)){
               echo '<p>Plusy od <span class="data-od">'.$r['losowanie_od'].'</span> do <span class="data-do">'.$r['losowanie_do'].'</span> wygral: <a target="_blank" href="http://www.wykop.pl/ludzie/'.$r['zwyciezca'].'"><span class="login">'.$r['zwyciezca'].'</span></a></p>';
            }
            
            echo '</div>';
            echo '<h2><b>Krok 2.</b> Poniżej możesz sprawdzić wyniki losowania lub przeprowadzić nowe. Wpisz tylko zakres dat i godzin:</h2>';
            echo '<div onclick="help(this)" class="hintbox"><span class="pytajnik">Chciałbym coś wylosować w tym wpisie, ale ktoś już tu losował. Co teraz?</span><p style="display:none" class="hint">Jeśli jesteś autorem wpisu i widzisz, że ktoś już zrobił losowanie, które Cię interesuje wpisując poprawne dane - nie przejmuj się - losowanie takie jest w takim razie ważne i obowiązujące. Jeśli jednak nie ma ustawionego dobrego losowania z odpowiednim zakresem dat - utwórz je teraz! Jeśli w formularzu poniżej wpiszesz dane losowania, które się już odbyło, pamiętaj że <b>nie nadpisze się ono</b>, tylko podam Ci kto wygrał. Nie musisz się więc niczym przejmować!</p></div>';                                                                                                             
          }
          else{
            echo '<h2><b>Krok 2.</b>Nikt jeszcze nie przeprowadzał losowania na tym wpisie. Możesz zrobić to jako pierwszy.</h2>';
            echo '<div onclick="help(this)" class="hintbox"><span class="pytajnik">Chciałbym rozpocząć nowe losowanie. Jak to zrobić?</span><p style="display:none" class="hint">A więc jeszcze ten wpis nie ma żadnego losowania. Wygląda na to, że Ty musisz je przeprowadzić. Aby rozpocząć nowe losowanie wypełnij poniższe pola z datami. Pamiętaj tylko, żeby uważnie wpisać datę i godzinę zakończenia.</p></div>';
            
          }
          echo '<div onclick="help(this)" class="hintbox"><span class="pytajnik">Jak mam poprawnie wypełnić pola z datami?</span><p style="display:none" class="hint">Daty i godziny wpisujemy w formacie: rok-miesiąc-dzień godzina:minuta:sekunda, przykładowo 2014-06-11 12:00:00. Data początkowa podstawiła sie automatycznie i jest datą dodania wpisu. Nie polecam jej zmieniać. Godzina zakończenia podstawiła się aktualna - trzeba ją zmodyfikować. Polecam ustawiać pełne godziny, np 16:00:00. <b>Jeśli zmodyfikujesz datę rozpoczęcia, będe uwzględniał tylko osoby, które dały plusa po danej godzinie!</b></p></div>';
          echo '<p>od: <input value="'.$wpis->date.'" type="text" size="17" id="data_od"> do: <input value="'.date("Y-m-d H:i:s").'" type="text" size="17" id="data_do"> <a href="javascript:losuj()">rozpocznij losowanie</a></p>';
        }
        
        //zapytanie o losowania w tym wpisie nie powiodlo sie, prawdopodobnie id
        //jest jakies nieprawidlowe, albo ktos sie glupio bawi aplikacja
        else{
          die('<p class="error">Niestety źle wpisałeś adres wpisu :/. Aby uzyskać adres wpisu, kliknij na znaczek #, który znajduje się obok liczby plusów.</p>');
        }
      }
      
      //wpis ma pustego autora, wiec prawdopodobnie nie istnieje wpis o takim id
      else{
        die('<p class="error">Niestety nie znalazłem takiego wpisu :/. Może autor go już usunął? Sprawdź jeszcze raz czy wkleiłeś poprawny adres!</p>');      
      }
      
    break;
    
    /////////////////////////////////////////////////////////////////////
    ////// step=2, czyli wykonuje sie tu druga czesc wpisu, a wiec //////
    ////// losowanie lub sprawdzenie wynikow losowania //////////////////
    /////////////////////////////////////////////////////////////////////
    
    case '2':
    
      //przekonwertowanie stringow z czasem z formularza na format czasu,
      //ktory mozna latwo porownac
      $czas_od=strtotime(urldecode($_GET['data_od']));
      $czas_do=strtotime(urldecode($_GET['data_do']));
      
      //nowa pusta tablica, gdzie bede wrzucal loginy plusujacych
      $nowatab=array();   
        
      echo '<p class="male">';
      
      //jesli obiekt wpis nie ma votersow, to znaczy ze nikt nie plusowal tego wpisu
      if($wpis->voters==null){
        die('<p class="error">Niestety nikt nie plusowal tego wpisu :/</p>');
      } 

      //sprawdzenie roznicy czasow
      if($czas_od>$czas_do){
        die('<p class="error">Godzina zakończenia losowania jest wcześniejsza niż godzina rozpoczęcia!</p>');
      }
      
      //losowanie moze sie odbyc po czasie, ktory deklarujemy jako czas zakonczenia
      //dodaje do tego margines 2 minuty, bo nas serwerach moze byc lekko inny czas      
      if((time()-120)<$czas_do){
        die('<p class="error">Musisz poczekać z losowaniem do godziny jego zakończenia. Jeśli nadeszła już ta godzina, odczekaj jeszcze około 2 minuty (ewentualna różnica czasu na serwerach dla bezpieczeństwa).</p>');
      }

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
      
      //sprawdzenie ile ludzi bierze udzial w losowaniu
      $ilu_bierze_udzial=count($nowatab);
      if($ilu_bierze_udzial==1){
         die('<p class="error">Tylko jedna osoba jest uprawniona do udziału w losowaniu (jedyny plus w danym zakresie czasu). Sens losowania stoi więc pod sporym znakiem zapytania :).</p>');
      }
      else if($ilu_bierze_udzial==0){
         die('<p class="error">Niestety nikt nie dał plusa w tym zakresie czasu :/. Musisz rozszerzyć ramy czasowe.</p>');
         break;
      }
      
      //losowanie - najprostszy sposob - mozna ewentualnie tu pofantazjowac
      //i dodac jakies seedy z id wpisu itd...
      $wylosowany=$nowatab[array_rand($nowatab)];
      
      //wylosowany wpis dodaje do bazy        
      $zap=mysql_query('insert into losowania set wpis='.$id_wpisu.', losowanie_od="'.urldecode($_GET['data_od']).'", losowanie_do="'.urldecode($_GET['data_do']).'", zwyciezca="'.$wylosowany.'"');
      
      //jesli zapytanie sie powiodlo to podajemy zwyciezce i koniec
      if($zap){
        echo '<p class="result">Zwyciezca tego losowania jest: <a target="_blank" href="http://www.wykop.pl/ludzie/'.$wylosowany.'"><span class="winner">'.$wylosowany.'</span></a></p>';
      }
      
      //jesli zapytanie sie nie powiodlo to zapewne znaczy, ze takie losowanie juz
      //jest w bazie (klucze unique w tabeli zablokowaly inserta)
      //w takim wypadku odczytuje wylosowanego wczesniej zwyciezce i go podaje
      else{
        $zap=mysql_query('select * from losowania where wpis='.$id_wpisu.' and losowanie_od="'.urldecode($_GET['data_od']).'" and losowanie_do="'.urldecode($_GET['data_do']).'"');
        if($zap){
          $r=mysql_fetch_assoc($zap);
          echo '<p>Zwyciezca tego losowania jest: <a target="_blank" href="http://www.wykop.pl/ludzie/'.$r['zwyciezca'].'"><span class="winner">'.$r['zwyciezca'].'</span></a></p>';
        }
        else{
          die('<p>Cos poszlo nie tak :(</p>');
        }        
      }     
    break;
  }
  
  //rozlaczenie z baza
  mysql_close($baza);
?>