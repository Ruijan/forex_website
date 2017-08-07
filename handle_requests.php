<?php
	require_once("Event.php");
	require_once("db_functions.php");
	function handleRequest(){
		$action = null;
		if(isset($_POST["action"])){
            $action = $_POST["action"];
        }
        if(isset($_GET["action"])){
            $action = $_GET["action"];
        }
        if($action != null){
        	$mysqli = connect_database();
        	if($action == "update_market"){
        		createEvent($mysqli);
        		updateMarket($mysqli);
        	}
        	elseif ($action == "predict") {
        		predict($mysqli);
        	}
        	elseif ($action == "create_event") {
        		createEvent($mysqli);
        	}
        	elseif ($action == "get_today_events"){
        		createEvent($mysqli);
        		echo getTodayEvents($mysqli);
        	}
        	elseif ($action == "get_next_events"){
        		createEvent($mysqli);
        		echo getNextEvents($mysqli);
        	}
        	elseif ($action == "get_predictable_events"){
        		createEvent($mysqli);
        		echo getPredictableEvents($mysqli);
        	}
        	elseif($action == "get_next_action"){
        		echo getNextAction($mysqli);
        	}
        	elseif($action == "cancel_event"){
        		cancelEvent($mysqli);
        	}
        	elseif($action == "open_trade"){
        		openTrade($mysqli);
        	}
        	elseif($action == "close_trade"){
        		closeTrade($mysqli);
        	}
        	$mysqli->close();
        }
	}


	handleRequest();
?>