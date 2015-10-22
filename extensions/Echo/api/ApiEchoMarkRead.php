<?php

class ApiEchoMarkRead extends ApiBase {

	public function execute() {
		// To avoid API warning, register the parameter used to bust browser cache
		$this->getMain()->getVal( '_' );

		$user = $this->getUser();
		if ( $user->isAnon() ) {
			$this->dieUsage( 'Login is required', 'login-required' );
		}

		$notifUser = MWEchoNotifUser::newFromUser( $user );

		$params = $this->extractRequestParams();

		// There is no need to trigger markRead if all notifications are read
		if ( $notifUser->getNotificationCount() > 0 ) {
			if ( count( $params['list'] ) ) {
				// Make sure there is a limit to the update
				$notifUser->markRead( array_slice( $params['list'], 0, ApiBase::LIMIT_SML2 ) );
				// Mark all as read
			} elseif ( $params['all'] ) {
				$notifUser->markAllRead();
				// Mark all as read for sections
			} elseif ( $params['sections'] ) {
				$notifUser->markAllRead( $params['sections'] );
			}
		}

		$rawCount = $notifUser->getNotificationCount();

		$result = array(
			'result' => 'success'
		);
		$rawCount = 0;
		foreach ( EchoAttributeManager::$sections as $section ) {
			$rawSectionCount = $notifUser->getNotificationCount( /* $tryCache = */true, DB_SLAVE, $section );
			$result[$section]['rawcount'] = $rawSectionCount;
			$result[$section]['count'] = EchoNotificationController::formatNotificationCount( $rawSectionCount );
			$rawCount += $rawSectionCount;
		}

		$result += array(
			'rawcount' => $rawCount,
			'count' => EchoNotificationController::formatNotificationCount( $rawCount ),
		);
		$this->getResult()->addValue( 'query', $this->getModuleName(), $result );
	}

	public function getAllowedParams() {
		return array(
			'list' => array(
				ApiBase::PARAM_ISMULTI => true,
			),
			'all' => array(
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_TYPE => 'boolean'
			),
			'sections' => array(
				ApiBase::PARAM_TYPE => EchoAttributeManager::$sections,
				ApiBase::PARAM_ISMULTI => true,
			),
			'token' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'list' => 'A list of notification IDs to mark as read',
			'all' => "If set to true, marks all of a user's notifications as read",
			'sections' => 'A list of sections to mark as read',
			'token' => 'edit token',
		);
	}

	public function needsToken() {
		return 'csrf';
	}

	public function getTokenSalt() {
		return '';
	}

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
		return true;
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Mark notifications as read for the current user';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=echomarkread&list=8',
			'api.php?action=echomarkread&all=true'
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=echomarkread&list=8'
				=> 'apihelp-echomarkread-example-1',
			'action=echomarkread&all=true'
				=> 'apihelp-echomarkread-example-2',
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Echo_(Notifications)/API';
	}
}
