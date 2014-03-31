<?php
require 'classes/Savemanga.php';
require 'classes/Savemanga_Factory.php';
require 'classes/Savemanga_Mangareader.php';
require 'classes/Savemanga_Mangapanda.php';

$urls = [
        "http://www.mangapanda.com/93/naruto.html"
        // ,"http://www.mangareader.net/93/naruto.html"
        // ,"http://www.mangapanda.com/103/one-piece.html"
        ];
foreach ($urls as $url) {
    $url = trim($url);
    if (strlen($url)) {
        $object       = Savemanga_Factory::getInstanceOf($url);
        $object->path = __DIR__."/mangas/";
        if (!is_dir($object->path)) {
          mkdir($object->path, 0777);
        }
        $object->getManga($url);
    }else{
      echo "Empty URL";
    }
}
?>