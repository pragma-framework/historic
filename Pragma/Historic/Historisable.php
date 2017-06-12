<?php
namespace Pragma\Historic;

trait Historisable{
	protected $is_historised = false; //tell if the object must be hisorized
	protected $histo_excluded = null;//ignored columns during the historise process
	protected $global_name = "";//when it's a delete, it store the name of the object deleted
	protected $was_new = true;
	protected $histo_ref = null;

	protected function historise($last = false){
		if($this->is_historised()){
			if($this->was_new){
				//store only the actions, not all the details
				$action = Action::build([
					'historisable_type' 		=> get_class($this),
					'historisable_id' 			=> $this->id,
					'historisable_ref_type'	=> ! is_null($this->histo_ref) ? get_class($this->histo_ref) : null,
					'historisable_ref_id'		=> ! is_null($this->histo_ref) ? $this->id : null,
					'type'									=> 'C',
					])->save();
			}
			else{
				$changes = [];
				foreach($this->fields as $k => $value){
					if( ! isset($this->histo_excluded[$k]) &&
							array_key_exists($k, $this->initial_values) &&
							$value != $this->initial_values[$k]
							){
						$changes[$k] = [
							'before' => $this->initial_values[$k],
							'after' => $this->fields[$k]
							];

					}
				}

				if( !empty($changes) ){
					$action = Action::build([
						'historisable_type' => get_class($this),
						'historisable_id' 	=> $this->id,
						'historisable_ref_type'	=> ! is_null($this->histo_ref) ? get_class($this->histo_ref) : null,
						'historisable_ref_id'		=> ! is_null($this->histo_ref) ? $this->id : null,
						'type'							=> 'U',
						])->save();

					foreach($changes as $k => $values){
						Change::build([
							'action_id'				=> $action->id,
							'field'						=> $k,
							'before'					=> $values['before'],
							'after'						=> $values['after'],
							])->save();
					}
				}
			}
			$this->init_histo_values($last);
		}
	}

	public function set_historised($val){
		$this->is_historised = $val;
		if($val){
			$this->pushHook('after_save', 'historise');
			$this->pushHook('before_save', 'init_was_new');
			$this->pushHook('before_delete', 'deleted_entry');
			$this->pushHook('after_open', 'init_histo_values');
		}
	}

	public function is_historised(){
		return $this->is_historised;
	}

	public function set_global_name($name){
		$this->global_name = $name;
	}

	public function get_global_name(){
		return $this->global_name;
	}

	public function set_histo_ref($ref){
		$this->histo_ref = $ref;
	}

	protected function init_histo_values($force = false){
		if(! $this->initialized || $force){
			$this->initial_values = $this->fields;
			$this->initialized = true;
		}
	}

	protected function init_was_new(){
		$this->was_new = $this->is_new();
	}

	/* ignored fields are passed as arguments */
	public function ignore_fields(){
		$this->histo_excluded = array_flip(func_get_args());
	}

	public function deleted_entry(){
		if($this->is_historised()){
			$action = Action::build([
						'historisable_type' 		=> get_class($this),
						'historisable_id' 			=> $this->id,
						'historisable_ref_type'	=> ! is_null($this->histo_ref) ? get_class($this->histo_ref) : null,
						'historisable_ref_id'		=> ! is_null($this->histo_ref) ? $this->histo_ref->id : null,
						'type'									=> 'D',
						'deleted_name'					=> $this->get_global_name()
						])->save();

		}
	}
}

