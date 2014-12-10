window.addEventListener( 'load', function () {
	var ids = { linkgen: 1, 'link-main': 1 },
		inputs = { cantica: 1, canto: 1, lines: 1, lang: 1 },
		elements = {},
		values = {},
		langOrEmpty,
		maxCantos;
	for ( var name in inputs ) {
		ids['input-' + name] = 1;
		ids['link-' + name] = 1;
	}
	for ( var id in ids ) {
		elements[id] = document.getElementById( id );
	}
	function setContent( id, str ) {
		var childNodes = elements[id].childNodes;
		if ( childNodes.length > 0 ) {
			childNodes[0].nodeValue = str;
		} else {
			elements[id].appendChild( new Text( str ) );
		}
	}
	function update() {
		for ( var i in inputs ) {
			values[i] = elements['input-' + i].value;
		}
		langOrEmpty = ( values.lang && values.lang !== 'it' ? values.lang + '/' : '' );
		elements['link-main'].href = langOrEmpty + values.cantica + values.canto + ',' + values.lines;
		setContent( 'link-lang', langOrEmpty );
		setContent( 'link-cantica', values.cantica );
		setContent( 'link-canto', values.canto );
		setContent( 'link-lines', values.lines );
		maxCantos = ( values.cantica === 'i' ? 34 : 33 );
		elements['input-canto'].setAttribute( 'max', maxCantos );
		if ( values.canto > maxCantos ) {
			elements['input-canto'].value = maxCantos;
		}
	}
	elements.linkgen.addEventListener( 'input', update );
	elements['input-cantica'].addEventListener( 'change', update );
} );
