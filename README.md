laraveldkim
===========

Sign all outgoing emails in Laravel with a DKIM signature.

TODO
----

* composer.json
* tests

How to Use
----------

Note that these instructions describe how to add the package manually, until I create the composer.json
settings. i.e. this is how I am using it now just to get the thing up and running quickly.

Steps are:

* Put code into application.
* Add an autoload entry.
* Add your private key settings.

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

Add your private key settings
-----------------------------

Your signing key details need to be added to the laravel application config. Add the following to your
`mail.php` config file:


    'dkim' => array(
        'private_key' => <<<ENDDKIMKEY
    -----BEGIN RSA PRIVATE KEY-----
    ...your key goes in here...
    -----END RSA PRIVATE KEY-----
    ENDDKIMKEY
        ,
        'domain_name' => 'example.com',
        'selector' => 'dkim',
    ),


Note that everything between the two instances of `ENDDKIMKEY` must be right up to the start of the line.
You may be able to put the RSA key more easily into a dot-file (e.g. `.mail.prod.php`) for more security.

The domain_name is the domain that email will be sent from. The selector is the selector you chose to
store your public key against in your DNS. The public key in the above example will be stored in the
TEXT entry of `dkim._domainkey.example.com`
