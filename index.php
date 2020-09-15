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
 * A simple report plugin example
 *
 * @package   report_wikipages
 * @copyright  2020 Richard Jones {@link https://richardnz.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Modified from MoodleBites for Developers Level 1
 * by Richard Jones.
 */

use report_wikipages\local\page_data;
require('../../config.php');
global $DB;
$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id]);
if (!$course) {
    print_error('invalidcourseid');
}

$context = context_course::instance($course->id);
$url = new moodle_url('/report/wikipages/index.php', ['course' => $id]);

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

require_login($course);

// Check basic permission.
require_capability('report/wikipages:view',$context);

// Check for wikis.
$wikis = $DB->get_records('wiki', ['course' => $id], null, 'id');

echo $OUTPUT->header();
foreach ($wikis as $wiki) {
    echo $OUTPUT->render(new page_data($wiki->id, $id, $context));
}
echo $OUTPUT->footer();