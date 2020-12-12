<?php


namespace Skyline\PageControl\FormBuilder;


use Skyline\PageControl\Controller\AbstractPageController;

interface FormApplyerInterface
{
	/**
	 * @return array
	 */
	public function getRelevantFieldNames(): array;

	/**
	 * @param array $values
	 */
	public function applyValues(array $values, AbstractPageController $controller);
}