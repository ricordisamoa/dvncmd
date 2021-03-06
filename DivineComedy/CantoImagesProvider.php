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
 * Provider of Wikimedia Commons images by canto.
 */
class CantoImagesProvider {

	private const COMMONS_CAT_PATH = 'Category:%s Canto %02d';

	/**
	 * @var ApiClient Wikimedia Commons API wrapper
	 */
	private $api;

	/**
	 * @param ApiClient $api Wikimedia Commons API wrapper
	 */
	public function __construct( ApiClient $api ) {
		$this->api = $api;
	}

	/**
	 * Get images from Wikimedia Commons about a Canto.
	 *
	 * @param Canto $canto The canto to get images about
	 * @param int $width Image width
	 * @param int $height Image height
	 * @return array
	 */
	public function getImages( Canto $canto, int $width, int $height ) : array {
		$images = $this->api->get( [
			'action'      => 'query',
			'prop'        => 'imageinfo',
			'iiprop'      => 'url',
			'iiurlwidth'  => $width,
			'iiurlheight' => $height,
			'generator'   => 'categorymembers',
			'gcmtitle'    => sprintf( self::COMMONS_CAT_PATH, $canto->getCantica(), $canto->getNum() ),
			'gcmtype'     => 'file'
		] );
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
