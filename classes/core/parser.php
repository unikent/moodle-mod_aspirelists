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
    const TIME_PERIOD_INDEX = 'http://resourcelists.kent.ac.uk/config/timePeriod';

    /** The raw, decoded, JSON */
    private $raw;

    /** The parsed list */
    private $data;

    /**
     * Constructor.
     *
     * @param string $data The raw data from the CURL
     */
    public function __construct($data) {
        $this->raw = json_decode($data);
        $this->data = array();

        $this->decode();
    }

    /**
     * Decode the list.
     */
    private function decode() {
        $timeperiods = $this->grab_timeperiods();
    }

    /**
     * Grab all known time periods.
     */
    public function grab_timeperiods() {
        if (isset($this->data['timeperiods'])) {
            return $this->data['timeperiods'];
        }

        $timeperiods = array();
        foreach ($this->raw as $k => $v) {
            $pos = strpos($k, self::TIME_PERIOD_INDEX);
            if ($pos !== 0) {
                continue;
            }

            $timeperiods[] = substr($k, strlen(self::TIME_PERIOD_INDEX));
        }

        $this->data['timeperiods'] = $timeperiods;

        return $timeperiods;
    }
}