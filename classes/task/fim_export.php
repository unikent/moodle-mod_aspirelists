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
 * Aspirelists mod.
 *
 * @package    mod_aspirelists
 * @copyright  2016 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_aspirelists\task;

/**
 * Export user information for aspirelists into the FIM metaverse.
 */
class fim_export extends \core\task\scheduled_task
{
    private $users;

    public function get_name() {
        return "FIM Export";
    }

    public function execute() {
        global $DB;

        $this->users = array();

        $contextpreload = \context_helper::get_preload_record_columns_sql('x');
        $courses = $DB->get_records_sql("
            SELECT c.id, c.shortname, $contextpreload
            FROM {course} c
            INNER JOIN {context} x ON (c.id=x.instanceid AND x.contextlevel=" . CONTEXT_COURSE . ")
        ");

        foreach ($courses as $course) {
            \context_helper::preload_from_record($course);
            $this->build_user_list($course);
        }

        // Update SV.
        $DB->delete_records('aspirelists_fimexport_sv');
        $DB->insert_records('aspirelists_fimexport_sv', $this->build_sv());

        // Update mv.
        $DB->delete_records('aspirelists_fimexport_mv');
        $DB->insert_records('aspirelists_fimexport_mv', $this->build_mv());

        return true;
    }

    /**
     * Build user list for a given course.
     */
    private function build_user_list($course) {
        $context = \context_course::instance($course->id);
        $users = get_enrolled_users($context, 'mod/aspirelists:addinstance', 0, 'u.id, u.username');
        foreach ($users as $user) {
            if (!isset($this->users[$user->username])) {
                $this->users[$user->username] = array();
            }

            $shortname = $course->shortname;
            $pos = strpos($shortname, ' ');
            if ($pos !== false) {
                $shortname = substr($shortname, 0, $pos);
            }

            $this->users[$user->username][] = $shortname;
        }
    }

    /**
     * Build single value list.
     */
    private function build_sv() {
        $sv = array();
        $usernames = array_keys($this->users);
        foreach ($usernames as $username) {
            $sv[] = array('username' => $username);
        }

        return $sv;
    }

    /**
     * Build multi valued list.
     */
    private function build_mv() {
        $mv = array();
        foreach ($this->users as $username => $courses) {
            $courses = array_unique($courses);
            if (empty($courses)) {
                continue;
            }

            // Build the scope string.
            $coursestr = '';
            foreach ($courses as $course) {
                $coursestr .= '&scope=' . $course;
            }

            $mv[] = $this->build_mv_map($username, 'http://resourcelists.kent.ac.uk/constraints?role=listcreator');
            $mv[] = $this->build_mv_map($username, 'http://resourcelists.kent.ac.uk/constraints?role=listpub' . $coursestr);
            $mv[] = $this->build_mv_map($username, 'http://medwaylists.kent.ac.uk/constraints?role=listcreator');
            $mv[] = $this->build_mv_map($username, 'http://medwaylists.kent.ac.uk/constraints?role=listpub' . $coursestr);
        }

        return $mv;
    }

    /**
     * Build a user/course mapping set.
     */
    private function build_mv_map($username, $stringval) {
        return array(
            'username' => $username,
            'attribute_name' => 'unikentaspirerole',
            'string_value' => $stringval
        );
    }
}
