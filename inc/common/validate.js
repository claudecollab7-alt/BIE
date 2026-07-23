function Trim(nStr){return nStr.replace(/(^\s*)|(\s*$)/g, "");}
function fnPaste(){event.returnValue=false;}
function isNull(obj,msg){
	if(msg!="Password")
		obj1=Trim(obj.value);
	else
		obj1=obj.value;
	if (obj1==""){
		alert("Please enter " +msg);
		obj.focus();
		return true;
	}else
		return false;
}
function isPassword(obj)
{
	if ((obj.value.length)<6)
	{
	alert("Password should have atleast 6 characters");
	obj.value ="";
	obj.focus();
	return true;
	}
	exp=/[^\W]/;
	if(!exp.test(obj.value))
	{alert("Special characters not accepted");
	obj.value ="";
	obj.focus();
	return true;
	}
	else
		return false;
}

/*
function isNumberEquation(evt)
{
	var charCode = (evt.which) ? evt.which : event.keyCode
	
	var allowedkeys = [37,38,39,40,45,46,48,49,50,51,52,53,54,55,56,57,106,107,109,110,111,187,61];
	alert (charCode);
	if(allowedkeys.includes(charCode)){
		alert("test");
		return true;
	}
	else{
	return false;
	}		
}*/

function isNumberEquation(evt){ 	
	var key = (evt.which) ? evt.which : event.keyCode
	alert (key);
	if (!((key == 8) || (key == 13) || (key == 61)|| (key == 43)|| (key == 45)|| (key == 42)|| (key == 47)||(key == 46) || (key == 45) || (key >= 48 && key <= 57) )) {
	  return false;
	} 	
}

