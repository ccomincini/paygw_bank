# Piano di Upgrade paygw_bank per Moodle 5.0

## Obiettivo
Creare una versione del plugin paygw_bank compatibile con Moodle 5.0, Bootstrap 5 e PHP 8.2-8.4, partendo dal plugin originale UNESCO e integrando funzionalità selezionate dal fork Snickser.

## Requisiti Moodle 5.0
- **PHP**: minimo 8.2.0, supportati 8.2.x, 8.3.x, 8.4.x
- **Bootstrap**: 5.3 (con BS4 backwards-compatibility layer)
- **version.php requires**: 2024100700
- **Estensione sodium**: richiesta

---

## FASE 1: Setup e Preparazione

### 1.1 Aggiornare version.php
```php
$plugin->version   = 2025011300;        // Data odierna
$plugin->requires  = 2024100700;        // Moodle 5.0
$plugin->supported = [500, 500];        // Solo Moodle 5.0
$plugin->component = 'paygw_bank';
$plugin->maturity  = MATURITY_BETA;
$plugin->release   = '2.0.0';
```

### 1.2 Verificare/Aggiornare boilerplate
Tutti i file PHP devono avere il boilerplate corretto Moodle con:
- Commento GPL
- Tag @package, @copyright, @license
- `defined('MOODLE_INTERNAL') || die();` dove appropriato

---

## FASE 2: Aggiornamenti Database

### 2.1 Modificare db/install.xml
Aggiungere campo `canceledbyuser`:
```xml
<FIELD NAME="canceledbyuser" TYPE="int" LENGTH="1" NOTNULL="false" SEQUENCE="false"/>
```

### 2.2 Creare db/upgrade.php
Aggiungere migration per upgrade da versioni precedenti:
- Aggiungere campo `canceledbyuser` se non esiste

---

## FASE 3: Funzionalità da Snickser

### 3.1 Auto-deny (Rifiuto automatico)
**File coinvolti:**
- `classes/gateway.php` - Aggiungere campo configurazione `autodeny`
- `classes/task/autodeny_task.php` - NUOVO: Scheduled task per auto-rifiuto
- `db/tasks.php` - NUOVO: Registrazione task
- `lang/en/paygw_bank.php` - Stringhe per autodeny

**Logica:**
- Campo `autodeny` in configurazione gateway (duration in secondi, 0 = disabilitato)
- Task schedulato che controlla richieste pendenti
- Se `timecreated + autodeny < time()` AND `status = 'P'` → rifiuta automaticamente

### 3.2 Notifiche avanzate
**File coinvolti:**
- `classes/gateway.php` - Aggiungere checkbox configurazione
- `lib.php` o nuova classe `classes/notification_helper.php`
- `lang/en/paygw_bank.php` - Stringhe

**Funzionalità:**
- `sendnewrequestmail`: Email admin quando nuova richiesta
- `sendnewattachmentsmail`: Email admin quando aggiunti file
- `sendconfirmailtosupport`: Email admin quando approvato

### 3.3 Gestione file migliorata
**File coinvolti:**
- `classes/attachtransfer_form.php` - Modificare per sostituzione file
- `manage.php` - Aggiungere preview PDF modal, archivio, eliminazione
- `templates/` - Template per modal preview
- `amd/src/` - JavaScript per modal PDF

**Funzionalità:**
- Preview PDF in modal Bootstrap 5
- Struttura nomi file unificata: `{code}_{userid}_{timestamp}_{originalname}`
- Possibilità utente di sostituire file caricati
- Archivio file approvati con opzione eliminazione

---

## FASE 4: Compatibilità Bootstrap 5

### 4.1 Aggiornare attributi data-*
Sostituire tutti i `data-toggle`, `data-target`, `data-dismiss` con equivalenti BS5:
- `data-toggle` → `data-bs-toggle`
- `data-target` → `data-bs-target`
- `data-dismiss` → `data-bs-dismiss`

### 4.2 Aggiornare classi CSS
- `.badge-*` → `.text-bg-*`
- `.badge-pill` → `.rounded-pill`
- `.float-left/right` → `.float-start/end`
- `.ml-*/mr-*` → `.ms-*/me-*`
- `.pl-*/pr-*` → `.ps-*/pe-*`
- `.dropdown-menu-left/right` → `.dropdown-menu-start/end`

### 4.3 Modal Bootstrap 5
Usare la nuova API:
```javascript
import ModalFactory from 'core/modal_factory';
// oppure per PDF preview
import Modal from 'core/modal';
```

---

## FASE 5: Compatibilità PHP 8.2-8.4

