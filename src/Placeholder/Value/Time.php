<?php


namespace Skyline\PageControl\Placeholder\Value;

use TASoft\Util\ValueObject\Time AS Ptime;


class Time extends AbstractValue
{
	/**
	 * @inheritDoc
	 */
	protected function convert(string $representation)
	{
		return new Ptime($representation);
	}

	/**
	 * @inheritDoc
	 */
	protected function reverse($value): string
	{
		if($value instanceof Ptime)
			return $value->format("G:i:s");
		return "";
	}
}