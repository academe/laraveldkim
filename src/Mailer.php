<?php namespace Academe\LaravelDkim;

use Illuminate\Mail\Message;
use Illuminate\Mail\Mailer as CoreMailer;
use Swift_SignedMessage;
use Swift_Signers_DKIMSigner;
use Config;

class Mailer extends CoreMailer {
	/**
	 * Create a new message instance.
	 *
	 * @return \Illuminate\Mail\Message
	 */
	protected function createMessage()
	{
		// Get the DKIM selector.
		$selector = Config::get('mail.dkim.selector');

		// If we have a DKIM selector, then add the signing.
		if (!empty($selector)) {
			// Use the signed message with support for signing.
			// So long as there is a selector we will do this, even if a private
			// key is not set. This is handy if the application wants to add its
			// own signatures later.
			$message = new Message(new Swift_SignedMessage);

			// Get the key and domain name.
			// Ideally these would be specific to the selector, with the selector being
			// chosen according to the domain of the sending address. At this stage we do
			// not know what the final sending addressis going to be.
			$private_key = Config::get('mail.dkim.private_key');
			$domain_name = Config::get('mail.dkim.domain_name');

			if (!empty($private_key) && !empty($domain_name)) {
				// Do the DKIM signing.
				$dkim_signer = new Swift_Signers_DKIMSigner($private_key, $domain_name, $selector);
				
				// Issue #1: ignore certain headers that cause end-to-end failure.
				$dkim_signer->ignoreHeader('Return-Path');
				$dkim_signer->ignoreHeader('Bcc');
				$dkim_signer->ignoreHeader('DKIM-Signature');
				$dkim_signer->ignoreHeader('Received');
				$dkim_signer->ignoreHeader('Comments');
				$dkim_signer->ignoreHeader('Keywords');
				$dkim_signer->ignoreHeader('Resent-Bcc');
				
				$message->attachSigner($dkim_signer);
			}
		} else {
			// Non-signed message.
			$message = new Message(new Swift_Message);
		}

		// If a global from address has been specified we will set it on every message
		// instances so the developer does not have to repeat themselves every time
		// they create a new message. We will just go ahead and push the address.
		if (isset($this->from['address']))
		{
			$message->from($this->from['address'], $this->from['name']);
		}

		return $message;
	}
}
