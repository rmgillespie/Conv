<?php
class Converter_Service {

    private $Currency_Info_XML;
    private $Currency_Rates_XML;

    function __construct() {
    } //End of constructor

    function __get($Var) {
		return $this->$Var;
    } //End of getter function

       
    # If the local xml file containing the exchange rates does not exist an attempt is made to create it. If this fails return false
    # If the local xml file containing the currency information does not exist or is unavailable return false
    public function Is_Service_Available() {
		# If the xml file containing the exchange 
		# rates does not exist create it
		if (!$this->Local_Rates_Exist()) { //True if the rates xml file does not exist
			if (!$this->Create_Local_Rates_File()) { //Attempt to create a new local xml file to store currency rates
				return false;
			} //End of if statement
		} //End of if statement

		# Attempt to set the local variable to store rate data from the rates xml file. If this fails return false
		$Dom = new DOMDocument(); //Creates a new DOM document
		if ($Dom->load(LOCAL_EXCHANGE_RATES_XML_URI)) {
			$this->Currency_Rates_XML = $Dom->saveXML();
		} else {
			return false;
		} //End of if else

		# If the xml file containing the currency info does not exist or is unavailable return false
		if ($this->Currency_Info_Exist()) { //True if the currency info xml file is available
			return true;
		} //End of if statement
		return false;
    } //End of Is_Service_Available function

	# If the local xml file containing exchange rate data exists and is readable return true otherwise return false
    private function Local_Rates_Exist() {
	if (file_exists(LOCAL_EXCHANGE_RATES_XML_URI) && is_readable(LOCAL_EXCHANGE_RATES_XML_URI)) {
	    return true;
	} //End of if statement
	return false;
    } //End of Local_Rates_Exist function


    # Attempt to create a new file to store exchange rate data
    private function Create_Local_Rates_File() {
		$xml = new DOMDocument('1.0', 'UTF-8');
		$xml->appendChild($xml->createElement("conv"));
		return $xml->save(LOCAL_EXCHANGE_RATES_XML_URI);
    } //End of Create_Local_Rates_File function

	
	# Attempts to get new rate for the code from Yahoo and/or Google. If the rate is not found return false. Otherwise update the xml file containing the exchange rate data. If the update fails return false.
    public function Add_New_Rate($Code) {
		# Attempt to get new rate from Yahoo. If the rate is null get the rate from Google
		$Rate = $this->Get_New_Rate_From_Service("Yahoo", $Code);
		if ($Rate == null) {
			$Rate = $this->Get_New_Rate_From_Service("Google", $Code);
		} //End of if statement

		if ($Rate == null) { //True if the rate was not returned/found
			return false;
		} else {
			$Xml = new SimpleXMLElement($this->Currency_Rates_XML);
			$Currency = $Xml->addChild('currency');
			$Currency->addAttribute('last_updated', date("F d Y H:i:s", time()));
			$Currency->addAttribute('code', $Code);
			$Currency->addAttribute('rate', $Rate);
			if (!$Xml->saveXML(LOCAL_EXCHANGE_RATES_XML_URI)) {
			return false;
			} else {
			$this->Currency_Rates_XML = $Xml->SaveXML();
			return true;
			} //End of if else statement
		} //End of if else statement
    } //End of Add_New_Rate function
	
	
	# Attempts to get new rate for the code from Yahoo and/or Google. If the rate is not found print out error and exit. Otherwise attempt to update the local xml file containing exchange rate data. If this fails return false otherwise return the rate	
    public function Update_Existing_Rate($Code, $Currency_Rates_Doc, $Code_Xpath) {
		# Attempt to get new rate from Yahoo. If the rate is null get the rate from Google
		$Rate = $this->Get_New_Rate_From_Service("Yahoo", $Code);
		if ($Rate == null) {
			$Rate = $this->Get_New_Rate_From_Service("Google", $Code);
		} //End of if statement

		if ($Rate != null) { //True if rate was found
			# Update the xml file with new rate and change the last updated attribute
			$Code_Xpath->item(0)->setAttribute('last_updated', date("F d Y H:i:s", time()));
			$Code_Xpath->item(0)->setAttribute('rate', $Rate);
			if (!$Currency_Rates_Doc->save(LOCAL_EXCHANGE_RATES_XML_URI)) { //True if update fails
			Error(3000); //Print out error message and exit
			} else {
			$this->Currency_Rates_XML = $Currency_Rates_Doc->saveXML();
			return $Rate;
			} //End of if else
		} else { //True if the rate was null from both Yahoo and Google
			Error(3000);
		} //End of if else	
    } //End of Update_Existing_Rate function

    
    # Returns the rate for the requested code from the requested service
    private function Get_New_Rate_From_Service($Service_Name, $Code) {
		$Service = new $Service_Name($Code);
		return $Service->getRate();
	} //End of Get_New_Rate_From_Service function
	
	
    # If the local xml file containing currency information exists and is readable attempt to get the data and assign it to a local variable. If this fails or the file does not exist return false
	private function Currency_Info_Exist() {
		if (file_exists(CURRENCY_INFORMATION_URI) && is_readable(CURRENCY_INFORMATION_URI)) {
			$Dom = new DOMDocument(); //Creates a new DOM document
			if ($Dom->load(CURRENCY_INFORMATION_URI)) { //True if file was loaded successfully
			$this->Currency_Info_XML = $Dom->saveXML(); //Sets the currency ino xml
			return true;
			} else { //True if an error occured when loading the file
			return false;
			} //End of if else statement
		} //End of if statement
		return false;
    } //End of Currency_Info_Exist function
    
} //End of class file
?>