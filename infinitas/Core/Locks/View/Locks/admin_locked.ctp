<?php
	/*
	 * Short Description / title.
	 *
	 * Overview of what the file does. About a paragraph or two
	 *
	 * Copyright (c) 2010 Carl Sutton ( dogmatic69 )
	 *
	 * @filesource
	 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
	 * @link http://www.infinitas-cms.org
	 * @package {see_below}
	 * @subpackage {see_below}
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @since {check_current_milestone_in_lighthouse}
	 *
	 * @author {your_name}
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice.
	 */

$notice = sprintf(
	__d('locks', 'The record you are trying to view has been locked by another user. %s to go back'),
	$this->Html->link(__d('locks', 'click here'), env('HTTP_REFERER'))
);
echo $this->Design->dashboard('', __d('locks', 'Content Locked'), array(
	'alert' => $notice
));
