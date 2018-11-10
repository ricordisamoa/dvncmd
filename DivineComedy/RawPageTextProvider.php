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
 * Provider of raw page text.
 */
class RawPageTextProvider {

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
	 * Get the raw text of a page.
	 *
	 * @param string $title The title of the page to get the text for
	 * @return string|null The raw text of the page, null if not found
	 */
	public function getRawPageText( string $title ) : ?string {
		$query = $this->api->get( [
			'action'  => 'query',
			'titles'  => $title,
			'prop'    => 'revisions',
			'rvprop'  => 'content',
			'rvlimit' => 1
		] );
		$query = $query['query'];
		if ( array_key_exists( 'pages', $query ) ) {
			foreach ( $query['pages'] as $pageid => $page ) {
				if ( array_key_exists( 'revisions', $page ) ) {
					return $page['revisions'][0]['*'];
				} else {
					return null;
				}
			}
		}
		return null;
	}

}
