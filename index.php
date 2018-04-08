<?
//RockPaperScissors GameBot for Telegram
//by v0va development
//since 07.04.2018
define('TOKEN', '');
define('NAME', 'Rock-Paper-Scissors GameBot');
define('VERSION', 'v1.0');
$rps = ['✊', '✌', '✋'];
$mysqli = new mysqli('localhost', '', '', '');
$data = json_decode(file_get_contents('php://input'));
$startkey = [
 'keyboard' => [
  ['Start'],
  ['Help', 'Settings']
 ],
 'resize_keyboard' => true
];

if(isset($data->message)){
	$msg = $data->message->text;
	$id = $data->message->from->id;
	if($res = $mysqli->query("SELECT * FROM users WHERE telegram=".$id)){
		if($res->num_rows == 0){
			if(!$mysqli->query("INSERT INTO `users` (`telegram`,`name`,`regdate`) VALUES (".$id.", '".$data->message->from->first_name."', ".strtotime('now').")")){
				sendErr($id,$mysqli->error);
			}
		}
	} else{
		sendErr($id,$mysqli->error);
	}
	switch($msg){
		case '/start':
		 $answer = 'Welcome to *Rock-Paper-Scissors Game Bot*!
To start a game press button or type /startgame';
		 $keyboard = $startkey;
		 $keyboard = json_encode($keyboard);
		 botApi('sendMessage', [
		  'chat_id' => $id,
		  'text' => $answer,
		  'reply_markup' => $keyboard,
		  'parse_mode' => 'Markdown'
		 ]);
		 break;
		case '/startgame':
		case 'Start':
		 $keyboard = [
		  'keyboard' => [
		   ['With real player', 'With bot'],
		   ['Cancel']
		  ],
		  'resize_keyboard' => true
		 ];
		 $keyboard = json_encode($keyboard);
		 if(!$mysqli->query("UPDATE users SET state=3 WHERE telegram=".$id)){
		 	 sendErr($id,$mysqli->error);
		 }
		 botApi('sendMessage', [
		  'chat_id' => $id,
		  'text' => 'OK, a new game. How are you going to play?',
		  'reply_markup' => $keyboard
		 ]);
		 break;
		case 'With real player':
		 if($res = $mysqli->query("SELECT * FROM users WHERE telegram=".$id)){
		 	 while($row = $res->fetch_assoc()){
		 	 	 if($row['state'] == 3){
		 	 	 	 if(!$mysqli->query("UPDATE users SET state=4 WHERE telegram=".$id)){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 	 $keyboard = [
		 	 	 	  'keyboard' => [
		 	 	 	   ['Host','Search'],
		 	 	 	   ['Send invitation to random user'],
		 	 	 	   ['Cancel']
		 	 	 	  ],
		 	 	 	  'resize_keyboard' => true
		 	 	 	 ];
		 	 	 	 $keyboard = json_encode($keyboard);
		 	 	 	 botApi('sendMessage', [
		 	 	 	  'chat_id' => $id,
		 	 	 	  'text' => 'Choose what you want to do',
		 	 	 	  'parse_mode' => 'Markdown',
		 	 	 	  'reply_markup' => $keyboard
		 	 	 	 ]);
		 	 	 }
		 	 }
		 } else{
		 	 sendErr($id,$mysqli->error);
		 }
		 break;
		case 'With bot':
		 if(!$mysqli->query("UPDATE users SET games=games+1 WHERE telegram=".$id)){
		 	 sendErr($id,$mysqli->error);
		 }
		 if($res = $mysqli->query("SELECT * FROM users WHERE telegram=".$id)){
		  while($row = $res->fetch_assoc()){
		 	 	 if($row['state'] == 3){
		 	 	 	 if(!$mysqli->query("UPDATE users SET state=2 WHERE telegram=".$id)){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 	 if(!$mysqli->query("INSERT INTO `rooms` (`player1`,`player2`) VALUES (".$id.", -1)")){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 	 $keyboard = [
		 	 	 	  'keyboard' => [
		 	 	 	   $rps,
		 	 	 	   ['Cancel']
		 	 	 	  ],
		 	 	 	  'resize_keyboard' => true
		 	 	 	 ];
		 	 	 	 $keyboard = json_encode($keyboard);
		 	 	 	 botApi('sendMessage', [
		 	 	 	  'chat_id' => $id,
		 	 	 	  'text' => 'Send me a symbol',
		 	 	 	  'parse_mode' => 'Markdown',
		 	 	 	  'reply_markup' => $keyboard
		 	 	 	 ]);
		 	 	 }
		 	 }
		 } else{
		 	 sendErr($id,$mysqli->error);
		 }
		 break;
		case 'Host':
		case 'Search':
		 if($res = $mysqli->query("SELECT * FROM users WHERE telegram=".$id)){
		 	 while($row = $res->fetch_assoc()){
		 	 	 if($row['state'] == 4){
		 	 	 	 if($msg == 'Host'){
		 	 	 	 	 if(!$mysqli->query("UPDATE users SET state=1 WHERE telegram=".$id)){
		 	 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 	 }
		 	 	 	 	 $keyboard = [
		 	 	 	 	  'keyboard' => [
		 	 	 	 	   ['Cancel']
		 	 	 	 	  ],
		 	 	 	 	  'resize_keyboard' => true
		 	 	 	 	 ];
		 	 	 	 	 $keyboard = json_encode($keyboard);
		 	 	 	 	 botApi('sendMessage', [
		 	 	 	 	  'chat_id' => $id,
		 	 	 	 	  'text' => 'Waiting for another player...',
		 	 	 	 	  'reply_markup' => $keyboard
		 	 	 	 	 ]);
		 	 	 	 }
		 	 	 	 if($msg == 'Search'){
		 	 	 	 	 if($res = $mysqli->query("SELECT * FROM users WHERE state=1 ORDER BY RAND() LIMIT 1")){
		 	 	 	 	  if($res->num_rows == 0){
		 	 	 	 	 	  botApi('sendMessage', [
		 	 	 	 	 	   'chat_id' => $id,
		 	 	 	 	 	   'text' => 'At the moment nobody wants to play. You can play *with bot* or *send somebody invitation*',
		 	 	 	 	 	   'parse_mode' => 'Markdown'
		 	 	 	 	 	  ]);
		 	 	 	 	 	  break;
		 	 	 	 	  }
		 	 	 	 	  while($row = $res->fetch_assoc()){
		 	 	 	 	 	  if($row['telegram'] == $id){
		 	 	 	 	 	 	  $keyboard = [
		          'keyboard' => [
		           ['With real player', 'With bot'],
		           ['Cancel']
		          ],
		          'resize_keyboard' => true
		         ];
		         $keyboard = json_encode($keyboard);
		 	 	 	 	 	 	  botApi('sendMessage', [
		 	 	 	 	 	    'chat_id' => $id,
		 	 	 	 	 	    'text' => 'Please, launch player search again',
		 	 	 	 	 	    'parse_mode' => 'Markdown',
		 	 	 	 	 	    'reply_markup' => $keyboard
		 	 	 	 	 	   ]);
		 	 	 	 	 	   if(!$mysqli->query("UPDATE users SET state=3 WHERE telegram=".$id)){
		 	 	 	 	 	 	   sendErr($id,$mysqli->error);
		 	 	 	 	 	   }
		 	 	 	 	 	   break;
		 	 	 	 	 	  }
		 	 	 	 	 	  //room creation
		 	 	 	 	 	  if(!$mysqli->query("INSERT INTO rooms (`player1`,`player2`,`player1Name`,`player2Name`) VALUES (".$row['telegram'].", ".$id.",'".$row['name']."', '".$data->message->from->first_name."')")){
		 	 	 	 	 	  	 sendErr($id,$mysqli->error);
		 	 	 	 	 	  	 break;
		 	 	 	 	 	  }
		 	 	 	 	   $keyboard = [
		 	 	 	     'keyboard' => [
		 	 	 	       $rps,
		 	 	 	       ['Cancel']
		 	 	 	     ],
		 	 	  	    'resize_keyboard' => true
		 	 	  	   ];
		 	 	 	    $keyboard = json_encode($keyboard);
		 	 	 	    if(!$mysqli->query("UPDATE users SET games=games+1 WHERE telegram=".$id)){
		 	       sendErr($id,$mysqli->error);
		       }
		       if(!$mysqli->query("UPDATE users SET games=games+1 WHERE telegram=".$row['telegram'])){
		 	       sendErr($id,$mysqli->error);
		       }
		 	 	 	    botApi('sendMessage', [
		 	 	 	     'chat_id' => $id,
		 	 	 	     'text' => 'You are playing with '.$row['name'].'. Send me a symbol',
		 	 	 	     'parse_mode' => 'Markdown',
		 	 	 	     'reply_markup' => $keyboard
		 	 	 	    ]);
		 	 	 	    botApi('sendMessage', [
		 	 	 	     'chat_id' => $row['telegram'],
		 	 	 	     'text' => 'You are playing with '.$data->message->from->first_name.'. Send me a symbol',
		 	 	 	     'parse_mode' => 'Markdown',
		 	 	 	     'reply_markup' => $keyboard
		 	 	 	    ]);
		 	 	 	 	  }
		 	 	 	  } else{
		 	 	 	 	  sendErr($id,$mysqli->error);
		 	 	 	  }
		 	 	 	 }
		 	 	 }
		 	 }
		 } else{
		 	 sendErr($id,$mysqli->error);
		 }
		 break;
		case $rps[0]:
		case $rps[1]:
		case $rps[2]:
		 if($res = $mysqli->query("SELECT * FROM rooms WHERE player1=".$id." OR player2=".$id)){
		 	 while($row = $res->fetch_assoc()){
		 	 	 if($row['player1'] == -1 || $row['player2'] == -1){
		 	 	 	 //bot
		 	 	 	 $bot = $rps[rand(0,2)];
		 	 	 	 $answer = compare($msg,$bot,$data->message->from->first_name,'BOT',$rps);
		 	 	 	 $keyboard = $startkey;
		 	 	 	 $keyboard = json_encode($keyboard);
		 	 	 	 botApi('sendMessage', [
		 	 	 	  'chat_id' => $id,
		 	 	 	  'text' => $answer,
		 	 	 	  'parse_mode' => 'Markdown',
		 	 	 	  'reply_markup' => $keyboard
		 	 	 	 ]);
		 	 	 	 if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$id)){
		 	    sendErr($id,$mysqli->error);
		    }
		    if(!$mysqli->query("DELETE FROM `rooms` WHERE id=".$row['id'])){
		    	 sendErr($id,$mysqli->error);
		    }
		 	 	 } else{
		 	 	 	 //not bot
		 	 	 	 if($res = $mysqli->query("SELECT * FROM rooms WHERE player1=".$id." OR player2=".$id)){
		 	 	 	 	 while($row = $res->fetch_assoc()){
		 	 	 	 	 	$p1n = $row['player1Name'];
		 	 	 	 	 	$p2n = $row['player2Name'];
		 	 	 	 	 	 if($id == $row['player1']){
		 	 	 	 	 	 	 //player1
		 	 	 	 	 	 	 $key = (int)array_search($msg,$rps);
		 	 	 	 	 	 	 if(!$mysqli->query("UPDATE rooms SET player1s=".$key." WHERE id=".$row['id'])){
		 	 	 	 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 	 	 	 }
		 	 	 	 	 	 	 if($row['player2s'] == -1){
		 	 	 	 	 	 	 	 //player2 hasnt decided yet
		 	 	 	 	 	 	 	 $keyboard = [
		 	 	 	 	 	 	 	  'keyboard' => [
		 	 	 	 	 	 	 	   ['Cancel']
		 	 	 	 	 	 	 	  ],
		 	 	 	 	 	 	 	  'resize_keyboard' => true
		 	 	 	 	 	 	 	 ];
		 	 	 	 	 	 	 	 $keyboard = json_encode($keyboard);
		 	 	 	 	 	 	 	 botApi('sendMessage', [
		 	 	 	 	 	 	 	  'chat_id' => $id,
		 	 	 	 	 	 	 	  'text' => $p2n.' hasnt decided yet',
		 	 	 	 	 	 	 	  'reply_markup' => $keyboard
		 	 	 	 	 	 	 	 ]);
		 	 	 	 	 	 	 } else{
		 	 	 	 	 	 	 	 //player2 has already decided
		 	 	 	 	 	 	 	 $answer = compare($msg,$rps[$row['player2s']],$p1n,$p2n,$rps);
		 	 	 	 	 	 	 	 $keyboard = $startkey;
		 	 	 	 	 	 	 	 $keyboard = json_encode($keyboard);
		 	 	 	 	 	 	 	 botApi('sendMessage', [
		 	 	 	 	 	 	 	  'chat_id' => $id,
		 	 	 	 	 	 	 	  'text' => $answer,
		 	 	 	 	 	 	 	  'parse_mode' => 'Markdown',
		 	 	 	 	 	 	 	  'reply_markup' => $keyboard
		 	 	 	 	 	 	 	 ]);
		 	 	 	 	 	 	 	 botApi('sendMessage', [
		 	 	 	 	 	 	 	  'chat_id' => $row['player2'],
		 	 	 	 	 	 	 	  'text' => $answer,
		 	 	 	 	 	 	 	  'parse_mode' => 'Markdown',
		 	 	 	 	 	 	 	  'reply_markup' => $keyboard
		 	 	 	 	 	 	 	 ]);
		 	 	 	 	 	 	 	 if(!$mysqli->query("DELETE FROM rooms WHERE id=".$row['id'])){
		 	 	 	 	 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 	 	 	 	 }
		 	 	 	 	 	 	 	 if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$row['player1'])){
		 	 	 	 	 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 	 	 	 	 }
		 	 	 	 	 	 	 }
		 	 	 	 	 	 }
		 	 	 	 	 	 if($id == $row['player2']){
		 	 	 	 	 	 	 //player2
		 	 	 	 	 	 	 $key = (int)array_search($msg,$rps);
		 	 	 	 	 	 	 if(!$mysqli->query("UPDATE rooms SET player2s=".$key." WHERE id=".$row['id'])){
		 	 	 	 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 	 	 	 }
		 	 	 	 	 	 	 if($row['player1s'] == -1){
		 	 	 	 	 	 	 	 //player1 hasnt decided yet
		 	 	 	 	 	 	 	 $keyboard = [
		 	 	 	 	 	 	 	  'keyboard' => [
		 	 	 	 	 	 	 	   ['Cancel']
		 	 	 	 	 	 	 	  ],
		 	 	 	 	 	 	 	  'resize_keyboard' => true
		 	 	 	 	 	 	 	 ];
		 	 	 	 	 	 	 	 $keyboard = json_encode($keyboard);
		 	 	 	 	 	 	 	 botApi('sendMessage', [
		 	 	 	 	 	 	 	  'chat_id' => $id,
		 	 	 	 	 	 	 	  'text' => $p1n.' hasnt decided yet',
		 	 	 	 	 	 	 	  'reply_markup' => $keyboard
		 	 	 	 	 	 	 	 ]);
		 	 	 	 	 	 	 } else{
		 	 	 	 	 	 	 	 //player1 has already decided
		 	 	 	 	 	 	 	 $answer = compare($rps[$row['player1s']],$msg,$p1n,$p2n,$rps);
		 	 	 	 	 	 	 	 $keyboard = $startkey;
		 	 	 	 	 	 	 	 $keyboard = json_encode($keyboard);
		 	 	 	 	 	 	 	 botApi('sendMessage', [
		 	 	 	 	 	 	 	  'chat_id' => $id,
		 	 	 	 	 	 	 	  'text' => $answer,
		 	 	 	 	 	 	 	  'parse_mode' => 'Markdown',
		 	 	 	 	 	 	 	  'reply_markup' => $keyboard
		 	 	 	 	 	 	 	 ]);
		 	 	 	 	 	 	 	 botApi('sendMessage', [
		 	 	 	 	 	 	 	  'chat_id' => $row['player1'],
		 	 	 	 	 	 	 	  'text' => $answer,
		 	 	 	 	 	 	 	  'parse_mode' => 'Markdown',
		 	 	 	 	 	 	 	  'reply_markup' => $keyboard
		 	 	 	 	 	 	 	 ]);
		 	 	 	 	 	 	 	 if(!$mysqli->query("DELETE FROM rooms WHERE id=".$row['id'])){
		 	 	 	 	 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 	 	 	 	 }
		 	 	 	 	 	 	 	 if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$row['player2'])){
		 	 	 	 	 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 	 	 	 	 }
		 	 	 	 	 	 	 }
		 	 	 	 	 	 }
		 	 	 	 	 }
		 	 	 	 } else{
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 }
		 	 }
		 } else{
		 	 sendErr($id,$mysqli->error);
		 }
		 break;
		case '/cancel':
		case 'Cancel':
		 $keyboard = $startkey;
		 $keyboard = json_encode($keyboard);
		 if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$id)){
		 	 sendErr($id,$mysqli->error);
		 }
		 if($res = $mysqli->query("SELECT * FROM rooms WHERE player1=".$id." OR player2=".$id)){
		 	 while($row = $res->fetch_assoc()){
		 	 	 if($id == $row['player1']){
		 	 	 	 //player1 exit, so we send notification to player2
		 	 	 	 if(!$mysqli->query("DELETE FROM rooms WHERE id=".$row['id'])){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 	 if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$row['player1'])){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 	 if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$row['player2'])){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 	 botApi('sendMessage', [
		 	 	 	  'chat_id' => $row['player2'],
		 	 	 	  'text' => $row['player1Name'].' exit',
		 	 	 	  'reply_markup' => $keyboard
		 	 	 	 ]);
		 	 	 }
		 	 	 if($id == $row['player2']){
		 	 	 	 //player2 exit, so we send notification to player1
		 	 	 	 if(!$mysqli->query("DELETE FROM rooms WHERE id=".$row['id'])){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 	 if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$row['player1'])){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 	 if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$row['player2'])){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 	 botApi('sendMessage', [
		 	 	 	  'chat_id' => $row['player1'],
		 	 	 	  'text' => $row['player2Name'].' exit',
		 	 	 	  'reply_markup' => $keyboard
		 	 	 	 ]);
		 	 	 }
		 	 }
		 } else{
		 	 sendErr($id,$mysqli->error);
		 }
		 botApi('sendMessage', [
		  'chat_id' => $id,
		  'text' => 'Operation canceled',
		  'reply_markup' => $keyboard
		 ]);
		 break;
		case '/help':
		case 'Help':
		 $answer = 'This is a *Rock-Paper-Scissors GameBot*! You can play famous game with real users or with bot. To start a new game type /startgame. To see your settings type /settings. Then choose how to play.

