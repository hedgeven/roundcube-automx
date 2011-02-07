<?php

/**
 * Enable the plugin: Add "automx" to 
 * the $rcmail_config['plugins'] - array in config/main.inc.php
 *
 * Automatically find out the mx-record from the users email-address
 * and set it as host to make the select-dropdown obsolete
 *
 * Works with a whitelist that can be defined in the config-file
 * for this plugin: config.inc.php
 * 
 * Hides the server-select-dropdown from the login-mask
 *
 * @version 0.8
 * @author Incloud
 */
class automx extends rcube_plugin
{
	private $_whitelist = array();
	public $task = "login|logout";
	
	/**
	 * We only need a javascript file and only hook the authenticate-process
	 */
	function init()
	{
		//parse whitelist from config-file and turn "pseudo-regex" with *.domain.com to a regex
		if ($this->load_config())
		{
			$ar_whitelist = rcmail::get_instance()->config->get('automx_whitelist');
			
			if (is_array($ar_whitelist) && count($ar_whitelist) > 0)
			{
				foreach ($ar_whitelist as $wlstring)
				{
					$wlstring = preg_quote($wlstring);
					$pattern = "~".str_replace('\*', ".*", $wlstring)."~U"; //ungreedy
					$this->_whitelist[] = $pattern;
				}
			}
		}
		
		$this->add_hook('authenticate', array($this, 'process'));
		$this->include_script('automx.js'); //hides select dropdown
	}
	
	/**
	 * If the entered email's mxrecord is in the whitelist, we use it.
	 * Otherwise, we return "localhost" to let roundcube handle the login
	 */
	function process($args)
	{
		if (!isset($args['user']))
			return null;
			
		$server = $this->getServerByEmail($args['user']);

		$isvalid = $this->isValidMailServer($server);
		
		if ($isvalid)
			return array('host' => $server);
		else
			return array('host' => "localhost");
	}
	
	/**
	 * Get mxrecord from email -> 
	 * david.mueller@incloud.de => returns mxrecord of mailserver: mailfoobar.incloud.de
	 * false on any error
	 *
	 * @param $email string: Email-Address of user
	 * @return bool(false) on any error, string with mailserver on success
	 */
	function getServerByEmail($email)
	{
		$email = trim($email);
		
		$email_parts = explode("@",$email,2);

		if (count($email_parts) !== 2 || empty($email_parts[1]))
			return false;
			
		$server = array();
		
		if (!getmxrr($email_parts[1], $server))
			return false;
			
		if (count($server) === 0)
			return false;
			
		return $server[0];
	}

	/**
	 * Checks if the mailserver we found out via getServerByEmail 
	 * is contained in the whitelist
	 * 
	 * @param $mailserver string: Mailserver like mailfoobar.incloud.de
	 * @return bool
	 */
	function isValidMailServer($mailserver)
	{
		$mailserver = trim($mailserver);
		
		if (!is_string($mailserver) || empty($mailserver))
			return false;
		
		$mailserver_parts = explode(".",$mailserver,2);
		
		if (count($mailserver_parts) !== 2 || empty($mailserver_parts[1]))
			return false;

		$valid = false;

		foreach ($this->_whitelist as $serverpattern)
		{
			if (preg_match($serverpattern, $mailserver))
			{
				$valid = true;
				break;
			}
		}

		return $valid;	
	}
}