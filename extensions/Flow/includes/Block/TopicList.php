<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Data\Pager\Pager;
use Flow\Data\Pager\PagerPage;
use Flow\Exception\FlowException;
use Flow\Formatter\TocTopicListFormatter;
use Flow\Formatter\TopicListFormatter;
use Flow\Formatter\TopicListQuery;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Exception\FailCommitException;

class TopicListBlock extends AbstractBlock {

	/**
	 * @var array
	 */
	protected $supportedPostActions = array( 'new-topic' );

	/**
	 * @var array
	 */
	protected $supportedGetActions = array( 'view', 'view-topiclist' );

	// @Todo - fill in the template names
	protected $templates = array(
		'view' => '',
		'new-topic' => 'newtopic',
	);

	/**
	 * @var Workflow|null
	 */
	protected $topicWorkflow;

	/**
	 * @var TopicListEntry|null
	 */
	protected $topicListEntry;

	/**
	 * @var PostRevision|null
	 */
	protected $topicTitle;

	/**
	 * @var PostRevision|null
	 */
	protected $firstPost;

	/**
	 * @var array
	 *
	 * Associative array mapping topic ID (in alphadecimal form) to PostRevision for the topic root.
	 */
	protected $topicRootRevisionCache = array();

	/**
	 * @constant(TOCLIMIT)
	 *
	 * The limit of Table of Contents topics that are rendered per request
	 */
	const TOCLIMIT = 50;

	protected function validate() {
		// for now, new topic is considered a new post; perhaps some day topic creation should get it's own permissions?
		if (
			!$this->permissions->isRevisionAllowed( null, 'new-post' ) ||
			!$this->permissions->isBoardAllowed( $this->workflow, 'new-post' )
		) {
			$this->addError( 'permissions', $this->context->msg( 'flow-error-not-allowed' ) );
			return;
		}
		if ( !isset( $this->submitted['topic'] ) || !is_string( $this->submitted['topic'] ) ) {
			$this->addError( 'topic', $this->context->msg( 'flow-error-missing-title' ) );
			return;
		}
		$this->submitted['topic'] = trim( $this->submitted['topic'] );
		if ( strlen( $this->submitted['topic'] ) === 0 ) {
			$this->addError( 'topic', $this->context->msg( 'flow-error-missing-title' ) );
			return;
		}
		if ( mb_strlen( $this->submitted['topic'] ) > PostRevision::MAX_TOPIC_LENGTH ) {
			$this->addError( 'topic', $this->context->msg( 'flow-error-title-too-long', PostRevision::MAX_TOPIC_LENGTH ) );
			return;
		}

		if ( trim( $this->submitted['content'] ) === '' ) {
			$this->addError( 'content', $this->context->msg( 'flow-error-missing-content' ) );
			return;
		}

		// creates Workflow, Revision & TopicListEntry objects to be inserted into storage
		list( $this->topicWorkflow, $this->topicListEntry, $this->topicTitle, $this->firstPost ) = $this->create();

		if ( !$this->checkSpamFilters( null, $this->topicTitle ) ) {
			return;
		}
		if ( $this->firstPost && !$this->checkSpamFilters( null, $this->firstPost ) ) {
			return;
		}
	}

	/**
	 * Creates the objects about to be inserted into storage:
	 * * $this->topicWorkflow
	 * * $this->topicListEntry
	 * * $this->topicTitle
	 * * $this->firstPost
	 *
	 * @throws \MWException
	 * @throws \Flow\Exception\FailCommitException
	 * @return array Array of [$topicWorkflow, $topicListEntry, $topicTitle, $firstPost]
	 */
	protected function create() {
		$title = $this->workflow->getArticleTitle();
		$user = $this->context->getUser();
		$topicWorkflow = Workflow::create( 'topic', $title );
		$topicListEntry = TopicListEntry::create( $this->workflow, $topicWorkflow );
		$topicTitle = PostRevision::create(
			$topicWorkflow,
			$user,
			$this->submitted['topic'],
			// topic title is never parsed
			'wikitext'
		);

		$firstPost = null;
		if ( !empty( $this->submitted['content'] ) ) {
			$firstPost = $topicTitle->reply(
				$topicWorkflow,
				$user,
				$this->submitted['content'],
				// default to wikitext when not specified, for old API requests
				isset( $this->submitted['format'] ) ? $this->submitted['format'] : 'wikitext'
			);
			$topicTitle->setChildren( array( $firstPost ) );
		}

		return array( $topicWorkflow, $topicListEntry, $topicTitle, $firstPost );
	}

