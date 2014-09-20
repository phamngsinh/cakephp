/**
 * Set up tags for forms that rely on the tagged behavior.
 * 
 * Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * 
 * @filesource
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package infinitas.tags
 * @subpackage infinitas.tags.assets
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.8a
 * 
 * @author dogmatic69
 * 
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 */

$(document).ready(function() {
	var tags = $('#' + Infinitas.model + 'GlobalTags');
	if(typeof tags.tagEditor != 'function') {
		return;
	}
		
	tags.tagEditor({
		confirmRemoval: false,
		completeOnSeparator: true,
		completeOnBlur: true,
		continuousOutputBuild: true
	});

	$('ul.tagEditor').prepend(
		'<li title="Remove All Tags" id="' + Infinitas.model + 'GlobalTagsReset">Remove All</li>'
	);

	$('#' + Infinitas.model + 'GlobalTagsReset').click(function() {
		$('#' + Infinitas.model + 'GlobalTags').tagEditorResetTags();
	});
});
