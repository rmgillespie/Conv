<?php
# Gets required classes, loads configuration settings from config file and sets up error handling
require_once('SupportingFiles/Configuration.php');


#   If required parameters for the converter service
#   do not exist or an unknown parameter exists return
#   the appropriate error code.
function Check_Parameters(&$Error_Code, $Specified_Parameter_Names) {
    $Empty_Parameter_Exists = false;
    $Url_Array = explode("?", $_SERVER['REQUEST_URI']); //Splits the request URI
    $Parameters_Array = explode("&", $Url_Array[1]); //Gets the parameters part from the exploded request URI

	# Removes parameters specified in the URL from the Specified_Parameter_Names array. Leaves only unspecified parameters in the Parameters_Array
    foreach ($Specified_Parameter_Names as $id => $Specified_Parameter_Name) { //For every parameter name specified
        foreach ($Parameters_Array as $key => $Parameter_Value) { //For every paramater in the URI
            
            if (isset($_GET[$Specified_Parameter_Name]) && $Specified_Parameter_Name . "=" . 
	    $_GET[$Specified_Parameter_Name] == $Parameter_Value) { //True if the specified parameter exists in URI
                
				# Remove parameter from arrays
                unset($Parameters_Array[$key]);
                unset($Specified_Parameter_Names[$key]);
				
                $Temp_Arr = explode("=", $Parameter_Value);
                if (empty($Temp_Arr[1])) { //True if the value is empty
                    $Empty_Parameter_Exists = true;
                } //End of if statement       
            } //End of if statement
        } //End of foreach
    } //End of foreach

    if (count($Specified_Parameter_Names) > 0 || $Empty_Parameter_Exists) { //True if one of the required parameters doesn't exist or is empty
        $Error_Code = 1000;
        return false;
    } else {
        if (count($Parameters_Array) > 0) { //True if an unkown parameter exists in the URI
            $Error_Code = 1100;
            return false;
        } //End of if statement
    } //End of if else statement
    return true;
} //End of Check_Parameters function


# Sets the default parameters to null
$from = null; $to = null; $amnt = null;

# Extracts the parameters from the URL
extract($_GET);

if (!Check_Parameters($Error_Code, array("from", "to", "amnt"))) { //True if one of the required parameters is missing or an unkown parameter exists
    Error($Error_Code); //Print out error message and exit
} else {

    # Creates an instance of the Currency_Converter class
    $Currency_Converter = new Currency_Converter($amnt, strtoupper($from), strtoupper($to));

    if (!$Currency_Converter->Is_Service_Available()) { //True if the resources required for the service are unavailable
        Error(3000);
    } else {

        if (!$Currency_Converter->Validate_Parameters($Error_Code)) { //True if one of the parameters is invalid
            Error($Error_Code);
        } else {

            # Get the rates from the local xml file. If a rate is out of date update them
            $Currency_Converter->Set_Rates();

            # Calculates the converted amount based upon the exchange rate data and assigns the value to a local variable
            $Result = $Currency_Converter->Convert();

            # Calculates and formats the rate for the requested conversion
            $Rate = number_format($Result / $amnt, 5);

            # Prints out the xml conversion result
            Write_Xml($Rate, $Result, $Currency_Converter);
            
        } //End of if else
    } //End of if else
} //End of if else
?>