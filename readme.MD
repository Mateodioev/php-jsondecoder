# JSON decoder
Decode a json string into a class

## First step

```bash
composer require mateodioev/jsondecoder
```

```php
use Mateodioev\Json;
```

## Usage

Create a class with public atributes, egg:

```php
class User {
	public int $id;
	public string $name;
	public string $username;
}
```

### Create a new instance of JSON class with json raw

```php
$jsonRaw = '{"id": 1, "name": "Mateo", "username": "mateodioev"}';
$json = new JSON($jsonRaw);
```

### Decode content

```php
$u = new User;

$json->decode($u)
```

Now var `$u` containts content of `$jsonRaw`

```php
var_dump($u);
```

example output:
```plaintext
class User#2 (3) {
  public int $id =>
  int(1)
  public string $name =>
  string(5) "Mateo"
  public string $username =>
  string(10) "mateodioev"
}
```

## Exceptions
```php
Mateodioev\Json\JsonDecodeException
```
