<?php
/* Copyright (C) 2013-2014  Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2018       Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2018-2025  Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2024		William Mead			<william.mead@manchenumerique.fr>
 * Copyright (C) 2024-2025	MDW						<mdeweerd@users.noreply.github.com>
 * Copyright (C) 2026		Anthony Berton			<anthony.berton@bb2a.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *      \file       htdocs/resource/list.php
 *      \ingroup    resource
 *      \brief      Page to manage resource objects
 */

// Load Dolibarr environment
require '../main.inc.php';
/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */
require_once DOL_DOCUMENT_ROOT.'/resource/class/dolresource.class.php';

// Load translation files required by the page
$langs->loadLangs(array("resource", "companies", "other"));

// Get parameters
$id          = GETPOSTINT('id');
$action      = GETPOST('action', 'alpha');
$massaction  = GETPOST('massaction', 'alpha');
$confirm     = GETPOST('confirm', 'alpha');
$toselect    = GETPOST('toselect', 'array:int');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'resourcelist';
$optioncss   = GETPOST('optioncss', 'alpha');

$sortorder = GETPOST('sortorder', 'aZ09comma');
$sortfield = GETPOST('sortfield', 'aZ09comma');

if (empty($sortorder)) {
	$sortorder = "ASC";
}
if (empty($sortfield)) {
	$sortfield = "t.ref";
}

// Initialize technical objects
$object = new Dolresource($db);
$extrafields = new ExtraFields($db);

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');
if (!is_array($search_array_options)) {
	$search_array_options = array();
}

$search_all      = trim(GETPOST('search_all', 'alphanohtml'));
$search_ref      = GETPOST("search_ref", 'alpha');
$search_type     = GETPOST("search_type", 'alpha');
$search_address  = GETPOST("search_address", 'alpha');
$search_zip      = GETPOST("search_zip", 'alpha');
$search_town     = GETPOST("search_town", 'alpha');
$search_state    = GETPOST("search_state", 'alpha');
$search_country  = GETPOST("search_country", 'alpha');
$search_phone    = GETPOST("search_phone", 'alpha');
$search_email    = GETPOST("search_email", 'alpha');
$search_max_users = GETPOST("search_max_users", 'alpha');
$search_url      = GETPOST("search_url", 'alpha');

$hookmanager->initHooks(array('resourcelist'));

// Load variable for pagination
$limit  = GETPOSTINT('limit') ? GETPOSTINT('limit') : $conf->liste_limit;
$page   = GETPOSTISSET('pageplusone') ? (GETPOSTINT('pageplusone') - 1) : GETPOSTINT("page");
if (empty($page) || $page == -1) {
	$page = 0;
}
$offset   = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Fields to search into when doing a "search in all"
$fieldstosearchall = array(
	't.ref'         => 'Ref',
	't.description' => 'Description',
);

// Definition of array of fields for columns
// Note: fields coming from JOINs (ty.label, st.nom, co.label) cannot be derived from
// $object->fields directly, so we keep them here with custom SQL aliases.
$arrayfields = array(
	't.ref'       => array('label' => $langs->trans("Ref"),           'checked' => '1', 'position' => 1),
	'ty.label'    => array('label' => $langs->trans("Type"),          'checked' => '1', 'position' => 2),
	't.address'   => array('label' => $langs->trans("Address"),       'checked' => '0', 'position' => 3),
	't.zip'       => array('label' => $langs->trans("Zip"),           'checked' => '0', 'position' => 4),
	't.town'      => array('label' => $langs->trans("Town"),          'checked' => '1', 'position' => 5),
	'st.nom'      => array('label' => $langs->trans("State"),         'checked' => '0', 'position' => 6),
	'co.label'    => array('label' => $langs->trans("Country"),       'checked' => '1', 'position' => 7),
	't.phone'     => array('label' => $langs->trans("Phone"),         'checked' => '0', 'position' => 8),
	't.email'     => array('label' => $langs->trans("Email"),         'checked' => '0', 'position' => 9),
	't.max_users' => array('label' => $langs->trans("MaxUsersLabel"), 'checked' => '1', 'position' => 10),
	't.url'       => array('label' => $langs->trans("URL"),           'checked' => '0', 'position' => 11),
);

// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_array_fields.tpl.php';

$object->fields = dol_sort_array($object->fields, 'position');
$arrayfields    = dol_sort_array($arrayfields, 'position');

include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

