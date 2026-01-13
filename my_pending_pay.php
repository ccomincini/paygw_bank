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
 * User pending payments page.
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

global $PAGE, $OUTPUT, $USER, $DB;

$context = context_system::instance();
$PAGE->set_context(context_user::instance($USER->id));
$canuploadfiles = get_config('paygw_bank', 'usercanuploadfiles');
$PAGE->set_url('/payment/gateway/bank/my_pending_pay.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('my_pending_payments', 'paygw_bank'));
$PAGE->navigation->extend_for_user($USER->id);
$PAGE->set_heading(get_string('my_pending_payments', 'paygw_bank'));
$PAGE->navbar->add(get_string('profile'), new moodle_url('/user/profile.php', ['id' => $USER->id]));
$PAGE->navbar->add(get_string('my_pending_payments', 'paygw_bank'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('my_pending_payments', 'paygw_bank'), 2);

$bankentries = bank_helper::get_user_pending($USER->id);
if (!$bankentries) {
    echo $OUTPUT->heading(get_string('noentriesfound', 'paygw_bank'));
    $table = null;
} else {
    $table = new html_table();
    $canuploadfiles = get_config('paygw_bank', 'usercanuploadfiles');
    $headarray = [
        get_string('date'),
        get_string('code', 'paygw_bank'),
        get_string('concept', 'paygw_bank'),
        get_string('total_cost', 'paygw_bank'),
        get_string('currency'),
    ];
    if ($canuploadfiles) {
        $headarray[] = get_string('hasfiles', 'paygw_bank');
    }
    $headarray[] = get_string('actions');
    $table->head = $headarray;

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
        $amount = helper::get_cost_as_string($payable->get_amount(), $currency, $surcharge);
        $component = $bankentry->component;
        $paymentarea = $bankentry->paymentarea;
        $itemid = $bankentry->itemid;
        $description = $bankentry->description;
        $urlpay = new moodle_url('/payment/gateway/bank/pay.php', [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'description' => $description,
        ]);
        $buttongo = '<a class="btn btn-primary" href="' . $urlpay . '">' . get_string('go') . '</a>';
        $dataarray = [
            date('Y-m-d', $bankentry->timecreated),
            $bankentry->code,
            $bankentry->description,
            $amount,
            $currency,
        ];

        if ($canuploadfiles) {
            $hasfiles = get_string('no');
            $files = bank_helper::files($bankentry->id);
            if (count($files) > 0) {
                $hasfiles = get_string('yes');
            }
            $dataarray[] = $hasfiles;
        }
        $dataarray[] = $buttongo;
        $table->data[] = $dataarray;
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
