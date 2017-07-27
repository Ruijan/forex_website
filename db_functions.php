<?php
require_once("db_events.php");

function createEvent($mysqli){
	$dom = new DOMDocument('1.0');
	$url = "https://sslecal2.forexprostools.com?columns=exc_flags,exc_currency,exc_importance,exc_actual,exc_forecast,exc_previous&features=datepicker,timezone&countries=25,32,6,37,72,22,17,39,14,10,35,43,56,36,110,11,26,12,4,5&calType=day&timeZone=55&lang=1";
    @$dom->loadHTMLFile($url);
    $table = $dom->getElementByID('ecEventsTable')->getElementsByTagName('tbody')[0]->getElementsByTagName('tr');
    $previous_event = null;
    foreach ($table as $line){
    	if  ($line->hasAttribute('event_attr_id') && $line->hasAttribute('id')){
    		$e = new Event();
    		$e->event_id = $line->getAttribute('event_attr_id');
    		$e->news_id = $line->getAttribute('id');
    		$e->news_id = split('_', $e->news_id)[1];
    		$e->t_a = DateTime::createFromFormat('Y-m-d H:i:s', $line->getAttribute('event_timestamp'));
    		$e->t_r = $e->t_a;

            
    		$e = fillEventFromHTMLLine($line, $e);
            if(!is_null($previous_event)){
                $time_diff = $e->t_a->diff($previous_event->t_a);
                $previous_event->next_event = $time_diff->s + $time_diff->i*60 + $time_diff->h*60*60 + $time_diff->d*24*60*60;
                $previous_event->tryAddingEventToDB($mysqli);
                if ($previous_event->id != -1){
                    $actual = $previous_event->actual;
                    $previous_event->fillFromDB($mysqli);
                    if($actual != 0 and $actual != $previous_event->actual and $previous_event->state == 0){
                        $previous_event->actual = $actual;
                        $previous_event->state = 1;
                        $now = new DateTime("now");
                        $now->setTimezone(new DateTimeZone("UTC"));
                        $previous_event->t_r = $now;
                        $previous_event->modifyInDB($mysqli);
                    }
                }
            }
            $previous_event = $e;
    	}
    }
}

function fillEventFromHTMLLine($line, $e){
    foreach($line->getElementsByTagName('td') as $column){
        if($column->hasAttribute('evtstrttime')){
            $e->time = $column->getAttribute('evtstrttime');
        }
        elseif ($column->getAttribute('id') == 'eventActual_'.$e->news_id) {
            $e->actual = '';
            for($i = 0; $i < strlen($column->nodeValue); $i += 1){
                $c = $column->nodeValue[$i];
                if(is_numeric($c) or $c == '.' or $c == '-'){
                    $e->actual = $e->actual.$c;
                    $e->state = 1;
                }
            }
        }
        elseif ($column->getAttribute('id') == 'eventPrevious_'.$e->news_id) {
            $e->previous = '';
            for($i = 0; $i < strlen($column->nodeValue); $i += 1){
                $c = $column->nodeValue[$i];
                if(is_numeric($c) or $c == '.' or $c == '-'){
                    $e->previous = $e->previous.$c;
                }
            }
        }
    }
    return $e;
}

