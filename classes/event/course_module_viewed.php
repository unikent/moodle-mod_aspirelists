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

namespace mod_aspirelists\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event Class
 */
class course_module_viewed extends \core\event\base
{
    /**
     * Init method.
     */
    protected function init() {
        global $CFG;

        $this->data['objecttable'] = 'aspirelists';
        $this->data['crud'] = 'r';
        $this->data[(floatval($CFG->release) <= 2.6) ? 'level' : 'edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns localised general event name.
     * 
     * @return string
     */
    public static function get_name() {
        return "AspireLists View";
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return 'User viewed aspirelists module \'' . $this->objectid . '\'.';
    }

    /**
     * Returns relevant URL.
     * 
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/aspirelists/view.php', array('id' => $this->objectid));
    }

    /**
     * Return the legacy event log data.
     * 
     * @return array
     */
    protected function get_legacy_logdata() {
        return array($this->courseid, 'aspirelist', 'view', 'view.php?id=' . $this->objectid, $this->cmid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->courseid)) {
            throw new \coding_exception('The \'courseid\' must be set.');
        }

        if (!isset($this->cmid)) {
            throw new \coding_exception('The \'cmid\' must be set.');
        }
    }
}