	public function commit() {
		if ( $this->action !== 'new-topic' ) {
			throw new FailCommitException( 'Unknown commit action', 'fail-commit' );
		}

		$metadata = array(
			'workflow' => $this->topicWorkflow,
			'board-workflow' => $this->workflow,
			'topic-title' => $this->topicTitle,
			'first-post' => $this->firstPost,
		);

		/*
		 * Order of storage is important! We've been changing when we stored
		 * workflow a couple of times. For now, it needs to be stored first:
		 * * TopicPageCreationListener.php (post listener) must first create the
		 *   Topic:Xyz page before NotificationListener.php (topic/post
		 *   listeners) creates notifications (& mails) that link to it
		 * * ReferenceExtractor.php (run from ReferenceRecorder.php, a post
		 *   listener) needs to parse content with Parsoid & for that it needs
		 *   the board title. AbstractRevision::getContent() will figure out
		 *   the title from the workflow: $this->getCollection()->getTitle()
		 * If you even feel the need to change the order, make sure you come
		 * up with a fix for the above things ;)
		 */
		$this->storage->put( $this->workflow, array() ); // 'discussion' workflow
		$this->storage->put( $this->topicWorkflow, $metadata ); // 'topic' workflow
		$this->storage->put( $this->topicListEntry, $metadata );
		$this->storage->put( $this->topicTitle, $metadata );
		if ( $this->firstPost !== null ) {
			$this->storage->put( $this->firstPost, $metadata + array(
				'reply-to' => $this->topicTitle
			) );
		}

		$output = array(
			'topic-page' => $this->topicWorkflow->getArticleTitle()->getPrefixedText(),
			'topic-id' => $this->topicTitle->getPostId(),
			'topic-revision-id' => $this->topicTitle->getRevisionId(),
			'post-id' => $this->firstPost ? $this->firstPost->getPostId() : null,
			'post-revision-id' => $this->firstPost ? $this->firstPost->getRevisionId() : null,
		);

		return $output;
	}

	public function renderTocApi( array $topicList, array $options ) {
		global $wgFlowDefaultLimit;

		$tocApiParams = array_merge(
			$options,
			array(
				'toconly' => true,
				'limit' => self::TOCLIMIT
			)
		);

		$findOptions = $this->getFindOptions( $options );

		// include the current sortby option.  Note that when 'user' is either
		// submitted or defaulted to this is the resulting sort. ex: newest
		$tocApiParams['sortby'] = $findOptions['sortby'];

		// In the case of 'newest' sort, we could save ourselves trouble and only
		// produce the necessary 40 topics that are missing from the ToC, by taking
		// the latest UUID from the topic list.
		// This is a bit harder for the case of 'updated' which requires a timestamp,
		// so in that case, we can stick to having repeated topics and letting the
		// data model sort through which ones it needs to update and which ones it
		// may ignore.
		if ( $tocApiParams['sortby'] === 'newest' ) {
			// Make sure we found topiclist block
			// and that it actually has roots in it
			$existingRoots = isset( $topicList['roots'] ) && is_array( $topicList['roots'] ) ?
				$topicList['roots'] : array();

			if ( count( $existingRoots ) > 0 ) {
				// Add new offset-id and limit to the api parameters and change the limit
				$tocApiParams['offset-id'] = end( $existingRoots );
				$tocApiParams['limit'] = self::TOCLIMIT - $wgFlowDefaultLimit;
			}
		}

		return $this->renderApi( $tocApiParams );
	}

