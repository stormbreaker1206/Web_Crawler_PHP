<?php
/**
 * Created by PhpStorm.
 * User: mayon
 * Date: 25/05/2019
 * Time: 12:49 AM
 */

class domDocumentPaser{
    private $doc;
     public function __construct($url){
         $options = array('https'=>array('method'=>"GET", 'header'=> "User-Agent: doddleBot/0.1\n"));
         $context = stream_context_create($options);
         $this->doc = new DOMDocument();
         @$this->doc->loadHTML(file_get_contents($url, false, $context));

     }

     public function getLinks(){
         return $this->doc->getElementsByTagName("a");
     }
    public function getTitle(){
        return $this->doc->getElementsByTagName("title");
    }

    public function getMetaTags() {
        return $this->doc->getElementsByTagName("meta");
    }
    public function getMetaImages() {
        return $this->doc->getElementsByTagName("img");
    }


}