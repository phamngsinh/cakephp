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
?>
<div class="install form">
	    <p>
			<?php
				echo __d('installer',  'Database tables and core data is about to be installed. '.
			    	'Please make sure you have created a database already, then fill out the '.
			    	'connection details below. If the installer is not able to connect to the database '.
			    	'the installer will not be able to continue', true );
			?>
		</p>
	    <p>
			<?php
				echo __d('installer',  'If you are unsure how to create a database, please contact your hosting provider\'s technical support.', true );
			?>
		</p>
		<?php if (isset($dbError) && $dbError === true) {?>
			<div class="general-error">
				<?php echo __d('installer', 'There is an error with your database connection. Please ensure that the details supplied are correct and that the database server is running.'); ?>
			</div>
		<?php }?>
		<?php if (isset($versionError)) {?>
			<div class="general-error">
				<?php echo sprintf(__d('installer', 'The database server you selected is running version %1$s. Infinitas requires at least %3$s version %2$s.'), $versionError, $requiredDb['version'], $requiredDb['name']); ?>
			</div>
		<?php }?>
		<?php if (isset($adminDbError) && $adminDbError === true) {?>
			<div class="general-error">
				<?php echo __d('installer', 'There is an error with the database administrator details you supplied. Please ensure that they are correct and that the user does have permissions to create, drop and alter tables in the database.'); ?>
			</div>
		<?php }?>

		<h4 class="field-heading">Basic database settings</h4>
		<div>
			<?php
				$serverName = pathinfo(env('SERVER_NAME'));
				$serverName = $serverName['filename'];
				echo $this->Form->input( 'Install.datasource', array(
					'label' => 'Database driver',
					'options' => $database,
					'empty' => '-- Database driver --',
					'after' => '<div>'.__d('installer', 'What type of database server should Infinitas use').'</div>'
				));

				echo $this->Form->input( 'Install.host', array(
					'label' => 'Database host',
					'value' => isset($this->data['Install']) ? $this->data['Install']['host'] : 'localhost',
					'after' => '<div>'.__d('installer', 'The server for your database. <strong>localhost</strong> should work, if it does not then you can get the info from your web host.').'</div>'
				));

				echo $this->Form->input( 'Install.login', array(
					'label' => 'Database username',
					'value' => isset($this->data['Install']) ? $this->data['Install']['login'] : $serverName,
					'after' => '<div>'.__d('installer', 'The username to access the database').'</div>'
				));

				echo $this->Form->input( 'Install.password', array(
					'type' => 'text',
					'label' => 'Database password',
					'value' => isset($this->data['Install']) ? $this->data['Install']['password'] : $serverName,
					'after' =>'<div>'. __d('installer', 'The password to your database').'</div>'
				));

				echo $this->Form->input( 'Install.database', array(
					'label' => 'Database name',
					'value' => isset($this->data['Install']) ? $this->data['Install']['database'] : $serverName,
					'after' => '<div>'.__d('installer', 'This is the name of the database Infinitas will use').'</div>'
				));
			?>
		</div>

		<h4 class="field-heading">Advanced database settings</h4>
		<div>
			<?php
				echo $this->Form->input( 'Install.port', array(
					'label' => 'Database port',
					'after' => '<div>'.__d('installer', 'This is the port your database server runs is on, leave blank for default.').'</div>'
				));

				echo $this->Form->input( 'Install.prefix', array(
					'label' => 'Table prefix',
					'after' => '<div>'.__d('installer', 'This is a prefix for all the tables that infinitas will create. Useful if you want to share the database with other applications.').'</div>'
				));
			?>
		</div>

		<h4 class="field-heading">Database administrator details</h4>
		<div>
			<blockquote>
				<p>
					If the database user you supplied above does not have the necessary permissions to create tables,
					please enter the details of a user that does. This information will not be saved, and will only be used during this installation process.
				</p>
			</blockquote>
			<?php
				echo $this->Form->input( 'Admin.username', array(
					'label' => 'Database administrator username',
					'value' => isset($this->data['Admin']) ? $this->data['Admin']['username'] : '',
					'after' => '<div>'.__d('installer', 'The username of a user that can create tables in your database.').'</div><br style="clear:both;" />'
				));

				echo $this->Form->input( 'Admin.password', array(
					'type' => 'text',
					'label' => 'Database administrator password',
					'value' => isset($this->data['Admin']) ? $this->data['Admin']['password'] : '',
					'after' =>'<div>'. __d('installer', 'The password for the above user.').'</div><br style="clear:both;" />'
				));
			?>
		</div>
</div>