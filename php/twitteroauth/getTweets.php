<?php
session_start();
require_once("twitteroauth.php"); //Path to twitteroauth library
 
$twitteruser = "treeptikTeam";
$notweets = 5;
$consumerkey = "1nyDpUOo3Ly8S0lHe0b9w";
$consumersecret = "8UI4OaaBa17QB9Arezn212ayjce7NBHFL7c1Qa1Ko";
$accesstoken = "333455889-mrckSlNrLAceexMS9AFD2efk52isbvn5q1VDMCBc";
$accesstokensecret = "Xp0nQ4ABvkUTMq6hrMrVedgxYldZIEVySJp37rklT4F8N";
 
function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
  return $connection;
}
 
$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
 
$tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets);
 
echo json_encode($tweets);
?>