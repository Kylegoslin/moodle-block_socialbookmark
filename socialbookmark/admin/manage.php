<?php
/*
	---------------------------------------------------------------------------------
 	Social Bookmark Block for Moodle
 	Copyright 2013-2014 Kyle Goslin, Daniel McSweeney
	Institute of Technology Blanchardstown

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
	---------------------------------------------------------------------------------

*/
require_once("../../../config.php");
require_once("../sb_lib.php");
global $CFG, $DB;
$formPath = "$CFG->libdir/formslib.php";
require_once($formPath);
$cid = required_param('id', PARAM_INT);

/** Navigation Bar **/
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('Course Page', new moodle_url('../../../course/view.php?id=' . $cid));
$PAGE->navbar->add(get_string('socialbookmark', 'block_socialbookmark'), new moodle_url('manage.php?id=' . $cid . '&p=my'));
$PAGE->navbar->add(get_string('manage', 'block_socialbookmark'));
$PAGE->set_url('/blocks/cmanager/manage.php');
$PAGE->set_context(get_system_context());

$p = optional_param('p', 'all', PARAM_TEXT);
$d = optional_param('d', '', PARAM_TEXT);


// If the delete record flag is set
if(isset($d)){

	// Delete the record
	$DB->delete_records('block_socialbookmark', array('id'=>$d));

	// Assoc ratings
	$DB->delete_records('block_socialbookmark_ratings', array('bm_id'=>$d));
	
}



//Store the current user id in JS format, for use later.
echo '<script> var userId="'.$USER->id.'";</script>';
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
class manage_form extends moodleform {
 
    function definition() {
        global $CFG;
        global $currentSess;
		global $inEditingMode, $DB;
		
       global $cid;

		$mform =& $this->_form; 
		$mform->addElement('header', 'mainheader','<span style="font-size:18px"> '.  get_string('manage','block_socialbookmark'). '</span>');

      	// Page description text
		$mform->addElement('html', '<p></p><div id="titlebar">

			<a href="manage.php?id='.$cid.'&p=my"><img style="height:16; width:16"src="../img/folder.png"> '.get_string('mybookmarks', 'block_socialbookmark').'</a>  
			<a href="manage.php?id='.$cid.'&p=all"><img style="height:16; width:16" src="../img/view.png"> '.get_string('viewall', 'block_socialbookmark').'</a>  <b>
			<a href="../add.php?cid='.$cid.'"><img style="height:16; width:16" src="../img/fave.png"> '.get_string('add', 'block_socialbookmark').'</a></b>
				
			
				</div><p></p>
				' .getFilter().'
			
					'.getCloud($cid, 12, '').'
				<p></p>

				<div id="maindiv">'.
				
				getBookmarks().' </div>');
	

	}
}


   $mform = new manage_form();//name of the form you defined in file above.



   echo $OUTPUT->header();
  
   $mform->focus();
   $mform->set_data($mform);
   $mform->display();
   echo $OUTPUT->footer();


function getPageNumberFilter(){

	global $cid;
	$out = '';



	return $out;

}
/*
Get the filter controls for the top right hand corner

*/
function getFilter(){
	global $cid;

	$out = '
    	<script>
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
    if($p != 'my'){
    		return $out;
    } else {
    	return '';
    }
}