function isNumberKey(evt)
{
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (charCode > 31 && (charCode < 48 || charCode > 57))
		return false;
	else
		return true;		
}
function isNumberKey_With_Dot(evt)
{
 	var charCode = (evt.which) ? evt.which : event.keyCode
 	if (charCode > 31 && (charCode < 48 || charCode > 57)){
		if(charCode == 46)
		return true;
		else
		return false;
	}
	else
		return true;
}
function isEmailKey(evt){ 	
	var key = (evt.which) ? evt.which : event.keyCode
	if (!((key == 8) || (key == 13) || (key == 46) || (key == 45) || (key == 64) || (key == 96) || (key >= 65 && key <= 90) || (key >= 48 && key <= 57) || (key >= 97 && key <= 122))) {
	  return false;
	} 	
}
function isPhoneKey(evt){ 	
	var key = (evt.which) ? evt.which : event.keyCode
	if (!((key == 8) || (key == 13) || (key == 45) || (key == 43) || (key == 32) || (key >= 48 && key <= 57))) {
	  return false;
	} 	
}
function isAlphaNumeric(evt){ 	
	var key = (evt.which) ? evt.which : event.keyCode
	if (!((key == 8) || (key == 13) || (key == 32) || (key >= 65 && key <= 90) || (key >= 48 && key <= 57) || (key >= 97 && key <= 122))) {
	  return false;
	} 	
}
function isAlphaOnly(evt){ 	
	var key = (evt.which) ? evt.which : event.keyCode
	if (!((key == 8) || (key == 13) || (key >= 65 && key <= 90) || (key >= 97 && key <= 122))) {
	  return false;
	} 	
}
function isNameKey(evt){ 	
	var key = (evt.which) ? evt.which : event.keyCode
	if (!((key == 8) || (key == 13) || (key == 46) || (key == 32) || (key >= 65 && key <= 90) || (key >= 97 && key <= 122))) {
	  return false;
	} 	
}
function isCompNameKey(evt){ 	
	var key = (evt.which) ? evt.which : event.keyCode
	//alert(key);
	if (!((key == 8) || (key == 13) || (key >= 43 && key <= 47) || (key == 32) || (key >= 65 && key <= 90) || (key >= 48 && key <= 57) || (key >= 95 && key <= 122))) {
	  return false;
	} 	
}
function checkStrength(password) {
	var strength = 0
	if (password.length < 6) {
		$('#result_pw').removeClass()
		$('#result_pw').addClass('short')
		return 'Too short'
	}
	//((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{6,20})
	if (password.length > 7) strength += 1
		// If password contains both lower and uppercase characters, increase strength value.
		if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1
		// If it has numbers and characters, increase strength value.
		if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1
		// If it has one special character, increase strength value.
		if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
		// If it has two special characters, increase strength value.
		if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
		// Calculated strength value, we can return messages
		// If value is less than 2
		if (strength < 2) {
		$('#result_pw').removeClass()
		$('#result_pw').addClass('weak')
		return "Weak"
		} else if (strength == 2) {
		$('#result_pw').removeClass()
		$('#result_pw').addClass('good')
		return 'Good'
		} else {
		$('#result_pw').removeClass()
		$('#result_pw').addClass('strong')
		return 'Strong'
	}
}
function isLen(obj,siz,msg){
	if(obj!=""){
		var strLen=obj.value;
		if(strLen.length < siz){
			alert(msg+" should be atleast " + siz + " characters");
			obj.focus();
			return true;  
		} 
	}else
		return false;
}
function isSpecial(obj)
{
	exp=/(^\w*$)/;
	if(!exp.test(obj.value))
	{
	alert("Special characters & space not accepted");
	obj.value ="";
	obj.focus();
	return true;
	}
}
function isPhoneNumber(obj)
{
	exp=/(^\w*$)/;
	if(!exp.test(obj.value))
	{
	alert("Enter your Mobile Number with out Space");
	obj.value ="";
	obj.focus();
	return true;
	}
}
function isLen(obj,siz,msg){
	if(msg!="Password")
		obj1=Trim(obj.value);
	else
		obj1=obj.value;
	if(obj1!=""){
		var strLen=obj.value;
		if(strLen.length < siz){
			alert(msg+" should be atleast " + siz + " characters");
			obj.focus();
			return true;  
		} 
	}else
		return false;
}
function isdefault(obj,msg,msg2){
	if((Trim(obj.value))==(Trim(msg))){
		alert("Please enter the "+msg2);
		obj.focus();
		return true;
	}else
		return false;
}
function isSame(obj1,obj2,msg1,msg2){
	if((Trim(obj1.value))==(Trim(obj2.value))){
		alert(msg1+" is same as the "+msg2);
		obj2.focus();
		return true;
	}else
		return false;
}	
function isNotSame(obj1,obj2,msg1,msg2){
	if((Trim(obj1.value))!=(Trim(obj2.value))){
		alert(msg1+" does not match");
		obj2.value="";
		obj2.focus();
		return true;
	}else
		return false;
}	
function isCorrect(obj1,obj2,msg1,msg2){
	if((Trim(obj1.value)) >= (Trim(obj2.value))){
		alert(msg1+" should be less than "+msg2);
		obj2.focus();
		return true;
	}else
		return false;
}	
function isTxtareaNull(obj,msg){
	if(Trim(obj.innerText) == ""){
		alert("Please enter " + msg);
		obj.focus();
		return true;
	}else
		return false;
}
function isTxtareaLen(obj,msg){
	if(obj.innerHTML.length > 255){
		alert("Please enter below 256 characters in " + msg);
		obj.focus();
		return true;
	}else
		return false;
}
function notEmail(obj,msg){
	var exp=/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
	if (!exp.test(obj.value)){
		alert("Please enter valid "+msg);
		obj.focus();
		return true;
	}else
		return false;
}
function notZipcode(obj,msg){
	exp = /[a-zA-Z|\d]-{1}/;
	if (!exp.test(obj.value)){
		alert("Please enter valid "+msg);
		obj.focus();
		return true;
	}else
		return false;
}
function notChecked(obj,msg){
	checked = false;
	if(obj.length){
		for(i=0;i<obj.length;i++){
			if(obj[i].checked){
				checked = true;break;
			}
		}
	}else if(obj.checked)
		checked = true;
	if(!(checked)){
		alert("Please select the "+msg);
		if(obj.length)
			obj[0].focus();
		else
			obj.focus();
		return true;
	}
}
function notSelected(obj,msg){
	if (obj.options[obj.selectedIndex].value == ""){
		alert("Please select the "+ msg);
		obj.focus();
		return true;
	}else
		return false;
}
function notImageFile(obj,msg){
	var exp = /^.+\.(jpg|gif|jpeg|png|JPG|JPEG|GIF|PNG)$/;
	if (!exp.test((obj.value).toLowerCase())){
		alert("Please choose jpg, gif or png file for "+msg);
		//obj.focus();
		return true;
	}else
		return false;
}
function notAudioFile(obj,msg){
	var exp = /^.+\.(mp3|MP3)$/;
	if (!exp.test((obj.value).toLowerCase())){
		alert("Please choose MP3 file for "+msg);
		//obj.focus();
		return true;
	}else
		return false;
}
function notDocFile(obj,msg){
	if(Trim(obj.value)!=""){
		var exp = /^.+\.(DOC|doc|TXT|txt)$/;
		if (!exp.test((obj.value).toLowerCase())){
			alert("Please choose doc or txt file for "+msg);
			obj.value="";
			//obj.focus();
			return true;
		}else
			return false;	
	}else
		return false;
}
function notPdfDocFile(obj,msg){
	var exp = /^.+\.(pdf|doc|PDF|DOC)$/;
	if (!exp.test((obj.value).toLowerCase())){
		alert("Please choose pdf or doc file for "+msg);
		obj.value="";
		//obj.focus();
		return true;
	}else
		return false;
}
function notPdfFile(obj,msg){
	var exp = /^.+\.(pdf|PDF)$/;
	if (!exp.test((obj.value).toLowerCase())){
		alert("Please choose pdf file for "+msg);
		//obj.value="";
		//obj.focus();
		return true;
	}else
		return false;
}
function notCsvFile(obj,msg){
	var exp = /^.+\.(csv|CSV)$/;
	if (!exp.test((obj.value).toLowerCase())){
		alert("Please choose csv file for "+msg);
		obj.value="";
		//obj.focus();
		return true;
	}else
		return false;
}
function notXlsFile(obj,msg){
	var exp = /^.+\.(xls|XLS)$/;
	if (!exp.test((obj.value).toLowerCase())){
		alert("Please choose xls file for "+msg);
		obj.value="";
		//obj.focus();
		return true;
	}else
		return false;
}
function notPrice(obj,msg){
	exp = /^[\d]*[\.]{0,1}[\d]{1,2}$/;
	if (!exp.test(obj.value)){
		alert("Please enter valid "+msg);
		obj.value ="";
		obj.focus();
		return true;
	}else
		return false;
}
function fnChkNum(obj,msg){
	exp = /^[\d]*$/;
	if (!exp.test(obj.value)){
		alert("Please enter only numeric values in "+msg);
		obj.value="";
		obj.focus();
		return true;
	}else
		return false;
}
function notWeight(obj,msg){
	exp = /^[\d]*[\.]{0,1}[\d]{1,2}$/;
	if (!exp.test(obj.value)){
		alert("Please enter valid "+msg);
		obj.select();
		obj.focus();
		return true;
	}else
		return false;
}
function fnChkAlpha(obj,msg)
{
	exp = (/(^([a-z]|[A-Z]|["."]|[\s])*$)/);
	if (!exp.test(obj.value))
	{
		alert("Please enter only alphabets in "+msg);
		obj.focus();
		return true;
	}
	else
		return false;
}	
function fnChkAlphaNum(obj,msg){
	exp = (/(^([a-z]|[A-Z]|[0-9])*$)/);
	if (!exp.test(obj.value)){
		alert("Please enter only alphanumeric in "+msg);
		obj.value="";
		obj.focus();
		return true;
	}else
		return false;
}
function fnChkAlphaNumeric(obj,msg){
	var alpha = /[a-zA-Z|]/;
	var Num = /[\d]/;
	if (!(alpha.test(obj.value) && Num.test(obj.value))){
		alert("Please enter only alphanumeric in "+msg);
		obj.value="";
		obj.focus();
		return true;
	}else
		return false;
}
function fnChkFolderName(){
	if(((window.event.keyCode < 48) || (window.event.keyCode > 57)) && ((window.event.keyCode < 65) || (window.event.keyCode > 90)) && ((window.event.keyCode < 97) || (window.event.keyCode > 122)) && (window.event.keyCode != 95)){
		alert("Only Alphabets(A-Z, a-z), Numbers(0-9) and Underscore(_) are allowed");
		window.event.keyCode = 0;
		return true;
	}
}
function isMultiPhone(obj,msg){
		data = obj.value;
		data1 =',';
	if (obj.value.length > 12 ){
		if(data.indexOf(data1) != -1){			
			var arr = data.split(',');
			if(arr[0].length != 10 || arr[1].length != 10){
			alert("Please enter valid "+msg);
			obj.focus();
			return true;
			}
		}else{
			alert("Please enter valid "+msg);
			obj.focus();
			return true;
		}
	}else
		return false;
}
function GetCountry(defaultValue,isNotWithSelect){
	var sCountry="Afghanistan,Albania,Algeria,American Samoa,Andorra,Angola,Anguilla,Antarctica,Antigua and Barbuda,Argentina,Armenia,Aruba,Australia,Austria,Azerbaidjan,Bahamas,Bahrain,Bangladesh,Barbados,Belarus,Belgium,Belize,Benin,Bermuda,Bolivia,Bosnia-Herzegovina,Botswana,Bouvet Island,Brazil,British Indian O. Terr.,Brunei Darussalam,Bulgaria,Burkina Faso,Burundi,Buthan,Cambodia,Cameroon,Canada,Cape Verde,Cayman Islands,Central African Rep.,Chad,Chile,China,Christmas Island,Cocos (Keeling) Isl.,Colombia,Comoros,Congo,Cook Islands,Costa Rica,Croatia,Cuba,Cyprus,Czech Republic,Czechoslovakia,Denmark,Djibouti,Dominica,Dominican Republic,East Timor,Ecuador,Egypt,El Salvador,Equatorial Guinea,Estonia,Ethiopia,Falkland Isl.(Malvinas),Faroe Islands,Fiji,Finland,France,France (European Ter.),French Southern Terr.,Gabon,Gambia,Georgia,Germany,Ghana,Gibraltar,Great Britain (UK),Greece,Greenland,Grenada,Guadeloupe (Fr.),Guam (US),Guatemala,Guinea,Guinea Bissau,Guyana,Guyana (Fr.),Haiti,Heard & McDonald Isl.,Honduras,Hong Kong,Hungary,Iceland,India,Indonesia,Iran,Iraq,Ireland,Israel,Italy,Ivory Coast,Jamaica,Japan,Jordan,Kazachstan,Kenya,Kirgistan,Kiribati,Korea (North),Korea (South),Kuwait,Laos,Latvia,Lebanon,Lesotho,Liberia,Libya,Liechtenstein,Lithuania,Luxembourg,Macau,Madagascar,Malawi,Malaysia,Maldives,Mali,Malta,Marshall Islands,Martinique (Fr.),Mauritania,Mauritius,Mexico,Micronesia,Moldavia,Monaco,Mongolia,Montserrat,Morocco,Mozambique,Myanmar,Namibia,Nauru,Nepal,Netherland Antilles,Netherlands,Neutral Zone,New Caledonia (Fr.),New Zealand,Nicaragua,Niger,Nigeria,Niue,Norfolk Island,Northern Mariana Isl.,Norway,Oman,Pakistan,Palau,Panama,Papua New,Paraguay,Peru,Philippines,Pitcairn,Poland,Polynesia (Fr.),Portugal,Puerto Rico (US),Qatar,Reunion (Fr.),Romania,Russian Federation,Rwanda,Saint Lucia,Samoa,San Marino,Saudi Arabia,Senegal,Seychelles,Sierra Leone,Singapore,Slovak Republic,Slovenia,Solomon Islands,Somalia,South Africa,Spain,Sri Lanka,St. Helena,St. Pierre & Miquelon,St. Tome and Principe,St.Kitts Nevis Anguilla,St.Vincent & Grenadines,Sudan,Suriname,Svalbard & Jan Mayen Is,Swaziland,Sweden,Switzerland,Syria,Tadjikistan,Taiwan,Tanzania,Thailand,Togo,Tokelau,Tonga,Trinidad & Tobago,Tunisia,Turkey,Turkmenistan,Turks & Caicos Islands,Tuvalu,Uganda,Ukraine,United Arab Emirates,United Kingdom,United States,Uruguay,US Minor outlying Isl.,Uzbekistan,Vanuatu,Vatican City State,Venezuela,Vietnam,Virgin Islands (British)";
	var xCountry=sCountry.split(",");
	var str="";
	if (!isNotWithSelect)str+="<option value='' selected>Select Country</option>\n";//else str+="<option value='' selected>Doesn't Matter</option>\n";
	for(i=0;i<xCountry.length; i++)
	if(xCountry[i]==defaultValue)str+="<option value='"+xCountry[i]+"' selected>"+xCountry[i]+"</option>\n";else str+="<option value='"+xCountry[i]+"'>"+xCountry[i]+"</option>\n";
	document.write(str);
}
function fnShowDate(obj,msg){
	var retdate=window.showModalDialog("includes/calender.htm","","dialogHeight: 219px; dialogWidth: 273px;  center: Yes; help: No; resizable: No; status: No;titlebar:No");
	obj.value=retdate;
}
function isNullMulti(obj,msg){
	if (Trim(obj.value)==""){
		alert("Please select the " + msg);
		obj.focus();
		return true;
	}else
		return false;
}
function fnProfile(v1){
	ref=window.open("employee_profile.php?Id="+v1,"Profile","Left=180, Top=90, height=500,width=650,toolbar=no,scrollbars=yes,menubar=no,resize=false");
}
function isNullCbo(obj,msg)
{
	if (Trim(obj.value)=="")
	{
		alert("Please select the " + msg);
		obj.focus();
		return true;
	}
	else
		return false;
}
function isNullhid(obj,msg)
	{
	if ((obj.value)=="")
		{
		alert("Please enter the " +msg);
		return true;
		}
	else
		return false;
	}
