<?php
	echo phpinfo();
	include dirname(__FILE__)."/event.php";
	$event = new Event();
	$dv_p_tm5 = 0;
	$dv_p_t0 = 0;
	if(isset($_GET["dv_p_tm5"])){
		$dv_p_tm5 = $_GET["dv_p_tm5"];
	}
	if(isset($_GET["dv_p_t0"])){
		$dv_p_t0 = $_GET["dv_p_t0"];
	}
	if(isset($_GET["actual"])){
		$event->actual = $_GET["actual"];
	}
	if(isset($_GET["previous"])){
		$event->previous = $_GET["previous"];
	}
	if(isset($_GET["forecast"])){
		$event->forecast = $_GET["forecast"];
	}
	if(isset($_GET["id"])){
		$event->id = $_GET["id"];
	}
	$event->getPrediction($dv_p_tm5, $dv_p_t0);
	echo $event->class;

?>