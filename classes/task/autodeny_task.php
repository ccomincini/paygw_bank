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
 * @copyright  2025 Luca Bösch <luca.boesch@bfh.ch>
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

            $autodeny = (int) $config['autodeny'];
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
