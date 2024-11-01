<?php
/*
Plugin Name: Voting Record
Plugin URI: http://www.davidjmiller.org/2009/voting-record/
Description: Stores and displays a searchable list of votes
Version: 2.0
Author: David Miller
Author URI: http://www.davidjmiller.org/
*/

/*
	Template Tag:
		e.g.: <?php recent_votes(); ?> shows recent votes based on set options
	Shortcodes:
		e.g.: [SEARCH-VOTES] allows searching of the recorded votes
		e.g.: [RECENT-VOTES] shows recent votes when used within a text widget
	Full help and instructions at http://www.davidjmiller.org/2009/voting-record/
*/

load_plugin_textdomain('voting_record', 'wp-content/plugins/voting-record'); 

function recent_votes() {
	global $wpdb;
	$table_name = $wpdb->prefix . "voting_record";
	$options = get_option(basename(__FILE__, ".php"));
	$limit = $options['limit'];
	$type = $options['type'];
	$recent_template = $options['recent_template'];
	$open_recent = str_replace('\"','"',$options['open_recent']);
	$close_recent = str_replace('\"','"',$options['close_recent']);
	$no_recent = str_replace('\"','"',$options['no_recent']);
	$extension = $options['extension'];
	$ordering = "date desc, id desc";
	$date = getdate();
	if ($recent_template == '') $recent_template = '<strong>{bill} - {vote}</strong><br/>';
	$query = "SELECT bill, description, vote, voter, date, result, tally FROM " . $table_name;
	switch ($type)
	{
	case 'days':
	case 'extend':
		$limitdate = date('Y-m-d', mktime(0,0,0,$date[mon],$date[mday] - $limit,$date[year]));
		$date= date('Y-m-d');
		$query .= " WHERE date >= '" . $limitdate . "' AND date <= '" . $date . "' ORDER BY " . $ordering;
		break;
	case 'votes':
	default:
		$query .= " ORDER BY " . $ordering . " LIMIT $limit";
		break;
	}
	$results = $wpdb->get_results($query);
	if (count($results)) {
		$votes_found = 'true';
	} else {
		$votes_found = 'false';
	}
	if ($type == 'extend') {
		if (count($results) < $extension) {
			$limit = $extension - count($results);
			$query = "SELECT bill, description, vote, voter, date, result, tally FROM " . $table_name;
			$query .= " ORDER BY " . $ordering . " LIMIT " . count($results) . ", " . $limit;
			$extras = $wpdb->get_results($query);
			if ((count($extras)) || (count($results))) {
				$votes_found = 'true';
			} else {
				$votes_found = 'false';
			}
		}
	}
	if ($votes_found == 'true') {
		echo $open_recent;
		foreach ($results as $cast) {
			$impression = $recent_template;
			$impression = str_replace("{bill}",$cast->bill,$impression);
			$impression = str_replace("{vote}",$cast->vote,$impression);
			$impression = str_replace("{voter}",$cast->voter,$impression);
			$impression = str_replace("{date}",$cast->date,$impression);
			$impression = str_replace("{desc}",$cast->description,$impression);
			$impression = str_replace("{result}",$cast->result,$impression);
			$impression = str_replace("{tally}",$cast->tally,$impression);
			echo $impression;
		}
		if (count($extras)) {
			foreach ($extras as $cast) {
				$impression = $recent_template;
				$impression = str_replace("{result}",$cast->result,str_replace("{tally}",$cast->tally,str_replace("{bill}",$cast->bill,str_replace("{vote}",$cast->vote,str_replace("{voter}",$cast->voter,str_replace("{desc}",$cast->description,str_replace("{date}",$cast->date,$impression)))))));
				echo $impression;
			}
		}
		echo $close_recent;
	} else {
		echo $no_recent;
	}
}
function search_votes() {
	global $wpdb;
	$table_name = $wpdb->prefix . "voting_record";
	$options = get_option(basename(__FILE__, ".php"));
	$search_template = $options['search_template'];
	$open_search = str_replace('\"','"',$options['open_search']);
	$close_search = str_replace('\"','"',$options['close_search']);
	$no_search = str_replace('\"','"',$options['no_search']);
	$bit = explode("&",$_SERVER['REQUEST_URI']);
	$url = $bit[0];
	$action = $bit[1];
	$place = $bit[2];
	$date = date('Y-m-d');
	if ($search_template == '') $search_template = '<strong>{bill} - {vote}</strong> {date}<br/>{tally}: {result}<br/>{desc}';

	//display a simple form allowing date, vote, and term search criteria
?>
	<form method="post" action="<?php echo $url . '&search'; ?>" class="search">
		<fieldset class="search">
			<table>
				<tr>
					<td align="right"><?php _e('Search Term', 'voting_record') ?>:</td>
					<td><input name="term" type="text" id="term" value="" size="30" /></td>
				</tr>
				<tr>
					<td align="right"><?php _e('Vote Cast', 'voting_record') ?>:</td>
					<td><select name="vote" id="vote">
						<option value=""></option>
						<option value="Yea"><?php _e('Yea', 'voting_record') ?></option>
						<option value="Nay"><?php _e('Nay', 'voting_record') ?></option>
						<option value="Present"><?php _e('Present', 'voting_record') ?></option>
					</select></td>
				</tr>
				<tr>
					<td align="right"><?php _e('Vote Date', 'voting_record') ?>:</td>
					<td><input name="date" type="text" id="date" value="" size="10" />(<?php echo $date; ?>)</td>
				</tr>
			</table>
		</fieldset>
		<div class="submit"><input type="submit" name="find_vote" value="<?php _e('Search', 'voting_record') ?>"  style="font-weight:bold;" /></div>
	</form>
	<?php	
	//Next we build the query if the submit button has been pressed
	if (isset($_POST['find_vote'])) {
		$term = $_POST['term'];
		$vote = $_POST['vote'];
		$date = $_POST['date'];
		$searching = 'true';
	}
	//return results if we are in searching mode
	if ($searching == 'true') {
		$where = ' WHERE';
		if ($term != NULL) {
			$where .= " (bill like '%" . $term . "%'";
			$where .= " OR description like '%" . $term . "%'";
			$where .= " OR voter like '%" . $term . "%')";
		}
		if ($vote != NULL) {
			if ($where != ' WHERE') $where .= ' AND';
			$where .= " vote = '" . $vote . "'";
		}
		if ($date != NULL) {
			if ($where != ' WHERE') $where .= ' AND';
			$where .= " date = '" . $date . "'";
		}
		if ($where == ' WHERE') $where = '';
		$ordering = " ORDER BY date desc, id desc";
		$query = "SELECT bill, description, vote, voter, date, result, tally FROM " . $table_name . $where . $ordering;
		$results = $wpdb->get_results($query);
		if (count($results)) {
			echo str_replace("{count}",count($results),$open_search);
			foreach ($results as $cast) {
				$impression = $search_template;
				$impression = str_replace("{bill}",$cast->bill,$impression);
				$impression = str_replace("{vote}",$cast->vote,$impression);
				$impression = str_replace("{voter}",$cast->voter,$impression);
				$impression = str_replace("{date}",$cast->date,$impression);
				$impression = str_replace("{desc}",$cast->description,$impression);
				$impression = str_replace("{result}",$cast->result,$impression);
				$impression = str_replace("{tally}",$cast->tally,$impression);
				echo $impression;
			}
			echo str_replace("{count}",count($results),$close_search);
		} else {
			echo $no_search;
		}
	}
}

