<?php
	if (empty($data['image'])) {
		return;
	}
	
	$title = empty($title) ? __d('contents', 'Image') : $title;
	$imageOptions = empty($imageOptions) ? array('width' => '150px') : $imageOptions;
	$linkOptions = empty($linkOptions) ? array('class' => 'thickbox') : $linkOptions;
?>
<div class="image">
	<?php
		echo sprintf('<span>%s</span>', $title),
		$this->Html->link(
			$this->Html->image(
				$data['content_image_path_small'],
				$imageOptions
			),
			$data['content_image_path_full'],
			array_merge(array('escape' => false), $linkOptions)
		),
		sprintf('<p>%s</p>', $data['image']);
	?>
</div>