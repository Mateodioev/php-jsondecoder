<?php

namespace Mateodioev\Json;

use JsonException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

use function json_decode;

class JSON
{
    private readonly array $json;
    private ReflectionProperty $property;

    /**
     * @throws JsonDecodeException
     */
    public function __construct(
        protected string $rawJson
    ) {
        try {
            $this->json = json_decode($this->rawJson, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new JsonDecodeException($e->getMessage());
        }
    }

    /**
     * Decode json raw into class source
	 * @param mixed $obj Class source
     * @throws ReflectionException
     * @throws JsonDecodeException
     */
    public function decode(&$obj): void
    {
        $class = new ReflectionClass($obj);
        $properties = $class->getProperties();

        foreach ($properties as $property) {
            $this->property = $property;
            $this->property->setAccessible(true);

            $name = $this->property->getName();
            $type = $this->property->getType();
			$attr = $this->property->getAttributes()[0] ?? null;

			if ($attr instanceof ReflectionAttribute && $attr->getName() == JsonField::class) {
				$this->passingAttributes($attr->newInstance(), $obj, $this->json[$name]);
				continue;
			}
			if ($type->isBuiltin() === false) {
				// Create new JSON instance and decode content
                $this->passingNotScalarTypes(
                    $obj,
                    $type->getName(),
                    \json_encode($this->json[$name])
                );
            } else {
                if (isset($this->json[$name])) {
                    $this->passingScalarTypes($obj, $this->json[$name]);
                }
            }
        }
    }

    /**
     * @throws JsonDecodeException
     * @throws ReflectionException
     */
    public function passingAttributes(JsonField $field, $obj, mixed $content): void
    {

		// Property type
		$type = $field->isArray() ? 'array' : $this->property->getType();
		$contentType = self::getType($content, true);

		// Check if json and class property type are the same
		if ($type != $contentType) {
			throw new JsonDecodeException($this->buildInvalidTypeMessage($contentType));

		} elseif ($type == 'array' && $field->isArray()) {
			$contents     = [];
			// Create new instance of class
			$subClassName = new ($field->class());
			foreach ($content as $subContent) {
				$subObj = new $subClassName;
				(new JSON(\json_encode($subContent)))->decode($subObj);

				$contents[] = $subObj;
				unset($subObj);
			}

			$this->property->setValue($obj, $contents);
		} else {
			// Pass singles atribute
			$this->passingNotScalarTypes($obj, $field->class(), $content);
		}
	}

    /**
     * @throws JsonDecodeException
     * @throws ReflectionException
     */
    public function passingNotScalarTypes($obj, string $classTarget, string $content): void
    {
        $subObj = new $classTarget;
        $json = new JSON($content);
        $json->decode($subObj);

        $this->property->setValue($obj, $subObj);
    }

    /**
     * @throws JsonDecodeException
     */
    public function passingScalarTypes($obj, mixed $value): void
    {
        $valueType = self::getType($value, true);

        if ($this->property->getType() != $valueType) {
            throw new JsonDecodeException($this->buildInvalidTypeMessage($valueType));
        }

        $this->property->setValue($obj, $value);
    }

	protected function buildInvalidTypeMessage(string $type): string
	{
		return \sprintf(
			'Invalid value for property %s, expected %s, given %s',
			$this->property->getName(),
			$this->property->getType(),
			$type
		);
	}

	public static function getType(mixed $value, bool $shortType = false): string
	{
		$type = \gettype($value);

		if (!$shortType) return $type;

        return match ($type) {
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            default => $type,
        };
	}
}