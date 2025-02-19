<?php

use MediaWiki\Specials\SpecialBookSources;

class SpecialBooksourcesTest extends SpecialPageTestBase {
	public static function provideISBNs() {
		return [
			[ '978-0-300-14424-6', true ],
			[ '0-14-020652-3', true ],
			[ '020652-3', false ],
			[ '9781234567897', true ],
			[ '1-4133-0454-0', true ],
			[ '978-1413304541', true ],
			[ '0136091814', true ],
			[ '0136091812', false ],
			[ '9780136091813', true ],
			[ '9780136091817', false ],
			[ '123456789X', true ],

			// T69021
			[ '1413304541', false ],
			[ '141330454X', false ],
			[ '1413304540', true ],
			[ '14133X4540', false ],
			[ '97814133X4541', false ],
			[ '978035642615X', false ],
			[ '9781413304541', true ],
			[ '9780356426150', true ],
		];
	}

	/**
	 * @covers MediaWiki\Specials\SpecialBookSources::isValidISBN
	 * @dataProvider provideISBNs
	 */
	public function testIsValidISBN( $isbn, $isValid ) {
		$this->assertSame( $isValid, SpecialBookSources::isValidISBN( $isbn ) );
	}

	protected function newSpecialPage() {
		$services = $this->getServiceContainer();
		return new SpecialBookSources(
			$services->getRevisionLookup(),
			$services->getContentLanguage()
		);
	}

	/**
	 * @covers MediaWiki\Specials\SpecialBookSources::execute
	 */
	public function testExecute() {
		[ $html, ] = $this->executeSpecialPage( 'Invalid', null, 'qqx' );
		$this->assertStringContainsString( '(booksources-invalid-isbn)', $html );
		[ $html, ] = $this->executeSpecialPage( '0-7475-3269-9', null, 'qqx' );
		$this->assertStringNotContainsString( '(booksources-invalid-isbn)', $html );
		$this->assertStringContainsString( '(booksources-text)', $html );
	}
}
