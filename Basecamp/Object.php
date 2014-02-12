<?php
class Basecamp_Object {
	var $_basecamp;
	var $account;
	var $id;

	public function __construct($id,$basecamp='') {
		$this->id				= $id;
		if ( $basecamp ) {
			$this->_basecamp = $basecamp;
		}
	}

	public static function objectFromResponse($object,&$basecamp='') {
		$newObject = new static('','');
		foreach ((array)$object as $key => $val ) {
			$newObject->$key = $val;
		}
		if ( $basecamp ) {
			$newObject->setBasecamp($basecamp);
			$newObject->account = $basecamp->account;
		}
		return $newObject;
	}

	public function setBasecamp(Basecamp $basecamp) {
		$this->_basecamp = $basecamp;
	}

	public function basecampRequired() {
		if ( !$this->_basecamp ) {
			throw	new Exception('There is no Basecamp object associated with this object to make requests');
		}
	}

}
?>