/*
  
  Return a list of bookmarks to the caller in a displayable HTML format.

*/
function getBookmarks(){
	global $DB, $CFG, $cid, $p, $USER;
	
	$outputHTML = '';
	$offset = '';

	$jump = optional_param('jump', '', PARAM_INT);
	if(!empty($jump)){
		$offsetNum = ($jump -1) * 10;
		$offset = ' LIMIT 10 OFFSET ' . $offsetNum;
	} 

	// Buld up a page offset if set

	$totalRecordCounter = 0;

	if($p == 'my'){ // Show only the current users bookmarks
			$result = $DB->get_records_sql('SELECT * FROM {block_socialbookmark} 
											WHERE courseid = ? AND userid = ?' . $offset, array($cid,$USER->id)); 

			// Count the records
			$totalRecordCounter = $DB->count_records_sql('SELECT count(id) FROM {block_socialbookmark} 
											WHERE courseid = ? AND userid = ?' . $offset, array($cid,$USER->id)); 



	}
	else if($p == 'all'){ // Show all bookmarks for the current course
		// Show all the records
		$result = $DB->get_records_sql('SELECT * FROM {block_socialbookmark} 
										WHERE courseid = ?' . $offset, array($cid));

		// Count the records
		$totalRecordCounter = $DB->count_records_sql('SELECT count(id) FROM {block_socialbookmark} 
										WHERE courseid = ?', array($cid)); 

	}
	else if($p == 'user'){ // Show all bookmarks for the current course
		
		// Show all the records
		$uid = required_param('uid', PARAM_INT);
		$result = $DB->get_records_sql('SELECT * FROM {block_socialbookmark} 
										WHERE userid = ?' . $offset, array($uid));

		// Count the records
		$totalRecordCounter = $DB->count_records_sql('SELECT count(id) FROM {block_socialbookmark} 
										WHERE userid = ?', array($uid)); 


		$outputHTML .= '<div style="height:60px; width:60%; background:#f3f3f3">
							<b>'.  get_string('filterby','block_socialbookmark'). ':</b> '.  get_string('user','block_socialbookmark'). ' <a href="manage.php?id='.$cid.'">[x]</a> <br>  
							<input type="button" value="Remove All Filters" onclick="window.location.href=\'manage.php?id='.$cid.'\';"> 
						</div>	';
	
	}
	else if($p == 'rating'){

	   $result = $DB->get_records_sql('SELECT * FROM {block_socialbookmark} 
	   								   WHERE courseid = ?' . $offset, array($cid));


		// Count the records
		$totalRecordCounter = $DB->count_records_sql('SELECT count(id) FROM {block_socialbookmark} 
	   								   WHERE courseid = ?', array($cid)); 



	   $r = required_param('r', PARAM_INT);
	   $outputHTML .= '<div style="height:60px; width:60%; background:#f3f3f3">
	   						<b>'.  get_string('filterby','block_socialbookmark'). ':</b> '.  get_string('rating','block_socialbookmark'). ' - '.$r.' <a href="manage.php?id='.$cid.'">[x]</a><br>
	   						<input type="button" value="Remove All Filters" onclick="window.location.href=\'manage.php?id='.$cid.'\';"> 
	   				   </div>	';
	

	}
	else if($p == 'tag'){ // Filter by: course & tag
			
			$tagId = required_param('tagid', PARAM_INT);
			$result = $DB->get_records_sql("SELECT * FROM {block_socialbookmark} 
											WHERE `tagid` LIKE '%{$tagId}%' " . $offset, array($cid));

			// Count the records
			$totalRecordCounter = $DB->count_records_sql("SELECT count(id) FROM {block_socialbookmark} 
											WHERE `tagid` LIKE '%{$tagId}%' ", array($cid)); 




 			$outputHTML .= '<div style="height:60px; width:60%; background:#f3f3f3">
 								<b>'.  get_string('filterby','block_socialbookmark'). ':</b> '.  get_string('tag','block_socialbookmark'). '' . tagIdToName($tagId) . ' <a href="manage.php?id='.$cid.'">[x]</a><br>
 								<input type="button" value="Remove All Filters" onclick="window.location.href=\'manage.php?id='.$cid.'\';"> 
 							</div>	';
	}

	//echo $totalRecordCounter;
	$numberOfRecords = 0;

	  foreach($result as $rec){

	  		$printRec = false;

	  		if(isset($_GET['r'])){
		  		$r = required_param('r', PARAM_INT);

		  		$score = getScoreNum($rec->id);
		  	 	
				//echo 'record score' . $score . ' ' . 'selected' . $r;
		  		
	               if($score == $r){
	               	   $printRec = true;
	               }
	            
	        }else {
	        	$printRec = true;
	        }



            if($printRec == true){
					$numberOfRecords++;
					
					// Get the raw record count, so we can output how many users have created
					// rating records
					$sql2 = 'select count(rating) from ' . $CFG->prefix .'block_socialbookmark_ratings WHERE bm_id =' . $rec->id;
					$dbRecordCount = $DB->get_field_sql($sql2,null , $strictness=IGNORE_MISSING);
					$recCount =  '(' . intval($dbRecordCount) . ' ratings)';
			

		            $outputHTML .= '<div id="singlebookmark" style="overflow:hidden; padding:10px; border-bottom:1px solid #f3f3f3;">';
					$outputHTML .= '<div style="float:left; width:60%; ">';

			  		$outputHTML .=' <table width="95%" border="0">';
			  		$outputHTML .= '	<tr><td style="width:20%"><span style="font-size: 14px; font-weight:bold">'.  get_string('title','block_socialbookmark'). ': </span></td>
			  		<td><a href="'.validateLink($rec->link).'" target="_blank"> ' . $rec->titletext . ' - '.getStemmedLink($rec->link).'</a></td></tr>';

			  		 // Print out the user who submitted the link
			  		 $subBy = $DB->get_record('user', array('id'=> $rec->userid), $fields='*', $strictness=IGNORE_MISSING); 

					 $submittedByString = $subBy->firstname .' ' . $subBy->lastname;
			  	
			  		 // Print the date and time submitted
			  		 $dateAdded = $rec->dateadded;
			  		 $dt = new DateTime("@$dateAdded");

			  	 
			  		 $outputHTML .= '	<tr><td style="width:20%; vertical-align: top;text-align: left;"><span style="font-size: 11px; font-weight:bold">'.get_string('description', 'block_socialbookmark').': </span></td>
			  		 <td><span style="font-size: 11px;">' . checkandTrimDesc($rec->desctext) . '</span></td></tr>';
			  		 //$outputHTML .= '	<tr><td style="width:20%"><b>URL: </b> </td><td><a href="'.$rec->link.'">' . $rec->link . '</a></td></tr>';
			  		 
			  		 $outputHTML .= '	<tr><td style="width:20%"><span style="font-size: 11px; font-weight:bold">'.get_string('tag', 'block_socialbookmark').': </span> </td>
			  		 <td><span style="font-size: 11px;"> '. getTags($rec->tagid) . '</span></td></tr>';
			  		 //$outputHTML .= '	<tr><td style="width:20%"><b>Status: </b> </td><td>' . getStatusText($rec->status) . '</td></tr>';
			  		 $outputHTML .= '	</table>';
			  		 $outputHTML .= '</div>';
	  		 

			  		 // Right div
			  		 $outputHTML .='<div style="overflow: hidden;"> ';
			  		 $outputHTML .= '	<table width="350px" border="0">';
			  		 $outputHTML .= '	<tr><td style="width:150px"><span style="font-size: 11px; font-weight:bold">'.get_string('rating', 'block_socialbookmark').':</span> </td><td ><span style="font-size: 11px;"> ' . getRatingScore($rec->id) . ' ' .$recCount.'</span></td></tr>';
			  		 $outputHTML .= '	<tr><td style="width:150px"><span style="font-size: 11px; font-weight:bold">'.get_string('dateadded', 'block_socialbookmark').':</span> </td><td><span style="font-size: 11px;"> ' . $dt->format('Y-m-d H:i:s'). '</span></td></tr>';
			  		 $outputHTML .= '	<tr><td style="width:150px"><span style="font-size: 11px; font-weight:bold">'.get_string('submittedby', 'block_socialbookmark').':</span> </td><td> <span style="font-size: 11px;"><a href="manage.php?id='.$cid.'&p=user&uid='.$rec->userid.'">' . $submittedByString. '</span></a></td></tr>';
			  		// If the current user created this record, then allow them to delete it.
			  		 if($rec->userid == $USER->id){
			  		 	$outputHTML .= '	<tr><td style="width:150px"><span style="font-size: 11px; font-weight:bold"></span> </td><td> <span style="font-size: 11px;"><a href="manage.php?id='.$cid.'&d='.$rec->id.'">['.get_string('delete', 'block_socialbookmark').']</span></a></td></tr>';
					 }
					 $outputHTML .=' 	</table>';
					 $outputHTML .= '</div>';


			  		 $outputHTML .= '</div> ';
			}
	  		
		 }

		 
		 
		 // Calculate the page numbers for the
		 // end of the page based upon the amount
		 // of records that exist.
		 //
		 //echo 'num records' . $numberOfRecords;

	 	$p_sec = optional_param('p', '', PARAM_TEXT);    
		$p = '';
		if(!empty($p_sec)){
			$p = 'p=' . $p_sec . '&';

		}

		$outputHTML.= '<center>';
		if(isset($_GET['tp'])){
			$numberOfRecords = required_param('tp', PARAM_INT);
		}
		
		$url = 'manage.php?id=' . $cid .'&'.$p;
		$jumpCounter = $totalRecordCounter / 10;
		 	
		$outputHTML .= ' <a href="'.$url.'">[ 1 ]</a>';

		for($x = 2; $x < $jumpCounter+1; $x++){
 		
 			$url = 'manage.php?id=' . $cid;
 		

 		
 			$outputHTML .= ' <a href="'.$url.'&tp='.$totalRecordCounter.'&'.$p.'jump='.$x.'">[ '.$x.' ]</a>';
 		}
		 


		$outputHTML.= '</center>';
	return $outputHTML;

}


function checkandTrimDesc($originalDesc){


		$cleanedString = '';
		if(strlen($originalDesc) > 300){

			$cleanedString = substr($originalDesc, 0, 300) . '...';

		} else {
			$cleanedString = $originalDesc;
		}
	
	
	
	
	 return $cleanedString;

}


/*

Cut the trailing half of a url off, leaving on the
base url.

*/
function getStemmedLink($link){


	return parse_url(validateLink($link), PHP_URL_HOST);
}
/*

 Return a list of tags

*/
function getTags($ids){
	$fullTagString = '';

	$tags = explode(' ', $ids);

	$tagCounter = 0;

	foreach($tags as $tag){
		if($tagCounter <10){
			if(!empty($tag)){
				$fullTagString .=  tagIdToName($tag) . ', ';
			}
		}
		$tagCounter++;
	}

	return rtrim($fullTagString, ", ");

}




function getScoreNum($id){

		global $DB, $CFG;
		$sql = 'select avg(rating) from ' . $CFG->prefix .'block_socialbookmark_ratings WHERE bm_id =' . $id;
		$res = $DB->get_field_sql($sql,null , $strictness=IGNORE_MISSING);

		$score = intval($res);

	return $score;
}
/*

Return the average rating score for an indivdual bookmark record
*/


function getRatingScore($id){


	global $DB, $CFG;

		$sql = 'select avg(rating) from ' . $CFG->prefix .'block_socialbookmark_ratings WHERE bm_id =' . $id;
		$res = $DB->get_field_sql($sql,null , $strictness=IGNORE_MISSING);

		$score = intval($res);
		
		$r1 = '';
		$r2 = '';
		$r3 = '';
		$r4 = '';
		$r5 = '';
		
		if($score == '1'){
			$r1 = 'checked="true" ';
		}
		else if($score == '2'){
			$r2 = 'checked="true"';
		}
		else if($score == '3'){
			$r3 = 'checked="true"';
		}
			else if($score == '4'){
			$r4 = 'checked="true"';
		}
			else if($score == '5'){
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
		    var selectedValue = value;
		    var bkId = this.name;
		    var user = userId;

					    $.post( "../ajax.php", { type: "saverating", bk: bkId, userid: user, selectedrating: selectedValue })
						  .done(function( data ) {
						    //alert( "Data Loaded: " + data );
						  });

		  }
		});
</script>


<?php
if(isset($_GET['r'])){
	$r = $_GET['r'] -1;
	echo "<script> document.getElementById('ratingSelected').selectedIndex = $r;</script>";
}

?>


