<div class="wrap">
<?php
    //var_dump($this->admin_page_url);
    //$this->test();
    
?>
<h2>WP Meetup Options</h2>
<p class="description">
    Options for Meetup.com integration by <a href="http://nuancedmedia.com/">Nuanced Media</a>.
</p>



<?php foreach ($this->feedback as $message_type => $messages): ?>

<?php foreach ($messages as $message): ?>
<p class="<?php echo $message_type; ?>"><?php echo $message; ?></p>
<?php endforeach; ?>

<?php endforeach; ?>



<?php if ($events): ?>
<h3>Events (Upcoming in the next month)</h3>
<pre>
<?php //var_dump($events); ?>
</pre>

<table class="widefat">
<thead>
    <tr>
        <th>Event Name</th>
        <th>Event Date</th>
        <th>Date Posted</th>
        <th>RSVP Count</th>
    </tr>
</thead>
<tfoot>
    <tr>
        <th>Event Name</th>
        <th>Event Date</th>
        <th>Date Posted</th>
        <th>RSVP Count</th>
    </tr>
</tfoot>
<tbody>
   
<?php foreach($events as $event): ?>
<tr>
    <td><a href="<?php echo get_permalink($event->ID); ?>"><?php echo $event->post_title; ?></a></td>
    <td><?php echo date('D M j, Y, g:i A', get_post_meta($event->ID, 'wp_meetup_time', TRUE)); ?></td>
    <td><?php echo date('D M j, Y, g:i A', strtotime($event->post_date)); ?></td>
    <td><?php echo get_post_meta($event->ID, 'wp_meetup_rsvp_count', TRUE); ?> going</td>
</tr>
<?php endforeach; ?>
   
</tbody>
</table>


<?php endif; ?>




<h3>API Key</h3>
<p>
    To use WP Meetup, you need to provide your <a href="http://www.meetup.com/meetup_api/key/">Meetup.com API key</a>.  Just paste that key here:
</p>
<form id="wp-meetup-api-key" action="<?php echo $this->admin_page_url; ?>" method="post">
<p>
    <label>Meetup.com API Key: </label>
    <input type="text" name="api_key" value="<?php echo $this->options['api_key']; ?>" />
</p>
<p>
    <input type="submit" value="Submit" class="button-secondary" />
</p>
</form>



<h3>Group Information</h3>
<p>
    To pull in your Meetup.com events, provide your group's Meetup.com URL, e.g. "http://www.meetup.com/tucsonhiking"
</p>
<form id="wp-meetup-api-key" action="<?php echo $this->admin_page_url; ?>" method="post">
<p>
    <label>Meetup.com Group URL: </label>
    <input type="text" name="group_url" value="<?php echo $group_url; ?>" />
</p>
<p>
    <input type="submit" value="Submit" class="button-secondary" />
</p>
</form>

</div><!--.wrap-->

