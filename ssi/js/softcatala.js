// Clears textarea and changes style when clicked on for the first time
function cleartext(t1) {
	
	if (t1.defaultValue==t1.value) t1.value = ""
	t1.style.color = '#000000';// It could be styled initially as #cccccc;
}

function selectform(triaform) {

	if (triaform=="0") {
	
		//Modify DOM
		var f = document.forms['cercaform'];
		var cercatext = document.getElementById('es');
		cercatext.setAttribute('name', 'keys');
		f.setAttribute('action', '/search/node');
	
		removeElement('fulltextsearch');
	
	}
	
	if (triaform=="1") {
	
		//Modify DOM
		var f = document.forms['cercaform'];
		var cercatext = document.getElementById('es');
		cercatext.setAttribute('name', 'search');
		f.setAttribute('action', '/wiki/Especial:Cerca');
		removeElement('ns100search');
		
		var fulltext = document.createElement("input");
		fulltext.setAttribute('name', 'fulltext');
		fulltext.setAttribute('value', 'yes');
		fulltext.setAttribute('id', 'fulltextsearch');
		fulltext.setAttribute('type', 'hidden');
		f.appendChild(fulltext);
	
	
	}
	
	if (triaform=="2") {
	
		//Modify DOM
		var f = document.forms['cercaform'];
		var cercatext = document.getElementById('es');
		cercatext.setAttribute('name', 'keywords');
		f.setAttribute('action', '/forum/search.php');
	
		removeElement('fulltextsearch');
	
	}
	
	if (triaform=="3") {
	
		//Modify DOM
		var f = document.forms['cercaform'];
		var cercatext = document.getElementById('es');
		cercatext.setAttribute('name', 'search');
		f.setAttribute('action', '/wiki/Especial:Cerca');
	
		var fulltext = document.createElement("input");
		fulltext.setAttribute('name', 'fulltext');
		fulltext.setAttribute('value', 'yes');
		fulltext.setAttribute('id', 'fulltextsearch');
		fulltext.setAttribute('type', 'hidden');
		f.appendChild(fulltext);
	
		var ns100 = document.createElement("input");
		ns100.setAttribute('name', 'ns100');
		ns100.setAttribute('value', '1');
		ns100.setAttribute('id', 'ns100search');
		ns100.setAttribute('type', 'hidden');
		f.appendChild(ns100);
	}

}


function correu_novetats(form) {

	window.open('/cgi-bin/infosubs.cgi?email='+form.email.value+'&action='+form.action.selectedIndex,'','height=300,width=300,left=80,top=80');

}

function removeElement(id)	{
	var Node = document.getElementById(id);

	if (Node) {

		Node.parentNode.removeChild(Node);
	}
}

function subscriu_novetats() {

	 var subscriubox = document.getElementById("boxsubscriu");
	 subscriubox.style.cssText="display:block; padding-top:5px;";
	 subscriubox.setAttribute("style", "display:block; padding-top:5px;");
	
}


function detectPlatform() {
	
	/** Platform detection, part of Javascript taken from Mozilla sites **/

	/*
	 * Do platform detection
	 */
	var PLATFORM_OTHER    = 0;
	var PLATFORM_WINDOWS  = 1;
	var PLATFORM_WINDOWS_64  = 11;
	var PLATFORM_LINUX    = 2;
	var PLATFORM_LINUX_64 = 21;
	var PLATFORM_MACOSX   = 3;
	var PLATFORM_MAC      = 4;
	var PLATFORM_ANDROID  = 5;
	var PLATFORM_MAEMO    = 6;
	var PLATFORM_IOS      = 7;
	
	var gPlatform = PLATFORM_WINDOWS;
	var ustring  = navigator.userAgent.toLowerCase();
	
	if (navigator.platform.indexOf("Win32") != -1)
		if (ustring.search("wow64") != -1)
			gPlatform = PLATFORM_WINDOWS_64;
		else
			gPlatform = PLATFORM_WINDOWS;
	else if (navigator.platform.indexOf("Win64") != -1)
		gPlatform = PLATFORM_WINDOWS_64;
	else if (navigator.platform.indexOf("Linux") != -1)
		if (navigator.platform.search("armv") != -1)
			if (ustring.search("android") != -1)
				gPlatform = PLATFORM_ANDROID;
			else if (ustring.search("maemo") != -1)
				gPlatform = PLATFORM_MAEMO;
			else
				gPlatform = PLATFORM_OTHER;
		else
			if (navigator.platform.search("x86_64") != -1)
				gPlatform = PLATFORM_LINUX_64;
			else
			gPlatform = PLATFORM_LINUX;
	else if (navigator.platform.indexOf("iPad") != -1 || navigator.platform.indexOf("iPhone") != -1)
		gPlatform = PLATFORM_IOS;
	else if (navigator.userAgent.indexOf("Mac OS X") != -1)
		gPlatform = PLATFORM_MACOSX;
	else if (navigator.userAgent.indexOf("MSIE 5.2") != -1)
		gPlatform = PLATFORM_MACOSX;
	else if (navigator.platform.indexOf("Mac") != -1)
		gPlatform = PLATFORM_MAC;
	else
	  gPlatform = PLATFORM_OTHER;
	
	return gPlatform;
}

