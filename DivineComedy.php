<?php

/**
 * Divine Comedy link shortener - dvncmd.tk
 *
 * PHP version 5
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
 * @copyright 2012-2014 Ricordisamoa
 * @license   https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero GPL
 */

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

$titles = [
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
function romanize( $num ) {
	$digits = str_split( strval( $num ) );
	$key = ['', 'C', 'CC', 'CCC', 'CD', 'D', 'DC', 'DCC', 'DCCC', 'CM',
		'', 'X', 'XX', 'XXX', 'XL', 'L', 'LX', 'LXX', 'LXXX', 'XC',
		'', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX'];
	$roman = '';
	$i = 3;
	while ( $i-- ) {
		$f = intval( array_pop( $digits ) ) + ( $i * 10 );
		$roman = ( array_key_exists( $f, $key ) ? $key[$f] : '' ) . $roman;
	}
	return implode( array_fill( 0, intval( implode( $digits, '' ) ) + 1, '' ), 'M' ) . $roman;
}

function getApi( $api, $data ) {
	$data['format'] = 'json';
	$params = http_build_query( $data );
	$res = file_get_contents( $api . '?' . $params );
	return json_decode( $res, true );
}

function compareLanglinks( $l1, $l2 ) {
	return strcmp( $l1['lang'], $l2['lang'] );
}

$languages = [];
$languages_query = getApi(
	WS_ORIG_API,
	[
		'action' => 'query',
		'meta'   => 'siteinfo',
		'siprop' => 'languages'
	]
);
$languages_query = $languages_query['query']['languages'];
foreach ( $languages_query as $language ) {
	$languages[$language['code']] = $language['*'];
}


/**
 * A simple class representing a Wikisource page in original language (Italian)
 *
 * It is supposed to be extended by Cantica and Canto.
 */
abstract class Orig {

	public function __construct( $orig ) {
		$this->orig = $orig;
	}

	private static $flag_formats = ['Flag of %s.svg', 'Nuvola %s flag.svg'];
	private static $flag_langs = [
		'ca' => ['Catalonia',          'Catalonia'],
		'cs' => ['the Czech Republic', 'Czech'],
		'de' => ['Germany',            'German'],
		'el' => ['Greece',             'Greek'],
		'en' => ['the United Kingdom', 'English language'],
		'es' => ['Spain',              'Spain'],
		'et' => ['Estonia',            'Estonian'],
		'fi' => ['Finland',            'Finnish'],
		'fr' => ['France',             'France'],
		'it' => ['Italy',              'Italy'],
		'la' => ['the Vatican City',   'Vatican'], // only Vatican?
		'no' => ['Norway',             'Norwegian'],
		'pl' => ['Poland',             'Polish'],
		'pt' => ['Portugal',           'Portuguese'],
		'ro' => ['Romania',            'Romanian'],
		'ru' => ['Russia',             'Russian'],
		'sl' => ['Slovenia',           'Slovenian'],
		'sv' => ['Sweden',             'Swedish'],
	];

	/**
	 * Returns the appropriate flag for a language.
	 *
	 * @param string $lang the language code
	 * @param int $index the index of the flag in $forms
	 *
	 * @return string the title of the flag image
	 */
	public static function getFlag( $lang, $index = 1 ) {
		if ( isset( self::$flag_langs[$lang] ) ) {
			$flag = sprintf( self::$flag_formats[$index], self::$flag_langs[$lang][$index] );
			return str_replace( ' ', '_', $flag );
		}
	}

	public function getLanglinks() {
		$res = getApi(
			WS_ORIG_API,
			[
				'action'  => 'query',
				'prop'    => 'langlinks',
				'lllimit' => 'max',
				'titles'  => $this->orig
			]
		);
		$res = $res['query']['pages'];
		$res = $res[array_keys( $res )[0]]['langlinks'];
		$res[] = [
			'lang' => WS_ORIG_LANG,
			'*'    => $this->orig
		];
		$res = array_values( $res );       // re-index array
		usort( $res, 'compareLanglinks' ); // sort by language code
		return $res;
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

	public $name;
	public $lang;

	public function __construct( $name, $lang = WS_ORIG_LANG ) {
		$this->name = $name;
		$this->lang = $lang;
	}

	public function numberOfCantos() {
		return in_array( $this->name, ['Purgatorio', 'Paradiso'] ) ? 33 : 34;
	}

	public function getCanto( $num ) {
		return new Canto( $this->name, $num, $this->lang );
	}

}

/**
 * Represents a Canto (original or translated) on Wikisource
 *
 * Should be instantiated by Cantica only.
 */
class Canto extends Orig {

	public function __construct( $cantica, $num, $lang = WS_ORIG_LANG ) {
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
			foreach ( $lls as $i => $ll ) {
				if ( $ll['lang'] === $this->lang ) {
					$this->title = $ll['*'];
					break;
				}
			}
		}
		$this->url = sprintf(
			WS_PATH, $this->lang, implode(
				'/', array_map( 'rawurlencode', explode( '/', str_replace( ' ', '_', $this->title ) ) )
			)
		);
	}

	private $cleanings = [
		// get only text in "<poem>" tags
		'/(^[\s\S]*<poem>[\s\n\r]*|[\s\n\r]*<\/poem>[\s\S]*$)/i' => '',

		// remove images (TODO: expect any possible ns-6 alias)
		'/\[\[\:?([Ff]ile|[Ii]mat?ge|[Ii]mmagine)\:.*?(\[\[.+?\]\].*?)*\]\]/' => '',

		// other languages
		'/^[\s\S]*<div class="verse"><pre>\s+/i' => '',
		'/\s+<\/pre><\/div>[\s\S]*$/i' => '',

		// strip <ref> tags
		'/<ref[\s\w]*(\/|>[^<>]*<\/ref)>/i' => '',

		// remove indentations at line beginning
		'/^[:\d\s\']*/m' => '',

		// remove unprintable templates
		'/\{\{([Oo]tsikko|[Ee]ncabezado|[Tt]itulus2)\n*\|[^\|\{\}]+(\|([^\|\{\}]+))*\}\}/' => '',
		'/\{\{([\w\§]+)\n*\|[^\|\{\}]+\}\}/' => '',

		// replace some templates with printable parts
		'/\{\{([\w\§]+)\n*\|[^\|\{\}]+\|([^\|\{\}]+)\}\}/' => '$2',

		// remove initial and final spaces
		'/(^[\s\n\r]+|[\s\n\r]+$)/' => '',

		// remove superfluous line-breaks
		'/\s*(<br\s?\/?>\s*)*\n+/' => '\n',
	];

	/**
	 * Returns the raw content of the Wikisource page for the current Canto.
	 * Internal use only.
	 *
	 * @return string
	 */
	public function getContent() {
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
	 * Returns the content of the current Canto, after stripping all but lines of poetry.
	 * Internal use only.
	 *
	 * @return string
	 */
	public function getCleanContent() {
		$content = $this->getContent();

		// apply standard replacements
		foreach ( $this->cleanings as $from => $to ) {
			$content = preg_replace( $from, $to, $content );
		}

		// remove final italic marks from Latin text
		if ( $this->lang === 'la' ) {
			$content = preg_replace( '/\'+\n/', '\n', $content );
		}

		return $content;
	}

	/**
	 * Returns the cleaned-up content of the current Canto, in form of lines.
	 *
	 * @param int $begin the starting line
	 * @param int $end   the ending line
	 *
	 * @return array
	 */
	public function getLines( $begin = null, $end = null ) {
		// split the text into lines
		$content = $this->getCleanContent();
		$lines = explode( '\n', $content );
		if ( $begin !== null and $end !== null ) {
			// select desired lines only
			if ( $begin > $end ) {
				die( 'Error: $begin cannot be greater than $end' );
			}
			if ( $end > count( $lines ) ) {
				die( sprintf( 'Error: exceeded number of lines in this canto: %d', count( $content ) ) );
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
	public function getImages() {
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
