<?php
class WP_Meetup_Events extends WP_Meetup_Model {

    private $wpdb;
    
    function __construct() {
        parent::__construct();
        global $wpdb;
        $this->wpdb = &$wpdb;
        $this->table_name = $this->table_prefix . "events";
        $this->import_model('groups');
        $this->import_model('event_posts');
    }
    
    function create_table() {
        $sql = "CREATE TABLE `{$this->table_name}` (
  `id` tinytext NOT NULL,
  `group_id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `name` text NOT NULL,
  `description` longtext NOT NULL,
  `visibility` tinytext NOT NULL,
  `status` tinytext NOT NULL,
  `time` int(11) NOT NULL,
  `utc_offset` int(10) NOT NULL,
  `event_url` varchar(255) NOT NULL,
  `venue` longtext,
  `rsvp_limit` int(11) DEFAULT NULL,
  `yes_rsvp_count` int(11) NOT NULL,
  `maybe_rsvp_count` int(11) NOT NULL,
  PRIMARY KEY (`id`(16)),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
          
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    function drop_table() {
        $sql = "DROP TABLE `{$this->table_name}`";
        
        $this->wpdb->query($sql);
    }
    
    function get_all() {
        $results = $this->wpdb->get_results("SELECT * FROM `{$this->table_name}` ORDER BY `time`", "OBJECT");
        foreach ($results as $key => $result) {
            $results[$key]->venue = unserialize($result->venue);
            $results[$key]->post = get_post($result->post_id);
            $results[$key]->group = $this->groups->get($result->group_id);
        }
        //pr($results);
        return $results;
    }
    
    function get_all_upcoming() {
        $today = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
        $results = $this->wpdb->get_results("SELECT * FROM `{$this->table_name}` WHERE `time` >= '{$today}' ORDER BY `time`", "OBJECT");
	foreach ($results as $key => $result) {
	    $results[$key]->venue = unserialize($result->venue);
            $results[$key]->post = get_post($result->post_id);
            $results[$key]->group = $this->groups->get($result->group_id);
        }
        //pr($results);
        return $results;
    }
    
    function get_upcoming($limit = 5) {
        $today = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
        $results = $this->wpdb->get_results("SELECT * FROM `{$this->table_name}` WHERE `time` >= '{$today}' ORDER BY `time` LIMIT {$limit}", "OBJECT");
        foreach ($results as $key => $result) {
            $results[$key]->venue = unserialize($result->venue);
            $results[$key]->post = get_post($result->post_id);
            $results[$key]->group = $this->groups->get($result->group_id);
        }
        //pr($results);
        return $results;
    }
    
    function get($event_id) {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM `{$this->table_name}` WHERE `id` = %s", array($event_id)));
    }
    
    function get_by_post_id($post_id) {
        if ($result = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM `{$this->table_name}` WHERE `post_id` = %s", array($post_id)))) {
            $result->venue = unserialize($result->venue);
            $result->group = $this->groups->get($result->group_id);
            return $result;
        } else {
            return FALSE;
        }
    }
    
    function get_by_group_id($group_id) {
        $results = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM `{$this->table_name}` WHERE `group_id` = %d", array($group_id)), "OBJECT");
        //$this->pr($results);
        foreach ($results as $key => $result) {
            $results[$key]->venue = unserialize($result->venue);
            $results[$key]->post = get_post($result->post_id);
            $results[$key]->group = $this->groups->get($result->group_id);
        }
        //pr($results);
        return $results;
    }
    
    function save($event) {
        $data = (array) $event;
        $data['venue'] = $event->venue ? serialize($event->venue) : NULL;
        
        if ($row = $this->get($event->id)) {
            unset($data['id']);
            $this->wpdb->update($this->table_name, $data, array('id' => $event->id));
        } else {
            $this->wpdb->insert($this->table_name, $data);
        }
        
    }
    
    function save_all($events = array()) {
        //$this->parent->pr($events);
        $data = array();
        foreach ($events as $key => $event) {
            $event_data = array(
                'id' => $event->id,
                'group_id' => $event->group->id,
                'name' => $event->name,
                'description' => $event->description,
                'visibility' => $event->visibility,
                'status' => $event->status,
                'time' => $event->time,
                'utc_offset' => $event->utc_offset/1000,
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
    
    function update_post_id($event_id, $post_id) {
        //$this->parent->pr($event_id, $post_id);
        $sql = "UPDATE `{$this->table_name}` SET `post_id` = {$post_id} WHERE `id` = '{$event_id}'";
        
        $this->wpdb->query($sql);
    }
    
    function clear_post_ids() {
        $sql = "UPDATE `{$this->table_name}` SET `post_id` = NULL";
        
        $this->wpdb->query($sql);
    }
    
    function remove($event_id) {
        $event = $this->get($event_id);
        $this->event_posts->remove($event->post_id);
        $this->wpdb->query($this->wpdb->prepare("DELETE FROM {$this->table_name} WHERE `id` = %s", array($event_id)));
    }
    
    function remove_all() {
        $sql = "TRUNCATE TABLE `{$this->table_name}`";
        $this->wpdb->query($sql);
    }
    
    function remove_by_group_id($group_id) {
        
        $events = $this->get_by_group_id($group_id);
        foreach ($events as $event) {
            //$this->pr('deleting event ' . $event->id);
            //$this->pr($this->get($event->id));
            $this->remove($event->id);
        }
        
    }

}
