<?php
/**
 * Extension based on SkinPerPage to allow a customized skin per namespace
 *
 * Require MediaWiki 1.19.0 or greater.
 *
 * @file
 * @author Alexandre Emsenhuber
 * @license GPLv2
 */

$wgHooks['RequestContextCreateSkin'][] = 'efSkinPerPageRequestContextCreateSkin';

// Add credits :)
$wgExtensionCredits['other'][] = array(
	'path'        => __FILE__,
	'name'        => 'SkinPerNamespace',
	'url'         => 'https://www.mediawiki.org/wiki/Extension:SkinPerNamespace',
	'version'     => '2014-04-01',
	'descriptionmsg' => 'skinpernamespace-desc',
	'author'      => 'Alexandre Emsenhuber',
);

$wgMessagesDirs['SkinPerNamespace'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['SkinPerNamespace'] = dirname( __FILE__ ) . "/SkinPerNamespace.i18n.php";

// Configuration part, you can copy it to your LocalSettings.php and change it
// there, *not* here. Also modify it after including this file or you won't see
// any changes.

/**
 * Array mapping namespace index (i.e. numbers) to skin names
 */
$wgSkinPerNamespace = array();

/**
 * Skins for special pages, mapping canonical name (see SpecialPage.php) to skin
 * names
 */
$wgSkinPerSpecialPage = array();

/**
 * Override preferences for logged in users ?
 * if set to false, this will only apply to anonymous users
 */
$wgSkinPerNamespaceOverrideLoggedIn = true;

// Implementation

/**
 * Hook function for RequestContextCreateSkin
 * @param IContextSource $context
 * @param Skin $skin
 * @return bool
 */
function efSkinPerPageRequestContextCreateSkin( $context, &$skin ) {
	global $wgSkinPerNamespace, $wgSkinPerSpecialPage,
		$wgSkinPerNamespaceOverrideLoggedIn;

	if ( !$wgSkinPerNamespaceOverrideLoggedIn && $context->getUser()->isLoggedIn() ) {
		return true;
	}

	$title = $context->getTitle();
	$ns = $title->getNamespace();
	$skinName = null;

	if ( $ns == NS_SPECIAL ) {
		list( $canonical, /* $subpage */ ) = SpecialPageFactory::resolveAlias( $title->getDBkey() );
		if ( isset( $wgSkinPerSpecialPage[$canonical] ) ) {
			$skinName = $wgSkinPerSpecialPage[$canonical];
		}
	}

	if ( $skinName === null && isset( $wgSkinPerNamespace[$ns] ) ) {
		$skinName = $wgSkinPerNamespace[$ns];
	}

	if ( $skinName !== null ) {
		$skin = $skinName;
	}

	return true;
}
