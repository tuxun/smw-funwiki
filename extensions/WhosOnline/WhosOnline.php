<?php
/**
 * WhosOnline extension - creates a list of logged-in users & anons currently online
 * The list can be viewed at Special:WhosOnline
 *
 * @file
 * @ingroup Extensions
 * @author Maciej Brencz <macbre(at)-spam-wikia.com> - minor fixes and improvements
 * @author ChekMate Security Group - original code
 * @see http://www.chekmate.org/wiki/index.php/MW:_Whos_Online_Extension
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Protect against arbitrary execution
 * This line must be present before any global variable is referenced.
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is not a valid entry point.' );
}

// Extension credits that show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'WhosOnline',
	'version' => '1.4.0',
	'author' => 'Maciej Brencz',
	'descriptionmsg' => 'whosonline-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:WhosOnline',
	'license-name' => 'GPL-2.0+',
);

// Showing anonymous users' IP addresses can be a security threat!
$wgWhosOnlineShowAnons = false;

// Set up the special page
$wgAutoloadClasses['SpecialWhosOnline'] = __DIR__ . '/WhosOnlineSpecialPage.php';
$wgAutoloadClasses['PagerWhosOnline'] = __DIR__ . '/WhosOnlineSpecialPage.php';
$wgMessagesDirs['WhosOnline'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['WhosOnlineAlias'] = __DIR__ . '/WhosOnline.alias.php';
$wgSpecialPages['WhosOnline'] = 'SpecialWhosOnline';

$wgHooks['BeforePageDisplay'][] = 'wfWhosOnline_update_data';

// update online data
function wfWhosOnline_update_data() {
	global $wgUser;

	// write to DB (use master)
	$dbw = wfGetDB( DB_MASTER );
	$now = gmdate( 'YmdHis', time() );

	// row to insert to table
	$row = array(
		'userid' => $wgUser->getId(),
		'username' => $wgUser->getName(),
		'timestamp' => $now
	);

	$method = __METHOD__;
	$dbw->onTransactionIdle( function() use ( $dbw, $method, $row ) {
		$dbw->upsert( 'online',
			$row,
			array( array( 'userid', 'username' ) ),
			array( 'timestamp' => $row['timestamp'] ),
			$method
		);
	} );

	return true;
}

// Register database operations
$wgHooks['LoadExtensionSchemaUpdates'][] = 'wfWhosOnlineCheckSchema';

function wfWhosOnlineCheckSchema( $updater ) {
	$updater->addExtensionUpdate( array( 'addTable', 'online',
		__DIR__ . '/whosonline.sql', true ) );
	return true;
}
