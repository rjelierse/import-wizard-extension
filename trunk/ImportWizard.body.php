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

class ImportWizardPage extends SpecialPage
{
	public function __construct ()
	{
		wfLoadExtensionMessages ('ImportWizard');
		SpecialPage::__construct ('ImportWizard', 'import-wizard');
	}
	
	public function isRestricted () { return false; }
	
	public function execute ($par)
	{
		global $IP, $wgJsMimeType, $wgOut, $wgRequest, $wgScriptPath, $wgStyleVersion, $wgUser;
		
		if (!$this->userCanExecute ($wgUser))
			return $this->displayRestrictionError ();
		
		$this->outputHeader ();
		
		$this->setHeaders ();
		
		$scriptFile = str_replace ($IP, $wgScriptPath, dirname (__FILE__) . '/ImportWizard.js');
		$styleFile = str_replace ($IP, $wgScriptPath, dirname (__FILE__) . '/ImportWizard.css');
		$wgOut->addScript( "<script type=\"{$wgJsMimeType}\" src=\"{$scriptFile}?$wgStyleVersion\"></script>\n");
		$wgOut->addLink (array ('rel' => 'stylesheet', 'href' => $styleFile));
		
		if ($wgRequest->getCheck ('iwPage3'))
			$this->showSaveForm ();
		elseif ($wgRequest->getCheck ('iwPage2'))
			$this->showSectionForm ();
		else
			$this->showImportForm ($par);
	}
	
	/**
	 * Shows the initial import form, where the user can select the page he wants to import, as well as the source from which to import.
	 */
	private function showImportForm ($pageName)
	{
		global $iwSources, $wgLogActions, $wgOut, $wgRequest;
		
		$wgOut->addWikiText (wfMsg ('iw-explain-page1'));
				
		$form  = Xml::openElement ('form', array ('method' => 'post', 'action' => SpecialPage::getTitle()->getLocalURL()));
		$form .= Xml::openElement ('table', array ('width' => '100%'));
		$form .= Xml::openElement ('tr');
		$form .= Xml::tags ('td', array ('width' => '200px'), Xml::label (wfMsg ('iw-source-title'), 'iwSourceTitle'));
		$form .= Xml::tags ('td', NULL, Xml::input ('iwSourceTitle', 50, $wgRequest->getVal ('iwSourceTitle', $pageName), array ('id' => 'iwSourceTitle')));
		$form .= Xml::closeElement ('tr');
		$form .= Xml::openElement ('tr');
		$form .= Xml::tags ('td', array ('width' => '200px'), Xml::label (wfMsg ('iw-source-wiki'), 'iwSourceWiki'));
		$form .= Xml::openElement ('td');
		$form .= Xml::openElement ('select', array ('name' => 'iwSourceWiki', 'id' => 'iwSourceWiki'));
		foreach ($iwSources as $sourceId => $sourceInfo)
			$form .= Xml::option ($sourceInfo['name'], $sourceId, ($wgRequest->getVal ('iwSourceWiki') == $sourceId));
		$form .= Xml::closeElement ('select');
		$form .= Xml::closeElement ('td');
		$form .= Xml::closeElement ('tr');
		$form .= Xml::openElement ('tr');
		$form .= Xml::element ('td', NULL, wfMsg ('iw-source-options'));
		$form .= Xml::tags ('td', NULL, Xml::checkLabel (wfMsg ('iw-option-expandtemplate'), 'iwExpandTemplates', 'iwExpandTemplates', true));
		$form .= Xml::closeElement ('tr');
		$form .= Xml::openElement ('tr');
		$form .= Xml::element ('td');
		$form .= Xml::tags ('td', NULL, Xml::checkLabel (wfMsg ('iw-option-followredirect'), 'iwFollowRedirects', 'iwFollowRedirects', true));
		$form .= Xml::closeElement ('tr');
		$form .= Xml::openElement ('tr');
		$form .= Xml::tags ('td', NULL, Xml::label (wfMsg ('iw-dest-title'), 'iwDestTitle'));
		$form .= Xml::tags ('td', NULL, Xml::input ('iwDestTitle', 50, $wgRequest->getVal ('iwDestTitle', $pageName), array ('id' => 'iwDestTitle')));
		$form .= Xml::closeElement ('tr');
		$form .= Xml::closeElement ('table');
		$form .= Xml::submitButton (wfMsg ('button-prev'), array ('disabled' => 'disabled'));
		$form .= Xml::submitButton (wfMsg ('button-next'), array ('name' => 'iwPage2'));
		$form .= Xml::closeElement ('form');
		
		$wgOut->addHTML ($form);
	}
	
