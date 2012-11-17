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

Quick useage examples.

```php
$obj = \XMLRPC\XML_RPC::CallMethod("http://www.somexmlrpcserver.com", 'xmlrpc.method', array('param1', 'param2', 'etc...'));
```

As an example $obj would contain data similar to the following:

```php
SimpleXMLElement Object
(
    [params] => SimpleXMLElement Object
        (
            [param] => Array
                (
                    [0] => SimpleXMLElement Object
                        (
                            [value] => SimpleXMLElement Object
                                (
                                    [string] => foo
                                )
                        )

                    [1] => SimpleXMLElement Object
                        (
                            [value] => SimpleXMLElement Object
                                (
                                    [string] => bar
                                )
                        )

                    [2] => SimpleXMLElement Object
                        (
                            [value] => SimpleXMLElement Object
                                (
                                    [int] => 28
                                )
                        )
                )
        )
)
```

## Configure

There are default options you can configure like host, port and XML-RPC client useragent located in **config/default.php**.
