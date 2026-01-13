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
 * Contains helper class for bank payment gateway.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_bank;

use core_user;
use core_payment\helper as payment_helper;
use stdClass;

/**
 * Helper class for bank payment gateway operations.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bank_helper {
    /**
     * Get an open bank entry for a user and item.
     *
     * @param int $itemid The item ID
     * @param int $userid The user ID
     * @return stdClass The bank entry record
     */
    public static function get_openbankentry($itemid, $userid): stdClass {
        global $DB;
        $record = $DB->get_record('paygw_bank', ['itemid' => $itemid, 'userid' => $userid, 'status' => 'P']);
        return $record;
    }

    /**
     * Check and update hasfiles flag for a bank entry.
     *
     * @param int $id The bank entry ID
     * @return stdClass|null The updated record or null
     */
    public static function check_hasfiles($id): ?stdClass {
        global $DB, $USER;
        $transaction = $DB->start_delegated_transaction();
        $record = $DB->get_record('paygw_bank', ['id' => $id]);
        if ($record->userid == $USER->id) {
            $record->hasfiles = 1;
            $DB->update_record('paygw_bank', $record);
            $transaction->allow_commit();
            return $record;
        }
        return null;
    }

    /**
     * Approve a payment.
     *
     * @param int $id The bank entry ID
     * @return stdClass The updated record
     */
    public static function aprobe_pay($id): stdClass {
        global $DB, $USER;
        $transaction = $DB->start_delegated_transaction();
        $record = $DB->get_record('paygw_bank', ['id' => $id]);
        $config = (object) payment_helper::get_gateway_configuration(
            $record->component,
            $record->paymentarea,
            $record->itemid,
            'bank'
        );
        $payable = payment_helper::get_payable($record->component, $record->paymentarea, $record->itemid);
        $paymentid = payment_helper::save_payment(
            $payable->get_account_id(),
            $record->component,
            $record->paymentarea,
            $record->itemid,
            (int) $record->userid,
            $record->totalamount,
            $payable->get_currency(),
            'bank'
        );
        $record->timechecked = time();
        $record->status = 'A';
        $record->usercheck = $USER->id;
        $record->paymentid = $paymentid;
        $DB->update_record('paygw_bank', $record);
        payment_helper::deliver_order($record->component, $record->paymentarea, $record->itemid, $paymentid, (int) $record->userid);

        $sendemail = get_config('paygw_bank', 'sendconfmail');
        if ($sendemail) {
            $supportuser = core_user::get_support_user();
            $paymentuser = self::get_user($record->userid);
            $fullname = fullname($paymentuser, true);
            $userlang = $USER->lang;
            $USER->lang = $paymentuser->lang;
            $subject = get_string('mail_confirm_pay_subject', 'paygw_bank');
            $contentmessage = new stdClass();
            $contentmessage->username = $fullname;
            $contentmessage->code = $record->code;
            $contentmessage->concept = $record->description;
            $mailcontent = get_string('mail_confirm_pay', 'paygw_bank', $contentmessage);
            email_to_user($paymentuser, $supportuser, $subject, $mailcontent);
            $USER->lang = $userlang;
        }

        $sendemail = get_config('paygw_bank', 'senconfirmailtosupport');
        $emailaddress = get_config('paygw_bank', 'notificationsaddress');
        if ($sendemail) {
            $supportuser = core_user::get_support_user();
            $subject = get_string('email_notifications_subject_confirm', 'paygw_bank');
            $contentmessage = new stdClass();
            $contentmessage->code = $record->code;
            $contentmessage->concept = $record->description;
            $mailcontent = get_string('email_notifications_confirm', 'paygw_bank', $contentmessage);
            $emailuser = new stdClass();
            $emailuser->email = $emailaddress;
            $emailuser->id = -99;
            email_to_user($emailuser, $supportuser, $subject, $mailcontent);
        }
        $transaction->allow_commit();

        return $record;
    }

    /**
     * Get files for a bank entry.
     *
     * @param int $id The bank entry ID
     * @return array The files
     */
    public static function files($id): array {
        $fs = get_file_storage();
        $files = $fs->get_area_files(\context_system::instance()->id, 'paygw_bank', 'transfer', $id);
        $realfiles = [];
        foreach ($files as $f) {
            if ($f->get_filename() != '.') {
                $realfiles[] = $f;
            }
        }
        return $realfiles;
    }

    /**
     * Get a user by ID.
     *
     * @param int $userid The user ID
     * @return stdClass The user record
     */
    public static function get_user($userid) {
        global $DB;
        return $DB->get_record('user', ['id' => $userid]);
    }

    /**
     * Deny a payment.
     *
     * @param int $id The bank entry ID
     * @return stdClass The updated record
     */
    public static function deny_pay($id): stdClass {
        global $DB, $USER;
        $transaction = $DB->start_delegated_transaction();
        $record = $DB->get_record('paygw_bank', ['id' => $id]);
        $config = (object) payment_helper::get_gateway_configuration(
            $record->component,
            $record->paymentarea,
            $record->itemid,
            'bank'
        );
        $payable = payment_helper::get_payable($record->component, $record->paymentarea, $record->itemid);
        $paymentuser = self::get_user($record->userid);
        $record->timechecked = time();
        $record->status = 'D';
        $record->usercheck = $USER->id;
        $DB->update_record('paygw_bank', $record);

        $sendemail = get_config('paygw_bank', 'senddenmail');
        if ($sendemail) {
            $supportuser = core_user::get_support_user();
            $fullname = fullname($paymentuser, true);
            $userlang = $USER->lang;
            $USER->lang = $paymentuser->lang;
            $subject = get_string('mail_denied_pay_subject', 'paygw_bank');
            $contentmessage = new stdClass();
            $contentmessage->username = $fullname;
            $contentmessage->code = $record->code;
            $contentmessage->concept = $record->description;
            $mailcontent = get_string('mail_denied_pay', 'paygw_bank', $contentmessage);
            email_to_user($paymentuser, $supportuser, $subject, $mailcontent);
            $USER->lang = $userlang;
        }
        $transaction->allow_commit();
        return $record;
    }

    /**
     * Send a denied notification.
     *
     * @param stdClass $request The payment request
     */
    public static function send_denied_notification($request): void {
        $sendemail = get_config('paygw_bank', 'senddenmail');
        if ($sendemail) {
            $supportuser = core_user::get_support_user();
            $paymentuser = self::get_user($request->userid);
            $fullname = fullname($paymentuser, true);
            $subject = get_string('mail_denied_pay_subject', 'paygw_bank');
            $contentmessage = new stdClass();
            $contentmessage->username = $fullname;
            $contentmessage->code = $request->code;
            $contentmessage->concept = $request->description;
            $mailcontent = get_string('mail_denied_pay', 'paygw_bank', $contentmessage);
            email_to_user($paymentuser, $supportuser, $subject, $mailcontent);
        }
    }

    /**
     * Get all pending payments.
     *
     * @return array The pending payments
     */
    public static function get_pending(): array {
        global $DB;
        $records = $DB->get_records('paygw_bank', ['status' => 'P']);
        return $records;
    }

    /**
     * Get pending payments for a user.
     *
     * @param int $userid The user ID
     * @return array The pending payments
     */
    public static function get_user_pending($userid): array {
        global $DB;
        $records = $DB->get_records('paygw_bank', ['status' => 'P', 'userid' => $userid]);
        return $records;
    }

    /**
     * Check if a user has an open bank entry for an item.
     *
     * @param int $itemid The item ID
     * @param int $userid The user ID
     * @return bool True if an open entry exists
     */
    public static function has_openbankentry($itemid, $userid): bool {
        global $DB;
        return $DB->count_records('paygw_bank', ['itemid' => $itemid, 'userid' => $userid, 'status' => 'P']) > 0;
    }

    /**
     * Create a new bank entry.
     *
     * @param int $itemid The item ID
     * @param int $userid The user ID
     * @param float $totalamount The total amount
     * @param string $currency The currency
     * @param string $component The component
     * @param string $paymentarea The payment area
     * @param string $description The description
     * @return stdClass|null The created record or null if already exists
     */
    public static function create_bankentry(
        $itemid,
        $userid,
        $totalamount,
        $currency,
        $component,
        $paymentarea,
        $description
    ): ?stdClass {
        global $DB;
        if (self::has_openbankentry($itemid, $userid)) {
            return null;
        }
        $record = new stdClass();
        $record->itemid = $itemid;
        $record->component = $component;
        $record->paymentarea = $paymentarea;
        $record->description = $description;
        $record->userid = $userid;
        $record->totalamount = $totalamount;
        $record->currency = $currency;
        $record->code = time();
        $record->usercheck = 0;
        $record->status = 'P';
        $record->timecreated = time();

        $id = $DB->insert_record('paygw_bank', $record);
        $record->id = $id;
        $record->code = self::create_code($id);
        $DB->update_record('paygw_bank', $record);

        $sendemail = get_config('paygw_bank', 'sendnewrequestmail');
        $emailaddress = get_config('paygw_bank', 'notificationsaddress');

        if ($sendemail) {
            $supportuser = core_user::get_support_user();
            $subject = get_string('email_notifications_subject_new', 'paygw_bank');
            $contentmessage = new stdClass();
            $contentmessage->code = $record->code;
            $contentmessage->concept = $record->description;
            $mailcontent = get_string('email_notifications_new_request', 'paygw_bank', $contentmessage);
            $emailuser = new stdClass();
            $emailuser->email = $emailaddress;
            $emailuser->id = -99;
            email_to_user($emailuser, $supportuser, $subject, $mailcontent);
        }
        return $record;
    }

    /**
     * Create a code for a bank entry.
     *
     * @param int $id The bank entry ID
     * @return string The code
     */
    public static function create_code($id): string {
        return "code_" . $id;
    }
}
