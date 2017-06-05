<?php
namespace Pragma\Historic;

trait Historisable{
	protected $is_historised = false; //indique si l'objet doit être historisé ou non
	protected $initial_values = null;//stocke son tableau de valeur initiale
	protected $histo_excluded = null;//tableau de colonnes ignorées dans la détection de changements
	protected $wasnew = false;
	protected $global_name = "";//en cas de suppression permet d'indiquer quelle colonne utiliser pour alimenter le deleted_name de l'historic
	protected $histo_ref = null;

	protected function historise($last = false){
		//on n'enregistre que les updates pour l'instant, pas les create
		if($this->is_historised() && ! $this->wasnew){
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
				$action = HistoricAction::build([
					'historisable_type' => get_class($this),
					'historisable_id' 	=> $this->id,
					'historisable_ref_type'	=> ! is_null($this->histo_ref) ? get_class($this->histo_ref) : null,
					'historisable_ref_id'		=> ! is_null($this->histo_ref) ? $this->id : null,
					'type'							=> 'U',
					])->save();

				foreach($changes as $k => $values){
					HistoricChange::build([
						'action_id'				=> $action->id,
						'field'						=> $k,
						'before'					=> $values['before'],
						'after'						=> $values['after'],
						])->save();
				}
			}
			$this->init_histo_values($last);
		}
	}

	public function set_historised($val){
		$this->is_historised = $val;
		if($val){
			$this->pushHook('after_save', 'historise');
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

	public function set_histo_ref(\Pragma\ORM\Model $ref){
		$this->histo_ref = $ref;
	}

	protected function init_histo_values($force = false){
		if(! $this->initialized || $force){
			$this->initial_values = $this->fields;
			$this->initialized = true;
		}
	}

	/* on peut passer les colonnes à ignorer directement dans les params */
	public function ignore_fields(){
		$this->histo_excluded = array_flip(func_get_args());
	}

	public function deleted_entry(){
		if($this->is_historised()){
			$action = HistoricAction::build([
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

