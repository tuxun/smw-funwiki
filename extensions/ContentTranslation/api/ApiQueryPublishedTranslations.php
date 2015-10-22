<?php
/**
 * Api module for querying published translations.
 *
 * @file
 * @copyright See AUTHORS.txt
 * @license GPL-2.0+
 */

/**
 *
 * @ingroup API ContentTranslationAPI
 */
class ApiQueryPublishedTranslations extends ApiQueryBase {

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName );
	}

	public function execute() {
		$from = $to = null;
		$params = $this->extractRequestParams();
		$result = $this->getResult();
		$user = $this->getUser();
		if ( isset( $params['from'] ) ) {
			$from = $params['from'];
		}
		if ( isset( $params['to'] ) ) {
			$to = $params['to'];
		}
		$limit = $params['limit'];
		$offset = $params['offset'];
		if ( $from !== null && !Language::isValidBuiltInCode( $from ) ) {
			$this->dieUsage( 'Invalid language', 'invalidlanguage' );
		}
		if ( $to !== null && !Language::isValidBuiltInCode( $to ) ) {
			$this->dieUsage( 'Invalid language', 'invalidlanguage' );
		}
		$translations = ContentTranslation\Translation::getAllPublishedTranslations(
			$from, $to, $limit, $offset
		);
		$resultSize = count( $translations );
		$result->addValue( array( 'result' ), 'translations', $translations );
	}

	public function getAllowedParams() {
		$allowedParams = array(
			'from' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'to' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'limit' => array(
				ApiBase::PARAM_DFLT => 500,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			),
			'offset' => array(
				ApiBase::PARAM_DFLT => '',
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			),
		);
		return $allowedParams;
	}

	protected function getExamplesMessages() {
		return array(
			'action=query&list=cxpublishedtranslations' =>
				'apihelp-query+cxpublishedtranslations-example-1',
			'action=query&list=cxpublishedtranslations&from=en' =>
				'apihelp-query+cxpublishedtranslations-example-2',
			'action=query&list=cxpublishedtranslations&from=en&to=es' =>
				'apihelp-query+cxpublishedtranslations-example-3',
		);
	}
}
