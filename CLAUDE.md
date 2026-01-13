# CLAUDE.md - Istruzioni per Claude Code

## Progetto: Upgrade paygw_bank per Moodle 5.0

### Percorso plugin
`/Users/carlo/Projects/moodle-development/plugins/bank`

### Obiettivo
Aggiornare il plugin paygw_bank per compatibilità con Moodle 5.0, Bootstrap 5, PHP 8.2-8.4.

---

## REGOLE GENERALI MOODLE

### Boilerplate obbligatorio per tutti i file PHP
```php
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
 * Descrizione del file.
 *
 * @package    paygw_bank
 * @copyright  2025 Il Tuo Nome <email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
```

### File di lingua
- Stringhe in **ordine alfabetico**
- **Nessun commento** nel file
- Solo boilerplate iniziale e array $string

### Stile codice
- Indentazione: 4 spazi
- Parentesi graffe su nuova riga per funzioni/classi
- Type hints PHP 8.x dove possibile
- Nessun uso di `${var}` nelle stringhe (deprecated PHP 8.2)

---

## FASE 1: version.php e boilerplate

### 1.1 Aggiornare version.php
Sostituire il contenuto con:

```php
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
 * Version information for paygw_bank.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @author     2025 Luca Bösch <luca.boesch@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2025011300;
$plugin->requires  = 2024100700;
$plugin->supported = [500, 500];
$plugin->component = 'paygw_bank';
$plugin->maturity  = MATURITY_BETA;
$plugin->release   = '2.0.0';
```

---

## FASE 2: Database

### 2.1 Aggiornare db/install.xml
Aggiungere il campo `canceledbyuser` prima della chiusura di `</FIELDS>`:

```xml
<FIELD NAME="canceledbyuser" TYPE="int" LENGTH="1" NOTNULL="false" SEQUENCE="false" COMMENT="1 if canceled by user"/>
```

### 2.2 Creare/Aggiornare db/upgrade.php

```php
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
 * Upgrade steps for paygw_bank.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the paygw_bank plugin.
 *
 * @param int $oldversion The old version of the plugin
 * @return bool
 */
function xmldb_paygw_bank_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025011300) {
        // Define field canceledbyuser to be added to paygw_bank.
        $table = new xmldb_table('paygw_bank');
        $field = new xmldb_field('canceledbyuser', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'timechecked');

        // Conditionally launch add field canceledbyuser.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Bank savepoint reached.
        upgrade_plugin_savepoint(true, 2025011300, 'paygw', 'bank');
    }

    return true;
}
```

---

## FASE 3: Task autodeny

### 3.1 Creare db/tasks.php

```php
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
 * Scheduled tasks for paygw_bank.
 *
 * @package    paygw_bank
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'paygw_bank\task\autodeny_task',
        'blocking' => 0,
        'minute' => '*/15',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];
```

### 3.2 Creare classes/task/autodeny_task.php

```php
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

namespace paygw_bank\task;

use core\task\scheduled_task;
use core_payment\helper;

/**
 * Scheduled task to auto-deny expired payment requests.
 *
 * @package    paygw_bank
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class autodeny_task extends scheduled_task {

    /**
     * Return the task's name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_autodeny', 'paygw_bank');
    }

    /**
     * Execute the task.
     */
    public function execute(): void {
        global $DB;

        // Get all pending payment requests.
        $pendingrequests = $DB->get_records('paygw_bank', ['status' => 'P']);

        foreach ($pendingrequests as $request) {
            // Get gateway configuration for this payment.
            try {
                $config = helper::get_gateway_configuration(
                    $request->component,
                    $request->paymentarea,
                    $request->itemid,
                    'bank'
                );
            } catch (\Exception $e) {
                continue;
            }

            // Check if autodeny is configured.
            if (empty($config['autodeny'])) {
                continue;
            }

            $autodeny = (int)$config['autodeny'];
            $expirytime = $request->timecreated + $autodeny;

            // If expired, deny the request.
            if (time() > $expirytime) {
                $request->status = 'D';
                $request->timechecked = time();
                $DB->update_record('paygw_bank', $request);

                // Send notification if configured.
                if (!empty(get_config('paygw_bank', 'senddenmail'))) {
                    \paygw_bank\bank_helper::send_denied_notification($request);
                }

                mtrace("Auto-denied payment request ID: {$request->id}, code: {$request->code}");
            }
        }
    }
}
```

---

## FASE 4: Aggiornare gateway.php

Aggiungere alla funzione `add_configuration_to_gateway_form()`:

