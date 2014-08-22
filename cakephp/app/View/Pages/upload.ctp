<?php
echo $this->Form->create('Fileupload',array('type' => 'file'));
echo $this->Form->input('file', array('type' => 'file','label' => false, 'div' => false));
echo $this->Form->end('Upload');
?>