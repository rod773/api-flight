<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Authorization, Origin');
header('Access-Control-Allow-Methods: *');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require 'vendor/autoload.php';




$users = new Users();


function getToken()
{
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        Flight::halt(403, json_encode([
            "error" => "Unauthenticated request",
            "status" => "error"
        ]));
    }
    $authorization = $headers["Authorization"];
    $authorizationArray = explode(" ", $authorization);
    $token = $authorizationArray[1];
    $key = 'example_key';

    try {
        return JWT::decode($token, new Key($key, 'HS256'));
    } catch (Throwable $th) {
        Flight::halt(403, json_encode([
            "error" => $th->getMessage(),
            "status" => "error"
        ]));
    }
}


function validateToken()
{
    $info = getToken();
    $db = Flight::db();
    $sql = "select * from usuarios where id = :id";
    $query = $db->prepare($sql);
    $query->bindValue(":id", $info->data, PDO::PARAM_INT);
    $query->execute();
    $rows = $query->fetchColumn();
    return $rows;
}



Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=spending_tracker', 'root', ''), function ($db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
});



Flight::route('GET /users', [$users, 'selectAll']);

Flight::route('GET /users/@id', [$users, 'selectOne']);

Flight::route('POST /users', [$users, 'insert']);

Flight::route('PUT /users', [$users, 'update']);



Flight::route('DELETE /users',);



Flight::route('GET /auth', function () {

    $db = Flight::db();

    $request_data = json_decode(file_get_contents("php://input"), true);


    $password = $request_data['password'];
    $email = $request_data['email'];


    $sql = "select * from spending_tracker.usuarios where  correo = :email and password = :password";

    $query = $db->prepare($sql);

    $query->bindValue(":password", $password, PDO::PARAM_STR);
    $query->bindValue(":email", $email, PDO::PARAM_STR);


    $array = [
        "error" => "no se pudo validad identidad",
        "status" => "error"
    ];

    if ($query->execute()) {

        $user = $query->fetch();

        $now = strtotime('now');

        $key = 'example_key';

        $payload = [
            'exp' => $now + 3600,
            'data' => $user['id'],

        ];


        $jwt = JWT::encode($payload, $key, 'HS256');

        $array = [
            "token" => $jwt
        ];
    }

    Flight::json($array);
});



Flight::start();
