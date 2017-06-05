<?php
namespace Pragma\Historic;

class HistoricAction extends Model{
	const TABLE_NAME = 'historic_actions';

	public function __construct(){
		parent::__construct(self::TABLE_NAME);
	}
}