function voting_record_dashboard_widget() {
	global $wpdb;
	$date = date('Y-m-d');
	$options = get_option(basename(__FILE__, ".php"));
	?>
		<div class="voting_record">
		<h2><?php __('Record a Vote', 'voting_record'); ?></h2>
		<form method="post" action="">
		<fieldset class="options">
		<table>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Bill Number', 'voting_record') ?>:</th>
				<td><input name="bill" type="text" id="bill" value="" size="10" /></td>
			</tr>
			<tr valign="top">
				<td align="right"><?php _e('Description', 'voting_record') ?>:</th>
				<td>
					<textarea name="description" id="description" rows="4" cols="40"></textarea>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Vote Cast', 'voting_record') ?>:</th>
				<td>
					<select name="vote" id="vote">
						<option value="Yea"><?php _e('Yea', 'voting_record') ?></option>
						<option value="Nay"><?php _e('Nay', 'voting_record') ?></option>
						<option value="Present"><?php _e('Present', 'voting_record') ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<td align="right"><?php _e('Vote Cast By', 'voting_record') ?>:</th>
				<td><input name="voter" type="text" id="voter" value="<?php echo $options['primary_voter']; ?>" size="20" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Vote Cast On', 'voting_record') ?>:</th>
				<td><input name="date" type="text" id="date" value="<?php echo $date; ?>" size="10" /></td>
			</tr>
			<tr valign="top">
				<td align="right"><?php _e('Vote Result', 'voting_record') ?>:</th>
				<td>
					<select name="result" id="result">
						<option value=""></option>
						<option value="pass"><?php _e('Pass', 'voting_record') ?></option>
						<option value="fail"><?php _e('Fail', 'voting_record') ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<td align="right"><?php _e('Vote Tally', 'voting_record') ?>:</th>
				<td><input name="tally" type="text" id="tally" value="" size="10" /></td>
			</tr>
		</table>
		</fieldset>
		<div class="submit"><input type="submit" name="record_vote" value="<?php _e('Save', 'voting_record') ?>"  style="font-weight:bold;" /></div>
		</form>    		
	</div>
	<?php	
	if (isset($_POST['record_vote'])) {
		// Prepare to record a vote
		$bill = $_POST['bill'];
		$description = $_POST['description'];
		$vote = $_POST['vote'];
		$voter = $_POST['voter'];
		$date = $_POST['date'];
		$result = $_POST['result'];
		$tally = $_POST['tally'];
		if (($bill == '') || ($vote == '') || ($date == '')) {
			echo '<div class="updated"><p>' . __('No Vote Recorded - be sure you include a bill number, a vote, and the date.', 'voting_record') . '</p></div>';
		} else {
			// store the values in the database
			$table_name = $wpdb->prefix . "voting_record";
			$insert = "INSERT INTO " . $table_name .
				" (bill, description, vote, voter, date, result, tally) " .
				"VALUES ('" . $bill . "','" . $description . "','$vote','" . 
				$voter . "','$date','$result','" . $tally . "')";
			$results = $wpdb->query($insert);

			// Show a message to say we've done something
			echo '<div class="updated"><p>' . __('Vote Recorded', 'voting_record') . 
				'</p></div>';
		}
	}
} 

