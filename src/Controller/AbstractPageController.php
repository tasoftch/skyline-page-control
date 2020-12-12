<?php
/*
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Skyline\PageControl\Controller;


use Skyline\Application\Controller\AbstractActionController;
use Skyline\Application\Controller\CustomRenderInformationInterface;
use Skyline\CMS\Security\SecurityTrait;
use Skyline\CMS\Security\Tool\UserTool;
use Skyline\HTML\Form\Control\Button\ButtonControl;
use Skyline\HTML\Form\SecureFormElement;
use Skyline\Kernel\Exception\SkylineKernelDetailedException;
use Skyline\PageControl\Exception\ValueException;
use Skyline\PageControl\FormBuilder\FormApplyerInterface;
use Skyline\PageControl\FormBuilder\FormBuilderInterface;
use Skyline\PageControl\FormBuilder\IterationBuilder;
use Skyline\PageControl\FormBuilder\PageDescriptionBuilder;
use Skyline\PageControl\FormBuilder\PageTitleBuilder;
use Skyline\PageControl\FormBuilder\PlaceholderBuilder;
use Skyline\PageControl\FormBuilder\Representation\RepresentationInterface;
use Skyline\PageControl\FormBuilder\SeparatorBuilder;
use Skyline\PageControl\FormBuilder\ValuePromise;
use Skyline\PageControl\Placeholder\DynamicIterationPlaceholder;
use Skyline\PageControl\Placeholder\DynamicPlaceholder;
use Skyline\PageControl\Placeholder\Value\NullValue;
use Skyline\PageControl\Placeholder\Value\ValueInterface;
use Skyline\Render\Context\DefaultRenderContext;
use Skyline\Render\Info\RenderInfo;
use Skyline\Render\Info\RenderInfoInterface;
use Skyline\Render\Model\AbstractModel;
use Skyline\Render\Model\ArrayModel;
use Skyline\Render\Template\TemplateInterface;
use Skyline\Router\Description\ActionDescriptionInterface;
use Symfony\Component\HttpFoundation\Response;
use TASoft\Service\ServiceManager;
use TASoft\Util\PDO;

/**
 * Subclass this action controller by your own classes for routing or security and call the renderPage method to deliver dynamic pages
 * @package Skyline\PageControl\Controller
 */
abstract class AbstractPageController extends AbstractActionController
{
	use SecurityTrait;

	/** @var array */
	private $cachedPlaceholders = [];
	private $placeholderStack = [];

	private $existsAdminAccess = false;
	private $hasAdminAccess = false;
	private $isAdminAccess = false;

	/** @var string|null */
	private $pageName;
	private $customTemplate;

	private $lockSubclassMethods = false;

	protected $pagesWithAdminAction = [
		// Add in your subclass any page names which will need a main action call also in the administration section.
	];

