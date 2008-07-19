<?
/*
    Copyright (c) 2007-2008 AG Projects
    http://ag-projects.com
    Author Adrian Georgescu

    This page provides functions for building graphical
    usage statistics for OpenSER and MediaProxy

*/

class SIPstatistics {
    var $SipEnabledZones = array();
	var $online          = array();
    var $StatisticsPresentities = array();

    function SIPstatistics () {
        global $CDRTool;

		$this->path=$CDRTool['Path'];

    	$this->mrtgcfg_file    = $this->path."/status/usage/sip_statistics.mrtg";
        $this->harvest_file    = "/tmp/CDRTool-sip-statistics.txt";
        $this->mrtgcfg_dir     = $this->path."/status/usage";
        $this->index_file      = $this->path."/status/usage/index.phtml";

        $this->harvest_script  = $this->path."/scripts/harvestStatistics.php";
        $this->generateMrtgDataScript     = $this->path."/scripts/generateMrtgData.php";
        $this->generateMrtgConfigScript   = $this->path."/scripts/generateMrtgConfig.php";

        $this->db        = new DB_cdrtool();
        $this->ser_db    = new DB_openser();

        if (class_exists('DB_siponline')) {
        	$this->online_db = new DB_siponline();
        } else {
        	$this->online_db = new DB_openser();
        }

        if (is_array($CDRTool['StatisticsPresentities'])) {
        	$this->StatisticsPresentities = $CDRTool['StatisticsPresentities'];
        }
	}

    function getSipEnabledZones () {
        global $CDRTool;

        $query="select domain from domain";
        dprint($query);

        if (!$this->ser_db->query($query)) return 0;
        if (!$this->ser_db->num_rows()) return 0;

		while ($this->ser_db->next_record()) {
            $zName=$this->ser_db->f('domain');

            if (is_array($CDRTool['statistics']['zoneFilter']) && !in_array($zName,$CDRTool['statistics']['zoneFilter'])) continue;

            if (!$seen[$zName]) {
            	$this->SipEnabledZones[$zName] = $zName;
                $this->statistics[$zName] =
                               array( 'online_users' => '0',
				                      'sessions'     => '0',
				                      'traffic'      => '0',
				                      'caller'       => '0',
				                      'called'       => '0'
                                      );
            	$seen[$zName]++;
        	}
        }

        if (is_array($CDRTool['statistics']['extraZones'])) {
            foreach ($CDRTool['statistics']['extraZones'] as $zName) {
    
                if (!$seen[$zName]) {
                    $this->SipEnabledZones[$zName] = $zName;
                    $this->statistics[$zName] =
                                   array( 'online_users' => '0',
                                          'sessions'     => '0',
                                          'traffic'      => '0',
                                          'caller'       => '0',
                                          'called'       => '0'
                                          );
                    $seen[$zName]++;
                }
            }
        }

        //print_r($this->SipEnabledZones);
        //dprint_r($this->statistics);

    }

