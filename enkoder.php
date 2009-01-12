<?php
/*
Plugin Name: PHPEnkoder
Plugin URI: http://www.weaselhat.com/phpenkoder/
Description: An anti-spam text scrambler based on the <a href="http://hivelogic.com/enkoder">Hivelogic Enkoder</a> Ruby on Rails TextHelper module.  Hat tip: Dan Benjamin for the original Ruby code, Yaniv Zimet for pure grit.
Author: Michael Greenberg
Version: 1.2
Author URI: http://www.weaselhat.com/
*/

/* LICENSE (New BSD)
Copyright (c) 2006, Michael Greenberg.  Derivative work of the Hivelogic Enkoder, Copyright (c) 2006, Automatic Corp.
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.

	3. Neither the name of Michael Greenberg, AUTOMATIC CORP. nor the names of
        its contributors may be used to endorse or promote products derived from this
        software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

/* 
Hello.  You probably don't want to read a lot of PHP.  I never really liked dollar signs, anyway.

By default, the plugin will filter all of your content and RSS.  If you want to turn this  off, you can still use the enkode() function to manually encode text.  Just pass it the text you want to encode and it will return what you should write instead.  As a shortcut, there's also the function enkode_mailto which takes an e-mail address and some link text (and optionally a subject line and link title); it will return the encoded mailto link with the given parameters.

To those of you who indeed love dollar signs and archaic syntax, enjoy.  Functions prefixed with enkode_ are for public use; those with enkoder_ are for plugin setup; and those with enk_ are intended to be private.
*/

/* WORDPRESS LOGIC *****************/
/*
Sets up the wordpress filters and config pages.  

There are two kinds of filters: one for plaintext e-mails and another for explicit e-mail links.  Naturally, the latter must be run first (priority 11 by default), or the e-mail detector will corrupt our links.
*/

add_option('enkode_pt',  1, 'Enkode plaintext e-mail addresses.');
add_option('enkode_mt',  1, 'Enkode mailto: e-mail addresses.');
add_option('enkode_rss', 1, '0 => don\'t enkode in RSS, 1 => hide in RSS, 2 => enkode in RSS.');
add_option('enkode_msg', "email hidden; JavaScript is required", "Message to display to non-JavaScript-capable browers (and on RSS).");

/* config stuff unabashedly stolen from akismet */
add_action('admin_menu', 'enkoder_config_page');

function enkoder_config_page() {
        if (function_exists('add_options_page')) {
                add_options_page(__('PHPEnkoder'),
                                 __('PHPEnkoder'), 1, 
				 basename(__FILE__),
                                 'enkoder_conf');
	}
}

function enkoder_conf() {
        if ( isset($_POST['submit']) ) {
                check_admin_referer();
		update_option('enkode_pt',  $_POST['enk_pt'] == 'on');
		update_option('enkode_mt',  $_POST['enk_mt'] == 'on');
		update_option('enkode_rss', intval($_POST['enk_rss']));
		update_option('enkode_msg', $_POST['enk_msg']); /* magic quotes better be on... */
	}
?>
<div class="wrap">
<h2><?php _e('PHPEnkoder Configuration'); ?></h2>
	<p><?php _e('PHPEnkoder should put a stop to e-mail crawling.  But if you like spam, feel free to disable some of its protection.  Perhaps you only want to manually enkode a few things?'); ?></p>

<form action="" method="post" id="phpenkoder-conf" style="margin: auto; width: 25em; ">
<fieldset>
<legend>Enkoding options</legend>
<p><input id="enk_pt"  name="enk_pt"  type="checkbox" <?php checked(1, get_option('enkode_pt')); ?> />&nbsp;<label for="enk_pt">Enkode plaintext e-mails</label></p>
<p><input id="enk_mt"  name="enk_mt"  type="checkbox" <?php checked(1, get_option('enkode_mt')); ?> />&nbsp;<label for="enk_mt">Enkode mailto: links</label></p>
</fieldset>
<fieldset>
<legend>RSS options</legend>
<p>Given the above enkoding options, what should be done to RSS?  The default is to hide e-mails, as not all RSS readers have Javascript support. <?php get_option('enkode_rss'); ?></p>
<input id="enk_rss2" name="enk_rss" value="2" type="radio" <?php checked(2, intval(get_option('enkode_rss'))); ?>>&nbsp;<label for="enk_rss2">Enkode e-mails in RSS (JavaScript may not be supported in all readers)</label><br />
<input id="enk_rss1" name="enk_rss" value="1" type="radio" <?php checked(1, intval(get_option('enkode_rss'))); ?>>&nbsp;<label for="enk_rss1">Hide e-mails in RSS (using the message below)</label><br />
<input id="enk_rss0" name="enk_rss" value="0" type="radio" <?php checked(0, intval(get_option('enkode_rss'))); ?>>&nbsp;<label for="enk_rss0">Do not enkode in RSS (make spammers happy!)</label><br />
</fieldset>
<p><label for="enk_msg">Message for non-JavaScript-capable browsers</label><input id="enk_msg" name="enk_msg" type="text" size="60" value="<?php echo get_option('enkode_msg'); ?>" /></p>
<p class="submit"><input type="submit" name="submit" value="<?php _e('Update Options &raquo;'); ?>" /></p>
</form>
</div>
<?php

}

