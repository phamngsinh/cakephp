<?php
	$modelName = (isset($modelName)) ? $modelName : Inflector::singularize($this->name);
	$Model	 = ClassRegistry::init($this->request->plugin . '.' . $modelName);
	$data = &${strtolower($modelName)};
	$allow = Configure::read($this->request->plugin . '.allow_ratings');

	if (Configure::read('Rating.time_limit')) {
		$allow &= date('Y-m-d H:i:s', strtotime('- '.Configure::read('Rating.time_limit'))) < $data[$modelName]['modified'];
	}

	if (!isset(${strtolower($modelName)}[$modelName]['rating']) || $allow !== true) {
		echo __d('management', 'Rating is currently dissabled for this page');
		return false;
	}
?>
<div class="star-rating rating {currentRating: '<?php echo $data[$modelName]['rating']; ?>', url:{action:'rate', id: <?php echo $data[$modelName]['id']; ?>}, target:'this'}">
	<span class="star-rating-result">
		<?php
			$stats = !isset($stats) || !$stats ? false : true;
			if ($data[$modelName]['rating_count'] > 0 && $stats) {
				echo sprintf(__d('management', 'Currently rated %s (out of %s votes)'), $data[$modelName]['rating'], $data[$modelName]['rating_count']);
			}
			else if ($stats) {
				echo sprintf(__d('management', 'This %s has not been rated yet'), prettyName($modelName));
			}
		?>
	</span>
	<div class="coreRatingBox">
		<?php
			echo $this->Form->create(
				$modelName,
				array(
					'url' => array(
						'plugin' => $this->request->params['plugin'],
						'controller' => $this->request->params['controller'],
						'action' => 'rate'
					)
				)
			);

			echo $this->Form->input($modelName.'.'.$Model->primaryKey, array('value' => $data[$modelName][$Model->primaryKey]));
			echo $this->Form->hidden('Rating.class', array('value' => $this->request->plugin . '.' . $modelName));
			echo $this->Form->hidden('Rating.foreign_id', array('value' => $data[$modelName][$Model->primaryKey]));

			echo $this->Form->input(
				'Rating.rating',
				array(
					'type'=>'radio',
					'legend' => false,
					'div' => false,
					'options' => array(1=>1, 2=>2, 3=>3, 4=>4, 5=>5),
					'class' => 'ratingRadioButton'
				)
			);

			echo $form->end('Submit');
		?>
	</div>
</div>