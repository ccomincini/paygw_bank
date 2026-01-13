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
 * Library functions for paygw_bank.
 *
 * @package    paygw_bank
 * @copyright  2022 UNESCO IESALC https://iesalc.unesco.org/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add bank payment nodes to the user profile navigation.
 *
 * @param core_user\output\myprofile\tree $tree The navigation tree
 * @param stdClass $user The user object
 * @param bool $iscurrentuser Whether this is the current user
 * @param stdClass $course The course object
 */
function paygw_bank_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    $url = new moodle_url('/payment/gateway/bank/my_pending_pay.php');
    $category = new core_user\output\myprofile\category('payments', get_string('payments', 'paygw_bank'), null);
    $node = new core_user\output\myprofile\node(
        'payments',
        'my_pending_payments',
        get_string('my_pending_payments', 'paygw_bank'),
        null,
        $url
    );
    $tree->add_category($category);
    $tree->add_node($node);
}

/**
 * Serve the files from the paygw_bank file areas.
 *
 * @param stdClass $course The course object
 * @param stdClass $cm The course module object
 * @param context $context The context
 * @param string $filearea The name of the file area
 * @param array $args Extra arguments (itemid, path)
 * @param bool $forcedownload Whether or not force download
 * @param array $options Additional options affecting the file serving
 * @return bool False if the file not found, just send the file otherwise
 */
function paygw_bank_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($filearea !== 'transfer') {
        return false;
    }

    // Make sure the user is logged in.
    require_login();

    // The first item in the $args array.
    $itemid = array_shift($args);

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'paygw_bank', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
