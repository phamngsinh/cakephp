<?php
	/**
	 * Configuration file for the Script Combiner helper. This file is used to determine
	 * the behaviour of the helper.
	 *
	 * @author Geoffrey Garbers
	 * @version 0.1
	 */
	$config['Assets'] = array(
		'timestamp' => true,
		'combineCss' => true,
		'combineJs' => true,
		'compressCss' => true,
		'compressJs' => false,
		'cacheLength' => '1 year',
		'cssCachePath' => CSS . 'combined' . DS,
		'jsCachePath' => JS . 'combined' . DS,
		'fileSeparator' => "\n\n/** FILE SEPARATOR **/\n\n",
		'bootstrap' => true
	);

	/**
	 * Core icons
	 */
	$config['CoreImages']['path'] = '/assets/img/icons/';
	$config['CoreImages']['images'] = array(
		'actions' => array(
			'accept' => 'accept.png',
			'add' => 'add.png',
			'addCategory' => 'addCategory.png',
			'addItem' => '',
			'arrow-down' => 'arrow-down.png',
			'arrow-left' => 'arrow-left.png',
			'arrow-right' => 'arrow-right.png',
			'arrow-up' => 'arrow-up.png',
			'cancel' => 'cancel.png',
			'copy' => 'copy.png',
			'cron_toggle' => 'cronjob.png',
			'date' => 'date.png',
			'delete' => 'trash.png',
			'download' => 'download.png',
			'edit' => 'edit.png',
			'export' => 'zip.png',
			'favourite' => 'favourite.png',
			'featured' => 'featured.png',
			'filter' => 'filter.png',
			'install' => 'install.png',
			'-locked' => 'locked.png',
			'move' => 'move.png',
			'new-window' => 'new_window.png',
			'order-asc' => 'order-asc.png',
			'order-desc' => 'order-desc.png',
			'preview' => 'preview.png',
			'print' => 'print.png',
			'reload' => 'reload.png',
			'remove' => 'remove.png',
			'restore' => 'restore.png',
			'save' => 'save.png',
			'search' => 'search.png',
			'send' => 'send.png',
			'sinc' => 'sinc.png',
			'stats' => 'stats.png',
			'system_toggle' => 'system.png',
			'toggle' => 'toggle.png',
			'trash' => 'trash.png',
			'uninstall' => 'uninstall.png',
			'unlock' => 'reload.png',
			'unlocked' => 'unlocked.png',
			'unzip' => 'unzip.png',
			'update' => 'update.png',
			'upload' => 'upload.png',
			'view' => 'view.png',
			'zip' => 'zip.png',
			'zoom-in' => 'zoom-in.png',
			'zoom-out' => 'zoom-out.png'
		),

		'applications' => array(
			'ai' => '',
			'doc' => 'doc.png',
			'docx' => 'docx.png',
			'dwt' => 'dwt.png',
			'eps' => '',
			'mirc' => 'mirc.png',
			'ods' => 'ooMaths.png',
			'odt' => 'ooWord.png',
			'pdf' => 'pdf.png',
			'ppt' => 'ppt.png',
			'pptx' => 'ppt.png',
			'ps' => 'ps.png',
			'psd' => 'psd.png',
			'rtf' => '',
			'txt' => 'txt.png',
			'xls' => 'sxl.png',
			'xlsx' => 'xlsx.png'
		),

		'archives' => array(
			'7z' => '7z.png',
			'cab' => 'cab.png',
			'dll' => 'dll.png',
			'exe' => 'exe.png',
			'msi' => '',
			'rar' => 'win-rar.png',
			'tar' => 'tar.png',
			'zip' => '',
		),

		'folders' => array(
			'config' => 'config.png',
			'documents' => 'documents.png',
			'empty' => 'empty.png',
			'images' => 'images.png',
			'mixed' => 'mixed.png',
			'music' => 'music.png',
			'sys-linux' => 'systemLinux.png',
			'sys-win' => 'systemWin.png',
			'video' => 'video.png',
			'web' => 'web.png'
		),

		'images' => array(
			'bmp' => 'bmp.png',
			'gif' => 'gif.png',
			'ico' => '',
			'jpe' => 'jpg.png',
			'jpeg' => 'jpg.png',
			'jpg' => 'jpg.png',
			'png' => 'png.png',
			'svg' => 'svg.png',
			'svgz' => '',
			'tga' => 'tga/png',
			'tif' => 'tiff.png',
			'tiff' => 'tiff.png'
		),

		'multiMedia' => array(
			'3gp' => '3gp.png',
			'dvi' => 'dvi.png',
			'mov' => 'mov.png',
			'mp2' => 'mp2.png',
			'mp3' => 'mp3.png',
			'mp4' => 'mp4.png',
			'mpeg' => 'mpeg.png',
			'ogg' => 'ogg.png',
			'ra' => 'rm.png',
			'ram' => 'rm.png',
			'rm' => 'rm.png',
			'rmi' => 'rm.png',
			's3m' => 's3m.png',
			'qt' => 'qt.png',
			'vlc' => 'vlc.png',
			'wav' => 'wav.png',
			'wma' => 'wma.png'
		),

		'notifications' => array(
			'forbidden' => 'forbidden.png',
			'help' => 'help.png',
			'info' => 'info.png',
			'message' => 'message.png',
			'status' => 'status.png',
			'stop' => 'stop.png',
			'success' => 'success.png',
			'warning' => 'warning.png',
			'loading' => 'loading.gif'
		),

		'social' => array(
			'badoo' => '',
			'gmail' => '',
			'google' => '',
			'facebook' => '',
			'icq' => 'icq.png',
			'last-fm' => 'last-fm.png',
			'msn' => 'msn.png',
			'picasa' => 'picasa.png',
			'rss' => 'rss.png',
			'skype' => 'skype.png',
			'twitter' => '',
			'vcf' => 'vcf.png',
			'yahoo' => 'yahoo-messanger.png'
		),

		'status' => array(
			'active' => 'active.png',
			'inactive' => 'inactive.png',
			'home' => 'home.png',
			'not-home' => 'not-home.png',
			'locked' => 'locked.png',
			'not-locked' => 'not-locked.png',
			'featured' => 'featured.png',
			'not-featured' => 'not-featured.png',
		),

		'unknown' => array(
			'unknown' => 'unknown.png',
			'readme' => 'readme.png',
			'' => 'readme.png'
		),

		'weather' => array(
			'clear' => 'clear.png',
			'clear-night' => 'clear-night.png',
			'cloudy' => 'cloudy.png',
			'fog' => 'fog.png',
			'partly-coudy' => 'partly-coudy.png',
			'partly-cloudy-night' => 'partly-cloudy-night.png',
			'showers' => 'showers.png',
			'snow' => 'snow.png',
			'storm' => 'storm.png'
		),

		'web' => array(
			'asx' => 'asx.png',
			'css' => 'css.png',
			'flv' => 'flv.png',
			'htm' => 'html.png',
			'html' => 'html.png',
			'java' => 'java.png',
			'js' => 'js.png',
			'json' => '',
			'php' => 'php.png',
			'php3' => 'php.png',
			'php4' => 'php.png',
			'php5' => 'php.png',
			'php6' => 'php.png',
			'py' => 'py.png',
			'sql' => 'sql.png',
			'swf' => 'flv.png',
			'xml' => 'xml.png'
		)
	);