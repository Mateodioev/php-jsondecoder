<?php

namespace Mateodioev\Json;

use Attribute;

#[Attribute]
class JsonField
{
	/**
	 * @throws InvalidJsonFieldException
	 */
	public function __construct(
		protected string $class,
		protected bool $isArray = true,
	) {
		if (\class_exists($this->class) === false) {
			throw new InvalidJsonFieldException("Class {$this->class} does not exist");
		}
	}

	public function class(): string
	{
		return $this->class;
	}

	public function isArray(): bool
	{
		return $this->isArray;
	}
}