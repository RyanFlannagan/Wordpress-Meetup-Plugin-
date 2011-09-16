<?php
require_once(dirname(__FILE__) . '/MeetupAPIBase.php');

/**
 * MeetupAPIMembers 
 * 
 * @package MeetupAPI
 * @version $id$
 * @copyright 2010 WizOne Solutions
 * @author Kevin Kaland <help [at] wizonesolutions [dot] com> 
 * @license GNU Public License Version 2.0
 */

class MeetupAPIMembers extends MeetupAPIBase {
  function __construct($apiKey) {
    $this->method = 'members';
    parent::__construct($apiKey, $this->method);
  }
}

