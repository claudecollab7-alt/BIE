
$(document).ready(function () {	
	
	$('.number_only').bind('paste input', allowNumbersOnly);
	$('.number_only_dot').bind('paste input', allowNumbersOnlywithDot);
	$('.number_only_comma').bind('paste input', allowNumbersOnlywithComma);
	$('.email_only').bind('paste input', allowEmailOnly);
	$('.phone_only').bind('paste input', allowPhoneOnly);
	$('.alpha_numeric').bind('paste input', allowAlphaNumeric);
	$('.alpha_only').bind('paste input', allowAlphaOnly);
	$('.name_only').bind('paste input', allowNameOnly);
	$('.splname_only').bind('paste input', allowCompName);
	$('.address_only').bind('paste input', allowAddress);	
});

function allowNumbersOnly(e) {
    var self = $(this);
    setTimeout(function () {
        var initVal = self.val(),
            outputVal = initVal.replace(/[^0-9]/g, "");
        if (initVal != outputVal) self.val(outputVal);
    });
}
function allowNumbersOnlywithDot(e) {
	var self = $(this);
	var regex = /[^0-9\.]/g;
	setTimeout(function () {
        var initVal = self.val(),
        outputVal = initVal.replace(regex, "");
        if (initVal != outputVal) self.val(outputVal);
    });
}
function allowNumbersOnlywithComma(e) {
	var self = $(this);
	var regex = /[^0-9\,]/g;
	setTimeout(function () {
        var initVal = self.val(),
        outputVal = initVal.replace(regex, "");
        if (initVal != outputVal) self.val(outputVal);
    });
}
function allowEmailOnly(e) {
	var self = $(this);
	var regex = /[^@a-zA-Z0-9._-]/g;
	setTimeout(function () {
        var initVal = self.val(),
        outputVal = initVal.replace(regex, "");
        if (initVal != outputVal) self.val(outputVal);
    });
}
function allowPhoneOnly(e) {
	var self = $(this);
	var regex = /[^ 0-9-+]/g;
	setTimeout(function () {
        var initVal = self.val(),
        outputVal = initVal.replace(regex, "");
        if (initVal != outputVal) self.val(outputVal);
    });
}
function allowAlphaNumeric(e) {
	var self = $(this);
	var regex = /[^ a-zA-Z0-9]/g;
	setTimeout(function () {
        var initVal = self.val(),
        outputVal = initVal.replace(regex, "");
        if (initVal != outputVal) self.val(outputVal);
    });
}
function allowAlphaOnly(e) {
	var self = $(this);
	var regex = /[^ a-zA-Z]/g;
	setTimeout(function () {
        var initVal = self.val(),
        outputVal = initVal.replace(regex, "");
        if (initVal != outputVal) self.val(outputVal);
    });
}
function allowNameOnly(e) {
	var self = $(this);
	var regex = /[^ a-zA-Z.]/g;
	setTimeout(function () {
        var initVal = self.val(),
        outputVal = initVal.replace(regex, "");
        if (initVal != outputVal) self.val(outputVal);
    });
}
function allowCompName(e) {
	var self = $(this);
	var regex = /[^ a-zA-Z0-9.,&_-]/g;
	setTimeout(function () {
        var initVal = self.val(),
        outputVal = initVal.replace(regex, "");
        if (initVal != outputVal) self.val(outputVal);
    });
}
function allowAddress(e) {
	var self = $(this);
	var regex = /[^ a-zA-Z0-9.,&_+-/()]\n/g;
	setTimeout(function () {
        var initVal = self.val(),
        outputVal = initVal.replace(regex, "");
        if (initVal != outputVal) self.val(outputVal);
    });
}

function getNum(val) {
   var val = parseFloat(val);
   if (isNaN(val)) {
     return 0;
   }
   return val;
}
function zeroPad(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}



/* ======== LIVE TIMER   ====================== *
var d = new Date(2017,1,1,0,0,0,0);
function transformMiliseconds(t){
  var r = Math.floor((t/(1000*60*60))%24)+0;
  var h = Math.floor((t/(1000*60*60))%12)+0;
  var m = Math.floor((t/(1000*60))%60);
  var s = Math.floor((t/1000)%60);  
  r = (r <12)?'AM':'PM';
  h = (h == 0)?12:h;
  h = (h <10)?'0'+h:h;
  m = (m <10)?'0'+m:m;
  s = (s <10)?'0'+s:s;
  return h+':'+m+':'+s+' '+r;
}
function tick(){
  var newd = new Date();
  document.getElementById('time_ticker').innerHTML = transformMiliseconds(newd-d);
}
var t = setInterval(tick, 1000);
/* ======== LIVE TIMER   ====================== */