// Purge search criteria
if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	$search_ref       = "";
	$search_type      = "";
	$search_address   = "";
	$search_zip       = "";
	$search_town      = "";
	$search_state     = "";
	$search_country   = "";
	$search_phone     = "";
	$search_email     = "";
	$search_max_users = "";
	$search_url       = "";
	$toselect         = array();
	$search_array_options = array();
}

$permissiontoread   = $user->hasRight('resource', 'read');
$permissiontoadd    = $user->hasRight('resource', 'write');
$permissiontodelete = $user->hasRight('resource', 'delete');
if (!$permissiontoread) {
	accessforbidden();
}

// Mass actions
$objectclass = 'Dolresource';
$objectlabel = 'Resources';
$uploaddir   = $conf->resource->dir_output;
include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';


/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) {
	$action     = 'list';
	$massaction = '';
}
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') {
	$massaction = '';
}

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}


/*
 * View
 */

$form       = new Form($db);
$objectstatic = new Dolresource($db);

$help_url = '';
$title    = $langs->trans('Resources');
$morejs   = array();
$morecss  = array();

$varpage       = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);

// Build the SQL query
$sql = "SELECT";
$sql .= " t.rowid,";
$sql .= " t.entity,";
$sql .= " t.ref,";
$sql .= " t.address,";
$sql .= " t.zip,";
$sql .= " t.town,";
$sql .= " t.fk_country,";
$sql .= " t.fk_state,";
$sql .= " t.description,";
$sql .= " t.phone,";
$sql .= " t.email,";
$sql .= " t.max_users,";
$sql .= " t.url,";
$sql .= " t.fk_code_type_resource,";
$sql .= " t.tms as date_modification,";
$sql .= " t.datec as date_creation,";
$sql .= " ty.label as type_label,";
$sql .= " st.nom as state_label,";
$sql .= " co.label as country_label";
// Add fields from extrafields
if (!empty($extrafields->attributes[$object->table_element]['label'])) {
	foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) {
		$sql .= ($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? ", ef.".$key." as options_".$key : '');
	}
}
// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters, $object, $action);
$sql .= $hookmanager->resPrint;

$sqlfields = $sql; // Save for count query

$sql .= " FROM ".MAIN_DB_PREFIX."resource as t";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_resource as ty ON ty.code=t.fk_code_type_resource";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_departements as st ON st.rowid=t.fk_state";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_country as co ON co.rowid=t.fk_country";
if (isset($extrafields->attributes[$object->table_element]['label']) && is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label'])) {
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX.$object->table_element."_extrafields as ef on (t.rowid = ef.fk_object)";
}
// Add tables from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListFrom', $parameters, $object, $action);
$sql .= $hookmanager->resPrint;

$sql .= " WHERE t.entity IN (".getEntity('resource').")";
if ($search_all) {
	$sql .= natural_search(array_keys($fieldstosearchall), $search_all);
}
if ($search_ref) {
	$sql .= natural_search('t.ref', $search_ref);
}
if ($search_type) {
	$sql .= natural_search('ty.label', $search_type);
}
if ($search_address) {
	$sql .= natural_search('t.address', $search_address);
}
if ($search_zip) {
	$sql .= natural_search('t.zip', $search_zip);
}
if ($search_town) {
	$sql .= natural_search('t.town', $search_town);
}
if ($search_state) {
	$sql .= natural_search('st.nom', $search_state);
}
if ($search_country && $search_country != '-1') {
	$sql .= " AND t.fk_country IN (".$db->sanitize($search_country).')';
}
if ($search_phone) {
	$sql .= natural_search('t.phone', $search_phone);
}
if ($search_email) {
	$sql .= natural_search('t.email', $search_email);
}
if ($search_max_users) {
	$sql .= natural_search('t.max_users', $search_max_users, 1);
}
if ($search_url) {
	$sql .= natural_search('t.url', $search_url);
}

// Add where from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_sql.tpl.php';
// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $object, $action);
$sql .= $hookmanager->resPrint;

// Count total records
$nbtotalofrecords = '';
if (!getDolGlobalInt('MAIN_DISABLE_FULL_SCANLIST')) {
	$sqlforcount = preg_replace('/^'.preg_quote($sqlfields, '/').'/', 'SELECT COUNT(*) as nbtotalofrecords', $sql);
	$sqlforcount = preg_replace('/GROUP BY .*$/', '', $sqlforcount);
	$resql = $db->query($sqlforcount);
	if ($resql) {
		$objforcount = $db->fetch_object($resql);
		$nbtotalofrecords = $objforcount->nbtotalofrecords;
	} else {
		dol_print_error($db);
	}
	if (($page * $limit) > (int) $nbtotalofrecords) {
		$page   = 0;
		$offset = 0;
	}
	$db->free($resql);
}

