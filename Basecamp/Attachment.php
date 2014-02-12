<?php
class Basecamp_Attachment {
	var $token;
	var $name;

	public function __construct($token,$name) {
		$this->token = $token;
		$this->name  = $name;
	}
}
?>
