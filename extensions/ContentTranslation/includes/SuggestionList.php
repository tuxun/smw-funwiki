<?php

namespace ContentTranslation;

class SuggestionList {
	const TYPE_DEFAULT = 0;
	const TYPE_FEATURED = 1;

	protected $id;
	protected $name;
	protected $info;
	protected $owner;
	protected $startTime;
	protected $endTime;
	protected $type;
	protected $public;

	public function __construct( array $params ) {
		if ( isset( $params['id'] ) ) {
			$this->id = (int)$params['id'];
		}

		$this->name = (string)$params['name'];

		if ( isset( $params['info'] ) ) {
			$this->info = (string)$params['info'];
		}

		if ( isset( $params['owner'] ) ) {
			$this->owner = (int)$params['owner'];
		}

		if ( isset( $params['public'] ) ) {
			$this->public = (bool)$params['public'];
		}

		if ( isset( $params['startTime'] ) ) {
			$this->startTime = $params['startTime'];
		}

		if ( isset( $params['endTime'] ) ) {
			$this->endTime = $params['endTime'];
		}

		if ( isset( $params['type'] ) ) {
			$this->type = $params['type'];
		}
	}

	/**
	 * @param stdClass $row
	 * @return SuggestionList
	 */
	public static function newFromRow( $row ) {
		$params = array(
			'id' => $row->cxl_id,
			'name' => $row->cxl_name,
			'info' => $row->cxl_info,
			'owner' => $row->cxl_owner,
			'startTime' => $row->cxl_start_time,
			'endTime' => $row->cxl_end_time,
			'type' => $row->cxl_type,
		);

		return new SuggestionList( $params );
	}

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function getDisplayNameMessage( \IContextSource $context ) {
		if ( $this->getType() === self::TYPE_FEATURED ) {
			return $context->msg( 'cx-suggestionlist-featured' );
		}

		return new \RawMessage( $this->getName() );
	}

	public function getInfo() {
		return $this->info;
	}

	public function getOwner() {
		if ( $this->owner ) {
			return $this->owner;
		}

		return 0;
	}

	public function isPublic() {
		return (bool)$this->public;
	}

	public function getStartTime() {
		return $this->startTime;
	}

	public function getEndTime() {
		return $this->endTime;
	}

	public function getType() {
		if ( $this->type === null ) {
			return self::TYPE_DEFAULT;
		}

		return $this->type;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}
}
