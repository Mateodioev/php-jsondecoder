<?php

use Mateodioev\Json\JSON;

require __DIR__.'/../vendor/autoload.php';

class User {
	public int $id;
	public string $name;
	public string $username;

	// Using atrribute for arrays
	#[Mateodioev\Json\JsonField(Product::class)]
	public array $product;
}

class Product {
	public int $id;
	public string $name;
	public float $price;
}

$rawJson = '{
	"id": 1,
	"name": "John Doe",
	"username": "johndoe",
	"product": [
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
