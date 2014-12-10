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

require_once 'DivineComedy.php';

$params = $_GET;
define( 'VIEW_MODE', isset( $params['q'] ) );

if ( isset( $params['lang'] ) && $params['lang'] !== '' ) {
	$lang = $params['lang'];
} else {
	$lang = WS_ORIG_LANG;
}

if ( isset( $lang ) && isset( $titles[$lang] ) ) {
	$heading = $titles[$lang];
} else {
	$heading = $titles['en'];
}

function getData( $params, $languages, $lang ) {
	$query = $params['q'];
	$parts = explode( ',', $query );

	$cantica = substr( $parts[0], 0, 1 );
	if ( isset( Cantica::$names[$cantica] ) ) {
		$cantica = new Cantica( Cantica::$names[$cantica], $lang );
	} else {
		die( 'No cantica, no party!' );
	}

	$canto = substr( $parts[0], 1 );
	if ( !is_numeric( $canto ) ) {
		die( 'Invalid canto!' );
	}
	$canto = $cantica->getCanto( intval( $canto ) );

	$versi = explode( '-', $parts[1] );
	if ( count( $versi ) < 1 || count( $versi ) > 2 ) {
		die( 'Error: must specify 1 or 2 line numbers!' );
	}
	if ( count( $versi ) == 1 ) {
		$versi[1] = $versi[0];
	}
	if ( !is_numeric( $versi[0] ) || !is_numeric( $versi[1] ) ) {
		die( 'The line numbers must be integer!' );
	}

	$versi = array_map( 'intval', $versi );

	if ( $versi[1] - $versi[0] > 11 ) {
		die( 'Error: exceeded maximum absolute number of lines (12)!' );
	}

	echo $canto->getLanglinkFlags();

	return [$cantica, $canto, $versi];
}

function getBody( $cantica, $canto, $versi ) {

	$res = '';
	$lines = $canto->getLines( $versi[0], $versi[1] );

	$res .= "<section><h2>{$cantica->name}, canto {$canto->num}, vers" .
		( count( $lines ) == 1 ? 'o ' . $versi[0] : 'i ' . implode( $versi, '-' ) ) .
		'</h2><blockquote>' . implode( $lines, '<br>' ) .
		"</blockquote><small>Text from <a href=\"{$canto->url}\">Wikisource</a></small></section>";

	foreach ( $canto->getImages() as $i => $img ) {
		$res .= "<a href=\"{$img['descriptionurl']}\"><img alt=\"{$img['title']}\"" .
			" src=\"{$img['thumburl']}\"></a>";
	}

	return $res;

}
