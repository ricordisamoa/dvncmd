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
 * @license   https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero GPL
 */

namespace DivineComedy;

/**
 * Cleaner for Russian Wikisource poem text.
 */
class RussianTextCleaner extends BasicTextCleaner {

	/**
	 * @inheritDoc
	 */
	public function getCleanText( string $text ) : string {
		$lines = explode( "\n", $text );
		$newLines = [];
		$inPoem = false;
		foreach ( $lines as $line ) {
			if ( strpos( $line, '{{poem-on' ) !== false ) {
				$inPoem = true;
				continue;
			}
			if ( strpos( $line, '{{poem-off' ) !== false ) {
				break;
			}
			if ( $inPoem ) {
				$newLines[] = preg_replace( '/<\/span>$/', '', $line );
			}
		}
		return parent::getCleanText( implode( "\n", $newLines ) );
	}

}
