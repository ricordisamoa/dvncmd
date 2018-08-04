<?php

/**
 * Divine Comedy link shortener - dvncmd.tk
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The license file can be found at COPYING.txt (in the parent directory).
 *
 * @author    Ricordisamoa
 * @copyright 2012-2017 Ricordisamoa
 * @license   AGPL-3.0-or-later
 */

namespace DivineComedy;

/**
 * Cleaner for Latin Wikisource poem text.
 */
class LatinTextCleaner extends BasicTextCleaner {

	/**
	 * @inheritDoc
	 */
	public function getCleanText( string $text ) : string {
		$text = parent::getCleanText( $text );

		// remove line numbers and italic marks
		$text = preg_replace( '/^(\d+[\.\,]?\s+)?\'\'|\'\'$/m', '', $text );
		return $text;
	}

}
