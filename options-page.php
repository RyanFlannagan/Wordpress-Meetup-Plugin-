<?php
    //var_dump($this->admin_page_url);
    //$this->test();
    
?>
<h2>WP Meetup Options</h2>
<p class="description">
    Options for Meetup.com integration by <a href="http://nuancedmedia.com/">Nuanced Media</a>
</p>

<?php if ($has_api_key): ?>


<pre>
<?php
        $this->mu_api->setQuery( array('group_urlname' => $this->options['group_url_name']) ); //Replace with a real group's URL name - it's what comes after the www.meetup.com/
        set_time_limit(0);
        $this->mu_api->setPageSize(200);
        $response = $this->mu_api->getResponse();
        //var_dump($response);
?>
</pre>

<h3><?php echo $response->results[0]->name; ?></h3>
<p>
    <?php echo $response->results[0]->description; ?>
</p>

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

