<?php


namespace Skyline\PageControl\FormBuilder;


use Skyline\HTML\Form\FormElement;
use Skyline\PageControl\Controller\AbstractPageController;
use Skyline\PageControl\FormBuilder\Representation\RepresentationInterface;

interface FormBuilderInterface
{
	/**
	 * @param AbstractPageController $controller
	 * @return null|RepresentationInterface|RepresentationInterface[]
	 */
	public function build(AbstractPageController $controller);
}