<?php

class WP_Meetup_Event_Posts extends WP_Meetup_Model {
    
    public $wpdb;
    
    function __construct() {
	parent::__construct();
        global $wpdb;
        $this->wpdb = &$wpdb;
	$this->import_model('options');
    }
    
    private function get_post_status($event_adjusted_time, $publish_buffer, $set_drafts = TRUE) {

        $today = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
        
        if (strtotime("+" . $publish_buffer) >= $event_adjusted_time) {
            if ($event_adjusted_time >= $today) {
                return 'publish';
            } else {
                return $set_drafts ? 'draft' : 'publish';
            }
        } else {
            return'future';
        }
        
        return FALSE;
    }

    
    function save_event($event, $publish_buffer) {
        $cpt = TRUE; //$this->options->get('publish_option') == 'cpt';
        $event_adjusted_time = $event->time + $event->utc_offset;
        $post_status = ($event->post_id) ? $event->post->post_status : $this->get_post_status($event_adjusted_time, $publish_buffer, FALSE);

        $post = array(
            'post_content' => $event->description,
            'post_title' => $event->name,
            'post_status' => $post_status,
            'post_date' => date("Y-m-d H:i:s", strtotime("-" . $publish_buffer, $event_adjusted_time)) 
        );
        
        if ($event->post_id) {
            $post['ID'] = $event->post_id;
        }
	
	if ($cpt) {
	    $post['post_type'] = 'wp_meetup_event';
	    //$this->pr(get_taxonomy('wp_meetup_group'));
	    $post['tax_input'] = array('wp_meetup_group' => array($event->group->name));
	}

        $post_id = $this->save($post);
	
	if ($cpt) {
	    $post_meta = array(
		'time' => $event->time,
		'utc_offset' => $event->utc_offset,
		'event_url' => $event->event_url,
		'venue' => $event->venue ? serialize($event->venue) : FALSE,
		'rsvp_limit' => $event->rsvp_limit,
		'yes_rsvp_count' => $event->yes_rsvp_count,
		'maybe_rsvp_count' => $event->maybe_rsvp_count
	    );
	    foreach ($post_meta as $meta_key => $meta_value) {
		if (!update_post_meta($post_id, $meta_key, $meta_value)) {
		    add_post_meta($post_id, $meta_key, $meta_value, TRUE);
		}
	    }
	}
	
	clean_post_cache($post_id);

        return $post_id;
        
    }
    
    function save($data) {
        
        $post_id = wp_insert_post($data);

        return $post_id;
    }
    
    function remove($post_id = FALSE) {
	wp_delete_post($post_id);
    }

function remove_all() {
	$this->wpdb->query("DELETE FROM {$this->wpdb->posts} WHERE `post_type` = 'wp_meetup_event';");
}
    
    /*function recategorize($post_id, $category_id) {
        $new_post = (array) get_post($post_id);
        $new_post['post_category'] = array($category_id);
        wp_update_post($new_post);
    }*/
    
    function set_date($post_id, $event_time, $event_utc_offset, $publish_buffer) {
	$event_adjusted_time = $event_time + $event_utc_offset;
        $post_status = $this->get_post_status($event_adjusted_time, $publish_buffer, FALSE);
	
        $new_post = array(
            'post_status' => $post_status,
            'post_date' => date("Y-m-d H:i:s", strtotime("-" . $publish_buffer, $event_adjusted_time)),
            'post_date_gmt' => date("Y-m-d H:i:s", strtotime("-" . $publish_buffer, $event_time)),//get_gmt_from_date($post_date),
            'post_modified' => current_time( 'mysql' ),
            'post_modified_gmt' => current_time( 'mysql', 1 )
        );

        $this->wpdb->update($this->wpdb->posts, $new_post, array('ID' => $post_id), array('%s','%s','%s','%s','%s'), array('%d'));
	
	clean_post_cache($post_id);
    }
    
}
