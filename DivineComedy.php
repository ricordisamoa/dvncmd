<?php
// phpcs:disable Generic.Files.OneObjectStructurePerFile,MediaWiki.Files.ClassMatchesFilename

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

// {{{ constants
define( 'WS_API', 'http://%s.wikisource.org/w/api.php' );
define( 'WS_PATH', 'http://%s.wikisource.org/wiki/%s' );
define( 'WS_ORIG_LANG', 'it' );
define( 'WS_ORIG_API', sprintf( WS_API, WS_ORIG_LANG ) );
define( 'WS_ORIG_PAGE_PATH', 'Divina Commedia/%s/Canto %s' );
// }}}

/**
 * Convert an integer into a Roman numeral
 *
 * @param int $num the number to convert
 *
 * @return string the romanized number
 *
 * @author    Steven Levithan
 * @author    Ricordisamoa
 * @copyright 2008 Steven Levithan, 2012 Ricordisamoa
 * @license   http://opensource.org/licenses/MIT  MIT License
 */
function romanize( int $num ) : string {
	$digits = str_split( strval( $num ) );
	$key = [
		'', 'C', 'CC', 'CCC', 'CD', 'D', 'DC', 'DCC', 'DCCC', 'CM',
		'', 'X', 'XX', 'XXX', 'XL', 'L', 'LX', 'LXX', 'LXXX', 'XC',
		'', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX'
	];
	$roman = '';
	$i = 3;
	while ( $i-- ) {
		$f = intval( array_pop( $digits ) ) + ( $i * 10 );
		$roman = ( array_key_exists( $f, $key ) ? $key[$f] : '' ) . $roman;
	}
	return implode( array_fill( 0, intval( implode( $digits, '' ) ) + 1, '' ), 'M' ) . $roman;
}

/**
 * Base class for all DivineComedy exceptions.
 */
class DivineComedyException extends \Exception {
}

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
	public function __construct( string $name, string $lang = WS_ORIG_LANG ) {
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

/**
 * Represents a Canto (original or translated) on Wikisource
 *
 * Should be instantiated by Cantica only.
 */
class Canto {

	/**
	 * @var string The name of the cantica
	 */
	private $cantica;

	/**
	 * @var int The number of the canto
	 */
	private $num;

	/**
	 * @var string The language code of the canto
	 */
	private $lang;

	/**
	 * @var string The title of the Wikisource page in original language
	 */
	private $orig;

	/**
	 * @var string The URL of the API endpoint of the localized Wikisource edition
	 */
	private $api;

	/**
	 * @var string The title of the Wikisource page in current language
	 */
	private $title;

	/**
	 * @var string The URL of the Wikisource page in current language
	 */
	private $url;

	/**
	 * @param string $cantica The name of the cantica
	 * @param int $num The number of the canto
	 * @param string $lang The language code of the canto
	 */
	public function __construct( string $cantica, int $num, string $lang = WS_ORIG_LANG ) {
		$this->cantica = $cantica;
		$this->num = $num;
		$this->lang = $lang;

		$this->orig = sprintf( WS_ORIG_PAGE_PATH, $this->cantica, romanize( $num ) );
		$this->api = sprintf( WS_API, $this->lang );

		if ( $this->lang === WS_ORIG_LANG ) {
			$this->title = $this->orig;
		} else {
			$lls = $this->getLanglinks();
			$this->title = $lls[$this->lang];
		}
		$this->url = sprintf(
			WS_PATH, $this->lang, implode(
				'/', array_map( 'rawurlencode', explode( '/', str_replace( ' ', '_', $this->title ) ) )
			)
		);
	}

	/**
	 * Get a text cleaner suitable for this canto.
	 *
	 * @return TextCleaner
	 */
	private function getTextCleaner() : TextCleaner {
		switch ( $this->lang ) {
			case 'la':
				return new LatinTextCleaner();
			case 'ru':
				return new RussianTextCleaner();
			default:
				return new BasicTextCleaner();
		}
	}

	/**
	 * Get the name of the cantica.
	 *
	 * @return string The name of the cantica
	 */
	public function getCantica() : string {
		return $this->cantica;
	}

	/**
	 * Get the number of the canto.
	 *
	 * @return int The number of the canto
	 */
	public function getNum() : int {
		return $this->num;
	}

	/**
	 * Get the URL of the Wikisource page in current language.
	 *
	 * @return string The URL of the Wikisource page in current language
	 */
	public function getUrl() : string {
		return $this->url;
	}

	/**
	 * Get the language links.
	 *
	 * @return array Keys are language codes, values are page titles
	 */
	public function getLanglinks() : array {
		$api = new ApiClient( WS_ORIG_API );
		$langlinksProvider = new LanguageLinksProvider( $api );
		return $langlinksProvider->getLanguageLinks( $this->orig );
	}

	/**
	 * Get the language links used for views.
	 *
	 * @return array Keys are language codes, values are page titles
	 */
	public function getLanglinksForPresentation() : array {
		$lls = $this->getLanglinks();
		$lls[WS_ORIG_LANG] = $this->orig;
		unset( $lls['fr'] ); // the French version is in prose
		unset( $lls[$this->lang] ); // do not show links to current language
		ksort( $lls ); // sort by language code
		return $lls;
	}

	/**
	 * Get the raw content of the Wikisource page for the current Canto.
	 *
	 * @return string|null
	 */
	private function getContent() {
		$api = new ApiClient( $this->api );
		$textProvider = new RawPageTextProvider( $api );
		return $textProvider->getRawPageText( $this->title );
	}

	/**
	 * Get the content of the current Canto, after stripping all but lines of poetry.
	 *
	 * @return string
	 */
	private function getCleanContent() {
		return $this->getTextCleaner()->getCleanText( $this->getContent() );
	}

	/**
	 * Get the cleaned-up content of the current Canto, in form of lines.
	 *
	 * @param int $begin The starting line
	 * @param int $end The ending line
	 * @return string[]
	 */
	public function getLines( int $begin = null, int $end = null ) : array {
		// split the text into lines
		$content = $this->getCleanContent();
		$lines = explode( "\n", $content );
		if ( $begin !== null && $end !== null ) {
			// select desired lines only
			if ( $begin > $end ) {
				throw new DivineComedyException( '$begin cannot be greater than $end' );
			}
			if ( $end > count( $lines ) ) {
				throw new DivineComedyException( sprintf(
					'exceeded number of lines in this canto: %d', count( $lines )
				) );
			}
			$lines = array_slice( $lines, $begin - 1, $end - $begin + 1 );
		}
		return $lines;
	}

}
