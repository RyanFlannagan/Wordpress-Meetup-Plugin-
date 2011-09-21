<?php

class WP_Meetup_Event_Posts {
    
    public $wpdb;
    
    function WP_Meetup_Event_Posts() {
        global $wpdb;
        $this->wpdb = &$wpdb;
    }
    
    private function get_post_status($event_adjusted_time, $publish_buffer) {

        $today = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
        
        if (strtotime("+" . $publish_buffer) >= $event_adjusted_time) {
            if ($event_adjusted_time >= $today) {
                return 'publish';
            } else {
                return 'draft';
            }
        } else {
            return'future';
        }
        
        return FALSE;
    }

    
    function save_event($event, $publish_buffer, $category_id, $show_plug) {
        
        $event_adjusted_time = $event->time + $event->utc_offset/1000;
        $post_status = ($event->post_id) ? $event->post->post_status : $this->get_post_status($event_adjusted_time, $publish_buffer);
        
        $description = "<div class=\"wp-meetup-event\">";
        $description .= "<a href=\"{$event->event_url}\" class=\"wp-meetup-event-link\">View event on Meetup.com</a>";
        $description .= "<dl class=\"wp-meetup-event-details\">";
        $description .= "<dt>Date</dt><dd>" . date("l, F j, Y, g:i A", $event_adjusted_time) . "</dd>";
        $description .= ($event->venue) ? "<dt>Venue</dt><dd>" .  $event->venue->name . "</dd>" : "";
        $description .= "</dl>";
        if ($show_plug)
            $description .= "<p class=\"wp-meetup-plug\">Meetup.com integration powered by <a href=\"http://nuancedmedia.com/\">Nuanced Media</a>.</p>";
        $description .= "</div>";
        $description .= $event->description;

        $post = array(
            'post_category' => array($category_id),
            'post_content' => $description,
            'post_title' => $event->name,
            'post_status' => $post_status,
            'post_date' => date("Y-m-d H:i:s", strtotime("-" . $publish_buffer, $event->time)) 
        );
        
        if ($event->post_id)
            $post['ID'] = $event->post_id;

        $post_id = $this->save($post);

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
    
    function recategorize($post_id, $old_category_id, $new_category_id) {
        
        $categories = wp_get_post_categories($post_id);
        foreach ($categories as $key => $category) {
            if ($category == $old_category_id) unset($categories[$key]);
        }
        
        $categories[] = $new_category_id;
        
        $new_post = array(
            'ID' => $post_id,
            'post_category' => $categories
        );
        wp_update_post($new_post);
    }
    
    function set_date($post_id, $event_time, $publish_buffer) {
        //$this->parent->pr($post_id, $event_time, $publish_buffer);
        $post_status = strtotime("+" . $publish_buffer) >=  $event_time ? 'publish' : 'future';
        $post_date = $post_status == 'publish' ? date("Y-m-d H:i:s") : date("Y-m-d H:i:s", strtotime("-" . $publish_buffer, $event_time));
        $new_post = array(
            'post_status' => $post_status,
            'post_date' => $post_date,
            'post_date_gmt' => get_gmt_from_date($post_date),
            'post_modified' => current_time( 'mysql' ),
            'post_modified_gmt' => current_time( 'mysql', 1 )
        );
        //$this->parent->pr($new_post);

        $this->wpdb->update($this->wpdb->posts, $new_post, array('ID' => $post_id), array('%s','%s','%s','%s','%s'), array('%d'));
    }
    
    /*function get_all($id_only = FALSE) {
	$posts = array();
	$the_query = new WP_Query(array(
	    'cat' => $this->parent->category_id,
	    'posts_per_page' => -1
	));
	if ($id_only) {
	    while ($the_query->have_posts()) : $the_query->the_post();
		$posts[] = get_post_meta(get_the_ID(), 'wp_meetup_id', TRUE);
	    endwhile;
	} else {
	    $posts = $the_query->posts;
	}
	wp_reset_query();
	
	return $posts;
    }*/
    
}