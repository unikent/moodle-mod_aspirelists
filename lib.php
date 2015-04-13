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

defined('MOODLE_INTERNAL') || die;

/**
 * Support list.
 */
function aspirelists_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
        return MOD_ARCHETYPE_RESOURCE;

        case FEATURE_GROUPS:
        return false;

        case FEATURE_GROUPINGS:
        return false;

        case FEATURE_GROUPMEMBERSONLY:
        return true;

        case FEATURE_MOD_INTRO:
        return true;

        case FEATURE_COMPLETION_TRACKS_VIEWS:
        return true;

        case FEATURE_GRADE_HAS_GRADE:
        return false;

        case FEATURE_GRADE_OUTCOMES:
        return false;

        case FEATURE_BACKUP_MOODLE2:
        return true;

        case FEATURE_SHOW_DESCRIPTION:
        return true;

        default:
        return null;
    }
}

/**
 * This is for participation report.
 */
function aspirelists_get_view_actions() {
    return array('view', 'view all');
}

/**
 * This is for participation report.
 */
function aspirelists_get_post_actions() {
    return array('update', 'add');
}

/**
 * Update an instance.
 */
function aspirelists_update_instance($data, $mform) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;
    $DB->update_record('aspirelists', $data);

    return true;
}


/**
 * Add an instance.
 */
function aspirelists_add_instance($data, $mform) {
    global $DB;
    return $DB->insert_record('aspirelists', $data);
}

/**
 * Delete an instance.
 */
function aspirelists_delete_instance($id) {
    global $DB;

    if (!$readinglist = $DB->get_record('aspirelists', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('aspirelists', array('id' => $id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object    $coursemodule
 * @return object info
 */
function aspirelists_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;

    // Get the resource.
    $aspireresource = $DB->get_record('aspirelists', array(
        'id' => $coursemodule->instance
    ), 'id, category, name, intro, introformat');

    if (!$aspireresource) {
        return null;
    }

    $info = new cached_cm_info();

    if ($coursemodule->showdescription == 1) {
        $info->content = format_module_intro(get_string('modulename', 'aspirelists'), $aspireresource, $coursemodule->id, false);
    }

    // If we are not showing all categories then set the link to direct to a new tab.
    if ($aspireresource->category != 'all') {
        $fullurl = new \moodle_url('/mod/aspirelists/view.php', array(
            'id' => $coursemodule->id,
            'redirect' => 1
        ));

        $info->onclick = "window.open('$fullurl'); return false;";
    }

    return $info;
}
