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



/*

Convert a stored tag name into the corrent displayable name.
*/
function tagIdToName($tId){

		global $DB;

 		$currentRecord =  $DB->get_record('block_socialbookmark_tags', array('id'=>$tId));
		
		return $currentRecord->tagname;

}

/*
Generate a tag cloud based on the tags that are currently
in use on this course.
*/
function getCloud($cid, $tagLimit, $urlRoot){

	global $DB;

	$html = '

	<div style="padding-top: 10px;
	padding-bottom: 10px;
	padding-left: 10px;
	border:0px solid;
	">

	
	<p></p>
	
	';

	$result = $DB->get_records_sql('SELECT * FROM {block_socialbookmark} WHERE courseid = ?', array($cid));

	$countArray = array();

	foreach($result as $rec){
			

			// Break down record by spaces
			$values = explode(' ', $rec->tagid);


			foreach($values as $singleId){
			
				if(!empty($singleId)) {

					if(array_key_exists($singleId, $countArray)){
									$newValue = $countArray[$singleId] + 1;

									$countArray[$singleId] = $newValue;

							} else {

								$countArray[$singleId] = 1;
								}
			}
		}
	}


$max = 0;
$min = 10000;
$jumpSize = 0;



foreach ($countArray as $key => $value) {
 	//echo $key . ' ' . $value . ' <br>';
     if($value > $max){
     	$max = $value;
     }

     if($value < $min){
     	$min = $value;
     }

}

$jumpSize = ($max - $min) / 5;

/*
echo 'min ' . $min . '<br>';
echo 'max ' . $max . '<br>';
echo 'jumpsize' . $jumpSize . '<br>';
*/
    // Count through the array value pairs
	// to decide their font sizes
	$limitCounter = 0;
	foreach ($countArray as $key => $value) {
		//echo 'proccesing ' . $value . '<br>';

		//	echo ' if value' . $value . ' >= ' . $min . ' and ' .$value . ' <= ' .($min+$jumpSize);
		if($limitCounter < $tagLimit){
			if($value >= $min && $value <= ($min + $jumpSize)){
					$html .= '<span style="font-size:10px"><a href="'.$urlRoot.'manage.php?id='.$cid.'&tagid='.$key.'&p=tag">'. tagIdToName($key) . '</a></span> '; 
			}
			else if($value > ($min+$jumpSize) && $value <= ($min + ($jumpSize*2))){
					$html .= '<span style="font-size:12px"><a href="'.$urlRoot.'manage.php?id='.$cid.'&tagid='.$key.'&p=tag">'. tagIdToName($key) . '</a></span> '; 
			} 
			else if($value > ($min+($jumpSize*2)) && $value <= ($min + ($jumpSize*3))){
					$html .= '<span style="font-size:15px"><a href="'.$urlRoot.'manage.php?id='.$cid.'&tagid='.$key.'&p=tag">' .tagIdToName($key) . '</a></span> '; 
			} 
			else if($value > ($min+($jumpSize*3)) && $value <= ($min + ($jumpSize*4))){
					$html .= '<span style="font-size:17px"><a href="'.$urlRoot.'manage.php?id='.$cid.'&tagid='.$key.'&p=tag">' . tagIdToName($key) . '</a></span> '; 
			}
			else if($value > ($min+($jumpSize*4)) && $value <= ($min + ($jumpSize*5))){
				$html .= '<span style="font-size:20px"><a href="'.$urlRoot.'manage.php?id='.$cid.'&tagid='.$key.'&p=tag">' . tagIdToName($key) . '</a></span> '; 
			} 
		}
		$limitCounter++;


	}


	return $html . '</div>';

}


/*

Check to see if the link starts with http
if it doesn't, then add it in
*/
function validateLink($link){

		if(substr( $string_n, 0, 4 ) === "http"){
		
			return $link;
		} else {
			$link = 'http://' . $link;
		return $link;
		}
}

?>