// Create the function use in the action hook

function add_voting_record_widget() {
	wp_add_dashboard_widget('voting_record', 'Record Votes', 'voting_record_dashboard_widget');	
} 

// Hook into the 'wp_dashboard_setup' action to register our other functions

add_action('wp_dashboard_setup', 'add_voting_record_widget' );

$vr_db_version = "1.0";

function install_vr() {
	global $wpdb;
	global $vr_db_version;
	$date = date('Y-m-d');

	$table_name = $wpdb->prefix . "voting_record";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
		id int(11) NOT NULL auto_increment,
		bill varchar(10) NOT NULL default '',
		description varchar(255),
		vote varchar(10) NOT NULL default '',
		voter varchar(64),
		date DATE default '$date' NOT NULL,
		result varchar(6),
		tally varchar(20),
		UNIQUE KEY id (id)
		);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	add_option("vr_db_version", $vr_db_version);
   }
}

register_activation_hook(__FILE__,'install_vr');

/*
	Define the options menu
*/

function voting_record_option_menu() {
	if (function_exists('current_user_can')) {
		if (!current_user_can('manage_options')) return;
	} else {
		global $user_level;
		get_currentuserinfo();
		if ($user_level < 8) return;
	}
	if (function_exists('add_options_page')) {
		add_options_page(__('Voting Record Options', 'voting_record'), __('Voting Record', 'voting_record'), "manage_options", __FILE__, 'voting_record_options_page');
		add_submenu_page('post-new.php', __('Manage Recorded Votes', 'voting_record'), __('Manage Recorded Votes', 'voting_record'), "publish_posts", __FILE__, 'manage_recorded_votes_page');
	}

}

