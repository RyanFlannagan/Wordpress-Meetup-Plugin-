<div class="wrap">
<?php
    //$this->pr($events);
    
?>
<h2>WP Meetup Options</h2>
<p class="description">
    Options for Meetup.com integration. <a href="http://wordpress.org/extend/plugins/wp-meetup/">Visit plugin page</a>.
</p>
<?php
$this->pr($groups);
?>


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

<?php
$post_status_map = array(
    'publish' => 'Published',
    'pending' => 'Pending',
    'draft' => 'Draft',
    'future' => 'Scheduled',
    'private' => 'Private',
    'trash' => 'Trashed'
);

$headings = array(
    'Event Name',
    'Event Date',
    'Date Posted',
    'RSVP Count'
);
$rows = array();
foreach ($events as $event) {
    $rows[] = array(
        $this->element('a', $event->name, array('href' => get_permalink($event->post_id))),
        date('D M j, Y, g:i A', $event->time + $event->utc_offset/1000),
        date('Y/m/d', strtotime($event->post->post_date)) . "<br />" . $post_status_map[$event->post->post_status],
        $event->yes_rsvp_count . " going"
    );
}
echo $this->data_table($headings, $rows);

?>

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
<?php
if (count($groups) > 0) {
    
    $rows = array();
    foreach ($groups as $group) {
        $rows[] = array(
            $group->name,
            $this->element('a', $group->link, array('href' => $group->link))
        );
    }
    echo $this->data_table(array('Group Name', 'Meetup.com Link'), $rows);
}
?>
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

<?php if ($group): ?>
<h3>Update Events Posts</h3>
<p>
    WP Meetup fetches the latest updates to your meetup events every hour and updates your event posts accordingly.  However, if you want recent changes to be reflected immediately, you can force an update by clicking "Update Event Posts."
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

