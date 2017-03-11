<?php 

/**********************************/
/* New Mexico Trends Database     */
/* Pika Software, LLC. (C) 2013   */
/* http://pikasoftware.com        */
/**********************************/

define('NMTRENDS_REST_URI','https://server-name/nmtrends/services/trends/v1.1/nm/');

// Start USER SETTINGS section 
/*
define('NMTRENDS_USERNAME','xxxx');
define('NMTRENDS_PASSWORD','xxxx');

define('DB_HOST','xxxx');
define('DB_NAME','xxxx');
define('DB_USER','xxxx');
define('DB_PASS','xxxx');

$skip_opposing = true;
$skip_race = true;
*/
// End USER SETTINGS section
// include('nmtrends_upload_v1_1.php');


// ### Application code starts here. ###

set_time_limit(1800);
ini_set('display_errors','On');
ini_set('memory_limit','1024M');

mysql_connect(DB_HOST,DB_USER,DB_PASS);
mysql_select_db(DB_NAME);


if (PHP_SAPI != "cli") {
	echo "Program must be run from CLI";
	exit(1);
}




// Run a query to collect the information we need.
// 20130712 - MDF - custom code for NMBAR - they don't track race
// 20130712 - MDF - custom code for NMBAR - they think opposing 
// name is breach of confidentiality

if ($skip_race)
{
	$race_column_name = 'NULL';
}

else
{
	$race_column_name = 'pri_client.ethnicity';
}

$sql = "SELECT COUNT(*) AS hispanic_is_present FROM information_schema.COLUMNS 
				WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'contacts' 
				AND COLUMN_NAME = 'hispanic'";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);

if (1 == $row['hispanic_is_present'])
{
	$hispanic_column_name = 'pri_client.hispanic';
}

else 
{
	$hispanic_column_name = "NULL";
}

if ($skip_sclo == true)
{
	$sclo0_column_name = 'NULL';
	$sclo1_column_name = 'NULL';
	$sclo2_column_name = 'NULL';
}

else
{
	$sclo0_column_name = 'city_ethnicity';
	$sclo1_column_name = 'city_problem';
	$sclo2_column_name = 'city_poverty';
}

$sql = "SELECT
			cases.case_id AS cms_case_id,
			cases.created AS cms_case_created,
			cases.open_date,
			cases.close_date,
			pri_client.gender,
			{$race_column_name} AS race,
			{$hispanic_column_name} AS hispanic,
			pri_client.disabled,
			IF(cases.client_age > 59,1,0) AS age_over_60,
			pri_client.zip,
			pri_client.county,
			cases.court_name,
			cases.judge_name,
			LPAD(cases.problem, 2, '0') AS problem,
			cases.outcome,
			cases.opp_info_opt_in,
			veteran_household,
			label AS language,
			children,
			persons_helped,
			poverty,
			client_age,
			client_id,
			close_code,
			{$sclo0_column_name} AS city_ethnicity,
			{$sclo1_column_name} AS city_problem,
			{$sclo2_column_name} AS city_poverty
		FROM cases
		LEFT JOIN contacts AS pri_client ON cases.client_id = pri_client.contact_id
		LEFT JOIN menu_language ON pri_client.language = menu_language.value
		WHERE cases.open_date > DATE_SUB(CURDATE(), INTERVAL 3 YEAR)
		AND cases.status != 4
		AND cases.open_date <= CURDATE()
		ORDER BY cases.open_date ASC";
/* AMW 2013-11-23 - Some programs have cases that have future open dates.  These
were causing the graphs to look weird, so let's omit them from the trends data.

Transfers (case_status == 4) are also to be omitted.

Some programs have single-digit problem codes; this causes problems.  Get rid of
them here before the data is sent to the server.
*/
//echo $sql; exit;
$result = mysql_query($sql) or trigger_error('SQL: ' . $sql . ' Error: ' .  mysql_error());
$trends_array = array();

