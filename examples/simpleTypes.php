<?php

use Mateodioev\Json\JSON;

require __DIR__.'/../vendor/autoload.php';


// Single class example
class User {
	public int $id;
	public string $name;
	public string $username;
}

// JSON string
$rawJson = '{
	"id": 1,
	"name": "John Doe",
	"username": "johndoe"
}';

$u = new User;

// Decode JSON string to User object
try {
    JSON::new($rawJson)->decode($u);
} catch (\Mateodioev\Json\JsonDecodeException|ReflectionException $e) {
}


var_dump($u);