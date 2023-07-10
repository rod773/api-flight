<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Users
{
    private $db;


    public function __construct()
    {
        Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=spending_tracker', 'root', ''), function ($db) {
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        });

        $this->db = Flight::db();
    }

    //************************************************* */

    public function selectAll()
    {

        if (!$this->validateToken()) {
            Flight::halt(403, json_encode([
                "error" => 'Unauthorized',
                "status" => "error"
            ]));
        }



        $sql = "select * from usuarios";

        $query = $this->db->prepare($sql);

        $query->execute();

        $data = $query->fetchAll();

        $array = [];

        foreach ($data as $row) {
            $array[] = [
                "id" => $row['id'],
                "name" => $row['nombre'],
                "email" => $row['correo'],
                "phone" => $row['telefono'],
                "status" => $row['status'],
                "rol" => $row['rol_id'],
            ];
        }



        Flight::json([
            "total rows" => $query->rowCount(),
            "rows" => $array,
        ]);
    }

    //***************************************************** */

    public function selectOne($id)
    {


        if (!$this->validateToken()) {
            Flight::halt(403, json_encode([
                "error" => 'Unauthorized',
                "status" => "error"
            ]));
        }



        $sql = "select * from usuarios where id = :id";

        $query = $this->db->prepare($sql);

        $query->bindValue(":id", $id, PDO::PARAM_INT);

        $query->execute();

        $data = $query->fetch();


        $array = [
            "id" => $data['id'],
            "name" => $data['nombre'],
            "email" => $data['correo'],
            "phone" => $data['telefono'],
            "status" => $data['status'],
            "rol" => $data['rol_id'],
        ];




        Flight::json($array);
    }

    //*************************************** */
    public function insert()
    {

        if (!$this->validateToken()) {
            Flight::halt(403, json_encode([
                "error" => 'Unauthorized',
                "status" => "error"
            ]));
        }



        $request_data = json_decode(file_get_contents("php://input"), true);

        $name = $request_data['name'];
        $phone = $request_data['phone'];
        $password = $request_data['password'];
        $email = $request_data['email'];



        $sql = "insert into spending_tracker.usuarios (correo,password,telefono,nombre) values (:email,:password,:phone,:name)";

        $query = $this->db->prepare($sql);

        $query->bindValue(":name", $name, PDO::PARAM_STR);
        $query->bindValue(":phone", $phone, PDO::PARAM_INT);
        $query->bindValue(":password", $password, PDO::PARAM_STR);
        $query->bindValue(":email", $email, PDO::PARAM_STR);



        $array = [
            "error" => "error al insertar",
            "status" => "error"
        ];

        if ($query->execute()) {
            $array = [
                $data = [
                    "id" => $this->db->lastInsertId(),
                    "name" => $name,
                    "phone" => $phone,
                    "password" => $password,
                    "email" => $email,
                ],

                "status" => "success"

            ];
        }

        Flight::json($array);
    }


    //*********************************************** */

    public function update()
    {

        if (!$this->validateToken()) {
            Flight::halt(403, json_encode([
                "error" => 'Unauthorized',
                "status" => "error"
            ]));
        }



        $request_data = json_decode(file_get_contents("php://input"), true);

        $id = $request_data['id'];
        $name = $request_data['name'];
        $phone = $request_data['phone'];
        $password = $request_data['password'];
        $email = $request_data['email'];

        $sql = "update usuarios set 
                correo=:email,
                password=:password,
                telefono=:phone,
                nombre=:name 
                where id=:id";


        $query = $this->db->prepare($sql);

        $query->bindValue(":id", $id, PDO::PARAM_INT);
        $query->bindValue(":name", $name, PDO::PARAM_STR);
        $query->bindValue(":phone", $phone, PDO::PARAM_INT);
        $query->bindValue(":password", $password, PDO::PARAM_STR);
        $query->bindValue(":email", $email, PDO::PARAM_STR);



        $array = [
            "error" => "error al actualizr",
            "status" => "error"
        ];

        if ($query->execute()) {
            $array = [
                "data" => [
                    "id" => $id,
                    "name" => $name,
                    "phone" => $phone,
                    "password" => $password,
                    "email" => $email,
                ],

                "status" => "success"

            ];
        }

        Flight::json($array);
    }


    //**********************************************
    public function delete()
    {

        if (!$this->validateToken()) {
            Flight::halt(403, json_encode([
                "error" => 'Unauthorized',
                "status" => "error"
            ]));
        }



        $id = Flight::request()->data->id;


        $sql = "delete from usuarios where id=:id";


        $query = $this->db->prepare($sql);

        $query->bindValue(":id", $id, PDO::PARAM_INT);




        $array = [
            "error" => "error al borrar",
            "status" => "error"
        ];

        if ($query->execute()) {
            $array = [
                "data" => [
                    "id" => $id,

                ],

                "status" => "success"

            ];
        }

        Flight::json($array);
    }

    //************************************************* */


    public function auth()
    {



        $request_data = json_decode(file_get_contents("php://input"), true);


        $password = $request_data['password'];
        $email = $request_data['email'];


        $sql = "select * from spending_tracker.usuarios where  correo = :email and password = :password";

        $query = $this->db->prepare($sql);

        $query->bindValue(
            ":password",
            $password,
            PDO::PARAM_STR
        );
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
    }

    //******************************************** */

    public function getToken()
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

    public function validateToken()
    {
        $info = $this->getToken();
        $db = Flight::db();
        $sql = "select * from usuarios where id = :id";
        $query = $db->prepare($sql);
        $query->bindValue(":id", $info->data, PDO::PARAM_INT);
        $query->execute();
        $rows = $query->fetchColumn();
        return $rows;
    }
}
