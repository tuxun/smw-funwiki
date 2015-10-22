<?php
/**
 * Page Schemas extension
 *
 * @file
 * @ingroup Extensions
 *
 * This is the main include file for the Page Schemas MediaWiki extension.
 *
 * Usage: Add the following line in LocalSettings.php:
 * require_once( "$IP/extensions/PageSchemas/PageSchemas.php" );
 */

// Check environment
if ( !defined( 'MEDIAWIKI' ) ) {
	echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
	die( -1 );
}

/* Configuration */

define( 'PAGE_SCHEMAS_VERSION', '0.4.3' );

// Credits
$wgExtensionCredits['parserhook'][] = array(
	'path'			=> __FILE__,
	'name'			=> 'Page Schemas',
	'author'		=> array( 'Yaron Koren', 'Ankit Garg', '...' ),
	'version'		=> PAGE_SCHEMAS_VERSION,
	'url'			=> 'http://www.mediawiki.org/wiki/Extension:Page_Schemas',
	'descriptionmsg'	=> 'ps-desc',
);

// Shortcut to this extension directory
$dir = dirname( __FILE__ ) . '/';

// Internationalization
$wgMessagesDirs['PageSchemas'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['PageSchemas'] = $dir . 'PageSchemas.i18n.php';
$wgExtensionMessagesFiles['PageSchemasAlias'] = $dir . 'PageSchemas.i18n.alias.php';

// Job classes
$wgJobClasses['pageSchemasCreatePage'] = 'PSCreatePageJob';

// Register page classes
$wgAutoloadClasses['PageSchemasHooks'] = $dir . 'PageSchemas.hooks.php';
$wgAutoloadClasses['PageSchemas'] = $dir . 'PageSchemas.classes.php';
$wgAutoloadClasses['PSSchema'] = $dir . 'PageSchemas.classes.php';
$wgAutoloadClasses['ApiQueryPageSchemas'] = $dir . 'ApiQueryPageSchemas.php';
$wgAutoloadClasses['PSGeneratePages'] = $dir . 'specials/PS_GeneratePages.php';
$wgAutoloadClasses['PSEditSchema'] = $dir . 'specials/PS_EditSchema.php';
$wgAutoloadClasses['PSTabs'] = $dir . 'PS_Tabs.php';
$wgAutoloadClasses['PSExtensionHandler'] = $dir . 'PS_ExtensionHandler.php';
$wgAutoloadClasses['PSCreatePageJob'] = $dir . 'PS_CreatePageJob.php';

// Register special pages
$wgSpecialPages['GeneratePages'] = 'PSGeneratePages';
$wgSpecialPages['EditSchema'] = 'PSEditSchema';
$wgSpecialPageGroups['GeneratePages'] = 'other';
$wgSpecialPageGroups['EditSchema'] = 'other';
// Register parser hook
$wgHooks['ParserFirstCallInit'][] = 'PageSchemasHooks::register';

// Register page_props usage
$wgPageProps['PageSchema'] = 'Contents of &lt;PageSchema&gt; tag';

$wgHooks['UnknownAction'][] = 'PSTabs::onUnknownAction';
$wgHooks['SkinTemplateTabs'][] = 'PSTabs::displayTabs';
$wgHooks['SkinTemplateNavigation'][] = 'PSTabs::displayTabs2';

// User right for viewing the 'Generate pages' page
$wgAvailableRights[] = 'generatepages';
$wgGroupPermissions['sysop']['generatepages'] = true;

// Register client-side modules
$pageSchemasResourceTemplate = array(
	'localBasePath' => $dir,
	'remoteExtPath' => 'PageSchemas'
);
$wgResourceModules += array(
	'ext.pageschemas.main' => $pageSchemasResourceTemplate + array(
		'scripts' => array(
			'PageSchemas.js',
		),
		'styles' => array(
			'PageSchemas.css',
		),
		'dependencies' => array(
			'jquery',
		),
	),
);
$wgResourceModules += array(
	'ext.pageschemas.generatepages' => $pageSchemasResourceTemplate + array(
		'scripts' => array(
			'generatepages.js',
		),
		'styles' => array(
		),
		'dependencies' => array(
			'jquery',
		),
	),
);

// Page Schemas global variables
$wgPageSchemasFieldNum = 0;
$wgPageSchemasHandlerClasses = array();
