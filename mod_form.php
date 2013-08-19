<?php

/**********************************************************************
 *                      @package Kent aspirelists                     *
 *                                                                    *
 *              University of Kent aspirelists resource               *
 *                                                                    *
 *                       Authors: jwk8, fg30, jw26                    *
 *                                                                    *
 *--------------------------------------------------------------------*
 *                                                                    *
 *                            Add/update form                         *
 *                                                                    *
 **********************************************************************/

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_aspirelists_mod_form extends moodleform_mod {

	function definition() {
        global $CFG, $OUTPUT, $COURSE;

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

        $options=array();
        $options['misc']['all']    = 'All';
        $options['canterbury'] = array();
        $options['medway'] = array();


        $shortname_full = explode(' ', $COURSE->shortname);
        $shortnames = explode('/', strtolower($shortname_full[0]));

        foreach ($shortnames as $shortname) {
            $mainUrl = "$config->baseurl/$config->group/$shortname/lists.json";
            $altUrl = "$config->altBaseurl/$config->group/$shortname/lists.json";

            $mainData = curlSource($mainUrl);
            $mainData = json_decode($mainData, true);


            if(isset($mainData["$config->baseurl/$config->group/$shortname"]['http://purl.org/vocab/resourcelist/schema#usesList'][0]['value'])) {
                $list_url = $mainData["$config->baseurl/$config->group/$shortname"]['http://purl.org/vocab/resourcelist/schema#usesList'][0]['value'];
                $level = 0;
                $d = aspirelists_getCats($list_url, $options, $level, $shortname, 'canterbury');
            }

            $altData = curlSource($altUrl);
            $altData = json_decode($altData, true);

            if(isset($altData["$config->altBaseurl/$config->group/$shortname"]['http://purl.org/vocab/resourcelist/schema#usesList'][0]['value'])) {
                $list_url = $altData["$config->altBaseurl/$config->group/$shortname"]['http://purl.org/vocab/resourcelist/schema#usesList'][0]['value'];
                $level = 0;
                $d = aspirelists_getCats($list_url, $options, $level, $shortname, 'medway');
            }
            debugging(var_dump(ARRAY('mainUrl'=>$mainUrl,'maindata'=>$mainData,'altUrl'=>$altUrl,'altdata'=>$altData,'listUrl'=>$listUrl),DEBUG_DEVELOPER));
        }
        debugging(var_dump(ARRAY('options'=>$options),DEBUG_DEVELOPER));
        
        $mform->addElement('selectgroups', 'category', 'Category', $options, array('size'=>20));

        // add standard buttons, common to all modules
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
        return;
    }



}