```php
// Auto-deny configuration.
$mform->addElement('duration', 'autodeny', get_string('autodeny', 'paygw_bank'), ['optional' => true]);
$mform->addHelpButton('autodeny', 'autodeny', 'paygw_bank');

// Notification settings.
$mform->addElement('advcheckbox', 'sendnewrequestmail', get_string('send_new_request_mail', 'paygw_bank'));
$mform->addElement('advcheckbox', 'sendnewattachmentsmail', get_string('send_new_attachments_mail', 'paygw_bank'));
$mform->addElement('advcheckbox', 'sendconfirmailtosupport', get_string('send_confirm_mail_to_support', 'paygw_bank'));
```

---

## FASE 5: Bootstrap 5 - Template modal PDF

### 5.1 Creare templates/pdf_preview_modal.mustache

```mustache
{{!
    This file is part of Moodle - http://moodle.org/

    Moodle is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Moodle is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
}}
{{!
    @template paygw_bank/pdf_preview_modal

    PDF preview modal template.

    Context variables required for this template:
    * pdfurl - URL of the PDF file
    * filename - Name of the file

    Example context (json):
    {
        "pdfurl": "https://example.com/file.pdf",
        "filename": "document.pdf"
    }
}}
<div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfPreviewModalLabel">{{filename}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{#str}}close, core{{/str}}"></button>
            </div>
            <div class="modal-body p-0">
                <iframe src="{{pdfurl}}" style="width: 100%; height: 80vh; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <a href="{{pdfurl}}" class="btn btn-primary" download="{{filename}}">
                    {{#str}}download, core{{/str}}
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{#str}}close, core{{/str}}
                </button>
            </div>
        </div>
    </div>
</div>
```

---

## FASE 6: File di lingua (ORDINE ALFABETICO!)

Sostituire completamente `lang/en/paygw_bank.php`:

```php
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
$string['code'] = 'Code';
$string['concept'] = 'Concept';
$string['cost'] = 'Cost';
$string['deny'] = 'Deny';
$string['email_notifications'] = 'Email notifications';
$string['email_notifications_confirm'] = 'The bank payment entry with code {$a->code} has been approved';
$string['email_notifications_help'] = 'An external email address can be notified when a new payment is queued or when status changes';
$string['email_notifications_new_attachments'] = 'The bank payment entry with code {$a->code} has new attachments';
$string['email_notifications_new_request'] = 'There is a new bank payment request. Code: {$a->code}';
$string['email_notifications_subject_attachments'] = 'A payment entry has new attachments';
$string['email_notifications_subject_confirm'] = 'A payment entry has been approved';
$string['email_notifications_subject_new'] = 'New bank payment entry';
$string['email_to_notify'] = 'Email address for notifications';
$string['file_already_uploaded'] = 'File already uploaded';
$string['file_uploaded'] = 'File uploaded';
$string['gatewaydescription'] = 'Bank transfer is a payment gateway for processing manual payments.';
$string['gatewayname'] = 'Bank transfer';
$string['hasfiles'] = 'Has files';
$string['instructionstext'] = 'Instructions shown before accepting transfer payment';
$string['internalerror'] = 'An internal error has occurred. Please contact us.';
$string['mail_confirm_pay'] = 'Dear {$a->username}. Your payment for "{$a->concept}" has been confirmed. Code: {$a->code}';
$string['mail_confirm_pay_subject'] = 'Payment confirmed';
$string['mail_denied_pay'] = 'Dear {$a->username}. Your payment for "{$a->concept}" has been denied. Code: {$a->code}';
$string['mail_denied_pay_subject'] = 'Payment denied';
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
```

---

## COMANDI DA ESEGUIRE

```bash
# 1. Vai nella directory del plugin
cd /Users/carlo/Projects/moodle-development/plugins/bank

# 2. Dopo ogni fase, verifica sintassi PHP
php -l version.php
php -l db/upgrade.php
php -l classes/task/autodeny_task.php

# 3. Se hai accesso a Moodle, esegui codechecker
php /path/to/moodle/local/codechecker/cli/moodlecheck.php /path/to/payment/gateway/bank
```

---

## CHECKLIST FINALE

- [ ] version.php aggiornato con requires 2024100700
- [ ] Tutti i file PHP hanno boilerplate corretto
- [ ] db/install.xml ha campo canceledbyuser
- [ ] db/upgrade.php gestisce migrazione
- [ ] db/tasks.php registra autodeny_task
- [ ] classes/task/autodeny_task.php creato
- [ ] gateway.php ha campi autodeny e notifiche
- [ ] Template modal usa Bootstrap 5 (data-bs-*)
- [ ] lang/en/paygw_bank.php in ordine alfabetico senza commenti
- [ ] Nessun uso di ${var} nelle stringhe
- [ ] Nessun uso di funzioni deprecated PHP 8.2+