*With real player*
You can host your own room or search for other players\' rooms.
  *Host*
  Just wait when another player joins your room
  *Search*
  You are able to search for another player\'s rooms
*With bot*
Bot will just choose random symbol. Then results will be compared

*Rules*
'.$rps[0].' beats '.$rps[1].'
'.$rps[1].' beats '.$rps[2].'
'.$rps[2].' beats '.$rps[0];
   botApi('sendMessage', [
    'chat_id' => $id,
    'text' => $answer,
    'parse_mode' => 'Markdown'
   ]);
		 break;
		case '/settings':
		case 'Settings':
		 if(!$mysqli->query("UPDATE users SET state=5 WHERE telegram=".$id)){
		 	 sendErr($id,$mysqli->error);
		 	 break;
		 }
		 if($res = $mysqli->query("SELECT * FROM users WHERE telegram=".$id)){
		 	 while($row = $res->fetch_assoc()){
		 	 	 $keyboard = [
		 	 	  'keyboard' => [
		 	 	   ['Sending notifications', 'Clear stats'],
		 	 	   ['About'],
		 	 	   ['Cancel']
		 	 	  ],
		 	 	  'resize_keyboard' => true
		 	 	 ];
		 	 	 $keyboard = json_encode($keyboard);
		 	 	 $sn = ($row['sendNotify']) ? 'Anybody' : 'Nobody';
		 	 	 $answer = '*Settings*
Here you can change your RPS account settings

*Who can send me notifications?* - _'.$sn.'_

*Stats*
*Total games played* - '.$row['games'];
      botApi('sendMessage', [
       'chat_id' => $id,
       'text' => $answer,
       'parse_mode' => 'Markdown',
       'reply_markup' => $keyboard
      ]);
		 	 }
		 } else{
		 	 sendErr($id,$mysqli->error);
		 }
		 break;
		case 'Sending notifications':
		 if($res = $mysqli->query("SELECT * FROM users WHERE telegram=".$id)){
		 	 while($row = $res->fetch_assoc()){
		 	 	 if($row['state'] == 5){
		 	 	 	 $set = ($row['sendNotify']) ? 0 : 1;
		 	 	 	 if(!$mysqli->query("UPDATE users SET sendNotify=".$set." WHERE telegram=".$id)){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 	 break;
		 	 	 	 }
		 	 	 	 $sn = ($row['sendNotify']) ? 'Nobody' : 'Anybody';
		 	 	  $answer = '*Settings*
Here you can change your RPS account settings

*Who can send me notifications?* - _'.$sn.'_

*Stats*
*Total games played* - '.$row['games'];
      botApi('sendMessage', [
       'chat_id' => $id,
       'text' => 'Settings changed',
       'parse_mode' => 'Markdown'
      ]);
      botApi('sendMessage', [
       'chat_id' => $id,
       'text' => $answer,
       'parse_mode' => 'Markdown'
      ]);
		 	 	 }
		 	 }
		 }
		 break;
		case 'Clear stats':
		 if($res = $mysqli->query("SELECT * FROM users WHERE telegram=".$id)){
		 	 while($row = $res->fetch_assoc()){
		 	 	 if($row['state'] == 5){
		 	 	 	 if(!$mysqli->query("UPDATE users SET games=0 WHERE telegram=".$id)){
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 	 $sn = ($row['sendNotify']) ? 'Nobody' : 'Anybody';
		 	 	  $answer = '*Settings*
Here you can change your RPS account settings

*Who can send me notifications?* - _'.$sn.'_

*Stats*
*Total games played* - 0';
      botApi('sendMessage', [
       'chat_id' => $id,
       'text' => 'Settings changed',
       'parse_mode' => 'Markdown'
      ]);
      botApi('sendMessage', [
       'chat_id' => $id,
       'text' => $answer,
       'parse_mode' => 'Markdown'
      ]);
		 	 	 }
		 	 }
		 } else{
		 	 sendErr($id,$mysqli->error);
		 }
		 break;
		case 'Send invitation to random user':
		 if($res = $mysqli->query("SELECT * FROM users WHERE telegram=".$id)){
		 	 while($row = $res->fetch_assoc()){
		 	 	 if($row['state'] == 4){
		 	 	 	 if($r = $mysqli->query("SELECT * FROM users WHERE sendNotify=1 AND telegram != ".$id." ORDER BY RAND() LIMIT 1")){
		 	 	 	 	 if($r->num_rows == 0){
		 	 	 	 	 	 $answer = 'It seems like nobody wants to get notifications. You can *play with bot*.';
		 	 	 	 	 	 botApi('sendMessage', [
		 	 	 	 	 	  'chat_id' => $id,
		 	 	 	 	 	  'text' => $answer,
		 	 	 	 	 	  'parse_mode' => 'Markdown'
		 	 	 	 	 	 ]);
		 	 	 	 	 }
		 	 	 	 	 while($ro = $r->fetch_assoc()){
		 	 	 	 	 	 if(!$mysqli->query("INSERT INTO `invites` (`fromid`,`toid`,`name`) VALUES (".$row['telegram'].",".$ro['telegram'].", '".$row['name']."')")){
		 	 	 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 	 	 }
		 	 	 	 	 	 $keyboard = [
		 	 	 	 	 	  'keyboard' => [
		 	 	 	 	 	   ['Yes','No']
		 	 	 	 	 	  ],
		 	 	 	 	 	  'resize_keyboard' => true
		 	 	 	 	 	 ];
		 	 	 	 	 	 $keyboard = json_encode($keyboard);
		 	 	 	 	 	 $answer = $row['name'].' invites you to play. Do you accept?';
		 	 	 	 	 	 botApi('sendMessage', [
		 	 	 	 	 	  'chat_id' => $id,
		 	 	 	 	 	  'text' => 'Your invitation sent to '.$ro['name']
		 	 	 	 	 	 ]);
		 	 	 	 	 	 botApi('sendMessage', [
		 	 	 	 	 	  'chat_id' => $ro['telegram'],
		 	 	 	 	 	  'text' => $answer,
		 	 	 	 	 	  'reply_markup' => $keyboard
		 	 	 	 	 	 ]);
		 	 	 	 	 }
		 	 	 	 } else{
		 	 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 }
		 	 	 }
		 	 }
		 } else{
		 	 sendErr($id,$mysqli->error);
		 }
		 break;
		case 'Yes':
		 if($res = $mysqli->query("SELECT * FROM invites WHERE toid=".$id)){
		 	 while($row = $res->fetch_assoc()){
		 	 	 $keyboard = [
		 	 	  'keyboard' => [
		 	 	   $rps,
		 	 	   ['Cancel']
		 	 	  ],
		 	 	  'resize_keyboard' => true
		 	 	 ];
		 	 	 $keyboard = json_encode($keyboard);
		 	 	 if(!$mysqli->query("INSERT INTO rooms (`player1`,`player2`,`player1Name`,`player2Name`) VALUES (".$row['fromid'].", ".$row['toid'].",'".$row['name']."', '".$data->message->from->first_name."')")){
		 	 	 	 sendErr($id,$mysqli->error);
		 	 	 	 	break;
		 	 	 	}
		 	 	 	if(!$mysqli->query("UPDATE users SET state=2 WHERE telegram=".$row['fromid'])){
		 	 	 		sendErr($id,$mysqli->error);
		 	 	 		break;
		 	 	 	}
		 	 	 	if(!$mysqli->query("UPDATE users SET state=2 WHERE telegram=".$row['toid'])){
		 	 	 		sendErr($id,$mysqli->error);
		 	 	 		break;
		 	 	 	}
		 	 	 	botApi('sendMessage', [
		 	 	 	 'chat_id' => $row['fromid'],
		 	 	 	 'text' => $data->message->from->first_name.' accepted your invitation. Send a symbol',
		 	 	 	 'reply_markup' => $keyboard
		 	 	 	]);
		 	 	 	botApi('sendMessage', [
		 	 	 	 'chat_id' => $row['toid'],
		 	 	 	 'text' => 'Send a symbol',
		 	 	 	 'reply_markup' => $keyboard
		 	 	 	]);
		 	 	 	if(!$mysqli->query("DELETE FROM invites WHERE id=".$row['id'])){
		 	 	 		sendErr($id,$mysqli->error);
		 	 	 	}
		 	 }
		 } else{
		 	 sendErr($id,$mysqli->error);
		 }
		 break;
		case 'No':
		 if($res = $mysqli->query("SELECT * FROM invites WHERE toid=".$id)){
		 	 while($row = $res->fetch_assoc()){
		 	 	 $keyboard = json_encode($startkey);
		 	 	 botApi('sendMessage', [
		 	 	  'chat_id' => $row['fromid'],
		 	 	  'text' => $data->message->from->first_name.' denied your invitation'
		 	 	 ]);
		 	 	 botApi('sendMessage', [
		 	 	  'chat_id' => $row['toid'],
		 	 	  'text' => 'OK',
		 	 	  'reply_markup' => $keyboard
		 	 	 ]);
		 	 	 if(!$mysqli->query("DELETE FROM invites WHERE id=".$row['id'])){
		 	 	 	 sendErr($id,$mysqli->error);
		 	 	 }
		 	 }
		 } else{
		 	 sendErr($id,$mysqli->error);
		 }
		 break;
		case 'About':
		 $answer = '*About*
'.NAME.' - '.VERSION.'
Author: [Vladimir Aksenov](https://vk.com/aks03vova)
GitHub: [Source code](https://github.com/v0vadev/rpsbot)';
   botApi('sendMessage', [
    'chat_id' => $id,
    'text' => $answer,
    'parse_mode' => 'Markdown'
   ]);
		 break;
		default:
		 sendErr($id,'unknown command');
		 break;
	}
}
function botApi($method, $params){
	$params = http_build_query($params);
	return json_decode(file_get_contents('https://api.telegram.org/bot'.TOKEN.'/'.$method.'?'.$params));
}
function choose(){
	return $rps[rand(0,2)];
}
function compare($p1,$p2,$p1Name,$p2Name,$rps){
	if($p1 == $p2){
		return $p1Name.' and '.$p2Name.' chose the same - '.$p1.'.
*Nobody wins*';
	}
	if(($p1 == $rps[0] && $p2 == $rps[1]) || ($p1 == $rps[1] && $p2 == $rps[2]) || ($p1 == $rps[2] && $p2 == $rps[0])){
		return $p1Name.' chose '.$p1.' and '.$p2Name.' chose '.$p2.'.
So *'.$p1Name.' wins*';
	}
	if(($p2 == $rps[0] && $p1 == $rps[1]) || ($p2 == $rps[1] && $p1 == $rps[2]) || ($p2 == $rps[2] && $p1 == $rps[0])){
		return $p1Name.' chose '.$p1.' and '.$p2Name.' chose '.$p2.'.
So *'.$p2Name.' wins*';
	}
}
function sendErr($id,$err){
	botApi('sendMessage', [
	 'chat_id' => $id,
	 'text' => 'Error: '.$err
	]);
}
?>