	public function renderApi( array $options ) {
		$options = $this->preloadTexts( $options );

		$response = array(
			'submitted' => $this->wasSubmitted() ? $this->submitted : $options,
			'errors' => $this->errors,
		);

		// Repeating the default until we use the API for everything (bug 72659)
		// Also, if this is removed other APIs (i.e. ApiFlowNewTopic) may need
		// to be adjusted if they trigger a rendering of this block.
		$isTocOnly = isset( $options['toconly'] ) ? $options['toconly'] : false;

		if ( $isTocOnly ) {
			/** @var TocTopicListFormatter $serializer */
			$serializer = Container::get( 'formatter.topiclist.toc' );
		} else {
			/** @var TopicListFormatter $serializer */
			$serializer = Container::get( 'formatter.topiclist' );
			$format = isset( $options['format'] ) ? $options['format'] : 'fixed-html';
			$serializer->setContentFormat( $format );
		}

		// @todo remove the 'api' => true, its always api
		$findOptions = $this->getFindOptions( $options + array( 'api' => true ) );

		// include the current sortby option.  Note that when 'user' is either
		// submitted or defaulted to this is the resulting sort. ex: newest
		$response['sortby'] = $findOptions['sortby'];

		if ( $this->workflow->isNew() ) {
			return $response + $serializer->buildEmptyResult( $this->workflow );
		}

		$page = $this->getPage( $findOptions );
		$workflowIds = array();
		/** @var TopicListEntry $topicListEntry */
		foreach ( $page->getResults() as $topicListEntry ) {
			$workflowIds[] = $topicListEntry->getId();
		}

		$workflows = $this->storage->getMulti( 'Workflow', $workflowIds );

		if ( $isTocOnly ) {
			// We don't need any further data, so we skip the TopicListQuery.

			$topicRootRevisionsByWorkflowId = array();
			$workflowsByWorkflowId = array();

			foreach ( $workflows as $workflow ) {
				$alphaWorkflowId = $workflow->getId()->getAlphadecimal();
				$topicRootRevisionsByWorkflowId[$alphaWorkflowId] = $this->topicRootRevisionCache[$alphaWorkflowId];
				$workflowsByWorkflowId[$alphaWorkflowId] = $workflow;
			}

			return $response + $serializer->formatApi( $this->workflow, $topicRootRevisionsByWorkflowId, $workflowsByWorkflowId, $page );
		}

		/** @var TopicListQuery $query */
		$query = Container::get( 'query.topiclist' );
		$found = $query->getResults( $page->getResults() );
		wfDebugLog( 'FlowDebug', 'Rendering topiclist for ids: ' . implode( ', ', array_map( function( UUID $id ) {
			return $id->getAlphadecimal();
		}, $workflowIds ) ) );

		return $response + $serializer->formatApi( $this->workflow, $workflows, $found, $page, $this->context );
	}

	/**
	 * Transforms preload params into proper options we can assign to template.
	 *
	 * @param array $options
	 * @return array
	 * @throws \MWException
	 */
	protected function preloadTexts( $options ) {
		if ( isset( $options['preload'] ) ) {
			$title = \Title::newFromText( $options['preload'] );
			$page = \WikiPage::factory( $title );
			if ( $page->isRedirect() ) {
				$title = $page->getRedirectTarget();
				$page = \WikiPage::factory( $title );
			}

			if ( $page->exists() ) {
				$content = $page->getContent( \Revision::RAW );
				$options['content'] = $content->serialize();
			}
		}

		if ( isset( $options['preloadtitle'] ) ) {
			$options['topic'] = $options['preloadtitle'];
		}

		return $options;
	}

	public function getName() {
		return 'topiclist';
	}

	protected function getLimit( array $options ) {
		global $wgFlowDefaultLimit, $wgFlowMaxLimit;
		$limit = $wgFlowDefaultLimit;
		if ( isset( $options['limit'] ) ) {
			$requestedLimit = intval( $options['limit'] );
			$limit = min( $requestedLimit, $wgFlowMaxLimit );
			$limit = max( 0, $limit );
		}

		return $limit;
	}

