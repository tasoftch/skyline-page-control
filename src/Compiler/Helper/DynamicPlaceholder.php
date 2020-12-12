<?php


namespace Skyline\PageControl\Compiler\Helper;


class DynamicPlaceholder extends \Skyline\PageControl\Placeholder\DynamicPlaceholder
{
	public static $interception;

	public function __construct(string $name, string $description = "", int $options = 0)
	{
		parent::__construct($name, $description, $options);
		(static::$interception)($name, $description, $options);
	}
}