<?php

class Scribunto_LuaLanguageLibrary extends Scribunto_LuaLibraryBase {
	const MAX_LANG_CACHE_SIZE = 20;

	public $langCache = array();
	public $timeCache = array();

	function register() {
		// Pre-populate the language cache
		global $wgContLang;
		$this->langCache[$wgContLang->getCode()] = $wgContLang;

		$statics = array(
			'getContLangCode',
			'isSupportedLanguage',
			'isKnownLanguageTag',
			'isValidCode',
			'isValidBuiltInCode',
			'fetchLanguageName',
			'fetchLanguageNames',
			'getFallbacksFor',
		);
		$methods = array(
			'lcfirst',
			'ucfirst',
			'lc',
			'uc',
			'caseFold',
			'formatNum',
			'formatDate',
			'formatDuration',
			'getDurationIntervals',
			'parseFormattedNumber',
			'convertPlural',
			'convertGrammar',
			'gender',
			'isRTL',
		);
		$lib = array();
		foreach ( $statics as $name ) {
			$lib[$name] = array( $this, $name );
		}
		$ths = $this;
		foreach ( $methods as $name ) {
			$lib[$name] = function () use ( $ths, $name ) {
				$args = func_get_args();
				return $ths->languageMethod( $name, $args );
			};
		}
		return $this->getEngine()->registerInterface( 'mw.language.lua', $lib );
	}

	function getContLangCode() {
		global $wgContLang;
		return array( $wgContLang->getCode() );
	}

	function isSupportedLanguage( $code ) {
		$this->checkType( 'isSupportedLanguage', 1, $code, 'string' );
		try {
			// There's no good reason this should throw, but it does. Sigh.
			return array( Language::isSupportedLanguage( $code ) );
		} catch ( MWException $ex ) {
			return array( false );
		}
	}

	function isKnownLanguageTag( $code ) {
		$this->checkType( 'isKnownLanguageTag', 1, $code, 'string' );
		return array( Language::isKnownLanguageTag( $code ) );
	}

	function isValidCode( $code ) {
		$this->checkType( 'isValidCode', 1, $code, 'string' );
		return array( Language::isValidCode( $code ) );
	}

	function isValidBuiltInCode( $code ) {
		$this->checkType( 'isValidBuiltInCode', 1, $code, 'string' );
		return array( (bool)Language::isValidBuiltInCode( $code ) );
	}

	function fetchLanguageName( $code, $inLanguage ) {
		$this->checkType( 'fetchLanguageName', 1, $code, 'string' );
		$this->checkTypeOptional( 'fetchLanguageName', 2, $inLanguage, 'string', null );
		return array( Language::fetchLanguageName( $code, $inLanguage ) );
	}

	function fetchLanguageNames( $inLanguage, $include ) {
		$this->checkTypeOptional( 'fetchLanguageNames', 1, $inLanguage, 'string', null );
		$this->checkTypeOptional( 'fetchLanguageNames', 2, $include, 'string', 'mw' );
		return array( Language::fetchLanguageNames( $inLanguage, $include ) );
	}

	function getFallbacksFor( $code ) {
		$this->checkType( 'getFallbacksFor', 1, $code, 'string' );
		$ret = Language::getFallbacksFor( $code );
		// Make 1-based
		if ( count( $ret ) ) {
			$ret = array_combine( range( 1, count( $ret ) ), $ret );
		}
		return array( $ret );
	}

	/**
	 * Language object method handler
	 */
	function languageMethod( $name, $args ) {
		$name = strval( $name );
		$code = array_shift( $args );
		if ( !isset( $this->langCache[$code] ) ) {
			if ( count( $this->langCache ) > self::MAX_LANG_CACHE_SIZE ) {
				throw new Scribunto_LuaError( 'too many language codes requested' );
			}
			try {
				$this->langCache[$code] = Language::factory( $code );
			} catch ( MWException $ex ) {
				throw new Scribunto_LuaError( "language code '$code' is invalid" );
			}
		}
		$lang = $this->langCache[$code];
		switch ( $name ) {
			// Zero arguments
			case 'isRTL':
				return array( $lang->$name() );

			// One string argument passed straight through
			case 'lcfirst':
			case 'ucfirst':
			case 'lc':
			case 'uc':
			case 'caseFold':
				$this->checkType( $name, 1, $args[0], 'string' );
				return array( $lang->$name( $args[0] ) );

			case 'parseFormattedNumber':
				if ( is_numeric( $args[0] ) ) {
					$args[0] = strval( $args[0] );
				}
				if ( $this->getLuaType( $args[0] ) !== 'string' ) {
					// Be like tonumber(), return nil instead of erroring out
					return array( null );
				}
				return array( $lang->$name( $args[0] ) );

			// Custom handling
			default:
				return $this->$name( $lang, $args );
		}
	}

	/**
	 * convertPlural handler
	 */
	function convertPlural( $lang, $args ) {
		$number = array_shift( $args );
		$this->checkType( 'convertPlural', 1, $number, 'number' );
		if ( is_array( $args[0] ) ) {
			$args = $args[0];
		}
		$forms = array_values( array_map( 'strval', $args ) );
		return array( $lang->convertPlural( $number, $forms ) );
	}

	/**
	 * convertGrammar handler
	 */
	function convertGrammar( $lang, $args ) {
		$this->checkType( 'convertGrammar', 1, $args[0], 'string' );
		$this->checkType( 'convertGrammar', 2, $args[1], 'string' );
		return array( $lang->convertGrammar( $args[0], $args[1] ) );
	}

