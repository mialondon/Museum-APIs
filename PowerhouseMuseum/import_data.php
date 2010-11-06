<?php

require_once "../../import_config.ini.php";

/*
 * Notes on import adaptations:
 * 
 * Powerhouse data doesn't have an 'interpretative' date, but instead uses date_earliest and date_latest
 * The differences in approach between earliest-latest date ranges and single
 * Ditto qualified vs simple (interprerative) dates and places - interpretative is a method we
 * use at the Science Museum to present a public-facing 'summarised' data and/or place while
 * keeping the more complicated, qualified (i.e. relating to specific relationships with 
 * e.g. types of people, places, etc) data intact for specialist use
 * 
 * You'll need to set your own values for $db_account, $db_password, $db_host and $db_name
 * 
 * Import written for: http://museumgam.es, November 2010
 *  
 */

echo "Loading list file... ";

// loading a local XML file
$file = 'data/api.powerhousemuseum.com_astronomy.xml';

if (file_exists($file)) {
    $data = simplexml_load_file($file);
 
    //print_r($data);
} else {
    exit('Failed to open $file.');
}

// update to do:
// call each item in turn to get data that's not available in summary/list view - dates and places
// but need to talk to PHM guys about how they're done as there are lots of provenance items with dates and places
// but nothing to say which item will have those fields, except maybe order?

/*
 $item_id; 

if( sizeof($data) > 0 ){
  foreach ($data->items->item as $museumobject) {
    $item_id = filter_var($museumobject->registration_number,FILTER_SANITIZE_ENCODED);
    echo $item_id."<br />";
    // store the bits of data I want from the list view (nearly all of it's there except date, place)
    }
} */

// set up database stuff specific to the db table for this API (if it's not using the general object table) object_table
// $db_objecttable = 'wp_mmg_objects_powerhouse';
$db_objecttable = 'wp_mmg_objects';
$db_objecttablefields = 'name, accession_number, institution, data_source_url, description,
      date_earliest, date_latest, interpretative_place, image_url'; 
$sql = '';

// process the file

if( sizeof($data) > 0 ){
  
  // create db connection cos there's something to store
  $dbc = @mysql_connect ($db_host, $db_account, $db_password) 
    or die ('Could not connect to MySQL:'.mysql_error());
  @mysql_select_db($db_name)
     or die ('Could not select the database:'.mysql_error());
  
  //echo "Building SQL string... ";

	foreach ($data->items->item as $museumobject) {
	  $object_name = filter_var($museumobject->title,FILTER_SANITIZE_ENCODED);
    $accession_number = filter_var($museumobject->registration_number,FILTER_SANITIZE_ENCODED);
    $institution = 'Powerhouse Museum';
    $data_source_url = 'http://api.powerhousemuseum.com'.filter_var($museumobject->item_uri,FILTER_SANITIZE_ENCODED);
    $source_display_url = filter_var($museumobject->permanent_url,FILTER_SANITIZE_ENCODED);
    // don't expect date or place to work immediately cos there are lots of repeated items
    $date_earliest = filter_var($museumobject->provenance->item->date_earliest,FILTER_SANITIZE_ENCODED);
    $date_latest = filter_var($museumobject->provenance->item->date_latest,FILTER_SANITIZE_ENCODED); 
    $interpretative_place = filter_var($museumobject->provenance->item->place,FILTER_SANITIZE_ENCODED);
    $description = filter_var($museumobject->description,FILTER_SANITIZE_ENCODED);
    $image_url = filter_var($museumobject->thumbnail->url,FILTER_SANITIZE_ENCODED);
       
    $sql = "insert into $db_objecttable  ($db_objecttablefields) " .
    "values ('$object_name','$accession_number','$institution', '$data_source_url', '$description',
    '$date_earliest', '$date_latest', '$interpretative_place', '$image_url') ";
    
    mysql_query($sql) or die ('Something bad happened:'.mysql_error());
        
    //echo $sql; 

	}

mysql_close($dbc); // tidy up more? ###

}

?>