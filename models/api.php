<?php
class WP_Meetup_Api extends WP_Meetup_Model {
    
    private $mu_api;
    
    function __construct() {
	parent::__construct();
	$this->import_model('options');
    }
    
    function get_events($group_url_names = array(), $start = FALSE, $end = "2m") {
	
	if ($start === FALSE) {
	    $start = mktime(0, 0, 0, date('n'), 1, date('Y'));
	    $start *= 1000;
	    $start = number_format($start, 0, '.', '');
	}

	if (count($group_url_names) == 0 || !$this->options->get('api_key')) {
	    return FALSE;
	}
	
        $this->mu_api = new MeetupAPIBase($this->options->get('api_key'), '2/events');
	
	$events = array();
	
	foreach ($group_url_names as $url_name) {
	    $this->mu_api->setQuery(array(
		'group_urlname' => $url_name,
		'status' => 'upcoming,past',
		'time' => $start . "," . $end
	    ));
    
	    set_time_limit(0);
	    $this->mu_api->setPageSize(200);
	    $this->mu_api->setFormat('json-alt');
	    $response = $this->mu_api->getResponse();
	    //$this->pr($response->results);
	    $events = array_merge($events, $response->results); 
	}
        
        return $events;
        
    }
    
    /**
     * the API call getResponse sometimes generates a bunch of PHP notices, so
     * this function temporarily disables error reporting.  It's ugly, I know.
     */
    function get_group($group_url_name) {
        error_reporting(0);
	$api_key = $this->options->get('api_key');
	    
	if (!$api_key || !$group_url_name)
	    return FALSE;
	
        $this->mu_api = new MeetupAPIBase($api_key, 'groups');
        $this->mu_api->setQuery( array('group_urlname' => $group_url_name) ); 
        set_time_limit(0);
        $this->mu_api->setPageSize(200);
	$this->mu_api->setFormat('json-alt');
	
        $group_info = $this->mu_api->getResponse();
	
        return (count($group_info->results) > 0) ? $group_info->results[0] : FALSE;
        
    }
	
	function is_valid_key($api_key) {
        	error_reporting(0);
		$test_group_url = 'tucsonhiking';
		
		$this->mu_api = new MeetupAPIBase($api_key, 'groups');
		$this->mu_api->setQuery( array('group_urlname' => $test_group_url) ); 
		set_time_limit(0);
		$this->mu_api->setPageSize(200);
		$this->mu_api->setFormat('json-alt');
		
		$response = $this->mu_api->getResponse();
//		$this->pr($response);
		return !property_exists($response, 'problem');
	}
    
}
