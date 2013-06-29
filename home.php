<!DOCTYPE html>
<html>
<!--
Divine Comedy link shortener - dvncmd.tk

Copyright © 2012-2013 by Ricordisamoa

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
</head>
<body>
<?php
$userlang='it';
if($_GET['lang'] and $_GET['lang']!='' and $_GET['lang']!=$userlang) $userlang=$_GET['lang'];
function romanize($num){
	# taken from http://blog.stevenlevithan.com/archives/javascript-roman-numeral-converter
	# originally by Steven Levithan, released under the MIT License
	# ported from JavaScript to PHP by me
	$digits=str_split(strval($num));
	$key=array('','C','CC','CCC','CD','D','DC','DCC','DCCC','CM',
		'','X','XX','XXX','XL','L','LX','LXX','LXXX','XC',
		'','I','II','III','IV','V','VI','VII','VIII','IX');
	$roman='';
	$i=3;
	while($i--){
		$f=intval(array_pop($digits))+($i*10);
		$roman=(array_key_exists($f,$key)?$key[$f]:'').$roman;
	}
	return implode(array_fill(0,intval(implode($digits,''))+1,''),'M').$roman;
}
$query=$_GET['q'];
$parts=explode(',',$query);
$cantica=substr($parts[0],0,1);
if($cantica=='i') $cantica_name='Inferno';
else if($cantica=='p') $cantica_name='Purgatorio';
else if($cantica=='d') $cantica_name='Paradiso';
else die('No cantica, no party!');
$canto=substr($parts[0],1);
if((($cantica=='p'||$cantica=='d')&&intval($canto)>33)||($cantica=='i'&&intval($canto)>34)) die('Error: '.$cantica_name.' has less than '.$canto.' canti.');
if(!is_numeric($canto)) die('Invalid canto!');
$versi=explode('-',$parts[1]);
if(count($versi)<1&&count($versi)>2) die('Error: must specify 1 or 2 line numbers!');
if(!is_numeric($versi[0])||(count($versi)==2&&!is_numeric($versi[1]))) die('The lines must be numbers!');
if(count($versi)==2&&intval($versi[0])>=intval($versi[1])) die('The line numbers must be in order!');
else if(count($versi)==1) $versi[1]=$versi[0];
if(intval($versi[1])-intval($versi[0])>11) die('Error: exceeded maximum absolute number of lines (12)!');
$pagetitle='Divina_Commedia/'.$cantica_name.'/Canto_'.romanize($canto);

$flags=[
	'ca'=>'Nuvola Catalonia flag.svg',# Flag of Catalonia.svg
	'cs'=>'Nuvola Czech flag.svg',# Flag of the Czech Republic.svg
	'de'=>'Nuvola German flag.svg',# Flag of Germany.svg
	'el'=>'Nuvola Greek flag.svg',# Flag of Greece.svg
	'en'=>'Nuvola English language flag.svg',
	'es'=>'Nuvola Spain flag.svg',# Flag of Spain.svg
	'et'=>'Nuvola Estonian flag.svg',# Flag of Estonia.svg
	'fi'=>'Nuvola Finnish flag.svg',# Flag of Finland.svg
	'fr'=>'Nuvola France flag.svg',# Flag of France.svg
	'it'=>'Nuvola Italy flag.svg',# Flag of Italy.svg
	'la'=>'Nuvola Vatican flag.svg',# Flag of the Vatican City.svg (temporary workaround)
	'no'=>'Nuvola Norwegian flag.svg',# Flag of Norway.svg
	'pl'=>'Nuvola Polish flag.svg',# Flag of Poland.svg
	'pt'=>'Nuvola Portuguese flag.svg',# Flag of Portugal.svg
	'ro'=>'Nuvola Romanian flag.svg',# Flag of Romania.svg
	'ru'=>'Nuvola Russian flag.svg',# Flag of Russia.svg
	'sl'=>'Nuvola Slovenian flag.svg',# Flag of Slovenia.svg
	'sv'=>'Nuvola Swedish flag.svg'# Flag of Sweden.svg
];

$languages=[];
$languages_query=http_build_query([
	action=>'query',
	meta=>'siteinfo',
	siprop=>'languages',
	format=>'json'
]);
$languages_query=json_decode(file_get_contents('http://it.wikisource.org/w/api.php?'.$languages_query),true)['query']['languages'];
foreach($languages_query as $language){
	$languages[$language['code']]=$language['*'];
}

