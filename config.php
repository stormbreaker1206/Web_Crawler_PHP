<?php
/**
 * Created by PhpStorm.
 * User: mayon
 * Date: 25/05/2019
 * Time: 10:35 AM
 */

ob_start();
try{

    $con = new PDO("mysql:dbname=doodle;host=localhost", "root","");
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);


}catch (PDOException $e){

    echo "Connection Failed" . $e->getMessage();

}