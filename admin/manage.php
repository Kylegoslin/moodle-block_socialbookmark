<?php
// ---------------------------------------------------------------------------------
// Social Bookmark Block for Moodle
// Copyright 2013-2014 Kyle Goslin, Daniel McSweeney
// Institute of Technology Blanchardstown
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// ---------------------------------------------------------------------------------

/**
 * Version details
 *
 * @package    block_socialbookmark
 * @copyright  2014 Kyle Goslin, Daniel McSweeney
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../../config.php");
require_once("../sb_lib.php");
global $CFG, $DB;
$formpath = "$CFG->libdir/formslib.php";
require_once($formpath);


$cid = optional_param('id', 0, PARAM_INT);

if (empty($cid)) {
	print_error('No User Id (cid)');
}
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('Course Page', new moodle_url('../../../course/view.php?id=' . $cid));
$PAGE->navbar->add(get_string('socialbookmark', 'block_socialbookmark'), new moodle_url('manage.php?id=' . $cid . '&p=my'));
$PAGE->navbar->add(get_string('manage', 'block_socialbookmark'));
$PAGE->set_url('/blocks/socialbookmark/admin/manage.php');
$PAGE->set_context(context_course::instance($cid));

$p = optional_param('p', 'all', PARAM_TEXT);
$d = optional_param('d', 0, PARAM_INT);


// If the delete record flag is set
if (!empty($d)) {
    // Delete the record
    $DB->delete_records('block_socialbookmark', array('id'=>$d));
    // Assoc ratings
    $DB->delete_records('block_socialbookmark_ratings', array('bm_id'=>$d));
}

//Store the current user id in JS format, for use later.
echo '<script> var userid="'.$USER->id.'";</script>';
?>


<!--// plugin-specific resources //-->
<script src='../star/jquery.js' type="text/javascript"></script>
<script src='../star/jquery.MetaData.js' type="text/javascript" language="javascript"></script>
<script src='../star/jquery.rating.js' type="text/javascript" language="javascript"></script>
<link href='../star/jquery.rating.css' type="text/css" rel="stylesheet"/>
<style>
#maindiv {
	padding-top: 10px;
	padding-bottom: 10px;
	padding-left: 10px;
	padding-right:10px;
	border:0px solid;
	width: 90%;
}
#titlebar {
	font-size: 16px;
}
#singlebookmark{
	padding-top:10px;

}
</style>

<?php
/**
* Bookmark management interface
*
* @package block_socialbookmark
* @copyright 2014 Kyle Goslin, Daniel McSweeney
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class manage_form extends moodleform {
 
    /** Main User interface
    */
    function definition() {
        global $CFG;
        global $currentsess;
		global $ineditingmode, $DB;
		
        global $cid;

		$mform =& $this->_form; 
		$mform->addElement('header', 'mainheader','<span style="font-size:18px"> '.  get_string('manage','block_socialbookmark'). '</span>');

      	// Page description text
		$mform->addElement('html', '<p></p><div id="titlebar">
                                    <a href="manage.php?id='.$cid.'&p=my"><img style="height:16; width:16"src="../img/folder.png"> '.
                                    get_string('mybookmarks', 'block_socialbookmark').'</a>  
			                        <a href="manage.php?id='.$cid.'&p=all"><img style="height:16; width:16" src="../img/view.png"> '.
			                        get_string('viewall', 'block_socialbookmark').'</a>  <b>
			                        <a href="../add.php?cid='.$cid.'"><img style="height:16; width:16" src="../img/fave.png"> '.
			                        get_string('add', 'block_socialbookmark').'</a></b>
				
                                    </div><p></p>
				                    ' .get_filter().'
			
					                '.get_cloud($cid, 12, '').'
				                    <p></p>
                                    <div id="maindiv">'.
				                    get_bookmarks().' </div>');
	

	}
}


$mform = new manage_form();//name of the form you defined in file above.



echo $OUTPUT->header();
$mform->focus();
$mform->set_data($mform);
$mform->display();
echo $OUTPUT->footer();





/** Get the filter controls for the top right hand corner */
function get_filter() {
	global $cid;

	$out = '<script>
    	    function filterByRating(value){
    	        window.location="manage.php?id='.$cid.'&p=rating&r=" + value;
    		

    	    }
        	</script>
    		<div style="float:right">
    	
    		'. get_string('filterbyrating', 'block_socialbookmark').'
    		
			<select id="ratingSelected" onchange="filterByRating(this.value)">
			  <option value="1">1</option>
			  <option value="2">2</option>
			  <option value="3">3</option>
			  <option value="4">4</option>
			  <option value="5">5</option>
			  
			</select>
		
			</div>
    ';

    $p = optional_param('p', '', PARAM_TEXT); 
    if ($p != 'my') {
    		return $out;
    } else {
    	return '';
    }
}


