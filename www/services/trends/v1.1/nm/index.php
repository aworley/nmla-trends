<?php 

/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/

define('NMTRENDS_NAME','New Mexico Trends System');
define('NMTRENDS_VERSION','1');
define('NMTRENDS_REVISION','0');

if (file_exists('../../../../config.php'))
{
	include_once('../../../../config.php');
}

else 
{
	define('DB_HOST','localhost');
	define('DB_NAME','trends_database');
	define('DB_USER','mysql_username');
	define('DB_PASS','mysql_password');
}



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


set_time_limit(1800);
ini_set('display_errors','Off');
ini_set('memory_limit','1024M');

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
	case 'upload':
	
		// AMW - Start this job's log entry.
		$sql = "INSERT INTO logs 
				SET organization_id = '{$auth_row['organization_id']}',
				start_datetime=NOW()";
		$result = mysql_query($sql);
		$log_id = mysql_insert_id();
		
		// AMW - Delete previously uploaded data.
		$sql = "DELETE FROM cases 
				WHERE organization_id = '{$auth_row['organization_id']}'";
		$result = mysql_query($sql);

		// AMW - Save newly uploaded data into the database.
		$data = pl_grab_req('data');
		$data = gzuncompress($data);
		$json = json_decode($data,true);
		
		$col_list_array = array(
							'organization_id',
							'cms_case_id',
							'cms_case_created', 
							'open_date',
							'close_date',
							'gender', 
							'race',
							'hispanic',
							'disabled',
							'age_over_60',
							'zip',
							'county',
							'court_name',
							'judge_name',
							'problem',
							'outcome',
							'opposing_party',
							'veteran_household',
							'language',
							'children',
							'persons_helped',
							'poverty',
							'client_age',
							'client_id',
							'close_code',
							'city_ethnicity',
							'city_problem',
							'city_poverty');
		$col_list = "`" . implode('`, `',$col_list_array) . "`";
		$case_count = 0;

		foreach ($json as $case_row)
		{
			// AMW 2013-11-23 - All redaction is now done on client-side as of v1.1.
			// AMW 2013-09-06
			/*
			$p = substr($case_row['problem'], 0, 2); 
			if ($p >= 30 && $p <= 49)
			{
				$case_row['opp_first_name'] = '';
				$case_row['opp_last_name'] = '[Redacted]';
			}
			*/

			// AMW 2013-09-10
			/*
			if ($case_row['organization_id'] == '4')
			{
				$case_row['opp_first_name'] = '';
				$case_row['opp_last_name'] = '[Redacted]';
			}
			*/
			
			$case_row['organization_id'] = $auth_row['organization_id'];

			// AMW 2014-05-18 - Temp fix.  Someone jumbled the ethnicity/race/hispanic values.
			// AMW 2016-06-27 - Re-enabling this just for one program until they
			// upgrade their upload script.
			if (2 == $auth_row['organization_id'])
			{
				$case_row['temp123456'] = $row['race'];
				$case_row['race'] = $row['hispanic'];
				$case_row['hispanic'] = $case_row['temp123456'];
				unset($case_row['temp123456']);
			}
			
			// Hispanic code merging needs to be done before Race merging because
			// some of the logic relies on the original race code.
			if ($case_row['hispanic'] != 1 && $case_row['race'] == '30')
			{
				switch ($auth_row['organization_id'])
					{
						case '1':
						case '2':
						case '4':
						case '6':
							$case_row['hispanic'] = 1;
							break;
					}
			}
			
			if ($case_row['hispanic'] != 1 
				&& ($case_row['race'] == 'H' || $case_row['race'] == '30')
				&& $auth_row['organization_id'] == '3')
			{
						$case_row['hispanic'] = 1;
			}
			
			// White
			if ($case_row['race'] == '10' && 
					($auth_row['organization_id'] == '1' || 
					 $auth_row['organization_id'] == '2' ||
					 $auth_row['organization_id'] == '6'))
			{
				$case_row['race'] = 'White';
			}
			
			else if ($case_row['race'] == 'W' && $auth_row['organization_id'] == '3')
			{
				$case_row['race'] = 'White';
			}
			
			else if ($case_row['race'] == '20' && $auth_row['organization_id'] == '4')
			{
				$case_row['race'] = 'White';
			}

			// Black
			else if ($case_row['race'] == '20' && 
					($auth_row['organization_id'] == '1' || 
					 $auth_row['organization_id'] == '2' ||
					 $auth_row['organization_id'] == '6'))
			{
				$case_row['race'] = 'Black';
			}
			
			else if ($case_row['race'] == 'B' && $auth_row['organization_id'] == '3')
			{
				$case_row['race'] = 'Black';
			}
			
			else if ($case_row['race'] == '40' && $auth_row['organization_id'] == '4')
			{
				$case_row['race'] = 'Black';
			}
			
			// Native American
			else if ($case_row['race'] == '40' && 
					($auth_row['organization_id'] == '1' || 
					 $auth_row['organization_id'] == '2' ||
					 $auth_row['organization_id'] == '6'))
			{
				$case_row['race'] = 'Native American';
			}
			
			else if ($case_row['race'] == 'N' && $auth_row['organization_id'] == '3')
			{
				$case_row['race'] = 'Native American';
			}
			
			else if ($case_row['race'] == '60' && $auth_row['organization_id'] == '4')
			{
				$case_row['race'] = 'Native American';
			}

			// Asian/Pacific Islander
			else if ($case_row['race'] == '50' && 
					($auth_row['organization_id'] == '1' || 
					 $auth_row['organization_id'] == '2' ||
					 $auth_row['organization_id'] == '4'))
			{
				$case_row['race'] = 'Asian/Pacific Islander';
			}
			
			else if (($case_row['race'] == '50' ||
								$case_row['race'] == '60' ||
								$case_row['race'] == '70') && $auth_row['organization_id'] == '6')
			{
				$case_row['race'] = 'Asian/Pacific Islander';
			}
			
			else if ($case_row['race'] == 'A' && $auth_row['organization_id'] == '3')
			{
				$case_row['race'] = 'Asian/Pacific Islander';
			}

			// Multiracial
			else if ($case_row['race'] == 'MR' && $auth_row['organization_id'] == '1')
			{
				$case_row['race'] = 'Multiracial';
			}
			
			else if ($case_row['race'] == '60' && $auth_row['organization_id'] == '2')
			{
				$case_row['race'] = 'Multiracial';
			}
			
			else if ($case_row['race'] == '80' && $auth_row['organization_id'] == '6')
			{
				$case_row['race'] = 'Multiracial';
			}
			
			else if ($case_row['race'] == 'M' && $auth_row['organization_id'] == '1')
			{
				$case_row['race'] = 'Multiracial';
			}
			
			// Other
			else if ($case_row['race'] == '99' && 
					($auth_row['organization_id'] == '1' || 
					 $auth_row['organization_id'] == '2' ||
					 $auth_row['organization_id'] == '6'))
			{
				$case_row['race'] = 'Other';
			}
			
			else if ($case_row['race'] == 'O' && $auth_row['organization_id'] == '3')
			{
				$case_row['race'] = 'Other';
			}
			
			else if (($case_row['race'] == '70' || $case_row['race'] == '99') && 
							 $auth_row['organization_id'] == '4')
			{
				$case_row['race'] = 'Other';
			}			
			
			// Not Entered
			else if ($case_row['race'] == 'X' && 
					($auth_row['organization_id'] == '1' || 
					 $auth_row['organization_id'] == '2' ||
					 $auth_row['organization_id'] == '3'))
			{
				$case_row['race'] = 'Not Entered';
			}
			
			else if ($case_row['race'] == '10' && $auth_row['organization_id'] == '4')
			{
				$case_row['race'] = 'Not Entered';
			}

			else if (strlen($case_row['race']) == 0 && 
					($auth_row['organization_id'] == '1' || 
					 $auth_row['organization_id'] == '2' ||
					 $auth_row['organization_id'] == '3' ||
					 $auth_row['organization_id'] == '4' ||
					 $auth_row['organization_id'] == '6'))
			{
				$case_row['race'] = 'Not Entered';
			}
			
			else if ($case_row['race'] == '30' && 
					($auth_row['organization_id'] == '1' || 
					 $auth_row['organization_id'] == '2' ||
					 $auth_row['organization_id'] == '4' ||
					 $auth_row['organization_id'] == '6'))
			{
				$case_row['race'] = 'Not Entered';
			}
			
			else if ($case_row['race'] == 'H' && 
					$auth_row['organization_id'] == '3')
			{
				$case_row['race'] = 'Not Entered';
			}

			else if ($case_row['race'] == 'H' && $auth_row['organization_id'] == '3')
			{
				$case_row['race'] = 'Not Entered';
			}
			
			// The 99's mean the program is omitting that data.
			// The others are used to clean dirty data, and hopefully can be removed
			// at a future date.
			if ($case_row['hispanic'] == '10'
					|| $case_row['hispanic'] == '20'
					|| $case_row['hispanic'] == '40'
					|| $case_row['hispanic'] == '50'
					|| $case_row['hispanic'] == '99')
			{
				$case_row['hispanic'] = null;
			}

			// Outcomes
			$outcome_lookup['1'] = array();
			$outcome_lookup['1']['A'] = 'Advice';
			$outcome_lookup['1']['L'] = 'Lost/Hearing Lost/Settled Unfavorably';
			$outcome_lookup['1']['W'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['1']['M'] = 'Mixed Result';
			$outcome_lookup['1']['N'] = 'NA';

			$outcome_lookup['3'] = array();
			$outcome_lookup['3']['A'] = 'Advice';
			$outcome_lookup['3']['14'] = 'Advice';
			$outcome_lookup['3']['02'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['3']['03'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['3']['04'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['3']['05'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['3']['06'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['3']['07'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['3']['08'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['3']['09'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['3']['16'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['3']['17'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['3']['01'] = 'NA';
			$outcome_lookup['3']['10'] = 'NA';
			$outcome_lookup['3']['11'] = 'NA';
			$outcome_lookup['3']['12'] = 'NA';
			$outcome_lookup['3']['13'] = 'NA';
			$outcome_lookup['3']['15'] = 'NA';

			$outcome_lookup['4'] = array();
			$outcome_lookup['4']['2'] = 'Lost/Hearing Lost/Settled Unfavorably';
			$outcome_lookup['4']['4'] = 'Lost/Hearing Lost/Settled Unfavorably';
			$outcome_lookup['4']['6'] = 'Lost/Hearing Lost/Settled Unfavorably';
			$outcome_lookup['4']['1'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['4']['3'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['4']['5'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['4']['7'] = 'No Effect';
			$outcome_lookup['4']['8'] = 'NA';
			$outcome_lookup['4']['1'] = 'NA';
			$outcome_lookup['4']['9'] = 'NA';
			$outcome_lookup['4']['10'] = 'NA';

			$outcome_lookup['6'] = array();
			$outcome_lookup['6']['2'] = 'Lost/Hearing Lost/Settled Unfavorably';
			$outcome_lookup['6']['4'] = 'Lost/Hearing Lost/Settled Unfavorably';
			$outcome_lookup['6']['6'] = 'Lost/Hearing Lost/Settled Unfavorably';
			$outcome_lookup['6']['1'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['6']['3'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['6']['5'] = 'Won/Hearing Won/Settled Favorably';
			$outcome_lookup['6']['7'] = 'No Effect';
			$outcome_lookup['6']['8'] = 'NA';

			if (array_key_exists($case_row['outcome'], $outcome_lookup[$auth_row['organization_id']]))
			{
				$case_row['outcome'] = $outcome_lookup[$auth_row['organization_id']][$case_row['outcome']];
			}
			// End outcome data mapping.

			// Veteran in Household.
			switch ($case_row['veteran_household'])
			{
				case '1':
				$case_row['veteran_household'] = 'Yes';
				break;

				case '0':
				$case_row['veteran_household'] = 'No';
				break;

				case '2':
				$case_row['veteran_household'] = 'NA';
				break;
			}
			// End Veteran in Household data mapping.

			// Merge duplicate Problem Codes			
			if ($case_row['problem'] == '11' && $auth_row['organization_id'] == 2)
			{
				$case_row['problem'] = '16';
			}

			else if ($case_row['problem'] == '78a' && $auth_row['organization_id'] == 2)
			{
				$case_row['problem'] = '27';
			}

			// Fix overlapping Problem Codes
			else if ($case_row['problem'] == '12' && $auth_row['organization_id'] == 3)
			{
				$case_row['problem'] = '12b';
			}
			// End Problem Codes

			$val_list = '';

			foreach ($col_list_array as $field_name)
			{
				// AMW 2013-12-03 - problem code cleanup
				$z = mysql_real_escape_string($case_row[$field_name]);
				
				if('problem' == $field_name && strlen($z) != 0)
				{
					$z = substr($z, 0, 3);
					$z = str_pad($z, 2, "0", STR_PAD_LEFT);
				}

				/*
				if('disabled' == $field_name && $auth_row['organization_id'] == 'lawaccess')
				{
				}
				*/
			
                                if ($field_name == 'organization_id')
                                {
	                                $val_list .= "'{$z}'";
                                }

                                else
                                {
                                        if (strlen($z) == 0)
                                        {
                                                $val_list .= ", null";
                                        }

                                        else
                                        {
                                                $val_list .= ", '{$z}'";
                                        }
                                }
                        }
                        
			$sql = "INSERT INTO cases
					({$col_list})			
					VALUES ({$val_list})";
			 
			mysql_query($sql);
			$case_count++;
		}
		
		$sql = "UPDATE logs SET end_datetime=NOW(), success_code='Yes',
				case_record_count='{$case_count}'
				WHERE log_id='{$log_id}'";
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
