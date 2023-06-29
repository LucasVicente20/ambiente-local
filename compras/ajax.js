function XMLHTTPRequest() {
	try { return new XMLHttpRequest(); }
	catch(ee) {
		if (window.ActiveXObject) {
			var prefixes = ["MSXML2", "Microsoft", "MSXML", "MSXML3"];
			for (var i = 0; i < prefixes.length; i++) {
				try { return new ActiveXObject(prefixes[i] + ".XmlHttp"); }
				catch(E) { return false; } // doesn't support 
			}
		}
	}
}
	function carregaLicitacao(documento){
			document.getElementById('LicitaComisao').style.display = "none";
	}