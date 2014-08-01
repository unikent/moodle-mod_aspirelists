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
 * mod_aspirelists parser
 *
 * @package    mod_aspirelists
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_aspirelists\core;

defined('MOODLE_INTERNAL') || die();

/**
 * This class represents a reading list.
 * 
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reading_list {
    /** Our Base URL */
    private $baseurl;

    /** The list ID */
    private $id;

    /** The parsed list */
    private $data;

    /**
     * Constructor.
     */
    public function __construct($id, $data) {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * Which time period is this list in?
     */
    public function get_time_period() {
        $period = $this->data[parser::INDEX_LISTS_TIME_PERIOD][0]['value'];
        return substr($period, strpos($period, parser::INDEX_TIME_PERIOD) + strlen(parser::INDEX_TIME_PERIOD));
    }

    /**
     * Grab list URL.
     */
    public function get_url() {
        return parser::INDEX_LISTS . $this->id;
    }

    /**
     * Name of a list.
     */
    public function get_name() {
        return $this->data[parser::INDEX_NAME_SPEC][0]['value'];
    }

    /**
     * Counts the number of items in a list.
     */
    public function get_item_count() {
        $data = $this->data;

        $count = 0;
        if (isset($data[parser::INDEX_LISTS_LIST_ITEMS])) {
            foreach ($data[parser::INDEX_LISTS_LIST_ITEMS] as $things) {
                if (preg_match('/\/items\//', clean_param($things['value'], PARAM_URL))) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get the time a list was last updated.
     */
    public function get_last_updated() {
        $data = $this->data;
        $time = null;

        if (isset($data[parser::INDEX_LISTS_LIST_UPDATED])) {
            $time = clean_param($data[parser::INDEX_LISTS_LIST_UPDATED][0]['value'], PARAM_TEXT);
            $time = strtotime($time);
        }

        return $time;
    }
}