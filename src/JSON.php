<?php

namespace Mateodioev\Json;

use JsonException;
use Reflection;
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

			if ($attr instanceof ReflectionAttribute) {
				$this->passingAttributes($attr, $obj, $this->json[$name]);
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

	public function passingAttributes(ReflectionAttribute $attr, $obj, mixed $content)
	{
		$type = $this->property->getType();
		$contentType = self::getType($content);

		if ($type == 'array' && $contentType != 'array') {
			throw new JsonDecodeException($this->buildInvalidTypeMessage($contentType));
		} elseif ($type == 'array') {
			$contents     = [];
			$subClassName = $attr->getName();
			foreach ($content as $subContent) {
				$subObj = new $subClassName;
				(new JSON(\json_encode($subContent)))->decode($subObj);

				$contents[] = $subObj;
			}

			$this->property->setValue($obj, $contents);
		} else {
			$this->passingNotScalarTypes($obj, $attr->getName(), $content);
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

		switch ($type) {
			case 'boolean': return 'bool';
			case 'integer': return 'int';
			case 'double': return 'float';
			default: return $type;
		}
	}
}