$enkoder_mailto_priority = 31;
$enkoder_plaintext_priority = 32;

define("EMAIL_REGEX", '[\w\d+_.-]+@(?:[\w\d_-]+\.)+[\w]{2,6}');
define("PTEXT_EMAIL", '/(?<=[^\w\d\+_.:-])(' . EMAIL_REGEX . ')/i'); /* note the banned first char */
define("MAILTO_EMAIL", '#(<a.*?href="mailto:' . EMAIL_REGEX . '.*?>.*?</a>)#i');
define("LINK_TEXT", "/>(.*?)</");

function enk_extract_linktext($text) {
	preg_match(LINK_TEXT, $text, $tmatches);
	return $tmatches[1];
}

function enk_email_to_link($matches) {
	return enkode_mailto($matches[1], $matches[1]);
}

function enk_hide_link($matches) {
	$text = enk_extract_linktext($matches[1]);
	return enk_noscript(enkode($matches[1]), $text);
}

function enk_mailto_to_linktext($matches) {
	return enk_extract_linktext($matches[1]);
}

/* enkode_plaintext_emails($text)

Encodes all plaintext e-mails into a JavaScript-obscured mailto; the text of the mailto is the e-mail address itself.
*/
function enkode_plaintext_emails($text) {
	return preg_replace_callback(PTEXT_EMAIL, 'enk_email_to_link', $text);
}

/* enkode_mailtos($text)

Encodes all mailto links into JavaScript obscured text.
*/
function enkode_mailtos($text) {
	return preg_replace_callback(MAILTO_EMAIL, 'enk_hide_link', $text);
}

/* used for RSS */
function enk_hide_emails($text) {
	$text = preg_replace_callback(MAILTO_EMAIL, 'enk_mailto_to_linktext', $text);

	/* make sure there's no e-mail in the link! */
	return preg_replace(PTEXT_EMAIL, '(' . get_option("enkode_msg") . ')', $text);
}

function enkoder_register_filters($hook, $filters = array('enkode_plaintext_emails', 'enkode_mailtos')) {
	global $enkoder_mailto_priority, $enkoder_plaintext_priority;

	if (get_option('enkode_mt'))
		add_filter($hook, $filters[1], $enkoder_mailto_priority);

	if (get_option('enkode_pt'))
		add_filter($hook, $filters[0], $enkoder_plaintext_priority);
}

function enkoder_register_single($hook, $filter = 'enk_hide_emails') {
	global $enkoder_plaintext_priority;

	if (get_option('enkode_pt') || get_option('enkode_mt'))
		add_filter($hook, $filter, $enkoder_plaintext_priority);
}

/* actually set up the filters */
function enkoder_setup_filters() {
	/* set up standard content filters */
	$content_hook = array('the_content', 'comment_text');
	foreach ($content_hook as $hook) {
		enkoder_register_filters($hook);
	}

	/* set up RSS filters */
	$rss_hook = array('the_content_rss', 'comment_rss', 'the_excerpt_rss');
	$conf_enk_rss = intval(get_option('enkode_rss'));
	if      ($conf_enk_rss == 2) $reg_rss = 'enkoder_register_filters';
	else if ($conf_enk_rss == 1) $reg_rss = 'enkoder_register_single';

	if (isset($reg_rss)) {
		foreach ($rss_hook as $hook) {
			$reg_rss($hook);
		}
	}
}

enkoder_setup_filters();

/* ENCODING ************************/

/* enkode_mailto($email, $text, $subject = "", $title = "")

Encodes a mailto link.

*/
function enkode_mailto($email, $text, $subject = "", $title = "") {
	$content = '<a href="mailto:' . $email;

	if ($subject) $content .= "?subject=$subject";

	$content .= '"';

	if ($title) $content .= " title=\"$title\"";

	$content .= ">$text</a>";

	return enk_noscript(enkode($content), $text);
}

function enk_noscript($enkoded, $text) {
	/* we don't want to print an e-mail plaintext! */
	if (strstr($text, "@")) {
		$text = "";
	} else {
		$text .= ' ';
	}

	$noscript = "<noscript>$text(" . get_option('enkode_msg') . ")</noscript>";

	return $enkoded . $noscript;
}

