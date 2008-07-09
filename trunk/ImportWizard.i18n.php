<?php
/* 
 * Advanced User Rights Management extension for MediaWiki
 * Copyright (C) 2008  Raymond Jelierse
 *	
 * This extension is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *	
 * This extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *	
 * You should have received a copy of the GNU General Public License along
 * with this extension; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if (!defined ('MEDIAWIKI'))
	die ();

$messages = array ();

$messages['en'] = array (
	'importwizard'      => 'Import wizard',
	'importwizard-desc' => 'Imports (sections of) articles from another wiki',
	# Group entry defaults
	'group-import-wizard'        => 'Import wizards',
	'group-import-wizard-member' => 'Import wizard',
	'grouppage-import-wizard'    => 'Special:ImportWizard',
	# Log entry
	'import-logentry-wizard'        => 'imported [[$1]] with import wizard',
	'import-logentry-wizard-detail' => 'from [$1 $2]',
	# Common elements
	'button-prev' => '< Back',
	'button-next' => 'Next >',
	# Errors
	'iw-nosourcetitleset'   => 'No source title was set. Please set a source title.',
	'iw-nosectionsselected' => 'No sections were selected, you have to select sections to import.',
	# Page 1
	'iw-explain-page1'         => 'Enter the title for the article you wish to import and select the source you wish to import it from. You can also enter the title for the destination article, but if you leave it empty, it will simply default to the source title.',
	'iw-source-title'          => 'Source title:',
	'iw-source-wiki'           => 'Source wiki:',
	'iw-source-options'        => 'Source options:',
	'iw-option-expandtemplate' => 'Expand templates',
	'iw-option-followredirect' => 'Follow redirections',
	'iw-dest-title'            => 'Destination title:',
	# Page 2
	'iw-explain-page2' => 'Select which sections you wish to import. You can select a section by clicking it. Selected sections have a green background.',
	# Page 3
	'iw-viewarticle'        => 'View $1',
);
?>