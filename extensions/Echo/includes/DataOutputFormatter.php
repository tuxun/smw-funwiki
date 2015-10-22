<?php

/**
 * Utility class that formats a notification in the format specified
 */
class EchoDataOutputFormatter {

	/**
	 * Format a notification for a user in the format specified
	 *
	 * @param string|bool specifify output format, false to not format any notifications
	 * @param User|null the target user viewing the notification
	 * @return array
	 */
	public static function formatOutput( EchoNotification $notification, $format = false, User $user = null ) {
		$event = $notification->getEvent();
		$timestamp = $notification->getTimestamp();
		$utcTimestampUnix = wfTimestamp( TS_UNIX, $timestamp );

		// Default to notification user if user is not specified
		if ( !$user ) {
			$user = $notification->getUser();
		}

		if ( $notification->getBundleBase() && $notification->getBundleDisplayHash() ) {
			$event->setBundleHash( $notification->getBundleDisplayHash() );
		}

		$timestampMw = self::getUserLocalTime( $user, $timestamp );

		// Start creating date section header
		$now = wfTimestamp();
		$dateFormat = substr( $timestampMw, 0, 8 );
		$timeDiff = $now - $utcTimestampUnix;
		// Most notifications would be more than two days ago, check this
		// first instead of checking 'today' then 'yesterday'
		if ( $timeDiff > 172800 ) {
			$date = self::getDateHeader( $user, $timestampMw );
		// 'Today'
		} elseif ( substr( self::getUserLocalTime( $user, $now ), 0, 8 ) === $dateFormat ) {
			$date = wfMessage( 'echo-date-today' )->escaped();
		// 'Yesterday'
		} elseif ( substr( self::getUserLocalTime( $user, $now - 86400 ), 0, 8 ) === $dateFormat ) {
			$date = wfMessage( 'echo-date-yesterday' )->escaped();
		} else {
			$date = self::getDateHeader( $user, $timestampMw );
		}
		// End creating date section header

		$output = array(
			'id' => $event->getId(),
			'type' => $event->getType(),
			'category' => $event->getCategory(),
			'timestamp' => array(
				// UTC timestamp in UNIX format used for loading more notification
				'utcunix' => $utcTimestampUnix,
				'unix' => self::getUserLocalTime( $user, $timestamp, TS_UNIX ),
				'mw' => $timestampMw,
				'date' => $date
			),
		);

		if ( $event->getVariant() ) {
			$output['variant'] = $event->getVariant();
		}

		$title = $event->getTitle();
		if ( $title ) {
			$output['title'] = array(
				'full' => $title->getPrefixedText(),
				'namespace' => $title->getNSText(),
				'namespace-key' =>$title->getNamespace(),
				'text' => $title->getText(),
			);
		}

		$agent = $event->getAgent();
		if ( $agent ) {
			if ( $event->userCan( Revision::DELETED_USER, $user ) ) {
				$output['agent'] = array(
					'id' => $agent->getId(),
					'name' => $agent->getName(),
				);
			} else {
				$output['agent'] = array( 'userhidden' => '' );
			}
		}

		if ( $notification->getReadTimestamp() ) {
			$output['read'] = $notification->getReadTimestamp();
		}

		// This is only meant for unread notifications, if a notification has a target
		// page, then it shouldn't be auto marked as read unless the user visits
		// the target page or a user marks it as read manully ( coming soon )
		$output['targetpages'] = array();
		if ( $notification->getTargetPages() ) {
			foreach ( $notification->getTargetPages() as $targetPage ) {
				$output['targetpages'][] = $targetPage->getPageId();
			}
		}

		if ( $format ) {
			$output['*'] = EchoNotificationController::formatNotification( $event, $user, $format );
		}

		return $output;
	}

	/**
	 * Get the date header in user's format, 'May 10' or '10 May', depending
	 * on user's date format preference
	 * @param User $user
	 * @param string $timestampMw
	 * @return string
	 */
	protected static function getDateHeader( User $user, $timestampMw ) {
		$lang = RequestContext::getMain()->getLanguage();
		$dateFormat = $lang->getDateFormatString( 'pretty', $user->getDatePreference() ?: 'default' );
		return $lang->sprintfDate( $dateFormat, $timestampMw );
	}

	/**
	 * Helper function for converting UTC timezone to a user's timezone
	 *
	 * @param User
	 * @param string
	 * @param int output format
	 *
	 * @return string
	 */
	public static function getUserLocalTime( User $user, $ts, $format = TS_MW ) {
		$timestamp = new MWTimestamp( $ts );
		$timestamp->offsetForUser( $user );
		return $timestamp->getTimestamp( $format );
	}

}
