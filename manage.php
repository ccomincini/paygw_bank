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
 * Manage bank transfer payments page.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;
use paygw_bank\bank_helper;

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();

global $PAGE, $OUTPUT, $DB;

$context = context_system::instance();
$PAGE->set_context($context);
$systemcontext = context_system::instance();
$PAGE->set_url('/payment/gateway/bank/manage.php');
$PAGE->set_pagelayout('report');
$pagetitle = get_string('manage', 'paygw_bank');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->navbar->add(get_string('pluginname', 'paygw_bank'), $PAGE->url);

$confirm = optional_param('confirm', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);

echo $OUTPUT->header();

require_capability('paygw/bank:managepayments', $systemcontext);

echo $OUTPUT->heading(get_string('pending_payments', 'paygw_bank'), 2);

if ($confirm == 1 && $id > 0) {
    require_sesskey();
    if ($action == 'A') {
        bank_helper::aprobe_pay($id);
        \core\notification::info(get_string('approve', 'paygw_bank'));
    }
    if ($action == 'D') {
        bank_helper::deny_pay($id);
        \core\notification::info(get_string('deny', 'paygw_bank'));
    }
}

$bankentries = bank_helper::get_pending();
if (!$bankentries) {
    echo $OUTPUT->heading(get_string('noentriesfound', 'paygw_bank'));
    $table = null;
} else {
    $table = new html_table();
    $table->head = [
        get_string('date'),
        get_string('code', 'paygw_bank'),
        get_string('username'),
        get_string('email'),
        get_string('concept', 'paygw_bank'),
        get_string('total_cost', 'paygw_bank'),
        get_string('currency'),
        get_string('hasfiles', 'paygw_bank'),
        get_string('actions'),
    ];

    foreach ($bankentries as $bankentry) {
        $config = (object) helper::get_gateway_configuration(
            $bankentry->component,
            $bankentry->paymentarea,
            $bankentry->itemid,
            'bank'
        );
        $payable = helper::get_payable($bankentry->component, $bankentry->paymentarea, $bankentry->itemid);
        $currency = $payable->get_currency();
        $customer = $DB->get_record('user', ['id' => $bankentry->userid]);
        $fullname = fullname($customer, true);

        // Add surcharge if there is any.
        $surcharge = helper::get_gateway_surcharge('paypal');
        $amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);

        $buttonaprobe = '<form name="formapprovepay' . $bankentry->id . '" method="POST">
            <input type="hidden" name="sesskey" value="' . sesskey() . '">
            <input type="hidden" name="id" value="' . $bankentry->id . '">
            <input type="hidden" name="action" value="A">
            <input type="hidden" name="confirm" value="1">
            <input class="btn btn-primary form-submit" type="submit" value="' . get_string('approve', 'paygw_bank') . '">
            </form>';

        $buttondeny = '<form name="formdenypay' . $bankentry->id . '" method="POST">
            <input type="hidden" name="sesskey" value="' . sesskey() . '">
            <input type="hidden" name="id" value="' . $bankentry->id . '">
            <input type="hidden" name="action" value="D">
            <input type="hidden" name="confirm" value="1">
            <input class="btn btn-secondary form-submit" type="submit" value="' . get_string('deny', 'paygw_bank') . '">
            </form>';

        $hasfiles = get_string('no');
        $files = bank_helper::files($bankentry->id);
        if ($bankentry->hasfiles > 0 || count($files) > 0) {
            $hasfiles = '<button type="button" class="btn btn-primary" data-bs-toggle="modal" '
                . 'data-bs-target="#staticBackdrop' . $bankentry->id . '" id="launchmodal' . $bankentry->id . '">'
                . get_string('view') . '</button>'
                . '<div class="modal fade" id="staticBackdrop' . $bankentry->id . '" '
                . 'aria-labelledby="staticBackdropLabel' . $bankentry->id . '" aria-hidden="true">'
                . '<div class="modal-dialog">'
                . '<div class="modal-content">'
                . '<div class="modal-header">'
                . '<h5 class="modal-title" id="staticBackdropLabel' . $bankentry->id . '">' . get_string('files') . '</h5>'
                . '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="' . get_string('close', 'paygw_bank') . '"></button>'
                . '</div>'
                . '<div class="modal-body">';

            foreach ($files as $f) {
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
                if (str_ends_with($filename, ".png") || str_ends_with($filename, ".jpg") || str_ends_with($filename, ".gif")) {
                    $hasfiles .= '<img src="' . $url . '"><br>';
                } else {
                    $hasfiles .= '<a href="' . $url . '" target="_blank">' . $filename . '</a><br>';
                }
            }

            $hasfiles .= '</div>'
                . '<div class="modal-footer">'
                . '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
                . get_string('close', 'paygw_bank') . '</button>'
                . '</div>'
                . '</div>'
                . '</div>'
                . '</div>';
        }

        $table->data[] = [
            date('Y-m-d', $bankentry->timecreated),
            $bankentry->code,
            $fullname,
            $customer->email,
            $bankentry->description,
            $amount,
            $currency,
            $hasfiles,
            $buttonaprobe . $buttondeny,
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
