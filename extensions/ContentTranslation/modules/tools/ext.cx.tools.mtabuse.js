/**
 * ContentTranslation Tools
 * A tool that allows editors to translate pages from one language
 * to another with the help of machine translation and other translation tools
 *
 * @file
 * @ingroup Extensions
 * @copyright See AUTHORS.txt
 * @license GPL-2.0+
 */
( function ( $, mw ) {
	'use strict';

	var template = '<div class="card mtabuse">' +
		'<h2 class="card__mtabuse-title"></h2>' +
		'<div class="card__mtabuse-details"></div>' +
		'<div class="card__mtabuse-link"></div>' +
		'</div>';

	function MTAbuseCard() {
		this.$card = $( template );
		// This card need to be "sticky" till mt is below abuse threshold.
		this.sticky = true;
		this.render();
	}

	MTAbuseCard.prototype.render = function () {
		this.$card.find( '.card__mtabuse-details' )
			.text( mw.msg( 'cx-mt-abuse-warning-text' ) );

		this.$card.find( '.card__mtabuse-link' ).append( $( '<a>' )
			.prop( {
				href: mw.msg( 'cx-tools-view-guidelines-link' ),
				target: '_blank'
			} )
			.text( mw.msg( 'cx-tools-view-guidelines' ) ) );

		this.$card.hide();
	};

	MTAbuseCard.prototype.onShow = function () {
		mw.hook( 'mw.cx.tools.shown' ).fire( true );
	};

	/**
	 * Detects whether MT is abused or not.
	 * @param {object} progress Translation progress in percentage.
	 * @return {boolean}
	 */
	MTAbuseCard.prototype.isAbuse = function ( progress ) {
		var mtPercentage = progress.mt / progress.any * 100 || 0;

		// Only use abuse detection when thresold is reached and there are 5 or more
		// sections with MT. Some times, even with less than 5 sections, the total
		// translations can be a lot. So the condition then becomes: Either the mtSectionCount
		// is geater than 5 or total translation is greater than 75%.
		// This also address the case of a source article having just 5 or less sections.
		return mtPercentage > 75 &&
			( progress.mtSectionsCount > 5 || progress.any * 100 > 75 );
	};

	MTAbuseCard.prototype.getCard = function () {
		return this.$card;
	};

	MTAbuseCard.prototype.start = function ( progress ) {
		var mtPercentage = parseInt( progress.mt / progress.any * 100 || 0, 10 );
		if ( !this.isAbuse( progress ) ) {
			this.stop();
			this.sticky = false;
			return;
		}
		this.$card.show();
		this.$card.find( '.card__mtabuse-title' )
			.text( mw.msg(
				'cx-mt-abuse-warning-title',
				mw.language.convertNumber( mtPercentage ) ) );

		this.onShow();
	};

	MTAbuseCard.prototype.stop = function () {
		this.$card.remove();
		mw.hook( 'mw.cx.tools.shown' ).fire( false );
	};

	MTAbuseCard.prototype.getTriggerEvents = function () {
		return [
			'mw.cx.progress'
		];
	};

	mw.cx.tools.mtabuse = MTAbuseCard;
}( jQuery, mediaWiki ) );
