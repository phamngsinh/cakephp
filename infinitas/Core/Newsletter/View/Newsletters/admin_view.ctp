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
    echo $this->Letter->adminOtherHead();
?>
<h3><?php echo __d('newsletter', 'Newsletter Preview'); ?></h3>
<?php
    echo $this->Letter->preview($newsletter['Newsletter']['id'], 'newsletters');
?>

<h3><?php echo __d('newsletter', 'Test in a mail client'); ?></h3>
<?php
    echo $this->Form->create('Newsletter', array('action' => 'view'));
    echo '<p>', __d('newsletter', 'Enter the email addresses you would like to send to seperated by a , {comma} (but with no spaces)'), '</p>';
    echo $this->Form->input('id', array('value' => $this->data['Newsletter']['id']));
    echo $this->Form->input('email_addresses', array('type' => 'textarea', 'class' => 'title'));
    echo $this->Form->end('Send the test');
?>