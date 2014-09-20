<?php
	/**
	 * Management Modules admin edit post.
	 *
	 * this page is for admin to manage the modules on the site
	 *
	 * Copyright (c) 2009 Carl Sutton ( dogmatic69 )
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice.
	 *
	 * @filesource
	 * @copyright	 Copyright (c) 2009 Carl Sutton ( dogmatic69 )
	 * @link		  http://infinitas-cms.org
	 * @package	   management
	 * @subpackage	management.views.configs.admin_edit
	 * @license	   http://www.opensource.org/licenses/mit-license.php The MIT License
	 */

	echo $this->Form->create('Contact', array('type' => 'file'));
		echo $this->Infinitas->adminEditHead(); ?>
		<fieldset>
			<h1><?php echo __d('contact', 'Contact Details'); ?></h1><?php
			echo $this->Form->input('id');
			echo $this->Form->input('image', array('type' => 'file'));
			echo $this->Form->input('branch_id', array('empty' => __d('contact', Configure::read('Website.empty_select'))));
			echo $this->Form->input('first_name');
			echo $this->Form->input('last_name');
			echo $this->Form->input('email');
			echo $this->Form->input('active'); ?>
		</fieldset>
		<fieldset>
			<h1><?php echo __d('contact', 'Optional'); ?></h1><?php
			echo $this->Form->input('position');
			echo $this->Form->input('phone');
			echo $this->Form->input('mobile');
			echo $this->Form->input('skype');
			echo $this->Form->input('configs'); ?>
		</fieldset>
	<?php echo $this->Form->end(); ?>