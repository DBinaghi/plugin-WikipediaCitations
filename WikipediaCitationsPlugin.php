<?php
class WikipediaCitationsPlugin extends Omeka_Plugin_AbstractPlugin
{

	protected $_hooks = array(
		'install',
		'uninstall',
	);

	protected $_filters = array(
		'item_citation',
	);

	public function hookInstall()
	{
		$this->_installOptions();
	}

	public function hookUninstall()
	{
		$this->_uninstallOptions();
	}
	
	public function filterItemCitation($citation,$args)
	{
		$item = $args['item'];
		
		$creators = metadata($item, array('Dublin Core', 'Creator'), array('all' => true));
        // Strip formatting and remove empty creator elements.
        $creators = array_filter(array_map('strip_formatting', $creators));
        if ($creators) {
            switch (count($creators)) {
                case 1:
                    $creator = preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[0]) . ', ';
                    break;
                case 2:
                    /// Chicago-style item citation: two authors
                    $creator = __('%1$s and %2$s', preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[0]), preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[1])) . ', ';
                    break;
                case 3:
                    /// Chicago-style item citation: three authors
                    $creator = __('%1$s, %2$s, and %3$s', preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[0]), preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[1]), preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[2])) . ', ';
                    break;
                default:
                    /// Chicago-style item citation: more than three authors
                    $creator = __('%s et al.', preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $creators[0])) . ', ';
            }
        }
        
        $title = metadata($item, 'display_title') ? '<em>' . metadata($item, 'display_title') . '</em>, ' : null;
		
        $publishers = metadata($item, array('Dublin Core', 'Publisher'), array('all' => true));
        // Strip formatting and remove empty creator elements.
        $publishers = array_filter(array_map('strip_formatting', $publishers));
        if ($publishers) {
            switch (count($publishers)) {
                case 1:
                    $publisher = $publishers[0] . ', ';
                    break;
                case 2:
                    /// Chicago-style item citation: two authors
                    $publisher = __('%1$s and %2$s', $publishers[0], $publishers[1]) . ', ';
                    break;
                case 3:
                    /// Chicago-style item citation: three authors
                    $publisher = __('%1$s, %2$s, and %3$s', $publishers[0], $publishers[1], $publishers[2]) . ', ';
                    break;
                default:
                    /// Chicago-style item citation: more than three authors
                    $publisher = __('%s et al.', $publishers[0]) . ', ';
			}
		}

        $date = metadata($item, array('Dublin Core', 'Date')) ? date('Y', metadata($item, array('Dublin Core', 'Date'))) . ', ' : null;

        $extra = null;
		
		if (element_exists('Item Type Metadata', 'ISBN')) {
	        
			$extra = metadata($item, array('Item Type Metadata', 'ISBN')) ? 'ISBN ' . metadata($item, array('Item Type Metadata', 'ISBN')) . ', ' : null; 
	       
        } 
		
		if (is_null($extra) && element_exists('Item Type Metadata', 'ISSN')) {
			
			$extra = metadata($item, array('Item Type Metadata', 'ISSN')) ? 'ISSN ' . metadata($item, array('Item Type Metadata', 'ISSN')) . ', ' : null; 
			
		}
        
        $accessed = format_date(time(), Zend_Date::DATE_LONG);

		$url = WEB_ROOT . '/items/show/' . $item->id;
        $url = '<span class="citation-url">' . html_escape($url) . '</span>';

		$citation = $creator . $title . $publisher . $date . $extra . ' ' . __('accessed %1$s, %2$s.', $accessed, $url);

        return $citation;
    }   
}