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
 * Provider of flat flag images.
 */
class FlatFlagProvider implements FlagProvider {

	private const FLAG_FORMAT = 'Flag of %s.svg';

	private const FLAG_LANGS = [
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
	public function getFlag( string $lang ) : ?string {
		if ( isset( self::FLAG_LANGS[$lang] ) ) {
			$flag = sprintf( self::FLAG_FORMAT, self::FLAG_LANGS[$lang] );
			return str_replace( ' ', '_', $flag );
		}
		return null;
	}

}
