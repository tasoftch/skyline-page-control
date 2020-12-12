<?php


namespace Skyline\PageControl\Exception;


use Throwable;

class ValueException extends \RuntimeException
{
	/** @var string */
	private $representation;
	/** @var string */
	private $desiredValueClass;

	public function __construct($message = "", $code = 0, Throwable $previous = null, ...$args)
	{
		parent::__construct(vsprintf($message, $argv), $code, $previous);
	}

	/**
	 * @return string
	 */
	public function getRepresentation(): string
	{
		return $this->representation;
	}

	/**
	 * @return string
	 */
	public function getDesiredValueClass(): string
	{
		return $this->desiredValueClass;
	}

	/**
	 * @param string $representation
	 * @return static
	 */
	public function setRepresentation(string $representation)
	{
		$this->representation = $representation;
		return $this;
	}

	/**
	 * @param string $desiredValueClass
	 * @return static
	 */
	public function setDesiredValueClass(string $desiredValueClass)
	{
		$this->desiredValueClass = $desiredValueClass;
		return $this;
	}
}