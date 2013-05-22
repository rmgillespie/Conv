<?php
require_once('SupportingFiles/get_file_contents.php');

class Yahoo extends Service {

    # If the code requested is the baserate set the rate to 1
    # Otherwise attempt to get rate from yahoo
    # If returned rate could not be found print out error message
    function __construct($Code) {
	if ($Code == BASERATE) { //True if the code is the baserate
            $this->setRate(1);
	    return;
        } else {

			$Url = "http://download.finance.yahoo.com/d/quotes.csv?s=" . BASERATE . $Code . "=X&f=l1";
			$Contents = get_file_contents($Url);

			if (($Contents === false)) { //True if the request failed or the stream failed to open
				Error(3000); //Print out error message and exit
			} //End of if statement

			$Rate = doubleval($Contents);
			if (($Rate != "") || ($Rate != null)) {
				$this->setRate($Rate);
			} //End of if statement
        } //End of if else statement
    } //End of constructor
	
} //End of class file
?>