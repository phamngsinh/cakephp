<?php
	if (!isset($lastSixMonths) || empty($lastSixMonths)) {
		return false;
	}
?>
<div class="page-header span6">
	<?php
		echo sprintf(
			__d('server_status', '<h1>Load for the last six months<small>Data between %s and %s</small></h1>'),
			$this->Time->niceShort($lastSixMonths['start_date']),
			$this->Time->niceShort($lastSixMonths['end_date'])
		);

		if (empty($lastSixMonths['month'])) {
			echo $this->ViewCounter->noData();
		}

		else{
			echo $this->Charts->draw(
				array(
					'bar' => array(
						'type' => 'vertical_group'
					)
				),
				array(
					'data' => array($lastSixMonths['max_load'], $lastSixMonths['average_load']),
					'axes' => array(
						'x' => $lastSixMonths['month'],
						'y' => true
					),
					'size' => array('width' => 450, 'height' => 130),
					'extra' => array('scale' => 'relative', 'html' => array('class' => 'chart')),
					'spacing' => array(
						'padding' => 2,
						'grouping' => 10,
						'width' => 20,
						'type' => 'absolute'
					),
					'color' => array(
						'series' => array(
							array('0d5c05'),
							array('03348a')
						)
					),
					'legend' => array(
						'position' => 'right',
						'order' => 'default',
						'labels' => array(
							__d('server_status', 'Max Load'),
							__d('server_status', 'Ave Load')
						)
					),
				)
			);
		}
	?>
</div>
