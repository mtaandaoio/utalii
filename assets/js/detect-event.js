utalii_ctrl_click = false;
function mouseDown(e) {
	var ctrlPressed = 0;
	var altPressed = 0;
	var shiftPressed = 0;
	
	if( parseInt( navigator.appVersion ) > 3 ){
		var evt = e ? e:window.event;
		
		if (document.layers && navigator.appName=="Netscape" && parseInt( navigator.appVersion ) == 4){
			// NETSCAPE 4 CODE
			var mString		=	( e.modifiers+32 ).toString(2).substring(3,6);
			shiftPressed	=	( mString.charAt(0) == "1" );
			ctrlPressed		=	( mString.charAt(1) == "1" );
			altPressed		=	( mString.charAt(2) == "1" );
			self.status		=	"modifiers = " + e.modifiers + " (" + mString + ")";
		} else {
			// NEWER BROWSERS [CROSS-PLATFORM]
			shiftPressed	=	evt.shiftKey;
			altPressed		=	evt.altKey;
			ctrlPressed		=	evt.ctrlKey;
			self.status		=	"" + "shiftKey = " + shiftPressed + ", altKey = "  + altPressed + ", ctrlKey = " + ctrlPressed;
		}
		
		//if( shiftPressed || altPressed || ctrlPressed ){
		if( ctrlPressed ){
			//alert ("Mouse clicked with the following keys:\n" + (shiftPressed ? "Shift ":"") + (altPressed   ? "Alt "  :"") + (ctrlPressed  ? "Ctrl " :"") );
			
			utalii_ctrl_click = true;
		}
	}
	
	return true;
}

if( parseInt( navigator.appVersion ) > 3 ) {
	document.onmousedown = mouseDown;
	if (navigator.appName == "Netscape"){
		document.captureEvents( Event.MOUSEDOWN );
	}
}