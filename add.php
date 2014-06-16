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
require_once("../../config.php");
global $CFG, $DB;
require_login();
$formPath = "$CFG->libdir/formslib.php";
require_once($formPath);
$cid = required_param('cid', PARAM_INT);

// Navigation Bar
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('Course Page', new moodle_url('../../course/view.php?id=' . $cid));
$PAGE->navbar->add(get_string('socialbookmark', 'block_socialbookmark'), new moodle_url('admin/manage.php?id=' . $cid . '&p=my'));
$PAGE->navbar->add(get_string('addnew', 'block_socialbookmark'));
$PAGE->set_url('/blocks/socialbookmark/add.php');

$context = context_course::instance($cid);
$PAGE->set_context($context);

if(has_capability('block/socialbookmark:addbookmark',$context)){
} else {
  error('Sorry you cant add a bookmark');
}?>
<link rel="stylesheet" href="jquery/jquery-ui.css">
<script src="star/jquery.js"></script>
<script src="jquery/jquery-ui.js"></script>
<style>

#maindiv {
  padding-top: 10px;
  padding-bottom: 10px;
  padding-left: 10px;
  border:0px solid;

  width: 900px;
}

#titlebar {
  font-size: 16px;
}
</style>
<?php
/**
* Interface for adding a new bookmark to the block.
* @package block_socialbookmark
* @copyright 2014 Kyle Goslin, Daniel McSweeney
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class block_socialbookmark_add_form extends moodleform {
 
     /**
     * Class Def creating the user interface for creating
     * a new bookmark
     */
    function definition() {
        global $CFG;
        global $currentsess;
		    global $ineditingmode, $DB;
        global $cid;
        $mform =& $this->_form; 
		    $mform->addElement('header', 'mainheader','<span style="font-size:18px">'.  
                           get_string('addnew','block_socialbookmark'). '</span>');

      	// Page description text
		    $mform->addElement('html', '<p></p>
			  <div id="titlebar">
        <a href="admin/manage.php?id='.$cid.'&p=my"><img style="height:16px; width:16px" src="img/folder.png"> '.  
        get_string('mybookmarks','block_socialbookmark'). '</a> 
        <a href="admin/manage.php?id='.$cid.'&p=all"><img style="height:16px; width:16px" src="img/view.png"> '.  
        get_string('viewall','block_socialbookmark'). '</a>
        <b> <img style="height:16px; width:16px" src="img/fave.png"> <a href="../add.php?cid='.$cid.'">Add</a></b>
			
        </div>
        <div id="maindiv">
				 ');
        $title_attributes=array('size'=>'50');
        $mform->addElement('text', 'title', get_string('title','block_socialbookmark'), $title_attributes);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('pleaseenteratitle','block_socialbookmark'), 'required', null, 'client');
        $mform->addElement('textarea', 'desc', get_string('description','block_socialbookmark'), 'wrap="virtual" rows="10" cols="100"');
        $mform->addRule('desc', get_string('pleaseenteradescription','block_socialbookmark'), 'required', null, 'client');
        $mform->setType('desc', PARAM_TEXT);
        $url_attributes=array('size'=>'50');
        $mform->addElement('text', 'url', get_string('url','block_socialbookmark'), $url_attributes);
        $mform->addRule('url', get_string('pleaseenteraurl','block_socialbookmark'), 'required', null, 'client');

        $attributes = $DB->get_records('block_socialbookmark_tags',array('courseid'=>$cid));


        $interactiveout = '';
        $interactiveout .=' <script>';
        $interactiveout .='		  $(function() { ';
        $interactiveout .='			    var availableTags = [ ';
        $interactiveout .='			      "Interesting", ';
				
        foreach ($attributes as $item) {
				  				$vid = $item->id;
				  	      $value = $item->tagname;
							 
                  if ($value != '') {
								      $interactiveout .='"'.$value.'", ';
					        }
				}
				


       $interactiveout .='			      "" ';
       $interactiveout .='			    ]; ';
       $interactiveout .='		
       function split( val ) {
        return val.split( /,\s*/ );
       }
       function extractLast( term ) {
         return split( term ).pop();
       }


       $( "#id_tags" )
 
      .bind( "keydown", function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB &&
            $( this ).data( "ui-autocomplete" ).menu.active ) {
          event.preventDefault();
        }
      })
      .autocomplete({
        minLength: 0,
        source: function( request, response ) {
          // delegate back to autocomplete, but extract the last term
          response( $.ui.autocomplete.filter(
            availableTags, extractLast( request.term ) ) );
        },
        focus: function() {
          // prevent value inserted on focus
          return false;
        },
        select: function( event, ui ) {
          var terms = split( this.value );
          // remove the current input
          terms.pop();
          // add the selected item
          terms.push( ui.item.value );
          // add placeholder to get the comma-and-space at the end
          terms.push( "" );
          this.value = terms.join( ", " );
          return false;
        }
       });
     });
    </script>';


    $mform->addElement('html', $interactiveout);
    $tag_attributes=array('size'=>'50');
    $mform->addElement('text', 'tags', 'Tag ', $tag_attributes);
    $attributes = $DB->get_records('block_socialbookmark_tags',array('courseid'=>$cid));

    // Allow the user to select from tags, that have just been placed
    // in front of them as a list.
    $tagselectoroutput = '';
    foreach ($attributes as $item) {
        $vid = $item->id;
        $value = $item->tagname;
        if ($value != '') {
            $tagselectoroutput .='<a href="#" onclick="add_tag(\' '.$value.'\')">'.$value.'</a>, ';
        }
    }
    $mform->addElement('html', '<div style="padding-left:100px; width:500px; left:200px"> <b>'.  
                       get_string('tagsuggestions','block_socialbookmark'). '</b>: '. $tagselectoroutput .'</div>' );

    $mform->setType('tags', PARAM_TEXT);
    $mform->addRule('tags', get_string('addatleastonetag','block_socialbookmark'), 'required', null, 'client');

		
    // Get the collection of tags from the database
		$mform->setType('url', PARAM_RAW);
		$mform->addElement('hidden', 'cid', $cid);
		$mform->setType('cid', PARAM_INT);
		$buttonarray=array();
		$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] = &$mform->createElement('cancel');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');

		$mform->addElement('html', '</div>');

	}
}


