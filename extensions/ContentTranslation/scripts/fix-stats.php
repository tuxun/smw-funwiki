<?php
/**
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

class CxFixStats extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script to fix some cx stats numbers.';

		// Default to safe option which doesn't actually change data.
		$this->addOption(
			'really',
			'(optional) Also execute actions'
		);

		$this->resets = array();
		$this->tags = array();
	}

	public function execute() {
		$this->dryrun = !$this->hasOption( 'really' );

		if ( $this->dryrun ) {
			$this->output( "DRY-RUN mode: actions are NOT executed\n" );
		} else {
			$this->output( "EXECUTE mode: actions ARE executed\n" );
		}

		$db = ContentTranslation\Database::getConnection( DB_MASTER );

		$translations = $this->getRelevantTranslations( $db );

		foreach ( $translations as $row ) {
			$name = sprintf(
				"%-35s\t[%s]\t->\t%-35s\t[%s]\t",
				$row->translation_source_title,
				$row->translation_source_language,
				$row->translation_target_title,
				$row->translation_target_language
			);

			$this->output( "$name ({$row->translation_status})\n" );
			$this->checkTargetUrl( $row );
		}

		$this->changeStatus( $this->resets, $this->dryrun );
		$this->addTags( $this->tags, $this->dryrun );
	}

	protected function changeStatus( $resets, $dry ) {
		$count = count( $resets );
		if ( !$count ) {
			$this->output( "No changes to do to the central database\n" );

			return;
		}

		if ( $dry ) {
			$this->output( "$count rows would be updated to set target_url to null\n" );

			return;
		}

		$this->output( "$count rows ARE updated to set target_url to null\n" );
		$db->update(
			'cx_translations',
			array( 'translation_target_url' => null ),
			array( 'translation_id' => $resets ),
			__METHOD__
		);
	}

	protected function addTags( $items, $dry ) {
		$count = count( $items );
		if ( !$count ) {
			$this->output( "No revisions to tag\n" );

			return;
		}

		if ( $dry ) {
			$this->output( "$count revisions would be tagged\n" );

			return;
		}

		$this->output( "$count rows are tagged\n" );

		foreach ( $items as $item ) {
			list( $row, $revId ) = $item;
			ChangeTags::addTags(
				'contenttranslation',
				null,
				$revId,
				null,
				FormatJson::encode( array(
					'from' => $row->translation_source_language,
					'to' => $row->translation_target_language,
				) )
			);
		}
	}

	protected function getRelevantTranslations( $db ) {
		$fields = '*';
		$conds = array();

		if ( $GLOBALS['wgContentTranslationTranslateInTarget'] ) {
			$conds['translation_target_language'] = $GLOBALS['wgLanguageCode'];
		}

		$res = $db->select( 'cx_translations', $fields, $conds, __METHOD__ );

		return $res;
	}

	protected function checkTargetUrl( $row ) {
		if ( $row->translation_status === 'deleted' ) {
			return;
		}

		$url = $row->translation_target_url;
		if ( trim( $url ) === '' ) {
			return;
		}

		// ALERT: assumes certain URL pattern
		$cutoff = strpos( $url, '/wiki/' ) + 6;
		$name = rawurldecode( substr( $url, $cutoff ) );

		$title = Title::newFromText( $name );
		if ( !$title ) {
			if ( $row->translation_status === 'published' ) {
				$this->output( "\\- E10 Translation published, but target is not valid title :(\n" );
			} else {
				$this->output( "\\- E11 Invalid title, page cannot exist\n" );
				$this->resets[] = $row->translation_id;
			}

			return;
		}

		if ( $title->exists() ) {
			if ( $this->hasCxTag( $title, $row ) ) {
				if ( $row->translation_status === 'draft' ) {
					$this->output( "\\- E20 Page exists but status is draft\n" );
				} else {
					// status=published and page created with CX, the ideal case
				}
			} elseif ( $title->isRedirect() ) {
				$this->output( "\\- E22 Page is a redirect\n" );
			} else {
				$user = ContentTranslation\GlobalUser::newFromId( $row->translation_started_by );
				$userName = $user->getName();
				$revId = $this->findRevisionToTag(
					$title,
					$userName,
					$row->translation_last_updated_timestamp
				);

				if ( $revId !== false ) {
					$this->output( "\\- E25 Page exists but no tag. Can tag rev $revId by $userName.\n" );
					$this->tags[] = array( $row, $revId );
				} else {
					$this->output( "\\- E24 Page exists but no tag.\n" );
				}
			}

			return;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$conds = array();
		# Assuming timestamps are in the correct format already
		$conds[] = 'log_timestamp > ' . $dbr->addQuotes( $row->translation_start_timestamp );
		$conds['log_namespace'] = $title->getNamespace();
		$conds['log_title'] = $title->getDbKey();

		$field = $dbr->selectField( 'logging', 'log_type', $conds, __METHOD__ );
		if ( $field ) {
			$this->output( "\\- E30 Page doesn't exist but has log entry: $field\n" );
			return;
		}

		if ( $row->translation_status === 'published' ) {
			$this->output( "\\- E40 Translation published, but no sign of target page :(\n" );
		} else {
			$this->output( "\\- E41 Page doesn't exist and never had log entry\n" );
			$this->resets[] = $row->translation_id;
		}

		return;
	}

	protected function hasCxTag( Title $title, $row ) {
		$dbr = wfGetDB( DB_SLAVE );
		$conds = array();
		# Apparently translation_start_timestamp has been incorrecly updated on changes in the past
		#$conds[] = 'rev_timestamp > ' . $dbr->addQuotes( $row->translation_start_timestamp );
		$conds['rev_page'] = $title->getArticleId();
		$conds[] = 'rev_id = ct_rev_id';
		$conds['ct_tag'] = 'contenttranslation';

		$field = $dbr->selectField( array( 'revision', 'change_tag' ), 'ct_tag', $conds, __METHOD__ );
		return $field === 'contenttranslation';
	}

	protected function findRevisionToTag( Title $title, $name, $timestamp ) {
		$dbr = wfGetDB( DB_SLAVE );

		// Allow one minute slack
		$ts = wfTimestamp( TS_UNIX, $timestamp ) + 60;

		$tables = array( 'revision', 'change_tag' );

		$conds = array();
		$conds[] = 'rev_timestamp < ' . $dbr->addQuotes( $dbr->timestamp( $ts ) );
		$conds['rev_page'] = $title->getArticleId();
		$conds['rev_user_text'] = $name;

		// Take the oldest timestamp by the author
		$options = array(
			'ORDER BY' => 'rev_timestamp ASC'
		);

		$revId = $dbr->selectField( $tables, 'rev_id', $conds, __METHOD__, $options );
		return $revId;
	}
}

$maintClass = 'CxFixStats';
require_once RUN_MAINTENANCE_IF_MAIN;
