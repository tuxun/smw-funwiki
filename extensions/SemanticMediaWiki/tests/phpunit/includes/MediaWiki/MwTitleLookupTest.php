<?php

namespace SMW\Tests\MediaWiki;

use SMW\MediaWiki\MwTitleLookup;

use Title;

/**
 * @covers \SMW\MediaWiki\MwTitleLookup
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
class MwTitleLookupTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMW\MediaWiki\MwTitleLookup',
			new MwTitleLookup( $database )
		);
	}

	public function testSelectAllOnCategoryNamespace() {

		$row = new \stdClass;
		$row->cat_title = 'Foo';

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->any() )
			->method( 'select' )
			->with( $this->stringContains( 'category' ),
				$this->anything(),
				$this->anything(),
				$this->anything(),
				$this->anything() )
			->will( $this->returnValue( array( $row ) ) );

		$instance = new MwTitleLookup( $database );

		$this->assertArrayOfTitles( $instance->byNamespace( NS_CATEGORY )->selectAll() );
	}

	public function testSelectAllOnMainNamespace() {

		$row = new \stdClass;
		$row->page_namespace = NS_MAIN;
		$row->page_title = 'Bar';

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->any() )
			->method( 'select' )
			->with( $this->anything(),
				$this->anything(),
				$this->equalTo( array( 'page_namespace' => NS_MAIN ) ),
				$this->anything(),
				$this->anything() )
			->will( $this->returnValue( array( $row ) ) );

		$instance = new MwTitleLookup( $database );

		$this->assertArrayOfTitles( $instance->byNamespace( NS_MAIN )->selectAll() );
	}

	public function testSelectByRangeOnCategoryNamespace() {

		$row = new \stdClass;
		$row->cat_title = 'Foo';

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->any() )
			->method( 'select' )
			->with( $this->stringContains( 'category' ),
				$this->anything(),
				$this->equalTo( array( "cat_id BETWEEN 1 AND 5" ) ),
				$this->anything(),
				$this->anything() )
			->will( $this->returnValue( array( $row ) ) );

		$instance = new MwTitleLookup( $database );

		$this->assertArrayOfTitles( $instance->byNamespace( NS_CATEGORY )->selectByIdRange( 1, 5 ) );
	}

	public function testSelectByRangeOnMainNamespace() {

		$row = new \stdClass;
		$row->page_namespace = NS_MAIN;
		$row->page_title = 'Bar';

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->any() )
			->method( 'select' )
			->with( $this->anything(),
				$this->anything(),
				$this->equalTo( array( "page_id BETWEEN 6 AND 10", 'page_namespace' => NS_MAIN ) ),
				$this->anything(),
				$this->anything() )
			->will( $this->returnValue( array( $row ) ) );

		$instance = new MwTitleLookup( $database );

		$this->assertArrayOfTitles( $instance->byNamespace( NS_MAIN )->selectByIdRange( 6, 10 ) );
	}

	public function testSelectAllOnMainNamespaceWithEmptyResult() {

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->any() )
			->method( 'select' )
			->with( $this->anything(),
				$this->anything(),
				$this->equalTo( array( 'page_namespace' => NS_MAIN ) ),
				$this->anything(),
				$this->anything() )
			->will( $this->returnValue( false ) );

		$instance = new MwTitleLookup( $database );

		$this->assertArrayOfTitles( $instance->byNamespace( NS_MAIN )->selectAll() );
	}

	public function testSelectMaxIdForMainNamespace() {

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->once() )
			->method( 'selectField' )
			->with( $this->equalTo( 'page' ),
				$this->anything(),
				$this->anything(),
				$this->anything() )
			->will( $this->returnValue( 9999 ) );

		$instance = new MwTitleLookup( $database );

		$this->assertEquals( 9999, $instance->byNamespace( NS_MAIN )->selectMaxId() );
	}

	public function testSelectMaxIdForCategoryNamespace() {

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$database->expects( $this->once() )
			->method( 'selectField' )
			->with( $this->equalTo( 'category' ),
				$this->anything(),
				$this->anything(),
				$this->anything() )
			->will( $this->returnValue( 1111 ) );

		$instance = new MwTitleLookup( $database );

		$this->assertEquals( 1111, $instance->byNamespace( NS_CATEGORY )->selectMaxId() );
	}

	public function testSelectAllOnMissingNamespaceThrowsException() {

		$this->setExpectedException( 'UnexpectedValueException' );

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new MwTitleLookup( $database );
		$instance->selectAll();
	}

	public function testSelectByRangeOnMissingNamespaceThrowsException() {

		$this->setExpectedException( 'UnexpectedValueException' );

		$database = $this->getMockBuilder( '\SMW\MediaWiki\Database' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new MwTitleLookup( $database );
		$instance->selectByIdRange( 1, 5 );
	}

	protected function assertArrayOfTitles( $arrayOfTitles ) {

		$this->assertInternalType( 'array', $arrayOfTitles );

		foreach ( $arrayOfTitles as $title ) {
			$this->assertInstanceOf( 'Title', $title );
		}
	}

}