<?php

/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/

if (isset($_SERVER['extras_path']))
{
	require_once($_SERVER['extras_path'] . "/config.php");
}

else if (file_exists('config.php'))
{
	include_once('config.php');
}

else 
{
	define('DB_HOST','localhost');
	define('DB_NAME','trends_database');
	define('DB_USER','mysql_username');
	define('DB_PASS','mysql_password');
	
	$base_url = 'https://localhost:4430/nmla-trends/www/nmtrends';
	
	ini_set('display_errors','On');
}

$path = "library/";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

if (isset($_SERVER['extras_path']))
{
	set_include_path(get_include_path() . PATH_SEPARATOR . require_once($_SERVER['extras_path'] . 'vendor/'));
}

if(mysql_connect(DB_HOST,DB_USER,DB_PASS) === false)
{ // Problem connecting to mysql
	echo "Error: Database credentials incorrect - cannot connect to MySQL";
	exit();
}
mysql_select_db(DB_NAME);

require_once('Zend/Auth.php');
require_once('Zend/Auth/Adapter/Interface.php');
require_once('Zend/Auth/Storage/Session.php');



class MyAuthAdapter implements Zend_Auth_Adapter_Interface
{
	protected $_username;
	protected $_password;
    /**
     * Sets username and password for authentication
     *
     * @return void
     */
    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot
     *                                     be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
    	$safe_username = mysql_real_escape_string($this->_username);
    	$safe_password = md5($this->_password);
        $sql = "SELECT * FROM organizations WHERE 1 AND username = '{$safe_username}' AND password = '{$safe_password}' LIMIT 1";
        $result = mysql_query($sql);
        if($result === false)
        {
        	throw new Zend_Auth_Adapter_Exception('The supplied parameters to MySQL failed to '
                                                . 'produce a valid sql statement, please check table and column names '
                                                . 'for validity.');
        }
        if(mysql_num_rows($result) == 1)
        {// Authentication success!
            return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS,$this->_username,array('Authentication successful.'));
        }
        else
        {// Authentication Failure!
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE ,$this->_username,array(''));        	
        }
    }
}

function sql_to_csv($sql, $redact_column = null)
{
	/* The output of this function should exactly match the output of:
	
	select * from cases into outfile '/tmp/cases.csv' 
	fields terminated by ',' enclosed by '"' lines terminated by '\n';
	*/
	
	$output = fopen('php://output', 'w');
	$rows = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($rows))
	{
		foreach ($row as $key => $val)
		{
			if ($val === null)
			{
				$row[$key] = '\N';
			}
			
			else 
			{
				$row[$key] = str_replace('"', '\"', $val);
			}
		}
		
		if ($redact_column !== null)
		{
			$row[$redact_column] = "[redacted]";
		}
		$x = '"' . implode($row, '","') . '"' . "\n";
		$x = str_replace('"\N"', '\N', $x);
		
		fwrite($output, $x);
		flush();
	}
	
	fclose($output);
}

/*	AMW - I added this function to allow browsers to successfully download
	very large (300MB+) files.  However browser downloads still failed with it
	in place.  wget works fine with either method.  I believe the browser is the
	failure point.  I'm going to leave the code in, in case it's useful later.
	*/
function chunk_table($table, $key)
{
	$chunk_size = 10000;  // When this was set to 100, it ran quite slow.
	$safe_key = mysql_real_escape_string($key);
	$safe_table = mysql_real_escape_string($table);
	$result = mysql_query("SELECT MAX({$safe_key}) FROM {$safe_table}");
	$row = mysql_fetch_array($result);
	$max = $row[0];
	
	for ($i = 0; $i < $max; $i = $i + $chunk_size)
	{
		sql_to_csv("SELECT * FROM {$safe_table} ORDER BY {$safe_key} DESC LIMIT {$i}, {$chunk_size}");
	}
}

if(!defined('NO_AUTH'))
{// Calling script has not disabled Auth
	if(Zend_Auth::getInstance()->hasIdentity() !== true)
	{// Check persistant storage to see if user already exists
		header("Location: login.php");
	}
	else
	{
		$session = new Zend_Auth_Storage_Session(null,'last_uri');
		$session->write($_SERVER['REQUEST_URI']);
	}
}
