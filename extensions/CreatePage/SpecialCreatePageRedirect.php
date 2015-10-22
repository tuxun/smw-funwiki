<?php

/**
 * Redirect the submission request of the createpage parser hook to the actual page.
 *
 * @since 0.1
 *
 * @file SpecialCreatePage.php
 * @ingroup CreatePage
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialCreatePageRedirect extends UnlistedSpecialPage {

	public function __construct() {
		parent::__construct( 'CreatePageRedirect' );
	}

	public function execute( $subPage ) {
		$req = $this->getRequest();
		if ( $req->getCheck( 'pagename' ) ) {
			$pageName = $req->getText( 'pagename' );
			$pageNamespace = $req->getText( 'pagens' );
			if ( $pageNamespace == '' ||
				substr( $pageName, 0, strlen( "$pageNamespace:" ) ) == "$pageNamespace:" ) {
				$title = Title::newFromText( $pageName );
			} else {
				$title = Title::newFromText( "$pageNamespace:$pageName" );
			}
			$target = $this->getTargetURL( $title );
		} else {
			$target = Title::newMainPage()->getLocalURL();
		}
		$this->getOutput()->redirect( $target, '301' );
	}

	private function getTargetURL( Title $title ) {
		global $wgCreatePageEditExisting, $wgCreatePageUseVisualEditor;

		$isKnown = $title->isKnown();
		$query = array();
		if ( !$isKnown || $wgCreatePageEditExisting ) {
			# Preload is not yet supported by VisualEditor, but probably will be eventually.
			# See https://phabricator.wikimedia.org/T51622
			$query['preload'] = $this->getRequest()->getText( 'preload', '' );
			if ( !$isKnown ) {
				$query['redlink'] = '1';
			}
			if ( $wgCreatePageUseVisualEditor ) {
				$query['veaction'] = 'edit';
			} else {
				$query['action'] = 'edit';
			}
		}
		return $title->getLocalUrl( $query );
	}
}
