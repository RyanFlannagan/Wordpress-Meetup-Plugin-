<?php

class WP_Meetup_Event_Posts extends WP_Meetup_Model {
    
    public $wpdb;
    
    function __construct() {
	parent::__construct();
        global $wpdb;
        $this->wpdb = &$wpdb;
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

    
    function save_event($event, $publish_buffer, $category_id) {
        
        $event_adjusted_time = $event->time + $event->utc_offset/1000;
        $post_status = ($event->post_id) ? $event->post->post_status : $this->get_post_status($event_adjusted_time, $publish_buffer);
        
        /*$description = "<div class=\"wp-meetup-event\">";
        $description .= "<a href=\"{$event->event_url}\" class=\"wp-meetup-event-link\">View event on Meetup.com</a>";
        $description .= "<dl class=\"wp-meetup-event-details\">";
        $description .= "<dt>Date</dt><dd>" . date("l, F j, Y, g:i A", $event_adjusted_time) . "</dd>";
        $description .= ($event->venue) ? "<dt>Venue</dt><dd>" .  $event->venue->name . "</dd>" : "";
        $description .= "</dl>";
        if ($show_plug)
            $description .= "<p class=\"wp-meetup-plug\">Meetup.com integration powered by <a href=\"http://nuancedmedia.com/\">Nuanced Media</a>.</p>";
        $description .= "</div>";
        $description .= $event->description;*/

        $post = array(
            'post_category' => array($category_id),
            'post_content' => $event->description,
            'post_title' => $event->name,
            'post_status' => $post_status,
            'post_date' => date("Y-m-d H:i:s", strtotime("-" . $publish_buffer, $event_adjusted_time)) 
        );
        
        if ($event->post_id) {
            $post['ID'] = $event->post_id;
        }

        $post_id = $this->save($post);
	
	clean_post_cache($post_id);

        return $post_id;
        
    }
    
    function save($data) {
        
        $post_id = wp_insert_post($data);

        return $post_id;
    }
    
    function remove($post_id = FALSE) {
	//$this->pr("Time to update post dates");
	//$posts = $this->get_all();
	
	//foreach ($posts_ids as $post_id) {
	    wp_delete_post($post_id);
	//}
	
    }
    
    /*function recategorize($post_id, $old_category_id, $new_category_id) {
        //pr($post_id, $old_category_id, $new_category_id);
        $categories = wp_get_post_categories($post_id);
        if ($old_category_id) {
            foreach ($categories as $key => $category) {
                if ($category == $old_category_id) unset($categories[$key]);
            }
        }
        $categories[] = $new_category_id;
        
        $new_post = array(
            'ID' => $post_id,
            'post_category' => $categories
        );
        wp_update_post($new_post);
    }*/
    
    function recategorize($post_id, $category_id) {
        //pr($post_id, $category_id);
        $new_post = (array) get_post($post_id);
        //pr($new_post);
        $new_post['post_category'] = array($category_id);
        wp_update_post($new_post);
    }
    
    function set_date($post_id, $event_time, $event_utc_offset, $publish_buffer) {
	//$this->pr($post_id, $event_time, $event_utc_offset, $publish_buffer);
	$event_adjusted_time = $event_time + $event_utc_offset/1000;
        $post_status = $this->get_post_status($event_adjusted_time, $publish_buffer, FALSE);
	
        $new_post = array(
            'post_status' => $post_status,
            'post_date' => date("Y-m-d H:i:s", strtotime("-" . $publish_buffer, $event_adjusted_time)),
            'post_date_gmt' => date("Y-m-d H:i:s", strtotime("-" . $publish_buffer, $event_time)),//get_gmt_from_date($post_date),
            'post_modified' => current_time( 'mysql' ),
            'post_modified_gmt' => current_time( 'mysql', 1 )
        );
        //$this->pr($new_post);

        $this->wpdb->update($this->wpdb->posts, $new_post, array('ID' => $post_id), array('%s','%s','%s','%s','%s'), array('%d'));
	
	clean_post_cache($post_id);
    }
    
}