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
 * Bank transfer payment page.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;
use paygw_bank\bank_helper;
use paygw_bank\pay_form;
use paygw_bank\attachtransfer_form;

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/lib.php');

global $PAGE, $OUTPUT, $USER;

$canuploadfiles = get_config('paygw_bank', 'usercanuploadfiles');
$maxnumberfiles = get_config('paygw_bank', 'maxnumberfiles');
if (!$maxnumberfiles) {
    $maxnumberfiles = 3;
}

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$component = required_param('component', PARAM_COMPONENT);
$paymentarea = required_param('paymentarea', PARAM_AREA);
$itemid = required_param('itemid', PARAM_INT);
$description = required_param('description', PARAM_TEXT);
$description = json_decode('"' . $description . '"');

$params = [
    'component' => $component,
    'paymentarea' => $paymentarea,
    'itemid' => $itemid,
    'description' => $description,
];

$mform = new pay_form(null, [
    'confirm' => 1,
    'component' => $component,
    'paymentarea' => $paymentarea,
    'itemid' => $itemid,
    'description' => $description,
]);
$mform->set_data($params);

$atform = new attachtransfer_form();
$atform->set_data($params);

$dataform = $mform->get_data();
$atdataform = $atform->get_data();
$confirm = 0;

if ($dataform != null) {
    $component = $dataform->component;
    $paymentarea = $dataform->paymentarea;
    $itemid = $dataform->itemid;
    $description = $dataform->description;
    $confirm = $dataform->confirm;
}

if ($atdataform != null) {
    $component = $atdataform->component;
    $paymentarea = $atdataform->paymentarea;
    $itemid = $atdataform->itemid;
    $description = $atdataform->description;
    $confirm = $atdataform->confirm;
}

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/payment/gateway/bank/pay.php', $params);
$PAGE->set_pagelayout('report');
$pagetitle = $description;
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$config = (object) helper::get_gateway_configuration($component, $paymentarea, $itemid, 'bank');
$payable = helper::get_payable($component, $paymentarea, $itemid);
$currency = $payable->get_currency();
$bankentry = null;

// Add surcharge if there is any.
$surcharge = helper::get_gateway_surcharge('bank');
$amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('gatewayname', 'paygw_bank'), 2);
echo '<div class="card">';
echo '<div class="card-body">';
echo '<ul class="list-group list-group-flush">';
echo '<li class="list-group-item"><h5 class="card-title">' . get_string('concept', 'paygw_bank') . ':</h5>';
echo '<div>' . $description . '</div>';
echo '</li>';

$instructions = format_text($config->instructionstext['text']);

if (bank_helper::has_openbankentry($itemid, $USER->id)) {
    $bankentry = bank_helper::get_openbankentry($itemid, $USER->id);
    $amount = $bankentry->totalamount;
    $confirm = 0;
} else {
    if ($confirm != 0) {
        $totalamount = $amount;
        $bankentry = bank_helper::create_bankentry(
            $itemid,
            $USER->id,
            $totalamount,
            $currency,
            $component,
            $paymentarea,
            $description
        );
        \core\notification::info(get_string('transfer_process_initiated', 'paygw_bank'));
        $confirm = 0;
    }
}

if ($surcharge && $surcharge > 0 && $bankentry == null) {
    echo '<li class="list-group-item"><h4 class="card-title">' . get_string('cost', 'paygw_bank') . ':</h4>';
    echo '<div id="price">' . helper::get_cost_as_string($payable->get_amount(), $currency) . '</div>';
    echo '</li>';
    echo '<li class="list-group-item"><h4 class="card-title">' . get_string('surcharge', 'core_payment') . ':</h4>';
    echo '<div id="price">' . $surcharge . '%</div>';
    echo '<div id="explanation">' . get_string('surcharge_desc', 'core_payment') . '</div>';
    echo '</li>';
    echo '<li class="list-group-item"><h4 class="card-title">' . get_string('total_cost', 'paygw_bank') . ':</h4>';
    echo '<div id="price">' . helper::get_cost_as_string($amount, $currency) . ' ' . $currency . '</div>';
    echo '</li>';
} else {
    echo '<li class="list-group-item"><h4 class="card-title">' . get_string('total_cost', 'paygw_bank') . ':</h4>';
    echo '<div id="price">' . helper::get_cost_as_string($amount, $currency) . ' ' . $currency . '</div>';
    echo '</li>';
}

