<?php
/**
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
 */

/**
 * Chinese-specific code.
 *
 * This handles both Traditional and Simplified Chinese.
 * Right now, we distinguish `zh_hans`, `zh_hant`, `zh_cn`, `zh_tw`, `zh_sg`,
 * and `zh_hk`.
 *
 * @ingroup Languages
 */
class LanguageZh extends LanguageZh_hans {
	/**
	 * this should give much better diff info
	 *
	 * @param string $text
	 * @return string
	 */
	public function segmentForDiff( $text ) {
		return preg_replace( '/[\xc0-\xff][\x80-\xbf]*/', ' $0', $text );
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function unsegmentForDiff( $text ) {
		return preg_replace( '/ ([\xc0-\xff][\x80-\xbf]*)/', '$1', $text );
	}

	/**
	 * @inheritDoc
	 */
	protected function getSerchIndexVariant() {
		return 'zh-hans';
	}

	/**
	 * @param string[] $termsArray
	 * @return string[]
	 */
	public function convertForSearchResult( $termsArray ) {
		$terms = implode( '|', $termsArray );
		$terms = self::convertDoubleWidth( $terms );
		$terms = implode( '|', $this->getConverterInternal()->autoConvertToAllVariants( $terms ) );
		$ret = array_unique( explode( '|', $terms ) );
		return $ret;
	}
}