// Install the options page and management page
add_action('admin_menu', 'voting_record_option_menu');

// the plugin options are stored in the options table under the name of the plugin file sans extension
add_option(basename(__FILE__, ".php"), $default_options, 'options for the Voting Record plugin');

// This method displays recorded votes for editing
function manage_recorded_votes_page(){
	global $wpdb;
	$table_name = $wpdb->prefix . "voting_record";
	echo '<h1>'.__('Manage Recorded Votes', 'voting_record').'</h1>';
	$bit = explode("&",$_SERVER['REQUEST_URI']);
	$url = $bit[0];
	$action = $bit[1];
	$id = $bit[2];
	if ($action == 'edit') {
		if (isset($_POST['edit_vote'])) {
			$description = $_POST['description'];
			$voter = $_POST['voter'];
			$result = $_POST['result'];
			$tally = $_POST['tally'];
			$edit = "UPDATE " . $table_name . " SET description = '".$description."', voter = '".$voter."', result = '".$result."', tally = '".$tally."' WHERE id = ".$id;
			$results = $wpdb->query($edit);

			// Show a message to say we've done something
			echo '<div class="updated"><p>' . __('Vote Updated', 'voting_record') . 
				'</p></div>';
		}
		$get = 'SELECT bill, description, vote, voter, date, result, tally FROM '.$table_name.' WHERE id = '.$id;
		$results = $wpdb->get_results($get);
		if (count($results)) {
			foreach ($results as $vote_record) {
				?>
		<form method="post" action="">
		<fieldset class="options">
		<table>
			<tr valign="top">
				<th scope="row" align="right"><?php echo $vote_record->bill.':<br/>'.$vote_record->date.'<br/>'.$vote_record->vote; ?></th>
				<td><textarea name="description" id="description" rows="4" cols="40"><?php echo $vote_record->description; ?></textarea></td>
				<td><input name="voter" type="text" id="voter" value="<?php echo $vote_record->voter; ?>" size="20" /><br/>
					<select name="result" id="result">
						<option value=""></option>
						<option value="pass"<?php if ($vote_record->result == 'pass') echo ' selected'; ?>><?php _e('Pass', 'voting_record') ?></option>
						<option value="fail"<?php if ($vote_record->result == 'fail') echo ' selected'; ?>><?php _e('Fail', 'voting_record') ?></option>
					</select>
				<br/>
				<input name="tally" type="text" id="tally" value="<?php echo $vote_record->tally; ?>" size="10" /></td>
			</tr>
		</table>
		</fieldset>
		<div class="submit"><input type="submit" name="edit_vote" value="<?php _e('Save', 'voting_record') ?>"  style="font-weight:bold;" /></div>
		</form>
		<form method="post" action="<?php echo $url.'&delete&'.$id; ?>">
		<div class="submit"><input type="submit" name="delete_vote" value="<?php _e('Remove This Vote', 'voting_record') ?>"  style="font-weight:bold;" />(Select "Remove This Vote" if the bill number, vote, or date are incorrect - you must record a new vote)</div>
		</form>
				<?php
			}
		}
	}
	if ($action == 'delete') {
		?>
		<form method="post" action="<?php echo $url.'&confirm_delete&'.$id; ?>">
		<div class="submit"><input type="submit" name="confirm_delete" value="<?php _e('Yes, Delete that vote', 'voting_record') ?>"  style="font-weight:bold;" /></div>
		</form>
		<?php
	}
	if ($action == 'confirm_delete') {
		$delete = "DELETE FROM " . $table_name . " WHERE id = ".$id;
		$results = $wpdb->query($delete);

		// Show a message to say we've done something
		echo '<div class="updated"><p>' . __('Vote Deleted', 'voting_record') . '</p></div>';
	}
	$end = 'SELECT max(id) last FROM '.$table_name;
	$results = $wpdb->get_results($end);
	if (count($results)) {
		foreach ($results as $result) {
			$last = $result->last;
		}
	}
	$list = 'SELECT id, bill, description, vote, voter, date, result, tally FROM '.$table_name;
	if ($action == 'next') {
		$list .= ' WHERE id > '.$id;
	}
	$list .= ' ORDER BY id LIMIT 20';
	$results = $wpdb->get_results($list);
	if (count($results)) {
		echo '<table><tr><td width="100">Non Editable</td><td width="100">Editable Data</td><td width="250">Editable Description</td><td width="50"></td></tr>';
		$current = 0;
		foreach ($results as $result) {
			echo '<tr><td><strong><a href="'.$url.'&edit&'.$result->id.'">Edit '.$result->bill.'</a></strong><br/>'.$result->date.'<br/>'.$result->vote.'</td><td>'.$result->voter.'<br/>'.$result->result.'<br/>'.$result->tally.'</td><td>'.$result->description.'</td><td><a href="'.$url.'&delete&'.$result->id.'">Delete</a></td></tr>';
			$current = $result->id;
		}
		echo '<tr><td>';
		if ($action == 'next') {
			echo '<a href="'.$url.'">Start Over</a> ';
		}
		echo '</td><td></td><td></td><td>';
		if ($current < $last) {
			echo '<a href="'.$url.'&next&'.$current.'">View More</a>';
		}
		echo '</td></tr></table>';
	}
}

