<?php
/* Copyright (C) 2005-2012	Regis Houssin	  <regis.houssin@inodbox.com>
 * Copyright (C) 2011-2012	Juanjo Menent	  <jmenent@2byte.es>
 * Copyright (C) 2016       Laurent Destailleur <aldy@users.sourceforge.net>
 * Copyright (C) 2013       Florian Henry   <florian.henry@open-concept.pro>
 * Copyright (C) 2016	    Gilles Poirier  <glgpoirier@gmail.com>
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
 *	\file       htdocs/resource/note.php
 *	\ingroup    resource
 *	\brief      Notes tab for a resource object
 */

// Load Dolibarr environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/resource/class/dolresource.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/resource.lib.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array('resource', 'companies'));

$id     = GETPOSTINT('id');
$ref    = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');

// Security check
$socid = ($user->socid ? $user->socid : 0);

$hookmanager->initHooks(array('resourcenote', 'globalcard'));

$object = new Dolresource($db);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be 'include', not 'include_once'.

$result = restrictedArea($user, 'resource', $object->id, 'resource');

$permissionnote = $user->hasRight('resource', 'write'); // Used by actions_setnotes.inc.php
$permissiontoread = $user->hasRight('resource', 'read');
if (!$permissiontoread) {
	accessforbidden();
}


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}
if (empty($reshook)) {
	include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php'; // Must be 'include', not 'include_once'
}


/*
 * View
 */

$help_url = '';
llxHeader('', $langs->trans('ResourceSingular'), $help_url, '', 0, 0, '', '', '', 'mod-resource page-card_notes');

$form = new Form($db);

if ($object->id > 0) {
	$head = resource_prepare_head($object);
	print dol_get_fiche_head($head, 'note', $langs->trans('ResourceSingular'), -1, 'resource');

	$linkback = '<a href="'.DOL_URL_ROOT.'/resource/list.php'.(!empty($socid) ? '?id='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno"></div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';

	print '<table class="border tableforfield centpercent">';
	print '<tr>';
	print '<td class="titlefield">'.$langs->trans("ResourceType").'</td>';
	print '<td>'.dol_escape_htmltag($object->type_label).'</td>';
	print '</tr>';
	print "</table>";

	print '</div>';

	$permission = $user->hasRight('resource', 'write');
	$cssclass   = 'titlefield';
	include DOL_DOCUMENT_ROOT.'/core/tpl/notes.tpl.php';

	print dol_get_fiche_end();
}

// End of page
llxFooter();
$db->close();