// Complete request and execute it with limit
$sql .= $db->order($sortfield, $sortorder);
if ($limit) {
	$sql .= $db->plimit($limit + 1, $offset);
}

$resql = $db->query($sql);
if (!$resql) {
	dol_print_error($db);
	exit;
}

$num = $db->num_rows($resql);

// Direct jump if only one record found
if ($num == 1 && getDolGlobalString('MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE') && $search_all && !$page) {
	$obj = $db->fetch_object($resql);
	header("Location: ".dol_buildpath('/resource/card.php', 1).'?id='.$obj->rowid);
	exit;
}

// Output page
llxHeader('', $title, $help_url, '', 0, 0, $morejs, $morecss, '', 'mod-resource page-list bodyforlist');

$arrayofselected = is_array($toselect) ? $toselect : array();

$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
	$param .= '&contextpage='.urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
	$param .= '&limit='.((int) $limit);
}
if ($search_ref != '') {
	$param .= '&search_ref='.urlencode($search_ref);
}
if ($search_type != '') {
	$param .= '&search_type='.urlencode($search_type);
}
if ($search_address != '') {
	$param .= '&search_address='.urlencode($search_address);
}
if ($search_zip != '') {
	$param .= '&search_zip='.urlencode($search_zip);
}
if ($search_town != '') {
	$param .= '&search_town='.urlencode($search_town);
}
if ($search_state != '') {
	$param .= '&search_state='.urlencode($search_state);
}
if ($search_country != '') {
	$param .= '&search_country='.urlencode($search_country);
}
if ($search_phone != '') {
	$param .= '&search_phone='.urlencode($search_phone);
}
if ($search_email != '') {
	$param .= '&search_email='.urlencode($search_email);
}
if ($search_max_users != '') {
	$param .= '&search_max_users='.urlencode($search_max_users);
}
if ($search_url != '') {
	$param .= '&search_url='.urlencode($search_url);
}

// Add $param from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';

// Mass actions available
$arrayofmassactions = array();
if ($permissiontodelete) {
	$arrayofmassactions['predelete'] = img_picto('', 'delete', 'class="pictofixedwidth"').$langs->trans("Delete");
}
if (GETPOSTINT('nomassaction') || in_array($massaction, array('presend', 'predelete'))) {
	$arrayofmassactions = array();
}
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

