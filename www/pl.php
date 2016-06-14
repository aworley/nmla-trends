<?php

/***************************/
/* Pika NM Trends (C) 2013 */
/* Pika Software, LLC.     */
/* http://pikasoftware.com */
/***************************/


// HTML FORM SUBMISSION FUNCTIONS

/**
 * @return unknown
 * @param string $var_name
 * @param string $default_value
 * @param string $filter_mode
 * @desc Returns the value of a GET variable without tripping a PHP warning if the variable isn't set.
*/
function pl_grab_get($var_name, $default_value = null, $filter_mode='nomode')
{
	$value = $default_value;
	
	if (isset($_GET[$var_name]))
	{
		$value = $_GET[$var_name];
		$value = pl_clean_form_input($value, $filter_mode);
	}
	
	return $value;
}


/**
 * @return unknown
 * @param string $var_name
 * @param string $default_value
 * @param string $filter_mode
 * @desc Returns the value of a POST variable without tripping a PHP warning if the variable isn't set.
*/
function pl_grab_post($var_name, $default_value = null, $filter_mode='nomode')
{
	$value = $default_value;
	
	if (isset($_POST[$var_name]))
	{
		$value = $_POST[$var_name];
		$value = pl_clean_form_input($value, $filter_mode);
	}
	
	return $value;
}

/**
* @return date
* @param $date string
* @desc Converts user-submitted dates to the ISO date format.
*/
function pl_date_mogrify($date_str)
{
	$date = $date_str;  // Used to construct the ISO date from the $date_str arg.
	$x = '';  // The final ISO date string, returned at end of function.
	$a = array();  // Stores the month, day and (sometimes) year.
	
	if (strlen($date_str) < 1)
	{
		return false;
	}
	
	// Eliminate commas.
	$date = str_replace(',', '', $date);
	
	// Identify possible date separators.
	if (strpos($date, '/') > 0)
	{
		$a = explode('/', $date);
	}
	
	else if (strpos($date, '-') > 0)
	{
		$a = explode('-', $date);
	}
	
	else if (strpos($date, ' ') > 0)
	{
		$a = explode(' ', $date);
	}
	
	else if (strpos($date, '\\') > 0)
	{
		$a = explode('\\', $date);
	}
	
	if (sizeof($a) > 1 && is_numeric($a[0]) && is_numeric($a[1]))
	{
		/*	A recognized date separator was found, and $a was populated
		with month, day and possibly year.
		
		If the date is not in YYYY?MM?DD format, assume MM?DD?YYYY.
		
		(A mode for DD?MM?YYYY dates here would be handy.)
		*/
		
		if (strlen($a[0]) == 4)
		{
			$x = "{$a[0]}-{$a[1]}-{$a[2]}";
		}
		
		else
		{
			// Determine the year first.
			if (sizeof($a) == 3)
			{
				if (strlen($a[2]) == 4)
				{
					$year = $a[2];
				}
				
				else if (strlen($a[2]) == 2)
				{
					$year = substr(date('Y'), 0, 2) . $a[2];
				}
				
				else
				{
					$year = date('Y');
				}
			}
			
			else
			{
				$year = date('Y');
			}
			
			$month = str_pad($a[0], 2, '0', STR_PAD_LEFT);
			$day = str_pad($a[1], 2, '0', STR_PAD_LEFT);
			
			$x = "$year-$month-$day";
		}
	}
	
	else
	{
		// Fallback mode
		
		/* attempt to determine the year, by grabbing the last 4 (non-white
		space) chars of the $date string
		If the year is earlier than 1902, strtotime will not work, so handle
		the date in a less flexible way
		*/
		//$year = substr(rtrim($date), -4, 4);
		
		/*	set upper and lower bounds for less flexible "old" data handling
		If there isn't a lower bound, text strings won't make it past this
		*/
		/*
		if ($year < 1970 && $year > 1500)
		{
		$a = explode('/', $date);
		
		$x = "{$a[2]}-{$a[0]}-{$a[1]}";
		}
		
		else
		{*/
		$x = date("Y-m-d", strtotime($date));
		//}
	}
	
	return $x;
}


// Converts ISO 2000-12-31 to 12/31/2000
function pl_date_unmogrify($date)
{
	if ($date == '0000-00-00' || !$date)
	{
		return FALSE;
	}
	
	else
	{
		$a = explode('-', $date);
		
		// get rid of leading zeros, if they exist
		$month = (int) $a[1];
		$day = (int) $a[2];
		
		return "{$month}/{$day}/{$a[0]}";
	}
}


