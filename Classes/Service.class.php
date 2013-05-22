<?php
class Service {

    private $Rate;

    function __construct() {
    } //End of constructor

    public function setRate($Rate) {
		$this->Rate = $Rate;
    }
	
    public function getRate() {
        return $this->Rate;
    } //End of getRate function
    
} //End of class file
?>