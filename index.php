<?php
/*

Automatically Hash Tagging Text Via PHP And MySQL
Part 2: Adding New Hash Tags To The Database Table
Copyright (C) 2011 Doug Vanderweide

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


//your database server variables
define('MYSQL_HOST', 'server');
define('MYSQL_USER', 'username');
define('MYSQL_PASS', 'password');
define('MYSQL_DB', 'database');
define('MYSQL_TABLE', 'table');
define('MYSQL_TERM_COLUMN', 'column');

function at_get_terms() {
	//retrieve terms from database
	//returns Boolean false on failure, array of terms on success
	
	if(!$link = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS)) {
		trigger_error('function at_get_terms: Cannot connect to database server. Please check your host name and credentials', E_USER_WARNING);
		return false;
	}
	
	if(!mysql_select_db(MYSQL_DB)) {
		trigger_error('function at_get_terms: Cannot select the database. Please check your database name', E_USER_WARNING);
		return false;
	}
	
	if(!$rs = mysql_query("SELECT " . MYSQL_TERM_COLUMN . " FROM " . MYSQL_TABLE)) {
		trigger_error('function at_get_terms: Error parsing query. MySQL error: ' . mysql_error(), E_USER_WARNING);
		return false;
	}
	
	if(mysql_num_rows($rs) == 0) {
		trigger_error('function at_get_terms: No terms found in database', E_USER_NOTICE);
		return false;
	}
	
	$out = array();
	while($row = mysql_fetch_array($rs)) {
		$out[] = $row[0];
	}
	return $out;
}

function save_tags($input, $terms) {
	//save new tags to database
	//returns Boolean false on error, 
	//string of added terms on success
	
	if(strlen(trim($input)) < 1) {
		trigger_error('function save_tags: string to be tagged is empty', E_USER_WARNING);
		return false;
	}
	if(!is_array($terms)) {
		trigger_error('function save_tags: terms is not an array', E_USER_WARNING);
		return false;
	}
	
	//get terms from subject string
	$tmp = array();
	$new_terms = array();
	preg_match_all('/#\w+(\s|$)/', $input, $tmp);
	foreach($tmp[0] as $term) {
		$new_terms[] = trim(strtolower(str_replace('#', '', $term)));
	}
	$tmp = array_diff($new_terms, $terms);
	return implode(", ", $tmp);
}

function autotag($input, $terms) {
	//tags $input with $terms
	//returns false on error, tagged string on success
	
	if(strlen(trim($input)) < 1) {
		trigger_error('function autotag: string to be tagged is empty', E_USER_WARNING);
		return false;
	}
	if(!is_array($terms)) {
		trigger_error('function autotag: terms is not an array', E_USER_WARNING);
		return false;
	}

	$tmp = array();	
	foreach($terms as $term){
		//matches will be terms exactly as in database,
		//followed by space or newline
		$tmp[] = "/($term)(\s|$)/i";
	}
	$out = preg_replace($tmp, '#$0', $input);
	$out = preg_replace('/#{2,}/', '#', $out);
	return $out;
}

$terms = at_get_terms();
$content = "Enter text in the textarea below, then click Submit. The text will be automatically tagged with terms contained in the database. Any newly tagged terms will be added to the database.";

if(isset($_POST['submit'])) {
	$content = "<strong>Hashtagged string:</strong> " . autotag(htmlspecialchars($_POST['ttext']), $terms);
	if($_POST['tadd'] == '1') {
		$content .= "<br /><strong>Terms added to database:</strong> " . save_tags(htmlspecialchars($_POST['ttext']), $terms);
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Automatically Hash Tagging Text Via PHP And MySQL Part 2: Adding New Hash Tags To The Database Table</title>
		<link rel="stylesheet" type="text/css" href="../demo.css">
	</head>
	<body>
		<h1>
			Automatically Hash Tagging Text Via PHP And MySQL<br />
			Part 2: Adding New Hash Tags To The Database Table
		</h1>
		<p class="notice"><?php echo $content; ?></p>
		<form id="tform" name="tform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<textarea id="ttext" name="ttext" cols="50" rows="3"><?php echo $_POST['ttext']; ?></textarea>
			<br />
			Add new terms to the database?<label><input type="radio" name="tadd" value="1" /> Yes</label> <label><input type="radio" name="tadd" value="0" checked="checked" /> No</label> 
			<br />
			<input type="submit" name="submit" id="submit" value="Submit" />
		</form>
		
		<h3>Terms in the database</h3>
		<table cellspacing="0">
		<?php
			for($i = 0; $i < count($terms); $i++) {
				if($i % 6 == 0) {
					echo "\t<tr>\n";
				}
				echo "\t\t<td>$terms[$i]</td>\n";
				if($i % 3 == 5) {
					echo "\t</tr>\n";
				}
			}
		?>
		</table>
		<p><a href="http://www.dougv.com/2011/04/12/automatically-hash-tagging-text-with-php-and-mysql-part-2-adding-new-hash-tags-to-the-database-table/">Automatically Hash Tagging Text Via PHP And MySQL Part 2: Adding New Hash Tags To The Database Table</a></p>
	</body>
</html>
