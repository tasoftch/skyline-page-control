<?php


namespace Skyline\PageControl\FormBuilder;


use Skyline\HTML\Element;
use Skyline\HTML\Form\FormElement;
use Skyline\PageControl\Controller\AbstractPageController;
use Skyline\PageControl\FormBuilder\Representation\ElementRepresentation;
use Skyline\PageControl\FormBuilder\Representation\RepresentationInterface;

class SeparatorBuilder implements FormBuilderInterface
{
	public function build(AbstractPageController $controller)
	{
		return new ElementRepresentation(
			new Element('hr', false)
		);
	}
}