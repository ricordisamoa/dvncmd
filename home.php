<?php

namespace DivineComedy;

?><!DOCTYPE html>
<html>
<!--
Divine Comedy link shortener - dvncmd.tk

Copyright © 2012-2014 by Ricordisamoa

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

The license file can be found at COPYING.txt (in this directory).
-->
<head>
<meta charset="utf-8">
<title>Divine Comedy link shortener - dvncmd.tk</title>
<base target="_blank">
<link href="//fonts.googleapis.com/css?family=IM+Fell+DW+Pica:400,400italic" rel="stylesheet" type="text/css">
<link href="/common.css" rel="stylesheet" type="text/css">
<link href="//upload.wikimedia.org/wikipedia/commons/thumb/e/e5/Dante_icon.png/32px-Dante_icon.png" rel="shortcut icon" type="image/png">
<?php

require_once 'View.php';

if ( !VIEW_MODE ) {
	?><script src="/form.js" type="text/javascript"></script><?php
}
?>
</head>
<body>
<?php

if ( VIEW_MODE ) {
	list( $cantica, $canto, $versi ) = getData( $params, $languages, $lang );
}

?>
<header>
<h1><?php

echo $heading;

?></h1>
<h2>link shortener</h2>
</header>
<?php

if ( VIEW_MODE ) {
	echo getBody( $cantica, $canto, $versi );
} else {

?>

<section>
<h2>Type a Divine Comedy Reference</h2>
<form id="linkgen">
<table>
<tr>
<td>
<label for="input-cantica">Cantica:</label><br>
<select id="input-cantica"><?php

foreach ( array_keys( Cantica::$names ) as $i => $code ) {
	echo '<option value="' . $code . '"' . ( $i == 0 ? ' selected' : '' ) .
		'>' . Cantica::$names[$code] . '</option>';
}

?></select>
</td>
<td>
<label for="input-canto">Canto:</label><br>
<input type="number" id="input-canto" min="1" max="34" value="1">
</td>
<td>
<label for="input-lines">Lines:</label><br>
<input type="text" id="input-lines" placeholder="1-6" pattern="^\d+(\-\d+)?$">
</td>
<td>
<label for="input-lang">Language code:</label><br>
<input type="text" id="input-lang" value="it">
</td>
</tr>
</table>
</form>
</section>

<section>
<h2>Get a Universal Link</h2>
<a id="link-main" target="_blank">http://dvncmd.tk/<span id="link-lang"></span><span id="link-cantica"></span><span id="link-canto"></span>,<span id="link-lines"></span></a>
</section>
<?php

}

?>
<a href="https://github.com/ricordisamoa/dvncmd">
<img style="position: fixed; top: 0; right: 0; border: 0;" src="//s3.amazonaws.com/github/ribbons/forkme_right_orange_ff7600.png" alt="Fork me on GitHub"></a>
</body>
</html>
