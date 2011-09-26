<div class="wrap">
<?php
    //$this->pr($events);
    
?>
<h2>WP Meetup Options</h2>
<p class="description">
    Options for Meetup.com integration. <a href="http://wordpress.org/extend/plugins/wp-meetup/">Visit plugin page</a>.
</p>



<?php foreach ($this->feedback as $message_type => $messages): ?>

<?php foreach ($messages as $message): ?>
<div class="<?php echo $message_type == 'error' ? 'error' : 'updated'; ?>"><p><?php echo $message; ?></p></div>
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
<?php
$post_status_map = array(
    'publish' => 'Published',
    'pending' => 'Pending',
    'draft' => 'Draft',
    'future' => 'Scheduled',
    'private' => 'Private',
    'trash' => 'Trashed'
);
?>
<?php foreach($events as $event): ?>
<tr>
    <td><a href="<?php echo get_permalink($event->post_id); ?>"><?php echo $event->name; ?></a></td>
    <td><?php echo date('D M j, Y, g:i A', $event->time + $event->utc_offset/1000); ?></td>
    <td><?php echo date('Y/m/d', strtotime($event->post->post_date)); ?><br /><?php echo $post_status_map[$event->post->post_status];//($event->post->post_status == 'future') ? "Scheduled" : "Published"; ?></td>
    <td><?php echo $event->yes_rsvp_count; ?> going</td>
</tr>
<?php endforeach; ?>
   
</tbody>
</table>

<?php elseif($group != FALSE): ?>

<p>There are no available events listed for this group.</p>

<?php endif; ?>



<form action="<?php echo $this->admin_page_url; ?>" method="post">
<h3>API Key</h3>
<p>
    To use WP Meetup, you need to provide your <a href="http://www.meetup.com/meetup_api/key/">Meetup.com API key</a>.  Just paste that key here:
</p>

<p>
    <label>Meetup.com API Key: </label>
    <input type="text" name="api_key" size="30" value="<?php echo $this->options->get('api_key'); ?>" />
</p>



<h3>Group Information</h3>
<p>
    To pull in your Meetup.com events, provide your group's Meetup.com URL, e.g. "http://www.meetup.com/tucsonhiking"
</p>
<p>
    <label>Meetup.com Group URL: </label>
    <input type="text" name="group_url" size="30" value="<?php echo $group_url; ?>" />
</p>

<?php
$date_select = "<select name=\"publish_buffer\">";
$options = array(
    '1 week' => '1 weeks',
    '2 weeks' => '2 weeks',
    '1 month' => '1 month'
);
foreach ($options as $label => $value) {
    $date_select .= "<option value=\"{$value}\"" . ($this->options->get('publish_buffer') == $value ? ' selected="selected"' : "") . ">$label</option>";
}
$date_select .= "</select>";
?>


<h3>Event-to-Post Options</h3>
<p>
    <label>Categorize each event post as <input type="text" name="category" value="<?php echo $this->options->get('category'); ?>" /></label>
</p>
<p>
    <label>Publish event posts <?php echo $date_select; ?> before the event date.</label>
</p>



<p>
    <input type="submit" value="Update Options" class="button-primary" />
</p>

<?php if ($events): ?>
<h3>Update Events Posts</h3>
<p>
    To better manage resources, WP Meetup does not automatically check Meetup.com for changes to events.  If you add new events or change the information for any events, you need to manually update the event posts.  Clicking "Update Event Posts" below will fetch the updates from Meetup.com and regenerate your event posts.
</p>
<p>
    <input type="submit" name="update_events" value="Update Event Posts" class="button-secondary" />
</p>
<?php endif; ?>

</form>



<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) {return;}
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<p>Powered by <a href="http://nuancedmedia.com/">Nuanced Media</a> <span class="fb-like" data-href="http://www.facebook.com/NuancedMedia" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></span></p>

</div><!--.wrap-->

