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

use MessageFormatter;

class DivineComedyView {

	private const COMMONS_API = 'http://commons.wikimedia.org/w/api.php';

	private const IMG_WIDTH = 1600;
	private const IMG_HEIGHT = 160;

	private const DEFAULT_LANG = 'it';

	private const TITLES = [
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

	private const SECTION_TITLES = [
		'en' => '{0, plural, one {{1}, canto {2}, line {3}} other {{1}, canto {2}, lines {3}-{4}}}',
		'it' => '{0, plural, one {{1}, canto {2}, verso {3}} other {{1}, canto {2}, versi {3}-{4}}}'
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
	 * @var FlagProvider Provider of flag images
	 */
	private $flagProvider;

	/**
	 * @var CantoImagesProvider Provider of Wikimedia Commons images by canto
	 */
	private $cantoImagesProvider;

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
			$this->lang = self::DEFAULT_LANG;
		}

		$this->flagProvider = new NuvolaFlagProvider();

		$commonsApiClient = new ApiClient( self::COMMONS_API );
		$this->cantoImagesProvider = new CantoImagesProvider( $commonsApiClient );
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
		return self::TITLES[$this->lang] ?? self::TITLES['en'];
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
	public function parseQuery() : void {
		$parts = explode( ',', $this->query );

		$this->cantica = $this->parseCantica( substr( $parts[0], 0, 1 ) );

		$this->canto = $this->parseCanto( substr( $parts[0], 1 ) );

		$this->versi = $this->parseLineNumbers( $parts[1] );
	}

	/**
	 * Get the ICU message formatter for the section title.
	 *
	 * @return MessageFormatter ICU message formatter
	 */
	private function getSectionTitleMessageFormatter() : MessageFormatter {
		if ( isset( self::SECTION_TITLES[$this->lang] ) ) {
			$lang = $this->lang;
		} else {
			$lang = 'en';
		}
		return new MessageFormatter( $lang, self::SECTION_TITLES[$lang] );
	}

	/**
	 * Get the body.
	 *
	 * @return string HTML
	 */
	public function getBody() : string {
		$res = '';
		$lines = $this->canto->getLines( $this->versi[0], $this->versi[1] );

		$sectionTitle = htmlspecialchars( $this->getSectionTitleMessageFormatter()->format( [
			count( $lines ),
			$this->cantica->getName(),
			$this->canto->getNum(),
			$this->versi[0],
			$this->versi[1]
		] ) );
		$lines = implode( '<br>', array_map( 'htmlspecialchars', $lines ) );
		$cantoUrl = htmlspecialchars( $this->canto->getUrl() );

		$res .= "<section><h2>$sectionTitle</h2><blockquote>$lines</blockquote>" .
			"<small>Text from <a href=\"$cantoUrl\">Wikisource</a></small></section>";

		$imgs = $this->cantoImagesProvider->getImages( $this->canto, self::IMG_WIDTH, self::IMG_HEIGHT );
		foreach ( $imgs as $i => $img ) {
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
		$lls = $this->canto->getLanglinksForPresentation();
		$ret = '<div id="langlinks-right">';
		foreach ( array_keys( $lls ) as $i => $llang ) {
			$ltitle = $lls[$llang];
			$ret .= '<a target="_self" href="' .
				( $llang === self::DEFAULT_LANG ? '' : ( '/' . $llang ) ) .
				"/{$this->query}\" title=\"$ltitle\">";
			$flag = $this->flagProvider->getFlag( $llang );
			if ( $flag !== null ) {
				$ret .= '<img height="70" src="//commons.wikimedia.org/wiki/Special:Filepath/' .
					$flag . '" alt="' . $ltitle . '">';
			} else {
				$ret .= $ltitle;
			}
			$ret .= '</a>';
			if ( $i == intval( count( $lls ) / 2 ) ) {
				$ret .= '</div><div id="langlinks-left">';
			} elseif ( $i < count( $lls ) - 1 ) {
				$ret .= '<br>';
			}
		}
		$ret .= '</div>';
		return $ret;
	}

}
