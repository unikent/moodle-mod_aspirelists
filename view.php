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

require(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . "/lib.php");

global $CFG, $USER, $DB, $PAGE;

// dont do anything if not logged in
require_login();

$id = optional_param('id', 0, PARAM_INT);
$listid = optional_param('list', 0, PARAM_INT);

// Get the relevant objects.
if ($id > 0) {
    if (!$module = $DB->get_record("course_modules", array("id" => $id))) {
        throw new \moodle_exception(get_string('cmunknown', 'error'));
    }

    if (!$course = $DB->get_record("course", array("id" => $module->course))) {
        throw new \moodle_exception(get_string('invalidcourseid', 'error', $module->course));
    }

    if (!$readinglist = $DB->get_record('aspirelists', array('id' => $module->instance), '*', MUST_EXIST)) {
        throw new \moodle_exception(get_string('cmunknown', 'error'));
    }
} elseif ($listid > 0) {

    if (!$readinglist = $DB->get_record('aspirelists', array('id' => $listid), '*', MUST_EXIST)) {
        throw new \moodle_exception(get_string('cmunknown', 'error'));
    }

    if (!$module = $DB->get_record("course_modules", array("instance" => $readinglist->id))) {
        throw new \moodle_exception(get_string('cmunknown', 'error'));
    }

    if (!$course = $DB->get_record('course', array('id' => $readinglist->course))) {
        throw new \moodle_exception(get_string('invalidcourseid', 'error', $readinglist->course));
    }
} else {
    throw new \moodle_exception("A module ID or resource id must be specified");
}

$config = get_config('aspirelists');

$context = context_course::instance($course->id);
$PAGE->set_context($context);

//Set page params and layout
$PAGE->set_url('/mod/aspirelists/view.php', array('id'=>$id));
$PAGE->set_title(format_string($readinglist->name));
$PAGE->add_body_class('mod_aspirelists');
$PAGE->set_heading(format_string($readinglist->name));
$PAGE->navbar->add($course->shortname, "{$CFG->wwwroot}/course/view.php?id=$course->id");
$PAGE->navbar->add(get_string('modulename', 'aspirelists'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->css('/mod/aspirelists/styles/styles.css');
$PAGE->requires->css('/mod/aspirelists/styles/fontello.css');

$event = \mod_aspirelists\event\course_module_viewed::create(array(
    'objectid' => $readinglist->id,
    'courseid' => $course->id,
    'other' => array('cmid' => $id),
    'context' => context_module::instance($id)
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot('aspirelists', $readinglist);
$event->trigger();

//Check to see if a specific category has been picked
if ($readinglist->category != 'all') {

    $category = explode('/', $readinglist->category);

    switch ($category[0]) {
    case 'medway':
        $base_url = $config->altBaseurl;
        break;
    case 'canterbury':
    default:
        $base_url = $config->baseurl;
        break;
    }

    $url = $base_url . '/sections/' . $category[1];

    if (isset($CFG->aspirelists_resourcelist) && $CFG->aspirelists_resourcelist === true) {
        aspirelists_getResources($url);
    } else {
        redirect($url . '.html') ;
    }


} else { // if not then display reading lists for any short codes given
    $shortname_full = explode(' ', $course->shortname);
    $shortnames = explode('/', strtolower($shortname_full[0]));

    echo $OUTPUT->header();
    echo $OUTPUT->heading("$readinglist->name", 2, 'aspirelists_main', '');

    $output = '';
    foreach ($shortnames as $shortname) {

        $m = aspirelists_getLists($config->baseurl, $config->group, $shortname, $config->modTimePeriod);
        if (!empty($m)) {$main[] = $m; }

        $a = aspirelists_getLists($config->altBaseurl, $config->group, $shortname, $config->altModTimePeriod);
        if (!empty($a)) {$alt[] = $a; }

        if (!empty($main)) {
            $output .= '<h3 style="margin: 10px 0px;">Canterbury</h3>';
            foreach ($main as $i) {
                $output .= $i;
            }
        }

        if (!empty($alt)) {
            $output .= '<h3 style="margin: 10px 0px; ">Medway</h3>';
            foreach ($alt as $i) {
                $output .= $i;
            }
        }
    }


    if (empty($output)) {
        echo aspirelists_resource_not_ready($context);
    } else {
        echo $output;
    }

    echo $OUTPUT->footer();
}


/**
 *
 *
 * @param unknown $context
 * @return unknown
 */
function aspirelists_resource_not_ready($context) {
    global $USER;
    $output = "";
    $role = get_user_roles($context, $USER->id);

    if (isset($role[1]) && ($role[1]->shortname == 'student' || $role[1]->shortname == 'sds_student')) {
        $output .= get_string('error:studentnolist', 'aspirelists');
    } else if (has_capability('moodle/course:update', $context)) {
            $output .= get_string('error:staffnolist', 'aspirelists');
        } else {
        $output .= get_string('error:defaultnolist', 'aspirelists');
    }

    return $output;
}
