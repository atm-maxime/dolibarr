<?php
/* Copyright (C) 2001-2007  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2005       Brice Davoleau          <brice.davoleau@gmail.com>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2006-2015  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2007       Patrick Raguin  		<patrick.raguin@gmail.com>
 * Copyright (C) 2010       Juanjo Menent           <jmenent@2byte.es>
 * Copyright (C) 2015       Marcos García           <marcosgdf@gmail.com>
 * Copyright (C) 2018       Florian Henry           <florian.henry@open-concept.pro>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/resource/agenda.php
 *  \ingroup    resource
 *  \brief      Agenda/events tab for a resource object
 */

// Load Dolibarr environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/resource.lib.php';
require_once DOL_DOCUMENT_ROOT.'/resource/class/dolresource.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array('resource', 'companies'));

// Get parameters
$id         = GETPOSTINT('id');
$ref        = GETPOST('ref', 'alpha');
$action     = GETPOST('action', 'aZ09');
$cancel     = GETPOST('cancel');
$backtopage = GETPOST('backtopage', 'alpha');

if (GETPOST('actioncode', 'array')) {
	$actioncode = GETPOST('actioncode', 'array', 3);
	if (!count($actioncode)) {
		$actioncode = '0';
	}
} else {
	$actioncode = GETPOST("actioncode", "alpha", 3) ? GETPOST("actioncode", "alpha", 3) : (GETPOST("actioncode") == '0' ? '0' : getDolGlobalString('AGENDA_DEFAULT_FILTER_TYPE_FOR_OBJECT'));
}

$search_rowid        = GETPOST('search_rowid');
$search_agenda_label = GETPOST('search_agenda_label');

$limit     = GETPOSTINT('limit') ? GETPOSTINT('limit') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page      = GETPOSTISSET('pageplusone') ? (GETPOSTINT('pageplusone') - 1) : GETPOSTINT("page");
if (empty($page) || $page == -1) {
	$page = 0;
}
$offset   = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortfield) {
	$sortfield = 'a.datep,a.id';
}
if (!$sortorder) {
	$sortorder = 'DESC,DESC';
}

$hookmanager->initHooks(array('agendaresource', 'globalcard'));

$object = new Dolresource($db);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be 'include', not 'include_once'.

$result = restrictedArea($user, 'resource', $object->id, 'resource');

$permissiontoread = $user->hasRight('resource', 'read');
if (!$permissiontoread) {
	accessforbidden();
}


/*
 * Actions
 */

$parameters = array('id' => $id);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	if (GETPOST('cancel', 'alpha') && !empty($backtopage)) {
		header("Location: ".$backtopage);
		exit;
	}
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
		$actioncode          = '';
		$search_agenda_label = '';
	}
}


/*
 * View
 */

$form = new Form($db);

if ($object->id > 0) {
	$picto = 'resource';
	$title = $langs->trans("Agenda");
	if (getDolGlobalString('MAIN_HTML_TITLE') && preg_match('/productnameonly/', getDolGlobalString('MAIN_HTML_TITLE')) && $object->ref) {
		$title = $object->ref." - ".$title;
	}
	$help_url = '';
	llxHeader('', $title, $help_url, '', 0, 0, '', '', '', 'mod-resource page-card_agenda');

	if (isModEnabled('notification')) {
		$langs->load("mails");
	}

	$head = resource_prepare_head($object);
	print dol_get_fiche_head($head, 'agenda', $langs->trans("ResourceSingular"), -1, $picto);

	$linkback = '<a href="'.DOL_URL_ROOT.'/resource/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno"></div>';

	$shownav = 1;
	if ($user->socid && !in_array('resource', explode(',', getDolGlobalString('MAIN_MODULES_FOR_EXTERNAL')))) {
		$shownav = 0;
	}

	dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref', 'ref', $morehtmlref);

	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';
	print '</div>';

	print dol_get_fiche_end();

	if (isModEnabled('agenda') && ($user->hasRight('agenda', 'myactions', 'read') || $user->hasRight('agenda', 'allactions', 'read'))) {
		$param = '&id='.$object->id;
		if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
			$param .= '&contextpage='.urlencode($contextpage);
		}
		if ($limit > 0 && $limit != $conf->liste_limit) {
			$param .= '&limit='.((int) $limit);
		}

		print_barre_liste($langs->trans("ActionsOnResource"), 0, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, '', 0, -1, '', 0, '', '', 0, 1, 1);

		$filters = array();
		$filters['search_agenda_label'] = $search_agenda_label;
		$filters['search_rowid']        = $search_rowid;

		show_actions_done($conf, $langs, $db, $object, null, 0, $actioncode, '', $filters, $sortfield, $sortorder);
	}
}

// End of page
llxFooter();
$db->close();
