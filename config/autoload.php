<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2013 Leo Feyer
 *
 * @package npcustomevents
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'neckarpixel',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'neckarpixel\npcustomevents\customevent'		=> 'system/modules/npcustomevents/elements/customevent.php',
	'neckarpixel\npcustomevents\customeventlist'	=> 'system/modules/npcustomevents/elements/customeventlist.php',
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'ce_npcustomevent'								=> 'system/modules/npcustomevents/templates',
	'ce_npcustomeventlist'							=> 'system/modules/npcustomevents/templates',

));

