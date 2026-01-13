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
 * Attach transfer file form for bank transfer gateway.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_bank;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Attach transfer file form class for bank transfer gateway.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attachtransfer_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $CFG;

        $maxbytes = 0;
        if (!empty($CFG->maxbytes)) {
            $maxbytes = $CFG->maxbytes;
        }

        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        $mform->addElement('hidden', 'confirm');
        $mform->setDefault('confirm', 2);
        $mform->setType('confirm', PARAM_INT);

        $mform->addElement('hidden', 'component');
        $mform->setType('component', PARAM_TEXT);

        $mform->addElement('hidden', 'paymentarea');
        $mform->setType('paymentarea', PARAM_TEXT);

        $mform->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);

        $mform->addElement('hidden', 'description');
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement(
            'filepicker',
            'userfile',
            get_string('file'),
            null,
            ['maxbytes' => $maxbytes, 'accepted_types' => ['document', 'image']]
        );
        $mform->addRule('userfile', null, 'required');

        $mform->addElement('submit', 'submitbutton', get_string('upload'));
    }

    /**
     * Validate the form data.
     *
     * @param array $data The form data
     * @param array $files The uploaded files
     * @return array The validation errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
