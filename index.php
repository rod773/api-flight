<?php


require 'vendor/autoload.php';

Flight::register('db','PDO',array('mysql:host=localhost;dbname=spending_tracker','root',''));

Flight::route('GET /users', function(){

    $db = Flight::db();

    $sql = "select * from usuarios";

    $query = $db->prepare($sql);

    $query->execute();

    $data = $query->fetchAll();

    $array = [];

    foreach ($data as $row) {
        $array[] = [
            "id"=>$row['id'],
            "name"=>$row['nombre'],
            "email"=>$row['correo'],
            "phone"=>$row['telefono'],
            "status"=>$row['status'],
            "rol"=>$row['rol_id'],
        ];
    }



    Flight :: json([
        "total rows"=>$query->rowCount(),
        "rows"=>$array
    ]);
});


Flight::start();