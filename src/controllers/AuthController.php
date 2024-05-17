<?php
require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;

class AuthController {
    private $db;
    private $jwt_key;

    public function __construct($db) {
        $this->db = $db;
        $this->jwt_key = getenv('JWT_SECRET');
    }

    public function register($username, $password) {
        $user = new User($this->db);
        $user->username = $username;
        $user->password = password_hash($password, PASSWORD_BCRYPT);
        
        if($user->create()) {
            return json_encode(["message" => "User was created."]);
        } else {
            return json_encode(["message" => "Unable to create user."]);
        }
    }

    public function login($username, $password) {
        $user = new User($this->db);
        $user->username = $username;
        
        if($user->read()) {
            if(password_verify($password, $user->password)) {
                $token = [
                    "iss" => "your-issuer",
                    "aud" => "your-audience",
                    "iat" => time(),
                    "exp" => time() + 3600,
                    "data" => [
                        "id" => $user->id,
                        "username" => $user->username
                    ]
                ];
                $jwt = JWT::encode($token, $this->jwt_key, 'HS256');
                return json_encode(["message" => "Successful login.", "jwt" => $jwt]);
            } else {
                return json_encode(["message" => "Login failed."]);
            }
        } else {
            return json_encode(["message" => "User not found."]);
        }
    }
}