$varpage       = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage, $conf->main_checkbox_left_column);
$selectedfields .= (count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');

$newcardbutton = dolGetButtonTitle($langs->trans('NewResource'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/resource/card.php?action=create', '', $permissiontoadd);

print '<form method="POST" id="searchFormList" action="'.dolBuildUrl($_SERVER["PHP_SELF"]).'">';
if ($optioncss != '') {
	print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
}
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="page" value="'.$page.'">';
print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';

print_barre_liste($title, $page, $_SERVER['PHP_SELF'], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'object_'.$object->picto, 0, $newcardbutton, '', $limit, 0, 0, 1);

$objecttmp = new Dolresource($db);
include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

if ($search_all) {
	$setupstring = '';
	foreach ($fieldstosearchall as $key => $val) {
		$fieldstosearchall[$key] = $langs->trans($val);
		$setupstring .= $key."=".$val.";";
	}
	print '<!-- Search done like if RESOURCE_QUICKSEARCH_ON_FIELDS = '.$setupstring.' -->'."\n";
	print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $search_all).implode(', ', $fieldstosearchall).'</div>';
}

print '<div class="div-table-responsive">';
print '<table class="tagtable liste">'."\n";

// Filter row
print '<tr class="liste_titre_filter">';
if ($conf->main_checkbox_left_column) {
	print '<td class="liste_titre maxwidthsearch center">';
	print $form->showFilterButtons('left');
	print '</td>';
}
if (!empty($arrayfields['t.ref']['checked'])) {
	print '<td class="liste_titre"><input type="text" class="flat" name="search_ref" value="'.dol_escape_htmltag($search_ref).'" size="8"></td>';
}
if (!empty($arrayfields['ty.label']['checked'])) {
	print '<td class="liste_titre"><input type="text" class="flat" name="search_type" value="'.dol_escape_htmltag($search_type).'" size="8"></td>';
}
if (!empty($arrayfields['t.address']['checked'])) {
	print '<td class="liste_titre"><input type="text" class="flat" name="search_address" value="'.dol_escape_htmltag($search_address).'" size="8"></td>';
}
if (!empty($arrayfields['t.zip']['checked'])) {
	print '<td class="liste_titre"><input type="text" class="flat" name="search_zip" value="'.dol_escape_htmltag($search_zip).'" size="8"></td>';
}
if (!empty($arrayfields['t.town']['checked'])) {
	print '<td class="liste_titre"><input type="text" class="flat" name="search_town" value="'.dol_escape_htmltag($search_town).'" size="8"></td>';
}
if (!empty($arrayfields['st.nom']['checked'])) {
	print '<td class="liste_titre"><input type="text" class="flat" name="search_state" value="'.dol_escape_htmltag($search_state).'" size="8"></td>';
}
if (!empty($arrayfields['co.label']['checked'])) {
	print '<td class="liste_titre">';
	print $form->select_country($search_country, 'search_country', '', 0, 'minwidth100imp maxwidth100');
	print '</td>';
}
if (!empty($arrayfields['t.phone']['checked'])) {
	print '<td class="liste_titre"><input type="text" class="flat" name="search_phone" value="'.dol_escape_htmltag($search_phone).'" size="8"></td>';
}
if (!empty($arrayfields['t.email']['checked'])) {
	print '<td class="liste_titre"><input type="text" class="flat" name="search_email" value="'.dol_escape_htmltag($search_email).'" size="8"></td>';
}
if (!empty($arrayfields['t.max_users']['checked'])) {
	print '<td class="liste_titre"><input type="text" class="flat" name="search_max_users" value="'.dol_escape_htmltag($search_max_users).'" size="8"></td>';
}
if (!empty($arrayfields['t.url']['checked'])) {
	print '<td class="liste_titre"><input type="text" class="flat" name="search_url" value="'.dol_escape_htmltag($search_url).'" size="8"></td>';
}
// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';

if (!$conf->main_checkbox_left_column) {
	print '<td class="liste_titre center maxwidthsearch">';
	print $form->showFilterButtons();
	print '</td>';
}
print '</tr>'."\n";

$totalarray = array();
$totalarray['nbfield'] = 0;

// Column title row
print '<tr class="liste_titre">';
if ($conf->main_checkbox_left_column) {
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', '', $sortfield, $sortorder, 'center maxwidthsearch ');
}
if (!empty($arrayfields['t.ref']['checked'])) {
	print_liste_field_titre($arrayfields['t.ref']['label'], $_SERVER["PHP_SELF"], "t.ref", "", $param, "", $sortfield, $sortorder);
}
if (!empty($arrayfields['ty.label']['checked'])) {
	print_liste_field_titre($arrayfields['ty.label']['label'], $_SERVER["PHP_SELF"], "ty.label", "", $param, "", $sortfield, $sortorder);
}
if (!empty($arrayfields['t.address']['checked'])) {
	print_liste_field_titre($arrayfields['t.address']['label'], $_SERVER["PHP_SELF"], "t.address", "", $param, "", $sortfield, $sortorder);
}
if (!empty($arrayfields['t.zip']['checked'])) {
	print_liste_field_titre($arrayfields['t.zip']['label'], $_SERVER["PHP_SELF"], "t.zip", "", $param, "", $sortfield, $sortorder);
}
if (!empty($arrayfields['t.town']['checked'])) {
	print_liste_field_titre($arrayfields['t.town']['label'], $_SERVER["PHP_SELF"], "t.town", "", $param, "", $sortfield, $sortorder);
}
if (!empty($arrayfields['st.nom']['checked'])) {
	print_liste_field_titre($arrayfields['st.nom']['label'], $_SERVER["PHP_SELF"], "st.nom", "", $param, "", $sortfield, $sortorder);
}
if (!empty($arrayfields['co.label']['checked'])) {
	print_liste_field_titre($arrayfields['co.label']['label'], $_SERVER["PHP_SELF"], "co.label", "", $param, "", $sortfield, $sortorder);
}
if (!empty($arrayfields['t.phone']['checked'])) {
	print_liste_field_titre($arrayfields['t.phone']['label'], $_SERVER["PHP_SELF"], "t.phone", "", $param, "", $sortfield, $sortorder);
}
if (!empty($arrayfields['t.email']['checked'])) {
	print_liste_field_titre($arrayfields['t.email']['label'], $_SERVER["PHP_SELF"], "t.email", "", $param, "", $sortfield, $sortorder);
}
if (!empty($arrayfields['t.max_users']['checked'])) {
	print_liste_field_titre($arrayfields['t.max_users']['label'], $_SERVER["PHP_SELF"], "t.max_users", "", $param, "", $sortfield, $sortorder);
}
if (!empty($arrayfields['t.url']['checked'])) {
	print_liste_field_titre($arrayfields['t.url']['label'], $_SERVER["PHP_SELF"], "t.url", "", $param, "", $sortfield, $sortorder);
}
// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
if (!$conf->main_checkbox_left_column) {
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', '', $sortfield, $sortorder, 'center maxwidthsearch ');
}
print "</tr>\n";

// Data rows
$i = 0;
$savnbfield = $totalarray['nbfield'];
$totalarray = array();
$totalarray['nbfield'] = 0;
$imaxinloop = ($limit ? min($num, $limit) : $num);
while ($i < $imaxinloop) {
	$obj = $db->fetch_object($resql);

	$objectstatic->id        = $obj->rowid;
	$objectstatic->ref       = $obj->ref;
	$objectstatic->type_label = $obj->type_label;
	$objectstatic->address   = $obj->address;
	$objectstatic->zip       = $obj->zip;
	$objectstatic->town      = $obj->town;
	$objectstatic->state     = $obj->state_label;
	$objectstatic->country   = $obj->country_label;
	$objectstatic->phone     = $obj->phone;
	$objectstatic->email     = $obj->email;
	$objectstatic->max_users = $obj->max_users;
	$objectstatic->url       = $obj->url;

	print '<tr data-rowid="'.$obj->rowid.'" class="oddeven row-with-select">';

	// Action column (left)
	if ($conf->main_checkbox_left_column) {
		print '<td class="nowrap center">';
		if ($massactionbutton || $massaction) {
			$selected = in_array($obj->rowid, $arrayofselected) ? 1 : 0;
			print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
		}
		print '</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}

	if (!empty($arrayfields['t.ref']['checked'])) {
		print '<td class="tdoverflowmax150">'.$objectstatic->getNomUrl(1).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	if (!empty($arrayfields['ty.label']['checked'])) {
		print '<td class="tdoverflowmax200">'.dol_escape_htmltag($objectstatic->type_label).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	if (!empty($arrayfields['t.address']['checked'])) {
		print '<td>'.dol_escape_htmltag($objectstatic->address).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	if (!empty($arrayfields['t.zip']['checked'])) {
		print '<td>'.dol_escape_htmltag($objectstatic->zip).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	if (!empty($arrayfields['t.town']['checked'])) {
		print '<td>'.dol_escape_htmltag($objectstatic->town).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	if (!empty($arrayfields['st.nom']['checked'])) {
		print '<td>'.dol_escape_htmltag($objectstatic->state).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	if (!empty($arrayfields['co.label']['checked'])) {
		print '<td>'.dol_escape_htmltag($objectstatic->country).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	if (!empty($arrayfields['t.phone']['checked'])) {
		print '<td>'.dol_print_phone($objectstatic->phone, '', 0, 0, 'AC_TEL', " ", 'phone').'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	if (!empty($arrayfields['t.email']['checked'])) {
		print '<td>'.dol_print_email($objectstatic->email, 0, 0, 1, 0, 0, 1).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	if (!empty($arrayfields['t.max_users']['checked'])) {
		print '<td>'.dol_escape_htmltag($objectstatic->max_users > 0 ? $objectstatic->max_users : '').'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	if (!empty($arrayfields['t.url']['checked'])) {
		print '<td>'.dol_print_url($objectstatic->url, '_blank', 32, 1).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}

	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';

	// Action column (right)
	if (!$conf->main_checkbox_left_column) {
		print '<td class="nowrap center">';
		if ($massactionbutton || $massaction) {
			$selected = in_array($obj->rowid, $arrayofselected) ? 1 : 0;
			print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
		}
		print '</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}

	print '</tr>';
	$i++;
}

// Show total line
include DOL_DOCUMENT_ROOT.'/core/tpl/list_print_total.tpl.php';

// If no record found
if ($num == 0) {
	$colspan = 1;
	foreach ($arrayfields as $key => $val) {
		if (!empty($val['checked'])) {
			$colspan++;
		}
	}
	print '<tr><td colspan="'.$colspan.'"><span class="opacitymedium">'.$langs->trans("NoRecordFound").'</span></td></tr>';
}

$db->free($resql);

$parameters = array('arrayfields' => $arrayfields, 'sql' => $sql);
$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object, $action);
print $hookmanager->resPrint;

print '</table>'."\n";
print '</div>'."\n";
print '</form>'."\n";

// End of page
llxFooter();
$db->close();
