<?php
/**
 * Implements Special:Wantedpages
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup SpecialPage
 */

namespace MediaWiki\Specials;

use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\MainConfigNames;
use WantedQueryPage;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * A special page that lists most linked pages that does not exist
 *
 * @ingroup SpecialPage
 */
class SpecialWantedPages extends WantedQueryPage {

	/**
	 * @param IConnectionProvider $dbProvider
	 * @param LinkBatchFactory $linkBatchFactory
	 */
	public function __construct(
		IConnectionProvider $dbProvider,
		LinkBatchFactory $linkBatchFactory
	) {
		parent::__construct( 'Wantedpages' );
		$this->setDatabaseProvider( $dbProvider );
		$this->setLinkBatchFactory( $linkBatchFactory );
	}

	public function isIncludable() {
		return true;
	}

	public function execute( $par ) {
		$inc = $this->including();

		if ( $inc ) {
			$this->limit = (int)$par;
			$this->offset = 0;
		}
		$this->setListoutput( $inc );
		$this->shownavigation = !$inc;
		parent::execute( $par );
	}

	public function getQueryInfo() {
		$dbr = $this->getDatabaseProvider()->getReplicaDatabase();
		$count = $this->getConfig()->get( MainConfigNames::WantedPagesThreshold ) - 1;
		$query = [
			'tables' => [
				'pagelinks',
				'pg1' => 'page',
				'pg2' => 'page'
			],
			'fields' => [
				'namespace' => 'pl_namespace',
				'title' => 'pl_title',
				'value' => 'COUNT(*)'
			],
			'conds' => [
				'pg1.page_namespace IS NULL',
				'pl_namespace NOT IN (' . $dbr->makeList( [ NS_USER, NS_USER_TALK ] ) . ')',
				'pg2.page_namespace != ' . $dbr->addQuotes( NS_MEDIAWIKI ),
			],
			'options' => [
				'HAVING' => [
					'COUNT(*) > ' . $dbr->addQuotes( $count ),
					'COUNT(*) > SUM(pg2.page_is_redirect)'
				],
				'GROUP BY' => [ 'pl_namespace', 'pl_title' ]
			],
			'join_conds' => [
				'pg1' => [
					'LEFT JOIN', [
						'pg1.page_namespace = pl_namespace',
						'pg1.page_title = pl_title'
					]
				],
				'pg2' => [ 'LEFT JOIN', 'pg2.page_id = pl_from' ]
			]
		];
		// Replacement for the WantedPages::getSQL hook
		$this->getHookRunner()->onWantedPages__getQueryInfo( $this, $query );

		return $query;
	}

	protected function getGroupName() {
		return 'maintenance';
	}
}

/**
 * Retain the old class name for backwards compatibility.
 * @deprecated since 1.40
 */
class_alias( SpecialWantedPages::class, 'WantedPagesPage' );
