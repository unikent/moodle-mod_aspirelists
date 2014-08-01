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
 * mod_aspirelists parser class.
 * 
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class parser {
    const INDEX_TIME_PERIOD = 'config/timePeriod';
    const INDEX_LISTS = 'lists/';
    const INDEX_LISTS_TIME_PERIOD = 'http://lists.talis.com/schema/temp#hasTimePeriod';
    const INDEX_LISTS_LIST_ITEMS = 'http://purl.org/vocab/resourcelist/schema#contains';
    const INDEX_LISTS_LIST_UPDATED = 'http://purl.org/vocab/resourcelist/schema#lastUpdated';

    /** Our Base URL */
    private $baseurl;

    /** The raw, decoded, JSON */
    private $raw;

    /** The parsed list */
    private $data;

    /**
     * Constructor.
     *
     * @param string $data The raw data from the CURL
     */
    public function __construct($baseurl, $data) {
        $this->baseurl = $baseurl;
        if (strrpos($this->baseurl, '/') !== strlen($this->baseurl) - 1) {
            $this->baseurl = $this->baseurl . '/';
        }

        $this->raw = json_decode($data, true);
        if (!$this->raw) {
            $this->raw = array();
        }

        $this->data = array();
    }

    /**
     * Shorthand method.
     */
    private function grab_dataset($index, $apiindex) {
        if (isset($this->data[$index])) {
            return $this->data[$index];
        }

        $data = array();
        foreach ($this->raw as $k => $v) {
            $pos = strpos($k, $apiindex);
            if ($pos !== 0) {
                continue;
            }

            $data[] = substr($k, strlen($apiindex));
        }

        $this->data[$index] = $data;

        return $data;
    }

    /**
     * Grab all known time periods.
     */
    public function grab_timeperiods() {
        return $this->grab_dataset('timeperiods', $this->baseurl . self::INDEX_TIME_PERIOD);
    }

    /**
     * Grabs all known lists.
     */
    public function grab_all_lists() {
        return $this->grab_dataset('lists', $this->baseurl . self::INDEX_LISTS);
    }

    /**
     * Get the raw data for a given list.
     */
    private function get_raw_list($list) {
        return $this->raw[$this->baseurl . self::INDEX_LISTS . $list];
    }

    /**
     * Which time period is this list in?
     */
    public function which_time_period($list) {
        $data = $this->get_raw_list($list);
        $data = $data[self::INDEX_LISTS_TIME_PERIOD];
        $data = $data[0];
        $data = $data['value'];
        $data = substr($data, strlen($this->baseurl . self::INDEX_TIME_PERIOD));

        return $data;
    }

    /**
     * Grabs lists for a specific time period.
     */
    public function grab_lists($timeperiod) {
        $lists = array();
        foreach ($this->grab_all_lists() as $list) {
            if ($this->which_time_period($list) == $timeperiod) {
                $lists[] = $list;
            }
        }

        return $lists;
    }

    /**
     * Grab list URL.
     */
    public function grab_list_url($list) {
        return self::INDEX_LISTS . $list;
    }

    /**
     * Name of a list.
     */
    public function list_name($list) {
        $data = $this->get_raw_list($list);
        return $data['http://rdfs.org/sioc/spec/name'][0]['value'];
    }

    /**
     * Counts the number of items in a list.
     */
    public function item_count($list) {
        $data = $this->get_raw_list($list);

        $count = 0;
        if (isset($data[self::INDEX_LISTS_LIST_ITEMS])) {
            foreach ($data[self::INDEX_LISTS_LIST_ITEMS] as $things) {
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
    public function last_updated($list) {
        $data = $this->get_raw_list($list);
        $time = null;

        if (isset($data[self::INDEX_LISTS_LIST_UPDATED])) {
            $time = clean_param($data[self::INDEX_LISTS_LIST_UPDATED][0]['value'], PARAM_TEXT);
            $time = strtotime($time);
        }

        return $time;
    }
}