function updateMarket($mysqli){
	$now = new DateTime("now");
    $now->setTimezone(new DateTimeZone("UTC"));
	if(isset($_POST["dv_p_tm5"]) and isset($_POST["dv_p_t0"])){
		$now = new DateTime("now");
        $now->setTimezone(new DateTimeZone("UTC"));
        if (!($stmt = $mysqli->prepare("UPDATE events SET t_update=?, dv_p_tm5=?, dv_p_t0=?, state = 2 WHERE state = 1 and t_real < DATE_ADD(UTC_TIME(), INTERVAL -300 SECOND)"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
            throw new Exception("2");
        }
        if (!$stmt->bind_param("sdd", $now->format("Y-m-d H:i:s"), $_POST["dv_p_tm5"], $_POST["dv_p_t0"])) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
            throw new Exception("3");
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            throw new Exception("4");
        }
	}
}

function getEventsFromDate($mysqli, $date=-1, $state=-1){
    $table = '<table>';
    $table = $table.(Event::displayShortHeadersAsRow());
    $where_suffix = "";
    if($date!=-1){
        $where_suffix = "WHERE cast(t_announced as date)=cast('".strftime("%Y-%m-%d", $date)."' as date)";
    }
    if($state != -1){
        if($where_suffix != ""){
            $where_suffix = $where_suffix." AND ";
        }
        else{
            $where_suffix = 'WHERE';
        }
        $where_suffix = $where_suffix." state=".$state;
    }
    $sql =  "SELECT * FROM events ".$where_suffix;
    if(!$result = $mysqli->query($sql)){
        die('There was an error running the query [' . $mysqli->error . ']');
    }
    while($row = $result->fetch_assoc()){
        $e = createEventFromRow($row);
        $table = $table.($e->displayAsRow());
    }
    $table = $table.'</table>';
    return $table;
}

function getTodayEvents($mysqli){
	$table = '<table>';
    $table = $table.(Event::displayHeadersAsRow());
	$sql =  "SELECT * FROM events WHERE cast(t_announced as date)=cast(UTC_TIME() as date)";
	if(!$result = $mysqli->query($sql)){
        die('There was an error running the query [' . $mysqli->error . ']');
    }
    while($row = $result->fetch_assoc()){
    	$e = createEventFromRow($row);
        $table = $table.($e->displayAsRow());
    }
    $table = $table.'</table>';
    return $table;
}

function createEventFromRow($row){
    $e = new Event();
    $e->id =  $row['id'];
    $e->event_id = $row["event_id"];
    $e->news_id = $row["news_id"];
    $e->t_a = $row["t_announced"];
    if (gettype($e->t_a)=="string"){
        $e->t_a = DateTime::createFromFormat('Y-m-d H:i:s', $e->t_a);
    }
    $e->t_r = $row["t_real"];
    if (gettype($e->t_r)=="string"){
        $e->t_r = DateTime::createFromFormat('Y-m-d H:i:s', $e->t_r);
    }
    $e->t_u = $row["t_update"];
    if (gettype($e->t_u)=="string"){
        $e->t_u = DateTime::createFromFormat('Y-m-d H:i:s', $e->t_u);
    }
    $e->actual = $row["actual"];
    $e->previous = $row["previous"];
    $e->dv_p_tm5 = $row["dv_p_tm5"];
    $e->dv_p_t0 = $row["dv_p_t0"];
    $e->prediction = $row["prediction"];
    $e->p_proba = $row["proba_predict"];
    $e->label = $row["label"];
    $e->av_success = $row["av_success"];
    $e->gain = $row["gain"];
    $e->state = $row["state"];
    $e->next_event = $row["next_event"];
    return $e;
}

function getNextEvents($mysqli){
	$table = '<table>';
	$table = $table.(Event::displayHeadersAsRow());
	$sql =  "SELECT * FROM events WHERE t_announced > DATE_ADD(UTC_TIME(), INTERVAL -300 SECOND) and state = 0";
	if(!$result = $mysqli->query($sql)){
        die('There was an error running the query [' . $mysqli->error . ']');
    }
    while($row = $result->fetch_assoc()){
    	$e = createEventFromRow($row);
        $table = $table.($e->displayAsRow());
    }
    $table = $table.'</table>';
    return $table;
}

function getPredictableEvents($mysqli){
	$table = '<table>';
    $table = $table.(Event::displayHeadersAsRow());
	$sql =  "SELECT * FROM events WHERE state = 2 and cast(t_announced as date)=cast(UTC_TIME() as date)";
	if(!$result = $mysqli->query($sql)){
        die('There was an error running the query [' . $mysqli->error . ']');
    }
    while($row = $result->fetch_assoc()){
    	$e = createEventFromRow($row);
        $table = $table.($e->displayAsRow());
    }
    $table = $table.'</table>';
    return $table;
}

function predict($mysqli){
	$e = new Event();
	$e->fillFromPost();
	$e->fillFromDB($mysqli);
	$e->fillFromPost();
	$e->state = 3;
	$e->modifyInDB($mysqli);
}

function getNextAction($mysqli){
	$sql =  "SELECT * FROM events WHERE state = 3 and t_real > DATE_ADD(UTC_TIME(), INTERVAL -500 SECOND) and t_real < DATE_ADD(UTC_TIME(), INTERVAL 500 SECOND)";
	if(!$result = $mysqli->query($sql)){
        die('There was an error running the query [' . $mysqli->error . ']');
    }
    while($row = $result->fetch_assoc()){
    	$e = createEventFromRow($row);
        return $e->simpleDisplay();
    }
}

function cancelEvent($mysqli){
	$e = new Event();
	$e->fillFromPost();
	$e->fillFromDB($mysqli);
	$e->state = -1;
	$e->modifyInDB($mysqli);
}

function openTrade($mysqli){
	$e = new Event();
	$e->fillFromPost();
	$e->fillFromDB($mysqli);
	$e->fillFromPost();
	$e->simpleDisplay();
	$e->state = 4;
	$e->modifyInDB($mysqli);
}

function closeTrade($mysqli){
	$e = new Event();
	$e->fillFromPost();
	$e->fillFromDB($mysqli);
	$e->fillFromPost();
	$e->state = 5;
	$e->modifyInDB($mysqli);
}
?>