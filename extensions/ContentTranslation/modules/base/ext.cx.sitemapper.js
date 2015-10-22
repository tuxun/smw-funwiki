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
( function ( $, mw ) {
	'use strict';

	/**
	 * Handles providing URLs to different wikis.
	 * @class
	 */
	mw.cx.SiteMapper = function ( siteconfig ) {
		this.config = siteconfig;
	};

	/**
	 * Some wikis have domain names that do not match the content language.
	 * See: wgLanguageCode in operations/mediawiki-config/wmf-config/InitialiseSettings.php
	 * @param {string} language Language code
	 * @return {string}
	 */
	mw.cx.SiteMapper.prototype.getWikiDomainCode = function ( language ) {
		var languageToWikiDomainMapping = mw.config.get( 'wgContentTranslationDomainCodeMapping' );

		return languageToWikiDomainMapping[ language ] || language;
	};

	/**
	 * Get the API for a remote wiki.
	 *
	 * @param {string} language Language code
	 * @return {mediawiki.Api}
	 */
	mw.cx.SiteMapper.prototype.getApi = function ( language ) {
		var url, domain;

		domain = this.getWikiDomainCode( language );
		url = this.config.api.replace( '$1', domain );

		return new mw.Api( {
			ajax: {
				url: url
			}
		} );
	};

	/**
	 * Get a URL to an article in a wiki for a given language.
	 *
	 * @param {string} language Language code
	 * @param {string} title Page title
	 * @param {Object} [params] Query parameters
	 * @return {string}
	 */
	mw.cx.SiteMapper.prototype.getPageUrl = function ( language, title, params ) {
		var domain,
			base = this.config.view,
			extra = '';

		domain = this.getWikiDomainCode( language );
		if ( params && !$.isEmptyObject( params ) ) {
			base = this.config.action || this.config.view;
			extra = ( base.indexOf( '?' ) !== -1 ? '&' : '?' ) + $.param( params );
		}

		return base.replace( '$1', domain ).replace( '$2', title ) + extra;
	};

	/**
	 * Get the cxserver URL for the current site.
	 *
	 * @param {string} module CXServer module path
	 * @param {Object} [params]
	 * @return {string}
	 */
	mw.cx.SiteMapper.prototype.getCXServerUrl = function ( module, params ) {
		if ( params !== undefined ) {
			$.each( params, function ( key, value ) {
				module = module.replace( key, encodeURIComponent( value ) );
			} );
		}

		return this.config.cx + module;
	};

	/**
	 * Get the target title to publish based on the wiki configuration.
	 * @param {string} title
	 * @return {string} target title
	 */
	mw.cx.SiteMapper.prototype.getTargetTitle = function ( title ) {
		var targetTitle, targetNameSpace;

		// If the title is already in the user namespace, just return it
		if ( new mw.Title( title ).getNamespaceId() === 2 ) {
			return title;
		}

		targetNameSpace = mw.config.get( 'wgContentTranslationTargetNamespace' );
		switch ( targetNameSpace ) {
		case 'Main':
			targetTitle = title;
			break;
		case 'User':
			targetTitle = 'User:' + mw.user.getName() + '/' + title;
			break;
		default:
			targetTitle = targetNameSpace + ':' + title;
			break;
		}

		return targetTitle;
	};

	/**
	 * Get the URL for Special:CX on the needed wiki
	 * according to given source and target title and the target language.
	 *
	 * @param {string} sourceTitle
	 * @param {string} targetTitle
	 * @param {string} sourceLanguage
	 * @param {string} targetLanguage
	 * @param {string} [campaign]
	 */
	mw.cx.SiteMapper.prototype.getCXUrl = function (
		sourceTitle,
		targetTitle,
		sourceLanguage,
		targetLanguage,
		campaign
	) {
		var cxPage, queryParams, uri;

		cxPage = 'Special:ContentTranslation';
		queryParams = {
			page: sourceTitle,
			from: sourceLanguage,
			to: targetLanguage,
			targettitle: targetTitle
		};

		if ( campaign ) {
			queryParams.campaign = campaign;
		}

		if ( mw.config.get( 'wgContentTranslationTranslateInTarget' ) ) {
			uri = new mw.Uri( this.getPageUrl( targetLanguage, cxPage ) );
			$.extend( uri.query, queryParams );

			return uri.toString();
		}

		return mw.util.getUrl( cxPage, queryParams );
	};

	/**
	 * Set CX Token in a cookie.
	 * This token guarantees that the translator reads the license agreement
	 * and starts translating from CX dashboard enabled as beta feature
	 * from any wiki under the top domain.
	 *
	 * @param {string} sourceLanguage Source language
	 * @param {string} targetLanguage Target language
	 * @param {string} sourceTitle Source title
	 */
	mw.cx.SiteMapper.prototype.setCXToken = function ( sourceLanguage, targetLanguage, sourceTitle ) {
		var now, slug, name, domain, options;

		now = new Date();
		slug = mw.Title.newFromText( sourceTitle ).getMain();
		name = [ 'cx', slug, sourceLanguage, targetLanguage ].join( '_' );
		domain = location.hostname.indexOf( '.' ) > 0 ?
			'.' + location.hostname.split( '.' ).splice( 1 ).join( '.' ) :
			null; // Mostly for domains like "localhost"
		options = {
			path: mw.config.get( 'wgCookiePath' ),
			domain: domain, // Use domain cookie. Example: domain=.wikipedia.org
			expires: new Date( now.getTime() + ( 3600 * 1000 ) ) // 1 hour from now
		};

		// At this point, the translator saw the license agreement.
		// Save that information in a domain cookie.
		$.cookie( name, true, options );
	};
}( jQuery, mediaWiki ) );
