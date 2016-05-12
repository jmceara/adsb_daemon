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

function mergeData($new_data, &$old_data) {

    $temp_array = array();
    foreach ($old_data as &$v) {
        $temp_array[$v['hex']] =& $v;
    }
    
    foreach ($new_data as &$v) {
        
        $ident1 = $v['ident'];
        $ident2 = $temp_array[$v['hex']]['ident'];
        
        if (!isset($temp_array[$v['hex']])) {
            $temp_array[$v['hex']] =& $v;           
        } else {
                                    
            $data1 = new DateTime($v['datetime']);
            $data2 = new DateTime($temp_array[$v['hex']]['datetime']);
            $dif = $data1->getTimestamp() - $data2->getTimestamp();
            // Compare timestamp
            if ($dif > 0) {
                
                
                if (key_exists("extras", $temp_array[$v['hex']])) {
                    $tmp_extras = $temp_array[$v['hex']]['extras'];
                } else {
                    $tmp_extras = null;
                }
                $temp_array[$v['hex']] = & $v;
                if (!is_null($tmp_extras)) {
                    $temp_array[$v['hex']]['extras'] = $tmp_extras;
                }
               
                // Restore IDENT field
                if ($ident2!="") {
                    $temp_array[$v['hex']]['ident'] = $ident2;
                }
                if ($ident1!="") {
                    $temp_array[$v['hex']]['ident'] = $ident1;
                }
                
            }
                        
            
        }
    }
    
    // Rebuild array
    $old_data = array_values($temp_array);

}

function rutime($ru, $rus, $index) {
    return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
     -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
}

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}


function loadParser($source) {
    // Load parser
    // Check if parser for this type exists
    if (file_exists('parser/' . strtolower($source['type']) . '.class.php')) {
        require_once 'parser/' . strtolower($source['type']) . '.class.php';

        $class = 'ADSB_Parser_' . strtolower($source['type']);
        // Check is class for this parser type exist and create a new instance
        if (class_exists($class)) {
            return (new $class($source));
        } else {
            echo "Class file for PARSER type " . $source['type'] . " does not exist.\n";
            return false;
        }
    } else {
        echo "Parser file for " . $source['type'] . " does not exist.\n";
        return false;
    }
}

function loadPlugins() {

    $plugins = null;

    $files = scandir('plugins/');
    foreach ($files as $key => $file) {

        // Check if filename is valid
        if (strtolower(substr($file, -9)) == 'class.php') {
            require_once 'plugins/' . $file;

            // Check if correct class exists and add plugin to list
            $class = 'ADSB_Plugin_' . strtolower(substr($file, 4, -10));
            if (class_exists($class)) {

                $tmp = new $class();

                // check if process method exist
                if (method_exists($tmp, 'process')) {
                    // check if 'enabled' method exist
                    if (method_exists($tmp, 'enabled')) {
                        if ($tmp->enabled() === true) {
                            $plugins[] = $tmp;
                        } else {
                            unset($tmp);
                        }
                    } else {
                        unset($tmp);
                    }
                } else {
                    unset($tmp);
                }
            }
        }
    }
    return $plugins;
}
