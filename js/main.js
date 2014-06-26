function _(el) { 
	return document.getElementById(el); 
}

function checker(total,message) {
setInterval(function(){
	if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
  		xmlhttp=new XMLHttpRequest();
  	}
	else {	// code for IE6, IE5
  		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  	}
  xmlhttp.open("GET","includes/ahah.php",true);
	xmlhttp.send();
	xmlhttp.onreadystatechange=function() {
  		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
	    	_("progressBar").value=xmlhttp.responseText;
	    	_("finished").innerHTML=xmlhttp.responseText;

    	}
    	if (xmlhttp.responseText >= 1 ) {
    		_("progressBar").className = "show";
    		_("progress").className = "show";
    	} 
    	if (xmlhttp.responseText == 1 ) {  
    		_("progressBar").className = "hide";
    		_("progress").className = "hide";
    	} 
    	if (xmlhttp.responseText == total ) {
    		_("result").innerHTML = message;
    	}	
  	}

},1000);
}

function runTask(url) {
		if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
  		xmlhttp=new XMLHttpRequest();
  	}
	else {	// code for IE6, IE5
  		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  	}
  	xmlhttp.open("GET",url,true);
	xmlhttp.send();
}