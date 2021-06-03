# Request
External alonity request

## Install

`composer require alonity/request`

### Examples
```php
use alonity\request\Request;

$request = new Request();

// Change request uri
$request->setURI($_SERVER['REQUEST_URI']);

// Send post
$post = $request->post('https://google.com', ['param1' => 'example', 'param2' => 'test'], ['timeout' => 2]);

// Send get
$post = $request->get('https://google.com', ['param1' => 'example', 'param2' => 'test'], ['timeout' => 1]);

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
```