/**
* @return string
* @param $str string
* @desc Prevents Javascript insertion attacks in a string.
*/
function pl_clean_html($str)
{
	// AMW - 2012-5-29 - Turned on quote encoding.
	$clean_str = htmlspecialchars($str, ENT_QUOTES);
	return $clean_str;
	//return "hello World";
}


/**
* @return array
* @param $a array
* @desc Prevents Javascript insertion attacks in an array of strings.
*/
function pl_clean_html_array($a)
{
	$b = array();
	
	foreach ($a as $key => $str)
	{
		// AMW - 2012-5-29 - Turned on quote encoding.
		$b[$key] = htmlspecialchars($str, ENT_QUOTES);
	}
	
	return $b;
}


// function pl_template($template_data, $template_file='templates/default.html', $retmode='no')
/**
* @return string
* @param template_file string
* @param template_data array
* @desc A basic templating function, replaces template tags with values from $template_data array
*/
function pl_template($template_file, $template_data = array(), $subtpl_label = null)
{
	$out = '';
	$subtpl_is_found = false;
	$section_is_found = false;
	$str = '';
	$tpl_prefix = '[[';
	$tpl_suffix = ']]';
	$current_subtpl = '';
	
	if (defined('PL_TEMPLATE_PREFIX'))
	{
		$tpl_prefix = PL_TEMPLATE_PREFIX;
	}
	
	if (defined('PL_TEMPLATE_SUFFIX'))
	{
		$tpl_suffix = PL_TEMPLATE_SUFFIX;
	}
	
	// Accept legacy argument order
	if (is_array($template_file))
	{
		$tmp = $template_data;
		$template_data = $template_file;
		$template_file = $tmp;
	}
	
	// Fix legacy use of argument #3
	if (strlen($subtpl_label) < 4)
	{
		$subtpl_label = null;
	}
	
	// Handle custom templates.
	if (file_exists(getcwd(). "-custom/{$template_file}"))
	{
		$template_file = getcwd(). "-custom/{$template_file}";
	}
	
	// Throw an error if the specified file cannot be found.
	if (!file_exists($template_file))
	{
		trigger_error("Invalid template file $template_file");
	}

	$file = fopen($template_file, 'r');

	if (!$file)
	{
		trigger_error('Failed to open template file');
	}

	while (!feof ($file))
	{
		$str = fgets ($file, 1024);
		
		// This will let us accept tags in HTML comment form.
		$str = str_replace("<!--[", $tpl_prefix, $str);
		$str = str_replace("]-->", $tpl_suffix, $str);

		// Not in sub mode
		if (is_null($subtpl_label))
		{
			// Weed out any text belonging to a sub template.
			if ($subtpl_is_found == true)
			{
				$p = strpos($str, "{$tpl_prefix}end:{$current_subtpl}{$tpl_suffix}");
				if (!($p === false))
				{
					$out .= $tpl_prefix . $current_subtpl . $tpl_suffix;
					$out .= substr($str, $p + strlen("{$tpl_prefix}end:{$current_subtpl}{$tpl_suffix}"));

					$subtpl_is_found = false;
				}
			}

			// Now handle sections.
			else if ($section_is_found == true)
			{
				$p = strpos($str, "{$tpl_prefix}end{$tpl_suffix}");
				if (!($p === false))
				{
					$section_text .= substr($str, 0, $p);

					if (!function_exists($section_data_src))
					{
						trigger_error("No function {$section_data_src}.");
					}
					
					$section_data = $section_data_src();

					if (is_array($section_data))
					{
						foreach ($section_data as $val)
						{
							$out .= pl_template_sub($section_text, $val);
						}
					}

					else if ($section_data != false)
					{
						$out .= $section_text;
					}

					// Do not display block if the data is zero or false and not an array.

					$out .= substr($str, $p + strlen("{$tpl_prefix}end{$tpl_suffix}"));
					$section_is_found = false;
				}

				else
				{
					$section_text .= $str;
				}
			}

			else
			{
				$p = strpos($str, "{$tpl_prefix}begin:");
				$p2 = strpos($str, "{$tpl_prefix}begin ");

				if (!($p === false))
				{
					$subtpl_is_found = true;

					$d = substr($str, $p + strlen("{$tpl_prefix}begin:"));
					$e = strpos($d, $tpl_suffix);
					$current_subtpl = substr($d, 0, $e);

					$f = substr($str, 0, $p);

					$out .= $f;
				}

				else if (!($p2 === false))
				{
					$section_is_found = true;

					$d = substr($str, $p2 + strlen("{$tpl_prefix}begin "));
					$e = strpos($d, $tpl_suffix);
					$section_data_src = substr($d, 0, $e);
					$section_text = substr($d, $e + strlen($tpl_suffix));
					$out .= substr($str, 0, $p2);
				}

				else
				{
					$out .= $str;
				}
			}
		}

		// In sub mode
		else
		{
			// Weed out text not related to the selected sub template.
			if ($subtpl_is_found == true)
			{
				$p = strpos($str, "{$tpl_prefix}end:$subtpl_label{$tpl_suffix}");
				if ($p === false)
				{
					$out .= $str;
				}

				else
				{
					$subtpl_is_found = false;
					$out .= substr($str, 0, $p);
					break;
				}
			}

			else
			{
				$p = strpos($str, "{$tpl_prefix}begin:$subtpl_label{$tpl_suffix}");
				if (!($p === false))
				{
					$subtpl_is_found = true;
					$out .= substr($str, $p + strlen("{$tpl_prefix}begin:$subtpl_label{$tpl_suffix}"));
				}
			}
		}
	}

	fclose($file);

	$out = pl_template_sub($out, $template_data);
	
	if (defined('PL_TEMPLATE_HTML_COMMENTS'))
	{
		$out = "\n<!-- Start:  '{$template_file}' -->\n{$out}\n<!-- End:  '{$template_file}' -->\n";
	}
	
	return $out;
}