$langlinks=http_build_query([
	action=>'query',
	prop=>'langlinks',
	format=>'json',
	lllimit=>'max',
	titles=>$pagetitle
]);
$langlinks=json_decode(file_get_contents('http://it.wikisource.org/w/api.php?'.$langlinks),true)['query']['pages'];
$langlinks=$langlinks[array_keys($langlinks)[0]]['langlinks'];
array_push($langlinks,['lang'=>'it','*'=>$pagetitle]);
foreach($langlinks as $index=>$langlink){
	if($langlink['lang']==$userlang) $pagetitle=str_replace(' ','_',$langlink['*']);
	if($langlink['lang']=='fr' or $langlink['lang']==$userlang) unset($langlinks[$index]);# do not show French for now
}
function compare_langlinks($l1,$l2){
	return strcmp($l1['lang'],$l2['lang']);
}
$langlinks=array_values($langlinks);# re-index array
usort($langlinks,'compare_langlinks');# sort by language code
echo '<div style="position:fixed;margin-top:100px;right:.5em;float:right">';
foreach($langlinks as $index=>$langlink){
	echo '<a target="_self" href="'.($langlink['lang']=='it'?'':('/'.$langlink['lang'])).'/'.$_GET['q'].'" title="'.$languages[$langlink['lang']].'">';
	if($flags[$langlink['lang']]) echo '<img height="70" src="http://commons.wikimedia.org/wiki/Special:Filepath/'.str_replace(' ','_',$flags[$langlink['lang']]).'" alt="'.$languages[$langlink['lang']].'">';
	else echo $languages[$langlink['lang']];
	echo '</a><br>';
	if($index==intval(count($langlinks)/2)) echo '</div><div style="position:fixed;left:.5em;float:left">';
}
echo '</div>';
?>
<header>
<h1><?php
$titles=[
	ca=>'La Divina Comèdia',
	cs=>'Božská komedie',
	en=>'Divine Comedy',
	es=>'La Divina Comedia',
	fi=>'Jumalaisesta näytelmästä',
	fr=>'La Divine Comédie',
	it=>'Divina Commedia',
	la=>'Divina Comoedia',
	pl=>'Boska Komedia',
	pt=>'A Divina Comédia',
	ro=>'Divina Comedie',
	ru=>'Божественная комедия',
	sl=>'Božanska komedija'
];
if($titles[$userlang]) echo $titles[$userlang];
else echo $titles['en'];
?></h1>
<h2>link shortener</h2>
</header>
<?php

# get the raw wikicode
$content_url='http://'.$userlang.'.wikisource.org/w/index.php?title='.$pagetitle.'&action=raw';
$content=file_get_contents($content_url);

# get only text in "<poem>" tags
$content=preg_replace('/(^[\s\S]*<poem>[\s\n\r]*|[\s\n\r]*<\/poem>[\s\S]*$)/','',$content);

# remove images (TODO: expect any possible ns-6 alias)
$content=preg_replace('/\[\[\:?([Ff]ile|[Ii]mat?ge|[Ii]mmagine)\:[^\[\]]+(\[\[[^\[\]]+\]\][^\[\]]+)*\]\]\n/','',$content);

# other languages
$content=preg_replace('/^[\s\S]*<div class="verse"><pre>\s+/','',$content);
$content=preg_replace('/\s+<\/pre><\/div>[\s\S]*$/','',$content);

# remove <ref> tags
$content=preg_replace('/<ref[\s\w]*(\/|>[^<>]*<\/ref)>/','',$content);

# remove indentations at line beginning
$content=preg_replace('/^[:\d\s\']*/m','',$content);

# remove final italic marks from Latin text
if($userlang=='la') $content=preg_replace('/\'+\n/','\n',$content);

# $templates='§|R|r|[Cc]ommentItem|[Aa]utoreCitato'; CURRENTLY IN TESTING
$templates='[\w\§]+';

# remove unprintable templates
$content=preg_replace('/\{\{([Oo]tsikko|[Ee]ncabezado|[Tt]itulus2)\n*\|[^\|\{\}]+(\|([^\|\{\}]+))*\}\}/','',$content);
$content=preg_replace('/\{\{('.$templates.')\n*\|[^\|\{\}]+\}\}/','',$content);

# replace some templates with printable parts
$content=preg_replace('/\{\{('.$templates.')\n*\|[^\|\{\}]+\|([^\|\{\}]+)\}\}/','$2',$content);

# remove initial and final spaces
$content=preg_replace('/(^[\s\n\r]+|[\s\n\r]+$)/','',$content);

# remove superfluous line-breaks
$content=preg_replace('/\s*(<br\s?\/?>\s*)*\n+/','\n',$content);

# split the text into lines
$content=explode('\n',$content);

if(intval($versi[1])>count($content)) die('Error: exceeded number of lines in this canto ('.count($content).')!');

# select desired lines only
$content=array_slice($content,intval($versi[0])-1,intval($versi[1])-intval($versi[0])+1);

# ...print them
echo '<section><h2>',$cantica_name,', canto ',$canto,', vers',(count($content)==1?'o '.$versi[0]:'i '.implode($versi,'-')),'</h2><blockquote>',implode($content,'<br>'),'</blockquote><small>Text from <a href="http://',$userlang,'.wikisource.org/wiki/',$pagetitle,'">Wikisource</a></small></section>';

$images=http_build_query([
	action=>'query',
	format=>'json',
	prop=>'imageinfo',
	iiprop=>'url',
	iiurlwidth=>1600,
	iiurlheight=>160,
	generator=>'categorymembers',
	gcmtitle=>'Category:'.$cantica_name.' Canto '.str_pad($canto,2,'0',STR_PAD_LEFT),
	gcmtype=>'file'
]);
$images=json_decode(file_get_contents('http://commons.wikimedia.org/w/api.php?'.$images),true)['query'];
if($images['pages']){
	foreach($images['pages'] as $pageid=>$page){
		$ii=$page['imageinfo'][0];
		echo '<a href="'.$ii['descriptionurl'].'"><img alt="'.$page['title'].'" src="'.$ii['thumburl'].'"></a>';
	}
}
?>
<a href="https://github.com/ricordisamoa/dvncmd"><img style="position: fixed; top: 0; right: 0; border: 0;" src="https://s3.amazonaws.com/github/ribbons/forkme_right_orange_ff7600.png" alt="Fork me on GitHub"></a>
</body>
</html>