// This method displays, stores and updates all the options
function voting_record_options_page(){
	global $wpdb;
	// This bit stores any updated values when the Update button has been pressed
	if (isset($_POST['update_options'])) {
		// Fill up the options array as necessary
		$options['limit'] = $_POST['limit'];
		$options['type'] = $_POST['type'];
		$options['primary_voter'] = $_POST['primary_voter'];
		$options['open_recent'] = $_POST['open_recent'];
		$options['close_recent'] = $_POST['close_recent'];
		$options['no_recent'] = $_POST['no_recent'];
		$options['recent_template'] = $_POST['recent_template'];
		$options['open_search'] = $_POST['open_search'];
		$options['close_search'] = $_POST['close_search'];
		$options['no_search'] = $_POST['no_search'];
		$options['search_template'] = $_POST['search_template'];
		if (($_POST['extension'] == '') || ($_POST['extension'] < 1)) {
			$options['extension'] = 5;
		} else {
			$options['extension'] = $_POST['extension'];
		}

		// store the option values under the plugin filename
		update_option(basename(__FILE__, ".php"), $options);
		
		// Show a message to say we've done something
		echo '<div class="updated"><p>' . __('Options saved', 'voting_record') . '</p></div>';
	} else {
		// If we are just displaying the page we first load up the options array
		$options = get_option(basename(__FILE__, ".php"));
	}
	//now we drop into html to display the option page form
	?>
		<div class="wrap">
		<h2><?php echo ucwords(str_replace('-', ' ', basename(__FILE__, ".php"). __(' Options', 'voting_record'))); ?></h2>
		<h3><a href="http://www.davidjmiller.org/2009/voting-record/"><?php _e('Help and Instructions', 'voting_record') ?></a></h3>
		<form method="post" action="">
		<fieldset class="options">
		<table class="optiontable">
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Primary Voter', 'voting_record') ?>:</th>
				<td><input name="primary_voter" type="text" id="primary_voter" value="<?php echo $options['primary_voter']; ?>" size="20" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Show votes for the last', 'voting_record') ?>:</th>
				<td><input name="limit" type="text" id="limit" value="<?php echo $options['limit']; ?>" size="2" />
					<select name="type" id="type">
						<option value="votes"<?php if ($options['type'] == 'votes') echo ' selected'; ?>><?php _e('Votes', 'voting_record') ?></option>
						<option value="days"<?php if ($options['type'] == 'days') echo ' selected'; ?>><?php _e('Days', 'voting_record') ?></option>
						<option value="extend"<?php if ($options['type'] == 'extend') echo ' selected'; ?>><?php _e('Days plus', 'voting_record') ?></option>
					</select>
</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('List Minimum', 'voting_record') ?>:</th>
				<td><input name="extension" type="text" id="extension" value="<?php echo $options['extension']; ?>" size="20" /><?php _e('Minimum list size when using "Days plus" mode', 'voting_record') ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Text and Code before Recent Votes', 'voting_record') ?>:</th>
				<td><input name="open_recent" type="text" id="open_recent" value="<?php echo htmlspecialchars(stripslashes($options['open_recent'])); ?>" size="40" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Text and Code after Recent Votes', 'voting_record') ?>:</th>
				<td><input name="close_recent" type="text" id="close_recent" value="<?php echo htmlspecialchars(stripslashes($options['close_recent'])); ?>" size="40" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Recent Votes template', 'voting_record') ?>:</th>
				<td><textarea name="recent_template" id="recent_template" rows="3" cols="60"><?php echo htmlspecialchars(stripslashes($options['recent_template'])); ?></textarea><br/><?php _e('Valid template tags', 'voting_record') ?>:{vote}, {bill}, {voter}, {date}, {desc}, {result}, {tally}</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Text and Code if no Recent Votes', 'voting_record') ?>:</th>
				<td><input name="no_recent" type="text" id="no_recent" value="<?php echo htmlspecialchars(stripslashes($options['no_recent'])); ?>" size="40" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Text and Code before Search Results', 'voting_record') ?>:</th>
				<td><input name="open_search" type="text" id="open_search" value="<?php echo htmlspecialchars(stripslashes($options['open_search'])); ?>" size="40" /><br/><?php _e('If you want to show the number of results use the template tag ', 'voting_record') ?>{count}
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Text and Code after Search Results', 'voting_record') ?>:</th>
				<td><input name="close_search" type="text" id="close_search" value="<?php echo htmlspecialchars(stripslashes($options['close_search'])); ?>" size="40" /><br/><?php _e('If you want to show the number of results use the template tag ', 'voting_record') ?>{count}
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Search Votes template', 'voting_record') ?>:</th>
				<td><textarea name="search_template" id="search_template" rows="3" cols="60"><?php echo htmlspecialchars(stripslashes($options['search_template'])); ?></textarea><br/><?php _e('Valid template tags', 'voting_record') ?>:{vote}, {bill}, {voter}, {date}, {desc}, {result}, {tally}</td>
			</tr>
			<tr valign="top">
				<th scope="row" align="right"><?php _e('Text and Code if no Search Results', 'voting_record') ?>:</th>
				<td><input name="no_search" type="text" id="no_search" value="<?php echo htmlspecialchars(stripslashes($options['no_search'])); ?>" size="40" />
				</td>
			</tr>
		</table>
		</fieldset>
		<div class="submit"><input type="submit" name="update_options" value="<?php _e('Update', 'voting_record') ?>"  style="font-weight:bold;" /></div>
		</form>    		
	</div>
	<?php	
}

$options = get_option(basename(__FILE__, ".php"));
add_shortcode('SEARCH-VOTES', 'search_votes');
add_shortcode('RECENT-VOTES', 'recent_votes');
?>