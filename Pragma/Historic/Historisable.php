<?php
namespace Pragma\Historic;

use Pragma\DB\DB;

trait Historisable{
	protected $is_historised = false; //tell if the object must be hisorized
	protected $histo_excluded = null;//ignored columns during the historise process
	protected $global_name = "";//when it's a delete, it store the name of the object deleted
	protected $stop_delete_propagation = false;//when the object is deleted, if true, pragma won't propagate the delete_name on old actions
	protected $should_store_creation_values = false;//tell if pragma should store values with the "create" action
	protected $was_new = true;
	protected $histo_ref = null;
	protected $initial_global_name = '';
	protected $global_name_fields = [];

	protected $action_classname = "Pragma\\Historic\\Action";
	protected $reference_classname = "Pragma\\Historic\\Reference";

	protected $overriden_classname = null;

	protected function historise($last = false){
		$action = null;
		if($this->is_historised()){
			if($this->was_new){
				$action = $this->historiseNew();
			}
			else{
				$action = $this->historiseUpdate();
			}
		}
		return $action;
	}

	protected function historiseNew(){
		//store only the actions, not all the details
		$action = $this->buildAction('C');

		$this->buildHistoRef($action);

		if($this->should_store_creation_values) {
			foreach($this->fields as $k => $v) {
				if( ! isset($this->histo_excluded[$k]) && array_key_exists($k, $this->describe()) ) {
					Change::build([
					'action_id'			=> $action->id,
					'field'					=> $k,
					'before'				=> null,
					'after'					=> $v,
					])->save();
				}
			}
		}

		return $action;
	}

	protected function historiseUpdate(){
		$action = null;

		$changes = $this->changes($this->histo_excluded);
		if( !empty($changes) ){
			$action = $this->buildAction('U');

			$this->buildHistoRef($action);

			foreach($changes as $k => $values){
				Change::build([
					'action_id'				=> $action->id,
					'field'					=> $k,
					'before'				=> $values['before'],
					'after'					=> $values['after'],
					])->save();
			}
		}
		return $action;
	}

  protected function buildAction($type) {
      $params = [
          'historisable_type' => self::get_classname($this),
          'historisable_id'   => $this->id,
          'type'              => $type,
          'global_name'       => $this->get_initial_global_name(),
      ];
      if(strtoupper($type) == 'D'){
          $params['deleted_name'] = $this->get_global_name();
      }
      return $this->action_classname::build($params)->save();
  }

  protected function buildHistoRef(Action $action) {
      if (!empty($this->histo_ref)) {
          foreach ($this->histo_ref as $ref) {
              $obj = $this->reference_classname::build([
                  'action_id' => $action->id,
                  'ref_type'  => self::get_classname($ref),
                  'ref_id'    => $ref->id,
              ]);
              if (method_exists($ref, 'get_initial_global_name')){
                  $obj->ref_global_name = $ref->get_initial_global_name();
              }
              $obj->save();
          }
      }
  }

	public function setActionClassname($classname) {
		$this->action_classname = $classname;
	}

	public function setReferenceClassname($classname) {
		$this->reference_classname = $classname;
	}

	public function set_historised($val){
		$this->is_historised = $val;
		if($val){
			$this->pushHook('after_save', 'historise', 'pragma:historic:historise');
			$this->pushHook('before_save', 'init_was_new', 'pragma:historic:init_was_new');
			$this->pushHook('before_delete', 'deleted_entry', 'pragma:historic:deleted_entry');
			$this->pushHook('after_open', 'init_histo_values', 'pragma:historic:init_histo_values');
			$this->pushHook('after_open', 'init_initial_global_name', 'pragma:historic:init_initial_global_name');
			$this->pushHook('after_build', 'init_histo_values', 'pragma:historic:init_histo_values_build');
		}
	}

	protected function init_histo_values() {
		$this->enableChangesDetection(true);
	}

	public function is_historised(){
		return $this->is_historised;
	}

	/*
	DEPRECATED: use self::set_global_name_fields() in __construct()
	 */
	public function set_global_name($name){
		$this->global_name = $name;
	}

	public function get_global_name(){
		if(empty($this->global_name) && !empty($this->global_name_fields)){
			$this->global_name = [];
			foreach($this->global_name_fields as $f){
				if(array_key_exists($f, $this->fields) && !empty($this->$f)){
					$this->global_name[] = $this->$f;
				}
			}
			$this->global_name = implode(' ', $this->global_name);
		}
		return $this->global_name;
	}

	public function set_histo_ref($ref){
		if (!is_array($ref)) {
			$ref = [$ref];
		}

		$this->histo_ref = $ref;
	}

	protected function init_was_new() {
		$this->was_new = $this->is_new();
	}

	protected function set_global_name_fields($fields = []){
		if(!is_array($fields)) {
			$fields = [$fields];
		}
		$this->global_name_fields = $fields;
		return $this;
	}

	protected function init_initial_global_name() {
		$this->initial_global_name = $this->get_global_name();
	}
	public function get_initial_global_name(){
		return empty($this->initial_global_name) ? $this->get_global_name() : $this->initial_global_name;
	}

	/* ignored fields are passed as arguments */
	public function ignore_fields(...$fields){
		if(empty($this->histo_excluded)){
			$this->histo_excluded = array_flip($fields);
		}else{
			$this->histo_excluded = array_merge($this->histo_excluded, array_flip($fields));
		}
	}

	public function stop_delete_propagation($val = true) {
		$this->stop_delete_propagation = $val;
	}

	public function deleted_entry(){
		$action = null;
		if($this->is_historised()){
			$action = $this->buildAction('D');

			$this->buildHistoRef($action);
		}

		if (! $this->stop_delete_propagation) {
			DB::getDB()->query("UPDATE 	".$this->action_classname::getTableName()."
								SET 	deleted_name = ?
								WHERE 	historisable_type = ?
								AND 	historisable_id = ?
								AND 	type != 'D'", [$this->get_global_name(), self::get_classname($this), $this->id]);
		}
		return $action;
	}

	public function historize_creation_values($val = true) {
		$this->should_store_creation_values = $val;
	}

	public function override_class_name($val) {
		$this->overriden_classname = $val;
	}

	private static function get_classname($object) {
		return (method_exists($object, "override_class_name") && $object->overriden_classname) ? $object->overriden_classname : get_class($object);
	}
}

