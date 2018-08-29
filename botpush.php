<?php



require "vendor/autoload.php";

$access_token = 'V9STYPKw1lj1QkwhDOzjFws9nL3vGYsyC+atEO+1IXagdD28oG6Q0qpdIObLfRnhnXe1f/sm1zEsRb0POsJz2lD68sOm145L1Ny9EsZDo7bS2gLQLGIXDT7Hrak28OHjTj8PMd8zfpvzsycCg4O9YAdB04t89/1O/w1cDnyilFU=';

$channelSecret = '2c889b8cbcbcf88fe77bc710164f058b';

$pushID = isset($_GET['userId']) ? $_GET['userId'] : 'Uc8ef1520e9fd27b5e7d63236edf3245a';

// Get POST body content
$content = file_get_contents('php://input');
// Parse JSON
$events = json_decode($content, true);

$message = "";

if ($events['project']['name'] == 'theandroid') {
  $message .= "🍰 ";
} else if ($events['project']['name'] == 'theios2.0swift4') {
  $message .= "🍎 ";
}

if ($events['object_kind'] == 'push') {
  if ($events['after'] == '0000000000000000000000000000000000000000') {
    $message .= "🔴 " . $events['user_name'] . " deleted branch " . str_replace('refs/heads/', '', $events['ref']) . " from " . $events['project']['path_with_namespace'];
  } else {
    $commits = $events['total_commits_count'];
    if ($events['total_commits_count'] == 1) {
      $commits .= ' commit';
    } else {
      $commits .= ' commits';
    }
    $message .= "🔵 " . $events['user_name'] . " pushed " . $commits . " to branch " . str_replace('refs/heads/', '', $events['ref']) . " to " . $events['project']['path_with_namespace'];
  }
  //$commits = $events['commits'];
  //if (count($commits) > 0) {
  //  foreach($commits as $k => $commit) {
  //    $message .= "\n  - " .substr($commit['id'], 0, 8) . ": " . $commit['message'] . " By " . $commit['author']['name'] ;
  //  }
  //}
} else if ($events['object_kind'] == 'pipeline') {
  if ($events['object_attributes']['status'] == 'success' || $events['object_attributes']['status'] == 'failed') {
    //developer/theandroid: Pipeline #605 of branch release/2.3.0 by Peerapong Samarnpong (peerapongsam) passed in 12:07
    if ($events['object_attributes']['status'] == 'success') {
      $message .= "✔ ";
    } else {
      $message .= "❌ ";
    }
    $message .= $events['project']['path_with_namespace'] . ": Pipeline #" . $events['object_attributes']['id'] . " of branch " . $events['object_attributes']['ref'];
    $message .= " By " . $events['user']['name'] . " (" . $events['user']['username'] . ")";
    $message .= " " . $events['object_attributes']['status'];
    if ($events['object_attributes']['duration'] != null) {
      $message .= " in " . convert_to_string_time($events['object_attributes']['duration']);
    }
  }  else {
    $message = "";
  }
} else {
  $message = "";
}

if ($message != "") {

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);

$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
$response = $bot->pushMessage($pushID, $textMessageBuilder);

echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
} else {
  echo "200 OK";
}

function convert_to_string_time($duration) {
  $result = ltrim( sprintf( '%02dh%02dm%02ds', floor( $duration / 3600 ), floor( ( $duration / 60 ) % 60 ), ( $duration % 60 ) ), '0hm' );
  if( $result == 's' ) { $result = '0s'; }
  return $result;
}
