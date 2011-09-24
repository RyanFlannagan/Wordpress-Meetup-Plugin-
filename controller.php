<?php
class WP_Meetup_Controller extends WP_Meetup {
    
    public $event_posts;
    public $events;
    public $options;
    public $api;
    
    function WP_Meetup_Controller() {
        parent::WP_Meetup();
        $this->event_posts = new WP_Meetup_Event_Posts();
	
	$this->events = new WP_Meetup_Events();
	$this->events->table_name = $this->table_prefix . "events";
	
	$this->options = new WP_Meetup_Options();
	$this->api = new WP_Meetup_Api();
    }
    
}