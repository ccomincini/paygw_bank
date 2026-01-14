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
 * Strings for component 'paygw_bank', language 'en'.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['additional_currencies'] = 'Additional currencies';
$string['additional_currencies_help'] = 'A comma separated list of currency codes. You can consult the codes at https://en.wikipedia.org/wiki/ISO_4217#Active_codes';
$string['allow_users_add_files'] = 'Allow users to add files';
$string['allow_users_cancel_payments'] = 'Allow users to cancel payments';
$string['approve'] = 'Approve';
$string['are_you_sure_cancel'] = 'Are you sure you want to cancel the payment process?';
$string['autodeny'] = 'Auto-deny after';
$string['autodeny_help'] = 'Automatically deny payment requests that have not been confirmed after this time period. Set to 0 to disable.';
$string['bank:managepayments'] = 'Manage bank transfer payments';
$string['cancel_process'] = 'Cancel process';
$string['close'] = 'Close';
$string['code'] = 'Code';
$string['concept'] = 'Concept';
$string['cost'] = 'Cost';
$string['deny'] = 'Deny';
$string['email_notifications'] = 'Email notifications';
$string['email_notifications_confirm'] = 'A payment has been approved.

Code: {$a->code}
User: {$a->userfullname}
Email: {$a->useremail}
Item: {$a->concept}
Amount: {$a->amount} {$a->currency}
Date: {$a->date}';
$string['email_notifications_help'] = 'An external email address can be notified when a new payment is queued or when status changes';
$string['email_notifications_new_attachments'] = 'The bank payment entry with code {$a->code} has new attachments';
$string['email_notifications_new_request'] = 'A new bank payment request has been received.

Code: {$a->code}
User: {$a->userfullname}
Email: {$a->useremail}
Item: {$a->concept}
Amount: {$a->amount} {$a->currency}';
$string['email_notifications_subject_attachments'] = 'A payment entry has new attachments';
$string['email_notifications_subject_confirm'] = 'Payment approved - {$a->code}';
$string['email_notifications_subject_new'] = 'New bank payment entry';
$string['email_to_notify'] = 'Email address for notifications';
$string['file_already_uploaded'] = 'File already uploaded';
$string['file_uploaded'] = 'File uploaded';
$string['gatewaydescription'] = 'Bank transfer is a payment gateway for processing manual payments.';
$string['gatewayname'] = 'Bank transfer';
$string['hasfiles'] = 'Has files';
$string['instructionstext'] = 'Instructions shown before accepting transfer payment';
$string['internalerror'] = 'An internal error has occurred. Please contact us.';
$string['mail_confirm_pay'] = 'Dear {$a->username},

Your payment has been confirmed.

Code: {$a->code}
Item: {$a->concept}
Amount: {$a->amount} {$a->currency}
Date: {$a->date}

You can now access your purchased content.

Best regards,
{$a->sitename}';
$string['mail_confirm_pay_subject'] = 'Payment confirmed - {$a->code}';
$string['mail_denied_pay'] = 'Dear {$a->username},

Your payment request has been denied.

Code: {$a->code}
Item: {$a->concept}
Amount: {$a->amount} {$a->currency}

Please contact support for more information.

Best regards,
{$a->sitename}';
$string['mail_denied_pay_subject'] = 'Payment denied - {$a->code}';
$string['manage'] = 'Manage transfers';
$string['managepayments'] = 'Manage transfers';
$string['max_number_of_files'] = 'Maximum number of files';
$string['my_pending_payments'] = 'My pending transfer payments';
$string['noentriesfound'] = 'No entries found';
$string['payment_denied'] = 'You have cancelled the payment';
$string['payments'] = 'Payments';
$string['pending_payments'] = 'Pending transfer payments';
$string['pluginname'] = 'Bank transfer';
$string['pluginname_desc'] = 'The bank transfer plugin allows payment for courses via bank transfer or other manual payment methods.';
$string['postinstructionstext'] = 'Instructions shown after accepting transfer payment';
$string['privacy:metadata'] = 'The bank transfer plugin does not store any personal data.';
$string['send_confirm_mail_to_support'] = 'Send email when a payment is approved';
$string['send_confirmation_mail'] = 'Send confirmation email to user';
$string['send_denied_mail'] = 'Send denial email to user';
$string['send_new_attachments_mail'] = 'Send email when new files are uploaded';
$string['send_new_request_mail'] = 'Send email for every new request';
$string['start_process'] = 'Start process';
$string['surcharge_info'] = 'This payment method has a surcharge of {$a}.';
$string['task_autodeny'] = 'Auto-deny expired payment requests';
$string['the_price_is'] = 'The total price is {$a}.';
$string['total_cost'] = 'Total cost';
$string['transfer_code'] = 'Transfer code';
$string['transfer_code_explanation'] = 'This is your code to include in your transfer concept: {$a}';
$string['transfer_process_initiated'] = 'Transfer process initiated';
$string['unpaidnotice'] = 'Expired';
$string['unpaidtimeend'] = 'Expiration date';
