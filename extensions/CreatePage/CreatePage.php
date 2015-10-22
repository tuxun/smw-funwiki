<?php

/**
 * Initialization file for the Create Page extension.
 *
 * Documentation:	 		https://www.mediawiki.org/wiki/Extension:Create_Page
 * Support					https://www.mediawiki.org/wiki/Extension_talk:Create_Page
 * Source code:				http://svn.wikimedia.org/viewvc/mediawiki/trunk/extensions/CreatePage
 *
 * @file CreatePage.php
 * @ingroup CreatePage
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

/**
 * This documentation group collects source code files belonging to Create Page.
 *
 * @defgroup CreatePage Create Page
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

global $wgVersion, $wgExtensionCredits, $wgExtensionMessagesFiles, $wgAutoloadClasses;
global $wgSpecialPages, $wgHooks, $wgResourceModules;

// Needs to be 1.18c because version_compare() works in confusing ways.
if ( version_compare( $wgVersion, '1.18c', '<' ) ) {
	die( '<b>Error:</b> Create Page requires MediaWiki 1.18 or above.' );
}

define( 'CP_VERSION', '0.4.0' );

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Create Page',
	'version' => CP_VERSION,
	'author' => array(
		'[https://www.mediawiki.org/wiki/User:Jeroen_De_Dauw Jeroen De Dauw]',
		'Ike Hecht'
	),
	'url' => 'https://www.mediawiki.org/wiki/Extension:Create_Page',
	'descriptionmsg' => 'cp-desc'
);
//Configuration
/* Set to true to edit existing pages. */
$wgCreatePageEditExisting = false;
/* Set to true to redirect to VisualEditor for page creation. */
$wgCreatePageUseVisualEditor = false;

// i18n
$wgMessagesDirs['CreatePage'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['CreatePage'] = __DIR__ . '/CreatePage.i18n.php';
$wgExtensionMessagesFiles['CreatePageAlias'] = __DIR__ . '/CreatePage.alias.php';
$wgExtensionMessagesFiles['CreatePageMagic'] = __DIR__ . '/CreatePage.magic.php';

$wgAutoloadClasses['SpecialCreatePageRedirect'] = __DIR__ . '/SpecialCreatePageRedirect.php';
$wgSpecialPages['CreatePageRedirect'] = 'SpecialCreatePageRedirect';

$wgResourceModules['ext.createPage'] = array(
	'scripts' => 'modules/createPage.searchSuggest.js',
	'dependencies' => 'mediawiki.searchSuggest',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'CreatePage'
);

$wgHooks['ParserFirstCallInit'][] = function( Parser &$parser ) {
	$parser->setFunctionHook( 'createpage', function( Parser $parser, PPFrame $frame, array $args ) {
		$html = Html::openElement( 'form', array(
			'action' => SpecialPage::getTitleFor( 'CreatePageRedirect' )->getLocalURL(),
			'method' => 'post',
			'style' => 'display: inline',
			'class' => 'createpageform'
			) );

		$html .= Html::input(
			'pagename',
			array_key_exists( 1, $args ) ? trim( $frame->expand( $args[1] ) ) : '', 'text',
				array( 'class' => 'pagenameinput' )
		);

		if ( array_key_exists( 0, $args ) ) {
			$namespaceText =  trim( $frame->expand( $args[0] ) );
			$attribs = array();

			// Find the ID of this namespace, if there is one.
			$namespaceID = MWNamespace::getCanonicalIndex( strtolower( $namespaceText ) );
			if ( $namespaceID != 0 ) {
				$attribs['nsid'] = $namespaceID;
			}
			$html .= Html::hidden( 'pagens', $namespaceText, $attribs );
		}

		$html .= '&#160;';

		$html .= Html::input(
			'createpage',
			array_key_exists( 2, $args ) ? trim( $frame->expand( $args[2] ) ) :
					wfMessage( 'cp-create' )->text(), 'submit'
		);

		if ( array_key_exists( 3, $args ) ) {
			$html .= Html::hidden( 'preload', trim( $frame->expand( $args[3] ) ) );
		}

		$html .= '</form>';

		return $parser->insertStripItem( $html );
	}, Parser::SFH_OBJECT_ARGS );

	return true;
};

$wgHooks['BeforePageDisplay'][] = function( OutputPage &$out ) {
	$out->addModules( 'ext.createPage' );
	return true;
};
