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
 * @license   https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero GPL
 */

namespace DivineComedy;

/**
 * Provider of language links.
 */
class LanguageLinksProvider {

	/**
	 * @var ApiClient MediaWiki API wrapper
	 */
	private $api;

	/**
	 * @param ApiClient $api MediaWiki API wrapper
	 */
	public function __construct( ApiClient $api ) {
		$this->api = $api;
	}

	/**
	 * Get the language links for a page.
	 *
	 * @param string $title The title of the page to get the language links for
	 * @return array Keys are language codes, values are page titles
	 */
	public function getLanguageLinks( string $title ) : array {
		$res = $this->api->get( [
			'action' => 'query',
			'formatversion' => 2,
			'prop' => 'langlinks',
			'lllimit' => 'max',
			'titles' => $title
		] );
		$res = $res['query']['pages'][0]['langlinks'];
		$langlinks = [];
		foreach ( $res as $ll ) {
			$langlinks[$ll['lang']] = $ll['title'];
		}
		return $langlinks;
	}

}
