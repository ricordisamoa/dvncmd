<!DOCTYPE html>
<html data-ng-app>
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
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.1.5/angular.min.js"></script>
</head>
<body>
<?php

require_once 'DivineComedy.php';

if (array_key_exists('q', $_GET)) {

    $lang = LANG;
    if (array_key_exists('lang', $_GET) and $_GET['lang'] != '') {
        $lang = $_GET['lang'];
    }

    $query = $_GET['q'];
    $parts = explode(',', $query);

    $cantica = substr($parts[0], 0, 1);
    switch ($cantica) {
    case 'i':
        $cantica = 'Inferno';
        break;
    case 'p':
        $cantica = 'Purgatorio';
        break;
    case 'd':
        $cantica = 'Paradiso';
        break;
    default:
        die('No cantica, no party!');
    }

    $cantica = new Cantica($cantica, $lang);
    $canto = substr($parts[0], 1);
    if (!is_numeric($canto)) {
        die('Invalid canto!');
    }
    $canto = $cantica->getCanto(intval($canto));

    $versi = explode('-', $parts[1]);
    if (count($versi) < 1 or count($versi) > 2) {
        die('Error: must specify 1 or 2 line numbers!');
    }
    if (count($versi) == 1) {
        $versi[1] = $versi[0];
    }
    if (!is_numeric($versi[0]) or !is_numeric($versi[1])) {
        die('The line numbers must be integer!');
    }

    $versi[0] = intval($versi[0]);
    $versi[1] = intval($versi[1]);

    if ($versi[1] - $versi[0] > 11) {
        die('Error: exceeded maximum absolute number of lines (12)!');
    }

    $lls = $canto->getLanglinks();

    foreach ($lls as $i => $ll) {
        if ($ll['lang'] == 'fr' or $ll['lang'] == $canto->lang) {
            unset($lls[$i]); // the French version is is prose
        }
    }
    $lls = array_values($lls);        // re-index array
    usort($lls, 'compareLanglinks'); // sort by language code

    echo '<div style="position:fixed;margin-top:100px;right:.5em;float:right">';
    foreach ($lls as $i => $ll) {
        $lname = $languages[$ll['lang']];
        echo '<a target="_self" href="'.($ll['lang'] == LANG ? '' : ('/'.$ll['lang'])).'/'.$query,
        '" title="'.$lname.'">';
        $flag = getFlag($ll['lang']);
        if ($flag != null) {
            echo '<img height="70" src="//commons.wikimedia.org/wiki/Special:Filepath/',
            $flag.'" alt="'.$lname.'">';
        } else {
            echo $lname;
        }
        echo '</a>';
        if ($i == intval(count($lls) / 2)) {
            echo '</div><div style="position:fixed;left:.5em;float:left">';
        } elseif ($i < count($lls) - 1) {
            echo '<br>';
        }
    }
    echo '</div>';

}

?>
<header>
<h1><?php

echo isset($lang) && array_key_exists($lang, $titles) ? $titles[$lang] : $titles['en'];

?></h1>
<h2>link shortener</h2>
</header>
<?php

if (array_key_exists('q', $_GET)) {

    $lines = $canto->getLines($versi[0], $versi[1]);

    echo '<section><h2>', $cantica->name, ', canto ', $canto->num, ', vers',
    (count($lines) == 1 ? 'o '.$versi[0] : 'i '.implode($versi, '-')),
    '</h2><blockquote>', implode($lines, '<br>'),
    '</blockquote><small>Text from <a href="', $canto->url, '">Wikisource</a></small></section>';

    foreach ($canto->getImages() as $i => $img) {
        echo '<a href="'.$img['descriptionurl'].'"><img alt="'.$img['title'],
        '" src="'.$img['thumburl'].'"></a>';
    }

    echo '<!--';
}

?>

<section>
<h2>Type a Divine Comedy Reference</h2>
<form>
<table>
<tr>
<td><label>Cantica:<br>
<select id="cantica" data-ng-model="cantica">
<option value="i" selected>Inferno</option>
<option value="p">Purgatorio</option>
<option value="d">Paradiso</option>
</select></label></td>
<td><label>Canto:<br>
<input type="number" id="canto" data-ng-model="canto" min="1" max="{{cantica === 'i' ? 34 : 33}}" value="1"></label></td>
<td><label>Lines:<br>
<input type="text" id="versi" data-ng-model="versi" placeholder="1-6" pattern="^\d+(\-\d+)?$"></label></td>
<td><label>Language code:<br>
<input type="text" id="lang" data-ng-model="lang" value="it"></label></td>
</tr>
</table>
</form>
</section>

<section>
<h2>Get a Universal Link</h2>
<a id="divcom-url" target="_blank"
href="{{lang!='it'&&lang!=''&&lang!=null?lang+'/':''}}{{cantica}}{{canto}},{{versi}}">http://dvncmd.tk/<span id="divcom-lang">{{lang!='it'&&lang!=''&&lang!=null?lang+'/':''}}</span><span id="divcom-cantica">{{cantica}}</span><span id="divcom-canto">{{canto}}</span>,<span id="divcom-versi">{{versi}}</span></a>
</section>
<?php

if (array_key_exists('q', $_GET)) {
    echo '-->';
}

?>
<a href="https://github.com/ricordisamoa/dvncmd">
<img style="position: fixed; top: 0; right: 0; border: 0;" src="//s3.amazonaws.com/github/ribbons/forkme_right_orange_ff7600.png" alt="Fork me on GitHub"></a>
</body>
</html>