// Recursively process template string
/*	Process the text in $out.
Find the first tag,
determine it's value,
replace all instances of that tag,
repeat until no more tags are present
*/
function pl_template_sub($str, $template_data)
{
	static $app_settings = null;
	// this value is flipped later if a menu is specified
	$use_auto_lookup = false;
	$tpl_prefix = '[[';
	$tpl_suffix = ']]';
	$prefix_size = 2;
	$suffix_size = 2;
	$tag_tabindex = 1;
	$encoding_mode = 'html';
	$y = array();  // Used to capture the exploded tag attributes.
	
	if (!is_array($app_settings)) 
	{
		$app_settings = pl_settings_get_all();
	}
	
	if (defined('PL_TEMPLATE_PREFIX'))
	{
		$tpl_prefix = PL_TEMPLATE_PREFIX;
		$prefix_size = strlen($tpl_prefix);
	}
	
	if (defined('PL_TEMPLATE_SUFFIX'))
	{
		$tpl_suffix = PL_TEMPLATE_SUFFIX;
		$suffix_size = strlen($tpl_suffix);
	}
	
	$pos = strpos($str, $tpl_prefix);
	
	// this might not work if a template field starts at the very beginning
	// of the template file (ie. $pos == 0)
	//		if ($pos === false)
	if (FALSE === $pos)
	{
		return $str;
	}
	
	// first, get the name of the first template field
	/*	Using $pos as the offset will hopefully prevent stray PHP array code ($a[$b[0]]) 
	from causing problems.
	*/
	$endpos = strpos($str, $tpl_suffix, $pos);
	
	// check $next_name for error conditions
	if ($pos > $endpos)
	{
		trigger_error("templating error #1");
	}
	
	if (($endpos - $pos) > 50)
	{
		trigger_error("template field is missing end tag, or name is too large");
	}
	
	$next_name = substr($str, $pos + $prefix_size, $endpos - $pos - $suffix_size);
	
	/*  Determine whether this tag requires a pl_menu lookup.  There are a couple older
	syntaxes still in use, be sure to handle those as well as the new syntax.
	*/
	// Check for new syntax
	if (strstr($next_name, ' '))
	{
		$use_auto_lookup = true;
		$tag_name = '';
		$tag_lookup = '';
		$tag_mode = 'text';
		$tag_tabindex = 1;
		$encoding_mode = 'html';
		
		/* Determine the tag_mode and tag_name from the first tag element.  The tag_lookup
		should default to the tag_name.  They are often identical, so this shortcut can save
		a few keystrokes.
		*/
		$a = explode(' ', $next_name);
		$tag_name = $a[0];
		// tag_lookup should, if not specified, default to the tag_name value.
		$tag_lookup = $tag_name;
		
		// Process options.  TODO - make this less picky about syntax.
		$options_str = substr($next_name, strlen($a[0]) + 1);
		$options = explode(' ', $options_str);
		foreach ($options as $q)
		{
			$a = null;
			$b = null;
			//list($a, $b)
			$y = explode('=', $q);
			if (isset($y[0])) $a = $y[0];
			if (isset($y[1])) $b = $y[1];
			
			switch ($a)
			{
				case 'tabindex':
				$tag_tabindex = $b;
				break;
				
				case 'lookup':
				case 'source':
				$tag_lookup = $b;
				break;
				
				case 'show_blank':
				if (false === $b || 'no' == $b)
				{
					$tag_mode = 'menu_no_blank';
				}
				break;
				
				case 'menu':
				case 'checkbox':
				case 'radio':
				case 'vradio':
				case 'text':
				case 'option':
				$tag_mode = $a;
				
				if (strlen($b) > 0)
				{
					$tag_lookup = $b;
				}
				
				break;
				
				
				case 'encode':
				
				if (in_array($b, array('html', 'none', 'js'))) 
				{
					$encoding_mode = $b;
				}
				
				else 
				{
					trigger_error("Encoding mode {$b} does not exist.");
				}
				
				break;
				
				
				default:
				break;
			}
		}
	}
	
	// Check for old syntax.
	else if (strstr($next_name, ","))
	{
		$use_auto_lookup = true;
		
		$tag_name = '';
		$tag_lookup = '';
		$tag_mode = 'menu';
		
		$a = explode(",", $next_name);
		
		$tag_name = $a[0];
		
		if (array_key_exists(1, $a))
		{
			$tag_lookup = $a[1];
		}
		
		if (array_key_exists(2, $a))
		{
			$tag_mode = $a[2];
		}
	}
	
	// menus that don't need a blank entry added - for backwards compatibility
	else if (strstr($next_name, ";"))
	{
		$use_auto_lookup = true;
		
		list($tag_name, $tag_lookup) = explode(";", $next_name);
		$tag_mode = 'menu_no_blank';
	}
	
	// If we get this far, it's not a lookup tag.
	else 
	{
		$tag_name = $next_name;
	}
	
	
	// Determine this tag's value, and then encode that value properly.
	if (array_key_exists($tag_name, $template_data))
	{
		$tag_value = $template_data[$tag_name];
		
		switch ($encoding_mode)
		{
			case 'none':
			break;
			
			
			case 'url':
			$tag_value = urlencode($tag_value);
			break;
			
			case 'html':
			case 'js':
			default:
			$tag_value = htmlentities($tag_value);
			break;
		}
	}
	
	else
	{
		$tag_value = null;
	}	
	
	if (true == $use_auto_lookup)
	{
		/*	This is a lookup tag, and so the value in $template_data[$next_name]
		needs to be replaced by the lookup value or by HTML code
		*/
		
		// Get the menu array
		$menu_array = pl_menu_get($tag_lookup);
		
		
		$x = '';
		// Determine what to draw based on $tag_mode
		switch ($tag_mode)
		{
			case 'text':
			
			$x = pl_array_lookup($tag_value, $menu_array);
			
			break;
			
			
			case 'radio':
			
			foreach ($menu_array as $key => $val)
			{
				if ($key == $tag_value)
				{
					$checked = ' checked ';
				}
				
				else
				{
					$checked = '';
				}
				
				$x .= "<input type=\"radio\" name=\"$tag_name\" value=\"$key\" class=\"plradio\" tabindex=\"{$tag_tabindex}\"{$checked}/>{$val} &nbsp; ";
			}
			
			break;
			
			case 'vradio':
			
			foreach ($menu_array as $key => $val)
			{
				if ($key == $tag_value)
				{
					$checked = ' checked ';
				}
				
				else
				{
					$checked = '';
				}
				
				$x .= "<input type=\"radio\" name=\"$tag_name\" value=\"$key\" class=\"plradio\" tabindex=\"{$tag_tabindex}\"{$checked}/>{$val}<br/>\n";
			}
			
			break;
			
			
			case 'checkbox':
			
			if ('yes_no' != $tag_lookup)
			{
				break;
			}
			
			$x .= pl_html_checkbox($tag_name, $tag_value);
			/*
			if (1 == $tag_value)
			{
				$checked = ' checked';
			}
			
			else
			{
				$checked = '';
			}
			
			// Assigning two fields the same name may not be compatible with all browsers.  May not be compliant with W3C standards, either.
			$x .= "<input type=\"hidden\" name=\"$tag_name\" value=\"0\">";
			$x .= "<input type=\"checkbox\" name=\"$tag_name\" value=\"1\" tabindex=\"1\"{$checked}>\n";
			*/
			
			break;
			
			
			case 'menu_no_blank':
			
			$x = pl_html_menu($menu_array, $tag_name, $tag_value, 0);
			
			break;
			
			
			case 'menu':
			
			$x = pl_html_menu($menu_array, $tag_name, $tag_value, 1);
			
			break;
			
			case 'option':
			$x = "";
			
			foreach ($menu_array as $key => $val)
			{
				$clean_key = $key;
				$clean_val = $val;
				$x .- "<option value=\"{$clean_key}\">{$clean_val}</option>\n";
			}
			
			break;
			
			
			default:
			
			$x = 'INVALID MENU MODE';
			
			break;
		}
		
		$template_data[$next_name] = $x;
	}
	
	// Check that the key exists in the template data, to avoid triggering a PHP warning
	if (array_key_exists($next_name, $template_data))
	{
		// we have the name, now replace the first and any additional fields
		$newstr = str_replace($tpl_prefix . $next_name . $tpl_suffix, $template_data[$next_name], substr($str, $pos));
	}
	
	// Next, check the application settings.
	else if (array_key_exists($next_name, $app_settings))
	{
		// we have the name, now replace the first and any additional fields
		$newstr = str_replace($tpl_prefix . $next_name . $tpl_suffix, $app_settings[$next_name], substr($str, $pos));
	}

	// Leave blank if no match is found in either the template data or the app. settings.
	else
	{
		$newstr = str_replace($tpl_prefix . $next_name . $tpl_suffix, '', substr($str, $pos));
	}
	
	// now proceed to next template field in $str
	return substr($str, 0, $pos) . pl_template_sub($newstr, $template_data);
}

