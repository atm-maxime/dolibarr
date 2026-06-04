<?php
/* Copyright (C) 2013-2014	Jean-François Ferry		<jfefe@aternatik.fr>
 * Copyright (C) 2023-2024	William Mead			<william.mead@manchenumerique.fr>
 * Copyright (C) 2024-2025	MDW						<mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024		Frédéric France			<frederic.france@free.fr>
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
 *   	\file       resource/card.php
 *		\ingroup    resource
 *		\brief      Page to manage resource object
 */

// Load Dolibarr environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/resource/class/dolresource.class.php';
require_once DOL_DOCUMENT_ROOT.'/resource/class/html.formresource.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/resource.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array('resource', 'companies', 'other', 'main'));

// Get parameters
$id         = GETPOSTINT('id');
$ref        = GETPOST('ref', 'alpha');
$action     = GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'aZ09');
$cancel     = GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

// Protection if external user
if ($user->socid > 0) {
	accessforbidden();
}

$object = new Dolresource($db);
$extrafields = new ExtraFields($db);

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be 'include', not 'include_once'.

$hookmanager->initHooks(array('resource', 'resourcecard', 'globalcard'));

$result = restrictedArea($user, 'resource', $object->id, 'resource');

$permissiontoread   = $user->hasRight('resource', 'read');
$permissiontoadd    = $user->hasRight('resource', 'write');   // Used by actions_addupdatedelete.inc.php
$permissiontodelete = $user->hasRight('resource', 'delete');


/*
 * Actions
 */

$parameters = array('resource_id' => $id);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$backurlforlist = DOL_URL_ROOT.'/resource/list.php';

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = DOL_URL_ROOT.'/resource/card.php?id='.($id > 0 ? $id : '__ID__');
			}
		}
	}

	// Map form field names to ORM $fields keys before delegating to standard include.
	// The form uses 'zipcode' and 'country_id'/'state_id' (for AJAX compatibility) while
	// the DB columns are 'zip', 'fk_country', 'fk_state'.
	if (GETPOSTISSET('zipcode')) {
		$_POST['zip'] = GETPOST('zipcode', 'alphanohtml');
	}
	if (GETPOSTISSET('country_id')) {
		$_POST['fk_country'] = GETPOSTINT('country_id');
	}
	if (GETPOSTISSET('state_id')) {
		$_POST['fk_state'] = GETPOSTINT('state_id');
	}

	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';
}


/*
 * View
 */

$title   = $langs->trans($action == 'create' ? 'AddResource' : 'ResourceSingular');
$help_url = '';
llxHeader('', $title, $help_url, '', 0, 0, '', '', '', 'mod-resource page-card');

$form         = new Form($db);
$formresource = new FormResource($db);

