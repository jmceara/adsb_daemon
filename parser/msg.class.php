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

class ADSB_Parser_msg extends Worker {

    private $data = null;
    private $host = null;
    private $port = null;
    private static $socket = null;

    public function __construct($source) {
        $this->host = $source['host'];
        $this->port = $source['port'];        
    }

    private function connect() {
        $ip = gethostbyname($this->host);
        self::$socket = socket_create(AF_INET, SOCK_STREAM, 0);

        $r = @socket_connect(self::$socket, $ip, $this->port);
        if ($r || socket_last_error() == 114 || socket_last_error() == 115) {
            return self::$socket;
        }

        $errno = socket_last_error(self::$socket);
        $errstr = socket_strerror($errno);
        socket_close(self::$socket);
        return false;
    }

    private function addLocalData($data) {
                
        if (!empty($this->data)) {
            $tmp = json_decode($this->data,true);
        }        
        $tmp[] = $data;
        $this->data = json_encode($tmp);
        unset($tmp);
    }
    
    public function run() {

        if (!self::$socket) {
            $this->connect();
        }
        
        while (true) {
          //  usleep(100000);
            $siz = socket_recv(self::$socket, $buffer, 500, MSG_WAITALL);
            $linhas = explode("\r\n", $buffer);
            
            for ($x = 0; $x < sizeof($linhas); $x++) {
                $linha = $linhas[$x];
                // SBS format is CSV format
                if ($linha != '') {
                    $tt = 0;
                    $line = explode(',', $linha);
                    if ((count($line) > 20) && ($line[0] == "MSG")) {
                        /* Required fields */
                        $data['hex'] = $line[4];
                        // Force datetime to current UTC datetime
                        $data['datetime'] = date('Y-m-d H:i:s');
                        /* End of required fields */
                        
                        $data['ident'] = trim($line[10]);
                        $data['latitude'] = $line[14];
                        $data['longitude'] = $line[15];
                        $data['verticalrate'] = $line[16];
                        $data['emergency'] = $line[20];
                        $data['speed'] = $line[12];
                        $data['squawk'] = $line[17];
                        $data['altitude'] = $line[11];
                        $data['heading'] = $line[13];
                        $data['ground'] = $line[21];
                        $data['emergency'] = $line[19];
                        $data['format_source'] = 'sbs';
                        $this->addLocalData($data);
                    }
                }
            } 
            
        }
    }

    public function getData() {

            $tmp = $this->data;
            $this->data = "";
            return $tmp;
    }

}
