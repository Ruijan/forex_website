<?php
/**
 * Created by PhpStorm.
 * User: MSI-GP60
 * Date: 7/17/2016
 * Time: 6:35 PM
 */
function connect_database(){
    $mysqli = mysqli_connect("127.0.0.1", "root", "", "forex");
    //$mysqli = mysqli_connect("pixelnos.com", "pixelnos_forex", "18061990", "pixelnos_forex");
    //$mysqli = mysqli_connect("37.187.143.44", "pixelnos_admin", "Potter321!", "pixelnos_mvd");
    if (!$mysqli) {
        echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
        throw new Exception("Error: Unable to connect to MySQL." . PHP_EOL);
    }
    return $mysqli;
}