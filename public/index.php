<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

// Use APC for autoloading to improve performance
// Change 'sf2' by the prefix you want in order to prevent key conflict with another application
/*
$loader = new ApcClassLoader('sf2', $loader);
$loader->register(true);
*/

require_once __DIR__.'/../src/AppKernel.php';
//require_once __DIR__.'/../src/AppCache.php';

$kernel = new AppKernel('prod', false);

//$kernel = new AppCache($kernel);
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    Request::setTrustedProxies(
        ['127.0.0.1', 'REMOTE_ADDR'],
        Request::HEADER_X_FORWARDED_AWS_ELB
    );
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
