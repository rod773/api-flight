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


Flight::route('GET /users/@id', function($id){

    $db = Flight::db();

    $sql = "select * from usuarios where id = :id";

    $query = $db->prepare($sql);

    $query->bindValue(":id",$id,PDO::PARAM_INT);

    $query->execute();

    $data = $query->fetch();

  
    $array = [
        "id"=>$data['id'],
        "name"=>$data['nombre'],
        "email"=>$data['correo'],
        "phone"=>$data['telefono'],
        "status"=>$data['status'],
        "rol"=>$data['rol_id'],
    ];




    Flight :: json($array);
});


Flight::route('POST /users', function(){

    $db = Flight::db();

    $name = Flight::request()->data->name;
    $phone = Flight::request()->data->phone;
    $password = Flight::request()->data->password;
    $email = Flight::request()->data->email;

    $sql = "insert into usuarios (correo,password,telefono,nombre)
      values (:email,:password,:phone,:name)";

    $query = $db->prepare($sql);

    $query->bindValue(":name",$name,PDO::PARAM_STR);
    $query->bindValue(":phone",$phone,PDO::PARAM_INT);
    $query->bindValue(":password",$password,PDO::PARAM_STR);
    $query->bindValue(":email",$email,PDO::PARAM_STR);

    

    $array = [
        "error"=>"error al insertar",
        "status"=>"error"
    ];

    if($query->execute()===true){
        $array = [
            $data = [
                "id"=>$db->lastInsertId(),
                "name"=>$name,
                "phone"=>$phone,
                "password"=>$password,
                "email"=>$email,
            ],

            "status"=>"success"
       
        ];
    }
    
    Flight :: json($array);
});



Flight::start();