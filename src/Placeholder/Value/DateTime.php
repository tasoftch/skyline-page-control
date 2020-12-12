<?php


namespace Skyline\PageControl\Placeholder\Value;

use TASoft\Util\ValueObject\DateTime AS PdateTime;

class DateTime extends AbstractValue
{

	/**
	 * @inheritDoc
	 */
	protected function convert(string $representation)
	{
		return new PdateTime($representation);
	}

	/**
	 * @inheritDoc
	 */
	protected function reverse($value): string
	{
		if($value instanceof PdateTime)
			return $value->format("Y-m-d G:i:s");
		return "";
	}
}