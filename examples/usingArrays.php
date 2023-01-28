<?php

use Mateodioev\Json\JSON;

require __DIR__.'/../vendor/autoload.php';

class User {
	public int $id;
	public string $name;
	public string $username;
	public array $products;
}

$rawJson = '{
	"id": 1,
	"name": "John Doe",
	"username": "johndoe",
	"products": [
		{
			"id": 1,
			"name": "Product 1",
			"price": 10.99
		},
		{
			"id": 2,
			"name": "Product 2",
			"price": 20.99
		}
	]
}';

$u = new User;

JSON::new($rawJson)->decode($u);
var_dump($u);