	public function performAction(ActionDescriptionInterface $actionDescription, RenderInfoInterface $renderInfo)
	{
		if(is_file($pc = SkyGetPath("$(C)/page-control.config.php"))) {
			$pc = require $pc;
		} else
			$pc = [];

		$cmk = $actionDescription->getActionControllerClass() . "::" . $actionDescription->getMethodName();
		if($adminInfo = $pc[$cmk] ?? NULL) {
			/** @var UserTool $uTool */
			$uTool = $this->get(UserTool::SERVICE_NAME);
			if($this->existsAdminAccess = isset($adminInfo['roles'])) {
				if($uTool->hasRoles($adminInfo['roles'])) {
					$this->hasAdminAccess = true;
				}
			}
			$this->pageName = $adminInfo['page'];
			if(!$this->customTemplate && isset($adminInfo["custom"]))
				$this->customTemplate = $adminInfo["custom"];
		}

		$this->renderInfo = $renderInfo;

		if(isset($_GET['admin'])) {
			/** @var UserTool $uTool */
			$uTool = $this->get(UserTool::SERVICE_NAME);

			/** @var DefaultRenderContext $ctx */
			$ctx = ServiceManager::generalServiceManager()->get("renderContext");
			$ctx->setRenderInfo($renderInfo);

			$this->performCodeUnderChallenge(function() use ($uTool, $adminInfo) {
				$uTool->requireUser();
				if($uTool->hasRoles($adminInfo['roles'])) {
					$this->isAdminAccess = true;
				}
			});

			if($this->isAdminAccess) {
				$form = new SecureFormElement("");
				$form->setActionControl(new ButtonControl("apply"));

				$representations = [];
				$builders = $this->getFormBuilders();

				foreach($builders as $builder) {
					$rep = $builder->build($this);
					if(is_array($rep))
						array_walk($rep, function($a) use (&$representations) { $representations[] = $a; });
					elseif($rep)
						$representations[] = $rep;
				}

				array_walk($representations, function(RepresentationInterface $R) use ($form) {
					$R->prepare($form);
				});

				if($this->shouldCallMainActionForAdmin($this->pageName)) {
					$this->lockSubclassMethods = true;

					parent::performAction($actionDescription, $renderInfo);

					$this->lockSubclassMethods = false;
				}

				$model = $this->renderInfo->get( RenderInfoInterface::INFO_MODEL );

				if($model instanceof AbstractModel) {
					$model["FORM"] = $form;
					$model["REPRESENTATIONS"] = $representations;
					$model["CUSTOM"] = $this->getCustomAdminTemplate( $this->pageName );
				} else {
					$this->renderModel([
						'FORM' => $form,
						'REPRESENTATIONS' => $representations,
						"CUSTOM" => $this->getCustomAdminTemplate( $this->pageName )
					]);
				}

				$state = $form->prepareWithRequest($this->request);
				if($state == $form::FORM_STATE_VALID) {
					$this->stopAndReloadAction(function() use ($form, $builders) {
						$data = $form->getData();
						foreach($builders as $builder) {
							if($builder instanceof FormApplyerInterface) {
								$names = $builder->getRelevantFieldNames();

								$builder->applyValues(
									array_filter($data, function($v, $k) use ($names) {
										return in_array($k, $names);
									}, ARRAY_FILTER_USE_BOTH),
									$this
								);
							}
						}
					});
				} elseif($state == $form::FORM_STATE_NONE) {
					$contents = [];
					/** @var RepresentationInterface $representation */
					foreach($representations as $representation) {
						if($n = $representation->getName()) {
							$v = $representation->getInitialValue();
							if(is_array($v))
								array_walk($v, function($v, $k) use (&$contents) {
									$contents[$k] = $v;
								});
							else
								$contents[$n] = $representation->getInitialValue();
						}
					}
					$form->setData($contents);
				}

				$this->renderTemplate(
					$adminInfo['layout'],
					[
						"Content" => 'admin-page-list'
					]
				);
			}

			return;
		}

		if(!$this->isAdminAccess && isset($adminInfo["page"]) && $this->shouldLoadPage($adminInfo["page"], $actionDescription, $renderInfo))
			$this->renderPage($adminInfo['page']);

		DynamicPlaceholder::setActionController($this);
		parent::performAction($actionDescription, $renderInfo);
	}

	/**
	 * @return FormBuilderInterface[]
	 */
	protected function getFormBuilders(): array {
		return [
			new PageTitleBuilder(),
			new PageDescriptionBuilder(),
			new SeparatorBuilder(),
			new PlaceholderBuilder(),
			new IterationBuilder()
		];
	}

	/**
	 * @param string $pageName
	 * @return string|TemplateInterface|null
	 */
	protected function getCustomAdminTemplate(string $pageName) {
		return $this->customTemplate;
	}

	/**
	 * @param $template
	 */
	protected function renderCustomAdminTemplate($template) {
		$this->customTemplate = $template;
	}

	/**
	 * Determine, if the action controller should preload the page setup before calling the main action method.
	 *
	 * @param string $pageName
	 * @param ActionDescriptionInterface $actionDescription
	 * @param RenderInfoInterface $renderInfo
	 * @return bool
	 */
	abstract protected function shouldLoadPage(string $pageName, ActionDescriptionInterface $actionDescription, RenderInfoInterface $renderInfo): bool;

	/**
	 * Returning true will call the target action method also for the administration.
	 * Please note that the following render* methods are deactivated:
	 *
	 *
	 *
	 * @param string $pageName
	 * @return bool
	 */
	protected function shouldCallMainActionForAdmin(string $pageName): bool {
		return in_array($pageName, $this->pagesWithAdminAction);
	}

	/**
	 * @inheritDoc
	 */
	protected function renderResponse(Response $response)
	{
		if($this->lockSubclassMethods) {
			trigger_error("Can not modify response in admin action method.", E_USER_WARNING);
			return;
		}
		parent::renderResponse($response);
	}

