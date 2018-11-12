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
 * Basic cleaner for poem text.
 */
class BasicTextCleaner implements TextCleaner {

	private const CLEANINGS = [
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
	 * @inheritDoc
	 */
	public function getCleanText( string $text ) : string {
		// TODO: is preg_replace() with arrays faster?
		foreach ( self::CLEANINGS as $from => $to ) {
			$text = preg_replace( $from, $to, $text );
		}

		return $text;
	}

}
