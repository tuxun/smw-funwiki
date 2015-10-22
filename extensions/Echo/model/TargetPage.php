<?php

/**
 * Map a title to an echo notification so that we can mark a notification as read
 * when visiting the page. This only supports titles with ids because majority
 * of notifications have page_id and searching by namespace and title is slow
 */
class EchoTargetPage extends EchoAbstractEntity {

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var Title|null
	 */
	protected $title;

	/**
	 * @var int
	 */
	protected $pageId;

	/**
	 * @var EchoEvent|null
	 */
	protected $event;

	/**
	 * @var int
	 */
	protected $eventId;

	/**
	 * Only allow creating instance internally
	 */
	protected function __construct() {}

	/**
	 * Create a EchoTargetPage instance from User, Title and EchoEvent
	 *
	 * @param User $user
	 * @param Title $title
	 * @param EchoEvent $event
	 * @return TargetPage|null
	 */
	public static function create( User $user, Title $title, EchoEvent $event ) {
		// This only support title with a page_id
		if ( $user->isAnon() || !$title->getArticleID() ) {
			return null;
		}
		$obj = new self();
		$obj->user = $user;
		$obj->event = $event;
		$obj->eventId = $event->getId();
		$obj->title = $title;
		$obj->pageId = $title->getArticleID();
		return $obj;
	}

	/**
	 * Create a EchoTargetPage instance from stdClass object
	 *
	 * @param stdClass $row
	 * @return EchoTargetPage
	 * @throws MWException
	 */
	public static function newFromRow( $row ) {
		$requiredFields = array (
			'etp_user',
			'etp_page',
			'etp_event'
		);
		foreach ( $requiredFields as $field ) {
			if ( !isset( $row->$field ) || !$row->$field ) {
				throw new MWException( $field . ' is not set in the row!' );
			}
		}
		$obj = new self();
		$obj->user = User::newFromId( $row->etp_user );
		$obj->pageId = $row->etp_page;
		$obj->eventId = $row->etp_event;
		return $obj;
	}

	/**
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return Title|null
	 */
	public function getTitle() {
		if ( !$this->title ) {
			$this->title = Title::newFromId( $this->pageId );
		}
		return $this->title;
	}

	/**
	 * @return int
	 */
	public function getPageId() {
		return $this->pageId;
	}

	/**
	 * @return EchoEvent
	 */
	public function getEvent() {
		if ( !$this->event ) {
			$this->event = EchoEvent::newFromID( $this->eventId );
		}
		return $this->event;
	}

	/**
	 * @return int
	 */
	public function getEventId() {
		return $this->eventId;
	}

	/**
	 * Convert the properties to a database row
	 * @return array
	 */
	public function toDbArray() {
		return array (
			'etp_user' => $this->user->getId(),
			'etp_page' => $this->pageId,
			'etp_event' => $this->eventId
		);
	}
}
