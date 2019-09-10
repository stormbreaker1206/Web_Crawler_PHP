<?php
include ("config.php");
include ("classes/domDocumentPaser.php");
/**
 * Created by PhpStorm.
 * User: mayon
 * Date: 25/05/2019
 * Time: 12:43 AM
 *
 *
 */
$alreadyCrawled = array();
$crawling = array();
$alreadyFoundImages = array();


function linkExist($url){

    global $con;
    $query = $con->prepare("select * from sites where url = :url");
    $query->bindParam(":url", $url);
    $query->execute();
    return $query->rowCount() !=0;

}

function imageExist($url){

    global $con;
    $query = $con->prepare("select * from images where src = :url");
    $query->bindParam(":url", $url);
    $query->execute();
    return $query->rowCount() !=0;

}

function insertLinks($url, $tile, $description, $keywords){

    global $con;
    $query = $con->prepare("insert into sites(url, title, description, keywords) values (:url, :title, :description, :keywords)");
    $query->bindParam(":url", $url);
    $query->bindParam(":description", $description);
    $query->bindParam(":title", $tile);
    $query->bindParam(":keywords", $keywords);
    return $query->execute();

}

function insertImage($url, $src, $alt, $title){

    global $con;
    $query = $con->prepare("insert into images(url, src, alt, title) values (:url, :src, :alt, :title)");
    $query->bindParam(":url", $url);
    $query->bindParam(":src", $src);
    $query->bindParam(":alt", $alt);
    $query->bindParam(":title", $title);
    return $query->execute();

}
function createLinks ($src, $url){
    $scheme = parse_url($url)["scheme"]; //https
    $host = parse_url($url)["host"]; //www.asknemes.com

    if(substr($src, 0,2)=="//"){
        $src = $scheme.":".$src;
    }elseif (substr($src, 0,1)=="/"){
        $src = $scheme."://".$host.$src;
    }elseif (substr($src, 0,2)== "./"){
        $src = $scheme."://".$host.dirname(parse_url($url)["path"]).substr($src, 1);

    }
    elseif (substr($src, 0,3)== "../"){
        $src = $scheme."://".$host."/".$src;

    }
    elseif (substr($src, 0,5)!= "https" && substr($src, 0,4)!= "http" ){
        $src = $scheme."://".$host."/".$src;

    }

    return $src;

}

function getDetails($url){
    global $alreadyFoundImages;

    $paser = new domDocumentPaser($url);
    $tileList = $paser->getTitle();
    if(sizeof($tileList) == 0 || $tileList->item(0) == NULL){
        return;
    }
    $title = $tileList->item(0)->nodeValue;
    $title = str_replace("\n","",$title);
    if($title == ""){
        return;
    }

    $description = "";
    $keywords = "";

    $metasArray = $paser->getMetatags();

    foreach($metasArray as $meta) {

        if($meta->getAttribute("name") == "description") {
            $description = $meta->getAttribute("content");
        }

        if($meta->getAttribute("name") == "keywords") {
            $keywords = $meta->getAttribute("content");
        }
    }

    $description = str_replace("\n", "", $description);
    $keywords = str_replace("\n", "", $keywords);

    if(linkExist($url)){

    }elseif (insertLinks($url, $title, $description, $keywords)){

    }else{

    }

    $imageArray = $paser->getMetaImages();
    foreach($imageArray as $image) {
        $src = $image->getAttribute("src");
        $alt = $image->getAttribute("alt");
        $title = $image->getAttribute("title");

        if(!$alt && !$title){
            continue;
        }

        $src = createLinks($src, $url);

        if(!in_array($src, $alreadyFoundImages)){
            $alreadyFoundImages[] = $src;
            //insert images

            if(imageExist($src)){

            }elseif (insertImage($url, $src, $alt, $title)){

            }else{

            }


        }



    }

       }
function followLinks($url){
    global $alreadyCrawled;
    global $crawling;

    $paser = new domDocumentPaser($url);
    $linkList = $paser->getLinks();
    foreach ($linkList as $link){
        $href = $link->getAttribute("href");

        if(strpos($href,"#")!== false){
            continue;
        }elseif (substr($href,0,11)=="javascript:"){
            continue;
        }
        $href = createLinks($href, $url);

        if(!in_array($href, $alreadyCrawled)) {
            $alreadyCrawled[] = $href;
            $crawling[] = $href;

            getDetails($href);
        }



    }

    array_shift($crawling);

    foreach($crawling as $site) {
        followLinks($site);
    }

}

$startUrl = "https://twitter.com/Newsday_TT?lang=en";
followLinks($startUrl);