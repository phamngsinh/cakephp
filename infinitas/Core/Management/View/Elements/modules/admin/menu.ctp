<?php
	$menuToLoad  = (isset($config['menu'])?$config['menu']:'core_admin');
	$type = (isset($config['type'])?$config['type']:'horizontal');

	echo $this->Infinitas->generateDropdownMenu(ClassRegistry::init('Menus.MenuItem')->find('menu', $menuToLoad), $type);
?>