while($row = mysql_fetch_assoc($result))
{
	$p = substr($row['problem'], 0, 2); 
	$opt_in = $row['opp_info_opt_in'];
	unset($row['opp_info_opt_in']);
	
	if ($skip_opposing)
	{
		$row['opposing_party'] = '[Redacted due to program policy]';
	}
	
	else if ($p >= 30 && $p <= 49)
	{
		$row['opposing_party'] = '[Redacted due to problem code]';
	}
	
	else if (true != $opt_in)
	{
		$row['opposing_party'] = '[Redacted, client did not release]';
	}
	
	else
	{
		$row['opposing_party'] = '';
		
		// Now add the first opposing party to the trend data.
		$opp_sql = "SELECT 
				opp.first_name AS opp_first_name,
				opp.last_name AS opp_last_name
				FROM conflict 
				LEFT JOIN contacts AS opp ON opp.contact_id = conflict.contact_id
				WHERE conflict.case_id = {$row['cms_case_id']} 
				AND conflict.relation_code = 2";
		$opp_result = mysql_query($opp_sql);

		while ($opp_row = mysql_fetch_assoc($opp_result))
		{
			if (strlen($opp_row['opp_first_name']) < 1)
			{
				$row['opposing_party'] .= $opp_row['opp_last_name'] . "; ";
			}
			
			else
			{
				$row['opposing_party'] .= $opp_row['opp_last_name'] . ", "
				. $opp_row['opp_first_name'] . "; ";
			}		
		}
	}
	
	//echo $row['opposing_party'] . "\n";

	if (!isset($skip_extras) || $skip_extras == true)
	{
		$row['veteran_household'] = null;
		$row['language'] = null;
		$row['children'] = null;
		$row['persons_helped'] = null;
		$row['poverty'] = null;
		$row['client_age'] = null;
	}
	
	// This foreach loop will fix any malformed UTF chars, so json_encode doesn't fail.
 	foreach ($row as $abc => $xyz)
 	{
 		if (!is_null($xyz))
		{
 			$row[$abc] = utf8_encode($xyz);
		}
 	}
	
	$trends_array[] = $row;
}

$json = json_encode($trends_array);
$content = http_build_query(array('action' => 'upload', 'data' => gzcompress($json)));

$process = curl_init();
curl_setopt($process, CURLOPT_URL,NMTRENDS_REST_URI . 'upload');
curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded', "Content-Length: " . strlen($content)));
curl_setopt($process, CURLOPT_HEADER, 1);
curl_setopt($process, CURLOPT_USERPWD, NMTRENDS_USERNAME . ":" . NMTRENDS_PASSWORD);
curl_setopt($process, CURLOPT_TIMEOUT, 300);
curl_setopt($process, CURLOPT_POST, 1);
curl_setopt($process, CURLOPT_POSTFIELDS, $content);
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
$return = curl_exec($process);
curl_close($process);

exit();
	
/*
MDF - some code I wrote to generate random data to test the service - might save some time later

function array_rand_flip($a)
{
	return array_rand(array_flip($a));
}
	
	// Test scenario - generate 1-100 random cases and send them
	$num_cases = rand(1,100);
	$trends_array = array();
	for($i = 0;$i < $num_cases;$i++)
	{
		$test_case = array(
			'cms_case_id' => rand(1,999999),
			'gender' => array_rand_flip(array('A','F','G','M','T','X')),
			'race' => array_rand_flip(array('A','B','H','N','O','W','X')),
			'hispanic' => array_rand_flip(array('0','1')),
			'disabled' => array_rand_flip(array('0','1')),
			'age_over_60' => array_rand_flip(array('0','1')),
			'zip' => str_pad(rand(1,99999),5,'0'),
			'county' => array_rand_flip(array('Bernadillo','Catron','Chaves','Cibola')),
			'opp_first_name' => array_rand_flip(array('Mike','Mary','David','Danielle','Jeff','Joan')),
			'opp_last_name' => array_rand_flip(array('Smith','Davis','Gordon','Lewis','Hollerand','Humphrey')),
			'court_name' => array_rand_flip(array('Bernadillo Municipal','3rd District','6th District','Municipal Court')),
			'judge_name' => array_rand_flip(array('Judge Baker','Judge O\'Rielly','Judge Smith','Judge Gonzales')),
			'problem' => str_pad(rand(1,99),2,"0"),
			'outcome' => str_pad(rand(1,9999),4,"0")
		);
		$trends_array[] = $test_case;
	}*/
	

?>
