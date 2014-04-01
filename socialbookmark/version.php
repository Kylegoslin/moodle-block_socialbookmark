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
/**
 * Version details
 *
 * @package    block_newblock
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2014040113;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2012112900;        // Requires this Moodle version
$plugin->component = 'block_socialbookmark'; // Full name of the plugin (used for diagnostics)
$plugin->cron = 300;
$plugin->release = '1.3';
