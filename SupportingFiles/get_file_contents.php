 <?php
 # If the function is called from the UWE server the proxy
 # configuration is used. Otherwise get file contents normally
 function get_file_contents($Uri) {
    if (stristr($_SERVER['HTTP_HOST'], 'cems')) {
		# Get a file via UWE proxy and stop caching
		$Context = stream_context_create(
		array('http'=>array('proxy'=>'proxysg.uwe.ac.uk:8080', 'header'=>'Cache-Control: no-cache')));  
		$Contents = @file_get_contents($Uri, false, $Context);
    } else {
		$Contents = @file_get_contents($Uri);
    } //End of if else
    return $Contents;
} //End of get_file_contents function
?>
