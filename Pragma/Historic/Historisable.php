<?php
namespace Pragma\Historic;

trait Historisable{
	protected $is_historised = false; //tell if the object must be hisorized
	protected $histo_excluded = null;//ignored columns during the historise process
	protected $global_name = "";//when it's a delete, it store the name of the object deleted
	protected $was_new = true;
	protected $histo_ref = null;

	protected function historise($last = false){
		$action = null;
		if($this->is_historised()){
			if($this->was_new){
				//store only the actions, not all the details
				$action = Action::build([
					'historisable_type' 		=> get_class($this),
					'historisable_id' 			=> $this->id,
					'type'									=> 'C',
				])->save();

				if (!empty($this->histo_ref)) {
					foreach ($this->histo_ref as $ref) {
						Reference::build([
							'action_id' => $action->id,
							'ref_type'  => get_class($ref),
							'ref_id'    => $ref->id,
						])->save();;
					}
				}
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
						'type'							=> 'U',
						])->save();

					if (!empty($this->histo_ref)) {
						foreach ($this->histo_ref as $ref) {
							Reference::build([
								'action_id' => $action->id,
								'ref_type'  => get_class($ref),
								'ref_id'    => $ref->id,
							])->save();
						}
					}

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
		return $action;
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
		if (!is_array($ref)) {
			$ref = [$ref];
		}

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
		if(empty($this->histo_excluded)){
			$this->histo_excluded = array_flip(func_get_args());
		}else{
			$this->histo_excluded = array_merge($this->histo_excluded, array_flip(func_get_args()));
		}
	}

	public function deleted_entry(){
		$action = null;
		if($this->is_historised()){
			$action = Action::build([
						'historisable_type' 		=> get_class($this),
						'historisable_id' 			=> $this->id,
						'type'									=> 'D',
						'deleted_name'					=> $this->get_global_name()
						])->save();

			if (!empty($this->histo_ref)) {
				foreach ($this->histo_ref as $ref) {
					Reference::build([
						'action_id' => $action->id,
						'ref_type'  => get_class($ref),
						'ref_id'    => $ref->id,
					])->save();
				}
			}
		}
		return $action;
	}
}

