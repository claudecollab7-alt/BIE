// JavaScript Document
    function PrintParts(arrElementNames){
     var objWindow=window.open("about:blank", "print", "left=0, top=0, width=800, height=450, toolbar=no, scrollbars=yes");
     var strHtml="<!DOCTYPE html><html lang='en'>";
	 strHtml += "<head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />";
	 strHtml += "<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' />";
     strHtml += "<link href='css/main.css' rel='stylesheet' type='text/css'>";
	 strHtml += "<link href='css/cis_styles.css' rel='stylesheet' type='text/css'>";
     strHtml += "<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>";
	 strHtml += "</head>";
     strHtml += "<body>";
  	 strHtml += "<div class='invoice'>"
	 for (var i=0; i<arrElementNames.length; i++)
     {
  	  var element=document.getElementById(arrElementNames[i]);
  	  strHtml += element.innerHTML;
     }
	 //strHtml +="</body>";
	 strHtml +="</div></body>";
     strHtml += "</html>";
     objWindow.document.write(strHtml);
     //objWindow.document.close();
     //objWindow.print();
     //objWindow.close();
    }

	function PrintPartsNew(arrElementNames, title) {
		var objWindow = window.open("about:blank", "print",
			"left=0,top=0,width=900,height=600,toolbar=no,scrollbars=yes");

		var strHtml  = "<!DOCTYPE html><html lang='en'><head>";
		strHtml += "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
		strHtml += "<title>" + title + "</title>";
		strHtml += "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";

		strHtml += "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
		strHtml += "<link href='css/bootstrap_limitless.css' rel='stylesheet'>";
		strHtml += "<link href='css/layout.css' rel='stylesheet'>";
		strHtml += "<link href='css/components.css' rel='stylesheet'>";
		strHtml += "<link href='css/colors.css' rel='stylesheet'>";
		strHtml += "<link href='css/custom.css' rel='stylesheet'>";

		strHtml += "<style>";
		/* Equal 1cm margins on all sides — same as the PHP @page rule */
		strHtml += "@page { size: A4 portrait; margin: 1cm; }";
		strHtml += "@media print {";
		strHtml +=   "*, *::before, *::after { box-sizing: border-box !important; }";
		strHtml +=   "html, body { overflow: visible !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }";
		/*
		* .invoice wrapper gets 2px right padding so the table border
		* is never flush with Chrome's printable-area boundary.
		* This is the definitive fix for the clipped-right-border bug.
		*/
		strHtml +=   ".invoice { padding-right: 2px !important; box-sizing: border-box !important; }";
		strHtml +=   ".po_print_table { font-size: 9px !important; width: 100% !important; table-layout: fixed !important; border-collapse: collapse !important; }";
		strHtml +=   ".po_print_table td, .po_print_table th { padding: 2px 3px !important; word-break: break-word !important; overflow: hidden !important; }";
		strHtml += "}";
		strHtml += "</style>";

		strHtml += "</head><body>";
		strHtml += "<div class='invoice'>";
		for (var i = 0; i < arrElementNames.length; i++) {
			var el = document.getElementById(arrElementNames[i]);
			if (el) strHtml += el.innerHTML;
		}
		strHtml += "</div>";

		strHtml += "<script>";
		strHtml += "window.addEventListener('load', function () {";
		strHtml += "  var imgs = document.images, total = imgs.length, done = 0;";
		strHtml += "  function check() { done++; if (done >= total) { setTimeout(function () { window.print(); window.close(); }, 400); } }";
		strHtml += "  if (total === 0) { setTimeout(function () { window.print(); window.close(); }, 400); return; }";
		strHtml += "  for (var i = 0; i < total; i++) {";
		strHtml += "    if (imgs[i].complete) { check(); } else { imgs[i].onload = check; imgs[i].onerror = check; }";
		strHtml += "  }";
		strHtml += "});";
		strHtml += "<\/script>";

		strHtml += "</body></html>";
		objWindow.document.write(strHtml);
		objWindow.document.close();
	}

	/*function PrintPartsNew(arrElementNames, title) {
		var objWindow = window.open("about:blank", "print", "left=0, top=0, width=800, height=450, toolbar=no, scrollbars=yes");
		var strHtml = "<!DOCTYPE html><html lang='en'>";
		strHtml += "<head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />";
		strHtml += "<title>" + title + "</title>";
		strHtml += "<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' />";
		strHtml += "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
		strHtml += "<link href='css/bootstrap_limitless.css' rel='stylesheet' type='text/css'>";
		strHtml += "<link href='css/layout.css' rel='stylesheet' type='text/css'>";
		strHtml += "<link href='css/components.css' rel='stylesheet' type='text/css'>";
		strHtml += "<link href='css/colors.css' rel='stylesheet' type='text/css'>";
		strHtml += "<link href='css/custom.css' rel='stylesheet' type='text/css'>";

		// THE ONLY ADDITION: force smaller font/padding so 15 columns fit A4 width in Chromium
		strHtml += "<style>";
		strHtml += "@page { size: A4 portrait; margin: 0.8cm 1.5cm 0.8cm 0.8cm; }";
		strHtml += "@media print {";
		strHtml += "  html, body { overflow: visible !important; width: 100% !important; }";
		strHtml += "  .po_print_table { font-size: 9px !important; width: 100% !important; }";
		strHtml += "  .po_print_table td, .po_print_table th { padding: 2px 3px !important; word-break: break-word; }";
		strHtml += "  .asign { padding-left: 50px; }";
		strHtml += "}";
		strHtml += "</style>";

		strHtml += "</head><body>";
		strHtml += "<div class='invoice'>";
		for (var i = 0; i < arrElementNames.length; i++) {
			var element = document.getElementById(arrElementNames[i]);
			strHtml += element.innerHTML;
		}
		strHtml += "</div>";

		strHtml += "<script>";
		strHtml += "window.addEventListener('load', function() {";
		strHtml += "  var imgs = document.images, total = imgs.length, done = 0;";
		strHtml += "  function check() { done++; if (done >= total) { setTimeout(function(){ window.print(); window.close(); }, 500); } }";
		strHtml += "  if (total === 0) { setTimeout(function(){ window.print(); window.close(); }, 500); return; }";
		strHtml += "  for (var i = 0; i < total; i++) {";
		strHtml += "    if (imgs[i].complete) check();";
		strHtml += "    else { imgs[i].onload = check; imgs[i].onerror = check; }";
		strHtml += "  }";
		strHtml += "});";
		strHtml += "<\/script>";

		strHtml += "</body></html>";
		objWindow.document.write(strHtml);
		objWindow.document.close();
	} 190626*/
