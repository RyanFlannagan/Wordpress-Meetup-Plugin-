<?php
class WP_Meetup_Options extends WP_Meetup_Model {
    
    private $option_map;
    private $options = array();
    private $category_id;
    
    function __construct() {
	parent::__construct();
        $this->option_map = array(
	    'api_key' => 'wp_meetup_api_key',
	    'group_url_name' => 'wp_meetup_group_url_name',
	    'category' => array('wp_meetup_category', 'events'),
	    'publish_buffer' => array('wp_meetup_publish_buffer', '2 weeks')
	);
        
        $this->get_all_options();
        
        if (!$this->category_id = get_cat_ID($this->options['category'])) {
            $result = wp_insert_term($this->options['category'], 'category');
            $this->category_id = $result['term_taxonomy_id'];
        }
    }
    
    function get($option_key) {
	
        if ($option_key == 'category_id')
            return $this->category_id;
        
	if (is_array($option_value = $this->option_map[$option_key])) {
	    $internal_key = $option_value[0];
	    $default_value = $option_value[1];
	} else {
	    $internal_key = $option_value;
	    $default_value = FALSE;
	}
	
	$this->options[$option_key] = get_option($internal_key, $default_value);
	
	return $this->options[$option_key];
	
    }
    
    function set($option_key, $value) {
	
	if (is_array($option_value = $this->option_map[$option_key])) {
	    $internal_key = $option_value[0];
	} else {
	    $internal_key = $option_value;
	}
	
	$this->options[$option_key] = $value;
	
	if ($option_key == 'category') {
	    if (!$this->category_id = get_cat_ID($this->options['category'])) {
		$result = wp_insert_term($this->options['category'], 'category');
                $this->category_id = $result['term_taxonomy_id'];
	    }
	}
	
	update_option($internal_key, $value);
	
	return TRUE;
    }
    
    function get_all_options() {
	foreach ($this->option_map as $option_key => $value) {
	    $this->get($option_key);
	}
    }
    
    function delete_all() {
        foreach ($this->option_map as $key => $value) {
	    delete_option(is_array($value) ? $value[0] : $value);
	}
    }
    
}