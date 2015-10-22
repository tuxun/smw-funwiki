<?php

namespace SMW\Test;

/**
 * Tests for the SMWExpElement deriving classes.
 *
 * @file
 * @since 1.9
 *
 * @ingroup SMW
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SMWExpElementTest extends CompatibilityTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string|false
	 */
	public function getClass() {
		return '\SMWExpElement';
	}

	public function instanceProvider() {
		$instances = array();

		$instances[] = new \SMWExpResource( 'foo' );
		$instances[] = new \SMWExpResource( 'foo', null );
		$instances[] = new \SMWExpResource( 'foo', new \SMWDIBlob( 'bar' ) );

		$instances[] = new \SMWExpNsResource( 'foo', 'bar', 'baz' );
		$instances[] = new \SMWExpNsResource( 'foo', 'bar', 'baz', null );
		$instances[] = new \SMWExpNsResource( 'foo', 'bar', 'baz', new \SMWDIBlob( 'bar' ) );

		$instances[] = new \SMWExpLiteral( 'foo' );
		$instances[] = new \SMWExpLiteral( 'foo', '' );
		$instances[] = new \SMWExpLiteral( 'foo', 'bar' );
		$instances[] = new \SMWExpLiteral( 'foo', '', null );
		$instances[] = new \SMWExpLiteral( 'foo', '', new \SMWDIBlob( 'bar' ) );
		$instances[] = new \SMWExpLiteral( 'foo', 'baz', new \SMWDIBlob( 'bar' ) );

		return $this->arrayWrap( $instances );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetDataItem( \SMWExpElement $element ) {
		$this->assertTypeOrValue( 'SMWDataItem', $element->getDataItem(), null );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetLexicalForm( \SMWExpElement $element ) {
		if ( method_exists( $element, 'getLexicalForm' ) ) {
			$this->assertType( 'string', $element->getLexicalForm() );
		}
		else {
			$this->assertTrue( true );
		}
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetDatatype( \SMWExpElement $element ) {
		if ( method_exists( $element, 'getDatatype' ) ) {
			$this->assertType( 'string', $element->getDatatype() );
		}
		else {
			$this->assertTrue( true );
		}
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetLocalName( \SMWExpElement $element ) {
		if ( method_exists( $element, 'getLocalName' ) ) {
			$this->assertType( 'string', $element->getLocalName() );
		}
		else {
			$this->assertTrue( true );
		}
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testgetNamespace( \SMWExpElement $element ) {
		if ( method_exists( $element, 'getNamespace' ) ) {
			$this->assertType( 'string', $element->getNamespace() );
		}
		else {
			$this->assertTrue( true );
		}
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetNamespaceId( \SMWExpElement $element ) {
		if ( method_exists( $element, 'getNamespaceId' ) ) {
			$this->assertType( 'string', $element->getNamespaceId() );
		}
		else {
			$this->assertTrue( true );
		}
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetQName( \SMWExpElement $element ) {
		if ( method_exists( $element, 'getQName' ) ) {
			$this->assertType( 'string', $element->getQName() );
		}
		else {
			$this->assertTrue( true );
		}
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetUri( \SMWExpElement $element ) {
		if ( method_exists( $element, 'getUri' ) ) {
			$this->assertType( 'string', $element->getUri() );
		}
		else {
			$this->assertTrue( true );
		}
	}
	/**
	 * @dataProvider instanceProvider
	 */
	public function testIsBlankNode( \SMWExpElement $element ) {
		if ( method_exists( $element, 'isBlankNode' ) ) {
			$this->assertType( 'boolean', $element->isBlankNode() );
		}
		else {
			$this->assertTrue( true );
		}
	}


}