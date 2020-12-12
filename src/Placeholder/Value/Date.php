<?php


namespace Skyline\PageControl\Placeholder\Value;

use TASoft\Util\ValueObject\Date AS Pdate;

class Date extends AbstractValue
{
	protected function convert(string $representation)
	{
		return new Pdate($representation);
	}

	protected function reverse($value): string
	{
		if($value instanceof Pdate)
			return $value->format( 'Y-m-d' );
		return "";
	}
}