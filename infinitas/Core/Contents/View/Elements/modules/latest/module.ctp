<?php
$config = array_merge(array(
	'title' => 'Latest Content',
	'limit' => 5,
	'truncate' => 60,
), $config);
if (!empty($config['id'])) {
	$config['model']= $config['id'];
}

if (isset($config['model']) && $config['model'] === '1') {
	$config['model'] = implode('.', current(array_values($this->request->models)));
}

if (isset($config['category']) && $config['category'] === '1') {
	$config['category'] = null;
	if (!empty($this->request->params['category'])) {
		$config['category'] = $this->request->params['category'];
	}
}

if (!empty($config['model'])) {
	list($plugin,) = pluginSplit($config['model']);
	if ($plugin != $this->plugin && empty($config['id'])) {
		return;
	}
}

$model = null;
$plugin = $this->request->plugin;
if (!empty($config['model']) && is_string($config['model'])) {
	list($plugin, $model) = pluginSplit($config['model']);
}

if (empty($config['model'])) {
	$config['model'] = implode('.', current(array_values($this->request->models)));
}

if (empty($latestContents)) {
	$findMethod = !empty($findMethod) ? $findMethod : 'latestList';
	$latestContents = ClassRegistry::init('Contents.GlobalContent')->find($findMethod, array(
		'limit' => $config['limit'],
		'model' => !empty($config['model']) ? $config['model'] : null,
		'category' => !empty($config['category']) ? $config['category'] : null
	));
}

if (empty($latestContents)) {
	return;
}
if (empty($model)) {
	list(,$model) = pluginSplit($config['model']);
}
if (empty($plugin)) {
	list($plugin,) = pluginSplit($config['model']);
}

foreach ($latestContents as &$latestContent) {
	$latestContent[$model] = &$latestContent['GlobalContent'];
	$url = $this->Event->trigger(
		current(pluginSplit($latestContent['GlobalContent']['model'])) . '.slugUrl',
		array('data' => $latestContent)
	);

	$url = current(current($url));

	$latestContent = $this->Html->link(
		String::truncate($latestContent['GlobalContent']['title'], $config['truncate']),
		InfinitasRouter::url($url)
	);
}

echo $this->Html->tag('div', implode('', array(
	$this->Html->tag('h4', $config['title']),
	$this->Design->arrayToList($latestContents, array(
		'div' => 'content'
	))
)), array('class' => 'span3'));