/**  Return a list of bookmarks to the caller in a displayable HTML format.*/
function get_bookmarks() {
    global $DB, $CFG, $cid, $p, $USER;
	
    $outputhtml = '';
    $offset = '';

    $jump = optional_param('jump', '', PARAM_INT);
    if (!empty($jump)) {
		$offsetnum = ($jump -1) * 10;
		$offset = ' LIMIT 10 OFFSET ' . $offsetnum;
    } 

	// Buld up a page offset if set
	$totalrecordcounter = 0;

	if ($p == 'my') { // Show only the current users bookmarks
			$result = $DB->get_records_sql('SELECT * 
                                            FROM {block_socialbookmark} 
											WHERE courseid = ? 
	                                        AND userid = ?' . $offset, array($cid,$USER->id)); 

			// Count the records
			$totalrecordcounter = $DB->count_records_sql('SELECT count(id) 
                                                          FROM {block_socialbookmark} 
				                                          WHERE courseid = ? 
				                                          AND userid = ?' . $offset, array($cid,$USER->id)); 



	}
	else if ($p == 'all') { // Show all bookmarks for the current course
		// Show all the records
		$result = $DB->get_records_sql('SELECT * 
			                            FROM {block_socialbookmark} 
										WHERE courseid = ?' . $offset, array($cid));

		// Count the records
		$totalrecordcounter = $DB->count_records_sql('SELECT count(id) 
			                                          FROM {block_socialbookmark} 
										              WHERE courseid = ?', array($cid)); 

	}
	else if ($p == 'user') { // Show all bookmarks for the current course
		
		// Show all the records
		$uid = optional_param('uid',0, PARAM_INT);
		$result = $DB->get_records_sql('SELECT * 
			                            FROM {block_socialbookmark} 
										WHERE userid = ?' . $offset, array($uid));

		// Count the records
		$totalrecordcounter = $DB->count_records_sql('SELECT count(id) 
			                                          FROM {block_socialbookmark} 
							                          WHERE userid = ?', array($uid)); 


		$outputhtml .= '<div style="height:60px; width:60%; background:#f3f3f3">
						    <b>'.  get_string('filterby','block_socialbookmark'). ':</b> '.  
							get_string('user','block_socialbookmark'). 
							' <a href="manage.php?id='.$cid.'">[x]</a> <br>  
							 <input type="button" value="Remove All Filters" onclick="window.location.href=\'manage.php?id='.$cid.'\';"> 
						</div>	';
	
	}
    else if ($p == 'rating') {

        $result = $DB->get_records_sql('SELECT * 
	   	                               FROM {block_socialbookmark} 
	   								   WHERE courseid = ?' . $offset, array($cid));


		// Count the records
		$totalrecordcounter = $DB->count_records_sql('SELECT count(id) 
			                                          FROM {block_socialbookmark} 
	   								                  WHERE courseid = ?', array($cid)); 


        $r = optional_param('r', 0, PARAM_INT);
	    $outputhtml .= '<div style="height:60px; width:60%; background:#f3f3f3">
	   						<b>'.  get_string('filterby','block_socialbookmark'). ':</b> '.  
	   						get_string('rating','block_socialbookmark'). ' - '.$r.' 
	   						<a href="manage.php?id='.$cid.'">[x]</a><br>
	   						<input type="button" value="Remove All Filters" onclick="window.location.href=\'manage.php?id='.$cid.'\';"> 
	   				   </div>	';
	

	}
	else if($p == 'tag'){ // Filter by: course & tag
			
	    $tagid = optional_param('tagid',0, PARAM_INT);
		$result = $DB->get_records_sql("SELECT * 
				                        FROM {block_socialbookmark} 
								    	WHERE `tagid` LIKE '%{$tagid}%' " . $offset, array($cid));

		// Count the records
		$totalrecordcounter = $DB->count_records_sql("SELECT count(id) 
			                                          FROM {block_socialbookmark} 
		                							  WHERE `tagid` LIKE '%{$tagid}%' ", array($cid)); 




 		$outputhtml .= '<div style="height:60px; width:60%; background:#f3f3f3">
 						<b>'.  get_string('filterby','block_socialbookmark'). ':</b> '.  
 					     '' . tag_id_to_name($tagid) . ' 
 						<a href="manage.php?id='.$cid.'">[x]</a><br>
 						<input type="button" value="Remove All Filters" onclick="window.location.href=\'manage.php?id='.$cid.'\';"> 
 						</div>	';
	}

	$numberofrecords = 0;

	foreach ($result as $rec) {
            $printrec = false;
            $r = optional_param('r', 0, PARAM_INT);
	  		if (!empty($r)) {
		  		$score = get_score_num($rec->id);
		  	 	
			    if ($score == $r) {
	               	   $printrec = true;
	            }
	            
	        } else {
	        	$printrec = true;
	        }



            if ($printrec == true) {
					$numberofrecords++;
					
					// Get the raw record count, so we can output how many users have created
					// rating records
					$sql2 = 'select count(rating) from ' . $CFG->prefix .'block_socialbookmark_ratings WHERE bm_id = ?';
					$dbrecordcount = $DB->get_field_sql($sql2, array($rec->id), $strictness=IGNORE_MISSING);
					$reccount =  '(' . intval($dbrecordcount) . ' ratings)';
			

		            $outputhtml .= '<div id="singlebookmark" style="overflow:hidden; padding:10px; border-bottom:1px solid #f3f3f3;">';
					$outputhtml .= '<div style="float:left; width:60%; ">';
			  		$outputhtml .=' <table width="95%" border="0">';
			  		$outputhtml .= '<tr><td style="width:20%"><span style="font-size: 14px; font-weight:bold">'.  get_string('title','block_socialbookmark'). ': </span></td>
			  		<td><a href="'.validate_link($rec->link).'" target="_blank"> ' . $rec->titletext . ' - '.get_stemmed_link($rec->link).'</a></td></tr>';

			  		 // Print out the user who submitted the link
			  		 $subby = $DB->get_record('user', array('id'=> $rec->userid), $fields='*', $strictness=IGNORE_MISSING); 
					 $submittedbystring = '';
                     if (!empty($subby)) {
					     $submittedbystring = $subby->firstname .' ' . $subby->lastname;
			         }
			  		 // Print the date and time submitted
			  		 $dateAdded = $rec->dateadded;
			  		 $dt = new DateTime("@$dateAdded");

			  	 
			  		 $outputhtml .= '	<tr><td style="width:20%; vertical-align: top;text-align: left;"><span style="font-size: 11px; font-weight:bold">'.get_string('description', 'block_socialbookmark').': </span></td>
			  		 <td><span style="font-size: 11px;">' . check_and_trim_desc($rec->desctext) . '</span></td></tr>';
			  		 $outputhtml .= '	<tr><td style="width:20%"><span style="font-size: 11px; font-weight:bold">'.get_string('tag', 'block_socialbookmark').': </span> </td>
			  		 <td><span style="font-size: 11px;"> '. get_tags($rec->tagid) . '</span></td></tr>';
	
			  		 $outputhtml .= '	</table>';
			  		 $outputhtml .= '</div>';
	  		 

			  		 // Right div
			  		 $outputhtml .='<div style="overflow: hidden;"> ';
			  		 $outputhtml .= '	<table width="350px" border="0">';
			  		 $outputhtml .= '	<tr><td style="width:150px"><span style="font-size: 11px; font-weight:bold">'.get_string('rating', 'block_socialbookmark').':</span> </td><td ><span style="font-size: 11px;"> ' . get_rating_score($rec->id) . ' ' .$reccount.'</span></td></tr>';
			  		 $outputhtml .= '	<tr><td style="width:150px"><span style="font-size: 11px; font-weight:bold">'.get_string('dateadded', 'block_socialbookmark').':</span> </td><td><span style="font-size: 11px;"> ' . $dt->format('Y-m-d H:i:s'). '</span></td></tr>';
			  		 $outputhtml .= '	<tr><td style="width:150px"><span style="font-size: 11px; font-weight:bold">'.get_string('submittedby', 'block_socialbookmark').':</span> </td><td> <span style="font-size: 11px;"><a href="manage.php?id='.$cid.'&p=user&uid='.$rec->userid.'">' . $submittedbystring. '</span></a></td></tr>';
			  		 // If the current user created this record, then allow them to delete it.
			  		 if ($rec->userid == $USER->id) {
			  		 	$outputhtml .= '<tr><td style="width:150px"><span style="font-size: 11px; font-weight:bold"></span> </td><td> <span style="font-size: 11px;"><a href="manage.php?id='.$cid.'&d='.$rec->id.'">['.get_string('delete', 'block_socialbookmark').']</span></a></td></tr>';
					 }
					 $outputhtml .=' 	</table>';
					 $outputhtml .= '</div>';


			  		 $outputhtml .= '</div> ';
			}
	  		
		 }

		 
        // Calculate the page numbers for the
        // end of the page based upon the amount
        // of records that exist.
	 	$p_sec = optional_param('p', '', PARAM_TEXT);    
		$p = '';
		if (!empty($p_sec)) {
			$p = 'p=' . $p_sec . '&';

		}

		$outputhtml.= '<center>';
		$numberofrecords = optional_param('tp',0, PARAM_INT);
		
		
		$url = 'manage.php?id=' . $cid .'&'.$p;
		$jumpcounter = $totalrecordcounter / 10;
		 	
		$outputhtml .= ' <a href="'.$url.'">[ 1 ]</a>';

		for ($x = 2; $x < $jumpcounter+1; $x++) {
 		
 			$url = 'manage.php?id=' . $cid;
 			$outputhtml .= ' <a href="'.$url.'&tp='.$totalrecordcounter.'&'.$p.'jump='.$x.'">[ '.$x.' ]</a>';
 		}
		 
        $outputhtml.= '</center>';
	return $outputhtml;

}

/** If a description is too long, use this method to trim
* it down to a better size.
* @param text $originaldesc original description
*/
function check_and_trim_desc($originaldesc){


		$cleanedstring = '';
		if(strlen($originaldesc) > 300){

			$cleanedstring = substr($originaldesc, 0, 300) . '...';

		} else {
			$cleanedstring = $originaldesc;
		}
    return $cleanedstring;

}




/** Cut the trailing half of a url off, leaving on the
* base url.
* @param text $link full un cleaned url string
*/
function get_stemmed_link($link) {

	return parse_url(validate_link($link), PHP_URL_HOST);
}


/** 
* List of tags 
* @param text $ids complete list of ids used as a single string
*/
function get_tags($ids) {

	$fulltagstring = '';
    $tags = explode(' ', $ids);
    $tagcounter = 0;

	foreach ($tags as $tag) {
		if ($tagcounter < 10) {
			if (!empty($tag)) {
				$fulltagstring .=  tag_id_to_name($tag) . ', ';
			}
		}
		$tagcounter++;
	}

	return rtrim($fulltagstring, ", ");

}



/** Return the current score number
* @param int $id of single record
*/
function get_score_num($id) {

		global $DB, $CFG;
		$sql = 'select avg(rating) from ' . $CFG->prefix .'block_socialbookmark_ratings WHERE bm_id = ?';
		$res = $DB->get_field_sql($sql,array($id) , $strictness=IGNORE_MISSING);

		$score = intval($res);

	return $score;
}




/** Return the average rating score for an indivdual bookmark record 
* @param int $id single record id
*/
function get_rating_score($id) {


	global $DB, $CFG;
    $sql = 'select avg(rating) from ' . $CFG->prefix .'block_socialbookmark_ratings WHERE bm_id = ?';
	$res = $DB->get_field_sql($sql,array($id) , $strictness=IGNORE_MISSING);

	$score = intval($res);
		
	$r1 = '';
	$r2 = '';
	$r3 = '';
	$r4 = '';
	$r5 = '';
	
	if ($score == '1') {
		$r1 = 'checked="true" ';
	}
	else if ($score == '2') {
		$r2 = 'checked="true"';
	}
	else if ($score == '3') {
		$r3 = 'checked="true"';
	}
	else if ($score == '4') {
		$r4 = 'checked="true"';
	}
	else if ($score == '5') {
		$r5 = 'checked="true"';
	}



    $output = '';

	$output.= '<input name="'.$id.'" type="radio" value="1" class="auto-submit-star" '.$r1.'/>';
    $output.= '<input name="'.$id.'" type="radio" value="2" class="auto-submit-star" '.$r2.'/>';
    $output.= '<input name="'.$id.'" type="radio" value="3" class="auto-submit-star" '.$r3.'/>';
    $output.= '<input name="'.$id.'" type="radio" value="4" class="auto-submit-star" '.$r4.'/>';
    $output.= '<input name="'.$id.'" type="radio" value="5" class="auto-submit-star" '.$r5.'/>';



	return $output;

}



?>



<script>
		// Send the star rating in to the db.
		  $('.auto-submit-star').rating({
		  callback: function(value, link){
		    var selectedvalue = value;
		    var bkid = this.name;
		    var user = userid;

					    $.post( "../ajax.php", { type: "saverating", bk: bkid, userid: user, selectedrating: selectedvalue })
						  .done(function( data ) {
						    //alert( "Data Loaded: " + data );
						  });

		  }
		});
</script>


<?php
$r = optional_param('r', '', PARAM_TEXT);  
if (!empty($r)) {
	$r = $r -1;
	echo "<script> document.getElementById('ratingSelected').selectedIndex = $r;</script>";
}




