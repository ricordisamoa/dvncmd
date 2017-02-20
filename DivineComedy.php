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

// {{{ constants
define( 'WS_API', 'http://%s.wikisource.org/w/api.php' );
define( 'WS_PATH', 'http://%s.wikisource.org/wiki/%s' );
define( 'WS_ORIG_LANG', 'it' );
define( 'WS_ORIG_API', sprintf( WS_API, WS_ORIG_LANG ) );
define( 'WS_ORIG_PAGE_PATH', 'Divina Commedia/%s/Canto %s' );

define( 'COMMONS_API', 'http://commons.wikimedia.org/w/api.php' );
define( 'COMMONS_CAT_PATH', 'Category:%s Canto %02d' );

define( 'IMG_WIDTH', 1600 );
define( 'IMG_HEIGHT', 160 );
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

function getApi( string $api, array $data ) {
	$data['format'] = 'json';
	$params = http_build_query( $data );
	$res = file_get_contents( $api . '?' . $params );
	return json_decode( $res, true );
}

/**
 * Base class for all DivineComedy exceptions.
 */
class DivineComedyException extends \Exception {
}

/**
 * A simple class representing a Wikisource page in original language (Italian)
 *
 * It is supposed to be extended by Cantica and Canto.
 */
abstract class Orig {

	/**
	 * @var string The title of the page in original language
	 */
	protected $orig;

	/**
	 * @var string The language code of the page
	 */
	protected $lang = 'it';

	/**
	 * @param string $orig The title of the page in original language
	 */
	public function __construct( string $orig ) {
		$this->orig = $orig;
	}

	private static $flag_formats = [ 'Flag of %s.svg', 'Nuvola %s flag.svg' ];
	private static $flag_langs = [
		'ca' => [ 'Catalonia',          'Catalonia' ],
		'cs' => [ 'the Czech Republic', 'Czech' ],
		'de' => [ 'Germany',            'German' ],
		'el' => [ 'Greece',             'Greek' ],
		'en' => [ 'the United Kingdom', 'English language' ],
		'es' => [ 'Spain',              'Spain' ],
		'et' => [ 'Estonia',            'Estonian' ],
		'fi' => [ 'Finland',            'Finnish' ],
		'fr' => [ 'France',             'France' ],
		'it' => [ 'Italy',              'Italy' ],
		'la' => [ 'the Vatican City',   'Vatican' ], // only Vatican?
		'no' => [ 'Norway',             'Norwegian' ],
		'pl' => [ 'Poland',             'Polish' ],
		'pt' => [ 'Portugal',           'Portuguese' ],
		'ro' => [ 'Romania',            'Romanian' ],
		'ru' => [ 'Russia',             'Russian' ],
		'sl' => [ 'Slovenia',           'Slovenian' ],
		'sv' => [ 'Sweden',             'Swedish' ],
	];

	/**
	 * Get the appropriate flag for a language.
	 *
	 * @param string $lang The language code
	 * @param int $index The index of the flag in $flag_formats
	 * @return string|null The title of the flag image, null if not found
	 */
	public static function getFlag( string $lang, int $index = 1 ) {
		if ( isset( self::$flag_langs[$lang] ) ) {
			$flag = sprintf( self::$flag_formats[$index], self::$flag_langs[$lang][$index] );
			return str_replace( ' ', '_', $flag );
		}
	}

	/**
	 * Get the language links.
	 *
	 * @return array Keys are language codes, values are page titles
	 */
	public function getLanglinks() : array {
		$res = getApi(
			WS_ORIG_API,
			[
				'action' => 'query',
				'formatversion' => 2,
				'prop' => 'langlinks',
				'lllimit' => 'max',
				'titles' => $this->orig
			]
		);
		$res = $res['query']['pages'][0]['langlinks'];
		$langlinks = [];
		foreach ( $res as $ll ) {
			$langlinks[$ll['lang']] = $ll['title'];
		}
		return $langlinks;
	}

