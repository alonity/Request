<?php

use alonity\request\Request;

ini_set('display_errors', true);
error_reporting(E_ALL);

require_once('../vendor/autoload.php');

$request = new Request();

// Change request uri
$request->setURI($_SERVER['REQUEST_URI']);

// Send post
$post = $request::post('https://google.com', ['param1' => 'example', 'param2' => 'test'], ['timeout' => 2]);

var_dump($post->send());

// Send get
$get = $request::get('https://google.com', ['param1' => 'example', 'param2' => 'test'], ['timeout' => 1]);

var_dump($get->send());

// Send multiple
$stack = $request::stack([$get, $post]);

foreach($stack->send() as $handler){
    var_dump($handler->getResponse());
}

?>