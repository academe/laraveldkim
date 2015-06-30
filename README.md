laraveldkim
===========

Sign all outgoing emails in Laravel 4 with a DKIM signature.

This package does not work on Laravel 5, and it is
unlikely I will find the timne or have the need to take it forward to Laravel 5. If you have any fixes though,
I will be happy to accept pull-requests. Laravel 5 still uses SwiftMailer for its mail transport, so it should
be possible to fix it.

However, if you are able to sent mail through another server that adds DKIM for you, then that is going to
be the simplest solution by far.

TODO
----

* tests

Why would you use this?
-----------------------

Most applications need to send out mail. To look as little like SPAM as possible, there are a number of
techniques that should be applied to sent mail. One is called 
[DomainKeys Identified Mail](http://www.dkim.org/) (DKIM).

DKIM works using a public/private key pair. The public key is stored in a TEXT record of the domain sending
the email, so all recipients (mail servers) can see that. The private key is kept secret and used to 
sign the outgoing message. The signature is a hash of the message content and some headers, and adds the hash to the 
top of the headers. Only someone with the private key can sign it in a way that can be validated by the
public key. Google, Yahoo, Hotmail all do this check. Any tampering of the message en-route will also break
the signature and so can be rejected at the destination.

Now, many applications will have DKIM handled for them by the operating system or the mail sending service
(e.g. Sendgrid, or their ISP's SMTP server, or set up using Control Panel). In some instances this is not
an option, and so this package was created to do the signing at the application level.

Laravel uses [Swift Mailer](http://swiftmailer.org/) to handle its outgoing email, and Swift Mailer has built-in
support for DKIM signing. This package injects the signing component each time a Swift message is instantiated.

There is a great description of DKIM on [Coding Horror](http://www.codinghorror.com/blog/2010/04/so-youd-like-to-send-some-email-through-code.html)

Limitations
-----------

This package was written to fill a specific need, so may not be as flexible as you want. Do feel free to
submit pull requests if you have any improvements.

The package makes the assumption that you are using the built-in Laravel email provider. If you are using
other email packages such as [laravel-mailgun](https://github.com/killswitch/laravel-mailgun) then I 
don't know it this will work (I don't know if other mail packages sit on top of Laravel's mail provider
or replace it). Try it and let me know how it goes.

It also assumes that all emails will be coming from a single domain. I did try giving Swift Mailer multiple
certificates against multiple domains to see if it would choose the one that matched the sending address,
but it just added all the certificates without question. Maybe there is scope to update SwiftMailer so that
it does not choose and use the signing components until much later in its processing, when it has more
details of what it is sending and to whom, so it can make better decisions on the settings to use when
signing. For example, until it knows the sending domain, it really does not know which of a potential
list of certificates to use for the signing.
So, just the one domain and certificate for now, and we will see how that goes out in the wild.

All emails will be signed with the same certificate.

Version 1.0.4 introduces a workaround for an issue in the SwiftMailer DKIM signer. Some email headers
should *never* be included in the signature. SwiftMailer does not exclude those headers automatically,
so they are now set to "ignore" explicitely in this package. You can check whether this has been
fixed in SwiftMailer here: https://github.com/swiftmailer/swiftmailer/issues/442

How to Use
----------

Steps are:

* Include the package in your application.
* Add your private key settings.
* Use the new mailer.

Include the package in your application
---------------------------------------

Add a requirement to your project's composer.json

    "require": {
        "academe/laraveldkim": "1.*"
    },

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
See [Laravel: Protecting Sensitive Configuration](http://laravel.com/docs/configuration#protecting-sensitive-configuration) 
for more details on dot file support.
Another approach may be to read the key from a text file in the config file and return that.
The "mail.dkim.private_key" needs to be a string containing the full private key, with each line
terminated with a newline character. It may be safer, if stored as a PHP string, to concatenate each
line of the key in double-quotes with explicit embedded newlines, like this:
`"-----BEGIN RSA PRIVATE KEY-----\n" . "line 1\n" . "line 2\n" etc.`. Assuming the end-of-line 
terminators in a source file are \n never was a great idea, though [PSR-2 does make it clear](http://www.php-fig.org/psr/psr-2/) (section 2.2) that lines MUST be terminated with a \n.


The domain_name is the domain that email will be sent from. The selector is the selector you chose to
store your public key against in your DNS. The public key in the above example will be stored in the
TEXT entry of `dkim._domainkey.example.com`

Use the new mailer
------------------

Now we need to tell Laravel to use the custom mailer rather than the standard built-in mailer. This is done
in the app.php config file. Please note that the "new mailer" is just a wrapper for the default Laravel
mailer, and is not a complete functional replacement, so you won't have to modify the use of the mailer
in your application.

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
