=== PHPEnkoder ===
Contributors: michael_greenberg
Donate link: http://www.weaselhat.com/phpenkoder/
Tags: spam, bot, robot, crawler, harvest, mailto, email, e-mail, encrypt, javascript, js, enkoder, hide, crawl, link, encode, encoder, encoding, enkode, mail, spambot, human, address, addresses, safe, plaintext, hidden, obfuscate, obfuscator, hider, anti-spam, hivelogic, shortcode, anti-spam
Requires at least: 2.3
Tested up to: 6.2
Stable tag: 1.15.1
License: Modified BSD (BSDv3)
License URI: http://opensource.org/licenses/BSD-3-Clause

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
  * Download and extract `phpenkoder.1.12.1.zip` from the plugin
    directory and upload `enkoder.php` to the
    `/wordpress/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin through its menu in `Settings`

== Frequently Asked Questions ==

= Why doesn't PHP Enkoder work in the excerpt? =

WordPress creates excerpts by simply stripping tags from truncated
content. This results in some Javascript-protecting comments appearing
in the excerpt text, as there isn't a convenient way to determine if
content being rendered is meant for an excerpt or the page. For now, a
customizable message appears; by default, it will be rendered as /*
email hidden; JavaScript is required */. Any ideas for workarounds
would be appreciated; please send them along.

= I opened up the inspector and I saw my supposedly encoded text. What gives? =

The inspector shows the current live state of the DOM---how the page
looks right now. Once PHPEnkoder's generated JavaScript runs, then the
DOM will include all of the secrets. If you check the source, you'll
see that your secrets are safe from (naively) prying eyes.

= Other questions? =

See [the webpage](http://www.weaselhat.com/phpenkoder/) for more information.

== Screenshots ==

Not applicable!  PHPEnkoder is designed to make your website look the
same to humans: e-mail addresses don't appear in the HTML source, but
JavaScript converts the complicated encoding to valid HTML.

== Changelog ==

See [the webpage](http://www.weaselhat.com/phpenkoder/) for more
information on changes.

== Upgrade Notice ==

See [the webpage](http://www.weaselhat.com/phpenkoder/) for
information on updates.

= 1.15 =

Thanks to [zitrusblau](https://github.com/zitrusblau) for implementing
programmatic control of when PHPEnkoder runs.

= 1.14.1 =

Improved API for `enkode_mailto`, allowing overriding of some defaults. Thanks to Martina Beil for the patch!