	/**
	 * Get the langlink flags.
	 *
	 * @param string $query The raw query
	 * @return string HTML
	 */
	public function getLanglinkFlags( string $query ) : string {
		$lls = $this->getLanglinks();
		$lls[WS_ORIG_LANG] = $this->orig;
		unset( $lls['fr'] ); // the French version is in prose
		unset( $lls[$this->lang] ); // do not show links to current language
		ksort( $lls ); // sort by language code

		$ret = '<div id="langlinks-right">';
		foreach ( array_keys( $lls ) as $i => $llang ) {
			$ltitle = $lls[$llang];
			$ret .= '<a target="_self" href="' .
				( $llang === WS_ORIG_LANG ? '' : ( '/' . $llang ) ) .
				"/$query\" title=\"$ltitle\">";
			$flag = self::getFlag( $llang );
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

/**
 * A wrapper class to obtain a specific Canto instance
 */
class Cantica extends Orig {
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
		$class = Canto::getClassName( $this->lang );
		return new $class( $this->name, $num, $this->lang );
	}

}

/**
 * Represents a Canto (original or translated) on Wikisource
 *
 * Should be instantiated by Cantica only.
 */
class Canto extends Orig {

	/**
	 * @var string The name of the cantica
	 */
	private $cantica;

	/**
	 * @var int The number of the canto
	 */
	private $num;

	/**
	 * @var string The title of the Commons category of the canto
	 */
	private $commonsCat;

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

		$this->commonsCat = sprintf( COMMONS_CAT_PATH, $this->cantica, $this->num );

		parent::__construct( sprintf( WS_ORIG_PAGE_PATH, $this->cantica, romanize( $num ) ) );
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
	 * Get the name of the class suitable for representing cantos by language.
	 *
	 * @param string $lang The language code
	 * @return string The class name
	 */
	public static function getClassName( string $lang ) : string {
		switch ( $lang ) {
			case 'la':
				return LatinCanto::class;
			case 'ru':
				return RussianCanto::class;
			default:
				return Canto::class;
		}
	}

	protected static $cleanings = [
		// get only text in "<poem>" tags
		'/(^[\s\S]*<poem>[\s\n\r]*|[\s\n\r]*<\/poem>[\s\S]*$)/i' => '',

		// remove images (TODO: expect any possible ns-6 alias)
		'/\[\[\:?([Ff]ile|[Ii]mat?ge|[Ii]mmagine)\:.*?(\[\[.+?\]\].*?)*\]\]/' => '',

		// other languages
		'/^[\s\S]*<div class="verse"><pre>\s+/i' => '',
		'/\s+<\/pre><\/div>[\s\S]*$/i' => '',

		// strip <ref> tags
		'/<ref[\s\w]*(\/|>[^<>]*<\/ref)>/i' => '',

		// remove indentations and spaces at line beginning
		'/^[:\s]*/m' => '',

		// remove unprintable templates
		'/\{\{([Oo]tsikko|[Ee]ncabezado|[Tt]itulus2)\n*\|[^\|\{\}]+(\|([^\|\{\}]+))*\}\}/' => '',
		'/\{\{([\w\§]+)\n*\|[^\|\{\}]+\}\}/' => '',

		// replace some templates with printable parts
		'/\{\{([\w\§]+)\n*\|[^\|\{\}]+\|([^\|\{\}]+)\}\}/' => '$2',

		// remove initial and final spaces
		'/(^[\s\n\r]+|[\s\n\r]+$)/' => '',

		// remove superfluous line-breaks
		'/\s*(<br\s?\/?>\s*)*\n+/' => "\n",
	];

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
	 * Get the raw content of the Wikisource page for the current Canto.
	 *
	 * @return string
	 */
	protected function getContent() {
		$query = getApi(
			$this->api,
			[
				'action'  => 'query',
				'titles'  => $this->title,
				'prop'    => 'revisions',
				'rvprop'  => 'content',
				'rvlimit' => 1
			]
		);
		$query = $query['query'];
		if ( array_key_exists( 'pages', $query ) ) {
			foreach ( $query['pages'] as $pageid => $page ) {
				if ( array_key_exists( 'revisions', $page ) ) {
					return $page['revisions'][0]['*'];
				} else {
					return;
				}
			}
		}
	}

	/**
	 * Get the content of a Canto, after stripping all but lines of poetry.
	 *
	 * @param string $content The content to clean
	 * @return string
	 */
	protected static function getCleanContentStatic( $content ) {
		// apply standard replacements
		// TODO: is preg_replace() with arrays faster?
		foreach ( self::$cleanings as $from => $to ) {
			$content = preg_replace( $from, $to, $content );
		}

		return $content;
	}

	/**
	 * Get the content of the current Canto, after stripping all but lines of poetry.
	 *
	 * @return string
	 */
	protected function getCleanContent() {
		return static::getCleanContentStatic( $this->getContent() );
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
		if ( $begin !== null and $end !== null ) {
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

	/**
	 * Returns an array of images from Wikimedia Commons about the current Canto.
	 *
	 * @return array
	 */
	public function getImages() : array {
		$images = getApi(
			COMMONS_API,
			[
				'action'      => 'query',
				'prop'        => 'imageinfo',
				'iiprop'      => 'url',
				'iiurlwidth'  => IMG_WIDTH,
				'iiurlheight' => IMG_HEIGHT,
				'generator'   => 'categorymembers',
				'gcmtitle'    => $this->commonsCat,
				'gcmtype'     => 'file'
			]
		);
		$res = [];
		if ( array_key_exists( 'query', $images ) ) {
			$images = $images['query'];
			if ( array_key_exists( 'pages', $images ) ) {
				foreach ( $images['pages'] as $pageid => $page ) {
					$k = $page['imageinfo'][0];
					$k['title'] = $page['title'];
					$res[] = $k;
				}
			}
		}
		return $res;
	}

}

/**
 * Canto subclass for Latin Wikisource
 */
class LatinCanto extends Canto {

	public function __construct( string $cantica, int $num, string $lang = WS_ORIG_LANG ) {
		parent::__construct( $cantica, $num, $lang );
		static::$cleanings += [
			// remove line numbers and italic marks
			'/^(\d+[\.\,]?\s+)?\'\'|\'\'$/m' => '',
		];
	}

}

/**
 * Canto subclass for Russian Wikisource
 */
class RussianCanto extends Canto {

	protected static function getCleanContentStatic( $content ) {
		$lines = explode( "\n", $content );
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
		return parent::getCleanContentStatic( implode( "\n", $newLines ) );
	}

}
