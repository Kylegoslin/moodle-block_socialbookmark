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
 * SOCIAL BOOKMARK BLOCK
  *
 * @package    block_socialbookmark
 * @copyright  2014 Kyle Goslin, Daniel McSweeney
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../../config.php");
global $CFG, $DB;
require_login();
$formpath = "$CFG->libdir/formslib.php";
require_once($formpath);
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
$cid = optional_param('id',0, PARAM_INT);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add('Course Page', new moodle_url('../../../course/view.php?id=' . $cid));
$PAGE->navbar->add(get_string('socialbookmark', 'block_socialbookmark'), new moodle_url('manage.php?id=' . $cid . '&p=my'));
$PAGE->navbar->add(get_string('settings', 'block_socialbookmark'));
$PAGE->set_url('/blocks/socialbookmark/admin/settings.php');
$PAGE->set_context(context_system::instance());
echo $OUTPUT->header();
$d = optional_param('d',0, PARAM_INT);


// If the delete record flag is set
if (!empty($d)) {
	if ($d != 0) {
	// Delete the record
	$DB->delete_records('block_socialbookmark_tags', array('id'=>$d));
    }
}





/**
* Bookmark management interface
*
* @package block_socialbookmark
* @copyright 2014 Kyle Goslin, Daniel McSweeney
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class block_socialbookmark_add_form extends moodleform {
 
    /** Main settings definition */
    function definition() {
      
        global $currentsess;
		global $ineditingmode, $DB;
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
    		'.block_socialbookmark_get_tags());
		
		$mform->addElement('text', 'tagtitle', get_string('tagtitle','block_socialbookmark'), null);
		$mform->setType('tagtitle', PARAM_TEXT);
		$mform->addElement('hidden', 'cid', $cid);
		$mform->setType('cid', PARAM_INT);
		$buttonarray=array();
		$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('addenteredtag', 'block_socialbookmark'));
		$buttonarray[] = &$mform->createElement('cancel');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');

		$mform->addElement('html', '</div>');

	}
}


$mform = new block_socialbookmark_add_form();//name of the form you defined in file above.


if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {

  	$record = new stdClass();
    $record->courseid = $fromform->cid;   
  	$record->tagname =	$fromform->tagtitle;

  	$DB->insert_record('block_socialbookmark_tags', $record, $returnid=true, $bulk=false);
  	
  	echo '<script>window.location="settings.php?id='.$fromform->cid.'"; </script>';
  	die;

} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.
 
  //Set default data (if any)
  $mform->set_data($mform);
  //displays the form
  $mform->display();
}


/** 
* Get the collection of tags associated with this course.
 */
function block_socialbookmark_get_tags() {

    global $DB;
    global $cid;
    $result = $DB->get_records_sql('SELECT * 
    	                            FROM {block_socialbookmark_tags}
    	                            WHERE courseid = ?', array($cid));
 
	$outputtags = '';
	
	foreach($result as $rec){
	    $outputtags .= $rec->tagname .' [ <a href="settings.php?id='.$cid.'&d='.$rec->id.'">'.  
	                   get_string('delete','block_socialbookmark'). ' </a>]<br>';
	 }

  	return $outputtags;
}



$mform->focus();

echo $OUTPUT->footer();



 
