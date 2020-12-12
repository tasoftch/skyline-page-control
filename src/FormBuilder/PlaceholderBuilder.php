<?php


namespace Skyline\PageControl\FormBuilder;

use Skyline\HTML\Form\Control\AbstractLabelControl;
use Skyline\HTML\Form\Control\Option\OptionValuesInterface;
use Skyline\HTML\Form\Control\Option\PopUpControl;
use Skyline\HTML\Form\FormElement;
use Skyline\PageControl\Controller\AbstractPageController;
use Skyline\PageControl\FormBuilder\Representation\Condition;
use Skyline\PageControl\FormBuilder\Representation\ControlRepresentation;
use Skyline\PageControl\FormBuilder\Representation\RepresentationInterface;
use TASoft\Service\ServiceManager;
use TASoft\Util\PDO;
use TASoft\Util\ValueObject\DateTime;

class PlaceholderBuilder implements FormBuilderInterface, FormApplyerInterface
{
	private $fieldNames = [];

	public function build(AbstractPageController $controller)
	{
		$PDO = ServiceManager::generalServiceManager()->get("PDO");

		$representations = [];

		if($PDO instanceof PDO) {
			foreach($PDO->select("SELECT
    SKY_PC_PLACEHOLDER.name,
    label,
    value,
    SKY_PC_PLACEHOLDER.options >> 8 AS options,
    SKY_PC_PLACEHOLDER.description,
    SKY_PC_PLACEHOLDER.placeholder,
    controlClass,
    controlOptionList,
       CASE
           WHEN d_condition IS NULL THEN -1
           WHEN SKY_PC_CONDITION.id IS NULL THEN 0
           ELSE 1 END AS hasCondition,
    date_from,
       date_until,
       ip1,ip2,ip3,ip4,
       SKY_PC_CONDITION.options AS copts
FROM SKY_PC_PAGE
         JOIN SKY_PC_PLACEHOLDER_Q ON page = id
         JOIN SKY_PC_PLACEHOLDER ON SKY_PC_PLACEHOLDER_Q.placeholder = SKY_PC_PLACEHOLDER.id
         JOIN SKY_PC_VALUE_TYPE ON SKY_PC_VALUE_TYPE.id = valueType
LEFT JOIN SKY_PC_CONDITION On SKY_PC_CONDITION.id = d_condition
WHERE SKY_PC_PAGE.name = ?", [$controller->getPageName()]) as $record) {
				$cc = $record["controlClass"];
				if(class_exists($cc)) {
					/** @var AbstractLabelControl $control */

					$this->fieldNames[] = $record["name"];

					$control = new $cc( $record["name"], $record["name"] );
					$control->setLabel($record["label"]);
					$control->setDescription( $record["description"] );
					if(method_exists($control, 'setPlaceholder'))
						$control->setPlaceholder( $record['placeholder'] );

					$contents[ $record['name'] ] = $record['value'];

					if(($ol = $record["controlOptionList"]) && $control instanceof OptionValuesInterface) {
						foreach($PDO->select("SELECT
sys_id,
       label,
       description
FROM SKY_PC_OPTION_LIST_ITEM
WHERE option_list = $ol") as $opt) {
							$control->setOption( $opt['sys_id'], $opt['label'] );
						}
					}

					$condition = NULL;
					if($record['hasCondition']>-1) {
						$genDate = function($d) { return is_null($d) ? NULL : new DateTime($d); };
						$condition = new Condition(
							$genDate($record['date_from']),
							$genDate($record['date_until']),
							$record["copts"] * 1
						);
						for($e=1;$e<5;$e++) {
							if($ip = $record["ip$e"])
								$condition->addIpAddress($ip);
						}
					}

					if($condition) {
						$representations[] = (new ControlRepresentation(
							$control,
							$record['value']
						))
							->setCondition($condition);
					} else {
						$representations[] = (new ControlRepresentation(
							$control,
							$record['value']
						));
					}

				} else {
					trigger_error("Can not create control for placeholder ${record['name']}", E_USER_NOTICE);
				}
			}
		}

		return $representations;
	}

	public function getRelevantFieldNames(): array
	{
		return $this->fieldNames;
	}

	public function applyValues(array $values, AbstractPageController $controller)
	{
		$PDO = ServiceManager::generalServiceManager()->get("PDO");

		if($PDO instanceof PDO) {
			foreach($values as $name => $value) {
				if($cond = $_POST["$name-c"] ?? "") {
					list($cfrom, $cuntil, $copts, $ip1, $ip2, $ip3, $ip4) = $cond = explode("|", $cond);
					$copts*=1;
					
					if($copts & Condition::VALUE_DID_CHANGE_OPTION) {
						$copts &= ~Condition::VALUE_DID_CHANGE_OPTION;

						$PDO->inject("DELETE SKY_PC_CONDITION FROM SKY_PC_CONDITION JOIN SKY_PC_PLACEHOLDER ON SKY_PC_CONDITION.id = d_condition WHERE name = ?")->send([
							$name
						]);

						$PDO->inject("INSERT INTO SKY_PC_CONDITION (options, date_from, date_until, ip1, ip2, ip3, ip4) VALUES ($copts, ?, ?, ?, ?, ?, ?)")->send([
							$copts & Condition::ENABLE_FROM_OPTION ? ($cfrom ?: NULL) : NULL,
							$copts & Condition::ENABLE_UNTIL_OPTION ? ($cuntil ?: NULL) : NULL,
							$copts & Condition::ENABLE_HOSTS_OPTION ? ($ip1 ?: NULL) : NULL,
							$copts & Condition::ENABLE_HOSTS_OPTION ? ($ip2 ?: NULL) : NULL,
							$copts & Condition::ENABLE_HOSTS_OPTION ? ($ip3 ?: NULL) : NULL,
							$copts & Condition::ENABLE_HOSTS_OPTION ? ($ip4 ?: NULL) : NULL
						]);

						$cond = $PDO->lastInsertId("SKY_PC_CONDITION");
					} else
						$cond = "";
				}

				$PDO->inject($cond ? "UPDATE SKY_PC_PLACEHOLDER SET value = ?, d_condition = $cond WHERE name = ?" : "UPDATE SKY_PC_PLACEHOLDER SET value = ? WHERE name = ?")->send([
					$value,
					$name
				]);
			}
		}
	}
}