<?php
    //var_dump($this->admin_page_url);
    //$this->test();
    
?>
<h2>WP Meetup Options</h2>
<p class="description">
    Options for Meetup.com integration by <a href="http://nuancedmedia.com/">Nuanced Media</a>
</p>

<?php if ($has_api_key): ?>

<h3>Group Information</h3>
<pre>
<?php

        //var_dump($response);
?>
</pre>

<h4>Group name: <?php echo $group->name; ?></h4>
<p>
    <?php echo $group->description; ?>
</p>

<?php endif; ?>

<?php if (count($events)): ?>
<h3>Events</h3>
<pre>
<?php //var_dump($events); ?>
</pre>
<?php foreach($events as $event): ?>

<h4><a href="<?php echo $event->event_url; ?>"><?php echo $event->name; ?></a></h4>
<p>Time: <?php echo date('D M j, Y, g:i A', $event->time); ?>, <?php echo $event->yes_rsvp_count; ?> going</p>

<?php endforeach; ?>
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
    <input type="submit" value="Submit" />
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
    <input type="submit" value="Submit" />
</p>
</form>

