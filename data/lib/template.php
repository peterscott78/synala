<?php

class template {

//////////////////////////////////////////////////////////////////////////
// Construct
//     $theme -- Theme to use from /themes/ directory
//////////////////////////////////////////////////////////////////////////

public function __construct($route = '') { 

	// Initialize
	global $config;

	// Get route
	$this->route = $route == '' ? trim($_GET['route'], '/') : trim($route, '/');
	if (rtrim($this->route, '/') == 'admin') { $this->route = 'admin/index'; }
	if (!preg_match("/^admin\//", $this->route)) { $this->route = 'public/' . $this->route; }

	// Set variables
	$this->theme = preg_match("/^admin\//", $this->route) ? 'admin' : 'public';
	$this->has_errors = 0;
	$this->page_title = '';
	$this->user_messages = array();

	// Load HTML tags
	$this->tags = new html_tags();

	// Initiate Smarty instance
	$this->smarty = new Smarty();
	$this->smarty->setTemplateDir(SITE_PATH . '/data/tpl/');
	$this->smarty->setCompileDir(SITE_PATH . '/data/tpl_c/');

}

//////////////////////////////////////////////////////////////////////////
// Parse the template
//////////////////////////////////////////////////////////////////////////

public function parse() { 

	// Get template contents
	$tpl_code = $this->get_template_contents();

	// Parse PHP code, if needed
	if (file_exists(SITE_PATH . '/data/php/' . $this->route . '.php')) { 
		require_once(SITE_PATH . '/data/php/' . $this->route . '.php');
	}

	// Merge fields
	foreach ($_POST as $key => $value) {
		if (is_array($value)) { continue; }
		$tpl_code = str_replace("~$key~", $value, $tpl_code);
	}

	// Set base variables
	$this->set_base_variables();

	// Parse data tables
	preg_match_all("/\{table(.*?)\}/si", $tpl_code, $table_match, PREG_SET_ORDER);
	foreach ($table_match as $match) { 
		$attr = $this->parse_attr($match[1]);
		if (!isset($attr['alias'])) { trigger_error(E_USER_ERROR, "Table tag found with no alias attribute"); }
		$tpl_code = str_replace($match[0], $this->generate_table($attr['alias'], $attr), $tpl_code);
	}

	// Go through HTML tags
	$methods = get_class_methods($this->tags);
	foreach ($methods as $tag) { 
		preg_match_all("/\{" . $tag . "(.*?)\}/si", $tpl_code, $tag_match, PREG_SET_ORDER);
		foreach ($tag_match as $match) { 
			$attr = $this->parse_attr($match[1]);
			$tpl_code = str_replace($match[0], $this->tags->$tag($attr), $tpl_code);
		}
	}

	// Parse template
	foreach ($_POST as $key => $value) {
		if (is_array($value)) { continue; }
		$tpl_code = str_replace("~$key~", $value, $tpl_code);
	}
	$this->smarty->display('eval:' . $tpl_code);
	exit(0);

}

//////////////////////////////////////////////////////////////////////////
// Set base variables
//////////////////////////////////////////////////////////////////////////

private function set_base_variables() { 

	// Initialize
	global $config;

	// Define base template variables
	$this->assign('site_uri', SITE_URI);
	$this->assign('site_path', SITE_PATH);
	$this->assign('theme_uri', SITE_URI . '/themes/' . $this->theme);
	$this->assign('theme_dir', SITE_PATH . '/themes/' . $this->theme);
	$this->assign('route', $this->route);
	$this->assign('page_title', $this->page_title);
	$this->assign('current_year', date('Y'));
	$this->assign('exchange_rate', fmoney($config['exchange_rate']));
	$this->assign('config', $config);

	// User message
	$user_message = '';
	$msg_types = array('success','info','error');
	foreach ($msg_types as $type) { 
		if (!isset($this->user_messages[$type])) { continue; }
		$css_type = $type == 'error' ? 'danger' : $type;

		// Get icon
		if ($type == 'info') { $icon = 'info'; }
		elseif ($type == 'error') { $icon = 'ban'; }
		else { $icon = 'check'; }

		// Create HTML
		$user_message .= '<div class="callout callout-' . $css_type . ' text-center"><p><i class="icon fa fa-' . $icon . '"></i> ';
		foreach ($this->user_messages[$type] as $msg) { 
			if ($msg == '') { continue; }
			$user_message .= "$msg<br />";
		}
		$user_message .= "</p></div>";
	}
	$this->assign('user_message', $user_message);
	
	// Check login
	//if (!defined('LOGIN')) {
	//	define('LOGIN', false);
	//	$GLOBALS['userid'] = 0;
	//}

	// Alerts, if admin panel
	if ($this->theme == 'admin' && $GLOBALS['userid'] > 0) { 

		// Update alerts
		DB::query("UPDATE alerts SET is_new = 2 WHERE is_new = 1 AND userid = %d", $GLOBALS['userid']);

		// Get total alerts
		$total_alerts = DB::queryFirstField("SELECT count(*) FROM alerts WHERE is_new = 2 AND userid = %d", $GLOBALS['userid']);
		if ($total_alerts == '') { $total_alerts = 0; }

		// Get alerts
		$alerts = array();
		$rows = DB::query("SELECT count(*) AS total, sum(amount) AS amount, type FROM alerts WHERE is_new = 2 AND userid = %d GROUP BY type ORDER BY type", $GLOBALS['userid']);
		foreach ($rows as $row) { 

			// Get icon
			if ($row['type'] == 'new_user') {
				$icon = 'fa-users text-light-blue';
				$name = '<b>' . $row['total'] . '</b> new users registered';
			} elseif ($row['type'] == 'new_deposit') { 
				$icon = 'fa-btc text-green';
				$name = '<b>' . $row['total'] . '</b> new deposits, total <b>' . fmoney_coin($row['amount']) . ' BTC</b>';
			} elseif ($row['type'] == 'product_purchase') { 
				$icon = 'fa-shield text-red';
				$name = '<b>' . $tow['total'] . '<b> product orders, total <b>' . fmoney_coin($row['amount']) . ' BTC</b>';
			} elseif ($row['type'] == 'invoice_paid') { 
				$icon = 'fa-file-pdf-o text-orange';
				$name = '<b>' . $tow['total'] . '<b> invoices paid, total <b>' . fmoney_coin($row['amount']) . ' BTC</b>';
			} else { continue; }

			// Add to alerts
			$vars = array(
				'icon' => $icon, 
				'name' => $name
			);
			array_push($alerts, $vars);
		}

		// Template variables
		$this->assign('total_alerts', $total_alerts);
		$this->assign('alerts', $alerts);
	}

	// Set variables
	$this->assign('is_login', ($GLOBALS['userid'] > 0 ? true : false));
	$this->assign('userid', $GLOBALS['userid']);

	// User variables, if needed
	if ($GLOBALS['userid'] > 0) { 
		$user = new user($GLOBALS['userid']);
		$profile = $user->load();

		$this->assign('user', $profile);
		$this->assign('username', $profile['username']);
		$this->assign('full_name', $profile['full_name']);
		$this->assign('email', $profile['email']);
	}



}

//////////////////////////////////////////////////////////////////////////
// Get template contents
//////////////////////////////////////////////////////////////////////////

private function get_template_contents() { 

	// Initialize
	$tpl_code = '';
	$theme_dir = SITE_PATH . '/themes/' . $this->theme;

	// Get header
	if (file_exists("$theme_dir/header.tpl")) { $tpl_code .= file_get_contents("$theme_dir/header.tpl"); }

	// Get body contents
	if (file_exists(SITE_PATH . '/data/tpl/' . $this->route . '.tpl')) { 
		$tpl_body = file_get_contents(SITE_PATH . '/data/tpl/' . $this->route . '.tpl');

		if (preg_match("/<h1>(.+?)<\/h1>/si", $tpl_body, $match)) { 
			$this->page_title = $match[1];
			$tpl_body = str_replace($match[0], "", $tpl_body);
		}
		$tpl_code .= $tpl_body;

	} elseif (file_exists(SITE_PATH . '/data/tpl/' . $this->theme . '/404.tpl')) { 
		$tpl_body = file_get_contents(SITE_PATH . '/data/tpl/' . $this->theme . '/404.tpl');

		if (preg_match("/<h1>(.+?)<\/h1>/si", $tpl_body, $match)) { 
			$this->page_title = $match[1];
			$tpl_body = str_replace($match[0], "", $tpl_body);
		}
		$tpl_code .= $tpl_body;

	} else { 
		echo "Template does not exist: $this->route";
		exit(0);
	}

	// Get footer
	if (file_exists("$theme_dir/footer.tpl")) { $tpl_code .= file_get_contents("$theme_dir/footer.tpl"); }

	// Return
	return $tpl_code;

}

//////////////////////////////////////////////////////////////////////////
// Assign variable
//////////////////////////////////////////////////////////////////////////

public function assign($key, $value) { 
	$this->smarty->assign($key, $value);
}

//////////////////////////////////////////////////////////////////////////
// Parse attributes
//     $string -- String from within HTML tag
//////////////////////////////////////////////////////////////////////////

public function parse_attr($string) { 
	
	// Parse string
	$attributes = array();
	preg_match_all("/(\w+?)\=\"(.*?)\"/", $string, $attribute_match, PREG_SET_ORDER);
	foreach ($attribute_match as $match) { 
		$value = str_replace("\"", "", $match[2]);
		$attributes[$match[1]] = $value;
	}
	
	// Return
	return $attributes;

}


//////////////////////////////////////////////////////////////////////////
// Create pagination links
//     $message - Contents of the message
//     $type - Type of message (can be: success, info, error)
//////////////////////////////////////////////////////////////////////////

public function create_pagination_links($total, $start = 0, $rows_per_page = 20, $route = '') { 
	if ($rows_per_page >= $total) { return ''; }
	if ($route == '') { $route = SITE_URI . '/' . $this->route; }

	// Initialize
	$total_pages = ceil($total / $rows_per_page);
	$current_page = $start >= $rows_per_page ? (ceil($start / $rows_per_page) + 1) : 1;
	$html = '<ul class="pagination">';

	// Get page range to display
	$start_page = ($current_page > 5) ? ($current_page - 5) : 1;
	$end_page = ($total_pages > ($current_page + 5)) ? ($current_page + 5) : $total_pages;

	// Previous page, if needed
	if ($current_page != 1) {
		$icon = $this->theme == 'staff' ? '<span class="icon12 minia-icon-arrow-left-3"></span>' : "&laquo;";
		$html .= '<li><a href="' . $route . '?start=' . ($start - $rows_per_page) . '">' . $icon . '</a></li>';
	}

	// Go thrwough pages
	for ($page_num = $start_page; $page_num <= $end_page; $page_num++) { 
		if ($page_num == $current_page) { 
			$html .= '<li class="active"><a>' . $page_num . '</a></li>';
		} else { 
			$html .= '<li><a href="' . $route . '?start=' . (($page_num - 1) * $rows_per_page) . '">' . $page_num . '</a></li>';
		}
	}

	// Next page, if needed
	if ($total > ($start + $rows_per_page)) { 
		$icon = $this->theme == 'staff' ? '<span class="icon12 minia-icon-arrow-right-3"></span>' : "&raquo;";
		$html .= '<li><a href="' . $route . '?start=' . ($start + $rows_per_page) . '">' . $icon . '</a></li>';
	}

	// Return
	$html .= '</ul>';
	return $html;

}

//////////////////////////////////////////////////////////////////////////
// Generate table HTML
//     $alias -- Table alias
//     $attr -- Attributes passed in HTML tag
//////////////////////////////////////////////////////////////////////////

private function generate_table($alias, $attr = array()) { 

	// Check
	$table_file = SITE_PATH . '/data/tables/' . $alias . '.php';
	if (!file_exists($table_file)) { trigger_error("Table does not exist with alias, $alias", E_USER_ERROR); }

	// Load table
	$class_name = 'table_' . $alias;
	require_once($table_file);
	$client = new $class_name($attr);

	// Set default variablesc
	if (!isset($client->class_name)) { $client->class_name = 'table table-bordered table-striped table-hover'; }
	if (!isset($client->rows_per_page)) { $client->rows_per_page = 20; }
	$start = isset($_GET['start']) ? $_GET['start'] : 0;

	// Add top pagination, if needed
	$html = '';
	if (isset($client->pagination) && $client->pagination == 'top') { 
		$total = $client->get_total();
		$html .= $this->create_pagination_links($total, $start) . "\n\n";
	}

	// Start HTML
	$html .= '<table class="' . $client->class_name . '"><thead><tr>' . "\n";
	foreach ($client->columns as $key => $name) { 
		$html .= "\t<th>$name</th>\n";
	}
	$html .= "</tr></thead>\n<tbody>\n";

	// Get rows to display
	$rows = $client->get_rows($start);

	// Go through rows
	foreach ($rows as $row) { 
		$html .= "<tr>\n";
		foreach ($client->columns as $key => $name) { 
			$value = isset($row[$key]) ? $row[$key] : '';
			$html .= "\t<td>$value</td>\n";
		}
		$html .= "</tr>\n";
	}

	// Add bottom pagination, if needed
	$html .= "</tbody></table>\n";
	if (isset($client->pagination) && $client->pagination == 'bottom') { 
		$total = $client->get_total();
		$html .= $this->create_pagination_links($total, $start, $client->rows_per_page) . "\n\n";
	}

	// Return
	return $html;
}

//////////////////////////////////////////////////////////////////////////
// Add message
//////////////////////////////////////////////////////////////////////////

public function add_message($message, $type = 'success') { 
	if ($message == '') { return; }
	if (!isset($this->user_messages[$type])) { $this->user_messages[$type] = array(); }

	array_push($this->user_messages[$type], $message);
	if ($type == 'error') { $this->has_errors = 1; }
}

}

?>