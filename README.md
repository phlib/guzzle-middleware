# phlib/guzzle-middleware

[![Latest Stable Version](https://img.shields.io/packagist/v/phlib/guzzle-middleware.svg)](https://packagist.org/packages/phlib/guzzle-middleware)
[![Total Downloads](https://img.shields.io/packagist/dt/phlib/guzzle-middleware.svg)](https://packagist.org/packages/phlib/guzzle-middleware)
![Licence](https://img.shields.io/github/license/phlib/guzzle-middleware.svg?style=flat-square)

Guzzle Middleware

## Install

Via Composer

``` bash
$ composer require phlib/guzzle-middleware
```

## PHP Usage
``` php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Phlib\Guzzle\AbsoluteUrls;
use Phlib\Guzzle\ConvertCharset;

$handler = \GuzzleHttp\HandlerStack::create();
$handler->push(new ConvertCharset());
$handler->push(new AbsoluteUrls);

$client = new \GuzzleHttp\Client(['handler' => $handler]);
echo (string)$client->get('http://www.example.com')->getBody();

```
