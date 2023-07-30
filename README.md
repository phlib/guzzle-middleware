# phlib/guzzle-middleware

[![Code Checks](https://img.shields.io/github/actions/workflow/status/phlib/guzzle-middleware/code-checks.yml?logo=github)](https://github.com/phlib/guzzle-middleware/actions/workflows/code-checks.yml)
[![Codecov](https://img.shields.io/codecov/c/github/phlib/guzzle-middleware.svg?logo=codecov)](https://codecov.io/gh/phlib/guzzle-middleware)
[![Latest Stable Version](https://img.shields.io/packagist/v/phlib/guzzle-middleware.svg?logo=packagist)](https://packagist.org/packages/phlib/guzzle-middleware)
[![Total Downloads](https://img.shields.io/packagist/dt/phlib/guzzle-middleware.svg?logo=packagist)](https://packagist.org/packages/phlib/guzzle-middleware)
![Licence](https://img.shields.io/github/license/phlib/guzzle-middleware.svg)

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

## License

This package is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
