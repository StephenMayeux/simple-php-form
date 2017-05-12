<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$resp = array('ok' => 0, 'msg' => 'You must make a POST request');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $resp = post_name();
}

send_response($resp);

function post_name() {

  $resp = array('ok' => 0);
  $entityBody = file_get_contents('php://input');
  $json = json_decode($entityBody);

  if (!$json->{'name'}) {
    $resp['error'] = "missing name in req body";
    return $resp;
  }

  $db = NULL;
  try {
    $db = new PDO("mysql:dbname=SimpleName;host=localhost:8889", "root", "root");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch(PDOException $e) {
    $resp['error'] = "Database error {$e->getCode()}: {$e->getMessage()}";
    $db = NULL;
    send_response($resp);
  }

  try {
    $db->beginTransaction();

    $sql ="INSERT INTO names (name)
    VALUES (:name)
    ON DUPLICATE KEY
    UPDATE name=:name";

    $stmt = $db->prepare($sql);

    $stmt->execute(array(
      ":name" => $json->{'name'}
    ));

    $db->commit();

    $resp['ok'] = 1;
    $resp['msg'] = "success saving name to db";

  }
  catch (PDOException $e) {
    $db->rollBack();
    $resp['error'] = "error writing name to db: {$e->getMessage()}";
  }

  $resp['ok'] = 1;
  $resp['msg'] = "success posting name";
  return $resp;
}

function send_response($resp) {
  if (isset($resp['ok'])) {
    send_response_code($resp, 200);
  }
  else {
    $status_code = isset($resp['status_code']) ? $resp['status_code'] : 500;
    unset($resp['status_code']);
    send_response_code($resp, $status_code);
  }
}

function send_response_code($resp, $code) {
  header("Content-Type: application/json;charset=utf-8");
  header("Access-Control-Allow-Origin: *");
  http_response_code($code);
  echo(json_encode($resp));
  exit();
}

?>
