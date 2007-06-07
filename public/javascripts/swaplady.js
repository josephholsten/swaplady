/*
 * Swaplady.js
 * provide functionality necessary for swaplady, including:
 * adding tags to product
 */
var Swaplady = {
  add_tag: function(tag, input_id) {
    document.getElementById(input_id).value += ' ' + tag.lastChild.nodeValue;
  }
}

/*
	Written by Jonathan Snook, http://www.snook.ca/jonathan
	Add-ons by Robert Nyman, http://www.robertnyman.com
*/

function getElementsByClassName(oElm, strTagName, strClassName){
	var arrElements = (strTagName == "*" && oElm.all)? oElm.all : oElm.getElementsByTagName(strTagName);
	var arrReturnElements = new Array();
	strClassName = strClassName.replace(/-/g, "\-");
	var oRegExp = new RegExp("(^|\s)" + strClassName + "(\s|$)");
	var oElement;
	for(var i=0; i<arrElements.length; i++){
		oElement = arrElements[i];
		if(oRegExp.test(oElement.className)){
			arrReturnElements.push(oElement);
		}
	}
	return (arrReturnElements)
}

function onloader() {
    tags = getElementsByClassName(document, 'a', 'tag')
    for (i=0; i<tags.length; i++) {
        tags[i].onclick = function() {
            Swaplady.add_tag(this, 'tags');
        };
    }
}

window.onload=onloader;