<?php



require "vendor/autoload.php";

$access_token = 'V9STYPKw1lj1QkwhDOzjFws9nL3vGYsyC+atEO+1IXagdD28oG6Q0qpdIObLfRnhnXe1f/sm1zEsRb0POsJz2lD68sOm145L1Ny9EsZDo7bS2gLQLGIXDT7Hrak28OHjTj8PMd8zfpvzsycCg4O9YAdB04t89/1O/w1cDnyilFU=';

$channelSecret = '2c889b8cbcbcf88fe77bc710164f058b';

$pushID = 'Uc8ef1520e9fd27b5e7d63236edf3245a';

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);

$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello world');
$response = $bot->pushMessage($pushID, $textMessageBuilder);

echo $response->getHTTPStatus() . ' ' . $response->getRawBody();