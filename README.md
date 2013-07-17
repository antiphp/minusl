AntiPhp MinusL (-l)
===================

MinusL checks the syntax of PHP, XML, JSON and INI files, the result will be cached. Whether a file has changed or not is determined by a hash function (by default PHP's md5_file) but you can use other functions (filemtime) or hash algorithms.

Syntax check for PHP files:
	``php -l``
	(that's where the name comes from)
	
Syntax check for XML files:
	``PHP's simplexml lib``
	
Syntax check for JSON files:
	``PHP's json_decode()``
	
Syntax check for INI files:
	``PHP's parse_ini_file()``
	

Composer.json
-------------

Extend your composer.json like this:

	{
		"require": {
			"php": ">=5.3.2",
			"antiphp/minusl": "dev-master"
		}
	}

	
Tests
-----

	// run an update of your composer setup
	php composer.phar update
	
	// short syntax
	php vendor/antiphp/minusl/bin/minusl.php run
	
	// - or -
	
	// extended syntax
	php vendor/antiphp/minusl/bin/minusl.php --hash=md5 --cache-file=/path/to/your/project/tmp/minusl.json --auto-save-interval=80 run /path/to/your/project

	
Output
------
	
	PHP MinusL (-l) Cached syntax checker by Christian Reinecke

	/home/www/minusl
	..E.............................................................................
	........................................................

	Error summary (1):
		[01] Errors parsing /home/www/minusl/latest/test.php

	General summary:
		files.................... 221
		files ignored............ 85
		files checked............ 136
		files *.json............. 20
		files *.php.............. 101
		files *.xml.............. 15
		files valid.............. 1
		files valid from cache... 134
		files invalid............ 1
		saved time............... 10.01s

	Fail.

	
Warning
-------

Please keep in mind: This is my first git/composer/packagist project and this
is just an alpha version. It is possible that there are a lot of changes coming soon.