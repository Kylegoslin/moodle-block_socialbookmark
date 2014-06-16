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
defined('MOODLE_INTERNAL') || die();
require_login();
/** Convert a stored tag name into the corrent displayable name.
* @param int $tid tag id
*/
function block_socialbookmark_tag_id_to_name($tid) {

    global $DB;

    $currentrecord =  $DB->get_record('block_socialbookmark_tags', array('id'=>$tid));
	
	if (!empty($currentrecord)) {	
        return $currentrecord->tagname;
    } 
    return null;
}


/** Generate a tag cloud based on the tags that are currently
*in use on this course.
* @param int $cid course id
* @param int $taglimit limit on number of tags
* @param text $urlroot root url
*/
function block_socialbookmark_get_cloud($cid, $taglimit, $urlroot) {

    global $DB;
    $html = '

    <div style="padding-top: 10px;
    padding-bottom: 10px;
    padding-left: 10px;
    border:0px solid;
	">

	
    <p></p>';

	$result = $DB->get_records_sql('SELECT * 
		                            FROM {block_socialbookmark} 
	                                WHERE courseid = ?', array($cid));

    $countarray = array();

    foreach ($result as $rec) {
        // Break down record by spaces
	    $values = explode(' ', block_socialbookmark_get_cloud_tags($cid));


        foreach ($values as $singleid) {
            if (!empty($singleid)) {
                if (array_key_exists($singleid, $countarray)) {
                    $newvalue = $countarray[$singleid] + 1;
                    $countarray[$singleid] = $newvalue;

                } else {
                        $countarray[$singleid] = 1;
                       }
		   }
		}
	}


// Tag cloud counters
$max = 0;
$min = 10000;
$jumpSize = 0;



foreach ($countarray as $key => $value) {
    if ($value > $max) {
        $max = $value;
    }
    if ($value < $min) {
        $min = $value;
    }

}

$jumpsize = ($max - $min) / 5;


// Count through the array value pairs
// to decide their font sizes
$limitcounter = 0;
foreach ($countarray as $key => $value) {
    if ($limitcounter < $taglimit) {
	    if ($value >= $min && $value <= ($min + $jumpsize)) {
            $html .= '<span style="font-size:10px"><a href="'.$urlroot.'manage.php?id='.$cid.'&tagid='.$key.'&p=tag">'. 
                      block_socialbookmark_tag_id_to_name($key) . '</a></span> '; 
        }
        else if ($value > ($min+$jumpsize) && $value <= ($min + ($jumpsize*2))) {
	        $html .= '<span style="font-size:12px"><a href="'.$urlroot.'manage.php?id='.$cid.'&tagid='.$key.'&p=tag">'. 
                             block_socialbookmark_tag_id_to_name($key) . '</a></span> '; 
        } 
        else if ($value > ($min+($jumpsize*2)) && $value <= ($min + ($jumpsize*3))) {
            $html .= '<span style="font-size:15px"><a href="'.$urlroot.'manage.php?id='.$cid.'&tagid='.$key.'&p=tag">' .
                             block_socialbookmark_tag_id_to_name($key) . '</a></span> '; 
		} 
		else if ($value > ($min+($jumpsize*3)) && $value <= ($min + ($jumpsize*4))) {
			$html .= '<span style="font-size:17px"><a href="'.$urlroot.'manage.php?id='.$cid.'&tagid='.$key.'&p=tag">' .
                             block_socialbookmark_tag_id_to_name($key) . '</a></span> '; 
		}
		else if ($value > ($min+($jumpsize*4)) && $value <= ($min + ($jumpsize*5))) {
		    $html .= '<span style="font-size:20px"><a href="'.$urlroot.'manage.php?id='.$cid.'&tagid='.$key.'&p=tag">' . 
                             block_socialbookmark_tag_id_to_name($key) . '</a></span> '; 
        } 
    }
        $limitcounter++;
}

return $html . '</div>';

}

/** 
* Get a list of tags tags for a course from a course id.
* @param $cid course id
*/
function block_socialbookmark_get_course_tags($cid) {

    global $DB, $CFG;
    $bookmarkassc = $DB->get_records('block_socialbookmark_tags', array('courseid'=>$cid)); 
    $ids = '';
    foreach ($bookmarkassc as $rec){

        $ids .= ' ' . $rec->id;
    }


    return $ids;
}


/** 
* Get a list of tags for a cloud
* @param $cid course id
*/
function block_socialbookmark_get_cloud_tags($cid) {

    global $DB, $CFG;
    $bookmarkassc = $DB->get_records('block_socialbookmark_assc', array('cid'=>$cid)); 
    $ids = '';
    foreach ($bookmarkassc as $rec){
        $ids .= ' ' . $rec->tagid;
    }

    return $ids;

}

/**
*Check to see if the link starts with http
*if it doesn't, then add it in
* @param text $link link to validate
*/
function block_socialbookmark_validate_link($link) {

    if (substr( $link, 0, 4 ) === "http") {
        return $link;
    } else {
        $link = 'http://' . $link;
        return $link;
	}
}
