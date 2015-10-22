<?php

namespace SMW\Tests\Store\Maintenance;

use SMW\SQLStore\SimplePropertyStatisticsRebuilder;

use FakeResultWrapper;

/**
 * @covers \SMW\SQLStore\SimplePropertyStatisticsRebuilder
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 * @group semantic-mediawiki-unit
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v2+
 * @since 1.9.2
 *
 * @author mwjames
 */
class SimplePropertyStatisticsRebuilderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$store = $this->getMockForAbstractClass( '\SMW\Store' );

		$messagereporter = $this->getMockBuilder( '\SMW\Messagereporter' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMW\SQLStore\SimplePropertyStatisticsRebuilder',
			new SimplePropertyStatisticsRebuilder( $store, $messagereporter )
		);
	}

	public function testRebuildWithValidPropertyStatisticsStoreInsertUsageCount() {

		$arbitraryPropertyTableName = 'allornothing';

		$propertySelectRow = new \stdClass;
		$propertySelectRow->count = 1111;

		$selectResult = array(
			'smw_title'   => 'Foo',
			'smw_id'      => 9999
		);

		$selectResultWrapper = new FakeResultWrapper( array( (object)$selectResult ) );

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->atLeastOnce() )
			->method( 'select' )
			->will( $this->returnValue( $selectResultWrapper ) );

		$database->expects( $this->once() )
			->method( 'selectRow' )
			->with( $this->stringContains( $arbitraryPropertyTableName ),
				$this->anything(),
				$this->equalTo( array( 'p_id' => 9999 ) ),
				$this->anything() )
			->will( $this->returnValue( $propertySelectRow ) );

		$store = $this->getMockBuilder( '\SMWSQLStore3' )
			->disableOriginalConstructor()
			->setMethods( array(
				'getDatabase',
				'getPropertyTables' ) )
			->getMock();

		$store->expects( $this->atLeastOnce() )
			->method( 'getDatabase' )
			->will( $this->returnValue( $database ) );

		$store->expects( $this->atLeastOnce() )
			->method( 'getPropertyTables' )
			->will( $this->returnValue( array(
				$this->getNonFixedPropertyTable( $arbitraryPropertyTableName ) )
			) );

		$messagereporter = $this->getMockBuilder( '\SMW\Messagereporter' )
			->disableOriginalConstructor()
			->setMethods( array( 'reportMessage' ) )
			->getMock();

		$instance = new SimplePropertyStatisticsRebuilder(
			$store,
			$messagereporter
		);

		$propertyStatisticsStore = $this->getMockBuilder( '\SMW\Store\PropertyStatisticsStore' )
			->disableOriginalConstructor()
			->getMock();

		$propertyStatisticsStore->expects( $this->atLeastOnce() )
			->method( 'insertUsageCount' );

		$instance->rebuild( $propertyStatisticsStore );
	}

	protected function getNonFixedPropertyTable( $propertyTableName ) {

		$propertyTable = $this->getMockBuilder( '\stdClass' )
			->setMethods( array(
				'isFixedPropertyTable',
				'getName' ) )
			->getMock();

		$propertyTable->expects( $this->atLeastOnce() )
			->method( 'isFixedPropertyTable' )
			->will( $this->returnValue( false ) );

		$propertyTable->expects( $this->atLeastOnce() )
			->method( 'getName' )
			->will( $this->returnValue( $propertyTableName ) );

		return $propertyTable;
	}

}