### 5.1 Verificare deprecazioni
- Nessun uso di `${var}` in stringhe (deprecated PHP 8.2)
- Nessun uso di `utf8_encode/decode` (deprecated PHP 8.2)
- Verificare nullable types corretti
- Verificare return types dichiarati

### 5.2 Tipizzazione moderna
- Aggiungere type hints dove mancanti
- Usare union types dove appropriato (es: `string|null`)

---

## FASE 6: File di lingua

### 6.1 Requisiti
- Stringhe in ordine alfabetico
- Nessun commento nel file
- Boilerplate corretto

### 6.2 Nuove stringhe da aggiungere
```php
$string['allow_users_cancel_payments'] = 'Allow users cancel payments';
$string['autodeny'] = 'Time to auto decline unconfirmed payment requests';
$string['autodeny_help'] = 'The time after which unapproved applications will be automatically rejected.';
$string['bank:manageincourse'] = 'Manage Transfers in course';
$string['cancel_process'] = 'Cancel process';
$string['are_you_sure_cancel'] = 'Are you sure you want to cancel the payment process?';
$string['payment_denied'] = 'You have canceled the payment';
$string['pendingrequests'] = 'All requests';
$string['send_new_request_mail'] = 'Send email for every new request';
$string['send_new_attachments_mail'] = 'Send email when new files are uploaded';
$string['send_confirm_mail_to_support'] = 'Send email when a payment is approved';
$string['unpaidnotice'] = 'Expired!';
$string['unpaidtimeend'] = 'Expiration date';
// ... altre stringhe necessarie
```

---

## FASE 7: Test e Validazione

### 7.1 Test funzionali
- [ ] Creazione nuova richiesta di pagamento
- [ ] Upload file
- [ ] Sostituzione file
- [ ] Preview PDF in modal
- [ ] Approvazione pagamento
- [ ] Rifiuto pagamento
- [ ] Auto-deny dopo timeout
- [ ] Notifiche email
- [ ] Cancellazione da parte utente

### 7.2 Test compatibilità
- [ ] PHP 8.2
- [ ] PHP 8.3
- [ ] PHP 8.4
- [ ] Moodle 5.0
- [ ] Bootstrap 5 rendering

### 7.3 Code checker
```bash
php admin/cli/phpunit.php --filter paygw_bank
php admin/tool/phpunit/cli/util.php --buildconfig
# Codechecker Moodle
php local/codechecker/cli/moodlecheck.php payment/gateway/bank
```

---

## Struttura file finale

```
payment/gateway/bank/
├── amd/
│   ├── build/
│   │   ├── gateways_modal.min.js
│   │   ├── pdf_preview.min.js          # NUOVO
│   │   └── repository.min.js
│   └── src/
│       ├── gateways_modal.js
│       ├── pdf_preview.js              # NUOVO
│       └── repository.js
├── classes/
│   ├── attachtransfer_form.php
│   ├── bank_helper.php
│   ├── external/
│   │   └── get_config_for_js.php
│   ├── gateway.php
│   ├── notification_helper.php         # NUOVO
│   ├── pay_form.php
│   ├── privacy/
│   │   └── provider.php
│   └── task/
│       └── autodeny_task.php           # NUOVO
├── db/
│   ├── access.php
│   ├── install.php
│   ├── install.xml
│   ├── services.php
│   ├── tasks.php                       # NUOVO
│   └── upgrade.php
├── lang/
│   └── en/
│       └── paygw_bank.php
├── pix/
│   └── img.svg
├── templates/
│   ├── bank_button_placeholder.mustache
│   └── pdf_preview_modal.mustache      # NUOVO
├── lib.php
├── manage.php
├── my_pending_pay.php
├── pay.php
├── settings.php
├── styles.css
└── version.php
```

---

## Comandi Claude Code

Eseguire in ordine:

1. **Backup originale**
   ```bash
   cp -r /Users/carlo/Projects/moodle-development/plugins/bank /Users/carlo/Projects/moodle-development/plugins/bank_backup_original
   ```

2. **Per ogni fase, commit separato**
   ```bash
   git add -A && git commit -m "FASE X: descrizione"
   ```

---

## Note importanti

1. **NON includere** dal fork Snickser:
   - autocommit/delayautocommit
   - unfixcost/suggest/maxcost
   - sendteachermail/onlyingroup
   - Supporto enrol_yafee/enrol_wallet
   - Link donazioni nel codice

2. **Testare SEMPRE** su ambiente Moodle 5.0 prima del deploy

3. **Mantenere retrocompatibilità** database per upgrade da versioni precedenti
