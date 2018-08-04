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
 * @license   AGPL-3.0-or-later
 */

namespace DivineComedy;

/**
 * Wrapper for MediaWiki's api.php.
 */
class ApiClient {

	/**
	 * @var string URL of the MediaWiki API endpoint
	 */
	private $apiUrl;

	/**
	 * @param string $apiUrl URL of the MediaWiki API endpoint
	 */
	public function __construct( string $apiUrl ) {
		$this->apiUrl = $apiUrl;
	}

	/**
	 * Get some data from the MediaWiki API.
	 *
	 * @param array $data Parameters for the MediaWiki API
	 * @return mixed Data returned by the MediaWiki API
	 */
	public function get( array $data ) {
		$data['format'] = 'json';
		$params = http_build_query( $data );
		$res = file_get_contents( $this->apiUrl . '?' . $params );
		return json_decode( $res, true );
	}

}
