<?php
/**
 * View Counter component
 *
 * Track views of rows when users browse the site.
 *
 * @todo session tracking to show the user what they last seen.
 *

 *
 * @filesource
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.ViewCounter.Controller.Component
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.8a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */
App::uses('InfinitasComponent', 'Libs.Controller/Component');

class ViewCounterComponent extends InfinitasComponent {
/**
 * settings for the component
 */
	public $__settings = array(
		'actions' => array(
			'view'
		),
		'admin' => false
	);

/**
 * Bind the behavior for view counts.
 *
 * This is done here due to the fact that we need to know what action
 * we are on, and its only used some of the time. The other option is
 * to bind it always and unbind it here but that is more overhead.
 *
 * @var object $Controller the controller being used
 * @var array $settings the config
 *
 * @return null nothing needed
 */
	public function initialize(Controller $Controller, $settings = array()) {
		parent::initialize($Controller);

		if ($this->Controller->Session->read('Spam.bot') || strstr($this->Controller->request->url, '?spam=true')) {
			return;
		}

		if (empty($this->Controller->modelClass)) {
			// no model being used.
			return;
		}

		$settings = array_merge($this->__settings, (array)$settings);

		$check =
			// dont tack anything in admin
			$settings['admin'] === false &&
			!(isset($Controller->request->params['admin']) && $Controller->request->params['admin']) &&

			// only track actions that are set
			in_array($Controller->request->params['action'], $settings['actions']);

		if ($check) {
			$Controller->{$Controller->modelClass}->Behaviors->attach('ViewCounter.Viewable');
			$Controller->{$Controller->modelClass}->__referer = $this->Controller->referer();
		}
	}

}