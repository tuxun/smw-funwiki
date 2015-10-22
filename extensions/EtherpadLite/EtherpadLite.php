<?php
/**
 * EtherpadLite extension
 *
 * @file
 * @ingroup Extensions
 *
 * The extension adds a tag "eplite" to the MediaWiki parser and
 * provides a method to embed Etherpad Lite pads on MediaWiki pages.
 * An Etherpad Lite server is not part of the extension.
 *
 * Usage:
 *
 * <eplite id="padid" />
 * <eplite id="myPseudoSecretPadHash-7ujHvhq06g" />
 * <eplite id="padid" height="200px" width="600px" />
 * <eplite id="padid" src="http://www.another-pad-server.org/p/" />
 *
 * Installation:
 *
 * Add the following lines in LocalSettings.php:
 *
 * require_once( "$IP/extensions/EtherpadLite/EtherpadLite.php" );
 * Etherpad Lite host server Url.
 * The shown one is a test server: it is not meant for production.
 * $wgEtherpadLiteDefaultPadUrl    = "http://beta.etherpad.org/p/";
 * $wgEtherpadLiteDefaultWidth     = "600px";
 * $wgEtherpadLiteDefaultHeigth    = "400px";
 *
 * Prerequisite:
 *
 * You need at least one Etherpad Lite host server
 * The shown one is a test server: it is not meant for production.
 * $wgEtherpadLiteDefaultPadUrl = "http://beta.etherpad.org/p/";
 *
 * For setting up your own Etherpad Lite server (based on node.js) see
 * Etherpad Lite homepage https://github.com/Pita/etherpad-lite
 *
 * This extension is based on:
 *
 * https://github.com/johnyma22/etherpad-lite-jquery-plugin
 * https://github.com/Pita/etherpad-lite/wiki/Embed-Parameters
 *
 * The present MediaWiki extension does not require jquery. It adds an iframe.
 *
 * @author Thomas Gries
 * @license GPL v2
 * @license MIT
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

# Check environment
if ( !defined( 'MEDIAWIKI' ) ) {
	echo( "This is an extension to MediaWiki and cannot be run standalone.\n" );
	die( - 1 );
}

# Credits
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'EtherpadLite',
	'author' => array( 'Thomas Gries' ),
	'version' => '1.14.0 20140331',
	'url' => 'https://www.mediawiki.org/wiki/Extension:EtherpadLite',
	'descriptionmsg' => 'etherpadlite-desc',
);

$dir = dirname( __FILE__ ) . '/';
$wgMessagesDirs['EtherpadLite'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['EtherpadLite'] = $dir . 'EtherpadLite.i18n.php';
$wgAutoloadClasses['EtherpadLite'] = $dir . 'EtherpadLite_body.php';
$wgHooks['ParserFirstCallInit'][] = 'EtherpadLite::EtherpadLiteParserInit';

# for Special:TrackingCategories
$wgTrackingCategories[] = 'etherpadlite-tracking-category';

# Define a default Etherpad Lite server Url and base path
# unless a different server is defined with the src= attribute
$wgEtherpadLiteDefaultPadUrl="http://pad.comptoir.net/p/";
$wgEtherpadLiteDefaultWidth     = "400px";
$wgEtherpadLiteDefaultHeight    = "400px";
$wgEtherpadLiteMonospacedFont   = false;
$wgEtherpadLiteShowControls     = true;
$wgEtherpadLiteShowLineNumbers  = true;
$wgEtherpadLiteShowChat         = true;
$wgEtherpadLiteShowAuthorColors = true;

# Whitelist of allowed Etherpad Lite server Urls
#
# If there are items in the array, and the user supplied URL is not in the array,
# the url will not be allowed
#
# Urls are case-sensitively tested against values in the array.
# They must exactly match including any trailing "/" character.
#
# Warning: Allowing all urls (not setting a whitelist)
# may be a security concern.
#
# an empty or non-existent array means: no whitelist defined
# this is the default: an empty whitelist. No servers are allowed by default.

$wgEtherpadLiteUrlWhitelist = array($wgEtherpadLiteDefaultPadUrl);
# include "*" if you expressly want to allow all urls (you should not do this)
# $wgEtherpadLiteUrlWhitelist = array( "*" );
