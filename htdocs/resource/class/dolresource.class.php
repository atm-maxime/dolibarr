<?php
/* Copyright (C) 2013-2015		Jean-François Ferry	<jfefe@aternatik.fr>
 * Copyright (C) 2023-2024		William Mead		<william.mead@manchenumerique.fr>
 * Copyright (C) 2024-2025	MDW						<mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024       Frédéric France             <frederic.france@free.fr>
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
 *  \file      	htdocs/resource/class/dolresource.class.php
 *  \ingroup    resource
 *  \brief      Class file for resource object
 */

require_once DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php";
require_once DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php";
require_once DOL_DOCUMENT_ROOT.'/core/class/commonpeople.class.php';

/**
 *  DAO Resource object
 */
class Dolresource extends CommonObject
{
	use CommonPeople;

	/**
	 * @var string	Module name
	 */
	public $module = 'resource';

	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'dolresource';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'resource';

	/**
	 * @var string	Prefix for triggers (RESOURCE_CREATE, RESOURCE_MODIFY, RESOURCE_DELETE)
	 */
	public $TRIGGER_PREFIX = 'RESOURCE';

	/**
	 * @var string String with name of icon for myobject.
	 */
	public $picto = 'resource';

	/**
	 * @var int<0,1>	Does object support extrafields? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var int<0,1>|string	Does this object support multicompany module?
	 */
	public $ismultientitymanaged = 1;

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array<string,array{type:string,label:string,enabled:int<0,1>|string,position:int,notnull?:int,visible:int|string,noteditable?:int,default?:string,index?:int,foreignkey?:string,searchall?:int,isameasure?:int,css?:string,csslist?:string,cssview?:string,help?:string,showoncombobox?:int,disabled?:int,arrayofkeyval?:array<int|string,string>,autofocusoncreate?:int,comment?:string}>
	 */
	public $fields = array(
		'rowid'                  => array('type' => 'integer',      'label' => 'TechnicalID',    'enabled' => 1, 'position' => 1,   'notnull' => 1,  'visible' => 0,  'noteditable' => 1, 'index' => 1, 'css' => 'left'),
		'ref'                    => array('type' => 'varchar(128)', 'label' => 'Ref',             'enabled' => 1, 'position' => 15,  'notnull' => 1,  'visible' => 1,  'index' => 1, 'searchall' => 1, 'showoncombobox' => 1, 'autofocusoncreate' => 1),
		'entity'                 => array('type' => 'integer',      'label' => 'Entity',          'default' => '1', 'enabled' => 1, 'position' => 20, 'notnull' => 1, 'visible' => 0, 'index' => 1),
		'fk_code_type_resource'  => array('type' => 'sellist:c_type_resource:label:code', 'label' => 'ResourceType', 'enabled' => 1, 'position' => 25, 'notnull' => 0, 'visible' => 1, 'css' => 'minwidth200'),
		'description'            => array('type' => 'html',         'label' => 'Description',     'enabled' => 1, 'position' => 30,  'notnull' => 0,  'visible' => 3,  'cssview' => 'wordbreak'),
		'address'                => array('type' => 'text',         'label' => 'Address',         'enabled' => 1, 'position' => 40,  'notnull' => 0,  'visible' => -1),
		'zip'                    => array('type' => 'varchar(25)',  'label' => 'Zip',             'enabled' => 1, 'position' => 45,  'notnull' => 0,  'visible' => -1),
		'town'                   => array('type' => 'varchar(50)',  'label' => 'Town',            'enabled' => 1, 'position' => 50,  'notnull' => 0,  'visible' => 1),
		'fk_country'             => array('type' => 'integer:Ccountry:core/class/ccountry.class.php', 'label' => 'CountryOrigin', 'enabled' => 1, 'position' => 55, 'notnull' => 0, 'visible' => 1,  'css' => 'maxwidth500 widthcentpercentminusxx', 'csslist' => 'tdoverflowmax150'),
		'fk_state'               => array('type' => 'integer:Cstate:core/class/cstate.class.php',    'label' => 'State',         'enabled' => 1, 'position' => 56, 'notnull' => 0, 'visible' => -1, 'css' => 'maxwidth500 widthcentpercentminusxx', 'csslist' => 'tdoverflowmax150'),
		'phone'                  => array('type' => 'phone',        'label' => 'Phone',           'enabled' => 1, 'position' => 60,  'notnull' => 0,  'visible' => -1),
		'email'                  => array('type' => 'email',        'label' => 'EMail',           'enabled' => 1, 'position' => 65,  'notnull' => 0,  'visible' => -1),
		'max_users'              => array('type' => 'integer',      'label' => 'MaxUsers',        'enabled' => 1, 'position' => 70,  'notnull' => 0,  'visible' => 1,  'help' => 'MaxUsersResourceDesc', 'css' => 'width75 right'),
		'url'                    => array('type' => 'url',          'label' => 'URL',             'enabled' => 1, 'position' => 75,  'notnull' => 0,  'visible' => -1, 'css' => 'minwidth300'),
		'note_public'            => array('type' => 'html',         'label' => 'NotePublic',      'enabled' => 1, 'position' => 80,  'notnull' => 0,  'visible' => 0,  'cssview' => 'wordbreak'),
		'note_private'           => array('type' => 'html',         'label' => 'NotePrivate',     'enabled' => 1, 'position' => 81,  'notnull' => 0,  'visible' => 0,  'cssview' => 'wordbreak'),
		'datec'                  => array('type' => 'datetime',     'label' => 'DateCreation',    'enabled' => 1, 'position' => 500, 'notnull' => 1,  'visible' => -2),
		'tms'                    => array('type' => 'timestamp',    'label' => 'DateModification','enabled' => 1, 'position' => 501, 'notnull' => 0,  'visible' => -2),
		'fk_user_author'         => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'picto' => 'user', 'enabled' => 1, 'position' => 510, 'notnull' => 0, 'visible' => -2, 'foreignkey' => '0', 'csslist' => 'tdoverflowmax150'),
		'fk_user_modif'          => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif',  'picto' => 'user', 'enabled' => 1, 'position' => 511, 'notnull' => -1, 'visible' => -2, 'csslist' => 'tdoverflowmax150'),
	);

	/**
	 * @var string 		Description
	 */
	public $description;

	/**
	 * @var string		Phone number
	 */
	public $phone;

	/**
	 * @var ?int		Maximum users
	 */
	public $max_users;

	/**
	 * @var string ID
	 */
	public $fk_code_type_resource;

	/**
	 * @var ?string
	 */
	public $type_label;

	/**
	 * @var int resource ID
	 * For resource-element link
	 * @see updateElementResource()
	 * @see fetchElementResource()
	 */
	public $resource_id;

	/**
	 * @var string resource type
	 */
	public $resource_type;

	/**
	 * @var int element ID
	 * For resource-element link
	 * @see updateElementResource()
	 * @see fetchElementResource()
	 */
	public $element_id;

	/**
	 * @var string element type
	 */
	public $element_type;

	/**
	 * @var int
	 */
	public $busy;

	/**
	 * @var int
	 */
	public $mandatory;

	/**
	 * @var int
	 */
	public $fulldayevent;

	/**
	 * @var int ID of the user who created the resource
	 */
	public $fk_user_author;

	/**
	 * @var int ID of the user who last modified the resource
	 */
	public $fk_user_modif;

	/**
	 * @var CommonObject	Used by fetchElementResource() to return an object
	 */
	public $objelement;

	/**
	 * @var array<int,array{code:string,label:string,active:int}>	Cache of type of resources.
	 */
	public $cache_code_type_resource = array();
	// END MODULEBUILDER PROPERTIES


	/**
	 *  Constructor
	 *
	 *  @param	DoliDB		$db      Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $langs;

		$this->db = $db;
		$this->status = 0;
		$this->cache_code_type_resource = array();

		if (!getDolGlobalInt('MAIN_SHOW_TECHNICAL_ID') && isset($this->fields['rowid']) && !empty($this->fields['ref'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object in database
	 *
	 * @param	User		$user		User that creates
	 * @param	int<0,1>	$notrigger	0=launch triggers after, 1=disable triggers
	 * @return	int						<0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = 0)
	{
		// Bridge CommonPeople property names to $fields DB column names
		$this->datec = dol_now();
		if (!empty($this->country_id)) {
			$this->fk_country = (int) $this->country_id;
		}
		if (!empty($this->state_id)) {
			$this->fk_state = (int) $this->state_id;
		}
		$this->fk_user_author = $user->id;

		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone an object into another one
	 *
	 * @param	User	$user		User that creates
	 * @param	int		$fromid		Id of object to clone
	 * @return	self|int<-1,-1>		New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		$result = $object->fetchCommon($fromid);

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_author);

		// Clear fields
		if (property_exists($object, 'ref')) {
			$object->ref = $langs->trans("CopyOf")." ".$object->ref;
		}
		if (property_exists($object, 'datec')) {
			$object->datec = dol_now();
		}

		// Clear unique extrafields
		if (is_array($object->array_options) && count($object->array_options) > 0) {
			$extrafields = new ExtraFields($this->db);
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option) {
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey])) {
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->setErrorsFromObject($object);
		}

		unset($object->context['createfromclone']);

		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object into memory from database
	 *
	 * @param	int		$id				Id of object
	 * @param	string	$ref			Ref of object
	 * @param	int		$noextrafields	0=Default to load extrafields, 1=No extrafields
	 * @param	int		$nolines		0=Default, 1=No lines (unused, kept for signature compat)
	 * @return	int						<0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null, $noextrafields = 0, $nolines = 0)
	{
		$result = $this->fetchCommon($id, $ref, '', $noextrafields);
		if ($result > 0) {
			// BC aliases: CommonPeople / legacy code uses these property names
			$this->date_creation     = $this->datec;
			$this->date_modification = $this->tms;
			$this->country_id        = (int) $this->fk_country;
			$this->state_id          = (int) $this->fk_state;

			// Load resource type label from cache (avoids a JOIN)
			if (!empty($this->fk_code_type_resource)) {
				$this->loadCacheCodeTypeResource();
				foreach ($this->cache_code_type_resource as $entry) {
					if ($entry['code'] == $this->fk_code_type_resource) {
						$this->type_label = $entry['label'];
						break;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Update object in database
	 *
	 * @param	User		$user		User that modifies
	 * @param	int<0,1>	$notrigger	0=launch triggers after, 1=disable triggers
	 * @return	int						<0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = 0)
	{
		global $conf, $langs;

		// Bridge CommonPeople property names to $fields DB column names
		if (isset($this->country_id)) {
			$this->fk_country = (int) $this->country_id;
		}
		if (isset($this->state_id)) {
			$this->fk_state = (int) $this->state_id;
		}

		// Handle directory rename when ref changes
		if (is_object($this->oldcopy) && !$this->oldcopy->isEmpty() && $this->oldcopy->ref !== $this->ref) {
			if (!empty($conf->resource->dir_output)) {
				$olddir = $conf->resource->dir_output."/".dol_sanitizeFileName($this->oldcopy->ref);
				$newdir = $conf->resource->dir_output."/".dol_sanitizeFileName($this->ref);
				if (file_exists($olddir)) {
					if (!@rename($olddir, $newdir)) {
						$langs->load("errors");
						$this->error = $langs->trans('ErrorFailToRenameDir', $olddir, $newdir);
						return -1;
					}
				}
			}
		}

		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Load data of resource links into memory from database
	 *
	 * @param	int		$id		Id of link element_resources
	 * @return	int				<0 if KO, >0 if OK
	 */
	public function fetchElementResource(int $id)
	{
		$sql = "SELECT";
		$sql .= " t.rowid,";
		$sql .= " t.resource_id,";
		$sql .= " t.resource_type,";
		$sql .= " t.element_id,";
		$sql .= " t.element_type,";
		$sql .= " t.busy,";
		$sql .= " t.mandatory,";
		$sql .= " t.fk_user_create,";
		$sql .= " t.tms as date_modification";
		$sql .= " FROM ".MAIN_DB_PREFIX."element_resources as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		dol_syslog(get_class($this)."::fetchElementResource", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id              = $obj->rowid;
				$this->resource_id     = $obj->resource_id;
				$this->resource_type   = $obj->resource_type;
				$this->element_id      = $obj->element_id;
				$this->element_type    = $obj->element_type;
				$this->busy            = $obj->busy;
				$this->mandatory       = $obj->mandatory;
				$this->fk_user_author  = $obj->fk_user_create;
				$this->date_modification = $obj->date_modification;

				if ($obj->element_id && $obj->element_type) {
					$this->objelement = fetchObjectByElement($obj->element_id, $obj->element_type);
				}
			}
			$this->db->free($resql);
			return $this->id;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Delete a resource object
	 *
	 * @param	User		$user		User making the change
	 * @param	int<0,1>	$notrigger	Disable all triggers
	 * @return	int						>0 if OK, <0 if KO
	 */
	public function delete(User $user, $notrigger = 0)
	{
		global $conf;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$rowid = $this->id;
		$error = 0;

		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element." WHERE rowid = ".((int) $rowid);
		dol_syslog(get_class($this)."::delete", LOG_DEBUG);
		if (!$this->db->query($sql)) {
			$this->error = $this->db->lasterror();
			$error++;
		}

		// Delete resource-element links
		if (!$error) {
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."element_resources";
			$sql .= " WHERE element_type='resource' AND resource_id = ".((int) $rowid);
			dol_syslog(get_class($this)."::delete element_resources", LOG_DEBUG);
			if (!$this->db->query($sql)) {
				$this->error = $this->db->lasterror();
				$error++;
			}
		}

		// Remove extrafields
		if (!$error) {
			$result = $this->deleteExtraFields();
			if ($result < 0) {
				$error++;
				dol_syslog(get_class($this)."::delete error on deleteExtraFields: ".$this->error, LOG_ERR);
			}
		}

		if (!$error && !$notrigger) {
			$result = $this->call_trigger('RESOURCE_DELETE', $user);
			if ($result < 0) {
				$error++;
			}
		}

		// Remove directory
		if (!$error && !empty($conf->resource->dir_output)) {
			$dir = $conf->resource->dir_output."/".dol_sanitizeFileName($this->ref);
			if (file_exists($dir)) {
				if (!@dol_delete_dir_recursive($dir)) {
					$this->errors[] = 'ErrorFailToDeleteDir';
					$error++;
				}
			}
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load resource objects into $this->lines
	 *
	 * @param	string				$sortorder	Sort order
	 * @param	string				$sortfield	Sort field
	 * @param	int					$limit		Limit page
	 * @param	int					$offset		Offset page
	 * @param	string|array<string,mixed>	$filter	Filter USF
	 * @return	int								<0 if KO, number of lines loaded if OK
	 */
	public function fetchAll(string $sortorder = '', string $sortfield = '', int $limit = 0, int $offset = 0, $filter = '')
	{
		require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);

		$sql = "SELECT ";
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
		// Add fields from extrafields
		if (!empty($extrafields->attributes[$this->table_element]['label'])) {
			foreach ($extrafields->attributes[$this->table_element]['label'] as $key => $val) {
				$sql .= ($extrafields->attributes[$this->table_element]['type'][$key] != 'separate' ? "ef.".$key." as options_".$key.', ' : '');
			}
		}
		$sql .= " ty.label as type_label";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_resource as ty ON ty.code=t.fk_code_type_resource";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX.$this->table_element."_extrafields as ef ON ef.fk_object=t.rowid";
		$sql .= " WHERE t.entity IN (".getEntity('resource').")";

		// Manage filter
		if (is_array($filter)) {
			foreach ($filter as $key => $value) {
				if (strpos($key, 'date')) {
					$sql .= " AND ".$this->db->sanitize($key)." = '".$this->db->idate($value)."'";
				} elseif (strpos($key, 'ef.') !== false) {
					$sql .= " AND ".$this->db->sanitize($key)." = ".((float) $value);
				} else {
					$sql .= " AND ".$this->db->sanitize($key)." LIKE '%".$this->db->escape($this->db->escapeforlike($value))."%'";
				}
			}
			$filter = '';
		}

		$errormessage = '';
		$sql .= forgeSQLFromUniversalSearchCriteria($filter, $errormessage);
		if ($errormessage) {
			$this->errors[] = $errormessage;
			dol_syslog(__METHOD__.' '.implode(',', $this->errors), LOG_ERR);
			return -1;
		}

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		dol_syslog(get_class($this)."::fetchAll", LOG_DEBUG);

		$this->lines = array();
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			while ($obj = $this->db->fetch_object($resql)) {
				$line = new Dolresource($this->db);
				$line->id                   = $obj->rowid;
				$line->ref                  = $obj->ref;
				$line->address              = $obj->address;
				$line->zip                  = $obj->zip;
				$line->town                 = $obj->town;
				$line->country_id           = $obj->fk_country;
				$line->fk_country           = $obj->fk_country;
				$line->state_id             = $obj->fk_state;
				$line->fk_state             = $obj->fk_state;
				$line->description          = $obj->description;
				$line->phone                = $obj->phone;
				$line->email                = $obj->email;
				$line->max_users            = $obj->max_users;
				$line->url                  = $obj->url;
				$line->fk_code_type_resource = $obj->fk_code_type_resource;
				$line->date_modification    = $obj->date_modification;
				$line->date_creation        = $obj->date_creation;
				$line->type_label           = $obj->type_label;

				$line->fetch_optionals();

				$this->lines[] = $line;
			}
			$this->db->free($resql);
			return $num;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Update element resource in database
	 *
	 * @param	?User		$user		User that modifies
	 * @param	int<0,1>	$notrigger	0=launch triggers after, 1=disable triggers
	 * @return	int						<0 if KO, >0 if OK
	 */
	public function updateElementResource($user = null, int $notrigger = 0)
	{
		$error = 0;
		$this->date_modification = dol_now();

		// Clean parameters
		if (!is_numeric($this->resource_id)) {
			$this->resource_id = 0;
		}
		if (isset($this->resource_type)) {
			$this->resource_type = trim($this->resource_type);
		}
		if (!is_numeric($this->element_id)) {
			$this->element_id = 0;
		}
		if (isset($this->element_type)) {
			$this->element_type = trim($this->element_type);
		}
		$this->busy      = (int) $this->busy;
		$this->mandatory = (int) $this->mandatory;

		$sql = "UPDATE ".MAIN_DB_PREFIX."element_resources SET";
		$sql .= " resource_id = ".(isset($this->resource_id) ? (int) $this->resource_id : "null").",";
		$sql .= " resource_type = ".(isset($this->resource_type) ? "'".$this->db->escape($this->resource_type)."'" : "null").",";
		$sql .= " element_id = ".(isset($this->element_id) ? (int) $this->element_id : "null").",";
		$sql .= " element_type = ".(isset($this->element_type) ? "'".$this->db->escape($this->element_type)."'" : "null").",";
		$sql .= " busy = ".(isset($this->busy) ? (int) $this->busy : "null").",";
		$sql .= " mandatory = ".(isset($this->mandatory) ? (int) $this->mandatory : "null").",";
		$sql .= " tms = ".(dol_strlen((string) $this->date_modification) != 0 ? "'".$this->db->idate($this->date_modification)."'" : 'null');
		$sql .= " WHERE rowid=".((int) $this->id);

		$this->db->begin();

		dol_syslog(get_class($this)."::updateElementResource", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
		}

		if (!$error && $user !== null && !$notrigger) {
			$result = $this->call_trigger('RESOURCE_MODIFY', $user);
			if ($result < 0) {
				$error++;
			}
		}

		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::updateElementResource ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * Return an array with resources linked to the element
	 *
	 * @param	string		$element		Element
	 * @param	int			$element_id		Id
	 * @param	string		$resource_type	Type
	 * @return	array<array{rowid:int,resource_id:int,resource_type:string,busy:int<0,1>,mandatory:int<0,1>}>
	 */
	public function getElementResources(string $element, int $element_id, string $resource_type = '')
	{
		$sql = 'SELECT rowid, resource_id, resource_type, busy, mandatory';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'element_resources';
		$sql .= " WHERE element_id=".((int) $element_id)." AND element_type='".$this->db->escape($element)."'";
		if ($resource_type) {
			$sql .= " AND resource_type LIKE '%".$this->db->escape($resource_type)."%'";
		}
		$sql .= ' ORDER BY resource_type';

		dol_syslog(get_class($this)."::getElementResources", LOG_DEBUG);

		$resources = array();
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);
				$resources[$i] = array(
					'rowid'         => $obj->rowid,
					'resource_id'   => $obj->resource_id,
					'resource_type' => $obj->resource_type,
					'busy'          => $obj->busy,
					'mandatory'     => $obj->mandatory,
				);
				$i++;
			}
		}

		return $resources;
	}

	/**
	 * Return an int number of resources linked to the element
	 *
	 * @param	string	$elementType	Element type
	 * @param	int		$elementId		Element id
	 * @return	int						Number of resources loaded
	 */
	public function fetchElementResources(string $elementType, int $elementId)
	{
		$resources = $this->getElementResources($elementType, $elementId);
		$i = 0;
		foreach ($resources as $resource) {
			$this->lines[$i] = fetchObjectByElement($resource['resource_id'], $resource['resource_type']);
			$i++;
		}
		return $i;
	}

	/**
	 * Load in cache resource type code (setup in dictionary)
	 *
	 * @return	int		<0 if KO, 0 if already loaded, number of lines loaded if OK
	 */
	public function loadCacheCodeTypeResource()
	{
		global $langs;

		if (is_array($this->cache_code_type_resource) && count($this->cache_code_type_resource)) {
			return 0;
		}

		$sql = "SELECT rowid, code, label, active";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_type_resource";
		$sql .= " WHERE active > 0";
		$sql .= " ORDER BY rowid";
		dol_syslog(get_class($this)."::loadCacheCodeTypeResource", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);
				$label = ($langs->trans("ResourceTypeShort".$obj->code) != "ResourceTypeShort".$obj->code ? $langs->trans("ResourceTypeShort".$obj->code) : ($obj->label != '-' ? $obj->label : ''));
				$this->cache_code_type_resource[$obj->rowid]['code']   = $obj->code;
				$this->cache_code_type_resource[$obj->rowid]['label']  = $label;
				$this->cache_code_type_resource[$obj->rowid]['active'] = $obj->active;
				$i++;
			}
			return $num;
		} else {
			dol_print_error($this->db);
			return -1;
		}
	}

	/**
	 * getTooltipContentArray
	 *
	 * @param	array<string,mixed>	$params	Params to construct tooltip data
	 * @since	v18
	 * @return	array{picto?:string,ref?:string,label?:string}|array{optimize:string}
	 */
	public function getTooltipContentArray($params)
	{
		global $langs;

		$langs->load('resource');

		if (getDolGlobalInt('MAIN_OPTIMIZEFORTEXTBROWSER')) {
			return array('optimize' => $langs->trans("ShowResource"));
		}

		$datas = array();
		$datas['picto'] = img_picto('', $this->picto).' <u>'.$langs->trans("Resource").'</u>';
		$datas['ref']   = '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;
		if (isset($this->type_label)) {
			$datas['label'] = '<br><b>'.$langs->trans("ResourceType").':</b> '.$this->type_label;
		}

		return $datas;
	}

	/**
	 * Return clickable link of object (with optional picto)
	 *
	 * @param	int<0,2>	$withpicto				Add picto into link
	 * @param	string		$option					Where point the link
	 * @param	int<0,1>	$notooltip				1=Disable tooltip
	 * @param	string		$morecss				Add more css on link
	 * @param	int<-1,1>	$save_lastsearch_value	-1=Auto, 0=No save, 1=Save lastsearch_values
	 * @return	string								String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager, $action;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1;
		}

		$result = '';
		$params = array(
			'id'         => $this->id,
			'objecttype' => $this->element.($this->module ? '@'.$this->module : ''),
			'option'     => $option,
		);
		$classfortooltip = 'classfortooltip';
		$dataparams = '';
		if (getDolGlobalInt('MAIN_ENABLE_AJAX_TOOLTIP')) {
			$classfortooltip = 'classforajaxtooltip';
			$dataparams = ' data-params="'.dol_escape_htmltag(json_encode($params)).'"';
			$label = '';
		} else {
			$label = implode($this->getTooltipContentArray($params));
		}

		$url = DOL_URL_ROOT.'/resource/card.php?id='.$this->id;

		if ($option != 'nolink') {
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && isset($_SERVER["PHP_SELF"]) && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (getDolGlobalString('MAIN_OPTIMIZEFORTEXTBROWSER')) {
				$label = $langs->trans("ShowResource");
				$linkclose .= ' alt="'.dolPrintHTMLForAttribute($label).'"';
			}
			$linkclose .= ($label ? ' title="'.dolPrintHTMLForAttribute($label).'"' : ' title="tocomplete"');
			$linkclose .= $dataparams.' class="'.$classfortooltip.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink') {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		$linkend = ($option == 'nolink') ? '</span>' : '</a>';

		$result .= $linkstart;
		if ($withpicto) {
			$result .= img_object(($notooltip ? '' : $label), ($this->picto ?: 'generic'), (($withpicto != 2) ? 'class="paddingright"' : ''), 0, 0, $notooltip ? 0 : 1);
		}
		if ($withpicto != 2) {
			$result .= $this->ref;
		}
		$result .= $linkend;

		$hookmanager->initHooks(array($this->element.'dao'));
		$parameters = array('id' => $this->id, 'getnomurl' => &$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action);
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 * Return a thumb for kanban views
	 *
	 * @param	string				$option		Where point the link
	 * @param	?array<string,mixed>	$arraydata	Array of data
	 * @return	string							HTML Code for Kanban thumb.
	 */
	public function getKanbanView($option = '', $arraydata = null)
	{
		global $langs;

		$selected = (empty($arraydata['selected']) ? 0 : $arraydata['selected']);

		$return = '<div class="box-flex-item box-flex-grow-zero">';
		$return .= '<div class="info-box info-box-sm">';
		$return .= '<span class="info-box-icon bg-infobox-action">';
		$return .= img_picto('', $this->picto);
		$return .= '</span>';
		$return .= '<div class="info-box-content">';
		$return .= '<span class="info-box-ref inline-block tdoverflowmax150 valignmiddle">'.(method_exists($this, 'getNomUrl') ? $this->getNomUrl() : $this->ref).'</span>';
		if ($selected >= 0) {
			$return .= '<input id="cb'.$this->id.'" class="flat checkforselect fright" type="checkbox" name="toselect[]" value="'.$this->id.'"'.($selected ? ' checked="checked"' : '').'>';
		}
		if (!empty($this->type_label)) {
			$return .= ' <div class="inline-block opacitymedium valignmiddle tdoverflowmax100">'.$this->type_label.'</div>';
		}
		if ($this->max_users > 0) {
			$return .= '<br><span class="info-box-label">'.img_picto('', 'user', 'class="pictofixedwidth"').$this->max_users.'</span>';
		}
		$return .= '</div>';
		$return .= '</div>';
		$return .= '</div>';

		return $return;
	}

	/**
	 * Get status label
	 *
	 * @param	int<0,6>	$mode	0=long label, 1=short label, 2=Picto+short label, 3=Picto, 4=Picto+long label, 5=Short+Picto, 6=Long+Picto
	 * @return	string				Label of status
	 */
	public function getLibStatut(int $mode = 0)
	{
		return $this->getLibStatusLabel($this->status, $mode);
	}

	/**
	 * Get status label
	 *
	 * @param	int			$status		Status id
	 * @param	int<0,6>	$mode		Label format
	 * @return	string					Label of status
	 */
	public static function getLibStatusLabel(int $status, int $mode = 0)
	{
		return '';
	}

	/**
	 * Initialize object with example values
	 *
	 * @return int
	 */
	public function initAsSpecimen()
	{
		$this->id              = 0;
		$this->ref             = 'RESOURCE-SPECIMEN';
		$this->fk_code_type_resource = '';
		$this->description     = 'Specimen resource description';
		$this->address         = '1 Example Street';
		$this->zip             = '75001';
		$this->town            = 'Paris';
		$this->fk_country      = 1;
		$this->country_id      = 1;
		$this->phone           = '+33 1 00 00 00 00';
		$this->email           = 'specimen@example.com';
		$this->max_users       = 10;
		$this->url             = 'https://www.example.com';
		$this->note_public     = 'Public note for specimen';
		$this->note_private    = '';
		$this->datec           = dol_now();
		$this->date_creation   = $this->datec;

		return 1;
	}

	/**
	 * Load info information in the object
	 *
	 * @param	int		$id		Id of object
	 * @return	void
	 */
	public function info($id)
	{
		$sql = "SELECT t.rowid, t.datec, t.tms as datem, t.fk_user_author, t.fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id                   = $obj->rowid;
				$this->user_creation_id     = $obj->fk_user_author;
				$this->user_modification_id = $obj->fk_user_modif;
				$this->date_creation        = $this->db->jdate($obj->datec);
				$this->date_modification    = empty($obj->datem) ? '' : $this->db->jdate($obj->datem);
			}
			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Load indicators this->nb for state board
	 *
	 * @return	int		<0 if KO, >0 if OK
	 */
	public function loadStateBoard()
	{
		$this->nb = array();

		$sql = "SELECT count(r.rowid) as nb";
		$sql .= " FROM ".MAIN_DB_PREFIX."resource as r";
		$sql .= " WHERE r.entity IN (".getEntity('resource').")";

		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$this->nb["dolresource"] = $obj->nb;
			}
			$this->db->free($resql);
			return 1;
		} else {
			dol_print_error($this->db);
			$this->error = $this->db->error();
			return -1;
		}
	}
}
