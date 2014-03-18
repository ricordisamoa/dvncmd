/*
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
*/
$(document).ready(function(){
	if(!location.search && !location.pathname){
		return;
	}
	var query = decodeURIComponent((location.search && location.search.trim() != "" ? location.search : location.pathname).substr(1)),
	reg = query.match(/^(i(0?[1-9]|[0-2][0-9]|3[0-4])|(p|d)(0?[1-9]|[12][0-9]|3[0-3]))(\,[0-9]{1,3}(\-[0-9]{1,3})?)?$/i);
	if(reg === null){
		return;
	}
	var cantica = null;
	switch(query.substr(0, 1).toLowerCase()){
		case "i": cantica = "Inferno"; break;
		case "p": cantica = "Purgatorio"; break;
		case "d": cantica = "Paradiso"; break;
	}
	if(cantica === null){
		return;
	}
	var romanize = function(num){
		// taken from http://blog.stevenlevithan.com/archives/javascript-roman-numeral-converter
		// by Steven Levithan, released under the MIT License
		var	digits=String(+num).split(""),
		key=["","C","CC","CCC","CD","D","DC","DCC","DCCC","CM",
			"","X","XX","XXX","XL","L","LX","LXX","LXXX","XC",
			"","I","II","III","IV","V","VI","VII","VIII","IX"],
		roman="",
		i=3;
		while(i--) roman=(key[+digits.pop()+(i*10)]||"")+roman;
		return Array(+digits.join("")+1).join("M")+roman;
	},
	canto = parseInt((query.split(/^[ipd]/i)[1]).split(',')[0]),
	cantoTrailed = ("0"+canto).substr(canto.toString().length-1),
	versi = query.split(',')[1],
	versInit = parseInt(versi.split('-')[0]),
	versEnd = parseInt(versi.split('-')[1]);
	versi = ", versi "+versInit+"-"+versEnd;
	var pageTitle = "Divina Commedia/"+cantica+"/Canto "+romanize(canto);
	$("section").last().children("h2").first().text(cantica+", canto "+canto+versi);
	$("section").last().find("a").first().attr("href", "//it.wikisource.org/wiki/"+pageTitle);
	$.get(
		"//it.wikisource.org/w/api.php",
		{
			action: "query",
			format: "json",
			prop: "revisions",
			rvlimit: 1,
			titles: pageTitle,
			rvprop: "content"
		},
		function(data){
			var content=data.query.pages[Object.keys(data.query.pages)[0]].revisions[0]["*"];
			var versi=content
			.replace(/^[\s\S]+<poem>\s+/,"")
			.replace(/\s+<\/poem>[\s\S]+$/,"")
			.replace(/ *\{\{[Rr]\|[0-9]+\}\}\n/g,"\n")
			.replace(/\{\{§\|[^\|]+\|([^\|]+)\}\}/g,"$1");
			console.groupCollapsed("Fetching "+pageTitle+" from it.wikisource.org...");
			console.log(versi);
			console.groupEnd();
			versi=versi.match(/.+/g);
			//console.log(versi);
			versi=versi.splice(versInit-1,versEnd-versInit+1);
			console.log(versi.join("\n"));
			$("section").hide()
			.last().show()
			.children("blockquote").first().append(versi.join("<br>"));
		},
		"jsonp"
	);
	$.get(
		"//commons.wikimedia.org/w/api.php",
		{
			action: "query",
			format: "json",
			prop: "imageinfo",
			iiprop: "url",
			iiurlwidth: 1600,
			iiurlheight: 160,
			generator: "categorymembers",
			gcmtitle: "Category:"+cantica+" Canto "+cantoTrailed,
			gcmtype: "file"
		},
		function(data){
			$.each(data.query.pages, function(pageid, page){
				var ii = page.imageinfo[0];
				$("<img>")
				.attr({
					src: ii.thumburl,
					alt: page.title
				})
				.wrap("<a>").parent()
				.attr("href", ii.descriptionurl)
				.appendTo(document.body);
			});
		},
		"jsonp"
	);
});/*
function ok(){
window.onload=function(){
document.getElementsByTagName('section')[2].innerHTML+=cantica+', canto '+canto+versi+'<br>';
document.getElementsByTagName('section')[0].style.display='none';
document.getElementsByTagName('section')[1].style.display='none';
document.getElementsByTagName('section')[2].style.display='block';
}
}
function genera(){
var cantica=document.getElementById('cantica').value;
var canto=document.getElementById('canto').value;
var versi=document.getElementById('versi').value;
if(cantica=='i'){document.getElementById('canto').max=34;}
else{document.getElementById('canto').max=33;
if(canto==34) document.getElementById('canto').value=33;canto=33;
}
document.getElementById('divcom-cantica').innerHTML=cantica;
document.getElementById('divcom-canto').innerHTML=canto;
document.getElementById('divcom-versi').innerHTML=versi;
document.getElementById('divcom-url').href='?'+cantica+canto+','+versi;
var clip=new ZeroClipboard.Client();
clip.setText('http://dvncmd.tk?'+cantica+canto+','+versi);
clip.glue('divcom-url-copy');
document.getElementById('divcom-url-twitter').href='http://twitter.com/?message=Read%20http://dvncmd.tk?'+cantica+canto+','+versi;
document.getElementById('divcom-url-facebook').href='http://www.facebook.com/connect/prompt_feed.php?&message=Read%20http://dvncmd.tk?'+cantica+canto+','+versi;
}
}
}*/
