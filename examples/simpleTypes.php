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
JSON::new($rawJson)->decode($u);


var_dump($u);