	protected function getFindOptions( array $requestOptions ) {
		$findOptions = array();

		// Compute offset/limit
		$limit = $this->getLimit( $requestOptions );

		// @todo Once we migrate View.php to use the API directly
		// all defaults will be handled by API and not here.
		$requestOptions += array(
			'include-offset' => false,
			'offset-id' => false,
			'offset-dir' => 'fwd',
			'offset' => false,
			'api' => true,
			'sortby' => 'user',
			'savesortby' => false,
		);

		$user = $this->context->getUser();
		if ( strlen( $requestOptions['sortby'] ) === 0 ) {
			$requestOptions['sortby'] = 'user';
		}
		// the sortby option in $findOptions is not directly used for querying,
		// but is needed by the pager to generate appropriate pagination links.
		if ( $requestOptions['sortby'] === 'user' ) {
			$requestOptions['sortby'] = $user->getOption( 'flow-topiclist-sortby' );
		}
		switch( $requestOptions['sortby'] ) {
		case 'updated':
			$findOptions = array(
				'sortby' => 'updated',
				'sort' => 'workflow_last_update_timestamp',
				'order' => 'desc',
			) + $findOptions;

			if ( $requestOptions['offset-id'] ) {
				throw new FlowException( 'The `updated` sort order does not allow the `offset-id` parameter. Please use `offset`.' );
			}
			break;

		case 'newest':
		default:
			$findOptions = array(
				'sortby' => 'newest',
				'sort' => 'topic_id',
				'order' => 'desc',
			) + $findOptions;

			if ( $requestOptions['offset'] ) {
				throw new FlowException( 'The `newest` sort order does not allow the `offset` parameter.  Please use `offset-id`.' );
			}
		}

		if ( $requestOptions['offset-id'] ) {
			$findOptions['pager-offset'] = UUID::create( $requestOptions['offset-id'] );
		} elseif ( $requestOptions['offset'] ) {
			$findOptions['pager-offset'] = intval( $requestOptions['offset'] );
		}

		if ( $requestOptions['offset-dir'] ) {
			$findOptions['pager-dir'] = $requestOptions['offset-dir'];
		}

		if ( $requestOptions['include-offset'] ) {
			$findOptions['pager-include-offset'] = $requestOptions['include-offset'];
		}

		if ( $requestOptions['api'] ) {
			$findOptions['offset-elastic'] = false;
		}

		$findOptions['pager-limit'] = $limit;

		if (
			$requestOptions['savesortby']
			&& !$user->isAnon()
			&& $user->getOption( 'flow-topiclist-sortby' ) != $findOptions['sortby']
		) {
			$user->setOption( 'flow-topiclist-sortby', $findOptions['sortby'] );
			$user->saveSettings();
		}

		return $findOptions;
	}

	/**
	 * Gets a set of workflow IDs
	 * This filters result to only include unmoderated and locked topics.
	 *
	 * Also populates topicRootRevisionCache with a mapping from topic ID to the
	 * PostRevision for the topic root.
	 *
	 * @param array $findOptions
	 * @return PagerPage
	 */
	protected function getPage( array $findOptions ) {
		$pager = new Pager(
			$this->storage->getStorage( 'TopicListEntry' ),
			array( 'topic_list_id' => $this->workflow->getId() ),
			$findOptions
		);

		$postStorage = $this->storage->getStorage( 'PostRevision' );

		// Work around lack of $this in closures until we can use PHP 5.4+ features.
		$topicRootRevisionCache =& $this->topicRootRevisionCache;

		return $pager->getPage( function( array $found ) use ( $postStorage, &$topicRootRevisionCache ) {
			$queries = array();
			/** @var TopicListEntry[] $found */
			foreach ( $found as $entry ) {
				$queries[] = array( 'rev_type_id' => $entry->getId() );
			}
			$posts = $postStorage->findMulti( $queries, array(
				'sort' => 'rev_id',
				'order' => 'DESC',
				'limit' => 1,
			) );
			$allowed = array();
			foreach ( $posts as $queryResult ) {
				$post = reset( $queryResult );
				if ( !$post->isModerated() || $post->isLocked() ) {
					$allowed[$post->getPostId()->getAlphadecimal()] = $post;
				}
			}
			foreach ( $found as $idx => $entry ) {
				if ( isset( $allowed[$entry->getId()->getAlphadecimal()] ) ) {
					$topicRootRevisionCache[$entry->getId()->getAlphadecimal()] = $allowed[$entry->getId()->getAlphadecimal()];
				} else {
					unset( $found[$idx] );
				}
			}

			return $found;
		} );
	}

	/**
	 * @param \OutputPage $out
	 */
	public function setPageTitle( \OutputPage $out ) {
		if ( $this->action !== 'new-topic' ) {
			// Only new-topic should override page title, rest should default
			parent::setPageTitle( $out );
			return;
		}

		$title = $this->workflow->getOwnerTitle();
		$message = $out->msg( 'flow-newtopic-first-heading', $title->getPrefixedText() );
		$out->setPageTitle( $message );
		$out->setHtmlTitle( $message );
		$out->setSubtitle( '&lt; ' . \Linker::link( $title ) );
	}
}
