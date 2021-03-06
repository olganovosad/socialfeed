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

namespace Claromentis\Socialfeed;

use Background;
use ClaPluginBackground;

/**
 * This background task runs every 15 minutes to update the feeds
 *
 * @author Nathan Crause
 */
class Plugin implements ClaPluginBackground {
	
	public function Background(Background $bg) {
		if ($bg->IsTimePass('SocialfeedUpdate', Stream::TTL)) {
			
		}
	}

}