/*function PrintPartsNew(arrElementNames,title){
     var objWindow=window.open("about:blank", "print", "left=0, top=0, width=800, height=450, toolbar=no, scrollbars=yes");
     var strHtml="<!DOCTYPE html><html lang='en'>";
	 strHtml += "<head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />";
	 strHtml += "<title>"+title+"</title>";
	 strHtml += "<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' />";
	 //strHtml += "<link href='css/bootstrap.css' rel='stylesheet' type='text/css'>"; Removed by prem on 21-07-2025 due to layout option not displayed in print
	 strHtml += "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
	 strHtml += "<link href='css/bootstrap_limitless.css' rel='stylesheet' type='text/css'>";
	 strHtml += "<link href='css/layout.css' rel='stylesheet' type='text/css'>";
	 strHtml += "<link href='css/components.css' rel='stylesheet' type='text/css'>";
	 strHtml += "<link href='css/colors.css' rel='stylesheet' type='text/css'>";
	 strHtml += "<link href='css/custom.css' rel='stylesheet' type='text/css'>";
	 strHtml += "</head>";
     strHtml += "<body>";
  	 strHtml += "<div class='invoice'>"
	 for (var i=0; i<arrElementNames.length; i++)
     {
  	  var element=document.getElementById(arrElementNames[i]);
  	  strHtml += element.innerHTML;
     }
	 //strHtml +="</body>";
	 strHtml +="</div></body>";
     strHtml += "</html>";
	objWindow.document.write(strHtml);	
	objWindow.onload = function () {
		setTimeout(function () { // wait until all resources loaded 
			objWindow.print();  // change window to winPrint
			objWindow.close();// change window to winPrint
		}, 200);
	};
	 objWindow.document.close();
    }*/

	// function PrintPartsNew(arrElementNames, title) {
	// 	var objWindow = window.open("about:blank", "print", "left=0, top=0, width=800, height=450, toolbar=no, scrollbars=yes");
	// 	var strHtml = "<!DOCTYPE html><html lang='en'>";
	// 	strHtml += "<head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />";
	// 	strHtml += "<title>" + title + "</title>";
	// 	strHtml += "<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' />";
	// 	strHtml += "<style>";
	// 	// strHtml += "   body { margin: 0; padding: 0; }"; // Remove body margin
	// 	// strHtml += "   .invoice { margin-left: 10px; }"; // Adjust left margin
	// 	strHtml += "</style>";
	// 	strHtml += "</head>";
	// 	strHtml += "<body>";
	// 	strHtml += "<div class='invoice'>";
		
	// 	for (var i = 0; i < arrElementNames.length; i++) {
	// 		var element = document.getElementById(arrElementNames[i]);
	// 		strHtml += element.innerHTML;
	// 	}
		
	// 	strHtml += "</div></body>";
	// 	strHtml += "</html>";
		
	// 	objWindow.document.write(strHtml);
		
	// 	objWindow.onload = function () {
	// 		setTimeout(function () {
	// 			objWindow.print();
	// 			objWindow.close();
	// 		}, 200);
	// 	};
		
	// 	objWindow.document.close();
	// }
	