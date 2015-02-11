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
     * Print lists.
     */
    public function print_lists($course, $context) {
        $subject = strtolower($course->shortname);
        if (empty($subject)) {
            return $this->resource_not_ready($context);
        }

        // Build API object.
        $api = new \mod_aspirelists\core\API();

        $output = '';
        $matches = explode('/', $subject);
        foreach ($matches as $shortname) {
            $formattedlists = array();

            // Grab lists.
            $lists = $api->get_lists($shortname);
            foreach ($lists as $list) {
                $campus = $list->get_campus();
                if (!isset($formattedlists[$campus])) {
                    $formattedlists[$campus] = array();
                }

                $formattedlists[$campus][] = $this->render_list($list);
            }

            foreach ($formattedlists as $campus => $lists) {
                $output .= '<h3>' . $campus . '</h3>';
                foreach ($lists as $list) {
                    $output .= $list;
                }
            }
        }

        if (empty($output)) {
            return $this->resource_not_ready($context);
        } else {
            return $output;
        }
    }

    /**
     * Render a list.
     */
    private function render_list($list) {
        $output = '<ul class="list_item_inset">';

        $output .= '<li class="list_item">';
        $output .= '<table>';
        $output .= '<tr>';
        $output .= '<td class="list_item_dets">';
        $output .= '<a href="' . $list->get_url() . '" target="_blank">';
        $output .= '<span class="fa fa-arrow-circle-o-right"></span>';
        $output .= '<span class="list_item_link">' . $list->get_name() . '</span>';

        $count = $list->get_item_count();
        if ($count > 0) {
            $itemnoun = ($count == 1) ? "item" : "items";
            $output .= '<span class="list_item_count">';
            $output .= $count . ' ' . $itemnoun;
            $output .= '</span>';
        }

        $output .= '</a>';
        $output .= '</td>';

        // Add update text if we have it.
        $lastupdated = $list->get_last_updated();
        if ($lastupdated) {
            $output .= '<td class="list_update">';
            $output .= '<ul class="list_item_update">';
            $output .= '<li class="title">last updated</li>';
            $output .= '<li class="month">' . date('F', $lastupdated) . '</li>';
            $output .= '<li class="day">' . date('j', $lastupdated) . '</li>';
            $output .= '<li class="year">' . date('Y', $lastupdated) . '</li>';
            $output .= '</ul>';
            $output .= '</td>';
        }
        $output .= '</tr>';
        $output .= '</table>';
        $output .= '</li>';

        $output .= '</ul>';

        return $output;
    }

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

