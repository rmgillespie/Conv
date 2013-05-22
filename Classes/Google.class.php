<?php
require_once('SupportingFiles/get_file_contents.php');

class Google extends Service {

    # If the code requested is the baserate set the rate to 1
    # Otherwise attempt to get rate from Google
    # If returned rate could not be found print out error message
    function __construct($Code) {
        if ($Code == BASERATE) { //True if the code is the baserate
            $this->setRate(1);
			return;
        } else {
			
			$Url = "http://www.google.com/ig/calculator?hl=en&q=1" . BASERATE . "%3D%3F" . $Code;
			$Contents = get_file_contents($Url);

			if (($Contents === false)) { //True if the request failed or the stream failed to open
				Error(3000); //Print out error message and exit
			} //End of if statement

			$Conversion_Data = explode("\"",$Contents); 
			$Rate_Data = (explode(" ", $Conversion_Data[3])); 
			$Rate = doubleval($Rate_Data[0]); 

			if (($Rate != "") || ($Rate != null)) {
				$this->setRate($Rate);
			} //End of if statement
        } //End of if else
    } //End of constructor
   
} //End of class file
?>