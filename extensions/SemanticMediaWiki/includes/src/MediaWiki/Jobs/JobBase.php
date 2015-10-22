<?php

namespace SMW\MediaWiki\Jobs;

use SMW\ContextResource;
use SMW\ExtensionContext;
use SMW\ContextAware;
use SMW\ContextInjector;

use Job;
use Title;

/**
 * @ingroup SMW
 *
 * @licence GNU GPL v2+
 * @since 1.9
 *
 * @author mwjames
 */
abstract class JobBase extends Job implements ContextAware, ContextInjector {

	/** @var ContextResource */
	protected $context = null;

	/** @var boolean */
	protected $enabledJobQueue = true;

	/** @var Job */
	protected $jobs = array();

	/**
	 * Whether to insert jobs into the JobQueue is enabled or not
	 *
	 * @since 1.9
	 *
	 * @param boolean|true $enableJobQueue
	 *
	 * @return JobBase
	 */
	public function setJobQueueEnabledState( $enableJobQueue = true ) {
		$this->enabledJobQueue = (bool)$enableJobQueue;
		return $this;
	}

	/**
	 * @note Job::batchInsert was deprecated in MW 1.21
	 * JobQueueGroup::singleton()->push( $job );
	 *
	 * @since 1.9
	 */
	public function pushToJobQueue() {
		$this->enabledJobQueue ? Job::batchInsert( $this->jobs ) : null;
	}

	/**
	 * @note Job::getType was introduced with MW 1.21
	 *
	 * @return string
	 */
	public function getType() {
		return $this->command;
	}

	/**
	 * @since 1.9
	 *
	 * @param ContextResource
	 */
	public function invokeContext( ContextResource $context ) {
		$this->context = $context;
	}

	/**
	 * @see ContextAware::withContext
	 *
	 * @since 1.9
	 *
	 * @return ContextResource
	 */
	public function withContext() {

		// JobBase is a top-level class and to avoid a non-instantiated object
		// a default builder is set as for when Jobs are triggered without
		// injected context (during command line etc.)
		if ( $this->context === null ) {
			$this->context = new ExtensionContext();
		}

		return $this->context;
	}

	/**
	 * Returns invoked Title object
	 *
	 * Apparently Job::getTitle() in MW 1.19 does not exist
	 *
	 * @since  1.9
	 *
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Whether the parameters contain an element for a given key
	 *
	 * @since  1.9
	 *
	 * @param mixed $key
	 *
	 * @return boolean
	 */
	public function hasParameter( $key ) {
		return isset( $this->params[ $key ] ) || array_key_exists( $key, $this->params );
	}

	/**
	 * Returns a parameter value for a given key
	 *
	 * @since  1.9
	 *
	 * @param mixed $key
	 *
	 * @return boolean
	 */
	public function getParameter( $key ) {
		return $this->params[ $key ];
	}

}
