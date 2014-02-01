laraveldkim
===========

Sign all outgoing emails in Laravel with a DKIM signature.

TODO
----

* composer.json

How to Use
----------

Note that these instructions describe how to add the package manually, until I create the composer.json
settings. i.e. this is how I am using it now just to get the thing up and running quickly.

Steps are:

* Put code into application.
* Add an autoload entry.

Put code into application
-------------------------

Pull this code into your application. Eventually it will install in the vendor directory, but for now you can put it
into the app directory. I'll assume `laraveldkim` is in `app/academe` for now. You will then be able to find
`Mailer.php` in `app/academe/laraveldkim/src`

Add an autoload entry
---------------------

We will manually add an entry to composer.json to autoload this package. In your laravel `composer.json` file (in
the same directory as your `app` directory, add this entry:

	"autoload": {
		"psr-4": {
			"Academe\\LaravelDkim\\": "app/academe/laraveldkim/src/"
		}
	}

You will already have an "autoload" entry, probably a "psr-0" entry and maybe already a "psr-4" entry. You will
need to merge in the above psr-4 entry.

For the composer autoloader to know how to use this, run the following command (or its equivalent on your system):

    php composer.phar dump-autoload

