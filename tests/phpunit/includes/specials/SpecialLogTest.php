<?php
/**
 * @license GPL-2.0-or-later
 * @author Legoktm
 */

use MediaWiki\Request\FauxRequest;
use MediaWiki\Specials\SpecialLog;

/**
 * @covers \MediaWiki\Specials\SpecialLog
 */
class SpecialLogTest extends SpecialPageTestBase {

	/**
	 * Returns a new instance of the special page under test.
	 *
	 * @return SpecialPage
	 */
	protected function newSpecialPage() {
		$services = $this->getServiceContainer();
		return new SpecialLog(
			$services->getLinkBatchFactory(),
			$services->getDBLoadBalancerFactory(),
			$services->getActorNormalization(),
			$services->getUserIdentityLookup()
		);
	}

	/**
	 * Verify that no exception was thrown for an invalid date
	 * @see T201411
	 */
	public function testInvalidDate() {
		[ $html, ] = $this->executeSpecialPage(
			'',
			// There is no 13th month
			new FauxRequest( [ 'wpdate' => '2018-13-01' ] ),
			'qqx'
		);
		$this->assertStringContainsString( '(log-summary)', $html );
	}

}
