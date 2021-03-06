<?php

/*
 * Copyright (C) 2015 Nathan Crause - All rights reserved
 *
 * This file is part of Socialfeed
 *
 * Copying, modification, duplication in whole or in part without
 * the express written consent of the copyright holder is
 * expressly prohibited under the Berne Convention and the
 * Buenos Aires Convention.
 */

namespace Claromentis\Socialfeed\Template\Engine;

use TemplaterComponentTmpl;
use Claromentis\Socialfeed\Template\Engine;

/**
 * This class provides a template engine based on a templater component object
 *
 * @author Nathan Crause
 */
class Component implements Engine {
	
	/**
	 *
	 * @var ComponentProxy 
	 */
	private $component;
	
	public function __construct(TemplaterComponentTmpl $component) {
		$this->component = new ComponentProxy($component);
	}

	public function render($template, array $args) {
		return $this->component->invokeTemplater($template, $args);
	}

}
