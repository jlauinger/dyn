<?php

require 'vendor/autoload.php';
require '.htconfig.php';
require 'database.php';

$app = new \Slim\Slim();

function json ($data) {
  global $app;
  $app->response->headers->set('Content-Type', 'application/json');
  $app->response->write(json_encode($data, JSON_PRETTY_PRINT));
}

$app->get('/', function () {
  $db = new Database();
  $sth = $db->prepare('SELECT id, hostname, description, ip, changed FROM hosts');
  $sth->execute();
  json($sth->fetchAll(PDO::FETCH_ASSOC));
});

$app->get('/ip', function () {
  if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    echo $_SERVER["HTTP_X_FORWARDED_FOR"];
  } else {
    echo $_SERVER["REMOTE_ADDR"];
  }
});

$app->post('/', function () use ($app) {
  $data = json_decode($app->request->getBody());
  if (!isset($data) || !isset($data->ip) || !isset($data->hostname) || !isset($data->client_secret)) {
    json(array("error" => "Required data is missing."));
    return;
  }
  $db = new Database();
  $sth = $db->prepare('UPDATE hosts SET ip = :ip, changed = CURRENT_TIMESTAMP WHERE hostname = :hostname AND client_secret = :client_secret');
  $sth->execute(array(
    ":ip" => $data->ip,
    ":hostname" => $data->hostname,
    ":client_secret" => $data->client_secret
  ));
  if ($sth->rowCount() == 1) {
    json(array("success" => "updated IP address for ".$data->hostname));
  } else {
    json(array("error" => "IP address not updated"));
  }
});

$app->run();
