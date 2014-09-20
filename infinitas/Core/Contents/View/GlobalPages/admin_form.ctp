<?php
	/**
	 * Static Page admin edit
	 *
	 * Editing current static pages
	 *
	 * Copyright (c) 2010 Carl Sutton ( dogmatic69 )
	 *
	 * @filesource
	 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
	 * @link http://www.infinitas-cms.org
	 * @package management
	 * @subpackage management.views.admin_edit
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @since 0.7a
	 *
	 * @author dakota
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice.
	 */

	echo $this->Form->create('GlobalPage');
		echo $this->Infinitas->adminEditHead(); 
		
		echo $this->Form->hidden('GlobalPage.file_name');
		$tabs = array(
			__d('contents', 'Content')
		);
		
		$options = array('type' => 'text');
		if (strstr($this->request->params['action'], 'edit')) {
			$options['readonly'] = true;
		}
		
		$body = array(
			$this->Form->input('GlobalPage.name', $options) . $this->Infinitas->wysiwyg('GlobalPage.body')
		);
		
		echo $this->Design->tabs($tabs, $body);
			
	echo $this->Form->end();
?>