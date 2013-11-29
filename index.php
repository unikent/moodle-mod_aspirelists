<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * List of all resources in course
 *
 * @package    mod
 * @subpackage resource
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

add_to_log($course->id, 'aspirelists', 'view all', "index.php?id=$course->id", '');

$strlists        = get_string('modulenameplural', 'aspirelists');
$strname         = get_string('name');
$strweek         = get_string("week");
$strtopic        = get_string("topic");
$strsummary      = get_string("summary");

$PAGE->set_url('/mod/aspirelists/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strlists);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strlists);
$PAGE->requires->css('/mod/aspirelists/styles/styles.css');

echo $OUTPUT->header();

if (!$lists = get_all_instances_in_course('aspirelists', $course)) {
    notice(get_string('thereareno', 'moodle', $strlists), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($course->format == "weeks") {
    $table->head  = array ($strweek, $strname, $strsummary, 'Category');
    $table->align = array ("center", "left", "left");
} else if ($course->format == "topics") {
    $table->head  = array ($strtopic, $strname, $strsummary, 'Category');
    $table->align = array ("center", "left", "left", "left", "left");
} else {
    $table->head  = array ($strname,$strsummary, 'Category');
    $table->align = array ("left", "left", "left", "left");
}

$modinfo = get_fast_modinfo($course);

foreach ($lists as $list) {

    if (!$list->visible) {
        //Show dimmed if the mod is hidden
        $link = "<a class=\"dimmed\" href=\"view.php?id=$list->coursemodule\">$list->name</a>";
    } else {
        //Show normal if the mod is visible
        $link = "<a href=\"view.php?id=$list->coursemodule\">$list->name</a>";
    }

    $summary = $list->intro;

    $category = $list->category;

    if ($course->format == "weeks" or $course->format == "topics") {
        $table->data[] = array ($list->section, $link, $summary, $category);
    } else {
        $table->data[] = array ($link, $summary, $category);
    }
}

echo html_writer::table($table);

echo $OUTPUT->footer();
