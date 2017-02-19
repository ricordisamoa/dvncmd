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
 * The license file can be found at COPYING.txt (in this directory).
 *
 * @author    Ricordisamoa
 * @copyright 2012-2017 Ricordisamoa
 * @license   https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero GPL
 */

namespace DivineComedy;

class DivineComedyView {

	private static $titles = [
		'ca' => 'La Divina Comèdia',
		'cs' => 'Božská komedie',
		'el' => 'Θεία Κωμωδία',
		'en' => 'Divine Comedy',
		'es' => 'La Divina Comedia',
		'fi' => 'Jumalaisesta näytelmästä',
		'fr' => 'La Divine Comédie',
		'it' => 'Divina Commedia',
		'la' => 'Divina Comoedia',
		'pl' => 'Boska Komedia',
		'pt' => 'A Divina Comédia',
		'ro' => 'Divina Comedie',
		'ru' => 'Божественная комедия',
		'sl' => 'Božanska komedija'
	];

	/**
	 * @var string|null The raw query
	 */
	private $query;

	/**
	 * @var string The language code
	 */
	private $lang;

	/**
	 * @var Cantica|null The cantica
	 */
	private $cantica;

	/**
	 * @var Canto|null The canto
	 */
	private $canto;

	/**
	 * @var int[]|null The starting and ending line numbers
	 */
	private $versi;

	/**
	 * @param array $params The query parameters
	 */
	public function __construct( array $params ) {
		if ( isset( $params['q'] ) ) {
			$this->query = $params['q'];
		}

		if ( isset( $params['lang'] ) && $params['lang'] !== '' ) {
			$this->lang = $params['lang'];
		} else {
			$this->lang = WS_ORIG_LANG;
		}
	}

	/**
	 * Get whether to display actual lines.
	 *
	 * @return bool
	 */
	public function isViewMode() : bool {
		return $this->query !== null;
	}

	/**
	 * Get the unescaped, possibly localized title.
	 *
	 * @return string
	 */
	public function getHeading() : string {
		return self::$titles[$this->lang] ?? self::$titles['en'];
	}

	/**
	 * Get an instance of Cantica from a one-character identifier.
	 *
	 * @param string $letter The identifier
	 * @return Cantica The Cantica instance
	 */
	private function parseCantica( string $letter ) : Cantica {
		if ( !isset( Cantica::$names[$letter] ) ) {
			throw new DivineComedyException( 'no valid cantica provided' );
		}

		return new Cantica( Cantica::$names[$letter], $this->lang );
	}

	/**
	 * Get an instance of Canto from a numeric identifier.
	 *
	 * @param string $num The identifier
	 * @return Canto The Canto instance
	 */
	private function parseCanto( string $num ) : Canto {
		if ( !is_numeric( $num ) ) {
			throw new DivineComedyException( 'invalid canto' );
		}

		return $this->cantica->getCanto( intval( $num ) );
	}

	/**
	 * Get line numbers from a string.
	 *
	 * @param string $nums Either a number, or two dash-separated numbers
	 * @return int[] The two parsed numbers
	 */
	private function parseLineNumbers( string $nums ) : array {
		$versi = explode( '-', $nums );
		if ( count( $versi ) < 1 || count( $versi ) > 2 ) {
			throw new DivineComedyException( '1 or 2 line numbers must be specified' );
		}
		if ( count( $versi ) === 1 ) {
			$versi[1] = $versi[0];
		}
		if ( !is_numeric( $versi[0] ) || !is_numeric( $versi[1] ) ) {
			throw new DivineComedyException( 'line numbers must be integers' );
		}

		$versi = array_map( 'intval', $versi );
		if ( $versi[1] - $versi[0] > 11 ) {
			throw new DivineComedyException( 'exceeded maximum absolute number of lines (12)' );
		}

		return $versi;
	}

	/**
	 * Parse the query.
	 */
	public function parseQuery() {
		$parts = explode( ',', $this->query );

		$this->cantica = $this->parseCantica( substr( $parts[0], 0, 1 ) );

		$this->canto = $this->parseCanto( substr( $parts[0], 1 ) );

		$this->versi = $this->parseLineNumbers( $parts[1] );
	}

	/**
	 * Get the body.
	 *
	 * @return string HTML
	 */
	public function getBody() : string {
		$res = '';
		$lines = $this->canto->getLines( $this->versi[0], $this->versi[1] );

		$res .= "<section><h2>{$this->cantica->getName()}, canto {$this->canto->getNum()}, vers" .
			( count( $lines ) == 1 ? 'o ' . $this->versi[0] : 'i ' . implode( $this->versi, '-' ) ) .
			'</h2><blockquote>' . implode( array_map( 'htmlspecialchars', $lines ), '<br>' ) .
			"</blockquote><small>Text from <a href=\"{$this->canto->getUrl()}\">Wikisource</a>" .
			'</small></section>';

		foreach ( $this->canto->getImages() as $i => $img ) {
			$res .= "<a href=\"{$img['descriptionurl']}\"><img alt=\"{$img['title']}\"" .
				" src=\"{$img['thumburl']}\"></a>";
		}

		return $res;
	}

	/**
	 * Get the langlink flags.
	 *
	 * @return string HTML
	 */
	public function getLanglinkFlags() : string {
		return $this->canto->getLanglinkFlags( $this->query );
	}

}