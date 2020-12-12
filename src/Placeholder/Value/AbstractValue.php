<?php


namespace Skyline\PageControl\Placeholder\Value;


abstract class AbstractValue implements ValueInterface
{
	private $value;
	private $options = 0;

	/**
	 * @return int
	 */
	public function getOptions(): int
	{
		return $this->options;
	}

	/**
	 * @inheritDoc
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @inheritDoc
	 */
	public function toScalar(): string
	{
		return $this->reverse($this->getValue());
	}

	/**
	 * @inheritDoc
	 */
	public function __construct(string $scalarRepresentation, int $options = 0)
	{
		$this->value = $this->convert($scalarRepresentation);
		$this->options = $options;
	}

	/**
	 * @param string $representation
	 * @return mixed
	 */
	abstract protected function convert(string $representation);

	/**
	 * @param $value
	 * @return string
	 */
	abstract protected function reverse($value): string;

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->toScalar();
	}
}