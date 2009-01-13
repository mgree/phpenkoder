=== PHPEnkoder ===
Contributors: michael_greenberg
Donate link: http://www.weaselhat.com/phpenkoder/
Tags: spam, mailto, email, e-mail, encrypt, javascript, enkoder
Requires at least: 2.0
Tested up to: 2.6.2
Stable tag: 1.2

Encodes mailto: links and e-mail addresses with JavaScript to stifle
webcrawlers.

== Description ==

PHPEnkoder is a port of the excellent Hivelogic Enkoder to PHP and,
more specifically, to Wordpress. It is used to display text in a way
that users can see and bots can't.

The encoding system is directly and unabashedly stolen from the
BSD-licensed source of Hivelogic Enkoder, which works by randomly
encoding a piece of text and sending to the browser self-evaluating
Javascript that will generate the original text. This works in two
ways: first, a bot must first have a fairly complete Javascript
implementation; second, the decoding process can be made arbitrarily
computationally intensive. This is similar to the idea of charging
computational payments to send e-mail, only this is actually
implemented.

== Installation ==

1. Upload `enkoder.php` to the `/wordpress//wp-content/plugins/` directory
Or:
1. Extract `enkoder-major.minor.{zip,tgz}` in the `/wordpress/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin through its menu in `Settings`

== Frequently Asked Questions ==

See [the webpage](http://www.weaselhat.com/phpenkoder/) for more information.

== Screenshots ==

Not applicable!  PHPEnkoder is designed to make your website look the
same to humans: e-mail addresses don't appear in the HTML source, but
JavaScript converts the complicated encoding to valid HTML.