function pl_settings_get($x)
{
	return "/aaron/trends";
}

function pl_settings_get_all()
{
	return array();
}

/**
* @return string
* @param $str string
* @param $mode string
* @desc Used by pl_grab_[post/get]() functions to cleanse incoming user-submitted form data.
*/
function pl_clean_form_input($form_str, $mode = 'nomode')
{
	static $magic_quotes_on = null;
	/*	This is split off into a standalone function since it's used in both
		pl_grab_var() and pl_grab_vars().  Probably shouldn't need to be invoked
		in any other cases.
		
		TODO - revamp to work better with new, simpler pl_table arrays,
		recursive for arrays
		TODO - return an error code.
	*/
	
	if (is_null($magic_quotes_on))
	{
		$magic_quotes_on = get_magic_quotes_gpc();
	}
	
	if (is_array($form_str))
	{
		$str = array();
		
		foreach ($form_str as $key => $val)
		{
			$str[$key] = pl_clean_form_input($val);
		}
		
		return $str;
	}

	else if (strlen($form_str) < 1)
	{
		return $form_str;
	}

	
	$str = $form_str;  // The "edited" version of the user-submitted string.

	// Get rid of any whitespace at beginning or end.
	$str = trim($str);
	
	if ($magic_quotes_on)
	{
		/*
		Examples:
		O\'Brian becomes O'Brian
		C:\\autoexec.bat is unaltered
		*/
		/* 	PHP tends to add slashes to strings.  Get rid of them,
		quote chars and such will be handled in pl_build_sql
		*/
		$str = stripslashes($str);
		/*	if there were slashes in the original string, before PHP escaped
		all slashes with a second slash, change these back to double
		slashes, since single slashes can cause problems in queries
		*/
		$str = str_replace('\\', '\\\\', $str);
	}
	
	// Perform any mode-specific transformations.
	switch ($mode)
	{
		case 'number':
		/*	This can be used to prevent non-numeric values from being assigned to
			numeric columns in MySQL.  They would be saved as '0', which makes it 
			impossible to discern actual zero values from improperly inputted values.  
			Save non-numerics as NULL instead.
		*/
		if (!is_null($str) && !is_numeric($str))
		{
			$str = null;
		}
		
		else if (is_null($str))
		{
			$str = null;
		}
		
		break;
		
				
		case 'date':
		$str = pl_date_mogrify($str);
		
		// Check for invalid dates.
		$a = explode('-', $str);
		if (sizeof($a) != 3)
		{
			$str = '';
		}
		
		else if (!checkdate($a[1], $a[2], $a[0]))
		{
			$str = '';
		}
		
		// Don't allow dates too far back or into the future.
		else if ($a[0] < 1800 || $a[0] > 2099)
		{
			$str = '';
		}
		
		break;
		
		
		case 'time':	
		$str = pl_time_mogrify($str);
		
		break;
		
		
		// Legacy.
		case 'boolean':
		// TRUE or FALSE, no NULLs allowed
		if (!(0 == $str || 1 == $str))
		{
			$str = 0;
		}
		
		break;
		
		
		// Legacy.
		case 'array':
		if (!is_array($str))
		{
			$str = array();
		}

		break;
		
		
		case 'text':
		case 'unformatted':
		case 'primary_key':
		case 'nomode':
		default:
		
		break;
	}
	
	return $str;
}

?>