    function generateMrtgConfigFile () {
        $this->getSipEnabledZones();
        
        if (!$handle = fopen($this->mrtgcfg_file, 'w+')) {
        	echo "Error opening {$this->mrtgcfg_file}.\n";
            return 0;
        }
        
        // printing cfg header

        fwrite($handle,"
### Global Config Options
WorkDir: {$this->mrtgcfg_dir}
IconDir: {$this->mrtgcfg_dir}/images
Refresh: 300
#WriteExpires: Yes
        ");

        $_zones=$this->SipEnabledZones;
        $_zones['total']='total';

        while(list($key,$value) = each($_zones)) {
        	fwrite($handle,"\n\n
## {$key}

Target[{$key}_users]: `{$this->generateMrtgDataScript} {$key} users`
Options[{$key}_users]: growright, gauge, nobanner
BodyTag[{$key}_users]: <BODY LEFTMARGIN=\"1\" TOPMARGIN=\"1\">
#PNGTitle[{$key}_users]: <center>Online Users for {$key}</center>
MaxBytes[{$key}_users]: 5000000
Title[{$key}_users]: Online Users for {$key}
ShortLegend[{$key}_users]: U
XSize[{$key}_users]: 300
YSize[{$key}_users]: 75
Ylegend[{$key}_users]: Users
Legend1[{$key}_users]: Online Users
LegendI[{$key}_users]:   Online Users
LegendO[{$key}_users]: 
PageTop[{$key}_users]: <H1> Online Users for {$key} </H1>

Target[{$key}_sessions]: `{$this->generateMrtgDataScript} {$key} sessions`
Options[{$key}_sessions]: growright, nobanner, gauge
BodyTag[{$key}_sessions]: <BODY LEFTMARGIN=\"1\" TOPMARGIN=\"1\">
MaxBytes[{$key}_sessions]: 50000
Title[{$key}_sessions]: Sessions Statistics for {$key}
ShortLegend[{$key}_sessions]: Ses
XSize[{$key}_sessions]: 300
YSize[{$key}_sessions]: 75
Ylegend[{$key}_sessions]: Sessions
Legend1[{$key}_sessions]: Active Sessions
LegendI[{$key}_sessions]:   Active Sessions
LegendO[{$key}_sessions]:   
PageTop[{$key}_sessions]: <H1> Active Sessions for {$key} </H1>

Target[{$key}_traffic]: `{$this->generateMrtgDataScript} {$key} traffic`
Options[{$key}_traffic]: gauge, growright, bits, nobanner
BodyTag[{$key}_traffic]: <BODY LEFTMARGIN=\"1\" TOPMARGIN=\"1\">
#PNGTitle[{$key}_traffic]: {$key} traffic
MaxBytes[{$key}_traffic]: 1250000000
Title[{$key}_traffic]: IP traffic for {$key}
XSize[{$key}_traffic]: 300
YSize[{$key}_traffic]: 75
Legend1[{$key}_traffic]: Caller Traffic in Bits per Second
Legend2[{$key}_traffic]: Called Traffic in Bits per Second
LegendI[{$key}_traffic]:   caller
LegendO[{$key}_traffic]:   called
PageTop[{$key}_traffic]: <H1> IP Traffic for {$key} </H1>

        ");
        
        }
        
        fclose($handle);
	}

    function generateMrtgData($domain,$dataType) {
    	$value1=0;
        $value2=0;

    	$lines=explode("\n",file_get_contents($this->harvest_file));
        foreach ($lines as $line) {
            if (preg_match("/^$domain\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/",$line,$m)) {
                if ($dataType == 'sessions') {
                	$value1 = $m[2];
                    $value2 = $m[2];
                } else if ($dataType == 'traffic') {
                	$value1 = $m[3];
                    $value2 = $m[4];
                } else if ($dataType == 'users') {
                	$value1 = $m[1];
                    $value2 = $m[1];
                }
            }
        }

        printf ("%d\n%d\n0\n0\n\n",$value1,$value2);
    }

    function getSIPOnlineUsers() {

        $query="select count(*) as c, domain from location group by domain";
        dprint($query);

        if (!$this->online_db->query($query)) return 0;
        if (!$this->online_db->num_rows()) return 0;

        while ($this->online_db->next_record()) {
            $this->online[$this->online_db->f('domain')]=$this->online_db->f('c');
        }

        dprint_r($this->online);
	}


    function harvestStatistics() {
        if (!$handle = fopen($this->harvest_file, 'w+')) {
        	echo "Error opening $this->harvest_file\n";
        	return 0;
        }

        global $DATASOURCES;

        $_mediaServers     = array();
        $_mediaDispatchers = array();

        foreach (array_keys($DATASOURCES) as $ds) {
        	if (is_array($DATASOURCES[$ds]['mediaServers'])) {
                $_mediaServers=array_merge($_mediaServers,$DATASOURCES[$ds]['mediaServers']);
            }
        	if (strlen($DATASOURCES[$ds]['mediaDispatcher'])) {
                $_mediaDispatchers[]=$DATASOURCES[$ds]['mediaDispatcher'];
            }
        }

        $this->mediaServers     = array_unique($_mediaServers);
        $this->mediaDispatchers = array_unique($_mediaDispatchers);

        fwrite($handle,"domains\t\t\tonline_users\tsessions\tcaller\tcalled\n\n");

		$this->getSipEnabledZones();
        $this->getSIPOnlineUsers();

        foreach($this->mediaServers as $server) {
            $this->statistics = $this->getrtpsessions($server, "25060", $this->statistics);
        }

        $domains=array_keys($this->SipEnabledZones);

        ksort($domains);

        foreach($domains as $_domain) {

        	foreach (array_keys($this->online) as $_domain_online) {
                if ($_domain == $_domain_online) {
                    // update online users in big stats array
                    $this->statistics[$_domain]['online_users']=$this->online[$_domain];
                }
            }
        }

        // we must multiply the IP traffic by 2 when traffic enters and exists the network where the relay is located
        // this depends however on topology, if IP traffic is relayed to a node on the same physical network as the media relay
        // the relayed traffic will not corespond with the IP traffic monitored at the edge of the network

        $totals=array('online_users' =>0,
                      'sessions'     =>0,
                      'caller'       =>0,
                      'called'       =>0
                      );


		dprint_r($this->statistics);

        while(list($key, $usage) = each($this->statistics)) {
        	if ($usage['online_users']) {
                $online_users=$usage['online_users'];
                $totals['online_users']+=$usage['online_users'];
            } else {
                $online_users=0;
            }
        	if ($usage['sessions']) {
                $sessions=$usage['sessions'];
                $totals['sessions']+=$usage['sessions'];
            } else {
                $sessions=0;
            }
        	if ($usage['caller']) {
                $caller=$usage['caller']*2;
                $totals['caller']+=$usage['caller']*2;
            } else {
                $caller=0;
            }
        	if ($usage['called']) {
                $called=$usage['called']*2;
                $totals['called']+=$usage['called']*2;
            } else {
                $called=0;
            }

        	fwrite($handle,"{$key}\t\t{$online_users}\t\t{$sessions}\t\t{$caller}\t{$called}\n");
            if (in_array($key,array_keys($this->StatisticsPresentities))) {

                if (!$online_users) {
                	$activity='busy';
                } else {
                	$activity='open';
                }

                $note=sprintf("%s: Sessions %d, Online %d",$key, $sessions,$online_users);

                $this->publishPresence ($this->StatisticsPresentities[$key]['soapEngine'],
                                        $this->StatisticsPresentities[$key]['SIPaccount'],
                                        $note,
                                        $activity);

            }

        }

		$total_text=sprintf("total\t\t%d\t\t%d\t\t%s\t%s\n",
        $totals['online_users'],
        $totals['sessions'],
        $totals['caller'],
        $totals['called']
        );

        fwrite($handle,$total_text);
        
        fclose($handle);
    }


    function getrtpsessions($ip, $port, $_domains) {
        if ($fp = fsockopen ($ip, $port, $errno, $errstr, "5") ) {
            fputs($fp, "status\n");
            $proxy      = array('status' => 'Ok');
            $crtSession = 'None';
            while (!feof($fp)) {
                $j++;
                $line = fgets($fp, 2048);
                $elements = explode(" ", $line);

                if ($elements[0] == 'proxy' && count($elements)==3) {
                    $proxy['sessionCount'] = $elements[1];
                    $traffic = explode("/", $elements[2]);
                    $proxy['traffic'] = array('caller'  => $traffic[0],
                                              'called'  => $traffic[1],
                                              'relayed' => $traffic[2]);
                    $proxy['sessions'] = array();
                } else if ($elements[0]=='session' && count($elements)==7) {
                        $crtSession = $elements[1];
                        $info = array('from' => $elements[2],
                                      'to'   => $elements[3],
                                      'fromAgent' => "'".$elements[4]."'",
                                      'toAgent'   => "'".$elements[5]."'",
                                      'duration'  => $elements[6],
                                      'streams'   => array());
                        $proxy['sessions'][$crtSession] = $info;

                        list($caller, $caller_domain) = explode("@", $proxy['sessions'][$crtSession]['from']);
                        $caller_domain_els=explode(":",$caller_domain);
                        $caller_domain=$caller_domain_els[0];

                        if (!strlen($caller_domain)) $caller_domain='unknown';

                        $_domains[$caller_domain]['sessions'] += 1;

                } else if ($elements[0] == 'stream' && count($elements)==9 && $proxy['sessions'][$crtSession]['duration'] > 0) {
                       $stream = array('caller'   => $elements[1],
                                       'called'   => $elements[2],
                                       'via'      => $elements[3],
                                       'bytes'    => explode("/", $elements[4]),
                                       'status'   => $elements[5],
                                       'codec'    => $elements[6],
                                       'type'     => $elements[7],
                                       'idletime' => $elements[8]);
                       $proxy['sessions'][$crtSession]['streams'][] = $stream;

                       $_domains[$caller_domain]['caller'] += floor($proxy['sessions'][$crtSession]['streams'][0]['bytes'][0]/$proxy['sessions'][$crtSession]['duration']);
                       $_domains[$caller_domain]['called'] += floor($proxy['sessions'][$crtSession]['streams'][0]['bytes'][1]/$proxy['sessions'][$crtSession]['duration']);
                       $_domains[$caller_domain]['traffic'] += $proxy['sessions'][$crtSession]['streams'][0]['bytes'][0];
                       $_domains[$caller_domain]['traffic'] += $proxy['sessions'][$crtSession]['streams'][0]['bytes'][1];
                }
            }
            return $_domains;
         }
    }

    function normalizeBytes($bytes) {
        $mb = $bytes/1024/1024.0;
        $kb = $bytes/1024.0;
        if ($mb >= 0.95) {
            return sprintf("%.2fM", $mb);
        } else if ($kb >= 1) {
            return sprintf("%.2fk", $kb);
        } else {
            return sprintf("%d", $bytes);
        }
    }
    
    function normalizeTraffic($traffic) {
        // input is in bytes/second
        $traffic = $traffic * 8;
        $mb = $traffic/1024/1024.0;
        $kb = $traffic/1024.0;
        if ($mb >= 0.95) {
            return sprintf("%.2fMbps", $mb);
        } else if ($kb >= 1) {
            return sprintf("%.2fkbps",$kb);
        } else {
            return sprintf("%dbps",$traffic);
        }
    }

    function buildStatistics() {

    	system($this->generateMrtgConfigScript);
        system($this->harvest_script);
        system("env LANG=C mrtg $this->mrtgcfg_file");

    }

    function getOnlineTrend() {
    	$ips_old=array();
        $ips_new=array();

		$query="select count(*) as c from location";
    	$this->online_db->query($query);
        $this->online_db->next_record();
        $count_new=$this->online_db->f('c');

        printf ("%d Contacts new registerd\n",$count_new);

		$query="select count(*) as c from count_contacts";
    	$this->online_db->query($query);
        $this->online_db->next_record();
        $count_old=$this->online_db->f('c');

        printf ("%d Contacts old registerd\n",$count_old);

		$query="select * from online_ips";

        dprint($query);
    	$this->online_db->query($query);
        while ($this->online_db->next_record()) {
			$els=explode(";",$this->online_db->f('ip'));
            $ips_old[]=$els[0];
        }
        sort($ips_old);

        printf ("%d IPs old registerd\n",count($ips_old));

		$query="select distinct(SUBSTRING_INDEX(SUBSTRING_INDEX(contact, '@',-1),':',1))
        as ip from location";
        dprint($query);
        $this->online_db->query($query);
        while ($this->online_db->next_record()) {
			$els=explode(";",$this->online_db->f('ip'));
            $ips_new[]=$els[0];
        }
        sort($ips_new);

        printf ("%d IPs new registerd\n",count($ips_new));

        $left=array_diff($ips_old,$ips_new);
        $join=array_diff($ips_new,$ips_old);

        sort($left);
        sort($join);

        if (count($join)) {
        	printf ("%d IPs joined: ",count($join));
            foreach ($join as $var) print "$var ";
            print "\n";
        }

        if (count($left)) {
        	printf ("%d IPs left: ",count($left));
            foreach ($left as $var) print "$var ";
            print "\n";
        }

        //print_r($left);

        $query="drop table if exists online_ips";
        dprint($query);

        $this->online_db->query($query);

        $query="create table online_ips
        select distinct(SUBSTRING_INDEX(SUBSTRING_INDEX(contact, '@',-1),':',1))
        as ip from location";
        dprint($query);

        $this->online_db->query($query);

        $query="drop table if exists count_contacts";
        dprint($query);

        $this->online_db->query($query);

        $query="create table count_contacts
        select count(*)a as c from location";
        dprint($query);

        $this->online_db->query($query);

        //dprint_r($this->online);
	}

    function publishPresence ($soapEngine,$SIPaccount,$note,$activity) {

	  	unset($soapEngines);
    	require("/etc/cdrtool/ngnpro_engines.inc");
		require_once("ngnpro_soap_library.php");

        //$soapEngines
        if (!array_key_exists($soapEngine,$soapEngines)) {
            print "Error: soapEngine '$soapEngine' does not exist.\n";
            return false;
        }

		$this->SOAPurl       = $soapEngines[$soapEngine]['url'];
        $this->PresencePort  = new WebService_SoapSIMPLEProxy_PresencePort($this->SOAPurl);

        $this->PresencePort->setOpt('curl', CURLOPT_SSL_VERIFYPEER, 0);
        $this->PresencePort->setOpt('curl', CURLOPT_SSL_VERIFYHOST, 0);
        $this->PresencePort->setOpt('curl', CURLOPT_TIMEOUT, 1);

        $allowed_activities=array('open',
                                  'idle',
                                  'busy',
                                  'available'
                                 );
    
        if (in_array($activity,$allowed_activities)) {
            $presentity['activity'] = $activity;
        } else {
            $presentity['activity'] = 'open';
        }
    
        $presentity['note']     = $note;

		dprint_r($presentity);
        $result = $this->PresencePort->setPresenceInformation(array("username" =>$SIPaccount['username'],"domain"   =>$SIPaccount['domain']),$SIPaccount['password'], $presentity);
    
        if (PEAR::isError($result)) {
            $error_msg  = $result->getMessage();
            $error_fault= $result->getFault();
            $error_code = $result->getCode();
            printf ("<p><font color=red>Error: %s (%s): %s</font>",$error_msg, $error_fault->detail->exception->errorcode,$error_fault->detail->exception->errorstring);
            return false;
        }
    
        return true;
    }
}


?>
