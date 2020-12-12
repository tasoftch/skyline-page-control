<?php


namespace Skyline\PageControl\FormBuilder\Representation;


abstract class AbstractRepresentation implements RepresentationInterface
{
	protected $value;
	private $name;

	public function __construct(string $name, $value) {
		$this->name = $name;
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getInitialValue()
	{
		if(is_callable($this->value))
			return call_user_func($this->value);
		return $this->value;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}


}