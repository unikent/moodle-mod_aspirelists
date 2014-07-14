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

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $settings->add(new admin_setting_configtext('aspirelists/timeout',
        get_string('settings:timeout', 'aspirelists'),
        get_string('settings:configtimeout', 'aspirelists'),
        '2'
    ));

    $settings->add(new admin_setting_configtext('aspirelists/baseurl',
        get_string('settings:baseurl', 'aspirelists'),
        get_string('settings:configbaseurl', 'aspirelists'),
        'http://resourcelists.kent.ac.uk'
    ));

    $settings->add(new admin_setting_configtext('aspirelists/altBaseurl',
        get_string('settings:altbaseurl', 'aspirelists'),
        get_string('settings:altconfigbaseurl', 'aspirelists'),
        'http://medwaylists.kent.ac.uk'
    ));

    $settings->add(new admin_setting_configselect('aspirelists/group',
        get_string('settings:group', 'aspirelists'),
        get_string('settings:configgroup', 'aspirelists'),
        'modules',
        array(
            'courses' => get_string('settings:group:courses', 'aspirelists'),
            'modules' => get_string('settings:group:modules', 'aspirelists'),
            'units' => get_string('settings:group:units', 'aspirelists'),
            'programmes' => get_string('settings:group:programmes', 'aspirelists'),
            'subjects' => get_string('settings:group:subjects', 'aspirelists')
        )
    ));

    $settings->add(new admin_setting_configcheckbox('aspirelists/redirect',
        get_string('settings:redirect', 'aspirelists'),
        get_string('settings:configredirect', 'aspirelists'),
        1
    ));

    $settings->add(new admin_setting_configtext('aspirelists/modTimePeriod',
        get_string('config_timePeriod', 'aspirelists'),
        get_string('config_timePeriod_desc', 'aspirelists'),
        '53304cb6f3d4d'
    ));

    $settings->add(new admin_setting_configtext('aspirelists/altModTimePeriod',
        get_string('config_altTimePeriod', 'aspirelists'),
        get_string('config_altTimePeriod_desc', 'aspirelists'),
        '53304d3387393'
    ));

    $settings->add(new admin_setting_configtext('aspirelists/cacheDelay',
        get_string('config_cacheDelay', 'aspirelists'),
        get_string('config_cacheDelay_desc', 'aspirelists'),
        60
    ));

    $settings->add(new admin_setting_configtext('aspirelists/cacheMaxDelay',
        get_string('config_cacheMaxDelay', 'aspirelists'),
        get_string('config_cacheMaxDelay_desc', 'aspirelists'),
        600
    ));
}
