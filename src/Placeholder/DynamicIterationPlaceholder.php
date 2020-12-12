<?php


namespace Skyline\PageControl\Placeholder;


class DynamicIterationPlaceholder extends DynamicPlaceholder
{
	private $iteratedContents;

	public function __construct(string $name, callable $iterationContents, string $description = "", int $options = 0)
	{
		parent::__construct($name, $description, $options);

		if($cnt = static::getActionController()) {
			$self = $this;
			$this->contents = function() use ($iterationContents, $self, $cnt) {
				$contents = "";
				if(is_iterable($ROWS = $cnt->renderDynamicContents($self))) {
					foreach($ROWS as $row => $ROW) {
						$contents .= $iterationContents($row, $ROW);
					}
				}
				return $contents;
			};
		}
	}
}