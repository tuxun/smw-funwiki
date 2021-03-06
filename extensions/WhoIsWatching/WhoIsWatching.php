<?php

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the skin file directly.
if (!defined('MEDIAWIKI')) {
	echo <<<EOT
To install WhoIsWatching extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/WhoIsWatching/WhoIsWatching.php" );
EOT;
	exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'version'        => '0.11.0',
	'name'           => 'WhoIsWatching',
	'author'         => 'Paul Grinberg, Siebrand Mazeland, Vitaliy Filippov',
	'email'          => 'vitalif at yourcmc dot ru',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:WhoIsWatching',
	'descriptionmsg' => 'whoiswatching-desc',
);

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['WhoIsWatching'] = $dir . 'WhoIsWatching_body.php';
$wgMessagesDirs['WhoIsWatching'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['WhoIsWatching'] = $dir . 'WhoIsWatching.i18n.php';
$wgExtensionMessagesFiles['WhoIsWatchingAlias'] = $dir . 'WhoIsWatching.alias.php';
$wgSpecialPages['WhoIsWatching'] = 'WhoIsWatching';

$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 'fnShowWatchingCount';

# Set the following to either 'UserName' or 'RealName' to display the list of watching users as such.
$whoiswatching_nametype = 'RealName';

# Set the following to either True or False to optionally allow users to add others to watch a particular page
$whoiswatching_allowaddingpeople = true;

# Set the following to either True or False to optionally display a count of zero users watching a particular page
$whoiswatching_showifzero = true;

function fnShowWatchingCount( &$template, &$tpl ) {
	global $wgLang, $wgPageShowWatchingUsers, $whoiswatching_showifzero, $wgOut;

	if ( $wgPageShowWatchingUsers && $whoiswatching_showifzero ) {
		$dbr = wfGetDB( DB_SLAVE );
		$watchlist = $dbr->tableName( 'watchlist' );
		$t = $template->getTitle();
		$res = $dbr->select( 'watchlist', 'COUNT(*) n', array(
			'wl_namespace' => $t->getNamespace(),
			'wl_title' => $t->getDBkey(),
		), 'SkinTemplate::outputPage' );
		$x = $dbr->fetchObject( $res );
		$numberofwatchingusers = $x->n;
		$msg = wfMsgExt(
			'number_of_watching_users_pageview', array( 'parseinline' ),
			$wgLang->formatNum( $numberofwatchingusers )
		);
		$tpl->set( 'numberofwatchingusers', $msg );
	}

	return true;
}
