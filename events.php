<?php

class WP_Meetup_Events {

    public $parent;
    public $table_name;
    private $wpdb;
    
    function WP_Meetup_Events() {
        global $wpdb;
        $this->wpdb = &$wpdb;
    }
    
    function create_table() {
        $sql = "CREATE TABLE `{$this->table_name}` (
            `id` tinytext NOT NULL,
            `name` text NOT NULL,
            `description` longtext NOT NULL,
            `visibility` tinytext NOT NULL,
            `status` tinytext NOT NULL,
            `time` int(11) NOT NULL,
            `utc_offset` int(10) unsigned NOT NULL,
            `event_url` varchar(255) NOT NULL,
            `venue` longtext,
            `rsvp_limit` int(11) DEFAULT NULL,
            `yes_rsvp_count` int(11) NOT NULL,
            `maybe_rsvp_count` int(11) NOT NULL,
            PRIMARY KEY (`id`(16))
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
          ";
          
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    function drop_table() {
        $sql = "DROP TABLE `{$this->table_name}`";
        
        $this->wpdb->query($sql);
    }
    
    function save($event) {
        $data = (array) $event;
        $data['venue'] = $event->venue ? serialize($event->venue) : NULL;
        //$this->parent->pr($data);

        $this->wpdb->insert($this->table_name, $data);
        
    }
    
    function save_all($events = array()) {
        //$this->parent->pr($events);
        $data = array();
        foreach ($events as $key => $event) {
            $event_data = array(
                'id' => $event->id,
                'name' => $event->name,
                'description' => '',//$event->description,
                'visibility' => $event->visibility,
                'status' => $event->status,
                'time' => $event->time,
                'utc_offset' => $event->utc_offset,
                'event_url' => $event->event_url,
                'venue' => property_exists($event, 'venue') ? $event->venue : NULL,
                'rsvp_limit' => property_exists($event, 'rsvp_limit') ? $event->rsvp_limit : NULL,
                'yes_rsvp_count' => $event->yes_rsvp_count,
                'maybe_rsvp_count' => $event->maybe_rsvp_count
            );
            $data[] = (object) $event_data;
        }
        
        foreach ($data as $new_event) {
            $this->save($new_event);
        }
        //$this->parent->pr($data);
        
    }
    
    

}