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
 * Lang strings.
 * @package local
 * @subpackage ousearch
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['ousearch'] = 'OU search';
$string['searchfor'] = 'Search: {$a}';
$string['untitled'] = '(Untitled)';
$string['searchresultsfor'] = 'Search results for <strong>{$a}</strong>';
$string['noresults'] = 'Could not find any matching results. Try using different words or removing
words from your query.';
$string['nomoreresults'] = 'Could not find any more results.';
$string['previousresults'] = 'Back to results {$a}';
$string['findmoreresults'] = 'More results';
$string['searchtime'] = 'Search time: {$a}s';
$string['resultsfail'] = 'Could not find any results including the word <strong>{$a}</strong>. Try
using different words.';
$string['remote'] = 'Remote search IP allow';
$string['configremote'] = 'List of IP addresses that are permitted to use the remote search facility.
This should be a list of zero or more numeric IP addresses, comma-separated. Be careful! Requests
from these IP addresses can search (and see summary text) as if they were any user. The default,
blank, prevents this access.';
$string['displayversion'] = 'OU search version: <strong>{$a}</strong>';
$string['nowordsinquery'] = 'Enter some words in the search box.';
$string['reindex'] = 'Re-indexing documents for module {$a->module} on course {$a->courseid}';

$string['fastinserterror'] = 'Failed to insert search data (high performance insert)';
$string['remotewrong'] = 'Remote search access is not configured (or not correctly configured).';
$string['remotenoaccess'] = 'This IP address does not have access to remote search';
$string['pluginname'] = $string['ousearch'];
$string['restofwebsite'] = 'Search the rest of this website';
$string['toomanyterms'] = '<strong>You have entered too many search terms (words).</strong> To ensure that search results can be displayed promptly, the system is limited to a maximum of {$a} search terms. Please press the Back button and modify your search.';
$string['maxterms'] = 'Maximum number of terms';
$string['maxterms_desc'] = 'If the user tries to search for more terms than this limit, they will get an error message. (For performance reasons.)';
$string['postinstall'] = 'This page generates search indexes for all existing content. It can take a very long time (hours or days) if you have a large amount of searchable content to index.';

$string['error_futureyear'] = 'Error: This website has a start date beyond that supported by the OU search system. Search data manipulation failed.';
$string['warning_lastyear'] = 'Warning: This website has a start date in the last year supported by the OU search system. Administrators should increase the number of available years.';
$string['task_split_tables'] = 'OU search: Split data into per-year tables';
$string['task_change_dates'] = 'OU search: Handle websites that have changed their start date';
$string['yearsenabled'] = 'Enable per-year tables';
$string['yearsenabled_desc'] = 'If enabled, search data will be split into different tables depending on the start date of each website (so websites starting in 2014 will be in a 2014 table, and so on). Turning this option on will start a transfer process to move existing data, which may take some time. **Once turned on, this option cannot be turned off.**';
$string['yearsenabled_on'] = 'Per-year tables are fully enabled and in use.';
$string['yearsenabled_transferring'] = 'Per-year tables are partially enabled ({$a}% of websites transferred).';

$string['splittimelimit'] = 'Year table split time';
$string['splittimelimit_desc'] = 'Max time to spend per hourly cron task in transferring the old search index into per-year tables';
$string['datetimelimit'] = 'Year table change date time';
$string['datetimelimit_desc'] = 'Max time to spend per hourly cron in transferring search index data if a website changes year';

$string['privacy:metadata:local_ousearch_documents'] = 'Contains one entry for each known document (potential search result).';
$string['privacy:metadata:local_ousearch_documents:intref1'] = 'Arbitrary int reference to identify document (null if not needed)';
$string['privacy:metadata:local_ousearch_documents:intref2'] = 'Arbitrary int reference to identify document (null if not needed)';
$string['privacy:metadata:local_ousearch_documents:plugin'] = 'Module or plug-in name e.g. mod/ouwiki, format/studycal';
$string['privacy:metadata:local_ousearch_documents:stringref'] = 'Arbitrary string reference to identify document (null if not needed)';
$string['privacy:metadata:local_ousearch_documents:timeexpires'] = 'Time (seconds since epoch) at which document should be checked for changes, or null for no checks';
$string['privacy:metadata:local_ousearch_documents:timemodified'] = 'Time (seconds since epoch) at which document was modified';
$string['privacy:metadata:local_ousearch_documents:userid'] = 'User this document belong to.';
$string['privacy_somebodyelse'] = 'Somebody else';
$string['privacy_you'] = 'You';
