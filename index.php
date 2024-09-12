<?php
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Credentials: true');
  header('Content-Type: application/json; charset=UTF-8');
  header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE');
  header('Access-Control-Allow-Headers: Content-Type,Access-Control-Allow-Headers,Authorization,X-Requested-With');

  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
  }
