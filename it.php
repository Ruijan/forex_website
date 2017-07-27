<?php
	$tests = ["create_event", "update_market", "predict", "open_trade", "close_trade"];
	$myvars = [	"action=create_event", "action=update_market&dv_p_tm5=0.00007&dv_p_t0=0.00070", 
				"action=update_market&prediction=0", "action=open_trade" , "action=close_trade"];
	$url = 'http://127.0.0.1/Pixelnos/forex/handle_requests.php';
	for ($i = 0; $i < sizeof($tests); $i++) {
		if($tests[$i] == "open_trade" or $tests[$i] == "close_trade" or $tests[$i] == "predict"){
			$myvars[$i] = $myvars[$i]."&id=139";
		}
		echo $url."?".$myvars[$i]."<br/>";
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars[$i]);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec( $ch );
		echo $response."<br/>=================================================================<br/>";
	}
	

?>