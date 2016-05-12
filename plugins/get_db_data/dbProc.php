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

class DB_Worker extends Worker {
    
    private $output_queue = null;
    private $input_queue = null;
    private $dbcon = null;
    
    public function __construct() {
        //$this->dbcon = new PDO('mysql:host=localhost;dbname=radar2', 'radar2', 'm89pDQYqds2jpuP4');
    }
    
    public function getData() {
        $tmp = $this->output_queue;
        $this->output_queue = "";
        return $tmp;
    }
    
    
    public function addInputRequest($data) {
        if (!is_null($this->input_queue)) {
            $tmp = json_decode($this->input_queue,true);
        } else {
            $tmp = Array();
        } 
        $tmp[] = $data;
    //    echo "\t[plugin][db] Adding {$hex} to input queue...\n";
        $this->input_queue = json_encode($tmp);
        return;
    }

    private function addOutputData($hex,$data) {
        if ((!is_null($this->output_queue)) && ($this->output_queue!="")) {
            $tmp = json_decode($this->output_queue,true);
        }
        
        $tmp[$hex] = $data;
        echo "\t\tAdicionando dados do DB para hez: ".$hex."\n";
        $this->output_queue = json_encode($tmp);
        return;
        
    }

    private function getDbData($data) {
        
        $dbcon = new PDO('mysql:host=localhost;dbname=radar2', 'radar2', 'm89pDQYqds2jpuP4');
        $return = Array();
        
        if ($dbcon) {
            // Get aircraft informatio            
            $stmt = $dbcon->prepare("SELECT * FROM aircraft_modes WHERE ModeS = :hex");
            $stmt->execute(array(':hex' => (string)$data['hex']));
                        
            if ($stmt->rowCount()>0) {
                $db_data = $stmt->fetch(PDO::FETCH_ASSOC);
                $return['ICAOTypeCode'] = $db_data['ICAOTypeCode'];
                $return['Registration'] = $db_data['Registration'];                
            } else {
                $return['ICAOTypeCode'] = '';
                $return['Registration'] = '';                
            }
            
            // Now we get route info based on IDENT
            $sql = "SELECT  routes.Operator_ICAO as operator_icao, routes.FromAirport_ICAO as from_icao, routes.ToAirport_ICAO as to_icao, 
                    ap1.name as from_name, ap2.name as to_name, ap1.country as from_country, ap2.country as to_country,
                    ap1.city as from_city, ap2.city as to_city, ap1.altitude as from_altitude, ap2.altitude as to_altitude, al.name as operator_name
                    FROM routes
                    LEFT JOIN airport as ap1 ON (ap1.icao = routes.FromAirport_ICAO)
                    LEFT JOIN airport as ap2 ON (ap2.icao = routes.ToAirport_ICAO)
                    LEFT JOIN airlines as al ON (al.icao = routes.Operator_ICAO)
                    WHERE routes.CallSign = :ident
                    LIMIT 1";
            
            $stmt = $dbcon->prepare($sql);
            $stmt->execute(array(':ident' => (string)$data['ident']));            
            
            if ($stmt->rowCount()>0) {
                $db_data = $stmt->fetch(PDO::FETCH_ASSOC);
                $return['operator_icao'] = $db_data['operator_icao'];
                $return['from_icao'] = $db_data['from_icao'];
                $return['to_icao'] = $db_data['to_icao'];
                $return['from_name'] = $db_data['from_name'];
                $return['to_name'] = $db_data['to_name'];
                $return['from_country'] = $db_data['from_country'];
                $return['to_country'] = $db_data['to_country'];
                $return['from_city'] = $db_data['from_city'];
                $return['to_city'] = $db_data['to_city'];
                $return['from_altitude'] = $db_data['from_altitude'];
                $return['to_altitude'] = $db_data['to_altitude'];
                $return['operator_name'] = $db_data['operator_name'];                
            }
            
        }
        
        return $return;
        
    }
    
    public function run() {        
        
        while(1) {
            usleep(150000); // Prevent hight CPU Usage
            
            if ((!is_null($this->input_queue)) && ($this->input_queue!="")) {
                // Get local queue and clear queue to next data
                $tmp = json_decode($this->input_queue,true);
                $this->input_queue = "";
                
                for($x=0;$x<sizeof($tmp);$x++) {
                    // Get data from DB
                    $data = $this->getDbData($tmp[$x]);
                    $this->addOutputData($tmp[$x]['hex'], $data);
                }                
                
            }            
            
        }
        
    }
    
}