<?php

define('BOT_TOKEN', '...');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
define('WEBHOOK_URL', 'https://"..."/bot/index.php');

$array_id = array("...", "...", "...", "...", "...", "...", "...", "...", "...", "...");

$apikeyBitly = '...';
$loginBitly = '...';
$url = 'https://...';

$content = file_get_contents("php://input");
$update = json_decode($content, true);


function apiRequestWebhook($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  header("Content-Type: application/json");
  echo json_encode($parameters);
  return true;
}

function exec_curl_request($handle) {
  $response = curl_exec($handle);

  if ($response === false) {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    error_log("Curl returned error $errno: $error\n");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if ($http_code >= 500) {
    // do not wat to DDOS server if something goes wrong
    sleep(10);
    return false;
  } else if ($http_code != 200) {
    $response = json_decode($response, true);
    error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401) {
      throw new Exception('Invalid access token provided');
    }
    return false;
  } else {
    $response = json_decode($response, true);
    if (isset($response['description'])) {
      error_log("Request was successful: {$response['description']}\n");
    }
    $response = $response['result'];
  }

  return $response;
}

function apiRequest($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
      $val = json_encode($val);
    }
  }
  $url = API_URL.$method.'?'.http_build_query($parameters);

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);

  return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  $handle = curl_init(API_URL);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POST, true);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  return exec_curl_request($handle);
}
//--------------------------------------------------------------------------------------------------------------------------------------------

function processMessage($message) {
  // process incoming message
  $message_id = $message['message_id'];
  $chat_id = $message['chat']['id'];
  if (isset($message['text'])) {
    // incoming text message
    $text = $message['text'];

    if (strpos($text, "/start") === 0) {
//      apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Выберите способ сокращения', 'reply_markup' => array(
//        'inline_keyboard' => [[array("text"=>"Ужатый, с рендерингом","callback_data"=>"/1"),array("text"=>"Полный (возможны проблемы с рендерингом)","callback_data"=>"/2")]],
//        'one_time_keyboard' => true,
//        'resize_keyboard' => true)));
//	if (strpos($text, "/start") === 0) {
	  apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Введите ссылку для редиректа, (должна начинаться с http/https)', 'reply_markup' => array(
        'keyboard' => array(array('Получить докумен Word для отслеживания')),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
		
//    } else if ($text === "Hello" || $text === "Hi") {
//		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Nice to meet you'));
//	} else if ($text === "Вставте ссылку") {
//    	apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Введите ссылку для редиректа'));



//---markupButton---
//		apiRequestJson("sendMessage", array('chat_id' => $chat_id,  'reply_markup' => array(
//        'keyboard' => array(array('Hello', 'Hi')),
//        'one_time_keyboard' => true,
//       'resize_keyboard' => true)));
//---

	} else if ($text === 'Получить докумен Word для отслеживания') {
		
		global $url;
		
		$uni = uniqid();
		$file = fopen("../redirect/w" . $uni . ".txt", "w");
		fwrite($file, '"' . $url . "/bot/word/1.png" . '"' . "\n" );
		fwrite($file, $chat_id);
		fclose($file);
	
		$xml = simplexml_load_file('./word/_rels/document.xml.rels');
		$xml->Relationship[1]['Target'] = $url . "/w" . $uni;
		$xml->asXml('./word/_rels/document.xml.rels');
		exec("zip ./word/word.docx ./word/_rels/document.xml.rels");
		$xml->Relationship[1]['Target'] = "...";
		$xml->asXml('./word/_rels/document.xml.rels');

		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Ваша документ для отслеживания № #' . urlencode ('w'. $uni)));
		
		$filepath = realpath("./word/word.docx");
		$post = array('chat_id' => $chat_id,'document' => '@'.$filepath);
		$ch = curl_init(API_URL . 'sendDocument');  
		curl_setopt($ch, CURLOPT_POST, 1);  
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_exec($ch);
		curl_close($ch);
		
//---		
//---
//---
    } else if (filter_var($text, FILTER_VALIDATE_URL)) {
		$uni = uniqid();
		$file = fopen("../redirect/" . $uni . ".txt", "w");
		fwrite($file, '"' . strtolower($text) . '"' . "\n" );
		fwrite($file, $chat_id);
		fclose($file);
		
		apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Выберите способ сокращения', 'reply_markup' => array(
        'inline_keyboard' => [[array("text"=>"Ужатый, с рендерингом","callback_data"=>"1::" . $uni),array("text"=>"Полный","callback_data"=>"2::" . $uni)]],
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));

	}else if (strpos($text, "/stop") === 0) {
      // stop now
    }else {
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => 'Неверная ссылка для редиректа, повторите ввод' ));
    }
  // else {
  //  apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'I understand only text messages'));
  }
}

//---
function checkRights($message) {
	global $array_id;
	$user_id = $message['from']['id'];
	$chat_id = $message['chat']['id'];
	
	if(!in_array($user_id, $array_id))
	{
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'False'));
		exit;
	}
}
//---

if (php_sapi_name() == 'cli') {
  // if run from console, set or delete webhook
  apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
  exit;
}




if (!$update) {
  // receive wrong update, must not happen
  exit;
}

if (isset($update["message"])) {
  checkRights($update["message"]);
  processMessage($update["message"]);
}

if (isset($update["callback_query"])) {
	global $url;
	//$message_id = $update["callback_query"]["message"]['message_id'];
	$chat_id = $update["callback_query"]["message"]['chat']['id'];
	$res = explode("::",$update["callback_query"]['data']);
	
	if ($res[0] <= 2){
		$url = 'http://answay.ga';
		switch($res[0]){
		case '1':
			$url .= '/r' . $res[1];
			rename("../redirect/" . $res[1] . ".txt", "../redirect/r" . $res[1] . ".txt");	
			$res[1] = 'r' . $res[1];
			break;
		case '2':
		rename("../redirect/r" . $res[1] . ".txt", "../redirect/" . $res[1] . ".txt");	
			$url .= '/' . $res[1];
			break;
		default:
			break;
		}
		
		apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Выберите сервис сокращения ссылок', 'reply_markup' => array(
		'inline_keyboard' => [[
		array("text"=>"bitly","callback_data"=>"3::" . $url . "::" . $res[1]),
		array("text"=>"tinyurl","callback_data"=>"4::" . $url . "::" . $res[1]),
		array("text"=>"без сервиса сокращения ссылок","callback_data"=>"5::" . $url . "::" . $res[1]),
		]],
		'one_time_keyboard' => true,
		'resize_keyboard' => true)));
	}
	
	else if ($res[0] > 2){
		global $apikeyBitly, $loginBitly, $url;
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Ваша ссылка для отслеживания № #' . urlencode ($res[2])));	
		switch($res[0]){
			case '3':
				$finalurl = json_decode(file_get_contents("https://api-ssl.bitly.com/v3/shorten?access_token=" . $apikeyBitly . "&login=" . $loginBitly. "&longUrl=" . urlencode ($res[1])), true)[data][url];
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $finalurl ));
				break;
			case '4':
				$finalurl = file_get_contents('http://tinyurl.com/api-create.php?url=' . $res[1]);
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $finalurl));
				break;
			case '5':
				apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $res[1]));
				break;
			default:
				break;
			}
	}
}
?>