$mform = new block_socialbookmark_add_form();//name of the form you defined in file above.
echo $OUTPUT->header();  

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {


    $record = new stdClass();
    $record->userid = $USER->id;
    $record->courseid = $cid;
    $record->titletext = $fromform->title;
    $record->desctext = $fromform->desc;
    $record->link = $fromform->url;
    $record->status = 0;
    $record->dateadded = time();

    $bkid = $DB->insert_record('block_socialbookmark', $record, $returnid=true, $bulk=false);


    // Add tag ids into the tag_assc table
    $tags = explode(' ', block_socialbookmark_get_tag_ids_from_text($fromform->tags));

    foreach ($tags as $tag) {
        if (!empty($tag)) {
            $tagassc = new stdClass();
            $tagassc->bkid = $bkid;
            $tagassc->cid = $cid;
            $tagassc->tagid = $tag;
            $DB->insert_record('block_socialbookmark_assc', $tagassc, $returnid=true, $bulk=false);

        }
    }

    echo '<script>window.location="admin/manage.php?id='.$cid.'"; </script>';

  //In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.
 
  //Set default data (if any)
  $mform->set_data($mform);
  //displays the form
  $mform->display();
}



echo $OUTPUT->footer();




/** Given a tag name, convert the name into the associated
* id for the tag.
*/
function block_socialbookmark_get_tag_ids_from_text($tags) {

    global $DB, $cid;
		$tagtext = trim($tags);
    $tagids = ''; // Id number for each tag stored here.
		$singletags = explode(', ', $tagtext);

    foreach ($singletags as $tag) {
        $tagtrim = str_replace(array('.', ','), '' , $tag);
				$res = $DB->get_records_sql('select * 
                                         from {block_socialbookmark_tags} 
                                         WHERE tagname = ? 
                                         AND courseid = ?',array($tagtrim, $cid));

        if (empty($res)) {
            $tagids .= ' ' . block_socialbookmark_add_new_tag($cid, $tagtrim);
        } else {
    		    foreach ($res as $item) {
    				    $tagids .= ' ' . $item->id;
    				}
        }


  		}

  		return $tagids;

   }


/**
* Add a new tag to the current course and return the
* id for the tag.
* @param int $cid course id
* @param int $tagtext text of the tag
*/
function block_socialbookmark_add_new_tag($cid, $tagtext) {

    global $DB, $cid;

    $record = new stdClass();
    $record->courseid = $cid;
    $record->tagname = $tagtext;

    $lastid = $DB->insert_record('block_socialbookmark_tags', $record, $returnid=true, $bulk=false);
    
    return $lastid;

}
?>

<script>
// Add a new tag dynamically to the text field 
// that holds the tags
function add_tag(tag){
  var field = document.getElementById('id_tags');
  if(field.value.length > 0){
      field.value += tag + ',';
    } else {
        field.value += tag + ',';
    }
}
</script>