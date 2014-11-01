<?php

date_default_timezone_set('America/Chicago');

require __DIR__ . '/vendor/autoload.php';

try {
  if (!file_exists($argv[1])) {
    throw new Exception('Missing config file');
  }
  
  $config = file_get_contents($argv[1]);
  $config = json_decode($config);
} catch (Exception $ex) {
  die('Error loading configuration.');
}

try {
  $twitter = new Twitter($config->twitter->consumerKey, $config->twitter->consumerSecret, $config->twitter->accessToken, $config->twitter->accessTokenSecret);
  
  if (!$twitter->authenticate()) {
    throw new Exception('Invalid Twitter info');
  }
} catch (Exception $ex) {
  die('Invalid Twitter auth');
}

$a = new DateTime('now');
$b = new DateTime($config->date);

$change = $a->diff($b);
$days = $change->days;

if ($days === 0) {
  $message = $config->formats->yep[mt_rand(0, count($config->formats->yep) - 1)];
} else if ($days < 0) {
  $message = $config->formats->old[mt_rand(0, count($config->formats->old) - 1)];
} else {
  $message = $config->formats->not[mt_rand(0, count($config->formats->not) - 1)];
}

$message = str_replace('DAYS_LEFT', $days, $message);

try {
  $twitter->send($message);
} catch (TwitterException $ex) {
  die('Twitter is not happy');
}