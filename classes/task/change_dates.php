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
 * Transfers search data in cases where websites have had their start dates
 * changed.
 *
 * @package local_ousearch
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ousearch\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Transfers search data in cases where websites have had their start dates
 * changed.
 */
class change_dates extends \core\task\scheduled_task {

    /**
     * Get task name.
     *
     * @return string task name.
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('task_change_dates', 'local_ousearch');
    }

    /**
     * Execute task.
     */
    public function execute() {
        try {
            \local_ousearch\year_tables::task_change_dates();
        } catch (\moodle_exception $e) {
            // It is OK to throw exceptions, but doing so does not actually
            // display the exception in cron log.
            mtrace('Exception occurred:');
            mtrace($e->getMessage());
            mtrace($e->getTraceAsString());
            throw $e;
        }
    }
}
