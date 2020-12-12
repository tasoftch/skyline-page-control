<?php


namespace Skyline\PageControl\Placeholder\Value;


class StringVal extends AbstractValue
{
	/**
	 * @inheritDoc
	 */
	protected function convert(string $representation)
	{
		return htmlspecialchars( $representation );
	}

	/**
	 * @inheritDoc
	 */
	protected function reverse($value): string
	{
		return $value;
	}
}