	/**
	 * Shows the form in which the user can select which sections should be imported.
	 */
	private function showSectionForm ()
	{
		global $iwSources, $wgOut, $wgParser, $wgRequest, $wgTitle;
		static $iwImportCache = array ();
		
		$sourceWiki = $wgRequest->getVal ('iwSourceWiki');
		$sourceTitle = $wgRequest->getVal ('iwSourceTitle');
		$destTitle = $wgRequest->getVal ('iwDestTitle');
				
		if (empty ($sourceTitle))
			return $this->showError ('iw-nosourcetitleset', 'iwPage1');
		$destTitleObj = !empty ($destTitle) ? Title::newFromText ($destTitle) : Title::newFromText ($sourceTitle);
		if ($destTitleObj->exists())
			return $this->showError ('articleexists', 'iwPage1');
			
		$articleBody = self::getArticleBody ($sourceTitle, $sourceWiki, $wgRequest->getCheck ('iwExpandTemplates'), $wgRequest->getCheck ('iwFollowRedirects'));
		
		if ($articleBody === false)
			return $this->showError ('import-noarticle', 'iwPage1');
		
		$wgOut->addWikiText (wfMsg ('iw-explain-page2'));
		
		$i = 0;
		$sections = array ();
		while ($section = $wgParser->getSection ($articleBody, $i, false))
		{
			$subSectionOffset = strpos ($section, '===');
			if ($subSectionOffset > 0)
				$section = substr ($section, 0, $subSectionOffset);
			
			$sections[] = $section;
			$i++;
		}
		
		$parserOptions = new ParserOptions ();
		$parserOptions->setUseTex (true);
		$parserOptions->setEditSection (false);
		
		$form  = Xml::openElement ('form', array ('method' => 'post', 'action' => SpecialPage::getTitle()->getLocalURL()));
		$form .= Xml::hidden ('iwSourceWiki', $sourceWiki);
		$form .= Xml::hidden ('iwSourceTitle', $sourceTitle);
		$form .= Xml::hidden ('iwDestTitle', $destTitle);
		$form .= $wgRequest->getCheck ('iwExpandTemplates') ? Xml::hidden ('iwExpandTemplates', 'iwExpandTemplates') : '';
		for ($i = 0; $i < count ($sections); $i++)
		{
			$sectionText = $wgParser->parse ($sections[$i], $wgTitle, $parserOptions)->getText();
			$form .= Xml::check ("iwIncludeSection[$i]", false, array ('style' => 'display: none;', 'id' => "iwIncludeSection[$i]", 'value' => $sections[$i]));
			$form .= Xml::tags ('div', array ('onClick' => "toggleCheck(this, 'iwIncludeSection[$i]');", 'class' => 'sectionBlock'), $sectionText);
		}
		$form .= Xml::submitButton (wfMsg ('button-prev'), array ('name' => 'iwPage1'));
		$form .= Xml::submitButton (wfMsg ('button-next'), array ('name' => 'iwPage3'));
		$form .= Xml::closeElement ('form');

		$wgOut->addHTML ($form);
	}
	
