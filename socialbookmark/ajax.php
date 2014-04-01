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
$type = required_param('type', PARAM_TEXT);

if($type == 'saverating'){

	saveRatingRecord();
}


/*
Add a user submitted rating to the database. If a record already exists from that
user for that bookmark, then update the record.
*/
function saveRatingRecord(){

	global $DB;


	$bkId = required_param('bk', PARAM_TEXT);
	$userId = required_param('userid', PARAM_TEXT);
	$ratingValue = required_param('selectedrating', PARAM_TEXT);
	//Save the rating record to the database.

	$record = new stdClass();
  $record->bm_id = $bkId;
  $record->userid = $userId;
  $record->rating = $ratingValue;

  $exists = $DB->record_exists('block_socialbookmark_ratings', array('userid'=>$userId, 'bm_id'=>$bkId)); 
  //echo $exists;
  if($exists ==1 ){
      $recId = $DB->get_field('block_socialbookmark_ratings', 'id', 
                              array('userid'=>$userId, 'bm_id'=>$bkId), $strictness=IGNORE_MULTIPLE);

      //echo 'rec Id' . $recId;
      $record = new stdClass();
      $record->id = $recId;
      $record->bm_id = $bkId;
      $record->userid = $userId;
      $record->rating = $ratingValue;
      $DB->update_record('block_socialbookmark_ratings', $record, $bulk=false);

  } else {
      //echo 'record inserted';
  	   $DB->insert_record('block_socialbookmark_ratings', $record, $returnid=true, $bulk=false);
  }

}

?>