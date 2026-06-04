<?php
/* Copyright (C) 2005-2012  Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2007-2009  Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012       Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2016		Gilles Poirier		 <glgpoirier@gmail.com>
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
 *       \file       htdocs/resource/contact.php
 *       \ingroup    resource
 *       \brief      Contacts management tab for resources
 */

// Load Dolibarr environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/resource/class/dolresource.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/resource.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array('companies', 'resource', 'sendings'));

$id     = GETPOSTINT('id');
$ref    = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');

$hookmanager->initHooks(array('resourcecontact', 'globalcard'));

$object = new Dolresource($db);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be 'include', not 'include_once'

// Security check
$socid = ($user->socid ? $user->socid : 0);
$result = restrictedArea($user, 'resource', $object->id, 'resource');

$permissiontoread = $user->hasRight('resource', 'read');
$permissiontoadd  = $user->hasRight('resource', 'write');
if (!$permissiontoread) {
	accessforbidden();
}


/*
 * Actions
 */

if ($action == 'addcontact' && $permissiontoadd) {
	if ($result > 0 && $id > 0) {
		$contactid = (GETPOSTINT('userid') ? GETPOSTINT('userid') : GETPOSTINT('contactid'));
		$typeid    = (GETPOST('typecontact') ? GETPOST('typecontact') : GETPOST('type'));
		$result    = $object->add_contact($contactid, $typeid, GETPOST("source", 'aZ09'));
	}

	if ($result >= 0) {
		header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
		exit;
	} else {
		if ($object->error == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
			$langs->load("errors");
			setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
		} else {
			setEventMessages($object->error, null, 'errors');
		}
	}
} elseif ($action == 'swapstatut' && $permissiontoadd) {
	$result = $object->swapContactStatus(GETPOSTINT('ligne'));
} elseif ($action == 'deletecontact' && $permissiontoadd) {
	$result = $object->delete_contact(GETPOSTINT('lineid'));

	if ($result >= 0) {
		header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
		exit;
	} else {
		dol_print_error($db);
	}
}


/*
 * View
 */

$form        = new Form($db);
$formcompany = new FormCompany($db);
$contactstatic = new Contact($db);
$userstatic  = new User($db);

$help_url = '';
llxHeader('', $langs->trans("Resource"), $help_url, '', 0, 0, '', '', '', 'mod-resource page-card_contact');

if ($object->id > 0) {
	$head = resource_prepare_head($object);
	print dol_get_fiche_head($head, 'contact', $langs->trans("ResourceSingular"), -1, 'resource');

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
	print '</table>';
	print '</div>';

	print dol_get_fiche_end();

	print '<br>';

	$hideaddcontactforuser      = getDolGlobalString('RESOURCE_HIDE_ADD_CONTACT_USER') ? 1 : 0;
	$hideaddcontactforthirdparty = getDolGlobalString('RESOURCE_HIDE_ADD_CONTACT_THIPARTY') ? 1 : 0;

	$permission = $permissiontoadd;
	include DOL_DOCUMENT_ROOT.'/core/tpl/contacts.tpl.php';
}

// End of page
llxFooter();
$db->close();