	/**
	 * gender handler
	 */
	function gender( $lang, $args ) {
		$this->checkType( 'gender', 1, $args[0], 'string' );
		$username = trim( array_shift( $args ) );

		if ( is_array( $args[0] ) ) {
			$args = $args[0];
		}
		$forms = array_values( array_map( 'strval', $args ) );

		// Shortcuts
		if ( count( $forms ) === 0 ) {
			return '';
		} elseif ( count( $forms ) === 1 ) {
			return $forms[0];
		}

		if ( $username === 'male' || $username === 'female' ) {
			$gender = $username;
		} else {
			// default
			$gender = User::getDefaultOption( 'gender' );

			// Check for "User:" prefix
			$title = Title::newFromText( $username );
			if ( $title && $title->getNamespace() == NS_USER ) {
				$username = $title->getText();
			}

			// check parameter, or use the ParserOptions if in interface message
			$user = User::newFromName( $username );
			if ( $user ) {
				$gender = GenderCache::singleton()->getGenderOf( $user, __METHOD__ );
			} elseif ( $username === '' ) {
				$parserOptions = $this->getParserOptions();
				if ( $parserOptions->getInterfaceMessage() ) {
					$gender = GenderCache::singleton()->getGenderOf( $parserOptions->getUser(), __METHOD__ );
				}
			}
		}
		return array( $lang->gender( $gender, $forms ) );
	}

	/**
	 * formatNum handler
	 */
	function formatNum( $lang, $args ) {
		$num = $args[0];
		$this->checkType( 'formatNum', 1, $num, 'number' );

		$noCommafy = false;
		if ( isset( $args[1] ) ) {
			$this->checkType( 'formatNum', 2, $args[1], 'table' );
			$options = $args[1];
			$noCommafy = !empty( $options['noCommafy'] );
		}
		return array( $lang->formatNum( $num, $noCommafy ) );
	}

	/**
	 * formatDate handler
	 */
	function formatDate( $lang, $args ) {
		$this->checkType( 'formatDate', 1, $args[0], 'string' );
		$this->checkTypeOptional( 'formatDate', 2, $args[1], 'string', '' );
		$this->checkTypeOptional( 'formatDate', 3, $args[2], 'boolean', false );

		list( $format, $date, $local ) = $args;
		$langcode = $lang->getCode();

		if ( $date === '' ) {
			$cacheKey = $this->getParserOptions()->getTimestamp();
			$timestamp = new MWTimestamp( $cacheKey );
			$date = $timestamp->getTimestamp( TS_ISO_8601 );
			$useTTL = true;
		} else {
			# Correct for DateTime interpreting 'XXXX' as XX:XX o'clock
			if ( preg_match( '/^[0-9]{4}$/', $date ) ) {
				$date = '00:00 '.$date;
			}

			$cacheKey = $date;
			$useTTL = false;
		}

		if ( isset( $this->timeCache[$format][$cacheKey][$langcode][$local] ) ) {
			$ttl = $this->timeCache[$format][$cacheKey][$langcode][$local][1];
			if ( $useTTL && $ttl !== null ) {
				$this->getEngine()->setTTL( $ttl );
			}
			return array( $this->timeCache[$format][$cacheKey][$langcode][$local][0] );
		}

		# Default input timezone is UTC.
		try {
			$utc = new DateTimeZone( 'UTC' );
			$dateObject = new DateTime( $date, $utc );
		} catch ( Exception $ex ) {
			throw new Scribunto_LuaError( "bad argument #2 to 'formatDate' (not a valid timestamp)" );
		}

		# Set output timezone.
		if ( $local ) {
			global $wgLocaltimezone;
			if ( isset( $wgLocaltimezone ) ) {
				$tz = new DateTimeZone( $wgLocaltimezone );
			} else {
				$tz = new DateTimeZone( date_default_timezone_get() );
			}
		} else {
			$tz = $utc;
		}
		$dateObject->setTimezone( $tz );
		# Generate timestamp
		$ts = $dateObject->format( 'YmdHis' );

		if ( $ts < 0 ) {
			throw new Scribunto_LuaError( "mw.language:formatDate() only supports years from 0" );
		} elseif ( $ts >= 100000000000000 ) {
			throw new Scribunto_LuaError( "mw.language:formatDate() only supports years up to 9999" );
		}

		$ttl = null;
		$ret = $lang->sprintfDate( $format, $ts, $tz, $ttl );
		$this->timeCache[$format][$cacheKey][$langcode][$local] = array( $ret, $ttl );
		if ( $useTTL && $ttl !== null ) {
			$this->getEngine()->setTTL( $ttl );
		}
		return array( $ret );
	}

	/**
	 * formatDuration handler
	 */
	function formatDuration( $lang, $args ) {
		$this->checkType( 'formatDuration', 1, $args[0], 'number' );
		$this->checkTypeOptional( 'formatDuration', 2, $args[1], 'table', array() );

		list( $seconds, $chosenIntervals ) = $args;
		$langcode = $lang->getCode();
		$chosenIntervals = array_values( $chosenIntervals );

		$ret = $lang->formatDuration( $seconds, $chosenIntervals );
		return array( $ret );
	}

	/**
	 * getDurationIntervals handler
	 */
	function getDurationIntervals( $lang, $args ) {
		$this->checkType( 'getDurationIntervals', 1, $args[0], 'number' );
		$this->checkTypeOptional( 'getDurationIntervals', 2, $args[1], 'table', array() );

		list( $seconds, $chosenIntervals ) = $args;
		$langcode = $lang->getCode();
		$chosenIntervals = array_values( $chosenIntervals );

		$ret = $lang->getDurationIntervals( $seconds, $chosenIntervals );
		return array( $ret );
	}
}