	/**
	 * Show the form where the user can set options to use when saving the imported article.
	 */
	private function showSaveForm ()
	{
		global $iwSources, $wgOut, $wgRequest, $wgUser;
		
		$sourceWiki = $wgRequest->getVal ('iwSourceWiki');
		$sourceTitle = $wgRequest->getVal ('iwSourceTitle');
		$destTitle = $wgRequest->getVal ('iwDestTitle', $sourceTitle);
		$selectedSections = $wgRequest->getArray ('iwIncludeSection');
		if (empty ($selectedSections))
			return $this->showError ('iw-nosectionsselected', 'iwPage2');
		
		$articleTitle = Title::newFromText ($destTitle);
		$articleText  = "{{import wizard}}\n\n";
		$articleText .= implode ("\n\n", array_values ($selectedSections));
		$articleFlags =  EDIT_NEW | EDIT_DEFER_UPDATES | EDIT_AUTOSUMMARY | EDIT_SUPPRESS_RC;
		$article = new Article ($articleTitle);
		$article->doEdit ($articleText, '', $articleFlags);
		
		$log = new LogPage ('import');
		$log->addEntry ('wizard', $articleTitle, '');
		
		$wgOut->addHTML (Xml::element ('p', NULL, wfMsg ('importsuccess')));
		$wgOut->addHTML (Xml::tags ('p', NULL, wfMsg ('iw-viewarticle', $wgUser->getSkin()->makeLinkObj ($articleTitle))));
		$wgOut->addReturnTo (SpecialPage::getTitle());
		$wgOut->returnToMain ();
	}
	
	private function showError ($errorMsg, $prev = 'disabled', $next = 'disabled')
	{
		global $wgOut, $wgRequest;
		
		$sourceWiki = $wgRequest->getVal ('iwSourceWiki');
		$sourceTitle = $wgRequest->getVal ('iwSourceTitle');
		$destTitle = $wgRequest->getVal ('iwDestTitle');
		
		$form  = Xml::openElement ('form', array ('method' => 'post', 'action' => SpecialPage::getTitle()->getLocalURL()));
		$form .= Xml::hidden ('iwSourceWiki', $sourceWiki);
		$form .= Xml::hidden ('iwSourceTitle', $sourceTitle);
		$form .= Xml::hidden ('iwDestTitle', $destTitle);
		$form .= Xml::tags ('p', NULL, wfMsg ($errorMsg));
		$form .= Xml::submitButton (wfMsg ('button-prev'), ($prev == 'disabled') ? array ('disabled' => 'disabled') : array ('name' => $prev));
		$form .= Xml::submitButton (wfMsg ('button-next'), ($next == 'disabled') ? array ('disabled' => 'disabled') : array ('name' => $next));
		$form .= Xml::closeElement ('form');
		
		$wgOut->addHTML ($form);
	}
	
	/**
	 * Function to download the body of a given article.
	 *
	 * @param string $title The title of the article to import.
	 * @param string $source The key of the wiki to import from.
	 * @param bool $expandTemplate Whether to expand templates in the article, or leave them as is. (default is true)
	 * @param bool $followRedirects Whether to follow redirects when importing the article. (default is true)
	 * @returns string The body of article in WikiText.
	 */
	private static function getArticleBody ($title, $source, $expandTemplate = true, $followRedirects = true)
	{
		global $iwSources;
		static $iwImportCache = array ();
		
		$exportTitle = self::escapeTitle ($title);
		
		$link = str_replace ('$1', $exportTitle, $iwSources[$source]['url']);
		$link .= ($expandTemplate) ? '&action=raw&templates=expand' : '&action=raw';
		
		if (empty ($aiImportCache[$link]))
		{
			$iwImportCache[$link] = Http::request ('GET', $link);
			// Trim the article for matching it later.
			$iwImportCache[$link] = trim ($iwImportCache[$link]);
		}
		
		if ($iwImportCache[$link] === false)
			return false;
		if (empty ($iwImportCache))
			return false;
		
		if (preg_match ('!^#REDIRECT \[\[(.+)\]\]$!', $iwImportCache[$link], $results) && $followRedirects)
			return self::getArticleBody ($results[1], $source, $expandTemplate, $followRedirects);
		
		return $iwImportCache[$link];
	}
	
	private static function escapeTitle ($title)
	{
		$title = str_replace (' ', '_', $title);
		$title = wfUrlencode ($title) ;
		return $title;
	}
}
?>