<?php
# Set up autoload function to load class files automatically
function __autoload($Class_Name) {
    #Sets the base directory for the class files
    $Class_Dir = 'Classes/'; 
    
    # Gets the URI to the class file based upon the base directory and the class file name
    $Class_Uri = $Class_Dir . $Class_Name . '.class.php';
    
    if (file_exists($Class_Uri) && is_readable($Class_Uri)) { //True if the class file exists and is readable
        require_once($Class_Uri);
    } else {
        Error(3100); //Print out error message and exit
    } //End of if else
} //End of __autoload function


# If autoload function does not exist (As autoload is only available in PHP 5+) get classes manually
if (!function_exists('__autoload')) {
    include "Classes/Error_Logger.class.php";
    include "Classes/Converter_Service.class.php";
    include "Classes/Currency_Converter.class.php";
	include "Classes/Service.class.php";
	include "Classes/Yahoo.class.php";
	include "Classes/Google.class.php";
} //End of if statement

require_once('SupportingFiles/Xml_Functions.php');  //Contains functions to output xml errors and xml result



/********************************************************************************************/
/*****			                 START OF CONFIGURATION					                *****/

$Config_File = 'SupportingFiles/Config.xml'; //The URI to the xml configuration file

if (file_exists($Config_File) && is_readable($Config_File)) { //True if the configuration file exists and PHP has read permissons
    if ($Xml = simplexml_load_file($Config_File)) { //Attempts to load the Config.xml file. Returns true if successfull
	
        #Gets the baserate from the config file
        $Baserate = $Xml->baserate;

        # Gets the URU of the local xml file containing the exchange rates for the baserate
        $Xml_Element = $Xml->baserate_xml_file;
        $Baserate_Xml_File = $Xml_Element->directory . $Xml_Element->name  . "_" . $Baserate . "." . $Xml_Element->extension;
		
        # Gets the URI of the local xml file containing the currency location information
        $Xml_Element = $Xml->currency_info_file;
        $Currency_Info = $Xml_Element->directory . $Xml_Element->name . "." . $Xml_Element->extension;

        # Gets the error logging file paths
        $Xml_Element = $Xml->errorlogging->logpaths;
        $Fatal_Log_Path = $Xml_Element->fatal_log_path;
        $Warning_Log_Path = $Xml_Element->warning_log_path;
        $Notice_Log_Path = $Xml_Element->notice_log_path;
        $Other_Log_Path = $Xml_Element->other_log_path;

        # Loads the error message data into a global variable which is accessed in the Error function (In Xml_Functions.php)
        $GLOBALS['Error_Message_Data'] = $Xml->errorlogging->errormessages;
    } else { //True if an error occured when attempting to load/access the config file
        
        $Dom = new DOMDocument;
        $Dom->preserveWhiteSpace = false;
		$Dom->formatOutput = true;
        $Dom->loadXML("<?xml version=\"1.0\" encoding=\"UTF-8\"?>
         <conv>
           <error code='3100'>Error in service</error>
         </conv>");
        echo $Dom->saveXml();
        exit;
    } //End of if else
    
} else { //True if either the config file does not exist or PHP does not have read permissons
    
    $Dom = new DOMDocument;
    $Dom->preserveWhiteSpace = false;
	$Dom->formatOutput = true;
    $Dom->loadXML("<?xml version=\"1.0\" encoding=\"UTF-8\"?>
     <conv>
       <error code='3100'>Error in service</error>
     </conv>");
    echo $Dom->saveXml();
    exit;
} //End of if else

# Sets runtime constants based upon the data from the config file
define("BASERATE", $Baserate, true); //Sets the baserate
define("LOCAL_EXCHANGE_RATES_XML_URI", $Baserate_Xml_File, true); //Sets the URI to the local xml file containing the baserate exchange rates
define("CURRENCY_INFORMATION_URI", $Currency_Info, true); //Sets the URI to the local file containing the currency and location information for the different exchange rates

/*****			                    END OF CONFIGURATION				                *****/
/********************************************************************************************/



/********************************************************************************************/
/*****			                START OF ERROR HANDLING CODE			             	*****/

# Disables display_errors within the PHP.ini file. Thus hidding any errors from the user
if (ini_get('display_errors')) { //True if display errors is enabled
    ini_set('display_errors', 0); //Sets display errors to false
} //End of if statement
	
# Creates a new instance of the Error_Logger class
$Error_Logger = new Error_Logger($Fatal_Log_Path, $Warning_Log_Path, $Notice_Log_Path, $Other_Log_Path);

# Sets the custom error handler. If this fails print out error message
if (set_error_handler(array($Error_Logger,'Error_Handler'))) {
	Error(3100);
} //End of if statement

/*****			                  END OF ERROR HANDLING CODE			             	*****/
/********************************************************************************************/
?>