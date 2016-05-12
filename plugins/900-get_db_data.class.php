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

require_once 'get_db_data/dbProc.php';

class ADSB_Plugin_get_db_data {
    
    private $enabled = true;
    private $name = "FAM DB Data";
    private $al = null;
    private $type = null;
    private $version = 1.0;
    private $debug = true;
    private $db = null;
    
    public function __construct() {
        $this->db = new DB_Worker();
        if (!is_null($this->db)) {
            $this->db->start();
        }
        
    }
    
    public function getVersion() {
        return $this->version;
    }
    
    public function enabled() {
        return $this->enabled;
    }

    public function getName() {
        return $this->name;
    }        
    
    public function process(&$data) {

        $db_queue = $this->db->getData();
        if ((!is_null($db_queue)) && ($db_queue!="")) {         
            $db_queue_array = json_decode($db_queue,true);            
        }
        
        for ($x=0;$x<sizeof($data);$x++) {
            
            /*
             * If this plane was never marked for DB data retrieve,
             * do it and send to DB worker to get data.
             */
            if (!@key_exists('db_requested', $data[$x]['extras'])) {
                // Request data from DB thread
                    
                    // Request DB data only for flights with IDENT filled
                    if (trim($data[$x]['ident'])!="") {
                        $this->db->addInputRequest($data[$x]);
                        // Mark this is already requested to DB Plugin
                        $data[$x]['extras']['db_requested'] = "1";
                    }
                    
            }
            
            /*
             * Now proccess all data returned from DB (old requests)
             */
            $hex = $data[$x]['hex'];            
            if (@key_exists($hex, $db_queue_array)) {
                $data[$x]['extras']['db'] = $db_queue_array[$hex];
            }
            
        }
        
        return;
        
    }
    
}