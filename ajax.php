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

$type = optional_param('type','', PARAM_TEXT);

if($type == 'saverating'){
    save_rating_record();
}



/**
* Add a user submitted rating to the database. If a record already exists from that
* user for that bookmark, then update the record.
*/
function save_rating_record() {

	global $DB;


	$bkid = optional_param('bk','', PARAM_TEXT);
	$userid = optional_param('userid','', PARAM_TEXT);
	$ratingvalue = optional_param('selectedrating','', PARAM_TEXT);

	//Save the rating record to the database.
  $record = new stdClass();
  $record->bm_id = $bkid;
  $record->userid = $userid;
  $record->rating = $ratingvalue;

  $exists = $DB->record_exists('block_socialbookmark_ratings', array('userid'=>$userid, 'bm_id'=>$bkid)); 
  
  if ($exists ==1 ) {
      $recid = $DB->get_field('block_socialbookmark_ratings', 'id', 
                              array('userid'=>$userid, 'bm_id'=>$bkid), $strictness=IGNORE_MULTIPLE);
      $record = new stdClass();
      $record->id = $recid;
      $record->bm_id = $bkid;
      $record->userid = $userid;
      $record->rating = $ratingvalue;
      $DB->update_record('block_socialbookmark_ratings', $record, $bulk=false);

  } else {
  	   $DB->insert_record('block_socialbookmark_ratings', $record, $returnid=true, $bulk=false);
  }

}

