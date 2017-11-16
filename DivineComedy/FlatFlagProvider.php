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
 * Provider of flat flag images.
 */
class FlatFlagProvider implements FlagProvider {

	/**
	 * @var string
	 */
	private static $flagFormat = 'Flag of %s.svg';

	/**
	 * @var array
	 */
	private static $flagLangs = [
		'ca' => 'Catalonia',
		'cs' => 'the Czech Republic',
		'de' => 'Germany',
		'el' => 'Greece',
		'en' => 'the United Kingdom',
		'es' => 'Spain',
		'et' => 'Estonia',
		'fi' => 'Finland',
		'fr' => 'France',
		'it' => 'Italy',
		'la' => 'the Vatican City', // only Vatican?
		'no' => 'Norway',
		'pl' => 'Poland',
		'pt' => 'Portugal',
		'ro' => 'Romania',
		'ru' => 'Russia',
		'sl' => 'Slovenia',
		'sv' => 'Sweden',
	];

	/**
	 * @inheritDoc
	 */
	public function getFlag( string $lang ) {
		if ( isset( self::$flagLangs[$lang] ) ) {
			$flag = sprintf( self::$flagFormat, self::$flagLangs[$lang] );
			return str_replace( ' ', '_', $flag );
		}
	}

}
