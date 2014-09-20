<?php
	$config['Contents'] = array(
		'truncate_category' => 100,
		 'slugUrl' => array(
			 'category' => array(
				 'SubCategory.slug' => 'category',
				 'GlobalCategory.id' => 'id',
				 'GlobalCategory.slug' => 'slug',
				 'GlobalCategory.GlobalContent.foreign_key' => 'id',
				 'GlobalCategory.GlobalContent.slug' => 'slug',
				 'url' => array(
					 'plugin' => 'contents',
					 'controller' => 'global_categories',
					 'action' => 'view'
				 )
			 ),
		 ),
		'GlobalCagegories' => array(
			'auto_redirect' => true,
			'robots' => array(
				'index' => array(
					'index' => false,
					'follow' => true
				),
				'view' => array(
					'index' => true,
					'follow' => true
				)
			)
		),
		'page_path' => CakePlugin::path('Contents') . 'webroot' . DS . 'pages',
		'info' => array(
			'reports' => __d('contents', 'Content reports can help identify issues with your content, including possible SEO problems'),
			'contents' => __d('contents', 'This section is the base for your site')
		)
	);