<?php
    //var_dump($this->admin_page_url);
    //$this->test();
    
?>
<h2>WP Meetup Options</h2>
<p class="description">
    Options for Meetup.com integration
</p>

<?php if ($has_api_key): ?>

<h3>I have an API KEY</h3>

<?php endif; ?>

<h3>API Key</h3>
<p>
    To use WP Meetup, you need to provide your <a href="http://www.meetup.com/meetup_api/key/">Meetup.com API key</a>.  Just paste that key here:
</p>
<form id="wp-meetup-api-key" action="<?php echo $this->admin_page_url; ?>" method="post">
<p>
    <label>Meetup.com API Key: </label>
    <input type="text" name="api_key" value="<?php echo $this->api_key; ?>" />
</p>
<p>
    <input type="submit" value="Submit" />
</p>
</form>



