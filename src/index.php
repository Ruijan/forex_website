<?php

require_once('RequestHandlerBuilder.php');
require_once('../connect.php');

$action = null;
if(isset($_POST["action"])){
    $action = $_POST["action"];
}
if(isset($_GET["action"])){
    $action = $_GET["action"];
}
$parameters = array_merge($_POST,$_GET);
$mysqli = connect_database();
$requestHandlerBuilder = new RequestHandlerBuilder();
$requestHandler = $requestHandlerBuilder->makeRequestHandlerWithRequest($action, $parameters, $mysqli);
$requestHandler->execute();