function notFile(obj,msg){
	if(Trim(obj.value)!=""){
		var exp = /^.+\.(DOC|doc|TXT|txt|JPG|jpg|JPEG|jpeg|GIF|gif|XLS|xls)$/;
		if (!exp.test((obj.value).toLowerCase())){
			alert("Please choose doc | txt | file for "+msg);
			obj.value="";
			//obj.focus();
			return true;
		}else
			return false;	
	}else
		return false;
}
function isNullhid(obj,msg)
	{
	if (obj=="")
		{
		alert("Please enter the " +msg);
		return true;
		}
	else
		return false;
	}
function file4ftp(obj){
	if(Trim(obj.value)!=""){
//		var exp = /^.+\.(DOC|doc|TXT|txt|JPG|jpg|JPEG|jpeg|GIF|gif|XLS|xls|zip|ZIP|rtf|RTF|avi|AVI|dat|DAT|mp3|MP3)$/;
		var exp = /^.+\.(exe|EXE)$/;
		if (exp.test((obj.value).toLowerCase())){
			alert("Invalid File format");
			obj.value="";
			//obj.focus();
			return true;
		}else
			return false;	
	}else
		return false;
}
function fnPressFiles(obj){
	var exp = /^.+\.(pdf|PDF|mp3|MP3)$/;
	if (!exp.test((obj.value).toLowerCase())){
		alert("Please choose valid file");
		obj.value="";
		//obj.focus();
		return true;
	}else
		return false;
}
function isEditorNull(obj,msg)
	{
	strTmp = obj.value;
	StrContent=strTmp.split("<BODY>");
	StrContent=Trim(StrContent[1]);
	StrContent=StrContent.split("</BODY>");
	StrContent=Trim(StrContent[0]);
	strLength=Trim(StrContent).length;
	if (strLength==0)
		{
		alert (msg);
		return true;
		}
	else
		{
		return false;
		}	
	}
