<?php


namespace Skyline\PageControl\FormBuilder\Representation;


use Skyline\HTML\Form\Control\AbstractLabelControl;
use Skyline\HTML\Form\Control\ControlInterface;

class IterationTableHeader
{
	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var string|null */
	private $label;
	/** @var callable  Signature: function(int $row): AbstractControl */
	private $cellGenerator;

	/**
	 * IterationTableHeader constructor.
	 * @param int $id
	 * @param string $name
	 * @param string|null $label
	 * @param callable $cellGenerator
	 */
	public function __construct(int $id, string $name, ?string $label, callable $cellGenerator)
	{
		$this->id = $id;
		$this->name = $name;
		$this->label = $label;
		$this->cellGenerator = $cellGenerator;
	}


	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string|null
	 */
	public function getLabel(): ?string
	{
		return $this->label;
	}

	public function getCellControl(int $row): ControlInterface {
		return call_user_func($this->cellGenerator, $row);
	}
}