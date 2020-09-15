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
 * Superframe: prepare block_data for page.
 *
 * @package    report_wikipages
 * @copyright  2020 Richard Jones <richardnz@outlook.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_wikipages\local;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class page_data implements renderable, templatable {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return \stdClass|array
     */

    protected $records;
    protected $context;
    protected $wid;

    public function __construct($wid, $cid, $context) {

        $this->records = self::fetch_page_data($wid, $cid);
        $this->context = $context;
        $this->wid = $wid;
    }

    public function export_for_template(renderer_base $output) {
        // Prepare the data for the template.
        $table = new stdClass();
        // Table headers.
        $table->tableheaders = [
                get_string('id', 'report_wikipages'),
                get_string('firstname', 'core'),
                get_string('lastname', 'core'),
                get_string('pagetitle', 'report_wikipages'),
                get_string('pagecontent', 'report_wikipages'),
                get_string('date', 'report_wikipages'),

        ];
        // Build the data rows.
        foreach ($this->records as $record) {
            $data = array();
            $data['id'] = $record->id;
            $data['firstname'] = $record->firstname;
            $data['lastname'] = $record->lastname;
            $data['pagetitle'] = $record->title;
            $content = $record->cachedcontent;
            $content = file_rewrite_pluginfile_urls($content, 'pluginfile.php',
                    $this->context->id, 'mod_wiki', 'attachments', $this->wid);
            $data['content'] = format_text($content, FORMAT_MOODLE, ['overflowdiv' => true,
                    'allowid' => true]);
            $data['date'] = $record->timemodified;
            $table->tabledata[] = $data;
        }

        return $table;
    }

    public static function fetch_page_data($wid, $cid) {
        global $DB;
        $sql = "SELECT p.id, p.title, p.cachedcontent, p.timemodified, p.pageviews, p.subwikiid,
                       u.firstname, u.lastname, u.deleted
                FROM {wiki_pages} p
                JOIN {wiki} w ON p.subwikiid = w.id
                JOIN {user} u ON p.userid = u.id
                JOIN {course} c ON w.course = c.id
                WHERE p.subwikiid = :wid
                AND course = :cid
                AND u.deleted = 0
                ORDER BY u.lastname";
        return $DB->get_records_sql($sql, ['wid' => $wid, 'cid' => $cid]);
    }
}
