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
	    $this->pr($group_url_names);
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
	    $response = $this->mu_api->getResponse();
	    //$this->pr($response->results);
	    $events = array_merge($events, $response->results); 
	}
        
        return $events;
        
    }
    
    function get_group($group_url_name = FALSE, $api_key = FALSE) {
        
	if (!$group_url_name)
	    $group_url_name = $this->options->get('group_url_name');
	    
	if (!$api_key)
	    $api_key = $this->options->get('api_key');
	    
	if (!$api_key || !$group_url_name)
	    return FALSE;
	    
	//$this->pr($group_url_name, $api_key);
	
        $this->mu_api = new MeetupAPIBase($api_key, 'groups');
        $this->mu_api->setQuery( array('group_urlname' => $group_url_name) ); 
        set_time_limit(0);
        $this->mu_api->setPageSize(200);
        $group_info = $this->mu_api->getResponse();
	
        return (count($group_info->results) > 0) ? $group_info->results[0] : FALSE;
        
    }
    
}