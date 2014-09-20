<?php
    /**
     * Comment Template.
     *
     * @todo -c Implement .this needs to be sorted out.
     *
     * Copyright (c) 2009 Carl Sutton ( dogmatic69 )
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @filesource
     * @copyright     Copyright (c) 2009 Carl Sutton ( dogmatic69 )
     * @link          http://infinitas-cms.org
     * @package       sort
     * @subpackage    sort.comments
     * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
     * @since         0.5a
     */

    echo $this->Form->create();
        echo $this->Infinitas->adminEditHead(); ?>
		<fieldset>
			<h1><?php echo __d('newsletter', 'Newsletter Template'); ?></h1><?php
			echo $this->Form->input('id');
			echo $this->Form->input('name', array('class' => 'title'));
			echo $this->Infinitas->wysiwyg('NewsletterTemplate.header');
			echo $this->Infinitas->wysiwyg('NewsletterTemplate.footer'); ?>
		</fieldset><?php
    echo $this->Form->end();