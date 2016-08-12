<?php 

require_once('/var/www/php_include/notifications.php');
set_error_handler('debug');
$content = file_get_contents("php://input");
$update = json_decode($content, true);
$exploded_message_text = explode(" ",$update['message']['text']);
if ($exploded_message_text[0] == "/start") {
	sendMessage($update['message']['chat']['id'],"Hello, \nI am here to park bot usernames\nYou have to set this bot up in @botfather and forward the message containing the bot token to me.\nThen I set up a webhook which tells the innocent user who sends /start to your bot that its currently under development and thats it.\nQuite easy right?\nThen lets get started!\nSend me the bot token directly or the message from @botfather containing the token\n\n<b>NOTE: WE DO NOT STORE YOUR BOT TOKEN, JUST THE CHAT ID OF YOUR BOT TO PROVIDE CUSTOM MESSAGES (SOON)</b>\nSource code: https://github.com/wjclub/telegram-bot-parkme/"); 
} else {
	$result = setWebhook($update['message']['text']);
	if ($result['ok'] == true) sendMessage($update['message']['chat']['id'],"Everything is set up correctly, your bot".$result['detail']['username']." is now parked...\nIf you are bored check out the @wj_bot which can tell jokes and generate random strings etc...😉");
	else sendMessage($update['message']['chat']['id'],"Something went wrong, please check your input or contact @wjclub\n<b>Detail: </b><code>".var_export($response,true)."</code>");
}

function sendMessage($chat_id,$reply){
	$ReplyContent = [
	'method' => "sendMessage",
	'chat_id' => $chat_id,
	'parse_mode' => 'HTML',
	'text' => $reply,
	];
	$replyJson =json_encode($ReplyContent);
	header("Content-Type: application/json");
	echo($replyJson);
}

function setWebhook($token) {
	$searchstring = "You can use this token to access HTTP API:";
	$othersearchstring = "Use this token to access the HTTP API:";
	$endstring = "For a description of the Bot API, see this page: https://core.telegram.org/bots/api";
	$pos = strpos($token,$searchstring);
	if ($pos !== FALSE) {
		$pos += strlen($searchstring);
		$length = strpos($token,$endstring);
		$length -= ($pos + 1);
		$token = substr($token,$pos,$length);
	} else {
		$pos = strpos($token,$othersearchstring);
		$pos += strlen($othersearchstring);
		$length = strpos($token,$endstring);
		$length -= ($pos + 1);
		$token = substr($token,$pos,$length);
	}
	$res = file_get_contents("https://api.telegram.org/bot".$token."/setWebhook?url=");
	$response = file_get_contents("https://api.telegram.org/bot".$token."/setWebhook?url=https://bots.wjclub.tk/telegram/parkmebot/imparked.php?bot=".explode(':',$token)[0]);
	$answer = json_decode($response,true);
	if ($answer["ok"] == true) return ['ok' => true, 'detail' => json_decode(file_get_contents("https://api.telegram.org/bot".$token."/getMe"),true)];
	else {
		trigger_error($response);
		return $response;
	}
}

?>
