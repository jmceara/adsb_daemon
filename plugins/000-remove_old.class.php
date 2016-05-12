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

class ADSB_Plugin_remove_old {
    
    private $enabled = true;
    private $name = "FAM Live Spotter";
    private $al = null;
    private $type = null;
    private $version = 1.0;
    private $debug = true;
    
    public function __construct() {
      //  echo "Oi do plugin Jonis!\n";
        
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
     
        $timeout = 150;
        
        $date_now = new DateTime();
        // Remove data older than X seconds
        $tmp = Array();
        
        for ($x=0;$x<sizeof($data);$x++) {
            $date_tmp = new DateTime($data[$x]['datetime']);
            if (($date_now->getTimestamp() - $date_tmp->getTimestamp()) > $timeout) {
                echo "\t***** Voo ".$data[$x]['hex']. " expirou...excluindo da lista. *****\n";
                echo "\tUltimo dado recebido em: ".$data[$x]['datetime']."\n\n";
            } else {
                $tmp[] = $data[$x];
            }
        }
                
        $data = $tmp;
        unset($date_now);
        unset($date_tmp);
        //unset($tmp);
        return;
    }
    
}