/**
 * ContentTranslation extension - Translation listing in dashboard.
 *
 * @file
 * @ingroup Extensions
 * @copyright See AUTHORS.txt
 * @license GPL-2.0+
 */
( function ( $, mw ) {
	'use strict';

	/**
	 * CXTranslationList
	 *
	 * @class
	 */
	function CXTranslationList( $container, translations, siteMapper ) {
		this.$container = $container;
		this.siteMapper = siteMapper;
		this.translations = translations;
		this.filters = {
			status: null,
			sourceLanguage: null,
			targetLanguage: null
		};

		this.$sourceLanguageFilter = null;
		this.$targetLanguageFilter = null;
		this.$header = null;
		this.$confirmationDialog = null;
		this.$overlay = null;

		this.listen();
	}

	CXTranslationList.prototype.init = function () {
		this.listTranslations();
		this.filters.status = 'draft';
		this.applyFilters( this.filters );
	};

	/**
	 * Get the thumbnail image of the given link.
	 * @param {string} id translation id
	 * @param {string} language
	 * @param {string} title Title
	 * @return {jQuery.Promise}
	 */
	CXTranslationList.prototype.getLinkImage = function ( language, title ) {
		return this.siteMapper.getApi( language ).get( {
			action: 'query',
			titles: title,
			prop: 'pageimages',
			piprop: 'thumbnail',
			pithumbsize: 150,
			redirects: true,
			format: 'json'
		}, {
			dataType: 'jsonp',
			// This prevents warnings about the unrecognized parameter "_"
			cache: true
		} );
	};

	/**
	 * Show a title image of the translation based on source title.
	 * @param {Object} translation
	 */
	CXTranslationList.prototype.showTitleImage = function ( translation, type ) {
		this.getLinkImage( translation.sourceLanguage, translation.sourceTitle )
			.done( function ( response ) {
				var pageId, page, imgSrc;

				pageId = Object.keys( response.query.pages )[ 0 ];
				page = response.query.pages[ pageId ];
				if ( page.thumbnail ) {
					imgSrc = page.thumbnail.source;
					$( '#' + type + translation.id ).find( '.image' ).attr( 'src', imgSrc );
				}
			} );
	};

	/**
	 * List all translations.
	 */
	CXTranslationList.prototype.listTranslations = function () {
		var i, translation, progress, $translation,
			$lastUpdated, $imageBlock, $image, $progressbar,
			sourceDir, targetDir,
			translationLinkUrl, $translationLink,
			$sourceLanguage, $targetLanguage, $languageContainer, $status,
			$actionsTrigger, $deleteTranslation, $menu, $menuContainer,
			$titleLanguageBlock,
			$translations = [];

		for ( i = 0; i < this.translations.length; i++ ) {
			translation = this.translations[ i ];

			try {
				progress = JSON.parse( translation.progress );
			} catch ( e ) {
				progress = {};
			}

			$translation = $( '<div>' )
				.addClass( 'cx-tlitem' )
				.attr( 'id', 'translation' + translation.id );
			$lastUpdated = $( '<div>' )
				.addClass( 'last-updated' )
				.text( moment( translation.lastUpdateTimeStamp, 'YYYYMMDDHHmmss Z' ).fromNow() );
			$imageBlock = $( '<div>' )
				.addClass( 'cx-tlitem__image' );
			$image = $( '<img>' )
				.addClass( 'image' );
			$progressbar = $( '<div>' )
				.addClass( 'progressbar' )
				.cxProgressBar( {
					weights: progress
				} );
			$imageBlock.append( $image );
			this.showTitleImage( translation, 'translation' );

			sourceDir = $.uls.data.getDir( translation.sourceLanguage );
			targetDir = $.uls.data.getDir( translation.targetLanguage );

			if ( translation.status === 'draft' ) {
				translationLinkUrl = new mw.Uri( mw.cx.siteMapper.getCXUrl(
					translation.sourceTitle,
					translation.targetTitle,
					translation.sourceLanguage,
					translation.targetLanguage
				) ).extend( {
					draft: translation.status === 'draft' ? translation.id : undefined
				} ).toString();
			}

			if ( translation.status === 'published' ) {
				translationLinkUrl = translation.targetURL;
			}

			$translationLink = $( '<a>' )
				.addClass( 'translation-link' )
				.prop( 'href', translationLinkUrl )
				// It must be a separate element to ensure
				// separation from the target title
				.append( $( '<span>' )
					.text( translation.sourceTitle )
					.addClass( 'source-title' )
					.prop( {
						lang: translation.sourceLanguage,
						dir: sourceDir
					} )
				);

			// If the translated title is different from the source title,
			// show it near the source title
			if ( translation.sourceTitle !== translation.targetTitle ) {
				$translationLink.append(
					$( '<span>' ).html( '&#160;' ), // nbsp to ensure separation between words
					$( '<span>' )
						.prop( {
							lang: translation.targetLanguage,
							dir: targetDir
						} )
						.addClass( 'target-title' )
						.text( translation.targetTitle )
				);
			}

			$sourceLanguage = $( '<div>' )
				.prop( {
					lang: translation.sourceLanguage,
					dir: sourceDir
				} )
				.addClass( 'cx-tlitem__languages__language cx-tlitem__languages__language--source' )
				.text( $.uls.data.getAutonym( translation.sourceLanguage ) );

			$targetLanguage = $( '<div>' )
				.prop( {
					lang: translation.targetLanguage,
					dir: targetDir
				} )
				.addClass( 'cx-tlitem__languages__language cx-tlitem__languages__language--target' )
				.text( $.uls.data.getAutonym( translation.targetLanguage ) );

			$languageContainer = $( '<div>' )
				.addClass( 'cx-tlitem__languages' )
				.append( $sourceLanguage, $targetLanguage );

			$status = $( '<div>' )
				.addClass( 'status status-' + translation.status )
				.text( mw.msg( 'cx-translation-status-' + translation.status ) );

			// If the translation is draft, allow deleting it
			if ( translation.status === 'draft' ) {
				$actionsTrigger = $( '<div>' )
					.addClass( 'cx-tlitem__actions__trigger' )
					.text( '…' );
				$deleteTranslation = $( '<li>' )
					.addClass( 'cx-discard-translation' )
					.text( mw.msg( 'cx-discard-translation' ) )
					.data( 'translation', translation );
				$menu = $( '<ul>' )
					.append( $deleteTranslation );
				$menuContainer = $( '<div>' )
					.addClass( 'cx-tlitem__actions' )
					.append( $actionsTrigger, $menu );
			} else {
				$menuContainer = $();
			}

			$titleLanguageBlock = $( '<div>' )
				.addClass( 'cx-tlitem__details' )
				.append( $translationLink, $progressbar, $lastUpdated, $languageContainer );

			$translation.append(
				$menuContainer,
				$imageBlock,
				$titleLanguageBlock
			);

			$translations.push( $translation );

			// Store reference to the DOM node
			translation.$element = $translation;
		}

		if ( $translations.length ) {
			this.$container.append( $( '<div>' )
				.addClass( 'cx-translationlist' )
				.append( $translations )
			);
		} else {
			this.$container.append( $( '<div>' )
				.addClass( 'cx-translationlist-empty' )
				.append(
					$( '<div>' )
						.addClass( 'cx-translationlist-empty__img' ),
					$( '<div>' )
						.addClass( 'cx-translationlist-empty__title' )
						.text( mw.msg( 'cx-translationlist-empty-title' ) ),
					$( '<div>' )
						.addClass( 'cx-translationlist-empty__desc' )
						.text( mw.msg( 'cx-translationlist-empty-desc' ) )
				) );
		}
	};

	CXTranslationList.prototype.listen = function () {
		var translationList = this;

		this.$container.on( 'click', '.cx-discard-translation', function ( e ) {
			var translation;

			e.stopPropagation();

			translation = $( e.target ).data( 'translation' );

			translationList.showDiscardConfirmation( translation ).done( function () {
				translationList.discardTranslation( translation ).done( function ( response ) {
					if ( response.cxdelete.result !== 'success' ) {
						return;
					}

					translationList.markTranslationAsDeleted( translation );
					mw.hook( 'mw.cx.translation.deleted' ).fire(
						translation.sourceLanguage,
						translation.targetLanguage,
						translation.sourceTitle,
						translation.targetTitle
					);
				} );
			} );
		} );

		this.$container.on( 'click', '.cx-tlitem', function () {
			if ( $( this ).hasClass( 'cx-translation-deleted' ) ) {
				return;
			}

			location.href = $( this ).find( '.translation-link' ).attr( 'href' );
		} );

		$( window ).scroll( $.throttle( 250, $.proxy( this.scroll, this ) ) );
	};

	CXTranslationList.prototype.scroll = function () {
		var scrollTop = $( window ).scrollTop(),
			offsetTop = this.$container.offset().top;

		if ( scrollTop > offsetTop ) {
			this.$container.addClass( 'sticky' );
		} else if ( scrollTop <= offsetTop ) {
			this.$container.removeClass( 'sticky' );
		}
	};

	/**
	 * Show the confirmation dialog for discarding a translation.
	 * @return {jQuery.Promise}
	 */
	CXTranslationList.prototype.showDiscardConfirmation = function () {
		var deferred, $cancelButton, $discardButton, $actions, $message,
			translationList = this;

		deferred = $.Deferred();

		if ( !this.$overlay ) {
			this.$overlay = mw.cx.widgets.overlay();
			$( 'body' ).append( this.$overlay );
		}

		if ( this.$confirmationDialog ) {
			$cancelButton = this.$confirmationDialog.find( '.cx-draft-discard-dialog__cancel' );
			$discardButton = this.$confirmationDialog.find( '.cx-draft-discard-dialog__discard' );
		} else {
			this.$confirmationDialog = $( '<div>' )
				.addClass( 'cx-draft-discard-dialog' );
			$cancelButton = $( '<button>' )
				.addClass( 'mw-ui-button mw-ui-quiet cx-draft-discard-dialog__cancel' )
				.text( mw.msg( 'cx-draft-cancel-button-label' ) );
			$discardButton = $( '<button>' )
				.addClass( 'mw-ui-button mw-ui-destructive cx-draft-discard-dialog__discard' )
				.text( mw.msg( 'cx-draft-discard-button-label' ) );
			$actions = $( '<div>' )
				.addClass( 'cx-draft-discard-dialog__actions' )
				.append( $discardButton, $cancelButton );
			$message = $( '<div>' )
				.addClass( 'cx-draft-discard-dialog__message' )
				.text( mw.msg( 'cx-draft-discard-confirmation-message' ) );

			$( 'body' ).append( this.$confirmationDialog.append( $message, $actions ) );
		}

		$cancelButton.one( 'click', function () {
			deferred.reject();
			translationList.$confirmationDialog.hide();
			translationList.$overlay.hide();
			$discardButton.off( 'click' );
		} );

		$discardButton.one( 'click', function () {
			deferred.resolve();
			translationList.$confirmationDialog.hide();
			translationList.$overlay.hide();
			$cancelButton.off( 'click' );
		} );

		this.$overlay.show();
		this.$confirmationDialog.show();

		return deferred.promise();
	};

	/**
	 * Mark the translation item in the translation list as deleted.
	 * @param {Object} translation
	 */
	CXTranslationList.prototype.markTranslationAsDeleted = function ( translation ) {
		$( '#translation' + translation.id )
			.addClass( 'cx-translation-deleted' )
			.find( '.status' )
			.removeClass( 'status-draft status-published' )
			.addClass( 'status-deleted' )
			.text( mw.msg( 'cx-translation-status-deleted' ) )
			.end()
			.find( '.cx-tlitem__actions' )
			.remove()
			.end()
			.find( '.translation-link' )
			.addClass( 'disabled' );
	};

	/**
	 * Discard a translation.
	 * @param {Object} translation
	 * @return {jQuery.Promise}
	 */
	CXTranslationList.prototype.discardTranslation = function ( translation ) {
		var apiParams = {
			action: 'cxdelete',
			from: translation.sourceLanguage,
			to: translation.targetLanguage,
			sourcetitle: translation.sourceTitle
		};

		return new mw.Api().postWithToken( 'edit', apiParams );
	};

	CXTranslationList.prototype.applyFilters = function ( filters ) {
		var i, translation, visible, j, filterProp, filterValue,
			keys = Object.keys( filters );

		for ( i = 0; i < this.translations.length; i++ ) {
			translation = this.translations[ i ];

			visible = true;
			for ( j = 0; j < keys.length; j++ ) {
				filterProp = keys[ j ];
				filterValue = filters[ filterProp ];

				if ( filterValue === null || filterValue === '' ) {
					continue;
				}

				visible = visible && translation[ filterProp ] === filterValue;
			}

			if ( visible ) {
				translation.$element.show();
			} else {
				translation.$element.hide();
			}
		}
	};

	mw.cx.CXTranslationList = CXTranslationList;
}( jQuery, mediaWiki ) );
