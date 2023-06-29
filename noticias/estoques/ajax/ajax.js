 var http_request = false;
 var campodestino = '';
 var varurl = '';
 var varparameters = '';
 function makeRequest(url, parameters, destino) {
    http_request = false;
    campodestino = destino;
    varurl = url;
    varparameters = parameters;
    if (window.XMLHttpRequest) { // Mozilla, Safari,...
       http_request = new XMLHttpRequest();
       if (http_request.overrideMimeType) {
       	// set type accordingly to anticipated content type
          http_request.overrideMimeType('text/html');
       }
    } else if (window.ActiveXObject) { // IE
       try {
          http_request = new ActiveXObject("Msxml2.XMLHTTP");
       } catch (e) {
          try {
             http_request = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {}
       }
    }
    
    if (!http_request) {
       alert('Cannot create XMLHTTP instance');
       return false;
    }
    http_request.open('GET', url + parameters, true);
 		http_request.onreadystatechange = alertContents;
 		http_request.send(null);
 }

 function alertContents() {
		if (http_request.readyState == 4) {
       if (http_request.status == 200) {
          document.getElementById(campodestino).innerHTML = http_request.responseText;
       } else {
          alert('There was a problem with the request.');
       }
    }
 }
 
 
 
 function makeRequest2(url, parameters, destino) {
    http_request = false;
    campodestino = destino;
    varurl = url;
    varparameters = parameters;
    if (window.XMLHttpRequest) { // Mozilla, Safari,...
       http_request = new XMLHttpRequest();
       if (http_request.overrideMimeType) {
       	// set type accordingly to anticipated content type
          http_request.overrideMimeType('text/html');
       }
    } else if (window.ActiveXObject) { // IE
       try {
          http_request = new ActiveXObject("Msxml2.XMLHTTP");
       } catch (e) {
          try {
             http_request = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {}
       }
    }
    
    if (!http_request) {
       alert('Cannot create XMLHTTP instance');
       return false;
    }
    http_request.open('GET', url + parameters, true);
 		http_request.onreadystatechange = alertContents2;
 		http_request.send(null);
 }

 function alertContents2() {
		if (http_request.readyState == 4) {
       if (http_request.status == 200) {
          document.getElementById(campodestino).value = http_request.responseText;
       } else {
          alert('There was a problem with the request.');
       }
    }
 }