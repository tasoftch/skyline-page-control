<?php


namespace Skyline\PageControl\FormBuilder\Representation;


use Skyline\HTML\ElementInterface;
use Skyline\HTML\Form\FormElement;

class ElementRepresentation extends AbstractRepresentation
{
	/** @var ElementInterface */
	private $element;

	public function __construct(ElementInterface $element = NULL)
	{
		parent::__construct("", NULL);
		$this->element = $element;
	}

	/**
	 * @return ElementInterface
	 */
	public function getElement(): ElementInterface
	{
		return $this->element;
	}

	/**
	 * @param ElementInterface $element
	 * @return static
	 */
	public function setElement(ElementInterface $element)
	{
		$this->element = $element;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function represent(FormElement $element)
	{
		echo $this->getElement()->toString();
	}

	/**
	 * @inheritDoc
	 */
	public function prepare(FormElement $element)
	{
	}
}