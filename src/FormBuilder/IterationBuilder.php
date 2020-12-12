<?php


namespace Skyline\PageControl\FormBuilder;


use Skyline\HTML\Form\Control\AbstractLabelControl;
use Skyline\HTML\Form\Control\Option\PopUpControl;
use Skyline\HTML\Form\FormElement;
use Skyline\PageControl\Controller\AbstractPageController;
use Skyline\PageControl\FormBuilder\Representation\IterationTableHeader;
use Skyline\PageControl\FormBuilder\Representation\IterationTableRepresentation;
use Skyline\PageControl\FormBuilder\Representation\RepresentationInterface;
use TASoft\Service\ServiceManager;
use TASoft\Util\PDO;
use TASoft\Util\ValueInjector;

class IterationBuilder implements FormBuilderInterface, FormApplyerInterface
{
	private $fieldNames = [];

	public function build(AbstractPageController $controller)
	{
		$PDO = ServiceManager::generalServiceManager()->get("PDO");

		$representations = [];

		if($PDO instanceof PDO) {
			$optionLists = [];
			$tables = [];

			foreach($PDO->select("SELECT
    SKY_PC_ITERATION.id,
    SKY_PC_ITERATION.name,
    SKY_PC_ITERATION.description,
    SKY_PC_ITERATION.options >> 8 AS i_options,
    (SKY_PC_ITERATION.options & 0xFF) & SKY_PC_VALUE_TYPE.options AS options,
       visibility_group,
       SKY_PC_ITERATION_HEADER.id AS header_id,
       SKY_PC_ITERATION_HEADER.name AS header_name,
       SKY_PC_ITERATION_HEADER.description AS header_label,
       hasNull,
       controlClass,
       controlOptionList,
       controlPlaceholder
FROM SKY_PC_ITERATION
    JOIN SKY_PC_ITERATION_Q ON iteration = id
    JOIN SKY_PC_PAGE On SKY_PC_PAGE.id = page
JOIN SKY_PC_ITERATION_HEADER_Q ON SKY_PC_ITERATION_HEADER_Q.iteration = SKY_PC_ITERATION.id
JOIN SKY_PC_ITERATION_HEADER ON header = SKY_PC_ITERATION_HEADER.id
JOIN SKY_PC_VALUE_TYPE ON value_type = SKY_PC_VALUE_TYPE.id
WHERE SKY_PC_PAGE.name = ? ORDER BY col", [$controller->getPageName()]) as $record) {
				$id = $record['id'] * 1;
				$tables[$id] = $id;



				$REP = $representations[$id] = $representations[$id] ?? (new IterationTableRepresentation($record["name"], $id*1))
					->setDescription($record['description'])
					->setOptions($record['i_options'] * 1)
					->setVisibilityGroup($record['visibility_group'] * 1);

				$cc = $record['controlClass'];

				if(class_exists($cc)) {
					$hid = $record["header_id"];

					if($ol = $record['controlOptionList'] ?? 0)
						$optionLists[ $ol ] = $ol;
					else
						trigger_error("Optionlist $ol not found", E_USER_WARNING);


					$REP->addHeader(
						new IterationTableHeader(
							$hid,
							$record["header_name"],
							$record["header_label"],
							function(int $row) use ($id, $hid, $record, &$optionLists, $REP) {
								$cc = $record['controlClass'];
								/** @var \Skyline\HTML\Form\Control\AbstractLabelControl $control */
								$control = new $cc($this->fieldNames[] = "t{$id}r{$row}c{$hid}", "t{$id}r{$row}c{$hid}");
								if(!$record['hasNull'])
									$control->addValidator(new \Skyline\HTML\Form\Validator\NotEmptyValidator());
								elseif($control instanceof \Skyline\HTML\Form\Control\Option\PopUpControl)
									$control->setNullPlaceholder("----");

								if(method_exists($control, 'setPlaceholder'))
									$control->setPlaceholder( $record['controlPlaceholder'] );

								if($control instanceof \Skyline\HTML\Form\Control\Option\OptionValuesInterface && ($ol = $record["controlOptionList"])) {
									foreach(($optionLists[$ol] ?? []) as $opt => $vals) {
										$control->setOption($opt, $vals[0]);
									}
								}

								$vi = new ValueInjector($REP);
								$rg = $vi->registeredControls;
								$rg[ $control->getName() ] = $control;
								$vi->registeredControls = $rg;

								return $control;
							}
						)
					);
				} else
					trigger_error("Can not build control for class $cc", E_USER_WARNING);
			}

			foreach($optionLists as $ol => &$list) {
				$list = [];
				foreach ($PDO->select("SELECT
sys_id,
       label,
       description
FROM SKY_PC_OPTION_LIST_ITEM
WHERE option_list = $ol") as $record) {
					$list[ $record['sys_id'] ] = [$record['label'], $record['description']];
				}
			}

			// Read already existing table from POST

			$tableCache = [];
			if(isset($_POST['apply'])) {
				foreach ($_POST as $name => $value) {
					if(preg_match("/^t(\d+)r(\d+)c(\d+)$/i", $name, $ms)) {
						if($ms[2] > 0)
							$tableCache[ $ms[1] ][ $ms[2] ][ $ms[3] ] = $value == PopUpControl::NULL_VALUE_MARKER ? null : $value;
					}
				}
			}

			foreach($tables as $id) {
				$REP = $representations[$id];

				if($cache = $tableCache[$id] ?? NULL) {
					foreach($cache as $row => $cells) {
						$ROW = $REP->getRow( $row );
						foreach($cells as $cid => $value) {
							$ROW->setCellValue($REP->getHeader($cid)->getName(), $value);
						}
					}
				} else {
					foreach($PDO->select("SELECT
    SKY_PC_ITERATION_CELL.id,
    row,
	SKY_PC_ITERATION_HEADER.name,
    visibility,
    value
FROM SKY_PC_ITERATION
JOIN SKY_PC_ITERATION_HEADER_Q ON iteration = id
JOIN SKY_PC_ITERATION_CELL ON SKY_PC_ITERATION_CELL.header = SKY_PC_ITERATION_HEADER_Q.header
JOIN SKY_PC_ITERATION_HEADER ON SKY_PC_ITERATION_HEADER_Q.header = SKY_PC_ITERATION_HEADER.id
WHERE SKY_PC_ITERATION.id = $id ORDER BY row, col") as $record) {
						$ROW = $REP->getRow( $record['row'] * 1 );

						$ROW->setCellValue( $record['name'], $record['value'] );
					}
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
			$headers = [];
			$rows = [];

			foreach($values as $name => $value) {
				if(preg_match("/^t\d+r(\d+)c(\d+)$/i", $name, $ms)) {
					$headers[ $ms[2] * 1 ] = $ms[2] * 1;
					$rows[ $ms[1] ][ $ms[2] ] = $value;
				}
			}

			if($headers) {
				$headers = implode(",", array_values($headers));
				$PDO->exec("DELETE FROM SKY_PC_ITERATION_CELL WHERE header IN ($headers)");
			}

			$inj = $PDO->inject("INSERT INTO SKY_PC_ITERATION_CELL (header, value, row) VALUES (?, ?, ?)");

			foreach($rows as $row => $cells) {
				foreach($cells as $hdr => $value)
					$inj->send([ $hdr, $value ?: NULL, $row ]);
			}
		}
	}
}