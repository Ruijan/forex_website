<?php
	
	include dirname(__FILE__)."/event.php";
	$dv_p_tm5 = 0;
	$dv_p_t0 = 0;
	$display = 'table';
	if(isset($_GET["dv_p_tm5"]))
		$dv_p_tm5 = $_GET["dv_p_tm5"];
	if(isset($_GET["dv_p_t0"]))
		$dv_p_t0 = $_GET["dv_p_t0"];
	if(isset($_GET["display"]))
		$display = $_GET["display"];
	
	$dom = new DOMDocument('1.0');
	$url = "https://sslecal2.forexprostools.com?columns=exc_flags,exc_currency,exc_importance,exc_actual,exc_forecast,exc_previous&features=datepicker,timezone&countries=25,32,6,37,72,22,17,39,14,10,35,43,56,36,110,11,26,12,4,5&calType=day&timeZone=55&lang=1";
    @$dom->loadHTMLFile($url);
    $table = $dom->getElementByID('ecEventsTable')->getElementsByTagName('tbody')[0]->getElementsByTagName('tr');
    
    if($display == 'table'){
    	echo 'Results: <br/>';
    	echo '<table>';
    }
    foreach ($table as $line){
    	if  ($line->hasAttribute('event_attr_id')){
    		$event = new Event();
    		$event->id = $line->getAttribute('event_attr_id');
    		$id_event = $line->getAttribute('id');
    		$id_event = split('_', $id_event)[1];
    		foreach($line->getElementsByTagName('td') as $column){
    			if($column->hasAttribute('evtstrttime')){
    				$event->time = $column->getAttribute('evtstrttime');
    			}
    			elseif ($column->getAttribute('id') == 'eventActual_'.$id_event) {
    				$event->actual = '';
    				for($i = 0; $i < strlen($column->nodeValue); $i += 1){
    					$c = $column->nodeValue[$i];
    					if(is_numeric($c) or $c == '.' or $c == '-'){
    						$event->actual = $event->actual.$c;
    					}
    				}
    			}
    			elseif ($column->getAttribute('id') == 'eventPrevious_'.$id_event) {
    				$event->previous = '';
    				for($i = 0; $i < strlen($column->nodeValue); $i += 1){
    					$c = $column->nodeValue[$i];
    					if(is_numeric($c) or $c == '.' or $c == '-'){
    						$event->previous = $event->previous.$c;
    					}
    				}
    			}
    			elseif ($column->getAttribute('id') == 'eventForecast_'.$id_event) {
    				$event->forecast = '';
    				for($i = 0; $i < strlen($column->nodeValue); $i += 1){
    					$c = $column->nodeValue[$i];
    					if(is_numeric($c) or $c == '.' or $c == '-'){
    						$event->forecast = $event->forecast.$c;
    					}
    				}
    			}
    		}
    		$event->getPrediction($dv_p_tm5, $dv_p_t0);
    		if($display == 'table'){
				$event->displayEventInTable();
    		}
    		else{
    			$event->displayEvent();
    			echo '<br/>';
    		}
    	}
    }
    if($display == 'table'){
    	echo '</table>';
    }  
?>