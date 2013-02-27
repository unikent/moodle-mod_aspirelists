<?php  // $Id$
/**
 * Capability definitions for the aspire module.
 *
 * For naming conventions, see lib/db/access.php.
 */
$capabilities = array(

    'mod/aspirelists:addinstance' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),

    // Ability to manage/publish reading lists
    'mod/talis:pub' => array(
        'riskbitmask' => RISK_MANAGETRUST & RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'guest' => CAP_PREVENT,
            'student' => CAP_PREVENT,
        )
    ),

);