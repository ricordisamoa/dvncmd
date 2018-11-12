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
 * Provider of Nuvola-style flag images.
 */
class NuvolaFlagProvider implements FlagProvider {

	private const FLAG_FORMAT = 'Nuvola %s flag.svg';

	private const FLAG_LANGS = [
		'ca' => 'Catalonia',
		'cs' => 'Czech',
		'de' => 'German',
		'el' => 'Greek',
		'en' => 'English language',
		'es' => 'Spain',
		'et' => 'Estonian',
		'fi' => 'Finnish',
		'fr' => 'France',
		'it' => 'Italy',
		'la' => 'Vatican', // only Vatican?
		'no' => 'Norwegian',
		'pl' => 'Polish',
		'pt' => 'Portuguese',
		'ro' => 'Romanian',
		'ru' => 'Russian',
		'sl' => 'Slovenian',
		'sv' => 'Swedish',
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
