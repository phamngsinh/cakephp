<?php
/**
 * Category view
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) 2009 Carl Sutton ( dogmatic69 )
 * @link http://infinitas-cms.org
 * @package Contents.View
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.5a
 */

$category['GlobalCategory']['before_event'] = '';
$eventData = $this->Event->trigger('contentsBeforeCategoryRender', array('content' => $category));
foreach ((array)$eventData['contentsBeforeCategoryRender'] as $_plugin => $_data) {
	$category['GlobalCategory']['before_event'] .= '<div class="before '.$_plugin.'">'.$_data.'</div>';
}

$category['GlobalCategory']['after_event'] = '';
$eventData = $this->Event->trigger('contentsAfterCategoryRender', array('content' => $category));
foreach ((array)$eventData['contentsAfterCategoryRender'] as $_plugin => $_data) {
	$category['GlobalCategory']['after_event'] .= '<div class="after '.$_plugin.'">'.$_data.'</div>';
}

if (isset($category['GlobalCategory']['created'])) {
	$category['GlobalCategory']['created'] = $this->Time->niceShort($category['GlobalCategory']['created']);
}
if (isset($category['GlobalCategory']['modified'])) {
	$category['GlobalCategory']['modified'] = $this->Time->niceShort($category['GlobalCategory']['modified']);
}

$category['GlobalCategory']['item_count'] = sprintf(__d('contents', '%d items'), $category['GlobalCategory']['item_count']);
$category['GlobalCategory']['author'] = $this->GlobalContents->author($category);

// need to overwrite the stuff in the viewVars for mustache
if (!empty($category['CategoryContent'])) {
	if (!empty($category['CategoryContent']['Contents.GlobalCategory'])) {
		$currentCategory = array_shift($category['CategoryContent']['Contents.GlobalCategory']);
		unset($category['CategoryContent']['Contents.GlobalCategory']);
	}

	$_relatedOut = array(
		'<div class="row related">',
		sprintf('<h3>%s</h3>', __d('contents', 'Related Content'))
	);

	$idsDone = array();
	foreach ($category['CategoryContent'] as $model => $relatedContents) {
		$model = pluginSplit($model);

		foreach ($relatedContents as $relatedContent) {
			if (in_array($relatedContent['id'], $idsDone)) {
				continue;
			}

			$idsDone[] = $relatedContent['id'];
			$relatedContent['GlobalCategory'] = $category['GlobalCategory'];
			$relatedContent[$model[1]] = $relatedContent;

			if (!empty($relatedContent['SubCategory'])) {
				$relatedContent['GlobalCategory'] = $relatedContent['SubCategory'];
			}

			$tmp = $this->Event->trigger($model[0] . '.slugUrl', array('data' => $relatedContent));
			$tmp = current($tmp['slugUrl']);

			if (!empty($tmp)) {
				$relatedContent['link'] = InfinitasRouter::url($tmp);

				$_relatedOut[] = $this->element('Contents.related_content', array(
					'data' => $relatedContent,
					'pluginName' => $model[0]
				));
			}
		}
	}
	$_relatedOut[] = '</div>';
	$this->set('relatedContent', implode('', $_relatedOut));
}

if (!empty($category['CategoryContent']['Contents.SubCategory'])) {
	$this->set('subCategory', $category['CategoryContent']['Contents.SubCategory']);
}
if (!empty($category['ParentCategory']['id'])) {
	$this->set('parentCategory', $category['ParentCategory']);
}
$this->set('category', $category);
echo $this->GlobalContents->renderTemplate($category);