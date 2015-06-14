<?php

class html_tags {

//////////////////////////////////////////////////////////////////////////
// Form
//////////////////////////////////////////////////////////////////////////

public function form($attr) { 

	// Initialize
	global $template;
	$action = isset($attr['action']) ? $attr['action'] : $template->route;
	$action = preg_replace("/^staff\//", "", $action);
	$method = isset($attr['method']) ? $attr['method'] : 'POST';

	$html = "<form action=\"" . SITE_URI . "/$action\" method=\"$method\"";
	if (isset($attr['enctype'])) { $html .= " enctype=\"$attr[enctype]\""; }
	$html .= ">";

	// Return
	return $html;
}

//////////////////////////////////////////////////////////////////////////
// Bar
//////////////////////////////////////////////////////////////////////////

function bar($attr) { 
	$html = '<div class="box"><div class="title"><h4><span>' . $attr['value'] . '</span></h3></div></div>';
	return $html;
}

//////////////////////////////////////////////////////////////////////////
// Submit
//////////////////////////////////////////////////////////////////////////

public function submit($attr) { 
	$value = isset($attr['value']) ? $attr['value'] : 'Submit Query';
	$class = isset($attr['class']) ? $attr['class'] : 'btn btn-info btn-sm';
	return '<center><button type="submit" name="submit" class="' . $class . '" value="' . $value . '">' . $value . '</button></center>';
}

//////////////////////////////////////////////////////////////////////////
// Boolean
//////////////////////////////////////////////////////////////////////////

public function boolean($attr) { 

	// Set variables
	if (isset($attr['checked']) && $attr['checked'] == 1) { 
		$chk_yes = 'checked="checked"';
		$chk_no = '';
	} else { 
		$chk_yes = '';
		$chk_no = 'checked="checked"';
	}

	// Generate HTML
	$html = "<input type=\"radio\" name=\"$attr[name]\" value=\"1\" $chk_yes> Yes ";
	$html .= "<input type=\"radio\" name=\"$attr[name]\" value=\"0\" $chk_no> No ";
	return $html;

}

//////////////////////////////////////////////////////////////////////////
// Current date
//////////////////////////////////////////////////////////////////////////

public function current_date($attr) { 

	// Get value
	$value = isset($attr['value']) ? $attr['value'] : DB::queryFirstField("SELECT DATE(now())");
	list($year, $month, $day) = explode("-", $value);

	// Add months
	$options = "<span><select name=\"" . $attr['name'] . "_month\" style=\"width: 120px; float: left;\">";
	for ($x = 1; $x <= 12; $x++) { 
		$chk = $x == $month ? 'selected="selected"' : '';
		$options .= "<option value=\"$x\" $chk>" . date('F', mktime(0, 0, 0, ($x + 1), 0, 0));
	}
	$options .= "</select> <select name=\"" . $attr['name'] . "_day\" style=\"width: 60px; float: left;\">";

	// Add days
	for ($x = 1; $x <= 31; $x++) { 
		$chk = $x == $day ? 'selected="selected"' : '';
		$options .= "<option value=\"$x\" $chk>$x";
	}
	$options .= "</select> <select name=\"" . $attr['name'] . "_year\" style=\"width: 80px; float: left;\">";

	// Add years
	$start_year = date('Y');
	for ($x = ($start_year - 2); $x <= ($start_year + 5); $x++) { 
		$chk = $x == $year ? 'selected="selected"' : '';
		$options .= "<option value=\"$x\" $chk>$x";
	}
	$options .= "</select>";
	
	// Return
	return $options;

}

//////////////////////////////////////////////////////////////////////////
// Tab control
//////////////////////////////////////////////////////////////////////////

public function tab_control($attr, $tpl_code) { 

	// Start
	$tab_id = 1;
	global $template;
	$html = '<div class="ctabs"><ul class="menu">' . "\n";

	// Go through tabs
	preg_match_all("/\{tab_page(.*?)\}(.*?)\{\/tab_page\}/si", $tpl_code, $tab_match, PREG_SET_ORDER);
	foreach ($tab_match as $match) { 
		$attr = $template->parse_attr($match[1]);
		$temp_html = "\t<li id=\"tab$tab_id\"><a href=\"#tab$tab_id\">$attr[name]</a><div>\n";
		$temp_html .= $match[2] . "\n\t</div></li>\n\n";
		$tpl_code = str_replace($match[0], $temp_html, $tpl_code);
		$tab_id++;
	}

	// Return
	$html .= $tpl_code . "</ul></div>\n\n";
	return $html;

}

}


?>