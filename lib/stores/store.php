<?php
/******************************************************************************
  Generic Store 1.0						Ariadne 

  Copyright (C) 1998-2005  Muze 

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

  --------------------------------------------------------------------------

  This Is a Generic implementation of the store, all generic functions are defined here

  the implemented functions are

	function get_config($field) 
	function is_supported($feature) 
	function newobject($path, $parent, $type, $data, $id=0, $lastchanged=0, $vtype="", $size=0, $priority=0) 
	function close() 
	function make_path($curr_dir, $path) 
	function save_properties($properties, $id) 
	function get_filestore($name) 

*******************************************************************************/


abstract class store {

	public $error;
	public $root;
	public $rootoptions;
	public $mod_lock;
	public $total;
	protected $code;
	protected $files;
	protected $_filestores;
	protected $config;



	public function __construct($path, $config) {
		echo "You have not configured the store properly. Please check your configuration files.";
		exit();
	}

	/*  abstract functions
		 need implementation in your implementation of this store
	*/

	public abstract function call($template, $args, $objects);

	public abstract function count($objects);

	public abstract function info($objects);

	public abstract function get($path);
	/**********************************************************************************
	 This function takes as argument a path to an object in the store and will retrieve
	 all the necessary data and return this in the objectlist type needed for 
	 store->call(). If the requested path does not exist, it will retrieve the object
	 with the longest matching path.

	 $path should always start and end with a '/'.
	 **********************************************************************************/

	public abstract function touch($id, $timestamp = -1);
	/**********************************************************************************
	 This function takes as argument a path to an object (or id of an object)
     in the store and will set the timestamp to $timestamp.

	 $path should always start and end with a '/'.
	 **********************************************************************************/

	public abstract function ls($path);
	/**********************************************************************************
	 This function takes as argument a path to an object in the store and will retrieve
	 all the objects and their data which have this object as their parent. It will 
	 then return this in the objectlist type needed for store->call(). If the requested
	 path does not exist, it will retrieve the object with the longest matching path.

	 $path should always start and end with a '/'.
	 **********************************************************************************/

	public abstract function parents($path, $top="/");
	/**********************************************************************************
	 This function takes as argument a path to an object in the store. It will return 
	 all objects with a path which is a substring of the given path. The resulsts are 
	 ordered by path (length), shortest paths first.
	 In effect all parents of an object in the tree are called, in order, starting at 
	 the root.

	 $path should always start and end with a '/'.
	 **********************************************************************************/

	public abstract function find($path, $criteria, $limit=100, $offset=0);
	/**********************************************************************************
	 This function takes as arguments a path to an object in the store and some search
	 criteria. It will search for all matching objects under the given path. If the
	 given path is not in this store but in a substore it will not automatically search
	 that substore. 

	 $criteria is of the form 

	 $criteria ::= ({ $property_name => ({ $valuename => ({ $compare_function, $value }) }) }) 

	 e.g.: $criteria["status"]["value"][">"]="'published'";

	 $path should always start and end with a '/'.

	 **********************************************************************************/


	public abstract function save($path, $type, $data, $properties="", $vtype="", $priority=false);
	/***************************************************************
		This function takes as argument a path, type, objectdata and 
		possibly a properties list and vtype (virtual type).
		If there exists no object with the given path, a new object is 
		saved with the given type, data, properties and vtype, and a
		new path is saved pointing to it.
		If there does exist an object with the given path, it's object
		data is overwritten with the given data and if vtype is set the
		current vtype is overwritten with the new one.

		$path must be an absolute path (containing no '..' and starting
			with '/')
		$type must be a valid type
		$data can be any string (usually a serialized object.)
		$properties is a multidimensional hash of the following form:
			$properties[{property_name}][][{value_name}]={value}
			{property_name} must be a valid property name
			{value_name} must be a valid value name for this property
			{value} can be a number, boolean or string. If it is a string
				it must be enclosed in single qoutes. All other single 
				quotes in the string must be escaped. e.g:
				"'\'t is a String'"
		example:
			$properties["name"][0]["value"]="'A name'";
			$properties["name"][1]["value"]="'A second name!'";
		if $properties["name"]=1 then all properties for property name
			will be removed.

		$vtype must be a valid type.
	 
		if $properties or $vtype are not set or empty ("",0 or false)
		they will be ignored. $vtype defaults to $type.
		Only those properties listed in $properties will be updated.
		Any other property set will remain as it was.
	***************************************************************/


	protected abstract function purge($path);
	/**********************************************************************
		This function will delete the object pointed to by $path and all
	other paths pointing to that object. It will then remove any property
	for this object from all property tables.
		The function returns the number of paths found and removed or 1 if
	there was no path found (meaning that the object doesn't exist and 
	therefor purge succeeded while doing nothing.)

	 $path should always start and end with a '/'.
	**********************************************************************/

	public abstract function delete($path);
	/**********************************************************************
		This function deletes the path given. If this is the last path pointing
	to an object, the object will be purged instead.

	$path should always start and end with a '/'.
	**********************************************************************/

	abstract function exists($path);
	/**********************************************************************
		This function checks the given path to see if it exists. If it does
	it returns the id of the object to which it points. Otherwise it returns
	0.

	$path should always start and end with a '/'.
	**********************************************************************/


	public abstract function link($source, $destination);
	/**********************************************************************
		Link adds an extra path to an already existing object. It has two
	arguments: $source and $destination. $source is an existing path of
	an object, $destination is the new path. $destination must not already
	exist.

	$destination should always start and end with a '/'.
	**********************************************************************/

	public abstract function move($source, $destination);
	/**********************************************************************
	$destination should always start and end with a '/'.
	**********************************************************************/


	public abstract function list_paths($path);
	/**********************************************************************
		This function returns an array of all paths pointing to the same object 
	as $path does.
	**********************************************************************/

