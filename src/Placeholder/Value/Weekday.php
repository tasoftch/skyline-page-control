<?php


namespace Skyline\PageControl\Placeholder\Value;


class Weekday extends AbstractValue
{
	private $name;
	private $abbreviation;

	/**
	 * @inheritDoc
	 */
	protected function convert(string $representation)
	{
		$exp = preg_split("/\s+/i", $representation);
		if(is_numeric($exp[0])) {
			if(count($exp) > 1)
				$this->name = trim($exp[1]);
			if(count($exp) > 2)
				$this->abbreviation = trim($exp[2]);
			return $exp[0] * 1;
		}
		else
			throw new \InvalidArgumentException("Argument for weekday must be: <number>[ <name>[ <abbr>]]");
	}

	/**
	 * @inheritDoc
	 */
	protected function reverse($value): string
	{
		return $this->getOptions() & 1 ? $this->getName() : $this->getAbbreviation();
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getAbbreviation()
	{
		return $this->abbreviation;
	}
}