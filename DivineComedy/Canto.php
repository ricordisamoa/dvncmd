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
 * Represents a Canto (original or translated) on Wikisource
 *
 * Should be instantiated by Cantica only.
 */
class Canto {

	const WIKISOURCE_API = 'http://%s.wikisource.org/w/api.php';

	const ORIGINAL_LANG = 'it';

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
	 * Convert an integer between 1 and 39 (inclusive) into a Roman numeral.
	 *
	 * @param int $num The number to convert
	 * @return string The romanized number
	 */
	private static function romanize( int $num ) : string {
		return str_repeat( 'X', intdiv( $num, 10 ) ) .
			[ '', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX' ][$num % 10];
	}

	/**
	 * @param string $cantica The name of the cantica
	 * @param int $num The number of the canto
	 * @param string $lang The language code of the canto
	 */
	public function __construct( string $cantica, int $num, string $lang ) {
		$this->cantica = $cantica;
		$this->num = $num;
		$this->lang = $lang;

		$this->orig = sprintf( 'Divina Commedia/%s/Canto %s', $this->cantica, self::romanize( $num ) );
		$this->api = sprintf( self::WIKISOURCE_API, $this->lang );

		if ( $this->lang === self::ORIGINAL_LANG ) {
			$this->title = $this->orig;
		} else {
			$lls = $this->getLanglinks();
			$this->title = $lls[$this->lang];
		}
		$this->url = sprintf(
			'http://%s.wikisource.org/wiki/%s',
			$this->lang,
			implode(
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
		$api = new ApiClient( sprintf( self::WIKISOURCE_API, self::ORIGINAL_LANG ) );
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
		$lls[self::ORIGINAL_LANG] = $this->orig;
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
	private function getContent() : ?string {
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
	 * @param int|null $begin The starting line
	 * @param int|null $end The ending line
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
