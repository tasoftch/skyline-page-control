<?php


namespace Skyline\PageControl\Placeholder\Value;


class Number extends AbstractValue
{
	/**
	 * @inheritDoc
	 */
	protected function convert(string $representation)
	{
		return $representation * 1;
	}

	/**
	 * @inheritDoc
	 */
	protected function reverse($value): string
	{
		return (string) $value;
	}
}