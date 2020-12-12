<?php


namespace Skyline\PageControl\Placeholder\Value;


class Boolean extends AbstractValue
{
	/**
	 * @inheritDoc
	 */
	protected function convert(string $representation)
	{
		switch (strtoupper($representation)) {
			case 1:
			case 'YES':
			case 'TRUE':
				return true;
			default:
				return false;
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function reverse($value): string
	{
		return $value ? "1" : '0';
	}
}