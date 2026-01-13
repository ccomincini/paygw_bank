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
 * Strings for component 'paygw_bank', language 'it'.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['additional_currencies'] = 'Valute aggiuntive';
$string['additional_currencies_help'] = 'Un elenco di codici valuta separati da virgola. Puoi consultare i codici su https://en.wikipedia.org/wiki/ISO_4217#Active_codes';
$string['allow_users_add_files'] = 'Consenti agli utenti di aggiungere file';
$string['allow_users_cancel_payments'] = 'Consenti agli utenti di annullare i pagamenti';
$string['approve'] = 'Approva';
$string['are_you_sure_cancel'] = 'Sei sicuro di voler annullare il processo di pagamento?';
$string['autodeny'] = 'Rifiuto automatico dopo';
$string['autodeny_help'] = 'Rifiuta automaticamente le richieste di pagamento non confermate dopo questo periodo di tempo. Imposta a 0 per disabilitare.';
$string['bank:managepayments'] = 'Gestisci pagamenti tramite bonifico';
$string['cancel_process'] = 'Annulla processo';
$string['close'] = 'Chiudi';
$string['code'] = 'Codice';
$string['concept'] = 'Causale';
$string['cost'] = 'Costo';
$string['deny'] = 'Rifiuta';
$string['email_notifications'] = 'Notifiche email';
$string['email_notifications_confirm'] = 'Il pagamento con codice {$a->code} è stato approvato';
$string['email_notifications_help'] = 'Un indirizzo email esterno può essere notificato quando viene accodato un nuovo pagamento o quando cambia lo stato';
$string['email_notifications_new_attachments'] = 'Il pagamento con codice {$a->code} ha nuovi allegati';
$string['email_notifications_new_request'] = 'È stata ricevuta una nuova richiesta di pagamento. Codice: {$a->code}';
$string['email_notifications_subject_attachments'] = 'Un pagamento ha nuovi allegati';
$string['email_notifications_subject_confirm'] = 'Un pagamento è stato approvato';
$string['email_notifications_subject_new'] = 'Nuova richiesta di pagamento tramite bonifico';
$string['email_to_notify'] = 'Indirizzo email per le notifiche';
$string['file_already_uploaded'] = 'File già caricato';
$string['file_uploaded'] = 'File caricato';
$string['gatewaydescription'] = 'Il bonifico bancario è un gateway di pagamento per elaborare pagamenti manuali.';
$string['gatewayname'] = 'Bonifico bancario';
$string['hasfiles'] = 'Ha file allegati';
$string['instructionstext'] = 'Istruzioni mostrate prima di accettare il pagamento tramite bonifico';
$string['internalerror'] = 'Si è verificato un errore interno. Contattaci.';
$string['mail_confirm_pay'] = 'Gentile {$a->username}, il tuo pagamento per "{$a->concept}" è stato confermato. Codice: {$a->code}';
$string['mail_confirm_pay_subject'] = 'Pagamento confermato';
$string['mail_denied_pay'] = 'Gentile {$a->username}, il tuo pagamento per "{$a->concept}" è stato rifiutato. Codice: {$a->code}';
$string['mail_denied_pay_subject'] = 'Pagamento rifiutato';
$string['manage'] = 'Gestisci bonifici';
$string['managepayments'] = 'Gestisci bonifici';
$string['max_number_of_files'] = 'Numero massimo di file';
$string['my_pending_payments'] = 'I miei pagamenti in attesa';
$string['noentriesfound'] = 'Nessuna voce trovata';
$string['payment_denied'] = 'Hai annullato il pagamento';
$string['payments'] = 'Pagamenti';
$string['pending_payments'] = 'Pagamenti in attesa';
$string['pluginname'] = 'Bonifico bancario';
$string['pluginname_desc'] = 'Il plugin Bonifico bancario consente il pagamento dei corsi tramite bonifico bancario o altri metodi di pagamento manuali.';
$string['postinstructionstext'] = 'Istruzioni mostrate dopo aver accettato il pagamento tramite bonifico';
$string['privacy:metadata'] = 'Il plugin Bonifico bancario non memorizza dati personali.';
$string['send_confirm_mail_to_support'] = 'Invia email quando un pagamento viene approvato';
$string['send_confirmation_mail'] = 'Invia email di conferma all\'utente';
$string['send_denied_mail'] = 'Invia email di rifiuto all\'utente';
$string['send_new_attachments_mail'] = 'Invia email quando vengono caricati nuovi file';
$string['send_new_request_mail'] = 'Invia email per ogni nuova richiesta';
$string['start_process'] = 'Avvia processo';
$string['surcharge_info'] = 'Questo metodo di pagamento ha un sovrapprezzo di {$a}.';
$string['task_autodeny'] = 'Rifiuto automatico richieste di pagamento scadute';
$string['the_price_is'] = 'Il prezzo totale è {$a}.';
$string['total_cost'] = 'Costo totale';
$string['transfer_code'] = 'Codice bonifico';
$string['transfer_code_explanation'] = 'Questo è il codice da inserire nella causale del bonifico: {$a}';
$string['transfer_process_initiated'] = 'Processo di bonifico avviato';
$string['unpaidnotice'] = 'Scaduto';
$string['unpaidtimeend'] = 'Data di scadenza';
