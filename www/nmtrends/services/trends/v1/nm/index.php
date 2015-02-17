<?php 

/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/

define('NMTRENDS_NAME','New Mexico Trends System');
define('NMTRENDS_VERSION','1');
define('NMTRENDS_REVISION','0');

define('DB_HOST','localhost');
define('DB_NAME','trends_database');
define('DB_USER','mysql_username');
define('DB_PASS','mysql_password');


function pl_grab_req($var_name = null,$default_value = null)
{
	$request = array();
	switch ($_SERVER['REQUEST_METHOD'])
	{
		case 'GET':
			$request = $_GET;
			break;
		case 'POST':
		case 'PUT':
			parse_str(file_get_contents('php://input'),$request);
			break;
	}
	$value = isset($request[$var_name]) ? $request[$var_name] : $default_value;
	return $value;
}

$action = pl_grab_req('action');


set_time_limit(0);
ini_set('display_errors','Off');
ini_set('memory_limit','999M');

mysql_connect(DB_HOST,DB_USER,DB_PASS);
mysql_select_db(DB_NAME);

$auth_row = array();

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="' . NMTRENDS_NAME . '"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'HTTP/1.0 401 Unauthorized';
    exit;
} else {
   	$safe_password_md5 = mysql_real_escape_string(md5($_SERVER['PHP_AUTH_PW']));
   	$safe_username = mysql_real_escape_string($_SERVER['PHP_AUTH_USER']);
	$sql = "SELECT organizations.* 
			FROM organizations 
			WHERE 1 
			AND username='{$safe_username}' 
			AND password='{$safe_password_md5}' 
			LIMIT 1";
	$result = mysql_query($sql);
	if(mysql_num_rows($result) != 1)
	{
		header('WWW-Authenticate: Basic realm="' . NMTRENDS_NAME . '" stale="FALSE"');
		header('HTTP/1.0 401 Unauthorized');
		exit();
	}
	else
	{
		$auth_row = mysql_fetch_assoc($result);
	}
}


$buffer = "";

switch($action) {
	case 'getLastUploadDate':
		$last_upload = $auth_row['last_upload'];
		if(strlen($last_upload) < 1 || $last_upload == '0000-00-00 00:00:00')
		{
			$last_upload = date('Y-m-d 00:00:00',strtotime("-1 day",time()));
		}
		output_response('json',create_response($last_upload,$action));
		break;
	case 'upload':
		$data = pl_grab_req('data');
		$data = gzuncompress($data);
		$json = json_decode($data,true);
		
		$col_list_array = array(
							'organization_id',
							'cms_case_id',
							'cms_case_created', 
							'open_date',
							'gender', 
							'race',
							'hispanic',
							'disabled',
							'age_over_60',
							'zip',
							'county',
							'opp_first_name',
							'opp_last_name',
							'court_name',
							'judge_name',
							'problem',
							'outcome');
		$col_list = "`" . implode('`, `',$col_list_array) . "`";
		$case_count = 0;

		foreach ($json as $case_row)
		{
			// AMW 2013-09-06
			$p = substr($case_row['problem'], 0, 2); 
			if ($p >= 30 && $p <= 49)
			{
				$case_row['opp_first_name'] = '';
				$case_row['opp_last_name'] = '[Redacted]';
			}

			// AMW 2013-09-10
			if ($case_row['organization_id'] == '4')
			{
				$case_row['opp_first_name'] = '';
				$case_row['opp_last_name'] = '[Redacted]';
			}
			
			$val_list_array = array();
			$case_row['organization_id'] = $auth_row['organization_id'];
			foreach ($col_list_array as $field_name)
			{
				$val_list_array[] = mysql_real_escape_string($case_row[$field_name]);
			}
			
			$val_list = "'" . implode("', '",$val_list_array) . "'";
			$sql = "INSERT INTO cases
					({$col_list})			
					VALUES ({$val_list})";
			 
			mysql_query($sql);
			$case_count++;
		}
		
		
		$sql = "UPDATE organizations
				SET last_upload = DATE_FORMAT(NOW(),'%Y-%m-%d %H-%i-%s')
				WHERE 1 AND organization_id = '{$auth_row['organization_id']}'
				LIMIT 1";
		$result = mysql_query($sql);
		//echo $sql;
		
		output_response('json',create_response(count($json). " Cases Uploaded",'upload',1));
		
		break;
	default:
		header("Content-Type text/php");
		echo NMTRENDS_NAME . ' Version: ' . NMTRENDS_VERSION . "." . NMTRENDS_REVISION;
		break;
}

function output_response($format = 'serialized', $response)
{
	switch ($format)
	{
		case 'json':
			header("Content-Type application/json; charset=UTF-8");
			echo json_encode($response);
			break;
		default:
			header("Content-Type text/php");
			echo serialize($response);
			break;
	}
	exit();
}

function create_response($data = null, $action = null, $response_code = 1, $response_description = "Operation Completed Successfully")
{
	$response = array();
	$response['request_method'] = $_SERVER['REQUEST_METHOD'];
	$response['action'] = $action;
	$response['response_code'] = $response_code;
	$response['response_description'] = $response_description;
	$response['response'] = $data;
	$response['username'] = '';
	if (isset($_SERVER['PHP_AUTH_USER']))
	{
		$response['username'] = $_SERVER['PHP_AUTH_USER'];
	}
	
	return $response;
}



?>