	public abstract function AR_implements($type, $implements);
	/**********************************************************************
		This function returns 1 if the $type implements the type or
	interface in $implements. Otherwise it returns 0.
	**********************************************************************/

	public abstract function load_properties($object, $values="");

	public abstract function load_property($object, $property, $values="");

	public abstract function add_property($object, $property, $values);

	public abstract function del_property($object, $property="", $values="");

	protected abstract function get_nextid($path, $mask="{5:id}");
	/**********************************************************************
		'private' function of mysql store. This will return the next
		'autoid' for $path.
	**********************************************************************/

	

	/*
		Implemented functions
	*/

	public function get_config($field) {
		switch ($field) {
			case 'code':
			case 'files':
			case 'root':
			case 'rootoptions':
				$result = $this->$field;
				break;
			default:
				$result =  null;
				debug("store::get_config: undefined field $field requested","store");
				break;
		}
		return $result;
	}

	public function is_supported($feature) {
	/**********************************************************************************
		This function takes as argument a feature description and returns
		true if this feature is supported and false otherwise
	**********************************************************************************/
		switch($feature) {
			case 'fulltext_boolean':
				if ($this->config["fulltext_boolean"]) {
					$result = true;
				} else {
					$result = false;
				}
			break;
			case 'fulltext':
				if ($this->config["fulltext"]) {
					$result = true;
				} else {
					$result = false;
				}
			break;
			case 'grants':
			case 'regexp':
				$result = true;
			break;
			default:
				$result = false;
			break;
		}
		return $result;
	}

	/**********************************************************************************
		This functions creates a new ariadne object
	**********************************************************************************/
	public function newobject($path, $parent, $type, $data, $id=0, $lastchanged=0, $vtype="", $size=0, $priority=0) {
		$class = $type;
		if ($subcpos = strpos($type, '.')) {
			$class = substr($type, 0, $subcpos);
			$vtype = $class;
		}
		if (!class_exists($class)) {
			include_once($this->code."objects/".$class.".phtml");
		}
		$object=new $class;
		$object->type=$type;
		$object->parent=$parent;
		$object->id=(int)$id;
		$object->lastchanged=(int)$lastchanged;
		$object->vtype=$vtype;
		$object->size=(int)$size;
		$object->priority=(int)$priority;
		$object->init($this, $path, $data);
		return $object;
	}

	public function close() {
		// This is the destructor function, nothing much to see :)
		if (is_array($this->_filestores)) {
			while (list($key, $filestore)=each($this->_filestores)) {
				$filestore->close();
			}
		}
	}

	public function __destruct() {
		$this->close();
	}
	
	public function make_path($curr_dir, $path) {
	/**********************************************************************
		This function creates an absolute path from the given starting path
	($curr_dir) and a relative (or absolute) path ($path). If $path starts
	with a '/' $curr_dir is ignored. 
	$path must be a string of substrings seperated by '/'. each of these 
	substrings may consist of charachters and/or numbers. If a substring
	is "..", it and the previuos substring will be removed. If a substring
	is "." or "", it is removed. All other substrings are then concatenated
	with a '/' between them and at the start and the end. This string is 
	then returned.
	**********************************************************************/
		$this->error = "";
		$result = "/";
		if ($path[0] === "/") {
			$path = substr($path, 1);
		} else {
			$path = substr($curr_dir, 1).'/'.$path;
		}
		if ($path) {
			$splitpath=explode("/", $path);
			foreach ($splitpath as $pathticle) {
				switch($pathticle) {
					case ".." :
						$result = dirname($result);
						// if second char of $result is not set, then current result is the rootNode
						if (isset($result[1])) {
							$result .= "/";
						}
						$result[0] = "/"; // make sure that even under windows, slashes are always forward slashes.
					break;
					case "." : break;
					case ""	 : break;
					default:
						$result .= $pathticle."/";
					break;
				}
			}
		}
		return $result;
	}

	public function save_properties($properties, $id) {
	/********************************************************************
		'private' function of mysql.phtml. It updates all property tables
		defined in $properties and sets the values to the values in
		$properties.
	********************************************************************/

		if ($properties && (is_array($properties)) && (is_int($id))) {
			while (list($property, $property_set)=each($properties)) {
				$this->del_property($id, $property);
				if (is_array($property_set)) {
					while (list($key, $values)=each($property_set)) {
						$this->add_property($id, $property, $values);
					}
				}
			}
		}
	}


	public function get_filestore($name) {
		global $AR;
		if (!$this->_filestores[$name]) {
			if ($AR->SVN->enabled && ($name == "templates")) {
				require_once($this->code."modules/mod_filestore_svn.phtml");
				$this->_filestores[$name]=new filestore_svn($name, $this->files, $this);
			} else {
				require_once($this->code."modules/mod_filestore.phtml");
				$this->_filestores[$name]=new filestore($name, $this->files, $this);
			}
		}
		return $this->_filestores[$name];
	}

	public function get_filestore_svn($name) {
		require_once($this->code."modules/mod_filestore_svn.phtml");
		if (!$this->_filestores["svn_" . $name]) {
			$this->_filestores["svn_" . $name] = new filestore_svn($name, $this->files, $this);
		}
		return $this->_filestores["svn_" . $name];
	}

	protected function compilerFactory(){
		switch($this->config["dbms"]){
			case 'axstore':
				return false;
			default:
				$compiler = $this->config["dbms"].'_compiler';
				return new $compiler($this,$this->tbl_prefix);
		}
	}

 
	public function __call($name,$arguments)
	{
		switch($name)
		{
		case "implements":
				return $this->AR_implements($arguments[0],$arguments[1]);
				break;
			default:
				return false;
		}
	}

} // end class store

?>