<?php
/*
	---------------------------------------------------------------------------------
 	Social Bookmark Block for Moodle
 	Copyright 2012-2013 Kyle Goslin, Daniel McSweeney
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
/**
 * Newblock block caps.
 *
 * @package    block_newblock
 * @copyright  Daniel Neis <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading('sampleheader',
                                         get_string('headerconfig', 'block_socialbookmark'),
                                         get_string('descconfig', 'block_socialbookmark')));
/*
$settings->add(new admin_setting_configcheckbox('newblock/foo',
                                                get_string('labelfoo', 'block_socialbookmark'),
                                                get_string('descfoo', 'block_socialbookmark'),
                                                '0'));
*/