<?php
/**
 * @file
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class BlogTagCloud {
	/** @var integer $tags_min_pts **/
	public $tags_min_pts = 8;
	/** @var integer $tags_max_pts **/
	public $tags_max_pts = 32;
	/** @var integer $tags_highest_count **/
	public $tags_highest_count = 0;
	/** @var string $tags_size_type **/
	public $tags_size_type = 'pt';
	/** @var array $tags **/
	public $tags = array();

	public function __construct( $limit = 10 ) {
		$this->limit = $limit;
		$this->initialize();
	}

	public function initialize() {
		$dbr = wfGetDB( DB_MASTER );
		$res = $dbr->select(
			'categorylinks',
			array( 'cl_to', 'COUNT(*) AS count' ),
			array(),
			__METHOD__,
			array(
				'GROUP BY' => 'cl_to',
				'ORDER BY' => 'count DESC',
				'LIMIT' => $this->limit
			)
		);

		$message = wfMessage( 'blog-tagcloud-blacklist' );
		$catsExcluded = array();
		if ( !$message->isDisabled() ) {
			$catsExcluded = explode( "\n* ", $message->inContentLanguage()->text() );
		}

		wfSuppressWarnings(); // prevent PHP from bitching about strtotime()
		foreach ( $res as $row ) {
			$tag_name = Title::makeTitle( NS_CATEGORY, $row->cl_to );
			$tag_text = $tag_name->getText();
			// Exclude dates and blacklisted categories
			if (
				!in_array( $tag_text, $catsExcluded ) &&
				strtotime( $tag_text ) == ''
			)
			{
				if ( $row->count > $this->tags_highest_count ) {
					$this->tags_highest_count = $row->count;
				}
				$this->tags[$tag_text] = array( 'count' => $row->count );
			}
		}
		wfRestoreWarnings();

		// sort tag array by key (tag name)
		if ( $this->tags_highest_count == 0 ) {
			return;
		}
		ksort( $this->tags );
		/* and what if we have _1_ category? like on a new wiki with nteen articles, mhm? */
		if ( $this->tags_highest_count == 1 ) {
			$coef = $this->tags_max_pts - $this->tags_min_pts;
		} else {
			$coef = ( $this->tags_max_pts - $this->tags_min_pts ) / ( ( $this->tags_highest_count - 1 ) * 2 );
		}
		foreach ( $this->tags as $tag => $att ) {
			$this->tags[$tag]['size'] = $this->tags_min_pts + ( $this->tags[$tag]['count'] - 1 ) * $coef;
		}
	}
}