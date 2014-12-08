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

require_once(dirname(__FILE__) . "/../../lib/readinglists/src/API.php");
require_once(dirname(__FILE__) . "/../../lib/readinglists/src/Parser.php");
require_once(dirname(__FILE__) . "/../../lib/readinglists/src/ReadingList.php");

/**
 * mod_aspirelists aspirelists class.
 *
 * @package    mod_aspirelists
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class aspirelists extends \unikent\ReadingLists\API
{
    public function __construct() {
        parent::__construct();

        $cache = \cache::make('mod_aspirelists', 'data');
        $this->set_cache_layer($cache);
        $this->set_timeout(get_config('aspirelists', 'timeout'));
        $this->set_timeperiod(get_config('aspirelists', 'timeperiod'));
    }
}