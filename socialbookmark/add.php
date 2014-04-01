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
require_once("../../config.php");
global $CFG, $DB;
$formPath = "$CFG->libdir/formslib.php";
require_once($formPath);
$cid = required_param('cid', PARAM_INT);

/** Navigation Bar **/
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('Course Page', new moodle_url('../../course/view.php?id=' . $cid));
$PAGE->navbar->add(get_string('socialbookmark', 'block_socialbookmark'), new moodle_url('admin/manage.php?id=' . $cid . '&p=my'));
$PAGE->navbar->add(get_string('addnew', 'block_socialbookmark'));
$PAGE->set_url('/blocks/cmanager/manage.php');
$PAGE->set_context(get_system_context());


?>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
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

class add_form extends moodleform {
 
    function definition() {
        global $CFG;
        global $currentSess;
		global $inEditingMode, $DB;
		global $cid;

       
		$mform =& $this->_form; 
		$mform->addElement('header', 'mainheader','<span style="font-size:18px">'.  get_string('addnew','block_socialbookmark'). '</span>');

      	// Page description text
		$mform->addElement('html', '<p></p>
			<div id="titlebar">
			<a href="admin/manage.php?id='.$cid.'&p=my"><img style="height:16px; width:16px" src="img/folder.png"> '.  get_string('mybookmarks','block_socialbookmark'). '</a> 
			<a href="admin/manage.php?id='.$cid.'&p=all"><img style="height:16px; width:16px" src="img/view.png"> '.  get_string('viewall','block_socialbookmark'). '</a>
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

		


		

    // Add the tags for classification
    $selectQuery = "courseid = '$cid'";
    $options = array();
    $attributes = $DB->get_recordset_select('block_socialbookmark_tags', $select=$selectQuery);
        

    
$interactiveOut = '';
$interactiveOut .=' <script>';
$interactiveOut .='		  $(function() { ';
$interactiveOut .='			    var availableTags = [ ';
$interactiveOut .='			      "Interesting", ';
				
		 foreach($attributes as $item){
				  				$vid = $item->id;
				  	         $value = $item->tagname;
							 if($value != ''){
								$interactiveOut .='			      "'.$value.'", ';
					}
				}
				


$interactiveOut .='			      "" ';
$interactiveOut .='			    ]; ';
$interactiveOut .='		
    function split( val ) {
      return val.split( /,\s*/ );
    }
    function extractLast( term ) {
      return split( term ).pop();
    }';


 $interactiveOut .='		
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
  </script>


';


											 
		

		$mform->addElement('html', $interactiveOut);
    $tag_attributes=array('size'=>'50');
		$mform->addElement('text', 'tags', 'Tag: ', $tag_attributes);


// Add the tags for classification
$selectQuery = "courseid = '$cid'";
$options = array();
$attributes = $DB->get_recordset_select('block_socialbookmark_tags', $select=$selectQuery);
    


    // Allow the user to select from tags, that have just been placed
    // in front of them as a list.
     $tagSelectorOutput = '';
     foreach($attributes as $item){
                  $vid = $item->id;
                     $value = $item->tagname;
               if($value != ''){
                $tagSelectorOutput .='           <a href="#" onclick="addTag(\' '.$value.'\')">'.$value.'</a>, ';
          }
        }
    $mform->addElement('html', '<div style="padding-left:100px; width:500px; left:200px"> <b>'.  get_string('tagsuggestions','block_socialbookmark'). '</b>'. $tagSelectorOutput .'</div>' );

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


   $mform = new add_form();//name of the form you defined in file above.



   echo $OUTPUT->header();
  
   $mform->focus();
   $mform->set_data($mform);
   $mform->display();
   echo $OUTPUT->footer();




  if($_POST){

  	$record = new stdClass();
  	$record->userid = $USER->id;
  	$record->courseid = $cid;
  	$record->tagid = getTagIdsFromText();
  	$record->titletext = required_param('title', PARAM_TEXT);
  	$record->desctext = required_param('desc', PARAM_TEXT);
  	$record->link = $_POST['url'];
  	$record->status = 0;
  	$record->dateadded = time();


  	$DB->insert_record('block_socialbookmark', $record, $returnid=true, $bulk=false);
  	echo '<script>window.location="admin/manage.php?id='.$cid.'"; </script>';
  }


/*

Given a tag name, convert the name into the associated
id for the tag.

*/
function getTagIdsFromText(){

   		global $DB, $cid;
      $tags = required_param('tags', PARAM_TEXT);
		$tagText = trim($tags);



   		$tagIds = ''; // Id number for each tag stored here.

		$singleTags = explode(', ', $tagText);


		foreach($singleTags as $tag){

				//echo 'tag on ----- ' . $tag . '<br>';
			 $tagTrim = str_replace(array('.', ','), '' , $tag);
				$res = $DB->get_records_sql('select * from {block_socialbookmark_tags} WHERE tagname = ? AND courseid = ?',array($tagTrim, $cid));

        if(empty($res)){
         
              $tagIds .= ' ' . addNewTag($cid, $tagTrim);

        } else {
    				foreach($res as $item){
    				//	echo 'current item' . $item->id;

    					$tagIds .= ' ' . $item->id;
    				}

  				}


  		}
  	//	echo 'returning ' . $tagIds;
  		
  		return $tagIds;




   }


/*
Add a new tag to the current course and return the
id for the tag.
*/
function addNewTag($cid, $tagText){

global $DB, $cid;

    $record = new stdClass();
    $record->courseid = $cid;
    $record->tagname =$tagText;

    $lastId = $DB->insert_record('block_socialbookmark_tags', $record, $returnid=true, $bulk=false);


    return $lastId;


}
?>

<script>


//
// Add a new tag dynamically to the text field 
// that holds the tags
function addTag(tag){
  var field = document.getElementById('id_tags');
  
    if(field.value.length > 0){
      field.value += tag + ',';
    } else {
        field.value += tag + ',';
    }
}
</script>