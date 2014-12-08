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

class mod_aspirelists_renderer extends plugin_renderer_base
{

    /**
     *  Returns a "sorry" page.
     */
    public function resource_not_ready($context) {
        global $USER;

        if (has_capability('moodle/course:update', $context)) {
            return get_string('error:staffnolist', 'aspirelists');
        }

        $roles = get_user_roles($context, $USER->id);
        foreach ($roles as $role) {
            if (strpos($role->shortname, 'student') !== false) {
                return get_string('error:studentnolist', 'aspirelists');
            }
        }

        return get_string('error:defaultnolist', 'aspirelists');
    }
}

