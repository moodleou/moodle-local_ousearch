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
 * Event handler class.
 *
 * @package local_ousearch
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ousearch;

defined('MOODLE_INTERNAL') || die;

/**
 * Class that handles events.
 */
abstract class event_handler {
    /**
     * Called when course_updated event happens.
     *
     * @param \core\event\course_updated $event Event data object
     */
    public static function course_updated(\core\event\course_updated $event) {
        year_tables::handle_updated_course($event->get_data()['objectid']);
    }

    /**
     * Called when course_restored event happens.
     *
     * @param \core\event\course_restored $event Event data object
     */
    public static function course_restored(\core\event\course_restored $event) {
        year_tables::handle_new_course($event->get_data()['objectid']);
    }

    /**
     * Called when course_created event happens.
     *
     * @param \core\event\course_created $event Event data object
     */
    public static function course_created(\core\event\course_created $event) {
        year_tables::handle_new_course($event->get_data()['objectid']);
    }
}
