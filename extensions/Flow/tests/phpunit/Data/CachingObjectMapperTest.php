<?php

namespace Flow\Tests\Data;

use Flow\Data\Mapper\CachingObjectMapper;
use Flow\Tests\FlowTestCase;

/**
 * @group Flow
 */
class CachingObjectManagerTest extends FlowTestCase {

	public function testReturnsSameObject() {
		$mapper = $this->createMapper();
		$object = $mapper->fromStorageRow( array( 'id' => 1 ) );
		$this->assertSame( $object, $mapper->fromStorageRow( array( 'id' => 1 ) ) );
	}

	public function testAllowsNullPkOnPut() {
		$this->createMapper()->toStorageRow( (object)array( 'id' => null ) );
		$this->assertTrue( true );
	}

	protected function createMapper() {
		$toStorageRow = function( $object ) { return (array)$object; };
		$fromStorageRow = function( array $row, $object ) {
			if ( $object === null ) {
				return (object)$row;
			} else {
				return (object)( $row + (array)$object );
			}
		};
		return new CachingObjectMapper( $toStorageRow, $fromStorageRow, array( 'id' ) );
	}
}
