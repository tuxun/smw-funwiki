<?php

namespace SMW\Tests\Store\Maintenance;

use SMW\Store\Maintenance\ConceptCacheRebuilder;
use SMW\DIConcept;

/**
 * @covers \SMW\Store\Maintenance\ConceptCacheRebuilder
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
class ConceptCacheRebuilderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$store = $this->getMockForAbstractClass( '\SMW\Store' );

		$settings = $this->getMockBuilder( '\SMW\Settings' )
			->disableOriginalConstructor()
			->getMock();

		$messagereporter = $this->getMockBuilder( '\SMW\Messagereporter' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMW\Store\Maintenance\ConceptCacheRebuilder',
			new ConceptCacheRebuilder( $store, $settings, $messagereporter )
		);
	}

	/**
	 * @depends testCanConstruct
	 */
	public function testRebuildWithoutOptionsAndActions() {

		$store = $this->getMockForAbstractClass( '\SMW\Store' );

		$settings = $this->getMockBuilder( '\SMW\Settings' )
			->disableOriginalConstructor()
			->getMock();

		$messagereporter = $this->getMockBuilder( '\SMW\Messagereporter' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new ConceptCacheRebuilder(
			$store,
			$settings,
			$messagereporter
		);

		$this->assertFalse( $instance->rebuild() );
	}

	/**
	 * @dataProvider actionProvider
	 */
	public function testRebuildFullConceptWithoutRangeSelectionOnMockStore( $action ) {

		$concept = new DIConcept( 'Foo', '', '', '', '' );

		$concept->setCacheStatus( 'full' );
		$concept->setCacheDate( '1358515326' ) ;
		$concept->setCacheCount( '1000' );

		$instance = $this->acquireInstanceFor( $concept );

		$instance->setParameters( array(
			$action => true
		) );

		$this->assertTrue( $instance->rebuild() );
	}

	/**
	 * @dataProvider actionProvider
	 */
	public function testRebuildEmptyConceptWithoutRangeSelectionOnMockStore( $action ) {

		$concept = new DIConcept( 'Foo', '', '', '', '' );
		$concept->setCacheStatus( 'empty' );

		$instance = $this->acquireInstanceFor( $concept );

		$instance->setParameters( array(
			$action => true
		) );

		$this->assertTrue( $instance->rebuild() );
	}

	/**
	 * @dataProvider actionProvider
	 */
	public function testRebuildFullConceptWithRangeSelectionOnMockStore( $action ) {

		$concept = new DIConcept( 'Foo', '', '', '', '' );

		$concept->setCacheStatus( 'full' );
		$concept->setCacheDate( '1358515326' ) ;
		$concept->setCacheCount( '1000' );

		$instance = $this->acquireInstanceFor( $concept );

		$instance->setParameters( array(
			$action => true,
			's'     => 0,
			'e'     => 90000
		) );

		$this->assertTrue( $instance->rebuild() );
	}

	/**
	 * @dataProvider actionProvider
	 */
	public function testRebuildSingleEmptyConceptWithRangeSelectionOnMockStore( $action ) {

		$concept = new DIConcept( 'Foo', '', '', '', '' );
		$concept->setCacheStatus( 'empty' );

		$instance = $this->acquireInstanceFor( $concept );

		$instance->setParameters( array(
			$action => true,
			's'     => 0,
			'e'     => 90000
		) );

		$this->assertTrue( $instance->rebuild() );
	}

	/**
	 * @dataProvider actionProvider
	 */
	public function testRebuildSingleFullConceptOnMockStore( $action ) {

		$concept = new DIConcept( 'Foo', '', '', '', '' );

		$concept->setCacheStatus( 'full' );
		$concept->setCacheDate( '1358515326' ) ;
		$concept->setCacheCount( '1000' );

		$instance = $this->acquireInstanceFor( $concept );

		$instance->setParameters( array(
			$action   => true,
			'old'     => 10,
			'concept' => 'Bar'
		) );

		$this->assertTrue( $instance->rebuild() );
	}

	/**
	 * @dataProvider actionProvider
	 */
	public function testRebuildWithNullConceptOnMockStore( $action ) {

		$instance = $this->acquireInstanceFor( null );

		$instance->setParameters( array(
			$action   => true,
			'concept' => 'Bar'
		) );

		$this->assertTrue( $instance->rebuild() );
	}

	private function acquireInstanceFor( $concept = null ) {

		$expectedToRun = $concept !== null ? $this->any() : $this->never();
		$refreshConceptCacheReturn = $concept !== null ? $concept->getConceptQuery() : null;

		$row = new \stdClass;
		$row->page_namespace = 0;
		$row->page_title = 1;

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $expectedToRun )
			->method( 'select' )
			->will( $this->returnValue( array( $row ) ) );

		$store = $this->getMockBuilder( 'SMWSQLStore3' )
			->disableOriginalConstructor()
			->getMock();

		$store->expects( $this->once() )
			->method( 'getConceptCacheStatus' )
			->will( $this->returnValue( $concept ) );

		$store->expects( $expectedToRun )
			->method( 'refreshConceptCache' )
			->will( $this->returnValue( array( $refreshConceptCacheReturn ) ) );

		$store->expects( $expectedToRun )
			->method( 'getDatabase' )
			->will( $this->returnValue( $database ) );

		$settings = $this->getMockBuilder( '\SMW\Settings' )
			->disableOriginalConstructor()
			->getMock();

		$messagereporter = $this->getMockBuilder( '\SMW\Messagereporter' )
			->disableOriginalConstructor()
			->setMethods( array( 'reportMessage') )
			->getMock();

		$instance = new ConceptCacheRebuilder(
			$store,
			$settings,
			$messagereporter
		);

		$instance->setParameters( array(
			'quiet' => true,
		) );

		return $instance;
	}

	public function actionProvider() {
		return array(
			array( 'status' ),
			array( 'create' ),
			array( 'delete' )
		);
	}

}
