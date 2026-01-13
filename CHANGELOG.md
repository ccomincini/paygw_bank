# Changelog - paygw_bank

## [2.0.0] - 2025-01-14

Upgrade completo del plugin per Moodle 5.0, Bootstrap 5 e PHP 8.2+.

### Modifiche principali

#### Compatibilità
- Aggiornato `version.php` per Moodle 5.0 (`requires = 2024100700`, `supported = [500, 500]`)
- Aggiornato copyright: UNESCO IESALC (2022) + Invisiblefarm s.r.l. - Carlo Comincini (2025)

#### Database
- Aggiunto campo `canceledbyuser` in `db/install.xml`
- Creato `db/upgrade.php` per la migrazione del database
- Creato `db/tasks.php` per il task schedulato

#### Nuove funzionalità
- **Auto-deny task** (`classes/task/autodeny_task.php`): rifiuto automatico delle richieste di pagamento scadute
- Configurazione autodeny nel gateway (campo duration)
- Nuove opzioni di notifica email nel gateway

#### Bootstrap 5
- Aggiornati attributi da `data-*` a `data-bs-*` in tutti i template e file PHP
- Creato template `templates/pdf_preview_modal.mustache` per preview PDF
- Aggiornato modal in `manage.php` con sintassi Bootstrap 5

#### Correzioni PHPCS
- Rimosso righe vuote dopo `{` nelle classi (PSR12)
- Suddivise righe troppo lunghe (max 132 caratteri)
- Aggiunto boilerplate corretto a tutti i file PHP
- Aggiunto docblock file-level dove mancante

#### File di lingua
- Aggiornato `lang/en/paygw_bank.php` con nuove stringhe in ordine alfabetico
- Creato `lang/it/paygw_bank.php` (traduzione italiana completa)
- Aggiunta stringa `close` per i modal

#### AMD/JavaScript
- Rigenerati file build con Grunt (`amd/build/*.min.js` e `*.min.js.map`)

#### CI/CD
- Creato `.github/workflows/ci.yml` per GitHub Actions
- Test automatici: PHPUnit, Behat, PHPCS, PHPDoc, Grunt

### File modificati
- `version.php`
- `manage.php`
- `pay.php`
- `my_pending_pay.php`
- `lib.php`
- `settings.php`
- `gateway.php`
- `db/install.xml`
- `db/install.php`
- `db/access.php`
- `db/services.php`
- `db/upgrade.php` (nuovo)
- `db/tasks.php` (nuovo)
- `classes/gateway.php`
- `classes/bank_helper.php`
- `classes/pay_form.php`
- `classes/attachtransfer_form.php`
- `classes/privacy/provider.php`
- `classes/external/get_config_for_js.php`
- `classes/task/autodeny_task.php` (nuovo)
- `lang/en/paygw_bank.php`
- `lang/it/paygw_bank.php` (nuovo)
- `templates/pdf_preview_modal.mustache` (nuovo)
- `amd/build/*` (rigenerati)
- `.github/workflows/ci.yml` (nuovo)

### Repository
- Fork da: https://github.com/unesco-iesalc/paygw_bank
- Nuovo repo: https://github.com/ccomincini/moodle-paygw_bank

### Note di deployment
```bash
# Aggiornare il remote sul server
cd /path/to/moodle/payment/gateway/bank
git remote set-url origin git@github.com:ccomincini/moodle-paygw_bank.git
git fetch origin
git reset --hard origin/main

# Purge caches Moodle
php admin/cli/purge_caches.php
```
