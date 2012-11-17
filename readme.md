# XML-RPC Bundle

## Installation

Place the files within your laravel/bundles folder.

## Bundle Registration

Add the following to your **application/bundles.php** file:

```php
'xmlrpc' => array(
	'autoloads' => array(
		'map' => array(
			'XMLRPC\\XML_RPC' => '(:bundle)/xmlrpc.php',
		),
	),
),
```

## Guide



## Configure

There are default options you can configure like host, port and XML-RPC client useragent located in **config/default.php**.
