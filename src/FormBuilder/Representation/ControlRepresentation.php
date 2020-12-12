<?php


namespace Skyline\PageControl\FormBuilder\Representation;


use Skyline\HTML\Form\Control\ControlInterface;
use Skyline\HTML\Form\FormElement;

class ControlRepresentation extends AbstractRepresentation
{
	/** @var ControlInterface */
	private $control;
	/** @var Condition|null */
	private $condition;

	public function __construct(ControlInterface $control, $value)
	{
		parent::__construct($control->getName(), $value);
		$this->control = $control;
	}

	/**
	 * @return ControlInterface
	 */
	public function getControl(): ControlInterface
	{
		return $this->control;
	}

	/**
	 * @param ControlInterface $control
	 * @return static
	 */
	public function setControl(ControlInterface $control)
	{
		$this->control = $control;
		return $this;
	}

	/**
	 * @param Condition $condition
	 * @return static
	 */
	public function setCondition(Condition $condition)
	{
		$this->condition = $condition;
		return $this;
	}

	/**
	 * @return Condition|null
	 */
	public function getCondition(): ?Condition
	{
		return $this->condition;
	}

	/**
	 * @inheritDoc
	 */
	public function represent(FormElement $FORM)
	{
		$control = $this->getControl();
		?>
		<div class="form-group row">
			<label for="<?=$control->getID()?>" class="col-md-3 col-form-label" data-trans="<?=$control->getName()?>-trans"><?=$control->getLabel()?></label>
			<div class="col-md-9">
                <div class="input-group">
					<?php
					if($control instanceof \Skyline\HTML\Form\Control\Option\PopUpControl)
						$FORM->manualBuildControl($control->getName(), ['class' => 'custom-select']);
					else
						$FORM->manualBuildControl($control->getName());
					if($cond = $this->getCondition()) {
					    ?>
                        <div class="input-group-append">
                            <input type="hidden" data-ref="<?=$control->getID()?>" name="<?=$control->getName()?>-c" value="<?=$cond?>">
                            <button onclick="open_condition_panel($(this).parent().find('input'))" title="Define a condition for this placeholder" class="btn btn-outline-secondary" type="button">?:</button>
                        </div>
                        <?php
                    }
					?>
                </div>

				<small class="form-text text-muted" data-trans="<?=$control->getName()?>-d-trans"><?= htmlspecialchars($control->getDescription()) ?></small>
			</div>
		</div>
		<?php
	}

	/**
	 * @inheritDoc
	 */
	public function prepare(FormElement $element)
	{
		$element->appendElement( $this->getControl() );
	}
}