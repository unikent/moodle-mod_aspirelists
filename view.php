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
require_once($CFG->libdir.'/completionlib.php');
require_once(dirname(__FILE__) . "/lib.php");

$id = optional_param('id', 0, PARAM_INT);
$listid = optional_param('list', 0, PARAM_INT);

// Get the relevant objects.
if ($id > 0) {
    if (!$cm = $DB->get_record("course_modules", array("id" => $id))) {
        throw new \moodle_exception(get_string('cmunknown', 'error'));
    }

    if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
        throw new \moodle_exception(get_string('invalidcourseid', 'error', $cm->course));
    }

    if (!$readinglist = $DB->get_record('aspirelists', array('id' => $cm->instance), '*', MUST_EXIST)) {
        throw new \moodle_exception(get_string('cmunknown', 'error'));
    }
} elseif ($listid > 0) {

    if (!$readinglist = $DB->get_record('aspirelists', array('id' => $listid), '*', MUST_EXIST)) {
        throw new \moodle_exception(get_string('cmunknown', 'error'));
    }

    if (!$course = $DB->get_record('course', array('id' => $readinglist->course))) {
        throw new \moodle_exception(get_string('invalidcourseid', 'error', $readinglist->course));
    }

    $cm = get_coursemodule_from_instance('aspirelists', $readinglist->id, $course->id, false, MUST_EXIST);
} else {
    throw new \moodle_exception("A module ID or resource id must be specified");
}

// Check login and get context.
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

$config = get_config('aspirelists');

$url = new moodle_url($CFG->wwwroot.'/mod/aspirelists/view.php');
if (isset($id)) {
    $url->param('id', $id);
} else {
    $url->param('list', $listid);
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_activity_record($readinglist);

//Set page params and layout
$PAGE->set_title(format_string($readinglist->name));
$PAGE->set_heading(format_string($readinglist->name));
$PAGE->requires->css('/mod/aspirelists/styles/styles.css');

$event = \mod_aspirelists\event\course_module_viewed::create(array(
    'objectid' => $readinglist->id,
    'context' => $context
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('aspirelists', $readinglist);
$event->trigger();

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

if (!empty($readinglist->item)) {
    redirect($readinglist->item . '.html');
    die();
}

// Check to see if a specific category has been picked.
if ($readinglist->category != 'all') {
    $category = explode('/', $readinglist->category);

    $url = \mod_aspirelists\core\API::CANTERBURY_URL;
    if (strtolower($category[0]) == 'medway'){
        $url = \mod_aspirelists\core\API::MEDWAY_URL;
    }

    redirect($url . '/sections/' . $category[1] . '.html');
    die();
}

echo $OUTPUT->header();
echo $OUTPUT->heading(s($readinglist->name));

$renderer = $PAGE->get_renderer('mod_aspirelists');
echo '<div id="aspirecontainer">';
echo $renderer->print_lists($course);
echo '</div>';

echo $OUTPUT->footer();