if ($bankentry != null) {
    echo '<li class="list-group-item"><h4 class="card-title">' . get_string('transfer_code', 'paygw_bank') . ':</h4>';
    echo '<div id="transfercode">' . $bankentry->code . '</div>';
    echo '</li>';
    $instructions = format_text($config->postinstructionstext['text']);
}

echo '</ul>';
echo '<div id="bankinstructions">' . $instructions . '</div>';

if ($confirm == 0 && !bank_helper::has_openbankentry($itemid, $USER->id)) {
    $mform->display();
} else {
    if ($canuploadfiles) {
        if ($atform != null) {
            $content = $atform->get_file_content('userfile');
            $name = $atform->get_new_filename('userfile');

            if ($name) {
                $fs = get_file_storage();
                $isalreadyuploaded = false;
                $files = bank_helper::files($bankentry->id);

                if (count($files) >= $maxnumberfiles) {
                    \core\notification::error(get_string('max_number_of_files_reached', 'paygw_bank'));
                } else {
                    foreach ($files as $f) {
                        $filename = $f->get_filename();
                        if ($name == $filename) {
                            $isalreadyuploaded = true;
                        }
                    }

                    if ($isalreadyuploaded) {
                        \core\notification::warning(get_string('file_already_uploaded', 'paygw_bank'));
                    } else {
                        $tempdir = make_request_directory();
                        $fullpath = $tempdir . '/' . $name;
                        $override = true;
                        $success = $atform->save_file('userfile', $fullpath, $override);

                        $fileinfo = [
                            'contextid' => context_system::instance()->id,
                            'component' => 'paygw_bank',
                            'filearea' => 'transfer',
                            'filepath' => '/',
                            'filename' => $name,
                            'itemid' => $bankentry->id,
                            'userid' => $USER->id,
                            'author' => fullname($USER, true),
                        ];
                        $fs->create_file_from_pathname($fileinfo, $fullpath);
                        bank_helper::check_hasfiles($bankentry->id);

                        $sendemail = get_config('paygw_bank', 'sendnewattachmentsmail');
                        $emailaddress = get_config('paygw_bank', 'notificationsaddress');

                        if ($sendemail) {
                            $supportuser = core_user::get_support_user();
                            $subject = get_string('email_notifications_subject_attachments', 'paygw_bank');
                            $contentmessage = new stdClass();
                            $contentmessage->code = $bankentry->code;
                            $contentmessage->concept = $bankentry->description;
                            $mailcontent = get_string('email_notifications_new_attachments', 'paygw_bank', $contentmessage);
                            $emailuser = new stdClass();
                            $emailuser->email = $emailaddress;
                            $emailuser->id = -99;
                            email_to_user($emailuser, $supportuser, $subject, $mailcontent);
                        }
                        \core\notification::info(get_string('file_uploaded', 'paygw_bank'));
                    }
                }
            }
        }

        $files = bank_helper::files($bankentry->id);
        if (count($files) > 0) {
            echo '<h3>' . get_string('files') . ':</h3>';
            echo '<ul class="list-group">';
            foreach ($files as $f) {
                echo '<li class="list-group-item">';
                $url = moodle_url::make_pluginfile_url(
                    $f->get_contextid(),
                    $f->get_component(),
                    $f->get_filearea(),
                    $f->get_itemid(),
                    $f->get_filepath(),
                    $f->get_filename(),
                    false
                );
                $filename = $f->get_filename();
                $imageexts = ['.png', '.jpeg', '.jpg', '.svg', '.gif'];
                $isimage = false;
                foreach ($imageexts as $ext) {
                    if (str_ends_with($filename, $ext)) {
                        $isimage = true;
                        break;
                    }
                }
                if ($isimage) {
                    echo $filename;
                    echo '<br><img style="max-height:100px" src="' . $url . '">';
                } else {
                    echo $filename;
                }
                echo '</li>';
            }
            echo '</ul>';
        }

        if (count($files) < $maxnumberfiles) {
            $atform->display();
        }
    }
}

echo '</div>';
echo '</div>';
echo $OUTPUT->footer();
