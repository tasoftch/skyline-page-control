<?php


namespace Skyline\PageControl\Placeholder\Value;


use Skyline\PageControl\Exception\ValueException;

class Email extends AbstractValue
{

	/**
	 * @inheritDoc
	 */
	protected function convert(string $representation)
	{
		if(filter_var($representation, FILTER_VALIDATE_EMAIL)) {
			return $representation;
		}
		throw new ValueException("Invalid emailadress representation");
	}

	/**
	 * @inheritDoc
	 */
	protected function reverse($value): string
	{
		return $value;
	}
}