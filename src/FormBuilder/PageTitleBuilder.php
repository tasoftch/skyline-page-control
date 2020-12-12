<?php


namespace Skyline\PageControl\FormBuilder;


use Skyline\HTML\Form\Control\Text\TextFieldControl;
use Skyline\HTML\Form\FormElement;
use Skyline\HTML\Form\Validator\NotEmptyValidator;
use Skyline\PageControl\Controller\AbstractPageController;
use Skyline\PageControl\FormBuilder\Representation\ControlRepresentation;
use TASoft\Service\ServiceManager;
use TASoft\Util\PDO;

class PageTitleBuilder implements FormBuilderInterface, FormApplyerInterface
{
	public function build(AbstractPageController $controller)
	{
		/** @var PDO $PDO */
		$PDO = ServiceManager::generalServiceManager()->get("PDO");


		return new ControlRepresentation(
			(new TextFieldControl('page-title'))
				->setDescription('The page title')
				->setLabel("Title")
				->setPlaceholder("My page")
				->addValidator(new NotEmptyValidator()),
			function() use ($controller, $PDO) {
				return $PDO->selectFieldValue("SELECT title FROM SKY_PC_PAGE WHERE name = ?", 'title', [ $controller->getPageName() ]);
			}
		);
	}

	public function getRelevantFieldNames(): array
	{
		return ['page-title'];
	}

	public function applyValues(array $values, AbstractPageController $controller)
	{
		/** @var PDO $PDO */
		$PDO = ServiceManager::generalServiceManager()->get("PDO");
		if($PDO) {
			$PDO->inject("UPDATE SKY_PC_PAGE SET title = ? WHERE name = ?")->send([
				$values['page-title'],
				$controller->getPageName()
			]);
		}
	}
}