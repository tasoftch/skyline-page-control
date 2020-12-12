<?php


namespace Skyline\PageControl\FormBuilder\Representation;


use Skyline\HTML\Form\FormElement;
use Skyline\PageControl\FormBuilder\ValuePromise;

interface RepresentationInterface
{
	public function getName(): string;

	/**
	 * Renders the representation into html output
	 */
	public function represent(FormElement $element);

	/**
	 * @return mixed|null|ValuePromise
	 */
	public function getInitialValue();

	/**
	 * Called to prepare a form element
	 * @param FormElement $element
	 */
	public function prepare(FormElement $element);
}