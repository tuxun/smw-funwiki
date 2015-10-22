<?php
/**
 * Code for language code and name processing.
 *
 * @file
 * @author Robert Leverington
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Handle language code and name processing for the Babel extension, it can also
 * be used by other extension which need such functionality.
 */
class BabelLanguageCodes {
	/**
	 * Takes a language code, and attempt to obtain a better variant of it,
	 * checks the MediaWiki language codes for a match, otherwise checks the
	 * Babel language codes CDB (preferring ISO 639-1 over ISO 639-3).
	 *
	 * @param $code String: Code to try and get a "better" code for.
	 * @return String (language code) or false (invalid language code).
	 */
	public static function getCode( $code ) {
		global $wgBabelLanguageCodesCdb;

		$mediawiki = Language::fetchLanguageName( $code );
		if ( $mediawiki !== '' ) {
			return $code;
		}

		$codes = false;
		try {
			$codesCdb = Cdb\Reader::open( $wgBabelLanguageCodesCdb );
			$codes = $codesCdb->get( $code );
		} catch ( Cdb\Exception $e ) {
			wfDebug( __METHOD__ . ": CdbException caught, error message was "
				. $e->getMessage() );
		}

		return $codes;
	}

	/**
	 * Take a code as input, and search a language name for it in
	 * a given language via Language::fetchLanguageNames() or
	 * else via the Babel language names CDB
	 *
	 * @param $code String: Code to get name for.
	 * @param $language String: Code of language to attempt to get name in,
	 *                  defaults to language of code.
	 * @return String (name of language) or false (invalid language code).
	 */
	public static function getName( $code, $language = null ) {
		global $wgBabelLanguageNamesCdb;

		// Get correct code, even though it should already be correct.
		$code = self::getCode( $code );
		if ( $code === false ) {
			return false;
		}

		$language = $language === null ? $code : $language;
		$names = Language::fetchLanguageNames( $language, 'all' );
		if ( isset( $names[$code] ) ) {
			return $names[$code];
		}

		$codes = false;
		try {
			$namesCdb = Cdb\Reader::open( $wgBabelLanguageNamesCdb );
			$codes = $namesCdb->get( $code );
		} catch ( Cdb\Exception $e ) {
			wfDebug( __METHOD__ . ": CdbException caught, error message was "
				. $e->getMessage() );
		}

		return $codes;
	}
}
