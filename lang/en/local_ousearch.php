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
