/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

( function ( $, mw ) {
	'use strict';

	QUnit.module( 'ext.cx.sitemapper', QUnit.newMwEnvironment( {
		setup: function () {
			this.siteMapper = new mw.cx.SiteMapper( {
				view: 'https://$1.wikipedia.org/wiki/$2',
				api: 'https://$1.wikipedia.org/w/api.php',
				cx: 'http://localhost:8080/page/$1/$2'
			} );
		}
	} ) );

	QUnit.test( 'getPageUrl', function ( assert ) {
		QUnit.expect( 2 );

		assert.strictEqual(
			this.siteMapper.getPageUrl( 'es', 'Title' ),
			'https://es.wikipedia.org/wiki/Title',
			'Simple title'
		);

		assert.strictEqual(
			this.siteMapper.getPageUrl( 'fi', 'Longer title' ),
			'https://fi.wikipedia.org/wiki/Longer title',
			'Title with space'
		);
	} );

	QUnit.test( 'getApi', function ( assert ) {
		var server = this.sandbox.useFakeServer();

		QUnit.expect( 2 );

		this.siteMapper.getApi( 'he' ).get( { action: 'testaction', format: 'json' } );
		assert.strictEqual( server.requests.length, 1 );
		assert.strictEqual(
			server.requests[0].url,
			'https://he.wikipedia.org/w/api.php?action=testaction&format=json'
		);

		server.requests[0].respond( 500 );
	} );
}( jQuery, mediaWiki ) );
