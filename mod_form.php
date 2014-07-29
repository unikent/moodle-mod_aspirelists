<?php

defined('MOODLE_INTERNAL') || die;

require_once $CFG->dirroot.'/course/moodleform_mod.php';

class mod_aspirelists_mod_form extends moodleform_mod {

    /**
     *
     */
    function definition() {
        global $CFG, $COURSE;

        $config = get_config('aspirelists');

        $mform =& $this->_form;

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $this->add_intro_editor();

        //-------------------------------------------------------

        $options = array();
        $options['misc']['all'] = 'All';
        $options['canterbury'] = array();
        $options['medway'] = array();


        $shortname_full = explode(' ', $COURSE->shortname);
        $shortnames = explode('/', strtolower($shortname_full[0]));

        foreach ($shortnames as $shortname) {
            // Canterbury first.
            if (true) {
                $data = aspirelists_curlSource("{$config->baseurl}/{$config->group}/{$shortname}/lists.json");
                $parser = new \mod_aspirelists\core\parser($data);
                $lists = $parser->grab_lists($config->modTimePeriod);

                if (!empty($lists)) {
                    $depth = 0;
                    foreach ($lists as $list) {
                        $url = $parser->grab_list_url($list);
                        aspirelists_getCats($url, $options, $depth, $shortname, 'canterbury');
                    }
                }
            }

            // Medway next.
            if (true) {
                $data = aspirelists_curlSource("{$config->altBaseurl}/{$config->group}/{$shortname}/lists.json");
                $parser = new \mod_aspirelists\core\parser($data);
                $lists = $parser->grab_lists($config->altModTimePeriod);

                if (!empty($lists)) {
                    $depth = 0;
                    foreach ($lists as $list) {
                        $url = $parser->grab_list_url($list);
                        aspirelists_getCats($url, $options, $depth, $shortname, 'medway');
                    }
                }
            }
        }

        $mform->addElement('selectgroups', 'category', 'Category', $options, array(
            'size' => 20
        ));

        // Add standard buttons, common to all modules.
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
        return;
    }
}
