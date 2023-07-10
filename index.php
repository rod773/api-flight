<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Authorization, Origin');
header('Access-Control-Allow-Methods: *');



require 'vendor/autoload.php';




$users = new Users();









Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=spending_tracker', 'root', ''), function ($db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
});



Flight::route('GET /users', [$users, 'selectAll']);

Flight::route('GET /users/@id', [$users, 'selectOne']);

Flight::route('POST /users', [$users, 'insert']);

Flight::route('PUT /users', [$users, 'update']);

Flight::route('DELETE /users', [$users, 'delete']);

Flight::route('GET /auth', [$users, 'auth']);



Flight::start();
