<?php
/* 
 * Copyright 2016 Jonis Maurin Ceará - http://www.jonis.com.br
 * jmceara@gmail.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once 'funcs.php';
require_once 'parser/msg.class.php';


// List of sources
$sources[] = Array("host" => "192.168.22.107", "port" => 30003, "type" => "msg");


// Load parsers
$parsers = null;
if (is_array($sources)) {
    foreach ($sources as $item) {
        if (key_exists("type", $item)) {
            $tmp = loadParser($item);
            if ($tmp) {
                $parsers[] = $tmp;
            }
        }
    }
}

// Load plugins
$plugins = loadPlugins();

// Debug
echo "Parsers: ".sizeof($parsers)."\n";
echo "Plugins: ".sizeof($plugins)."\n";        
        

// Start all parsers
for ($x=0;$x<sizeof($parsers);$x++) {
    $parsers[$x]->start();
}

$i - 0;
// infinite loop
$data = Array();
while(1) {
       $i++; 
    // Collects data from each parser
    usleep(150000); // Must wait sometime
    
    // CPU Usage statistics
    $rustart = getrusage();

    // Get data from all sources/parsers
    for ($x=0;$x<sizeof($parsers);$x++) {
                
        $tmp_data = @json_decode($parsers[$x]->getData(),true);
        if ((is_array($tmp_data)) && (!is_null($data))) {
            // If data is returned, merge into temp data array
            mergeData($tmp_data, $data);
        }
    }    
    
    // Send to all plugins
    for ($x=0;$x<sizeof($plugins);$x++) {
        $plugins[$x]->process($data);
    }
    
    // CPU usage statistics
    $ru = getrusage();
    
    
   // Debug information only
    if ($i==20) {
        $i = 0;
        echo "Flight table size: ".sizeof($data)."\n";
        echo "This process used " . rutime($ru, $rustart, "utime") . " ms for its computations\n";
        echo "It spent " . rutime($ru, $rustart, "stime") . " ms in system calls\n";    
        echo "Used memory: ".convert(memory_get_usage()) . "\n\n"; 
        
        // Check data of every flight
        foreach ($data as $item) {
              if (@key_exists("db", $item['extras'])) {
                  echo "Flight {$item['hex']} ICAOTypeCode: {$item['extras']['db']['ICAOTypeCode']}, Registration: {$item['extras']['db']['Registration']}, ident: {$item['ident']}".
                          " From: {$item['extras']['db']['from_icao']} ({$item['extras']['db']['from_name']}) To: {$item['extras']['db']['to_icao']} ({$item['extras']['db']['to_name']})\n";
              } else {
                  echo "Flight ".$item['hex']." has no DB information yet :( ident: ".$item['ident']."\n";
              }
        }
        echo "\n";
    }

    
    
}


exit(0);