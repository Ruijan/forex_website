<?php
$action = null;
if(isset($_POST["action"])){
    $action = $_POST["action"];
}
if(isset($_GET["action"])){
    $action = $_GET["action"];
}
$requestHandler = new RequestHandler();
$requestHandler->setRequest($requestHandler->getRequestTypeFromString($action));
$requestHandler->execute();
