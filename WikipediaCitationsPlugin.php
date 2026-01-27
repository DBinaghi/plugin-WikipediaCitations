<?php
class WikipediaCitationsPlugin extends Omeka_Plugin_AbstractPlugin
{
	protected $_hooks = array(
		'install',
		'uninstall',
		'initialize'
	);

	protected $_filters = array(
		'item_citation'
	);

	public function hookInstall()
	{
		$this->_installOptions();
	}

	public function hookUninstall()
	{
		$this->_uninstallOptions();
	}
	
	public function hookInitialize()
	{
		add_translation_source(dirname(__FILE__) . '/languages');
	}

	public function filterItemCitation($citation, $args)
	{
		$item = $args['item'];
		$components = [];

		// creators
		$creators = metadata($item, array('Dublin Core', 'Creator'), array('all' => true));
		// Strip formatting and remove empty creator elements.
		$creators = array_filter(array_map('strip_formatting', $creators));
		if ($creators) {
			switch (count($creators)) {
				case 1:
					$components[] = preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[0]);
					break;
				case 2:
					/// Chicago-style item citation: two authors
					$components[] = __('%1$s and %2$s', preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[0]), preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[1]));
					break;
				case 3:
					/// Chicago-style item citation: three authors
					$components[] = __('%1$s, %2$s and %3$s', preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[0]), preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[1]), preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[2]));
					break;
				default:
					/// Chicago-style item citation: more than three authors
					$components[] = __('%s et al.', preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[0]));
			}
		}
		
        // title
		if (metadata($item, 'display_title')) $components[] = '<em>' . strip_tags(metadata($item, 'display_title')) . '</em>';
		
		// publishers
		$publishers = metadata($item, array('Dublin Core', 'Publisher'), array('all' => true));
		// Strip formatting and remove empty publisher elements.
		$publishers = array_filter(array_map('strip_formatting', $publishers));
		if ($publishers) {
			switch (count($publishers)) {
				case 1:
					$components[] = $publishers[0];
					break;
				case 2:
					/// Chicago-style item citation: two authors
					$components[] = __('%1$s and %2$s', $publishers[0], $publishers[1]);
					break;
				case 3:
					/// Chicago-style item citation: three authors
					$components[] = __('%1$s, %2$s and %3$s', $publishers[0], $publishers[1], $publishers[2]);
					break;
				default:
					/// Chicago-style item citation: more than three authors
					$components[] = __('%s et al.', $publishers[0]);
			}
		}

        // date
		$date = strip_tags(metadata($item, array('Dublin Core', 'Date')));
		if (strlen($date) > 3) $components[] = substr($date, 0, 4);

		// isbn-issn-identifier
		if (element_exists('Item Type Metadata', 'ISBN')) {
		    if (metadata($item, array('Item Type Metadata', 'ISBN'))) $components[] = 'ISBN ' . strip_tags(metadata($item, array('Item Type Metadata', 'ISBN'))); 
		} elseif (element_exists('Item Type Metadata', 'ISSN')) {
		    if (metadata($item, array('Item Type Metadata', 'ISSN'))) $components[] = 'ISSN ' . strip_tags(metadata($item, array('Item Type Metadata', 'ISSN'))); 
		} elseif (!is_null(metadata($item, array('Dublin Core', 'Identifier'))) && (strpos(metadata($item, array('Dublin Core', 'Identifier')), 'ISBN') !== false || strpos(metadata($item, array('Dublin Core', 'Identifier')), 'ISSN') !== false)) {
			$components[] = strip_tags(metadata($item, array('Dublin Core', 'Identifier')));
		}
		
		// access date
		$accessed = format_date(time(), Zend_Date::DATE_LONG);

		// url
		$url = WEB_ROOT . '/items/show/' . $item->id;
		
		$citation = implode(', ', $components) . ', ' . __('accessed %1$s, %2$s', $accessed, '<span class="citation-url">' . html_escape($url) . '</span>');
    
		return $citation;
	}   
}
