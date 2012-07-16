=== PHPEnkoder ===
Contributors: michael_greenberg
Donate link: http://www.weaselhat.com/phpenkoder/
Tags: spam, bot, robot, crawler, harvest, mailto, email, e-mail, encrypt, javascript, js, enkoder, hide, crawl, link, encode, encoder, encoding, enkode, mail, spambot, human, address, addresses, safe, plaintext, hidden, obfuscate, obfuscator, hider, anti-spam, hivelogic, shortcode, anti-spam
Requires at least: 2.3
Tested up to: 3.4.1
Stable tag: 1.11

Encodes mailto: links and e-mail addresses with JavaScript to stifle
webcrawlers.  Automatically turns plaintext e-mails into (enkoded)
links.

== Description ==

PHPEnkoder is a port of the excellent [Hivelogic
Enkoder](http://hivelogic.com/enkoder) to PHP and, more specifically,
to Wordpress. It is used to display text in a way that users can see
and bots can't.

The encoding system is directly and unabashedly stolen from the
BSD-licensed source of Hivelogic Enkoder, which works by randomly
encoding a piece of text and sending to the browser self-evaluating
Javascript that will generate the original text. This works in two
ways: first, a bot must first have a fairly complete Javascript
implementation (in particular, it must have `eval`); second, the
decoding process can be made arbitrarily computationally
intensive. This is similar to the idea of charging computational
payments to send e-mail, only this is actually implemented.

By default, PHPEnkoder scrambles e-mails in plaintext and in `mailto:`
links.  It additionally provides a shortcode for manual scrambling,
used like so: `[enkode text="shown to non-JS browsers"]this will be
scrambled[/enkode]`.

== Installation ==

1. Either: 
  * Go to 'Plugins > Add New' and search for PHPEnkoder
  * Download and extract `phpenkoder.1.10.zip` from the plugin
    directory and upload `enkoder.php` to the
    `/wordpress/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin through its menu in `Settings`

== Frequently Asked Questions ==

See [the webpage](http://www.weaselhat.com/phpenkoder/) for more information.

== Screenshots ==

Not applicable!  PHPEnkoder is designed to make your website look the
same to humans: e-mail addresses don't appear in the HTML source, but
JavaScript converts the complicated encoding to valid HTML.
