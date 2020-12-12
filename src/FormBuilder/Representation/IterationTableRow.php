<?php


namespace Skyline\PageControl\FormBuilder\Representation;


use Skyline\HTML\Form\Control\ControlInterface;
use TASoft\Util\ValueInjector;

class IterationTableRow
{
	/** @var int */
	private $row;
	/** @var IterationTableRepresentation */
	private $table;

	private $cells = [];

	/**
	 * IterationTableRow constructor.
	 * @param int $row
	 */
	public function __construct(int $row, IterationTableRepresentation $table)
	{
		$this->row = $row;
		$this->table = $table;
		foreach($table->getHeaders() as $header) {
			$hdr = $table->getHeader($header);
			$this->cells[$header] = $hdr->getCellControl($row);
		}
	}

	/**
	 * @return int
	 */
	public function getRow(): int
	{
		return $this->row;
	}

	/**
	 * @return IterationTableRepresentation
	 */
	public function getTable(): IterationTableRepresentation
	{
		return $this->table;
	}


	public function setCellValue(string $headerName, $value) {
		if(in_array($headerName, array_keys($this->cells))) {
			$vi = new ValueInjector($this->table);
			$v = $vi->value;
			$v[ $this->cells[$headerName]->getName() ] = $value;
			$vi->value = $v;
		}
	}

	/**
	 * @return array
	 */
	public function getCells(): array
	{
		return $this->cells;
	}
}