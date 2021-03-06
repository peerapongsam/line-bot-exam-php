<?php
define("LINEAPI","");
define("MESSAGE","send message from php");
define("TOKEN","rVWPHXpSYSmTmBwdTp83wtMgExtD4tLDrhAVguoQGBA");

$token = (isset($_GET['token'])) ? $_GET['token'] : "rVWPHXpSYSmTmBwdTp83wtMgExtD4tLDrhAVguoQGBA";

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
  push($events, $message);
} else if ($events['object_kind'] == 'pipeline') {
  pipeline($events, $message);
} else {
  $message = "";
}

if ($message != "") {
  echo send_notify($message, $token);
}

function pipeline($events, &$message) {
  if ($events['object_attributes']['status'] == 'success' || $events['object_attributes']['status'] == 'failed') {
    //developer/theandroid: Pipeline #605 of branch release/2.3.0 by Peerapong Samarnpong (peerapongsam) passed in 12:07
    if ($events['object_attributes']['status'] == 'success') {
      $message .= "✔ ";
    } else {
      $message .= "❌ ";
    }
    $message .= $events['project']['path_with_namespace'] . ": Pipeline #" . $events['object_attributes']['id'] . " of ";
      if ($events['object_attributes']['tag']) {
        $message .=   "tag ";
      } else {
        $message .=   "branch ";
      }
    $message .= $events['object_attributes']['ref'];
    $message .= " By " . $events['user']['name'] . " (" . $events['user']['username'] . ")";
    $message .= " " . $events['object_attributes']['status'];
    if ($events['object_attributes']['duration'] != null) {
      $message .= " in " . convert_to_string_time($events['object_attributes']['duration']);
    }
    $message .= "\n" . $events['project']['web_url'] . "/pipelines/" . $events['object_attributes']['id'];
  }  else {
    $message = "";
  }
}

function push($events, &$message) {
  if ($events['after'] == '0000000000000000000000000000000000000000') {
    $message .= "🔴 " . $events['user_name'] . " deleted branch " . str_replace('refs/heads/', '', $events['ref']) . " from " . $events['project']['path_with_namespace'];
  } else {
    $commits = $events['total_commits_count'];
    if ($events['total_commits_count'] == 1) {
      $commits .= ' commit';
    } else {
      $commits .= ' commits';
    }
    if ($events['before'] == '0000000000000000000000000000000000000000') {
      $message .= "⚪ " . $events['user_name'] . " created branch " . str_replace('refs/heads/', '', $events['ref']) . " with " . $commits . " at " . $events['project']['path_with_namespace'];
    } else {
      $message .= "🔵 " . $events['user_name'] . " pushed " . $commits . " to branch " . str_replace('refs/heads/', '', $events['ref']) . " at " . $events['project']['path_with_namespace'];
    }
    $commits = $events['commits'];
    $url = "";
    if (count($commits) > 0) {
      $last = (count($commits) - 1);
      if (count($commits) > 1) {
        $message .= "\n- " . ucfirst(ltrim($commits[$last]['message'], '- '));
        $message .= "... and " . (count($commits) - 1) . " more commit(s)";
        if ($events['before'] == '0000000000000000000000000000000000000000') {
          //http://git.devpantip.com/developer/theandroid/commit/208328d5721f4211b88b39fa25bd16f2f5e41357
          $message .= "\n- " . ucfirst(ltrim($commits[$last]['message'], '- '));
          $url = $events['repository']['homepage'] . "/commit/" . $events['after'];
        } else {
          //http://git.devpantip.com/developer/theandroid/compare/71c105ed2868ecd1e5c437a98b936402152fa36f...daa400979464bad816db27e5f58aaaefe7493f9b
          $url = $events['repository']['homepage'] .  "/compare/" . $events['before'] . "..." . $events['after'];
        }
      } else {
        //http://git.devpantip.com/developer/theandroid/commit/208328d5721f4211b88b39fa25bd16f2f5e41357
        $message .= "\n- " . ucfirst(ltrim($commits[$last]['message'], '- '));
        $url = $events['repository']['homepage'] . "/commit/" . $events['after'];
      }
    }
    if ($url != "") {
      $message .= "\n" . $url;
    }
  }
}

function convert_to_string_time($duration) {
  $result = ltrim( sprintf( '%02dh%02dm%02ds', floor( $duration / 3600 ), floor( ( $duration / 60 ) % 60 ), ( $duration % 60 ) ), '0hm' );
  if( $result == 's' ) { $result = '0s'; }
  return $result;
}

function send_notify($message, $token) {
    $data = array("message" => $message);
    $data = http_build_query($data,'','&');
    $headerOptions = array(
      'http'=>array(
        'method'=>'POST',
        'header'=> "Content-Type: application/x-www-form-urlencoded\r\n"
                   ."Authorization: Bearer " . $token . "\r\n"
                   ."Content-Length: ".strlen($data)."\r\n",
         'content' => $data
       ),
     );
    $context = stream_context_create($headerOptions);
    return file_get_contents("https://notify-api.line.me/api/notify", false, $context);
}
 