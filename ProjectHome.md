This extension adds an extra import option to [MediaWiki](http://www.mediawiki.org/), in the form of an easy to use wizard.

## Features ##
  * Direct import from predefined list of sources.
  * Store imported article under a different title than the source article.
  * Option to expand templates.
  * Option to follow redirects.
  * Select the exact sections of an article to import.

## Installation ##
You can either download the source using the link on your right, or you can check out the source from SVN.

The installation procedure is as follows:
  * Place the source in `extensions/ImportWizard/` under your MediaWiki root directory.
  * Add the following line to your `LocalSettings.php`:
```
require_once "$IP/extensions/ImportWizard/ImportWizard.php";
```

## User rights ##
| `import-wizard` | Allows a user with that right set to use the import wizard. |
|:----------------|:------------------------------------------------------------|