if ($action == 'create') {
	// ----------------------------------------------------------------
	// CREATE FORM
	// ----------------------------------------------------------------
	print load_fiche_titre($title, '', 'object_resource');
	print dol_get_fiche_head();

	print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'" method="POST" name="formresource">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}

	print '<table class="border centpercent">';

	// Ref
	print '<tr><td class="titlefieldcreate fieldrequired">'.$langs->trans("ResourceFormLabel_ref").'</td>';
	print '<td><input class="minwidth200" name="ref" value="'.dol_escape_htmltag(GETPOST('ref', 'alpha')).'" autofocus spellcheck="false"></td></tr>';

	// Type
	print '<tr><td>'.$langs->trans("ResourceType").'</td>';
	print '<td>';
	$formresource->select_types_resource(GETPOST('fk_code_type_resource', 'aZ09'), 'fk_code_type_resource', '', 2, 0, 0, 0, 1, 'minwidth200');
	print '</td></tr>';

	// Description
	print '<tr><td class="tdtop">'.$langs->trans("Description").'</td>';
	print '<td>';
	require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
	$doleditor = new DolEditor('description', GETPOST('description', 'restricthtml'), '', 200, 'dolibarr_notes');
	$doleditor->Create();
	print '</td></tr>';

	// Address
	print '<tr><td class="tdtop">'.$form->editfieldkey('Address', 'address', '', $object, 0).'</td>';
	print '<td><textarea name="address" id="address" class="quatrevingtpercent" rows="3" wrap="soft">';
	print dol_escape_htmltag(GETPOST('address', 'alphanohtml'), 0, 1);
	print '</textarea></td></tr>';

	// Zip
	print '<tr><td>'.$form->editfieldkey('Zip', 'zipcode', '', $object, 0).'</td><td>';
	print $formresource->select_ziptown(GETPOST('zipcode', 'alpha'), 'zipcode', array('town', 'selectcountry_id', 'state_id'), 0, 0, '', 'maxwidth100');
	print '</td></tr>';

	// Town
	print '<tr>';
	print '<td>'.$form->editfieldkey('Town', 'town', '', $object, 0).'</td><td>';
	print $formresource->select_ziptown(GETPOST('town', 'alpha'), 'town', array('zipcode', 'selectcountry_id', 'state_id'));
	print '</td></tr>';

	// Country
	print '<tr><td>'.$langs->trans("CountryOrigin").'</td><td>';
	print $form->select_country(GETPOST('country_id', 'int') ?: '', 'country_id');
	if ($user->admin) {
		print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
	}
	print '</td></tr>';

	// State (shown only when a country is selected)
	$countryid = GETPOSTINT('country_id');
	if (!getDolGlobalString('SOCIETE_DISABLE_STATE') && $countryid > 0) {
		$stateLabel = (getDolGlobalInt('MAIN_SHOW_REGION_IN_STATE_SELECT') == 1 || getDolGlobalInt('MAIN_SHOW_REGION_IN_STATE_SELECT') == 2) ? 'Region-State' : 'State';
		print '<tr><td>'.$form->editfieldkey($stateLabel, 'state_id', '', $object, 0).'</td><td class="maxwidthonsmartphone">';
		print img_picto('', 'state', 'class="pictofixedwidth"');
		print $formresource->select_state($countryid, GETPOSTINT('state_id'));
		print '</td></tr>';
	}

	// Phone
	print '<tr><td>'.$form->editfieldkey('Phone', 'phone', '', $object, 0).'</td>';
	print '<td>'.img_picto('', 'object_phoning', 'class="pictofixedwidth"');
	print '<input type="tel" name="phone" id="phone" value="'.dol_escape_htmltag(GETPOST('phone', 'alpha')).'"></td></tr>';

	// Email
	print '<tr><td>'.$form->editfieldkey('EMail', 'email', '', $object, 0).'</td>';
	print '<td>'.img_picto('', 'object_email', 'class="pictofixedwidth"');
	print '<input type="email" name="email" id="email" value="'.dol_escape_htmltag(GETPOST('email', 'alpha')).'" spellcheck="false"></td></tr>';

	// Max users
	print '<tr><td>'.$form->editfieldkey('MaxUsers', 'max_users', '', $object, 0, 'string', '', 0, 0, 'id', $langs->trans('MaxUsersResourceDesc')).'</td>';
	print '<td>'.img_picto('', 'object_user', 'class="pictofixedwidth"');
	print '<input type="text" class="width75 right" name="max_users" id="max_users" value="'.dol_escape_htmltag(GETPOST('max_users', 'int')).'"></td></tr>';

	// URL
	print '<tr><td>'.$form->editfieldkey('URL', 'url', '', $object, 0).'</td>';
	print '<td>'.img_picto('', 'object_url', 'class="pictofixedwidth"');
	print '<input type="url" class="minwidth300" name="url" id="url" value="'.dol_escape_htmltag(GETPOST('url', 'alpha')).'" spellcheck="false"></td></tr>';

	// Other attributes
	$parameters = array();
	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action);
	print $hookmanager->resPrint;
	if (empty($reshook)) {
		print $object->showOptionals($extrafields, 'create');
	}

	print '</table>';
	print dol_get_fiche_end();
	print $form->buttonsSaveCancel("Create");
	print '</form>';
} elseif ($object->id > 0 || $object->fetch($id, $ref) > 0) {
	// ----------------------------------------------------------------
	// VIEW / EDIT
	// ----------------------------------------------------------------

	// JavaScript for country → reload page to update state dropdown
	if (!empty($conf->use_javascript_ajax) && ($action == 'edit')) {
		print '<script type="text/javascript">';
		print '$(document).ready(function () {
			$("#selectcountry_id").change(function() {
				document.formresource.action.value = "edit";
				document.formresource.submit();
			});
		});';
		print '</script>'."\n";
	}

	$head = resource_prepare_head($object);

	if ($action == 'edit') {
		// ---- EDIT FORM ----
		print dol_get_fiche_head($head, 'resource', $title, -1, 'resource');

		print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'" method="POST" name="formresource">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';
		if ($backtopage) {
			print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
		}

		print '<table class="border centpercent">';

		// Ref
		print '<tr><td class="titlefieldcreate fieldrequired">'.$langs->trans("ResourceFormLabel_ref").'</td>';
		print '<td><input class="minwidth200" name="ref" value="'.dol_escape_htmltag(GETPOSTISSET('ref') ? GETPOST('ref', 'alpha') : $object->ref).'" spellcheck="false"></td></tr>';

		// Type
		print '<tr><td>'.$langs->trans("ResourceType").'</td>';
		print '<td>';
		$formresource->select_types_resource($object->fk_code_type_resource, 'fk_code_type_resource', '', 2, 0, 0, 0, 1, 'minwidth200');
		print '</td></tr>';

		// Description
		print '<tr><td class="tdtop">'.$langs->trans("Description").'</td>';
		print '<td>';
		require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
		$doleditor = new DolEditor('description', (GETPOSTISSET('description') ? GETPOST('description', 'restricthtml') : $object->description), '', 200, 'dolibarr_notes');
		$doleditor->Create();
		print '</td></tr>';

		// Address
		print '<tr><td class="tdtop">'.$form->editfieldkey('Address', 'address', '', $object, 0).'</td>';
		print '<td><textarea name="address" id="address" class="quatrevingtpercent" rows="3" wrap="soft">';
		print dol_escape_htmltag(GETPOSTISSET('address') ? GETPOST('address', 'alphanohtml') : $object->address, 0, 1);
		print '</textarea>';
		print $form->widgetForTranslation("address", $object, (bool) $permissiontoadd, 'textarea', 'alphanohtml', 'quatrevingtpercent');
		print '</td></tr>';

		// Zip
		print '<tr><td>'.$form->editfieldkey('Zip', 'zipcode', '', $object, 0).'</td><td>';
		print $formresource->select_ziptown(GETPOSTISSET('zipcode') ? GETPOST('zipcode', 'alpha') : $object->zip, 'zipcode', array('town', 'selectcountry_id', 'state_id'), 0, 0, '', 'maxwidth100');
		print '</td></tr>';

		// Town
		print '<tr>';
		print '<td>'.$form->editfieldkey('Town', 'town', '', $object, 0).'</td><td>';
		print $formresource->select_ziptown(GETPOSTISSET('town') ? GETPOST('town', 'alpha') : $object->town, 'town', array('zipcode', 'selectcountry_id', 'state_id'));
		print $form->widgetForTranslation("town", $object, (bool) $permissiontoadd, 'string', 'alphanohtml', 'maxwidth100 quatrevingtpercent');
		print '</td></tr>';

		// Country
		print '<tr><td>'.$langs->trans("CountryOrigin").'</td><td>';
		print $form->select_country(GETPOSTISSET('country_id') ? (string) GETPOSTINT('country_id') : (string) $object->country_id, 'country_id');
		if ($user->admin) {
			print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
		}
		print '</td></tr>';

		// State
		$countryid = GETPOSTISSET('country_id') ? GETPOSTINT('country_id') : $object->country_id;
		if (!getDolGlobalString('SOCIETE_DISABLE_STATE') && $countryid > 0) {
			$stateLabel = (getDolGlobalInt('MAIN_SHOW_REGION_IN_STATE_SELECT') == 1 || getDolGlobalInt('MAIN_SHOW_REGION_IN_STATE_SELECT') == 2) ? 'Region-State' : 'State';
			print '<tr><td>'.$form->editfieldkey($stateLabel, 'state_id', '', $object, 0).'</td><td class="maxwidthonsmartphone">';
			if ($countryid > 0) {
				print img_picto('', 'state', 'class="pictofixedwidth"');
				print $formresource->select_state($countryid, GETPOSTISSET('state_id') ? GETPOSTINT('state_id') : $object->state_id);
			} else {
				print '<span class="opacitymedium">'.$langs->trans("ErrorSetACountryFirst").' ('.$langs->trans("SeeAbove").')</span>';
			}
			print '</td></tr>';
		}

		// Phone
		print '<tr><td>'.$form->editfieldkey('Phone', 'phone', '', $object, 0).'</td>';
		print '<td>'.img_picto('', 'object_phoning', 'class="pictofixedwidth"');
		print '<input type="tel" name="phone" id="phone" value="'.dol_escape_htmltag(GETPOSTISSET('phone') ? GETPOST('phone', 'alpha') : $object->phone).'"></td></tr>';

		// Email
		print '<tr><td>'.$form->editfieldkey('EMail', 'email', '', $object, 0).'</td>';
		print '<td>'.img_picto('', 'object_email', 'class="pictofixedwidth"');
		print '<input type="email" name="email" id="email" value="'.dol_escape_htmltag(GETPOSTISSET('email') ? GETPOST('email', 'alpha') : $object->email).'" spellcheck="false"></td></tr>';

		// Max users
		print '<tr><td>'.$form->editfieldkey('MaxUsers', 'max_users', '', $object, 0, 'string', '', 0, 0, 'id', $langs->trans('MaxUsersResourceDesc')).'</td>';
		print '<td>'.img_picto('', 'object_user', 'class="pictofixedwidth"');
		print '<input type="text" class="width75 right" name="max_users" id="max_users" value="'.dol_escape_htmltag(GETPOSTISSET('max_users') ? GETPOST('max_users', 'int') : ($object->max_users > 0 ? $object->max_users : '')).'"></td></tr>';

		// URL
		print '<tr><td>'.$form->editfieldkey('URL', 'url', '', $object, 0).'</td>';
		print '<td>'.img_picto('', 'object_url', 'class="pictofixedwidth"');
		print '<input type="url" class="minwidth300" name="url" id="url" value="'.dol_escape_htmltag(GETPOSTISSET('url') ? GETPOST('url', 'alpha') : $object->url).'" spellcheck="false"></td></tr>';

		// Other attributes
		$parameters = array();
		$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action);
		print $hookmanager->resPrint;
		if (empty($reshook)) {
			print $object->showOptionals($extrafields, 'edit', array(), '', 1);
		}

		print '</table>';
		print dol_get_fiche_end();
		print $form->buttonsSaveCancel("Modify");
		print '</form>';
	} else {
		// ---- VIEW MODE ----
		print dol_get_fiche_head($head, 'resource', $title, -1, 'resource');

		// Confirmation dialogs
		if ($action == 'delete' || ($conf->use_javascript_ajax && empty($conf->dol_use_jmobile))) {
			print $form->formconfirm(
				$_SERVER["PHP_SELF"]."?id=".$object->id,
				$langs->trans("DeleteResource"),
				$langs->trans("ConfirmDeleteResource"),
				"confirm_delete",
				'',
				0,
				"action-delete"
			);
		}

		$linkback = '<a href="'.DOL_URL_ROOT.'/resource/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
		dol_banner_tab($object, 'ref', $linkback, 1, 'ref');

		print '<div class="fichecenter">';
		print '<div class="underbanner clearboth"></div>';

		print '<table class="border tableforfield centpercent">';

		// Resource type
		print '<tr>';
		print '<td class="titlefield">'.$langs->trans("ResourceType").'</td>';
		print '<td>'.dol_escape_htmltag($object->type_label).'</td>';
		print '</tr>';

		// Description
		print '<tr>';
		print '<td>'.$langs->trans("ResourceFormLabel_description").'</td>';
		print '<td>'.$object->description.'</td>';
		print '</tr>';

		// Max users
		print '<tr>';
		print '<td>'.$langs->trans("MaxUsers").'</td>';
		print '<td>'.($object->max_users > 0 ? $object->max_users : '').'</td>';
		print '</tr>';

		// Other attributes
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

		print '</table>';
		print '</div>';
		print '<div class="clearboth"></div>';

		print dol_get_fiche_end();
	}

	// ---- ACTION BUTTONS ----
	print '<div class="tabsAction">';
	$parameters = array();
	$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);
	if (empty($reshook)) {
		if ($action != 'edit') {
			print dolGetButtonAction('', $langs->trans('Modify'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);
		}
		if ($action != 'edit') {
			$deleteUrl = '';
			$buttonId  = 'action-delete';
			if (!$conf->use_javascript_ajax || !empty($conf->dol_use_jmobile)) {
				$deleteUrl = $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=delete&token='.newToken();
				$buttonId  = 'action-delete-no-ajax';
			}
			print dolGetButtonAction('', $langs->trans("Delete"), 'delete', $deleteUrl, $buttonId, $permissiontodelete);
		}
	}
	print '</div>';
} else {
	dol_print_error($db);
}

// End of page
llxFooter();
$db->close();
