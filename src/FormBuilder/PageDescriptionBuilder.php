<?php


namespace Skyline\PageControl\FormBuilder;


use Skyline\HTML\Form\Control\Text\TextAreaControl;
use Skyline\HTML\Form\FormElement;
use Skyline\HTML\Form\Validator\NotEmptyValidator;
use Skyline\PageControl\Controller\AbstractPageController;
use Skyline\PageControl\FormBuilder\Representation\ControlRepresentation;
use Skyline\PageControl\FormBuilder\Representation\RepresentationInterface;
use TASoft\Service\ServiceManager;
use TASoft\Util\PDO;

class PageDescriptionBuilder implements FormBuilderInterface, FormApplyerInterface
{
	public function build(AbstractPageController $controller)
	{
		/** @var PDO $PDO */
		$PDO = ServiceManager::generalServiceManager()->get("PDO");


		return new ControlRepresentation(
			(new TextAreaControl('page-description'))
				->setDescription('The page description')
				->setLabel("Description")
				->setPlaceholder("My page")
				->addValidator(new NotEmptyValidator()),
			function() use ($controller, $PDO) {
				return $PDO->selectFieldValue("SELECT description FROM SKY_PC_PAGE WHERE name = ?", 'description', [ $controller->getPageName() ]);
			}
		);
	}

	public function getRelevantFieldNames(): array
	{
		return ['page-description'];
	}

	public function applyValues(array $values, AbstractPageController $controller)
	{
		/** @var PDO $PDO */
		$PDO = ServiceManager::generalServiceManager()->get("PDO");
		if($PDO) {
			$PDO->inject("UPDATE SKY_PC_PAGE SET description = ? WHERE name = ?")->send([
				$values['page-description'],
				$controller->getPageName()
			]);
		}
	}
}