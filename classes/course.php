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
 * Provides a course API for aspirelists.
 *
 * @package    mod_aspirelists
 * @copyright  2016 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_aspirelists;

defined('MOODLE_INTERNAL') || die();

/**
 * Provides a course API for aspirelists.
 *
 * @package    mod_aspirelists
 * @copyright  2016 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course {
    private $course;

    /**
     * Constructor.
     */
    public function __construct($courseorid) {
        global $DB;

        if (is_object($courseorid)) {
            $this->course = $courseorid;
        } else {
            $this->course = $DB->get_record('course', array(
                'id' => $courseorid
            ));
        }
    }

    /**
     * Get all reading lists for a course.
     */
    public function get_lists($tp = 'default') {
        // Build API object.
        $api = new \mod_aspirelists\core\API();
        if ($tp !== 'default') {
            $api->set_timeperiod($tp);
        }

        // Extract the shortnames.
        $matches = $api->extract_shortcodes($this->course->shortname);

        // Grab categories for each shortname.
        $lists = array();
        foreach ($matches as $shortname) {
            // Grab lists.
            $matchlists = $api->get_lists($shortname);
            if (!empty($matchlists)) {
                $lists[$shortname] = $matchlists;
            }
        }

        return $lists;
    }

    /**
     * Do we actually have a list?
     * We only check one year back.
     */
    public function has_list() {
        $lists = $this->get_lists();
        if (!empty($lists)) {
            return true;
        }

        $tp = (int)get_config('aspirelists', 'timeperiod') - 1;
        $lastyearlists = $this->get_lists($tp);
        if (!empty($lastyearlists)) {
            return true;
        }

        return false;
    }

    /**
     * Is this year's list published?
     */
    public function is_published() {
        $lists = $this->get_lists();
        if (!empty($lists)) {
            return true;
        }

        $tp = (int)get_config('aspirelists', 'timeperiod') - 1;
        $lastyearlists = $this->get_lists($tp);
        if (!empty($lastyearlists)) {
            return false;
        }

        return false;
    }
}