$("document").ready(function (){
	
	// Detect if portada
	if ( $("body").hasClass("fondo5") ) {
	
		var platform = detectPlatform();

		if ( platform === 2 || platform === 21 ) {
			llista_enable("lin");
		} else if ( platform === 3 || platform === 4 ) {
			llista_enable("mac");
		} else {
			if ( detectmobile() ) {
				llista_enable("mob");
			} else {
				llista_enable("win");
			}
		}
	}
});

//http://stackoverflow.com/questions/11381673/javascript-solution-to-detect-mobile-browser
function detectmobile() {

	var check = false;
	(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
	return check;
}



function llista_enable(cas) {

	var spanwin = document.getElementById("windows_link");
	var spanlin = document.getElementById("linux_link");
	var spanmac = document.getElementById("mac_link");
	var spanmob = document.getElementById("mob_link");


	if(spanwin == null || spanlin == null || spanmac == null || spanmob == null) {
		return;
	}

	if (cas == "lin") {

		/** Llista **/
		$(".llista_home").hide();
		$(".llista_lin").show();
		
		/** Icons **/
		$("#win1").hide();
		$("#win2").show();

		$("#mac1").hide();
		$("#mac2").show();
		
		$("#lin1").show();
		$("#lin2").hide();

		/** Tabs **/
		$(".tab_home").removeClass("selected_home");
		$("#linux_home").addClass("selected_home");
		
		spanwin.innerHTML = "<a href=\"javascript:llista_enable('win')\" onclick=\"javascript:llista_enable('win')\"> Windows</a>";
		spanlin.innerHTML = " GNU/Linux";
		spanmac.innerHTML = "<a href=\"javascript:llista_enable('mac')\" onclick=\"javascript:llista_enable('mac')\"> OS X</a>";
		spanmob.innerHTML = "<a href=\"javascript:llista_enable('mob')\" onclick=\"javascript:llista_enable('mob')\"> Mòbil/tauleta</a>";
		
	}

	if (cas =="mac") {

		//** Llista **/
		$(".llista_home").hide();
		$(".llista_mac").show();
		
		/** Icons **/
		$("#win1").hide();
		$("#win2").show();

		$("#mac1").show();
		$("#mac2").hide();
		
		$("#lin1").hide();
		$("#lin2").show();
		
		/** Tabs **/
		$(".tab_home").removeClass("selected_home");
		$("#mac_home").addClass("selected_home");
		
		spanwin.innerHTML = "<a href=\"javascript:llista_enable('win')\" onclick=\"javascript:llista_enable('win')\"> Windows</a>";
		spanlin.innerHTML = "<a href=\"javascript:llista_enable('lin')\" onclick=\"javascript:llista_enable('lin')\"> GNU/Linux</a>";
		spanmac.innerHTML = " OS X";
		spanmob.innerHTML = "<a href=\"javascript:llista_enable('mob')\" onclick=\"javascript:llista_enable('mob')\"> Mòbil/tauleta</a>";
	}

	if (cas =="win") {

		/** Llista **/
		$(".llista_home").hide();
		$(".llista_win").show();
		
		/** Icons **/
		$("#win1").show();
		$("#win2").hide();

		$("#mac1").hide();
		$("#mac2").show();
		
		$("#lin1").hide();
		$("#lin2").show();
		
		/** Tabs **/
		$(".tab_home").removeClass("selected_home");
		$("#windows_home").addClass("selected_home");
		
		spanwin.innerHTML = " Windows";
		spanlin.innerHTML = "<a href=\"javascript:llista_enable('lin')\" onclick=\"javascript:llista_enable('lin')\"> GNU/Linux</a>";
		spanmac.innerHTML = "<a href=\"javascript:llista_enable('mac')\" onclick=\"javascript:llista_enable('mac')\"> OS X</a>";
		spanmob.innerHTML = "<a href=\"javascript:llista_enable('mob')\" onclick=\"javascript:llista_enable('mob')\"> Mòbil/tauleta</a>";
	}
	
	if (cas == "mob") {

		/** Llista **/
		$(".llista_home").hide();
		$(".llista_mob").show();
		
		/** Icons **/
		$("#win1").hide();
		$("#win2").show();

		$("#mac1").hide();
		$("#mac2").show();
		
		$("#lin1").hide();
		$("#lin2").show();
		
		/** Tabs **/
		$(".tab_home").removeClass("selected_home");
		$("#mob_home").addClass("selected_home");
		
		spanwin.innerHTML = "<a href=\"javascript:llista_enable('win')\" onclick=\"javascript:llista_enable('win')\"> Windows</a>";
		spanlin.innerHTML = "<a href=\"javascript:llista_enable('lin')\" onclick=\"javascript:llista_enable('lin')\"> GNU/Linux</a>";
		spanmac.innerHTML = "<a href=\"javascript:llista_enable('mac')\" onclick=\"javascript:llista_enable('mac')\"> OS X</a>";
		spanmob.innerHTML = " Mòbil/tauleta";

	}

}

/** Cookie messages **/
$(document).ready(function () {
	$.cookieCuttr({
		cookieAnalyticsMessage: 'Aquest web utilitza galetes pròpies i de tercers per optimitzar i adaptar-se a la vostra navegació i preferències, entre altres tasques. Si continueu navegant entendrem que accepteu la nostra Política de privadesa.',
		cookieWhatAreTheyLink: '/avislegal.htm',
		cookieAcceptButtonText: 'Accepta',
		cookieWhatAreLinkText: 'Més informació...'
	});
}); 




