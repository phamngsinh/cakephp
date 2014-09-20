<?php
/**
 * WebmasterHelper
 *
 * @package Infinitas.Webmaster.Helper
 */

/**
 * WebmasterHelper
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Webmaster.Helper
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.9a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class WebmasterHelper extends AppHelper {
/**
 * Helpers to load
 *
 * @var array
 */
	public $helpers = array(
		'Text',
		'Html'
	);

/**
 * generate all meta tags
 *
 * @return string
 */
	public function seoMetaTags() {
		$contentIndex = Configure::read($this->plugin . '.robots.' . $this->request->params['action'] . '.index');
		if (isset($this->_View->viewVars['seoContentIndex'])) {
			$contentIndex = $this->_View->viewVars['seoContentIndex'];
		}

		if (!is_bool($contentIndex)) {
			$contentIndex = true;
		}

		$contentFollow = Configure::read($this->plugin . '.robots.' . $this->request->params['action'] . '.follow');
		if (isset($this->_View->viewVars['seoContentFollow'])) {
			$contentFollow = $this->_View->viewVars['seoContentFollow'];
		}

		if (!is_bool($contentFollow)) {
			$contentFollow = true;
		}

		$canonicalUrl = null;
		if (isset($this->_View->viewVars['seoCanonicalUrl'])) {
			$canonicalUrl = $this->_View->viewVars['seoCanonicalUrl'];
		}

		$contentTitle = Configure::read('Website.name');
		if (!empty($this->_View->viewVars['title_for_layout'])) {
			$siteName = Configure::read('Website.name');
			$contentTitle = sprintf('%s :: %s', substr($this->_View->viewVars['title_for_layout'], 0, 66 - strlen($siteName)), $siteName);
		}

		return implode('', array(
			$this->metaIcon(),
			$this->metaCharset(),
			$this->metaRobotTag($contentIndex, $contentFollow),
			$this->metaDescription(),
			$this->metaKeywords(),
			$this->metaTitle($contentTitle),
			$this->metaCanonicalUrl($canonicalUrl),
			$this->metaAuthor(),
			$this->metaGenerator(),
			$this->metaGoogleVerification(),
		));
	}

/**
 * robots tag
 *
 * This helps controll what robots follow and index. For example it is a
 * good idea to not index pagination pages but allow robots to follow the
 * links to get to the content.
 *
 * If calling this method directly pass the params as per normal. If you
 * would like to specify this from the controller or anywhere else
 * set some variables in to the view:
 *  - seoContentIndex
 *  - seoContentFollow
 *
 * the variables should be bool and true is assumed if they are not found
 * or passed in.
 *
 * @param boolean $contentIndex allow robots to index content
 * @param boolean $contentFollow allow robots to follow links
 *
 * @return string
 */
	public function metaRobotTag($contentIndex = true, $contentFollow = true) {
		$robot = array('all');
		if (!$contentIndex || !$contentFollow) {
			$robot = array();
			$robot['index'] = 'noindex';
			if ($contentIndex !== false) {
				$robot['index'] = 'index';
			}

			$robot['follow'] = 'nofollow';
			if ($contentFollow !== false) {
				$robot['follow'] = 'follow';
			}
		}

		return $this->Html->meta(
			array('name' => 'robots', 'content' => implode(',', $robot))
		);
	}

/**
 * set the meta description for the page
 *
 * Description is automatically truncated to 255 so that it is not too
 * long and spamy for the search engines
 *
 * @param string $description the description of the page
 *
 * @return string
 */
	public function metaDescription($description = null) {
		if (!$description && !empty($this->_View->viewVars['seoMetaDescription'])) {
			$description = $this->_View->viewVars['seoMetaDescription'];
		}

		if (!$description) {
			$description = Configure::read($this->plugin . '.meta.description');
		}

		if (!$description) {
			$description = Configure::read('Website.description');
		}

		if (!$description) {
			return false;
		}

		return $this->Html->meta('description', $this->Text->truncate($description, 255));
	}

