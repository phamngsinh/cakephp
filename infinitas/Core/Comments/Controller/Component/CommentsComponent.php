<?php
App::uses('InfinitasComponent', 'Libs.Controller/Component');

/**
 * Comments component
 */

class CommentsComponent extends InfinitasComponent {

/**
 * Allow posting comments to any controller
 *
 * @return void
 */
	public function actionComment() {
		$modelClass = $this->Controller->modelClass . 'Comment';
		if (!empty($this->Controller->request->data[$modelClass])) {
			$message = 'Your comment has been saved and will be available after admin moderation.';
			if (Configure::read('Comments.auto_moderate') === true) {
				$message = 'Your comment has been saved and is active.';
			}

			$this->Controller->request->data[$modelClass]['ip_address'] = $this->Controller->request->clientIp();
			$this->Controller->request->data[$modelClass]['class'] = $this->Controller->request->plugin . '.' . $this->Controller->modelClass;

			if (!empty($this->Controller->request->data[$modelClass]['om_non_nom'])) {
				$this->Controller->Session->write('Spam.bot', true);
				$this->Controller->Session->write('Spam.detected', time());

				$this->Controller->notice(__d('comments', 'Not so fast spam bot.'), array(
					'redirect' => '/?bot=true'
				));
			}

			$saved = $this->Controller->{$this->Controller->modelClass}->createComment($this->Controller->request->data);
			if ($saved) {
				$this->Event->trigger('Comments.newCommentSaved', $saved[$modelClass]);
				$this->Controller->notice(__d('comments', $message), array(
					'redirect' => true
				));
			}

			$this->Controller->notice('not_saved');
		}

		return $this->Controller->render(null, null, App::pluginPath('Comments') . 'View' . DS . 'InfinitasComments' . DS . 'add.ctp');
	}

}