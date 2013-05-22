<?php
class Currency_Converter extends Converter_Service {

    private $Amount;
    private $From_Code;
    private $From_Rate;
    private $To_Code;
    private $To_Rate;

    function __construct($Amount, $From, $To) {
        $this->Amount = $Amount;
        $this->From_Code = $From;
        $this->To_Code = $To;
    } //End of constructor
    
    function __get($Var) {
        return $this->$Var;
    } //End of getter function
    


    # If the amount is invalid the appropriate error code is returned. Checks if request codes exist; if the code does not exist attempt to get the new code and add it to xml file. If this fails the appropriate error code is returned
    public function Validate_Parameters(&$Error_Code) {
        $Amount = $this->Amount;

        if (!is_numeric($Amount)) { //True if the amount is not numeric
            $Error_Code = 2300;
            return false;
        } else {

            if ($Amount < 0) { //True if the amount is negative
                $Error_Code = 2200;
                return false;
            } else {

                if (!preg_match('/^\d+(\.(\d{2}))?$/', $Amount)) { //True if the amount is to more than 2 decimal places
                    $Error_Code = 2100;
                    return false;
                } else { //True if the amount is valid 
                    
                    if (!$this->Codes_Exist($Error_Code)) { //True if the codes exist in the local xml file
						$Error_Code = 2000;
                        return false;
                    } //End of if statement
                    return true;
                } //End of if else
            } //End of if else
        } //End of if else
    } //End of Validate_Parameters function
	

	# Attempts to find both from and to codes in the local xml file. If the code does not exist attempt to get the rate for the code and add it to the local xml file. If both codes exist return true
    private function Codes_Exist(&$Error_Code) {
        # Create a new dom document with the local xml file containing the exchange rate data
        $Currency_Rates_Doc = new DomDocument();
        $Currency_Rates_Doc->loadXML(parent::__get('Currency_Rates_XML'));
        $Xpath = new DomXPath($Currency_Rates_Doc);
        $Codes_Found = 0;

        for ($i=0; $i<2; $i++) { //Loop through twice (for every code - From and To)
            # Get the current code
            switch ($i) {
                case 0: $Current_Code = $this->From_Code; break;
                case 1: $Current_Code = $this->To_Code; break;
            } //End of switch

            # Attempt to find the currency code in the local xml file
            $Code_Xpath = $Xpath->query('/conv/currency[@code="' . $Current_Code. '"]');
            
            # If the from code does not exist the attempt to find it
            if ($Code_Xpath->length < 1) { //True if the code was not found in the local file
				if (!$this->Add_New_Rate($Current_Code)) { //True if the code was not found or the update failed
					return false;
				} //End of if statement
				$Codes_Found++;
            } else { //True if the code was found in the local file
                $Codes_Found++;
            } //End of if else
        } //End of for loop

        if ($Codes_Found != 2) { //True if one of the codes was not found in the xml file
            $Error_Code = 2000;
            return false;
        } //End of if statement
        return true;
    } //End of Codes_Exist function
	
	
	# Gets the rates from the local xml file.
	# If the rates are out-of-date then attempt to update
	# Otherwise print out error message
    public function Set_Rates() {
	
        $Currency_Rates_Doc = new DomDocument();
        $Currency_Rates_Doc->loadXML(parent::__get('Currency_Rates_XML'));
        $Xpath = new DomXPath($Currency_Rates_Doc);

        for ($i=0; $i<2; $i++) { //Loop through for every code - From and To
            switch ($i) { //Gets the current/appropriate code
                case 0: $Code_Rate_Var = "From_Rate"; $Current_Code = $this->From_Code; break;
                case 1: $Code_Rate_Var = "To_Rate"; $Current_Code = $this->To_Code; break;
            } //End of switch

            # Attempt to find the currency code in the local xml file
            $Code_Xpath = $Xpath->query('/conv/currency[@code="' . $Current_Code. '"]');
            
			# If the rate was updated more than 24 hours ago attempt to update the rate
            if ($this->IsOutOfDate($Code_Xpath)) {
				$this->$Code_Rate_Var = $this->Update_Existing_Rate($Current_Code, $Currency_Rates_Doc, $Code_Xpath);
            } else { //True if the currency code in the local xml file is not out of date;
				$this->$Code_Rate_Var = $Code_Xpath->item(0)->getAttribute('rate');
            } //End of if else
			
        } //End of for loop
    } //End of Set_Rates function
	
	
    # If the code was last updated more than 24 hours ago return true
    private function IsOutOfDate($Code_Xpath) {
        $Last_Updated_Time_Stamp = strtotime($Code_Xpath->item(0)->getAttribute('last_updated'));
        if (($Last_Updated_Time_Stamp == null) || (strtotime('+1 day', $Last_Updated_Time_Stamp) < time())) { //True if the timestamp is more than 1 day old
			return true;
        } else {
            return false;
        } //End of if else
    } //End of IsOutOfDate function
	
	
    # Calculates and formats the conversion result based upon the amount and rates
    public function Convert() {
        return doubleval(str_replace(",", "", number_format(((float) $this->Amount / (float) $this->From_Rate) * (float) $this->To_Rate, 2)));
    } //End of Convert function
    
    
    # Gets the requested attribute for the requested attribute from the currency information file. If the attribute was not found return undefined
    public function getCodeInfo($Code_Type, $Attribute) {
        $Code = $this->$Code_Type;
        $Currency_Info_XML_Element = new SimpleXmlElement(parent::__get('Currency_Info_XML'));
        $Code_Xpath = $Currency_Info_XML_Element->xpath('/currencies/currency[@code="' . $Code . '"]');

        if (count($Code_Xpath) == 1) { //True if the code was found in the xml file
            $Output = $Code_Xpath[0][$Attribute]; //Attempts to get the requested attribute
            if (!empty($Output)) { //True if the data for the requested code does not exist
                return $Output;
            } //End of if statement
        } //End of if statement
        return 'Undefined';
    } //End of getCodeInfo function
    
} //End of class file
?>