/**
 * set the meta keywords for the page
 *
 * Keywords are automatically truncated to 255 so that it is not too
 * long and spamy for the search engines
 *
 * @param string|array $keywords string or array of keywords to use
 *
 * @return string
 */
	public function metaKeywords($keywords = null) {
		if (!$keywords && !empty($this->_View->viewVars['seoMetaKeywords'])) {
			$keywords = $this->_View->viewVars['seoMetaKeywords'];
		}

		if (!$keywords) {
			$keywords = Configure::read($this->plugin . '.meta.keywords');
		}

		if (!$keywords) {
			$keywords = Configure::read('Website.keywords');
		}

		if (is_array($keywords)) {
			$keywords = implode(',', $keywords);
		}

		if (!$keywords) {
			return false;
		}

		return $this->Html->meta('keywords', $this->Text->truncate($keywords, 255));
	}

/**
 * tell serach engines which is the correct page
 *
 * As it is possible to get to content from various pages it is best
 * to tell the search engin what the canonical url is. This stops
 * penalties for duplicate content but still allows linkjuice from
 * links to the wrong url
 *
 * @param mixed $canonicalUrl string or array url of the canonical url
 *
 * @return string
 */
	public function metaCanonicalUrl($canonicalUrl = null) {
		if (empty($canonicalUrl)) {
			return false;
		}

		return sprintf('<link rel="canonical" href="%s" />', Router::url($canonicalUrl, true));
	}

/**
 * generate an author meta tag
 *
 * If there is no author specified the site name is used as the author
 *
 * @param string $author the author
 *
 * @return string
 */
	public function metaAuthor($author = null) {
		if (!$author) {
			$author = Configure::read('Website.name');
		}

		return $this->Html->meta(array('name' => 'author', 'content' => $author));
	}

/**
 * generate a generator meta tag
 *
 * If there is no generator specified Infinitas + version numeber is used
 *
 * @param string $generator the generator
 *
 * @return string
 */
	public function metaGenerator($generator = null) {
		if (!$generator) {
			$generator = sprintf('Infinitas %s', Configure::read('Infinitas.version'));
		}

		return $this->Html->meta(array('name' => 'generator', 'content' => $generator));
	}

/**
 * render the favicon tag
 *
 * @return string
 */
	public function metaIcon() {
		return $this->Html->meta('icon');
	}

/**
 * render the charset tag
 *
 * @return string
 */
	public function metaCharset() {
		return $this->Html->charset();
	}

/**
 * provide a google site authentication tag
 *
 * If you need to provide google site authentication add a config under
 * Webmaster.google_site_verification with the value provided by google
 *
 * If the configuration option is set it will automatically insert the
 * correct tags for google to authorise the site.
 *
 * @return string
 */
	public function metaGoogleVerification() {
		if (!Configure::read('Webmaster.google_site_verification')) {
			return false;
		}

		return $this->Html->meta(
			array('name' => 'google-site-verification', 'content' => Configure::read('Webmaster.google_site_verification'))
		);
	}

/**
 * display the page title
 *
 * This should not be longer than 70 chars as that is the limit on google
 * search. The default when using WebmasterHelper::seoMetaTags() is to limit
 * the size of the title by 70 - length of site name so that you have
 * a good title with the site name.
 *
 * @param string $title the title of the page
 *
 * @return string
 */
	public function metaTitle($title = null) {
		if (!$title) {
			return false;
		}

		return sprintf('<title>%s</title>', $title);
	}

/**
 * display a rss feed link
 *
 * The default is to just show the current url + .rss extension.
 * You can use a string url or array url and specify the title and
 * type of feed.
 *
 * @param array $feed the string url or array of data
 *
 * @return string
 */
	public function metaRss($feed = null) {
		if (!is_array($feed)) {
			$feed = array('url' => $feed);
		}

		$feed = array_merge(
			array(
				'url' => $this->here . '.rss',
				'type' => 'application/rss+xml',
				'title' => sprintf(__d('feed', '%s RSS feed'), Configure::read('Website.name'))
			),
			$feed
		);

		return sprintf(
			'<link rel="alternate" type="%s" title="%s" href="%s"/>',
			$feed['type'],
			$feed['title'],
			Router::url($feed['href'], true)
		);
	}

}