/* enkode($content, $max_passes = 20, $max_length = 1024)

Encodes a string to be view-time written by obfuscated Javascript.  The max passes parameter is a tight bound on the number of encodings perormed.  The max length paramater is a loose bound on the length of the generated Javascript.  Setting it to 0 will use a single randomly chosen pass of encoding.

The function works by selecting encodings at random from the array enkodings, applying them to the given string, and then producing Javascript to decode.  The Javascript works by recursive evaluation, which should be nasty enough to stop anything but the most determined spambots.

*/
function enkode($content, $max_passes = 20, $max_length = 1024) {
	global $enkodings, $enk_dec_num;

	/* our base case -- we'll eventually evaluate this code */
	$kode = "document.write(\"" . 
		addslashes($content) .
                "\");";

	$max_length = max($max_length, strlen($kode) + JS_LEN + 1);

	$result = "";

	/* build up as many encodings as we can */
	for ($passes = 0;
	     $passes < $max_passes && strlen($kode) < $max_length;
             $passes++) {
		/* pick an encoding at random */
		$idx = rand(0, count($enkodings) - 1);
		$enc = $enkodings[$idx][0];
		$dec = $enkodings[$idx][1];

		$kode = enkode_pass($kode, $enc, $dec);
	}

	/* mandatory numerical encoding, prevents catching @ signs and interpreting neighboring characters as e-mail addresses */
	$kode = enkode_pass($kode, 'enk_enc_num', $enk_dec_num);

	return enk_build_js($kode);
}

/* enkode_pass($kode, $enc, $dec)

Encodes a single pass.  $enc is a function pointer and $dec is the Javascript.
*/
function enkode_pass($kode, $enc, $dec) {
	/* first encode */
	$kode = addslashes($enc($kode));

	/* then generate encoded code with decoding afterwards */
	$kode = "kode=\"$kode\";$dec;"; 

	return $kode;
}

/* enk_build_js($kode)

Generates the Javascript recursive evaluator, which is 157 characters of boilerplate code.

*/
define('JS_LEN', 169);
function enk_build_js($kode) {
	$clean = addslashes($kode);

	$msg = get_option('enkode_msg');

	$js = <<<EOT
<script type="text/javascript">
/* $msg <!-- */
function hivelogic_enkoder() {
var kode="$clean";var i,c,x;while(eval(kode));
}
hivelogic_enkoder()
/* --> */
</script>
EOT;

	return $js;
}

/* ENCODINGS ***********************/
/* 
Each encoding should consist of a function and a Javascript string; the function performs some scrambling of a string, and the Javascript unscrambles that string (assuming that it's stored in a variable kode).  The listed enkodings are those used in the Hivelogic Enkoder.
*/

/* REVERSE ENCODING */
function enk_enc_reverse($s) {
	return strrev($s);
}

$enk_dec_reverse = <<<EOT
kode=kode.split('').reverse().join('')
EOT;

/* SHIFT ENCODING */
/* This is buggy -- javascript bugs out when some weird characters are included.  For this reason, enk_enc_num is written below. */
/*
function enk_enc_shift($s) {
	$shifted = strval($s);

	$len = strlen($s);
	for ($i = 0;$i < $len;$i++) {
		$b = ord($s[$i]) + 3;
		if ($b > 127) { $b -= 128; }
		$shifted[$i] = chr($b);
	}

	return $shifted;
}

$enk_dec_shift = <<<EOT
x='';for(i=0;i<kode.length;i++){c=kode.charCodeAt(i)-3;if(c<0)c+=128;x+=String.fromCharCode(c)}kode=x
EOT;
*/

/* NUM ENCODING (adapted)*/
function enk_enc_num($s) {
	$nums = "";

	$len = strlen($s);
	for ($i = 0;$i < $len;$i++) {
		$nums .= strval(ord($s[$i]) + 3);
		if ($i < $len - 1) { $nums .= ' '; } /* pwned lol rotfl!!!11 */
	}

	return $nums;
}

$enk_dec_num = <<<EOT
kode=kode.split(' ');x='';for(i=0;i<kode.length;i++){x+=String.fromCharCode(parseInt(kode[i])-3)}kode=x
EOT;

/* SWAP ENCODING */
function enk_enc_swap($s) {
	$swapped = strval($s);

	$len = strlen($s);
	for ($i = 0;$i < $len - 1;$i += 2) {
		$tmp = $swapped[$i + 1];
		$swapped[$i + 1] = $swapped[$i];
		$swapped[$i] = $tmp; /* 'fuck' is written here so you can grep for it */
	}

	return $swapped;
}

$enk_dec_swap = <<<EOT
x='';for(i=0;i<(kode.length-1);i+=2){x+=kode.charAt(i+1)+kode.charAt(i)}kode=x+(i<kode.length?kode.charAt(kode.length-1):'')
EOT;

/* ENCODING LIST *******************/
/*
Listed fully below.  Add in this format to get new phases, which will be used automatically.
*/

$enkodings = array(
array('enk_enc_reverse', $enk_dec_reverse),
array('enk_enc_num',     $enk_dec_num),
array('enk_enc_swap',    $enk_dec_swap)
);

?>
