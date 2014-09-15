<?php

echo $this->Form->create('User', array('type'=>'file'));
echo $this->Form->input('file', array('type'=>'file'));
echo $this->Form->input('dirname', array('type'=>'hidden'));
echo $this->Form->input('basename', array('type'=>'hidden'));
echo $this->Form->input('checksum', array('type'=>'hidden'));
echo $this->Form->end(__('Submit'));
?>

