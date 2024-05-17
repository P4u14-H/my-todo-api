<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;

class TodoController {
    private $db;
    private $jwt_key;

    public function __construct($db) {
        $this->db = $db;
        $this->jwt_key = getenv('JWT_SECRET');
    }

    private function authenticate($jwt) {
        try {
            $decoded = JWT::decode($jwt, $this->jwt_key, 'HS256');
            var_dump($decoded);
            return $decoded->data->id;
        } catch (Exception $e) {
            return null;
        }
    }

    public function create($jwt, $title, $description) {
        $user_id = $this->authenticate($jwt);
        if (!$user_id) {
            return json_encode(["message" => "Access denied."]);
        }

        $todo = new Todo($this->db);
        $todo->title = $title;
        $todo->description = $description;
        $todo->user_id = $user_id;

        if ($todo->create()) {
            return json_encode(["message" => "ToDo created."]);
        } else {
            return json_encode(["message" => "Unable to create ToDo."]);
        }
    }

    public function read($jwt) {
        $user_id = $this->authenticate($jwt);
        if (!$user_id) {
            return json_encode(["message" => "Access denied."]);
        }

        $todo = new Todo($this->db);
        $stmt = $todo->readByUser($user_id);
        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return json_encode($todos);
    }

    public function update($jwt, $id, $title, $description, $status) {
        $user_id = $this->authenticate($jwt);
        if (!$user_id) {
            return json_encode(["message" => "Access denied."]);
        }

        $todo = new Todo($this->db);
        $todo->id = $id;
        $todo->title = $title;
        $todo->description = $description;
        $todo->status = $status;
        $todo->user_id = $user_id;

        if ($todo->update()) {
            return json_encode(["message" => "ToDo updated."]);
        } else {
            return json_encode(["message" => "Unable to update ToDo."]);
        }
    }

    public function delete($jwt, $id) {
        $user_id = $this->authenticate($jwt);
        if (!$user_id) {
            return json_encode(["message" => "Access denied."]);
        }

        $todo = new Todo($this->db);
        $todo->id = $id;
        $todo->user_id = $user_id;

        if ($todo->delete()) {
            return json_encode(["message" => "ToDo deleted."]);
        } else {
            return json_encode(["message" => "Unable to delete ToDo."]);
        }
    }
}
