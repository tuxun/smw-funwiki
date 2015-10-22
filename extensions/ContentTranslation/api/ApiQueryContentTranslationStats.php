<?php
/**
 * Api module for querying Content translations for stats.
 *
 * @file
 * @copyright See AUTHORS.txt
 * @license GPL-2.0+
 */

/**
 * Api module for querying ContentTranslation stats.
 *
 * @ingroup API ContentTranslationAPI
 */
class ApiQueryContentTranslationStats extends ApiQueryBase {

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName );
	}

	public function execute() {
		$result = $this->getResult();
		$result->addValue(
			array( 'query', 'contenttranslationstats' ),
			'pages',
			ContentTranslation\Translation::getStats()
		);
		$result->addValue(
			array( 'query', 'contenttranslationstats' ),
			'translators',
			ContentTranslation\Translator::getStats()
		);
	}

	protected function getExamplesMessages() {
		return array(
			'action=query&list=contenttranslationstats' =>
				'apihelp-query+contenttranslationstats-example-1',
		);
	}
}
