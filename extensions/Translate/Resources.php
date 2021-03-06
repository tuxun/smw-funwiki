<?php

/**
 * JavaScript and CSS resource definitions.
 *
 * @file
 * @license GPL-2.0+
 */

global $wgResourceModules;

$resourcePaths = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'Translate',
);

$wgResourceModules['ext.translate'] = array(
	'styles' => 'resources/css/ext.translate.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.base'] = array(
	'scripts' => 'resources/js/ext.translate.base.js',
	'dependencies' => array(
		'mediawiki.util',
		'mediawiki.api',
		'ext.translate.hooks',
	),
	'messages' => array(
		'translate-js-support-unsaved-warning',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.dropdownmenu'] = array(
	'styles' => 'resources/css/ext.translate.dropdownmenu.css',
	'scripts' => 'resources/js/ext.translate.dropdownmenu.js',
) + $resourcePaths;

$wgResourceModules['ext.translate.editor'] = array(
	'scripts' => array(
		'resources/js/ext.translate.editor.js',
		'resources/js/ext.translate.editor.helpers.js',
		'resources/js/ext.translate.editor.shortcuts.js',
		'resources/js/ext.translate.proofread.js',
		'resources/js/ext.translate.pagemode.js',
	),
	'styles' => array(
		'resources/css/ext.translate.editor.css',
		'resources/css/ext.translate.proofread.css',
		'resources/css/ext.translate.pagemode.css',
	),
	'dependencies' => array(
		'ext.translate.base',
		'ext.translate.storage',
		'ext.translate.hooks',
		'ext.translate.dropdownmenu',
		'ext.uls.buttons',
		'mediawiki.util',
		'mediawiki.Uri',
		'mediawiki.api',
		'mediawiki.api.parse',
		'mediawiki.user',
		'mediawiki.jqueryMsg',
		'jquery.makeCollapsible',
		'jquery.tipsy',
		'jquery.textchange',
		'jquery.autosize',
		'jquery.textSelection',
	),
	'messages' => array(
		'tux-status-translated',
		'tux-status-saving',
		'tux-status-unsaved',
		'tux-editor-placeholder',
		'tux-editor-paste-original-button-label',
		'tux-editor-discard-changes-button-label',
		'tux-editor-save-button-label',
		'tux-editor-skip-button-label',
		'tux-editor-cancel-button-label',
		'tux-editor-confirm-button-label',
		'tux-editor-shortcut-info',
		'tux-editor-edit-desc',
		'tux-editor-add-desc',
		'tux-editor-message-desc-more',
		'tux-editor-message-desc-less',
		'tux-editor-suggestions-title',
		'tux-editor-in-other-languages',
		'tux-editor-need-more-help',
		'tux-editor-ask-help',
		'tux-editor-tm-match',
		'tux-warnings-more',
		'tux-warnings-hide',
		'tux-editor-save-failed',
		'tux-editor-n-uses',
		'tux-editor-doc-editor-placeholder',
		'tux-editor-doc-editor-save',
		'tux-editor-doc-editor-cancel',
		'translate-edit-nopermission',
		'translate-edit-askpermission',
		'tux-editor-outdated-warning',
		'tux-editor-outdated-warning-diff-link',
		'tux-proofread-action-tooltip',
		'tux-proofread-edit-label',
		'tux-proofread-translated-by-self',
		'tux-editor-close-tooltip',
		'tux-editor-expand-tooltip',
		'tux-editor-collapse-tooltip',
		'tux-editor-message-tools-history',
		'tux-editor-message-tools-delete',
		'tux-editor-message-tools-translations',
		'tux-editor-loading',
		'tux-session-expired',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.groupselector'] = array(
	'styles' => 'resources/css/ext.translate.groupselector.css',
	'scripts' => 'resources/js/ext.translate.groupselector.js',
	'position' => 'top',
	'dependencies' => array(
		'ext.translate.base',
		'ext.translate.statsbar',
		'mediawiki.jqueryMsg',
		'ext.translate.loader',
		'jquery.ui.position.custom',
	),
	'messages' => array(
		'translate-msggroupselector-projects',
		'translate-msggroupselector-search-placeholder',
		'translate-msggroupselector-search-all',
		'translate-msggroupselector-search-recent',
		'translate-msggroupselector-view-subprojects',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.helplink'] = array(
	'styles' => 'resources/css/ext.translate.helplink.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.hooks'] = array(
	'scripts' => 'resources/js/ext.translate.hooks.js',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.legacy'] = array(
	'styles' => 'resources/css/ext.translate.legacy.css',
) + $resourcePaths;

$wgResourceModules['ext.translate.loader'] = array(
	'styles' => 'resources/css/ext.translate.loader.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.messagetable'] = array(
	'scripts' => 'resources/js/ext.translate.messagetable.js',
	'styles' => 'resources/css/ext.translate.messagetable.css',
	'dependencies' => array(
		'ext.translate.base',
		'ext.translate.hooks',
		'mediawiki.Uri',
		'mediawiki.util',
		'jquery.appear',
		'mediawiki.jqueryMsg',
		'ext.translate.parsers',
		'ext.translate.loader',
		'ext.uls.buttons',
		'jquery.textchange',
	),
	'messages' => array(
		'translate-messagereview-progress',
		'translate-messagereview-failure',
		'translate-messagereview-done',
		'api-error-badtoken',
		'api-error-emptypage',
		'api-error-fuzzymessage',
		'api-error-invalidrevision',
		'api-error-owntranslation',
		'api-error-unknownmessage',
		'api-error-unknownerror',
		'tpt-unknown-page',
		'tux-edit',
		'tux-status-fuzzy',
		'tux-status-optional',
		'tux-status-translated',
		'tux-status-proofread',
		'translate-edit-title',
		'tux-messagetable-more-messages',
		'tux-messagetable-loading-messages',
		'tux-message-filter-result',
		'tux-message-filter-advanced-button',
		'tux-empty-list-all',
		'tux-empty-list-all-guide',
		'tux-empty-list-translated',
		'tux-empty-list-translated-guide',
		'tux-empty-list-translated-action',
		'tux-empty-list-other',
		'tux-empty-list-other-guide',
		'tux-empty-list-other-action',
		'tux-empty-list-other-link',
		'tux-empty-no-messages-to-display',
		'tux-empty-show-optional-messages',
		'tux-message-filter-placeholder',
		'translate-language-disabled',
		'tux-empty-no-outdated-messages',
		'tux-empty-nothing-new-to-proofread',
		'tux-empty-you-can-help-providing',
		'tux-empty-you-can-review-already-proofread',
		'tux-empty-nothing-to-proofread',
		'tux-empty-there-are-optional',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.messagewebimporter'] = array(
	'styles' => 'resources/css/ext.translate.messagewebimporter.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.multiselectautocomplete'] = array(
	'scripts' => 'resources/js/ext.translate.multiselectautocomplete.js',
	'dependencies' => array(
		'jquery.ui.autocomplete',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.navitoggle'] = array(
	'skinScripts' => array(
		'vector' => 'resources/js/ext.translate.navitoggle.js',
	),
	'skinStyles' => array(
		'vector' => 'resources/css/ext.translate.navitoggle.css',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.pagetranslation.uls'] = array(
	'scripts' => 'resources/js/ext.translate.pagetranslation.uls.js',
	'dependencies' => array(
		'ext.uls.mediawiki',
		'mediawiki.Uri',
		'mediawiki.util',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.parsers'] = array(
	'scripts' => 'resources/js/ext.translate.parsers.js',
	'dependencies' => array(
		'mediawiki.util',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.quickedit'] = array(
	'scripts' => 'resources/js/ext.translate.quickedit.js',
	'styles' => 'resources/css/ext.translate.quickedit.css',
	'messages' => array( 'translate-js-nonext', 'translate-js-save-failed' ),
	'dependencies' => array(
		'jquery.form',
		'jquery.ui.dialog',
		'jquery.autosize',
		'mediawiki.util',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.selecttoinput'] = array(
	'scripts' => 'resources/js/ext.translate.selecttoinput.js',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.aggregategroups'] = array(
	'scripts' => 'resources/js/ext.translate.special.aggregategroups.js',
	'styles' => 'resources/css/ext.translate.special.aggregategroups.css',
	'position' => 'top',
	'dependencies' => array(
		'jquery.ui.autocomplete',
		'mediawiki.api',
		'mediawiki.util',
	),
	'messages' => array(
		'tpt-aggregategroup-remove-confirm',
		'tpt-aggregategroup-edit-name',
		'tpt-aggregategroup-edit-description',
		'tpt-aggregategroup-update',
		'tpt-aggregategroup-update-cancel',
		'tpt-invalid-group',
		'tpt-aggregategroup-add',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.importtranslations'] = array(
	'scripts' => 'resources/js/ext.translate.special.importtranslations.js',
	'dependencies' => array(
		'jquery.ui.autocomplete',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.languagestats'] = array(
	'scripts' => 'resources/js/ext.translate.special.languagestats.js',
	'styles' => 'resources/css/ext.translate.special.languagestats.css',
	'messages' => array(
		'translate-langstats-expandall',
		'translate-langstats-collapseall',
		'translate-langstats-expand',
		'translate-langstats-collapse',
	),
	'dependencies' => 'jquery.tablesorter',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.managegroups'] = array(
	'styles' => 'resources/css/ext.translate.special.managegroups.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.managetranslatorsandbox'] = array(
	'scripts' => 'resources/js/ext.translate.special.managetranslatorsandbox.js',
	'styles' => 'resources/css/ext.translate.special.managetranslatorsandbox.css',
	'position' => 'top',
	'dependencies' => array(
		'ext.translate.loader',
		'ext.translate.translationstashstorage',
		'ext.uls.buttons',
		'ext.uls.mediawiki',
		'mediawiki.api',
		'mediawiki.jqueryMsg',
		'mediawiki.language',
		'jquery.ui.dialog',
	),
	'messages' => array(
		'tsb-all-languages-button-label',
		'tsb-accept-button-label',
		'tsb-reject-button-label',
		'tsb-selected-count',
		'tsb-older-requests',
		'tsb-accept-all-button-label',
		'tsb-reject-all-button-label',
		'tsb-user-posted-a-comment',
		'tsb-reminder-link-text',
		'tsb-reminder-sending',
		'tsb-reminder-sent',
		'tsb-reminder-sent-new',
		'tsb-reminder-failed',
		'tsb-didnt-make-any-translations',
		'tsb-translations-source',
		'tsb-translations-user',
		'tsb-translations-current',
		'tsb-request-count',
		'tsb-no-requests-from-new-users',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.pagemigration'] = array(
	'styles' => 'resources/css/ext.translate.special.pagemigration.css',
	'scripts' => 'resources/js/ext.translate.special.pagemigration.js',
	'dependencies' => array(
		'mediawiki.api',
		'mediawiki.ui.button',
		'jquery.ajaxdispatcher',
	),
	'messages' => array(
		'pm-page-does-not-exist',
		'pm-old-translations-missing',
		'pm-extra-units-warning',
		'pm-pagename-missing',
		'pm-langcode-missing',
		'pm-add-icon-hover-text',
		'pm-swap-icon-hover-text',
		'pm-delete-icon-hover-text',
		'pm-pagetitle-invalid',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.pagepreparation'] = array(
	'scripts' => 'resources/js/ext.translate.special.pagepreparation.js',
	'messages' => array(
		'pp-save-message',
		'pp-prepare-message',
		'pp-already-prepared-message',
		'pp-pagename-missing',
	),
	'dependencies' => array(
		'jquery.mwExtension',
		'mediawiki.Title',
		'mediawiki.action.history.diff',
		'mediawiki.api',
		'mediawiki.jqueryMsg',
		'mediawiki.ui',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.pagetranslation'] = array(
	'scripts' => 'resources/js/ext.translate.special.pagetranslation.js',
	'styles' => 'resources/css/ext.translate.special.pagetranslation.css',
	'dependencies' => array(
		'ext.translate.multiselectautocomplete',
		'mediawiki.ui.button',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.searchtranslations'] = array(
	'scripts' => 'resources/js/ext.translate.special.searchtranslations.js',
	'dependencies' => array(
		'ext.translate.editor',
		'ext.uls.mediawiki',
		'ext.uls.geoclient',
		'ext.translate.groupselector',
		'mediawiki.Uri',
		'mediawiki.language',
	),
	'messages' => array(
		'translate-documentation-language',
		'translate-search-more-languages-info',
		'translate-search-more-groups-info',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.searchtranslations.styles'] = array(
	'styles' => 'resources/css/ext.translate.special.searchtranslations.css',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.supportedlanguages'] = array(
	'styles' => 'resources/css/ext.translate.special.supportedlanguages.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translate'] = array(
	'scripts' => 'resources/js/ext.translate.special.translate.js',
	'dependencies' => array(
		'mediawiki.jqueryMsg',
		'mediawiki.Uri',
		'mediawiki.api',
		'mediawiki.api.parse',
		'ext.translate.base',
		'ext.translate.groupselector',
		'ext.translate.messagetable',
		'ext.translate.navitoggle',
		'ext.translate.workflowselector',
		'ext.uls.mediawiki',
	),
	'messages' => array(
		'translate-documentation-language',
		'tpt-discouraged-language-force-header',
		'tpt-discouraged-language-force-content',
		'tpt-discouraged-language-header',
		'tpt-discouraged-language-content',
		'tux-editor-proofreading-hide-own-translations',
		'tux-editor-proofreading-show-own-translations',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translate.styles'] = array(
	'styles' => 'resources/css/ext.translate.special.translate.css',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translationstash'] = array(
	'scripts' => 'resources/js/ext.translate.special.translationstash.js',
	'styles' => 'resources/css/ext.translate.special.translationstash.css',
	'position' => 'top',
	'dependencies' => array(
		'ext.translate.editor',
		'ext.translate.messagetable',
		'ext.translate.translationstashstorage',
		'mediawiki.api',
		'mediawiki.language',
		'ext.uls.mediawiki',
	),
	'messages' => array(
		'translate-translationstash-translations',
		'translate-translationstash-skip-button-label',
		'tsb-limit-reached-title',
		'tsb-limit-reached-body',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translationstats'] = array(
	'scripts' => 'resources/js/ext.translate.special.translationstats.js',
	'dependencies' => array(
		'jquery.ui.datepicker',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.statsbar'] = array(
	'styles' => 'resources/css/ext.translate.statsbar.css',
	'scripts' => 'resources/js/ext.translate.statsbar.js',
	'messages' => array(
		'translate-statsbar-tooltip',
		'translate-statsbar-tooltip-with-fuzzy',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.storage'] = array(
	'scripts' => 'resources/js/ext.translate.storage.js',
) + $resourcePaths;


$wgResourceModules['ext.translate.tabgroup'] = array(
	'styles' => 'resources/css/ext.translate.tabgroup.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.translationstashstorage'] = array(
	'scripts' => 'resources/js/ext.translate.translationstashstorage.js',
	'dependencies' => array(
		'mediawiki.api',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.workflowselector'] = array(
	'styles' => 'resources/css/ext.translate.workflowselector.css',
	'scripts' => 'resources/js/ext.translate.workflowselector.js',
	'messages' => array(
		'translate-workflow-set-doing',
		'translate-workflow-state-',
		'translate-workflowstatus',
	),
	'dependencies' => array(
		'ext.translate.dropdownmenu',
		'mediawiki.api',
	),
) + $resourcePaths;

// Third party module
$wgResourceModules['jquery.ajaxdispatcher'] = array(
	'scripts' => 'resources/js/jquery.ajaxdispatcher.js',
) + $resourcePaths;

$wgResourceModules['jquery.autosize'] = array(
	'scripts' => 'resources/js/jquery.autosize.js',
) + $resourcePaths;

$wgResourceModules['jquery.textchange'] = array(
	'scripts' => 'resources/js/jquery.textchange.js',
) + $resourcePaths;

// Use different name to not conflict with core.
// MediaWiki <= 1.23 has 1.8.x, which is too old for us.
$wgResourceModules['jquery.ui.position.custom'] = array(
	'scripts' => 'resources/js/jquery.ui.position.js',
) + $resourcePaths;

$wgHooks['ResourceLoaderTestModules'][] =
	// Dependencies must be arrays here
	function ( array &$modules ) use ( $resourcePaths ) {
		$modules['qunit']['ext.translate.parsers.test'] = array(
			'scripts' => array( 'tests/qunit/ext.translate.parsers.test.js' ),
			'dependencies' => array( 'ext.translate.parsers' ),
		) + $resourcePaths;

		$modules['qunit']['ext.translate.special.pagemigration.test'] = array(
			'scripts' => array( 'tests/qunit/ext.translate.special.pagemigration.test.js' ),
			'dependencies' => array( 'ext.translate.special.pagemigration' ),
		) + $resourcePaths;

		return true;
	};
