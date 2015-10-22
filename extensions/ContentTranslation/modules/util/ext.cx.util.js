/**
 * ContentTranslation extension
 * A tool that allows editors to translate pages from one language
 * to another with the help of machine translation and other translation
 *
 * @file
 * @ingroup Extensions
 * @copyright See AUTHORS.txt
 * @license GPL-2.0+
 */
( function ( mw, $ ) {
	'use strict';

	/**
	 * Generate a jQuery selector for all possible sections.
	 * @return {string} the section selector string
	 */
	mw.cx.getSectionSelector = function () {
		var sectionTypes;

		sectionTypes = [
			'div', 'p',
			// tables
			'table', 'tbody', 'thead', 'tfoot', 'caption', 'th', 'tr', 'td',
			// lists
			'ul', 'ol', 'li', 'dl', 'dt', 'dd',
			// HTML5 heading content
			'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hgroup',
			// HTML5 sectioning content
			'article', 'aside', 'body', 'nav', 'section', 'footer', 'header', 'figure',
			'figcaption', 'fieldset', 'details', 'blockquote',
			// other
			'hr', 'button', 'canvas', 'center', 'col', 'colgroup', 'embed',
			'map', 'object', 'pre', 'progress', 'video'
		];

		return sectionTypes.join( ',' );
	};

	mw.cx.getSourceSection = function ( id ) {
		// Sanity check, id should be either a number or prefixed with mw
		if ( !id || ( isNaN( id ) && id.indexOf( 'mw' ) !== 0 ) ) {
			return $( [] );
		} else {
			return $( document.getElementById( id ) );
		}
	};

	mw.cx.getTranslationSection = function ( id ) {
		// Sanity check
		if (  !id || isNaN( id ) && id.indexOf( 'mw' ) !== 0 ) {
			return $( [] );
		} else {
			return $( document.getElementById( 'cx' + id ) );
		}
	};

	/**
	 * Return array with duplicate items removed
	 * @param {array} list List of strings, numbers or boolean
	 * @return {array}
	 */
	mw.cx.unique = function ( list ) {
		return $.grep( list, function ( v, k ) {
			return $.inArray( v, list ) === k;
		} );
	};
}( mediaWiki, jQuery ) );
