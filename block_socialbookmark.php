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

require_once("sb_lib.php");
defined('MOODLE_INTERNAL') || die();

/**
 * Version details
  *
 * @package    block_socialbookmark
 * @copyright  2014 Kyle Goslin, Daniel McSweeney
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_socialbookmark extends block_base {

    /** init */
    public function init() {
        $this->title = get_string('pluginname', 'block_socialbookmark');
    }

    /** my moodle can only have SITEID and it's redundant here, so take it away*/
    public function applicable_formats() {
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
    }

    /** Allow multiple */
    public function instance_allow_multiple() {
          return true;
    }
    /** Has config */
    public function has_config() {
        return true;
    }


    /** Get the content for the block */
    public function get_content() {
        if ($this->content !== null) {
          return $this->content;
        }
 
        $cid = optional_param('id', '', PARAM_INT);
        $this->content         =  new stdClass;
        $this->content->text   = get_cloud($cid, 6, '../blocks/socialbookmark/admin/').'<p></p><hr> 
    
        <a href="../blocks/socialbookmark/add.php?cid='.$cid.'">
        <img style="height:16px; width:16px" src="../blocks/socialbookmark/img/fave.png"> '.
        get_string('addbookmark', 'block_socialbookmark').'</a><br>
        
        <a href="../blocks/socialbookmark/admin/manage.php?id='.$cid.'">
        <img style="height:16px; width:16px" src="../blocks/socialbookmark/img/view.png"> '.
        get_string('viewbookmarks', 'block_socialbookmark').'</a><br> 
        
        <a href="../blocks/socialbookmark/admin/settings.php?id='.$cid.'">
        <img style="height:16px; width:16px" src="../blocks/socialbookmark/img/cog.png"> '.
        get_string('settings', 'block_socialbookmark').'</a>';
        $this->content->footer = '';
        return $this->content;
    } 


}






