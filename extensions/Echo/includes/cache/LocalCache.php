<?php

/**
 * Base Local cache object, which borrows the concept from Flow user listener
 */
abstract class EchoLocalCache {

	/**
	 * Max number of objects to hold in $targets.  In theory, 1000
	 * is very hard to reach in a normal web request. We need to
	 * put cap so it doesn't reach memory limit when running email
	 * digest against large amount of notications
	 */
	const TARGET_MAX_NUM = 1000;

	/**
	 * Target object cache
	 * @var MapCacheLRU
	 */
	protected $targets;

	/**
	 * Lookup ids that have not been resolved for a target
	 * @param int[]
	 */
	protected $lookups = array();

	/**
	 * Resolve ids in lookups to targets
	 */
	abstract protected function resolve();

	/**
	 * Use Factory method like EchoTitleLocalCache::create()
	 */
	protected function __construct() {
		$this->targets = new MapCacheLRU( self::TARGET_MAX_NUM );
	}

	/**
	 * Add a key to the lookup and the key is used to resolve cache target
	 *
	 * @param int $key
	 */
	public function add( $key, $target = null ) {
		if (
			count( $this->lookups ) < self::TARGET_MAX_NUM
			&& !$this->targets->get( $key )
		) {
			$this->lookups[$key] = $key;
		}
	}

	/**
	 * Get the cache target based on the key
	 *
	 * @param int $key
	 * @return mixed|null
	 */
	public function get( $key ) {
		$target = $this->targets->get( $key );
		if ( $target ) {
			return $target;
		}

		if ( isset( $this->lookups[$key] ) ) {
			$this->resolve();
			$target = $this->targets->get( $key );
			if ( $target ) {
				return $target;
			}
		}
		return null;
	}

	/**
	 * Clear everything in local cache
	 */
	public function clearAll() {
		$this->targets->clear();
		$this->lookups = array();
	}

	/**
	 * @return int[]
	 */
	public function getLookups() {
		return $this->lookups;
	}

	/**
	 * @return array
	 */
	public function getTargets() {
		return $this->targets;
	}

}
