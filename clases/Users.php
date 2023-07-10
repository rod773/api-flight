<?php


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

    public function selectAll()
    {

        if (!validateToken()) {
            Flight::halt(403, json_encode([
                "error" => 'Unauthorized',
                "status" => "error"
            ]));
        }

        $db = Flight::db();

        $sql = "select * from usuarios";

        $query = $db->prepare($sql);

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



    public function selectOne($id)
    {


        if (!validateToken()) {
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
}
