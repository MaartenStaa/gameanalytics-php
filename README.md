# GameAnalytics

PHP wrapper for the GameAnalytics REST API.

[![Build Status](https://travis-ci.org/MaartenStaa/gameanalytics-php.svg)][1]
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MaartenStaa/gameanalytics-php/badges/quality-score.png?b=master)][2]
[![Code Coverage](https://scrutinizer-ci.com/g/MaartenStaa/gameanalytics-php/badges/coverage.png?b=master)][3]

## Installation

Using [Composer](http://getcomposer.org/), add the package to your `require` section.

```json
{
	"require": {
		"maartenstaa/gameanalytics-php": "~2"
	}
}
```

This package requires you to select an HTTP client to be used. For more information,
[read the documentation][4].

## Usage

First, create a client using the game key and associated secret key. The third
parameter is the HTTP client you want to use. If you do not provide it, the package
will try to auto-detect which one is available. The fourth and final parameter is
the HTTP message factory you wish to use. Again, if you do not provide it, the package
will try to auto-detect which is available.

```php
use MaartenStaa\GameAnalytics\Client;

$client = new Client($gameKey, $secretKey);
```

Next, you can use the "init" and "event" functions on the client to get a message
instance. Use the set() function to configure the required parameters (refer to the
official documentation) and use send() to send the message. You will receive a
PSR-7 response object.

```php
$client->init()->set(array(...))->send();

$message = $client->event('user');
$message->set('foo', 'bar')
	->set('baz', 'bax')
	->send();
```

## Contributing

### Coding standard

All code is to follow the [PSR-2][5] coding standard.

### Unit tests

If you find a bug, feel free to send a pull request to fix it, but make sure to
always include a regression test.

[1]: https://travis-ci.org/MaartenStaa/gameanalytics-php
[2]: https://scrutinizer-ci.com/g/MaartenStaa/gameanalytics-php/?branch=master
[3]: https://scrutinizer-ci.com/g/MaartenStaa/gameanalytics-php/?branch=master
[4]: http://php-http.readthedocs.org/en/latest/
[5]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
