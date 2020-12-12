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

namespace Skyline\PageControl\Placeholder;


use Skyline\PageControl\Controller\AbstractPageController;

class DynamicPlaceholder extends AbstractEditablePlaceholder
{
	/** @var AbstractPageController|null */
	protected static $actionController;

	/** @var int  */
	protected $options = 0;

	// Control the access
	const OPTION_ADMIN_ONLY = 1<<0;
	const OPTION_ROOT_ONLY = 1<<1;
	const OPTION_EDITOR_ONLY = 1<<2;



	/**
	 * @return AbstractPageController|null
	 */
	public static function getActionController(): ?AbstractPageController
	{
		return self::$actionController;
	}

	/**
	 * @param AbstractPageController|null $actionController
	 */
	public static function setActionController(?AbstractPageController $actionController): void
	{
		self::$actionController = $actionController;
	}

	/**
	 * DynamicPlaceholder constructor.
	 * @param string $name
	 * @param string $description
	 * @param int $options
	 */
	public function __construct(string $name, string $description = "", int $options = 0)
	{
		parent::__construct($name);
		$this->setDescription($description);
		$this->options = $options;

		if($cnt = static::getActionController()) {
			$self = $this;
			$this->contents = function() use ($cnt, $self) {
				return $cnt->renderDynamicContents($self);
			};
		}
	}

	public function __toString(): string
	{
		if($cnt = static::getActionController())
			$cnt->pushPlaceholder($this);

		$string = parent::__toString();

		if($cnt = static::getActionController())
			$cnt->popPlaceholder();

		return $string;
	}

	/**
	 * @return int
	 */
	public function getOptions(): int
	{
		return $this->options;
	}
}