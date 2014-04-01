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
global $CFG, $DB;
$formPath = "$CFG->libdir/formslib.php";
require_once($formPath);
/** Navigation Bar **/
$cid = optional_param('id', 0, PARAM_INT);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('Course Page', new moodle_url('../../../course/view.php?id=' . $cid));

$PAGE->navbar->add(get_string('socialbookmark', 'block_socialbookmark'), new moodle_url('manage.php?id=' . $cid . '&p=my'));
$PAGE->navbar->add(get_string('settings', 'block_socialbookmark'));


$PAGE->set_url('/blocks/cmanager/manage.php');
$PAGE->set_context(get_system_context());



$d = optional_param('d', '', PARAM_TEXT);


// If the delete record flag is set
if(isset($d)){

	// Delete the record
	$DB->delete_records('block_socialbookmark_tags', array('id'=>$d));
	//echo 'deleted';
	
	
}

?>
<style>

#maindiv {
	padding-top: 10px;
	padding-bottom: 10px;
	padding-left: 10px;
	border:1px solid;
	width: 700px;

}

#titlebar {
	font-size: 16px;
}


</style>
<?php

class add_form extends moodleform {
 
    function definition() {
        global $CFG;
        global $currentSess;
		global $inEditingMode, $DB;
		global $cid;

       
		$mform =& $this->_form; 
		$mform->addElement('header', 'mainheader','<span style="font-size:18px">'.  get_string('settings','block_socialbookmark'). '</span>');

      	// Page description text
		$mform->addElement('html', '<p></p>
			<div id="titlebar">
		    </div>
			<div id="maindiv">

			'.get_string('tagspagedesc', 'block_socialbookmark').'
					<h3> '.  get_string('tagsforthiscourse','block_socialbookmark'). '</h3>
					<p></p>
    				'.getTags().'

				 ');

		$mform->addElement('text', 'tagtitle', get_string('tagtitle','block_socialbookmark'), null);
		$mform->setType('tagtitle', PARAM_TEXT);
		$mform->addElement('hidden', 'cid', $cid);
		$mform->setType('cid', PARAM_INT);
		$buttonarray=array();
		$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('addenteredtag'));
		$buttonarray[] = &$mform->createElement('cancel');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');

		$mform->addElement('html', '</div>');

	}
}


   $mform = new add_form();//name of the form you defined in file above.


/*

 Get the collection of tags associated with this course.

*/
function getTags(){

	global $DB;
	global $cid;
	$result = $DB->get_records_sql('SELECT * FROM {block_socialbookmark_tags} WHERE courseid = ?', array($cid));
 
	$outputTags = '';
	
	  foreach($result as $rec){
	    $outputTags .= $rec->tagname .' [ <a href="settings.php?id='.$cid.'&d='.$rec->id.'">'.  get_string('delete','block_socialbookmark'). ' </a>]<br>';
	 }

  	return $outputTags;
  }

   echo $OUTPUT->header();
  
   $mform->focus();
   $mform->set_data($mform);
   $mform->display();
   echo $OUTPUT->footer();




  if($_POST){

  	$record = new stdClass();
  

	$record->courseid = $_POST['cid'];
  	$record->tagname =$_POST['tagtitle'];

  	$DB->insert_record('block_socialbookmark_tags', $record, $returnid=true, $bulk=false);


  	$cid =$_POST['cid'];

  	echo '<script>window.location="settings.php?id='.$cid.'"; </script>';
  	die;
  	
  }

 
?>