<?php

echo "Loading file... ";

// loading a local file
$file = 'data/api.powerhousemuseum.com.xml';

if (file_exists($file)) {
    $data = simplexml_load_file($file);
 
    //print_r($data);
} else {
    exit('Failed to open $file.');
}

// set up database connections
// global $wpdb; // run prepare just in case there's dodgy data in the XML ###
$db_account = 'root';
$db_password = 'root';
$db_host = 'localhost';
$db_name = 'wordpress_mu';
$db_objecttable = 'wp_mmg_objects_powerhouse';
$db_objecttablefields = 'name, accession_number, institution, data_source_url,
      interpretative_date, interpretative_place, image_url'; 
$sql = '';

// open the XML file and start to process it
// getting to specific bits

if( sizeof($data) > 0 ){
  
  // create db connection cos there's something to store
  $dbc = @mysql_connect ($db_host, $db_account, $db_password) 
    or die ('Could not connect to MySQL:'.mysql_error());
  @mysql_select_db($db_name)
     or die ('Could not select the database:'.mysql_error());
  
  echo "Building SQL string... ";

$testing = $data->items->item; // see if this barfs ###

    echo "<pre>".print_r($testing)."</pre>";

	foreach ($data->items->item as $museumobject) {
	  $object_name = filter_var($museumobject->title,FILTER_SANITIZE_ENCODED);
    $accession_number = filter_var($museumobject->registration_number,FILTER_SANITIZE_ENCODED);
    $institution = 'Powerhouse Museum';
    $data_source_url = 'http://'.filter_var($museumobject->PermanentURI,FILTER_SANITIZE_ENCODED);
    $interpretative_date = filter_var($museumobject->InterpretativeDate,FILTER_SANITIZE_ENCODED);
    $interpretative_place = filter_var($museumobject->InterpretativePlace,FILTER_SANITIZE_ENCODED);
    $image_url = filter_var($museumobject->thumbnail->url,FILTER_SANITIZE_ENCODED);
    
    //echo $object_name;
    
    $sql = "insert into $db_objecttable  ($db_objecttablefields) " .
    "values ('$object_name','$accession_number','$institution', '$data_source_url',
    '$interpretative_date', '$interpretative_place', '$image_url') ";
    
    mysql_query($sql) or die ('Something bad happened:'.mysql_error());
        
    echo $sql; 

	}

mysql_close($dbc); // tidy up more? ###

}

?>