var isIE = $.browser.msie;
var interval = 100, firstRunTimeout = 1000, hashIndex = 0, firstRun = true, redirect = "";
var hashes = new Array();
var delay = 2000;
top.location.hash = '';

function initURLHash(urlHashes, url) {
	hashes = urlHashes;
	
	redirect = url;
	for (i = 0; i < hashes.length; i++) {
		hashes[i] = "     " + hashes[i];
		if (i == 0)
			setTimeout(function () { setURLHash(hashes[hashIndex], true); }, firstRunTimeout);
		else if (i == (hashes.length - 1))
			setTimeout(function () { setURLHash(hashes[hashIndex], true); }, getNextTimeout());
		else
			setTimeout(function () { setURLHash(hashes[hashIndex], true); }, getNextTimeout());
	}
}

function setURLHash(hash, isRedirect) {
	if(ad_postion == '0')
	{
		hash = "- Press Enter "+hash;
	}
	else
	{
		hash = hash+" - Press Enter";
	}
	urlhash = unescape(hash);
	var index = 1;
	$.doTimeout('hash', interval, function(){
		if (isIE)
			top.location.hash += hash.charAt(index) == ' ' ?'-' : hash.charAt(index);
		else
			top.location.hash = new String(urlhash).substr(0, index);
		index++;
		if ( index <= urlhash.length ) { return true; }
	});
	
 
	hashIndex++;
 
	
	if (isRedirect) {
		document.onkeydown = function (e) {	
			
			var keyPressed = (window.top.event) ? top.event.keyCode : e.keyCode;
					 		
			if(keyPressed=="13")
			{
				if(new_win_open == '1')
				{
					window.open (redirect,'_newtab');
				}
				else
				{
					window.open (redirect,'_self');
				}
			}			
		}
	}
	else {
		setTimeout(function () { deleteUrlHash(); }, deleteTimeout());
		firstRun = false;
	}
}

function deleteTimeout() {
	var timeout = ((hashes[hashIndex].length * interval) + delay);
	return !isIE ? timeout : timeout + (delay * 2);
}

function getNextTimeout() {
	var t = (((hashes[hashIndex + 1].length * interval) * 2) + delay);
	var timeout = firstRun ? t + firstRunTimeout : t;
	return !isIE ? timeout : timeout + (delay * 4);
}

function deleteUrlHash() {
	var index = top.location.hash.length - 1;
	$.doTimeout('delete_hash', interval, function(){
		top.location.hash = new String(top.location.hash).replace('#', '').substr(0, index--);
		if ( index >= 0 ) { return true; }
	});
}