<?php

use alonity\request\Request;

ini_set('display_errors', true);
error_reporting(E_ALL);

require_once('../vendor/autoload.php');

$request = new Request();

// Change request uri
$request->setURI($_SERVER['REQUEST_URI']);

// Send post
$post = $request->post('https://google.com', ['param1' => 'example', 'param2' => 'test'], ['timeout' => 2]);

var_dump($post);

// Send get
$post = $request->get('https://google.com', ['param1' => 'example', 'param2' => 'test'], ['timeout' => 1]);

var_dump($post);

// Send multiple
$post = $request->getStack([
    'https://google.com',
    'https://youtube.com'
], [
    ['param1' => 'google', 'param2' => 'test'],
    ['param1' => 'youtube', 'param2' => 'test']
], [
    ['timeout' => 1],
    ['timeout' => 5]
]);

var_dump($post);

?>