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

namespace Skyline\PageControl\Compiler;


use Skyline\Compiler\AbstractCompiler;
use Skyline\Compiler\CompilerConfiguration;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Helper\ModuleStorageHelper;
use Skyline\Expose\Compiler\AbstractAnnotationCompiler;
use Skyline\Expose\Compiler\AbstractExposedSymbolsCompiler;
use TASoft\Util\PDO;
use Skyline\PageControl\Compiler\Helper\DynamicPlaceholder;

class DynamicPlaceholderCompiler extends AbstractAnnotationCompiler
{
	private $pageControlFile;

	public function __construct(string $compilerID, $pageControlFile, bool $excludeMagicMethods = true)
	{
		parent::__construct($compilerID, $excludeMagicMethods);
		$this->pageControlFile = $pageControlFile;
	}

	/** @var PDO */
	private $PDO;
	/**
	 * @inheritDoc
	 */
	public function compile(CompilerContext $context)
	{
		$storage = new ModuleStorageHelper();

		foreach($this->yieldClasses('ACTIONCONTROLLER') as $class) {
			$list = $this->findClassMethods($class, self::OPT_PUBLIC_OBJECTIVE);
			foreach($list as $name => $method) {
				$annots = $this->getAnnotationsOfMethod($method, true)['page-control'] ?? [];
				if($annots) {
					$infos = [];
					foreach($annots as $annot) {
						if(preg_match("/^\s*(\S+)\s+(.+)$/i", $annot, $ms)) {
							$infos[ $ms[1] ][] = trim($ms[2]);
						}
					}

					if(isset($infos["page"]) && count($infos["page"])) {
						$storage[$name]['page'] = $infos['page'][0];

						if(isset($infos['layout']) && count($infos['layout']) && isset($infos['role']) && count($infos['role'])) {
							$storage[$name]['layout'] = $infos["layout"][0];
							$storage[$name]['roles'] = $infos["role"];
						}
					}
					if(isset($infos['custom']) && count($infos["custom"])) {
						$storage[$name]['custom'] = $infos["custom"][0];
					}
				}
			}
		}

		$dir = $context->getSkylineAppDirectory(CompilerConfiguration::SKYLINE_DIR_COMPILED);

		$data = $storage->exportStorage();
		file_put_contents("$dir/$this->pageControlFile", $data);
		return;

		/** @var PDO $PDO */
		$this->PDO = $context->getServiceManager()->get("PDO");
		$templates = require $context->getSkylineAppDirectory( CompilerConfiguration::SKYLINE_DIR_COMPILED ) . DIRECTORY_SEPARATOR . "templates.config.php";

		foreach($templates['catalog'] as $catalogName => $names) {
			foreach($names as $templateName => $files) {
				$this->readFiles($catalogName, $templateName, $files, $templates);
			}
		}
	}

	private function registerDynamicPlaceholder($name, $description, $options, $catalogName, $templateName, $file, $templates) {
		$pageID = $this->PDO->selectFieldValue("SELECT id FROM SKY_PC_PAGE WHERE name = ?", 'id', [$templateName]);
		if(!$pageID) {
			$this->PDO->inject("INSERT INTO SKY_PC_PAGE (name, title, description, main_template, content_template) VALUES (?, '', '', '', '')")->send([
				$templateName
			]);
			$pageID = $this->PDO->lastInsertId("SKY_PC_PAGE");
		}

		$id = $this->PDO->selectFieldValue("SELECT id FROM SKY_PC_PLACEHOLDER WHERE name = ? AND page = $pageID LIMIT 1", 'id', [$name]);
		if(!$id) {
			$this->PDO->inject("INSERT INTO SKY_PC_PLACEHOLDER (name, page) VALUES (?, ?)")->send([
				$name,
				$pageID
			]);
			$id = $this->PDO->lastInsertId("SKY_PC_PLACEHOLDER");
		}

		$this->PDO->inject("UPDATE SKY_PC_PLACEHOLDER SET description = ?, options = ? WHERE id = $id")->send([
			$description,
			$options * 1
		]);
	}

	private function readFiles($catalogName, $templateName, $files, $templates) {
		DynamicPlaceholder::$interception = function($plName, $description, $options) use ($catalogName, $templateName, &$file, $templates) {
			$this->registerDynamicPlaceholder(
				$plName,
				$description,
				$options,
				$catalogName,
				$templateName,
				$file,
				$templates
			);
		};


		foreach($files as $file) {
			if($file = $templates["files"][$file] ?? NULL) {
				$contents = file_get_contents($file);
				if(preg_match_all("/new\s+DynamicPlaceholder/i", $contents, $ms, PREG_OFFSET_CAPTURE)) {
					foreach($ms[0] as $mms) {
						$pline = preg_split("/<\?php|;/", substr($contents, 0, $mms[1]));
						$line = array_pop($pline);

						$pline = preg_split("/\?>|;/", substr($contents, $mms[1]));
						$line .= array_shift($pline) . ";";

						eval("use Skyline\PageControl\Compiler\Helper\DynamicPlaceholder; $line");
					}
				}
			}
		}
	}
}