<?php
/* 
 * Advanced Import Tool extension for MediaWiki
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

// Credits
$wgExtensionCredits['specialpage'][] = array (
	'name'           => 'Import Wizard',
	'author'         => '[http://www.wikid.eu/index.php/User:Rjelierse Raymond&nbsp;Jelierse]',
	'version'        => '0.1.1 (2008-07-02)',
	'url'            => 'http://code.google.com/p/import-wizard-extension/',
	'descriptionmsg' => 'importwizard-desc',
);

// Added user right and corresponding group
$wgAvailableRights[] = 'import-wizard';
$wgGroupPermissions['import-wizard']['import-wizard'] = true;

// Log entry
$wgLogActions['import/wizard'] = 'import-logentry-wizard';

// Localisation
$wgExtensionMessagesFiles['ImportWizard'] = dirname( __FILE__ ) . '/ImportWizard.i18n.php';

// Special page class
$wgSpecialPages['ImportWizard'] = 'ImportWizardPage';
$wgSpecialPageGroups['ImportWizard'] = 'other';
$wgAutoloadClasses['ImportWizardPage'] = dirname( __FILE__ ) . '/ImportWizard.body.php';

// Settings initialisation
if (empty ($iwSources)) $iwSources = array (
	'wikipedia' => array ('name' => 'Wikipedia (English)', 'url' => 'http://en.wikipedia.org/w/index.php?title=$1'),
);
?>