function fnDivCheck()
	{	
	var val = tblView.offsetHeight;
	if (tblView.offsetHeight >= 320)  
		DivDisplay.style.overflow = "auto";
	else
		{
		DivDisplay.style.overflow = "hidden";
		DivDisplay.style.height = val;
		}
	}
function fnHelp()
{
	var objWin = window.open("img_dimension.htm","help","width=350,height=275,menubar=no,location=no");
	objWin.focus();
}
function stripEditorNull(obj)
{
	strTmp = obj.value;
	strTmp = strTmp.replace(/<HTML(.*)>/i,"");
	strTmp = strTmp.replace('<HEAD>',"");
	strTmp = strTmp.replace(/<META\scontent=(.*)>/ig,"");
	strTmp = strTmp.replace(/<LINK(.*)type=text\/css rel=stylesheet>/ig,"");
	strTmp = strTmp.replace(/<font/ig,"<ont");
	strTmp = strTmp.replace(/<\/font>/ig,"</ont>");
	strTmp = strTmp.replace(/style=/ig,"tyle=");
	strTmp = strTmp.replace('</HEAD>',"");
	strTmp = strTmp.replace('<BODY>',"");
	strTmp = strTmp.replace("</BODY>","");
	strTmp = strTmp.replace("</HTML>","");
	return strTmp;
}
function isFeatureText(obj)
{
	if((obj.value.length)>151)
	{
		alert("Maximum of 150 characters allowed");
		obj.focus();
		return true;
	}
	else
	return false;
}
function fnAlphaNumSpace(obj,msg){
	exp = (/(^([a-z]|[A-Z]|[0-9]|[\s])*$)/);
	if (!exp.test(obj.value)){
		alert("Please enter only alphanumeric & spaces in "+msg);
		obj.value="";
		obj.focus();
		return true;
	}else
		return false;
}
function notPrice1(obj)
{
	exp = /^[\d]*[\.]{0,1}[\d]{1,2}$/;
	if (!exp.test(obj.value))
	{
		alert("Please enter valid price");
		obj.value ="";
		obj.focus();
		return true;
	}
	else
		return false;
}
function fnChkPercent(obj)
{
	exp = /^[\d]*[\.]{0,1}[\d]{1,2}$/;
	if (!exp.test(obj.value))
	{
		alert("Please enter only numerics");
		obj.value ="";
		obj.focus();
		return true;
	}
	else
		return false;
}
//------------------------------------PHONE-NO-VALIDATION-------------------------------------------------------
var digits = "0123456789";
// non-digit characters which are allowed in phone numbers
var phoneNumberDelimiters = "()- ";
// characters which are allowed in international phone numbers
// (a leading + is OK)
var validWorldPhoneChars = phoneNumberDelimiters + "+";
// Minimum no of digits in an international phone no.
var minDigitsInIPhoneNumber = 10;
function isInteger(s)
{   var i;
    for (i = 0; i < s.length; i++)
    {   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}
function trim(s)
{   var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not a whitespace, append to returnString.
    for (i = 0; i < s.length; i++)
    {   
        // Check that current character isn't whitespace.
        var c = s.charAt(i);
        if (c != " ") returnString += c;
    }
    return returnString;
}
function stripCharsInBag(s, bag)
{   var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++)
    {   
        // Check that current character isn't whitespace.
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}
function checkInternationalPhone(strPhone){
	var bracket=3;
	strPhone=trim(strPhone);
	if(strPhone.indexOf("+")>1) return false;
	if(strPhone.indexOf("-")!=-1)bracket=bracket+1
	if(strPhone.indexOf("(")!=-1 && strPhone.indexOf("(")>bracket)return false;
	var brchr=strPhone.indexOf("(")
	if(strPhone.indexOf("(")!=-1 && strPhone.charAt(brchr+2)!=")")return false;
	if(strPhone.indexOf("(")==-1 && strPhone.indexOf(")")!=-1)return false;
	s=stripCharsInBag(strPhone,validWorldPhoneChars);
	return (isInteger(s) && s.length >= minDigitsInIPhoneNumber);
}
function ValidatePhoneNo(obj,msg){
	var Phone=obj.value;
	alert(Phone);
	if (Phone.value!="")
	{
		if (checkInternationalPhone(Phone.value)==false){
			alert("Please Enter a Valid " +msg);
			Phone.value="";
			Phone.focus();
			return true;
		}
		else{
			return false;
		}
	}
}
function fnCheckDate(obj,msg,opn){
	var dateEntered=obj.value;
	var date = dateEntered.substring(0, 2);
    var month = dateEntered.substring(3, 5);
    var year = dateEntered.substring(6, 10); 
    var dateToCompare = new Date(year, month - 1, date);
    var currentDate = new Date(); 
	if(opn == 'G'){
		if(dateToCompare > currentDate){
			obj.focus();
			alert("Please enter valid date in "+msg);
			return true;
		}
	}
	if(opn == 'L'){
		if(dateToCompare < currentDate){
			obj.focus();
			alert("Please enter valid date in "+msg);
			return true;
		}
	}
}
function IsNumeric(sText)
{
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;
   }
function echeck(str) 
{
		var at="@";
		var dot=".";
		var lat=str.indexOf(at)
		var lstr=str.length
		var ldot=str.indexOf(dot)
		//var emailId = document.frmEnquiry.mailid.value;
		//SplitName = emailId.split("@")
		if (str.indexOf(at)==-1)
		{
		   alert("Please enter a valid e-mail id");
		   return false;
		}
		if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr)
		{
			alert("Invalid e-mail id");
			return false;
		}
		if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr)
		{
			alert("Invalid e-mail id");
			return false;
		}
		if (str.indexOf(at,(lat+1))!=-1)
		{
			alert("invalid e-mail id");
			return false;
		}
		if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot)
		{
			alert("invalid e-mail id");
			return false;
		}
		if (str.indexOf(dot,(lat+2))==-1)
		{
			alert("invalid e-mail id");
			return false;
		}
		if (str.indexOf(" ")!=-1)
		{
			alert("invalid e-mail id");
			return false;
		}
		if (str.length<6)
		{
			alert("your e-mail id should not be less than 6 character.");
			return false;
		}
}
function echeck(str) 
{
		var at="@";
		var dot=".";
		var lat=str.indexOf(at)
		var lstr=str.length
		var ldot=str.indexOf(dot)
		//var emailId = document.frmEnquiry.mailid.value;
		//SplitName = emailId.split("@")
		if (str.indexOf(at)==-1)
		{
		   alert("Please enter a valid e-mail id");
		   return false;
		}
		if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr)
		{
			alert("Invalid e-mail id");
			return false;
		}
		if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr)
		{
			alert("Invalid e-mail id");
			return false;
		}
		if (str.indexOf(at,(lat+1))!=-1)
		{
			alert("invalid e-mail id");
			return false;
		}
		if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot)
		{
			alert("invalid e-mail id");
			return false;
		}
		if (str.indexOf(dot,(lat+2))==-1)
		{
			alert("invalid e-mail id");
			return false;
		}
		if (str.indexOf(" ")!=-1)
		{
			alert("invalid e-mail id");
			return false;
		}
		if (str.length<6)
		{
			alert("your e-mail id should not be less than 6 character.");
			return false;
		}
}
//-------------------------  DATE CHECKING ---------------------------
function check_date(field){
var checkstr = "0123456789";
var DateField = field;
var Datevalue = "";
var DateTemp = "";
var seperator = "-";
var day;
var month;
var year;
var leap = 0;
var err = 0;
var i;
   err = 0;
   DateValue = DateField.value;
   /* Delete all chars except 0..9 */
   for (i = 0; i < DateValue.length; i++) {
	  if (checkstr.indexOf(DateValue.substr(i,1)) >= 0) {
	     DateTemp = DateTemp + DateValue.substr(i,1);
	  }
   }
   DateValue = DateTemp;
   /* Always change date to 8 digits - string*/
   /* if year is entered as 2-digit / always assume 20xx */
   if (DateValue.length == 6) {
      DateValue = DateValue.substr(0,4) + '20' + DateValue.substr(4,2); }
   if (DateValue.length != 8) {
      err = 19;}
   /* year is wrong if year = 0000 */
   year = DateValue.substr(4,4);
   if (year == 0) {
      err = 20;
   }
   /* Validation of month*/
   month = DateValue.substr(2,2);
   if ((month < 1) || (month > 12)) {
      err = 21;
   }
   /* Validation of day*/
   day = DateValue.substr(0,2);
   if (day < 1) {
     err = 22;
   }
   /* Validation leap-year / february / day */
   if ((year % 4 == 0) || (year % 100 == 0) || (year % 400 == 0)) {
      leap = 1;
   }
   if ((month == 2) && (leap == 1) && (day > 29)) {
      err = 23;
   }
   if ((month == 2) && (leap != 1) && (day > 28)) {
      err = 24;
   }
   /* Validation of other months */
   if ((day > 31) && ((month == "01") || (month == "03") || (month == "05") || (month == "07") || (month == "08") || (month == "10") || (month == "12"))) {
      err = 25;
   }
   if ((day > 30) && ((month == "04") || (month == "06") || (month == "09") || (month == "11"))) {
      err = 26;
   }
   /* if 00 ist entered, no error, deleting the entry */
   if ((day == 0) && (month == 0) && (year == 00)) {
      err = 0; day = ""; month = ""; year = ""; seperator = "";
   }
   /* if no error, write the completed date to Input-Field (e.g. 13.12.2001) */
   if (err == 0) {
      DateField.value = day + seperator + month + seperator + year;
   }
   /* Error-message if err != 0 */
   else {
      alert("Please check the Date....!");
      DateField.select();
	  DateField.focus();
	  return false;
   }
}
//  End -->
//------------------------------------PHONE-NO-VALIDATION-------------------------------------------------------