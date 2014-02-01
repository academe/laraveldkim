laraveldkim
===========

Sign all outgoing emails in Laravel with a DKIM signature.

TODO
----

* composer.json and packagist (academe/laraveldkim)
* tests
* Links to some articles with more details about DKIM

Why would you use this?
-----------------------

Most applications need to send out mail. To look as little like SPAM as possible, there are a number of
techniques that should be applied to sent mail. One is called Domain Keys Identified Mail (DKIM).

DKIM works using a public/private key pair. The public key is stored in a TEXT record of the domain sending
the email, so all recipients (mail servers) can see that. The private key is kept secret and used to 
sign the outgoing message. The signature is a hash of the message content and some headers, and adds the hash to the 
top of the headers. Only someone with the private key can sign it in a way that can be validated by the
public key. Google, Yahoo, Hotmail all do this check. Any tampering of the message en-route will also break
the signature and so can be rejected at the destination.

Now, many applications will have DKIM handled for them by the operating system or the mail sending service
(e.g. Sendgrid, or their ISP's SMTP server, or set up using Control Panel). In some instances this is not
an option, and so this package was created to do the signing at the application level.

How to Use
----------

Note that these instructions describe how to add the package manually, until I create the composer.json
settings. i.e. this is how I am using it now just to get the thing up and running quickly.

Steps are:

* Put code into application.
* Add an autoload entry.
* Add your private key settings.
* Use the new mailer.

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

Use the new mailer
------------------

Now we need to tell Laravel to use the custom mailer rather than the standard built-in mailer. This is done
in the app.php config file.

The `providers` array will have this entry:

    'Illuminate\Mail\MailServiceProvider',

Comment that out and replace it with this entry:

    'Academe\LaravelDkim\MailServiceProvider',

That's it. Your emails should now be signed with DKIM. You can check the headers in the source of emails that
your application sends out and you should see the DKIM signature in there. It will look something like this:

    DKIM-Signature: v=1; a=rsa-sha1; bh=Gwuoen3CG+KClMvlMKjUh1ZJmzg=;
    d=example.com; h=Message-ID: Date: From: MIME-Version: Content-Type;
    i=@example.com; s=dkim; t=1391259163;
    b=cIkL/FZ6/v/XUdcYvhvmSo9abedf0DLlM/LYkOX4GoW4EUzPxN10hOHQpWlqjeDa2YdsI7GH
    dGCc16Xgb2kpZbPEom0RMv62G4SYf8763abb7380ebMRP2tv0/Mq+CaOmQejk34vlBnzcj0JE
    6PGOPxEEe9dgdoOMx4uEhhlkd=
