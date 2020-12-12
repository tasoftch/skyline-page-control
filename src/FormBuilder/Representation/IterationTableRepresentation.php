<?php


namespace Skyline\PageControl\FormBuilder\Representation;


use Skyline\HTML\Form\Control\AbstractControl;
use Skyline\HTML\Form\Control\ControlInterface;
use Skyline\HTML\Form\FormElement;

class IterationTableRepresentation extends AbstractRepresentation
{
	/** @var int */
	private $tableID;
	/** @var string|null */
	private $description;
	private $options = 0;
	private $visibilityGroup = 0;

	private $headers = [];
	private $header_ids = [];
	private $rows = [];

	private $registeredControls = [];

	public function __construct(string $name, int $tableID)
	{
		parent::__construct($name, []);
		$this->tableID = $tableID;
	}

	/**
	 * @inheritDoc
	 */
	public function represent(FormElement $element)
	{

		?>
		<hr>
		<h3><?=htmlspecialchars( $this->getName() )?></h3>
		<p class="text-muted"><?=nl2br(htmlspecialchars( $this->getDescription() ))?></p>
	<table class="table table-responsive-lg" id="table-<?=$this->tableID?>">
        <thead class="thead-dark">
        <tr>
			<?php
			foreach($this->getHeaders() as $header) {
			    $header = $this->getHeader($header);
				?><th class="" scope="col"><?=htmlspecialchars( $header->getLabel() )?></th><?php
			}
			?>
        </tr>
        </thead>
        <tbody>
        <?php
        /** @var IterationTableRow $row */

        echo "<tr class='control-row'>";
        foreach($this->getHeaders() as $header) {
            $header = $this->getHeader($header);
            $cell = $header->getCellControl(0);
            $element->appendElement($cell);
            echo "<td>";
			if($cell instanceof \Skyline\HTML\Form\Control\Option\PopUpControl)
				$element->manualBuildControl($cell->getName(), ['class' => 'custom-select']);
            elseif($cell instanceof ControlInterface)
				$element->manualBuildControl($cell->getName());
            echo "</td>";
        }
        echo "</tr>";

        foreach($this->rows as $row) {
			printf("<tr data-row='%d' onclick='select_row($this->tableID, $(this).attr(\"data-row\"))'>", $row->getRow());

			/** @var ControlInterface $control */
			foreach($row->getCells() as $control) {
				echo "<td>";
				if($control instanceof \Skyline\HTML\Form\Control\Option\PopUpControl)
					$element->manualBuildControl($control->getName(), ['class' => 'custom-select']);
				elseif($control instanceof ControlInterface)
					$element->manualBuildControl($control->getName());
				else
				    echo " ";
				echo "</td>";
            }

			echo "</tr>";
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="100">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="add_row(<?=$this->tableID?>)">Neue Zeile</button>
                <button type="button" class="btn btn-sm btn-outline-primary q mu" disabled onclick="move_row_up(<?=$this->tableID?>)">Nach oben</button>
                <button type="button" class="btn btn-sm btn-outline-primary q md" disabled onclick="move_row_down(<?=$this->tableID?>)">Nach unten</button>
                <button type="button" class="btn btn-sm btn-outline-danger q" disabled onclick="delete_row(<?=$this->tableID?>)">Löschen</button>
            </td>
        </tr>
        </tfoot>
    </table>
		<?php
	}

	/**
	 * @inheritDoc
	 */
	public function prepare(FormElement $element)
	{
		foreach($this->registeredControls as $registeredControl) {
			$element->appendElement($registeredControl);
		}
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @return int
	 */
	public function getOptions(): int
	{
		return $this->options;
	}

	/**
	 * @return int
	 */
	public function getVisibilityGroup(): int
	{
		return $this->visibilityGroup;
	}

	/**
	 * @param string|null $description
	 * @return IterationTableRepresentation
	 */
	public function setDescription(?string $description): IterationTableRepresentation
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * @param int $options
	 * @return IterationTableRepresentation
	 */
	public function setOptions(int $options): IterationTableRepresentation
	{
		$this->options = $options;
		return $this;
	}

	/**
	 * @param int $visibilityGroup
	 * @return IterationTableRepresentation
	 */
	public function setVisibilityGroup(int $visibilityGroup): IterationTableRepresentation
	{
		$this->visibilityGroup = $visibilityGroup;
		return $this;
	}

	/**
	 * @param IterationTableHeader
	 * @return $this
	 */
	public function addHeader(IterationTableHeader $header): IterationTableRepresentation {
		$this->headers[ $header->getName() ] = $header;
		$this->header_ids[ $header->getId() ] = $header;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders(): array {
		return array_keys($this->headers);
	}

	/**
	 * @param string $name
	 * @return IterationTableHeader|null
	 */
	public function getHeader($name): ?IterationTableHeader {
		return is_numeric($name) ? ($this->header_ids[$name] ?? NULL) : ($this->headers[$name] ?? NULL);
	}

	/**
	 * @param int $row
	 * @param bool $create
	 * @return IterationTableRow|null
	 */
	public function getRow(int $row, bool $create = true): ?IterationTableRow {
		if(isset($this->rows[$row]))
			return $this->rows[$row];
		return $create ? ($this->rows[$row] = new IterationTableRow($row, $this)) : NULL;
	}

	public function swapRow(int $row, bool $up) {

    }

    public function deleteRow(int $row) {

    }
}