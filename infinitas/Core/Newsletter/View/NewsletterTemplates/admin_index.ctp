<?php
    /**
     * Newsletter Templates admin index
     *
     * this is the page for admins to view all the templates in the newsletter
     * plugin.
     *
     * Copyright (c) 2009 Carl Sutton ( dogmatic69 )
     *
     * Licensed under The MIT License
     * Redistributions of files must retain the above copyright notice.
     *
     * @filesource
     * @copyright     Copyright (c) 2009 Carl Sutton ( dogmatic69 )
     * @link          http://infinitas-cms.org
     * @package       newsletter
     * @subpackage    newsletter.views.templates.admin_index
     * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
     */
?>
<?php
	echo $this->Form->create(null, array('action' => 'mass'));
	echo $this->Infinitas->adminIndexHead($filterOptions, array(
		'add',
		'edit',
		'copy',
		'view',
		'export',
		'delete'
	));
?>
<table class="listing">
	<?php
		echo $this->Infinitas->adminTableHeader(array(
			$this->Form->checkbox('all') => array(
				'class' => 'first'
			),
			$this->Paginator->sort('name'),
			$this->Paginator->sort('modified') => array(
				'class' => 'date'
			),
			__d('newsletter', 'Status') => array(
				'class' => 'small'
			)
		));

		foreach ($templates as $template) { ?>
			<tr>
				<td><?php echo $this->Infinitas->massActionCheckBox($template); ?>&nbsp;</td>
				<td>
					<?php
						echo $this->Html->link($template['NewsletterTemplate']['name'], array(
							'action' => 'edit',
							$template['NewsletterTemplate']['id']
						));
					?>&nbsp;
				</td>
				<td><?php echo $this->Infinitas->date($template['NewsletterTemplate']); ?></td>
				<td><?php //echo $this->Locked->display($template); ?>&nbsp;</td>
			</tr> <?php
		}
	?>
</table>
<?php
	echo $this->Form->end();
	echo $this->element('pagination/admin/navigation'); ?>