	/**
	 * @inheritDoc
	 */
	protected function renderStream(callable $streamHandler, int $contentLength = 0)
	{
		if($this->lockSubclassMethods) {
			trigger_error("Can not modify stream response in admin action method.", E_USER_WARNING);
			return;
		}
		parent::renderStream($streamHandler, $contentLength); // TODO: Change the autogenerated stub
	}

	/**
	 * @inheritDoc
	 */
	protected function renderTemplate($template, array $children = [])
	{
		if($this->lockSubclassMethods) {
			trigger_error("Can not modify basic templates in admin action method.", E_USER_WARNING);
			return;
		}
		parent::renderTemplate($template, $children); // TODO: Change the autogenerated stub
	}

	/**
	 * Renders all data model, configurations and templates to deliver the defined page.
	 *
	 * @param $pageName
	 */
	protected function renderPage($pageName) {
		/** @var PDO $PDO */
		$PDO = $this->PDO;

		if($page = $PDO->selectOne("SELECT * FROM SKY_PC_PAGE WHERE name = ? OR id = ? LIMIT 1", [$pageName, $pageName])) {
			if($page["title"])
				$this->renderTitle($page['title']);
			if($page["description"])
				$this->renderDescription($page["description"]);

			foreach($PDO->select("SELECT
    SKY_PC_PLACEHOLDER.name,
    CASE
        WHEN valueType = 7
            THEN CONCAT(SKY_PC_DAY_NAME.id, ' ', SKY_PC_DAY_NAME.name, ' ', SKY_PC_DAY_NAME.short)
        WHEN valueType = 9
            THEN CONCAT(SKY_PC_MONTH_NAME.id, ' ', SKY_PC_MONTH_NAME.name, ' ', SKY_PC_MONTH_NAME.short)
        ELSE
            value
        END AS value,
    valueClass,
    (SKY_PC_PLACEHOLDER.options & 0xFF) & SKY_PC_VALUE_TYPE.options AS options
FROM SKY_PC_PLACEHOLDER_Q
         JOIN SKY_PC_PLACEHOLDER ON SKY_PC_PLACEHOLDER_Q.placeholder = SKY_PC_PLACEHOLDER.id
         JOIN SKY_PC_VALUE_TYPE ON SKY_PC_VALUE_TYPE.id = valueType
         LEFT JOIN SKY_PC_DAY_NAME ON value = SKY_PC_DAY_NAME.id AND valueType = 7
         LEFT JOIN SKY_PC_MONTH_NAME ON value = SKY_PC_MONTH_NAME.id AND valueType = 9
    LEFT JOIN SKY_PC_CONDITION ON d_condition = SKY_PC_CONDITION.id
WHERE page = :page AND (SKY_PC_CONDITION.id IS NULL OR
                    (SKY_PC_CONDITION.options & 1 = 0 OR (SKY_PC_CONDITION.options & 1 = 1 AND NOW() >= date_from)) AND
                    (SKY_PC_CONDITION.options & 2 = 0 OR (SKY_PC_CONDITION.options & 2 = 2 AND NOW() <= date_until)) AND
                    (SKY_PC_CONDITION.options & 4 = 0 OR (SKY_PC_CONDITION.options & 4 = 4 AND :host IN (ip1, ip2, ip3, ip4)))
    )", ['page' => $page['id'], 'host' => $_SERVER["REMOTE_ADDR"]]) as $record) {
				$this->cachedPlaceholders[ $record["name"] ] = $this->generateValue($record);
			}

			$this->renderTemplate($page['main_template'], [
				'Content' => $page["content_template"]
			]);
		} else {
			($detail = new SkylineKernelDetailedException("Page Not Found", 404))->setDetails("The rrquested page $pageName was not found on this server.");
			throw $detail;
		}
	}


	/**
	 * @param DynamicPlaceholder $placeholder
	 * @return string|array
	 */
	public function renderDynamicContents(DynamicPlaceholder $placeholder) {
		if($placeholder instanceof DynamicIterationPlaceholder) {
			/** @var PDO $PDO */
			$PDO = $this->PDO;
			$ROWS = [];
			foreach($PDO->select("SELECT
    CASE
        WHEN value_type = 7
            THEN CONCAT(SKY_PC_DAY_NAME.id, ' ', SKY_PC_DAY_NAME.name, ' ', SKY_PC_DAY_NAME.short)
        WHEN value_type = 9
            THEN CONCAT(SKY_PC_MONTH_NAME.id, ' ', SKY_PC_MONTH_NAME.name, ' ', SKY_PC_MONTH_NAME.short)
        ELSE
            value
        END AS value,
    SKY_PC_ITERATION_HEADER.name,
    valueClass,
    (SKY_PC_ITERATION.options & 0xFF) & SKY_PC_VALUE_TYPE.options AS options,
       row
FROM SKY_PC_ITERATION_CELL
JOIN SKY_PC_ITERATION_HEADER ON header = SKY_PC_ITERATION_HEADER.id
    JOIN SKY_PC_ITERATION_HEADER_Q ON SKY_PC_ITERATION_HEADER_Q.header = SKY_PC_ITERATION_HEADER.id
    JOIN SKY_PC_ITERATION ON iteration = SKY_PC_ITERATION.id
JOIN SKY_PC_VALUE_TYPE ON value_type = SKY_PC_VALUE_TYPE.id
LEFT JOIN SKY_PC_DAY_NAME ON value = SKY_PC_DAY_NAME.id AND value_type = 7
LEFT JOIN SKY_PC_MONTH_NAME ON value = SKY_PC_MONTH_NAME.id AND value_type = 9
WHERE iteration = :iteration OR SKY_PC_ITERATION.name = :iteration AND visibility_group & visibility = visibility ORDER BY row, col", ['iteration' => $placeholder->getName()]) as $record) {
				$r = $record["row"] * 1;
				$c = $record["name"];

				$ROWS[$r][$c] = $this->generateValue($record);
			}

			return $ROWS;
		}

		$value = $this->cachedPlaceholders[ $placeholder->getName() ] ?? '';
		if(preg_match("/^\\$(\w+)\\$$/i", $value, $ms)) {
			$model = $this->getRenderInfo()->get( RenderInfoInterface::INFO_MODEL );
			if($model instanceof ArrayModel) {
				$value = $model->getValueForKey( $ms[1] );
			}
		} elseif(preg_match("/^%([^%]+)%$/i", $value, $ms)) {
			$value = ServiceManager::generalServiceManager()->getParameter( $ms[1] );
		}
		return $value;
	}

	public function generateValue(array $record): ?ValueInterface {
		if(is_null($record["value"])) {
			return NULL;
		} elseif(class_exists($class = "Skyline\\PageControl\\Placeholder\\Value\\{$record['valueClass']}")) {
			try {
				return new $class( $record['value'], $record['options'] );
			} catch (ValueException $exception) {
				$exception->setDesiredValueClass($class);
				$exception->setRepresentation($record['value']);
				return $this->handleValueCreationException($exception);
			}
		} else {
			trigger_error("Value class {$record['valueClass']} not found", E_USER_WARNING);
		}
		return NULL;
	}

	/**
	 * @param ValueException $exception
	 */
	protected function handleValueCreationException(ValueException $exception): ?ValueInterface {
		trigger_error($exception->getMessage(), E_USER_WARNING);
		return NULL;
	}

	/**
	 * @param DynamicPlaceholder $placeholder
	 * @return $this
	 */
	public function pushPlaceholder(DynamicPlaceholder $placeholder) {
		$this->placeholderStack[] = $placeholder;
		return $this;
	}

	/**
	 * @return DynamicPlaceholder|null
	 */
	public function getCurrentPlaceholder(): ?DynamicPlaceholder {
		return $this->placeholderStack[ count($this->placeholderStack) -1 ] ?? NULL;
	}

	public function getCurrentPlaceholderStack(): array {
		return $this->placeholderStack;
	}

	public function popPlaceholder() {
		array_pop($this->placeholderStack);
	}

	/**
	 * Returns whether an admin control is available for the current rendering action or not.
	 *
	 * @return bool
	 */
	public function existsAdminAccess(): bool {
		return $this->existsAdminAccess;
	}

	/**
	 * Returns whether the current client is authorized to receive admin access to the current rendering page.
	 *
	 * @return bool
	 */
	public function hasAdminAccess(): bool {
		return $this->hasAdminAccess;
	}

	/**
	 * Returns whether the current client is in admin rendering for the current page.
	 *
	 * @return bool
	 */
	public function isAdminAccess(): bool {
		return $this->isAdminAccess;
	}

	/**
	 * @return string|null
	 */
	public function getPageName(): ?string {
		return $this->pageName;
	}
}