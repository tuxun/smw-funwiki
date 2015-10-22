<?php

/**
 * Class for the 'listerrors' parser hooks.
 * 
 * @since 0.4
 * 
 * @file
 * @ingroup ParamProcessor
 * 
 * @author Jeroen De Dauw
 */
class ValidatorListErrors extends ParserHook {
	/**
	 * Array to map the possible values for the 'minseverity' parameter
	 * to their equivalent in the ProcessingError::SEVERITY_ enum.
	 * 
	 * @since 0.4
	 * 
	 * @var array
	 */
	protected static $severityMap = array(
		'minor' => ProcessingError::SEVERITY_MINOR,
		'low' => ProcessingError::SEVERITY_LOW,
		'normal' => ProcessingError::SEVERITY_NORMAL,
		'high' => ProcessingError::SEVERITY_HIGH,
		'fatal' => ProcessingError::SEVERITY_FATAL
	);

	/**
	 * No LSB in pre-5.3 PHP *sigh*.
	 * This is to be refactored as soon as php >=5.3 becomes acceptable.
	 */	
	public static function staticInit( Parser &$wgParser ) {
		$className = __CLASS__;
		$instance = new $className();
		return $instance->init( $wgParser );
	}	
	
	/**
	 * Gets the name of the parser hook.
	 * @see ParserHook::getName
	 * 
	 * @since 0.4
	 * 
	 * @return string
	 */
	protected function getName() {
		return 'listerrors';
	}
	
	/**
	 * Returns an array containing the parameter info.
	 * @see ParserHook::getParameterInfo
	 * 
	 * @since 0.4
	 * 
	 * @return array of Parameter
	 */
	protected function getParameterInfo( $type ) {
		$params = array();

		$params['minseverity'] = array(
			'default' => ValidatorSettings::get( 'errorListMinSeverity' ),
			'values' => array_keys( self::$severityMap ),
			'message' => 'validator-listerrors-par-minseverity',
		);

 		return $params;
	}
	
	/**
	 * Returns the list of default parameters.
	 * @see ParserHook::getDefaultParameters
	 * 
	 * @since 0.4
	 * 
	 * @return array
	 */
	protected function getDefaultParameters( $type ) {
		return array( 'minseverity' );
	}
	
	/**
	 * Renders and returns the output.
	 * @see ParserHook::render
	 * 
	 * @since 0.4
	 * 
	 * @param array $parameters
	 * 
	 * @return string
	 */
	public function render( array $parameters ) {
		$errorList = $this->getErrorList(
			ProcessingErrorHandler::getErrors(),
			self::$severityMap[$parameters['minseverity']]
		);
		
		return $errorList;
	}
	
	/**
	 * Returns a list of errors in wikitext.
	 * 
	 * @since 0.4
	 * 
	 * @param array $errors
	 * @param integer $minSeverity
	 * 
	 * @return string
	 */
	public function getErrorList( array $errors, $minSeverity = ProcessingError::SEVERITY_MINOR ) {
		$elementHtml = array();
		
		if ( count( $errors ) == 0 ) {
			return '';
		}

		$elements = array_keys( $errors );
		natcasesort( $elements );
		
		foreach ( $elements as $element ) {
			$elementErrors = $this->getErrorListForElement( $errors[$element], $element, $minSeverity ); 
			
			if ( $elementErrors ) {
				$elementHtml[] = $elementErrors;
			}
		}

		if ( count( $elementHtml ) == 0 ) {
			return '';
		}
		
		return Html::element(
			'h2',
			array(),
			wfMsg( 'validator-listerrors-errors' )
		) . implode( "\n\n", $elementHtml );
	}
	
	/**
	 * Returns wikitext listing the errors for a single element. 
	 * 
	 * @since 0.4
	 * 
	 * @param array $allErrors
	 * @param string $element
	 * @param integer $minSeverity
	 * 
	 * @return mixed String or false
	 */	
	public function getErrorListForElement( array $allErrors, $element, $minSeverity = ProcessingError::SEVERITY_MINOR ) {
		$errors = array();

		foreach ( $allErrors as $error ) {
			if ( $error->hasSeverity( $minSeverity ) ) {
				$errors[] = $error;
			}
		}			

		if ( count( $errors ) > 0 ) {
			$lines = array();
			
			foreach ( $errors as $error ) {
				// TODO: switch on severity
				$lines[] = '* ' . wfMsgExt(
					'validator-listerrors-severity-message',
					'parsemag',
					$this->getSeverityMessage( $error->getSeverity() ),
					$error->message
				);
			}
			
			return '<h3>' . htmlspecialchars( $element ) . "</h3>\n\n" . implode( "\n", $lines );
		}
		else {
			return false;
		}
	}	
	
	/**
	 * Returns a message for a severity.
	 * 
	 * @since 0.4
	 * 
	 * @param integer $severity
	 * 
	 * @return string
	 */
	protected function getSeverityMessage( $severity ) {
		static $reverseMap = false;
		
		if ( $reverseMap === false ) {
			$reverseMap = array_flip( self::$severityMap );
		}
		
		return wfMsg( 'validator-listerrors-' . $reverseMap[$severity] );
	}		

	/**
	 * @see ParserHook::getDescription()
	 */
	public function getDescription() {
		return wfMsg( 'validator-listerrors-description' );
	}	
}
