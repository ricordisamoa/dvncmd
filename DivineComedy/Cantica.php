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
 * @copyright 2012-2018 Ricordisamoa
 * @license   AGPL-3.0-or-later
 */

namespace DivineComedy;

/**
 * A wrapper class to obtain a specific Canto instance
 */
class Cantica {

	public static $names = [
		'i' => 'Inferno',
		'p' => 'Purgatorio',
		'd' => 'Paradiso'
	];

	/**
	 * @var string The name of the cantica
	 */
	private $name;

	/**
	 * @var string The language code of the cantica
	 */
	private $lang;

	/**
	 * @param string $name The name of the cantica
	 * @param string $lang The language code of the cantica
	 */
	public function __construct( string $name, string $lang ) {
		$this->name = $name;
		$this->lang = $lang;
	}

	/**
	 * Get the name of the cantica.
	 *
	 * @return string The name of the cantica
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * Get the number of cantos in the cantica.
	 *
	 * @return int The number of cantos in the cantica
	 */
	public function numberOfCantos() : int {
		return in_array( $this->name, [ 'Purgatorio', 'Paradiso' ] ) ? 33 : 34;
	}

	/**
	 * Get an instance of Canto.
	 *
	 * @param int $num The number of the canto
	 * @return Canto The instance of Canto
	 */
	public function getCanto( int $num ) : Canto {
		return new Canto( $this->name, $num, $this->lang );
	}

}
