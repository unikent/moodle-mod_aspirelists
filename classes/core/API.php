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

namespace mod_aspirelists\core;

defined('MOODLE_INTERNAL') || die();

/**
 * mod_aspirelists aspirelists class.
 *
 * @package    mod_aspirelists
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class API extends \unikent\ReadingLists\API
{
    public function __construct() {
        parent::__construct();

        $cache = \cache::make('mod_aspirelists', 'data');
        $this->set_cache_layer($cache);
        $this->set_timeout(get_config('aspirelists', 'timeout'));
        $this->set_timeperiod(get_config('aspirelists', 'timeperiod'));
    }

    /**
     * Extract shortcodes.
     */
    public function extract_shortcodes($shortcode) {
        $shortcode = strtolower($shortcode);
        $matches = explode('/', $shortcode);

        return array_map(function($match) {
            if (strpos($match, ' ') !== false) {
                $match = explode(' ', $match);
                $match = $match[0];
            }

            return trim($match);
        }, $matches);
    }
}
