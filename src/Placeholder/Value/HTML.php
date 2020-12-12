<?php


namespace Skyline\PageControl\Placeholder\Value;


class HTML extends AbstractValue
{
	/**
	 * @inheritDoc
	 */
	protected function convert(string $representation)
	{
		return $representation;
	}

	/**
	 * @inheritDoc
	 */
	protected function reverse($value): string
	{
		return (string) $value;
	}
}