<?
class CDRS_ser_radius extends CDRS {
    var $table                 = "radacct";
    var $CDR_class             = "CDR_ser_radius";
    var $subscriber_table      = "subscriber";
    var $ENUMtld               = '';

    var $CDRFields=array('id'              => 'RadAcctId',
                         'callId'          => 'AcctSessionId',
                         'duration'        => 'AcctSessionTime',
                         'startTime'       => 'AcctStartTime',
                         'stopTime'        => 'AcctStopTime',
                         'inputTraffic'    => 'AcctInputOctets',
                         'outputTraffic'   => 'AcctOutputOctets',
                         'aNumber'         => 'CallingStationId',
                         'username'        => 'UserName',
                         'domain'           => 'Realm',
                         'cNumber'          => 'CalledStationId',
                         'timestamp'       => 'timestamp',
                         'SipMethod'       => 'SipMethod',
                         'disconnect'      => 'SipResponseCode',
                         'SipFromTag'      => 'SipFromTag',
                         'SipToTag'        => 'SipToTag',
                         'RemoteAddress'   => 'SipTranslatedRequestURI',
                         'SipCodec'        => 'SipCodecs',
                         'SipUserAgents'   => 'SipUserAgents',
                         'applicationType' => 'SipApplicationType',
                         'BillingPartyId'  => 'UserName',
                         'SipRPID'         => 'SipRPID',
                         'SipProxyServer'  => 'NASIPAddress',
                         'gateway'         => 'SourceIP',
                         'SourceIP'        => 'SourceIP',
                         'SourcePort'      => 'SourcePort',
                         'CanonicalURI'    => 'CanonicalURI',
                         'normalized'      => 'Normalized',
                         'rate'            => 'Rate',
                         'price'           => 'Price',
                         'DestinationId'   => 'DestinationId',
                         'BillingId'       => 'BillingId',
                         'MediaTimeout'    => 'MediaInfo',
                         'RTPStatistics'   => 'RTPStatistics',
                         'ENUMtld'         => 'ENUMtld'
                         );

    var $CDRNormalizationFields=array('id'               => 'RadAcctId',
                                      'callId'          => 'AcctSessionId',
                                      'username'        => 'UserName',
                                      'domain'            => 'Realm',
                                      'gateway'         => 'SourceIP',
                                      'duration'        => 'AcctSessionTime',
                                      'startTime'       => 'AcctStartTime',
                                      'stopTime'        => 'AcctStopTime',
                                      'inputTraffic'    => 'AcctInputOctets',
                                      'outputTraffic'   => 'AcctOutputOctets',
                                      'aNumber'          => 'CallingStationId',
                                      'cNumber'           => 'CalledStationId',
                                      'timestamp'        => 'timestamp',
                                      'RemoteAddress'   => 'SipTranslatedRequestURI',
                                      'CanonicalURI'    => 'CanonicalURI',
                                      'SipMethod'       => 'SipMethod',
                                      'applicationType' => 'SipApplicationType',
                                      'BillingPartyId'  => 'UserName',
                                      'price'           => 'Price',
                                      'DestinationId'   => 'DestinationId',
                                      'ENUMtld'         => 'ENUMtld'
                                      );

    var $GROUPBY=array('UserName'             => 'SIP Billing Party',
                       'CallingStationId'     => 'SIP Caller Party',
                       'SipRPID'              => 'SIP Remote Party Id',
                       'DestinationId'        => 'SIP Destination Id',
                       'NASIPAddress'         => 'SIP Proxy',
                       'SourceIP'             => 'Source IP',
                       'Realm'                => 'SIP Billing domain',
                       'UserAgentType'        => 'Caller User Agent',
                       'UserAgentLeft'        => 'Caller User Agent (version)',
                       'UserAgentRight'       => 'Called User Agent (version)',
                       'SipCodecs'            => 'Codec type',
                       'SipApplicationType'   => 'Application',
                       'SipResponseCode'      => 'SIP status code',
                       'BillingId'            => 'Tech prefix',
                       ' '                    => '-------------',
                       'hour'                 => 'Hour of day',
                       'DAYOFWEEK'            => 'Day of Week',
                       'DAYOFMONTH'           => 'Day of Month',
                       'DAYOFYEAR'            => 'Day of Year',
                       'BYMONTH'              => 'Month',
                       'BYYEAR'               => 'Year'
                       );

    var $FormElements=array(
        "begin_hour","begin_min","begin_month","begin_day","begin_year","begin_datetime",
        "end_hour","end_min","end_month","end_day","end_year","end_datetime",
        "call_id","sip_proxy",
        "a_number","a_number_comp","UserName","UserName_comp","BillingId",
        "c_number","c_number_comp","DestinationId","ExcludeDestinations",
        "NASPortId","Realm","Realms","UserNameS",
        "SipMethod","SipCodec","SipRPID","SipUserAgents",
        "application","SipStatus","SipStatusClass","SipProxyServer","gateway",
        "duration","action","MONTHYEAR",
        "order_by","order_type","group_by",
        "cdr_source","trace",
        "unnormalize","MediaTimeout","cdr_table"
        );

    var $createTableFile="/setup/radius/OpenSER/radacct.mysql";

    function LoadDisconnectCodes() {

        $query="select * from sip_status order by code";
        $this->disconnectCodesElements[]=array("label"=>"Any Status","value"=>"");
        $this->disconnectCodesElements[]=array("label"=>"Undefined (0)","value"=>"0");
        $this->disconnectCodesClassElements[]=array("label"=>"Any Status Class","value"=>"");

        if ($this->cdrtool->query($query)) {
            while($this->cdrtool->next_record()) {
                $key         = $this->cdrtool->f('code');
                $value       = $this->cdrtool->f('description');
                $value_print = $this->cdrtool->f('description')." (".$this->cdrtool->f('code').")";
    
                if (preg_match("/^[^2-6]/",$key)) {
                    continue;
                }
                $this->disconnectCodesElements[]=array("label"=>$value_print,"value"=>$key);
                $this->disconnectCodesDescription[$key]=$value;
    
                $class=substr($key,0,1);
                $class_text=substr($key,0,1)."XX (".$this->cdrtool->f('code_type').")";
                if (!$seen[$class]) {
                    $this->disconnectCodesClassElements[]=array("label"=>$class_text,"value"=>substr($key,0,1));
                    $this->disconnectCodesClassDescription[substr($key,0,1)]=$class_text;
                    $seen[$class]++;
                }
                $i++;
            }
        }
    }

    function showTableHeader($begin_datetime,$end_datetime) {

        if (preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/",$begin_datetime) && preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/",$end_datetime)) {
            print "<p>From $begin_datetime to $end_datetime";
        }

        print "
        <table border=1 cellspacing=2 width=100% align=center>
        <tr>
        <td>
        <table border=0 cellspacing=2 width=100%>
        <tr bgcolor=lightgrey>
        <td>Id</td>
        <td><b>Start time</b></td>
        <td><b>Sip Proxy</b></td>
        <td><b>SIP caller</b></td>
        <td><b>In</b></td>
        <td><b>SIP destination</b></td>
        <td><b>Out</b></td>
        <td><b>Dur</b></td>
        <td><b>Price</b></td>
        <td align=right><b>KBIn</b></td>
        <td align=right><b>KBOut</b></td>
        <td align=right><b>Status</b></td>
        <td align=right><b>Codec</b></td>
        </tr>
        ";
    }

    function showExportHeader() {
        print "id,StartTime,StopTime,BillingParty,BillingDomain,PSTNCallerId,CallerParty,CalledParty,DestinationId,DestinationName,RemoteAddress,CanonicalURI,Duration,Price,SIPProxy,Caller KBIn,Called KBIn,CallingUserAgent,CalledUserAgent,StatusCode,StatusName,Codec,Application\n";
    }

    function showTableHeaderSubscriber($begin_datetime,$end_datetime) {
        if (!$this->export) {
            if (preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/",$begin_datetime) && preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/",$end_datetime)) {
                print "<p>
                From $begin_datetime to $end_datetime
                ";
            }
            print  "
            <table border=1 cellspacing=2 width=100% align=center>
            <tr>
            <td>
            <table border=0 cellspacing=2 width=100%>
            <tr bgcolor=lightgrey>
            <td>
            <td><b>Date and time</b>
            <td><b>From</b></td>
            <td><b>To</b></td>
            <td><b>Destination</b></td>
            <td><b>Dur</b></td>
            <td><b>Price</b></td>
            </tr>
            ";
        } else {
            print "id,StartTime,StopTime,SIPBillingParty,SIPBillingDomain,RemotePartyId,CallerParty,CalledParty,DestinationId,DestinationName,RemoteAddress,CanonicalURI,Duration,Price,SIPProxy,Caller KBIn,Called KBIn,CallingUserAgent,CalledUserAgent,StatusCode,StatusName,Codec,Application\n";
        }
    }

    function showTableHeaderStatistics($begin_datetime,$end_datetime) {
        $group_byPrint=$this->GROUPBY[$this->group_byOrig];

        if (!$this->export) {
            if (preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/",$begin_datetime) && preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/",$end_datetime)) {
                print "<p>
                From $begin_datetime to $end_datetime
                ";
            }

            print "
            <table border=1 cellspacing=2 width=100% align=center>
            <tr>
            <td>
            <table border=0 cellspacing=2 width=100%>
            <tr bgcolor=lightgrey>
                <td></td>
                <td>            <b>Calls</b></td>
                <td align=right><b>Seconds</b></td>
                <td align=right><b>Minutes</b></td>
                <td align=right><b>Hours</b></td>
                <td align=right><b>Price</b></td>
                <td align=right><b>TrafficIn(MB)</b></td>
                <td align=right><b>TrafficOut(MB)</b></td>
                <td align=center colspan=2><b>Success</b></td>
                <td align=center colspan=2><b>Failure</b></td>
                <td>            <b>$group_byPrint</b></td>
                <td>            <b>Description</b></td>
                <td>            <b>Action</b></td>
            </tr>
            ";
        } else {
            print "id,Calls,Seconds,Minutes,Hours,Price,TrafficIn(MB),TrafficOut(MB),Success(%),Success(calls),Failure(%),Failure(calls),$group_byPrint,Description\n";
        }
    }


    function initForm() {

        // form els added below must have global vars
        foreach ($this->FormElements as $_el) {
            global ${$_el};
            ${$_el} = trim($_REQUEST[$_el]);
        }

        $action         = "search";
        
        if ($this->CDRTool['filter']['gateway']) {
            $gateway=$this->CDRTool["filter"]["gateway"];
        }

        if ($this->CDRTool['filter']['aNumber']) {
            $UserNameS    = explode(" ",$this->CDRTool['filter']['aNumber']);
            if (count($UserNameS) == 1) {
                $UserName=$this->CDRTool['filter']['aNumber'];
            }
        }

        if ($this->CDRTool['filter']['domain']) {
            $Realm  = $this->CDRTool['filter']['domain'];
        }

        if (!$maxrowsperpage) $maxrowsperpage=25;

        $this->f = new form;

        if (isset($this->CDRTool['dataSourcesAllowed'])) {
            while (list($k,$v)=each($this->CDRTool['dataSourcesAllowed'])) {
                if ($this->DATASOURCES[$v]['invisible']) continue;
                $cdr_source_els[]=array("label"=>$this->DATASOURCES[$v]['name'],"value"=>$v);
            }
        }
        
        if (!$cdr_source) {
            $cdr_source=$cdr_source_els[0]['value'];
        }

        $this->f->add_element(array("name"=>"cdr_source",
                                    "type"=>"select",
                                    "options"=>$cdr_source_els,
                                    "size"=>"1",
                                    "extrahtml"=>"onChange=\"document.datasource.submit.disabled = true; location.href = 'callsearch.phtml?cdr_source=' + this.options[this.selectedIndex].value\"",
                                    "value"=>"$cdr_source"
                              )
                       );

        $cdr_table_els=array();
        foreach ($this->tables as $_table) {
            if (preg_match("/^.*(\d{6})$/",$_table,$m)) {
                $cdr_table_els[]=array("label"=>$m[1],"value"=>$_table);
            } else {
                $cdr_table_els[]=array("label"=>$_table,"value"=>$_table);
            }
        }
    
        $this->f->add_element(array(  "name"=>"cdr_table",
                                "type"=>"select",
                                "options"=>$cdr_table_els,
                                "size"=>"1",
                                "value"=>$cdr_table
                                ));

        if ($begin_datetime) {
            preg_match("/^(\d\d\d\d)-(\d+)-(\d+)\s+(\d\d):(\d\d)/", "$begin_datetime", $parts);
            $begin_year    =date(Y,$begin_datetime);
            $begin_month=date(m,$begin_datetime);
            $begin_day     =date(d,$begin_datetime);
            $begin_hour    =date(H,$begin_datetime);
            $begin_min     =date(i,$begin_datetime);
        } else {
            $begin_day      = $_REQUEST["begin_day"];
            $begin_month    = $_REQUEST["begin_month"];
            $begin_year     = $_REQUEST["begin_year"];
            $begin_hour     = $_REQUEST["begin_hour"];
            $begin_min      = $_REQUEST["begin_min"];
        }
        
        if ($end_datetime) {
            preg_match("/^(\d\d\d\d)-(\d+)-(\d+)\s+(\d\d):(\d\d)/", "$end_datetime", $parts);
            $end_year    =date(Y,$end_datetime);
            $end_month     =date(m,$end_datetime);
            $end_day     =date(d,$end_datetime);
            $end_hour    =date(H,$end_datetime);
            $end_min     =date(i,$end_datetime);
        }  else {
            $end_day        = $_REQUEST["end_day"];
            $end_month      = $_REQUEST["end_month"];
            $end_year       = $_REQUEST["end_year"];
            $end_hour       = $_REQUEST["end_hour"];
            $end_min        = $_REQUEST["end_min"];
        }
        
        $default_year    =Date("Y");
        $default_month    =Date("m");
        $default_day    =Date("d");
        $default_hour    =Date(H,time());
        
        if ($default_hour > 1) {
            $default_hour=$default_hour-1;
        }
        
        $default_hour=preg_replace("/^(\d)$/","0$1",$default_hour);
        $default_min    =Date("i");
        
        if ($default_min > 10) {
            $default_min=$default_min-10;
            $default_min=preg_replace("/^(\d)$/","0$1",$default_min);
        }

        if (!$begin_hour)  $begin_hour  = $default_hour;
        if (!$begin_min)   $begin_min   = $default_min;
        if (!$begin_day)   $begin_day   = $default_day;
        if (!$begin_month) $begin_month = $default_month;
        if (!$begin_year)  $begin_year  = $default_year;

        if (!$end_hour)  $end_hour  = 23;
        if (!$end_min)   $end_min   = 55;
        if (!$end_day)   $end_day   = $default_day;
        if (!$end_month) $end_month = $default_month;
        if (!$end_year)  $end_year  = $default_year;

        $m=0;
        while ($m<24) {
            if ($m<10) { $v="0".$m; } else { $v=$m; }
            $hours_els[]=array("label"=>$v,"value"=>$v);
            $m++;
        }
        $this->f->add_element(array(
                    "name"=>"begin_hour",
                    "type"=>"select",
                    "options"=>$hours_els,
                    "size"=>"1"
                    ));
        $this->f->add_element(array(    "name"=>"end_hour",
                    "type"=>"select",
                    "options"=>$hours_els,
                    "size"=>"1",
                    "value"=>"23"
                    ));
        $m=0;
        while ($m<60) {
            if ($m<10) { $v="0".$m; } else { $v=$m; }
            $min_els[]=array("label"=>$v,"value"=>$v);
            $m++;
        }
        $this->f->add_element(array(    "name"=>"begin_min",
                    "type"=>"select",
                    "options"=>$min_els,
                    "size"=>"1"
                    ));
        $this->f->add_element(array(
                    "name"=>"end_min",
                    "type"=>"select",
                    "options"=>$min_els,
                    "size"=>"1"
                    ));
        $m=1;
        while ($m<32) {
            if ($m<10) { $v="0".$m; } else { $v=$m; }
            $days_els[]=array("label"=>$v,"value"=>$v);
            $m++;
        }
        $this->f->add_element(array(    "name"=>"begin_day",
                                "type"=>"select",
                                "options"=>$days_els,
                                "size"=>"1"
        
                    ));
        $this->f->add_element(array(    "name"=>"end_day",
                                "type"=>"select",
                                "options"=>$days_els,
                                "size"=>"1"
        
                    ));

        $m=1;
        while ($m<13) {
            if ($m<10) { $v="0".$m; } else { $v=$m; }
            $month_els[]=array("label"=>$v,"value"=>$v);
            $m++;
        }
        
        $this->f->add_element(array(    "name"=>"begin_month",
                                "type"=>"select",
                                "options"=>$month_els,
                                "size"=>"1"
                    ));
        $this->f->add_element(array(    "name"=>"end_month",
                                "type"=>"select",
                                "options"=>$month_els,
                                "size"=>"1"
                    ));
        $thisYear=date("Y",time());
        $y=$thisYear;
        while ($y>$thisYear-6) {
            $year_els[]=array("label"=>$y,"value"=>$y);
            $y--;
        }
        $this->f->add_element(array(    "name"=>"begin_year",
                                "type"=>"select",
                                "options"=>$year_els,
                                "size"=>"1"
        
                    ));
        $this->f->add_element(array(    "name"=>"end_year",
                                "type"=>"select",
                                "options"=>$year_els,
                                "size"=>"1"
                    ));
        $this->f->add_element(array(    "name"=>"call_id",
                                "type"=>"text",
                                "size"=>"50",
                                "maxlength"=>"100"
                    ));

        if ($this->CDRTool['filter']['aNumber']) {
            $UserNameS    = explode(" ",$this->CDRTool['filter']['aNumber']);
            $c=count($UserNameS);
            if ($c > 1) {
                $UserNameS_els[]=array("label"=>"All $c numbers","value"=>"");
                foreach ($UserNameS as $_el) {
                    $UserNameS_els[]=array("label"=>$_el,"value"=>$_el);
                }
    
                $this->f->add_element(array(    "name"=>"UserName",
                                        "type"=>"select",
                                        "options"=>$UserNameS_els,
                                        "size"=>"1"
                            ));
            } else {
                $this->f->add_element(array(    "name"=>"UserName",
                                        "type"=>"text",
                                        "size"=>"25",
                                        "maxlength"=>"255"
                            ));

            }

         } else {

            $this->f->add_element(array(    "name"=>"UserName",
                                    "type"=>"text",
                                    "size"=>"25",
                                    "maxlength"=>"255"
                        ));
        }

        $this->f->add_element(array(    "name"=>"a_number",
                                "type"=>"text",
                                "size"=>"25",
                                "maxlength"=>"255"
                    ));
        $this->f->add_element(array(    "name"=>"BillingId",
                                "type"=>"text",
                                "size"=>"25",
                                "maxlength"=>"255"
                    ));
        $this->f->add_element(array(    "name"=>"c_number",
                                "type"=>"text",
                                "size"=>"25",
                                "maxlength"=>"255"
                    ));
        $this->f->add_element(array(  "name"=>"SipStatus",
                                "type"=>"select",
                                "options"=>$this->disconnectCodesElements,
                                "size"=>"1",
                                "value"=>$SipStatus,
                                ));
        $this->f->add_element(array(  "name"=>"SipStatusClass",
                                "type"=>"select",
                                "options"=>$this->disconnectCodesClassElements,
                                "size"=>"1"
                                ));

        if (!$this->CDRTool['filter']['aNumber']) {
            $durations_els = array(
                array("label"=>"All calls","value"=>""),
                array("label"=>"0 seconds","value"=>"zero"),
                array("label"=>"non 0 seconds","value"=>"nonzero"),
                array("label"=>"non 0 seconds with 0 price","value"=>"zeroprice"),
                array("label"=>"less than 5 seconds","value"=>"< 5"),
                array("label"=>"more than 5 seconds","value"=>"> 5"),
                array("label"=>"less than 60 seconds","value"=>"< 60"),
                array("label"=>"greater than 1 hour","value"=>"> 3600"),
                array("label"=>"one hour","value"=>"onehour"),
                array("label"=>"greater than 5 hours","value"=>"> 18000"),
                array("label"=>"Un-normalized calls","value"=>"unnormalized"),
                array("label"=>"One way media","value"=>"onewaymedia")
                );
        } else {
            $durations_els = array(
                array("label"=>"All calls","value"=>""),
                array("label"=>"0 seconds call","value"=>"zero"),
                array("label"=>"Succesfull calls","value"=>"nonzero"),
                array("label"=>"less than 60 seconds","value"=>"< 60"),
                array("label"=>"greater than 1 hour","value"=>"> 3600")
                );
            $this->GROUPBY=array(
                           'UserName'             => 'SIP Billing Party',
                           'CallingStationId'     => 'SIP Caller Party',
                           'DestinationId'        => 'SIP Destination Id',
                           'SipApplicationType'   => 'Application',
                               ' '                => '-------------',
                           'hour'                 => 'Hour of day',
                           'DAYOFWEEK'            => 'Day of Week',
                           'DAYOFMONTH'           => 'Day of Month',
                           'DAYOFYEAR'            => 'Day of Year',
                           'BYMONTH'              => 'Month',
                           'BYYEAR'               => 'Year'

                           );

        }
        
        $this->f->add_element(array(    "name"=>"duration",
                                "type"=>"select",
                                "options"=>$durations_els,
                                "value"=>"All",
                                "size"=>"1"
                    ));
        $comp_ops_els = array(
                array("label"=>"Begins with","value"=>"begin"),
                array("label"=>"Contains","value"=>"contain"),
                array("label"=>"Is empty","value"=>"empty"),
                array("label"=>"Equal","value"=>"equal")
                );
        $this->f->add_element(array(    "name"=>"a_number_comp",
                                "type"=>"select",
                                "options"=>$comp_ops_els,
                                "value"=>"begin",
                                "size"=>"1"
                    ));
        $this->f->add_element(array(    "name"=>"c_number_comp",
                                "type"=>"select",
                                "options"=>$comp_ops_els,
                                "value"=>"begin",
                                "size"=>"1"
                    ));
        $this->f->add_element(array(    "name"=>"UserName_comp",
                                "type"=>"select",
                                "options"=>$comp_ops_els,
                                "value"=>"begin",
                                "size"=>"1"
                    ));
        $this->f->add_element(array(    "name"=>"Realm",
                                "type"=>"text",
                                "size"=>"25",
                                "maxlength"=>"25"
                    ));
        $this->f->add_element(array(    "name"=>"MediaTimeout",
                                "type"=>"checkbox",
                                "value"=>"1"
                    ));
        $this->f->add_element(array("type"=>"submit",
                              "name"=>"submit",
                              "value"=>"Search"
                    ));
        $max_els=array(
                array("label"=>"5","value"=>"5"),
                        array("label"=>"10","value"=>"10"),
                        array("label"=>"15","value"=>"15"),
                        array("label"=>"25","value"=>"25"),
                        array("label"=>"50","value"=>"50"),
                        array("label"=>"100","value"=>"100"),
                        array("label"=>"500","value"=>"500")
                );
        $this->f->add_element(array(    "name"=>"maxrowsperpage",
                                "type"=>"select",
                                "options"=>$max_els,
                                "size"=>"1",
                                "value"=>"25"
                    ));
        $order_type_els=array(
                        array("label"=>"Descending","value"=>"DESC"),
                        array("label"=>"Ascending","value"=>"ASC")
                        );
        $this->f->add_element(array(    "name"=>"order_type",
                                "type"=>"select",
                                "options"=>$order_type_els,
                                "size"=>"1"
                    ));
        $this->f->add_element(array("type"=>"hidden",
                              "name"=>"action",
                              "value"=>$action,
                ));
        $order_by_els=array(array("label"=>"Id","value"=>"RadAcctId"),
                            array("label"=>"Date","value"=>"AcctStopTime"),
                            array("label"=>"Billing Party","value"=>"UserName"),
                            array("label"=>"Remote Party Id","value"=>"SipRPID"),
                            array("label"=>"Caller Party","value"=>"CallingStationId"),
                            array("label"=>"Destination","value"=>"CalledStationId"),
                            array("label"=>"Duration","value"=>"AcctSessionTime"),
                            array("label"=>"Input traffic","value"=>"AcctInputOctets"),
                            array("label"=>"Output traffic","value"=>"AcctOutputInputOctets"),
                            array("label"=>"Price","value"=>"Price"),
                            array("label"=>"Failures(%)","value"=>"zeroP"),
                            array("label"=>"Success(%)","value"=>"nonzeroP"),
                            array("label"=>"Group by","value"=>"group_by")
        );

        $group_by_els[]=array("label"=>"","value"=>"");

        while (list($k,$v)=each($this->GROUPBY)) {
            $group_by_els[]=array("label"=>$v,"value"=>$k);
        }

        $this->f->add_element(array("name"=>"order_by",
                                    "type"=>"select",
                                    "options"=>$order_by_els,
                                    "value"=>$order_by,
                                    "size"=>"1"
                                ));
        $this->f->add_element(array("name"=>"group_by",
                                    "type"=>"select",
                                    "options"=>$group_by_els,
                                    "value"=>$group_by,
                                    "size"=>"1"
                                ));
        $application_els=array(
                               array("label"=>"Any application","value"=>""),
                               array("label"=>"Audio","value"=>"audio"),
                               array("label"=>"Video","value"=>"video"),
                               array("label"=>"Text message" ,"value"=>"message"),
                               array("label"=>"Presence" ,"value"=>"presence")
                               );

        $this->f->add_element(array("name"=>"application",
                                    "type"=>"select",
                                    "options"=>$application_els,
                                    "value"=>$application,
                                    "size"=>"1"
                                ));
        $this->f->add_element(array("name"=>"SipUserAgents",
                                    "type"=>"text",
                                    "size"=>"25",
                                    "maxlength"=>"50",
                                    "value"=>$SipUserAgents
                    ));
        $this->f->add_element(array("name"=>"SipCodec",
                                    "type"=>"text",
                                    "size"=>"10",
                                    "maxlength"=>"50",
                                    "value"=>$SipCodec
                    ));
        $this->f->add_element(array("name"=>"SipProxyServer",
                                    "type"=>"text",
                                    "size"=>"25",
                                    "maxlength"=>"255",
                                    "value"=>$SipProxyServer
                                    ));
        $this->f->add_element(array("name"=>"gateway",
                                    "type"=>"text",
                                    "size"=>"25",
                                    "maxlength"=>"255",
                                    "value"=>$gateway
                                    ));
        $this->f->add_element(array("name"=>"sip_proxy",
                                    "type"=>"text",
                                    "size"=>"25",
                                    "maxlength"=>"255",
                                    "value"=>$sip_proxy
                                    ));
        $this->f->add_element(array("name"=>"DestinationId",
                                    "type"=>"text",
                                    "size"=>"10"
                                ));
        $this->f->add_element(array(    "name"=>"ExcludeDestinations",
                                "type"=>"text",
                                "size"=>"20",
                                "maxlength"=>"255"
                    ));
        $this->f->load_defaults();

    }

    function searchForm() {
        global $perm;

        $this->initForm();
        $this->f->start("","POST","","","datasource");

        print "<table cellpadding=5 CELLSPACING=0 border=6 width=100% align=center>";

        $this->showDataSources ($this->f);
        $this->showDateTimeElements ($this->f);

        // freeze some form els
        if ($this->CDRTool['filter']['aNumber']) {
            $ff[]="a_number";
            $ff[]="a_number_comp";
            $ff[]="UserName";
            $ff[]="UserName_comp";
        }

        if ($this->CDRTool['filter']['domain']) {
            $Realm=$this->CDRTool['filter']['domain'];
            $ff[]="Realm";
        }

        if ($this->CDRTool['filter']['gateway']) {
            $gateway=$this->CDRTool['filter']['gateway'];
            $ff[]="gateway";
        }

        if (count($ff)) {
            $this->f->freeze($ff);
        }

        print "
        <tr> 
            <td align=left>
            <b>SIP Session Id / Source IP</b>
            </td>
            <td valign=top>
            ";
            $this->f->show_element("call_id","");
            print " / ";
            $this->f->show_element("gateway","");
            print "
            Sip Proxy ";

            $this->f->show_element("sip_proxy","");
            print "
            </td>
        </tr>
        <tr>
        </tr>
        ";

        print "
        <tr> 
            <td align=left>
            <b>SIP User Agent / Media Codecs</b>
            </td>
            <td valign=top>
            ";
            $this->f->show_element("SipUserAgents","");
            print " Codec: ";
            $this->f->show_element("SipCodec","");
            print "
            </td>
        </tr>
        <tr>
        </tr>
        ";

        print "
        <tr> 
            <td align=left>
            <b>SIP Billing Party (Username)</b>
            </td>
            <td valign=top> 
            ";
            $this->f->show_element("UserName_comp","");
            $this->f->show_element("UserName","");
            print "@";
            $this->f->show_element("Realm","");
            print " Tech prefix: ";
            $this->f->show_element("BillingId","");
            print "
            </td>
        </tr>
        <tr>
        </tr>
        ";

        print "
        <tr> 
            <td align=left>
            <b>
            SIP Caller Party (From URI)
            </b>
            </td>
            <td valign=top>
            ";
            $this->f->show_element("a_number_comp","");
            $this->f->show_element("a_number");
            print "
            </td>
        </tr>
        <tr>
        </tr>
        ";

        print "
        <tr> 
            <td align=left>
            <b>SIP Destination (Canonical URI)
            </b>
            </td>
            <td valign=top>   ";
            $this->f->show_element("c_number_comp","");
            $this->f->show_element("c_number","");
            print " Exclude: ";
            $this->f->show_element("ExcludeDestinations_comp");
            $this->f->show_element("ExcludeDestinations","");
            print "
            </td>
        </tr>
        <tr>
        </tr>
        ";

        print "
        <tr> 
            <td align=left>
            <b>Duration / Application / Status</b>
            </td>
            <td valign=top>   ";
            $this->f->show_element("duration","");
            $this->f->show_element("application","");
            $this->f->show_element("SipStatus","");
            $this->f->show_element("SipStatusClass","");
            print " Media timeout ";
            $this->f->show_element("MediaTimeout","");
            print "
            </td>
        </tr>
        ";
        print "
        <tr> 
            <td align=left>
            <b>Order by / Group by</b>
            </td>
            <td valign=top>
             ";
            $this->f->show_element("order_by","");
            $this->f->show_element("order_type","");

            if ($perm->have_perm("statistics")) {
               print " Group by ";
               $this->f->show_element("group_by","");
            }

            print "<nobr> Max results per page ";
            $this->f->show_element("maxrowsperpage","");

            print "</nobr>&nbsp";

            if (!$perm->have_perm('readonly')) {
                print ";&nbsp;&nbsp; <nobr>ReNormalize";
                print "<input type=checkbox name=ReNormalize value=1>
                </nobr>";
            }

            print "
            </td>
        </tr>
        ";

        print "
        </table>
        <p>
        <center>
        ";

        $this->f->show_element("submit","");
        $this->f->finish();

        print "
        </center>
        ";
    }

    function searchFormSubscriber() {
        global $perm;

        $this->initForm();
        $this->f->start("","POST","","","datasource");

        print "
        <table cellpadding=5 CELLSPACING=0 border=6 width=100% align=center>
        ";
        $this->showDataSources ($this->f);
        $this->showDateTimeElements ($this->f);

        // freeze some form els
        if ($this->CDRTool['filter']['aNumber']) {
            $UserNameS    = explode(" ",$this->CDRTool['filter']['aNumber']);
            if (count($UserNameS) == 1) {
                $ff[]="UserName";
            }
        }

        if ($this->CDRTool['filter']['domain']) {
            $ff[]="Realm";
        }

        if ($this->CDRTool["filter"]["gateway"]) {
            $ff[]="gateway";
        }

        if (count($ff)) {
            $this->f->freeze($ff);
        }

        print "
        <tr> 
            <td align=left>
            <b>
            SIP Caller Party
            </b>
            </td>
            <td valign=top>
            ";
            $this->f->show_element("a_number","");
            print "
            </td>
        </tr>
        <tr>
        </tr>
        ";

        print "
        <tr> 
            <td align=left>
            <b>
            SIP Billing Party
            </b>
            </td>
            <td valign=top>
            ";
            $this->f->show_element("UserName","");
            print "
            </td>
        </tr>
        <tr>
        </tr>
        ";

        print "
        <tr> 
            <td align=left>
            <b>
            SIP Destination
            </b>
            </td>
            <td valign=top>   ";
            $this->f->show_element("c_number_comp","");
            $this->f->show_element("c_number","");
            //$this->f->show_element("DestinationId","");
            print "
            </td>
        </tr>
        <tr>
        </tr>
        ";
        
        print "
        <tr> 
            <td align=left>
            <b>SIP Session duration</b>
            </td>
            <td valign=top>   ";
            $this->f->show_element("duration","");
            print " Application ";
            $this->f->show_element("application","");
            print "
            </td>
        </tr>
        ";
        print "
        <tr> 
            <td align=left>
            <b>Order by</b>
            </td>
            <td valign=top>
             ";
             $this->f->show_element("order_by","");
             $this->f->show_element("order_type","");

             if ($perm->have_perm("statistics")) {
                print " Group by ";
                $this->f->show_element("group_by","");
             }

             print " Max results per page ";
             $this->f->show_element("maxrowsperpage","");
             print "
             </td>
        </tr>
        ";

        print "
        </table>
        <p>
        <center>
        ";

        $this->f->show_element("submit","");
        $this->f->finish();

        print "
        </center>
        ";

    }

    function show() {
        global $perm;

        foreach ($this->FormElements as $_el) {
            ${$_el} = trim($_REQUEST[$_el]);
        }

        // overwrite some elements based on user rights
        if ($this->CDRTool['filter']['gateway']) {
            $gateway  =$this->CDRTool['filter']['gateway'];
        }

        if (!$this->export) {
            if (!$begin_datetime) {
                $begin_datetime="$begin_year-$begin_month-$begin_day $begin_hour:$begin_min";
                $begin_datetime_timestamp=mktime($begin_hour, $begin_min, 0, $begin_month,$begin_day,$begin_year);
            } else {
                $begin_datetime_timestamp=$begin_datetime;
                $begin_datetime=Date("Y-m-d H:i",$begin_datetime);
            }

            $begin_datetime_url=urlencode($begin_datetime_timestamp);

            if (!$end_datetime) {
                $end_datetime_timestamp=mktime($end_hour, $end_min, 0, $end_month,$end_day,$end_year);
                $end_datetime="$end_year-$end_month-$end_day $end_hour:$end_min";
            } else {
                $end_datetime_timestamp=$end_datetime;
                $end_datetime=Date("Y-m-d H:i",$end_datetime);
            }
            $end_datetime_url=urlencode($end_datetime_timestamp);
        } else {
            $begin_datetime=Date("Y-m-d H:i",$begin_datetime);
            $end_datetime=Date("Y-m-d H:i",$end_datetime);
        }

        if (!$order_by || (!$group_by && $order_by == "group_by")) {
            $order_by=$this->idField;
        }

        if (!$cdr_table) $cdr_table=$this->table;

        $this->url="?cdr_source=$this->cdr_source&cdr_table=$cdr_table";

        if ($this->CDRTool['filter']['domain']) {
            $this->url   = $this->url."&Realms=".urlencode($this->CDRTool['filter']['domain']);
            $Realms      = explode(" ",$this->CDRTool['filter']['domain']);
        } else if ($Realms) {
            $this->url   = $this->url."&Realms=".urlencode($Realms);
            $Realms      = explode(" ",$Realms);
        }

        if ($this->CDRTool['filter']['aNumber']) {
            $this->url   = $this->url."&UserNameS=".urlencode($this->CDRTool['filter']['aNumber']);
            $UserNameS    = explode(" ",$this->CDRTool['filter']['aNumber']);
        } else if ($UserNameS) {
            $this->url   = $this->url."&UserNameS=".urlencode($UserNameS);
            $UserNameS    = explode(" ",$UserNameS);
        }

        if ($order_by) {
            $this->url.="&order_by=$order_by&order_type=$order_type";
        }

        $this->url.="&begin_datetime=$begin_datetime_url";
        $this->url.="&end_datetime=$end_datetime_url";

        if (!$call_id && $begin_datetime && $end_datetime) {
            $where .=  " ($this->startTimeField >= '$begin_datetime' and $this->startTimeField < '$end_datetime') ";
        } else {
            $where .=  " ($this->startTimeField >= '1970-01-01' ) ";
        }

        if ($MONTHYEAR) {
            $where .=  " and $this->startTimeField like '$MONTHYEAR%' ";
            $MONTHYEAR_url=urlencode($MONTHYEAR);
            $this->url.="&MONTHYEAR=$MONTHYEAR_url";
        }

        if ($this->CDRTool['filter']['aNumber']) {
            $UserName_comp="equal";
            // force user to see only CDRS with his a_numbers
             $where .= "
            and $this->usernameField in (" ;
            $rr=0;
            foreach ($UserNameS as $_el) {
                $_el=trim($_el);
                if (strlen($_el)) {
                    if ($rr) $where .= ", ";
                    $where .= " '$_el'";
                    $rr++;
                }
            }
            $where .= ") ";

        } else if ($UserNameS)  {
            $UserName_comp="equal";
             $where .= "
            and $this->usernameField in (" ;
            $rr=0;
            foreach ($UserNameS as $_el) {
                $_el=trim($_el);
                if (strlen($_el)) {
                    if ($rr) $where .= ", ";
                    $where .= " '$_el' ";
                    $rr++;
                }
            }
            $where .= ") ";

        }

        if ($UserName_comp != "empty") {
            $UserName=trim($UserName);
            if ($UserName) {
                $UserName_encoded=urlencode($UserName);
                if ($UserName_comp=="begin") {
                    $where .= " and $this->usernameField like '".addslashes($UserName)."%'";
                } elseif ($UserName_comp=="contain") {
                    $where .= " and $this->usernameField like '%".addslashes($UserName)."%'";
                } elseif ($UserName_comp=="equal") {
                    $where .= " and $this->usernameField = '".addslashes($UserName)."'";
                }
                $this->url.="&UserName=$UserName_encoded&UserName_comp=$UserName_comp";
            }

        } else {
            $where .= " and $this->usernameField = ''";
            $this->url.="&UserName_comp=$UserName_comp";
        }

        $a_number=trim($a_number);
        if ($a_number_comp == "empty") {
            $where .= " and $this->aNumberField = ''";
            $this->url.="&a_number_comp=$a_number_comp";
        } else if (strlen($a_number)) {
            $a_number=urldecode($a_number);
            if (!$a_number_comp) $a_number_comp="equal";
            $a_number_encoded=urlencode($a_number);

            $this->url.="&a_number=$a_number_encoded";

            if ($a_number_comp=="begin") {
                $where .= " and ($this->aNumberField like '".addslashes($a_number)."%'";
                $s=1;
            } elseif ($a_number_comp=="contain") {
                $where .= " and ($this->aNumberField like '%".addslashes($a_number)."%'";
                $s=1;
            } elseif ($a_number_comp=="equal") {
                $where .= " and ($this->aNumberField = '".addslashes($a_number)."'";
                $s=1;
            }

            if ($this->CDRTool['filter']['aNumber']) {
                $where .= " or $this->CanonicalURIField like '".addslashes($a_number)."%') ";
            } else {
                if ($s) $where .= ")";
            }

            $this->url.="&a_number_comp=$a_number_comp";

        }

        $Realm=trim($Realm);

        if ($this->CDRTool['filter']['domain']) {
            $where .= "
            and (" ;
            $rr=0;
            foreach ($Realms as $realm) {
                if ($rr) {
                    $where .= " or ";
                }
                $where .= " $this->domainField like '".addslashes($realm)."%' ";

                $rr++;
            }
            $where .= " ) ";

        } else if ($Realm) {
            $Realm=urldecode($Realm);
            $where .= " and $this->domainField like '".addslashes($Realm)."%' ";
            $Realm_encoded=urlencode($Realm);
            $this->url.="&Realm=$Realm_encoded";
        } else if ($Realms)  {
            $where .= "
            and (" ;
            $rr=0;
            foreach ($Realms as $realm) {
                if ($rr) {
                    $where .= " or ";
                }
                $where .= " $this->domainField like '".addslashes($realm)."%' ";

                $rr++;
            }
            $where .= " ) ";
        }

        $BillingId=trim($BillingId);
        if (preg_match("/^\d+$/",$BillingId) && $this->BillingIdField) {
            $where .= " and $this->BillingIdField = '".addslashes($BillingId)."'";
            $BillingId_encoded=urlencode($BillingId);
            $this->url.="&BillingId=$BillingId_encoded";
        }

        if ($application) {
            $where .= " and $this->applicationTypeField like '%".addslashes($application)."%'";
            $application_encoded=urlencode($application);
            $this->url.="&application=$application_encoded";
        }

        if ($DestinationId) {
            if ($DestinationId=="empty") {
                $DestinationIdSQL="";
            } else {
                $DestinationIdSQL=$DestinationId;
            }
            $where .= " and $this->DestinationIdField = '".addslashes($DestinationIdSQL)."'";
            $DestinationId_encoded=urlencode($DestinationId);
            $this->url.="&DestinationId=$DestinationId_encoded";
        }

        if (strlen(trim($ExcludeDestinations))) {
            $ExcludeDestArray=explode(" ",trim($ExcludeDestinations));

            foreach ($ExcludeDestArray as $exclDst) {
                if (preg_match("/^0+(\d+)$/",$exclDst,$m)) {
                    $exclDest_id=$m[1];
                } else {
                    $exclDest_id=$exclDst;
                }

                $where .= " and ".
                $this->CanonicalURIField.
                " not like '".
                addslashes(trim($exclDst)).
                "'";
            }

            $ExcludeDestinations_encoded=urlencode($ExcludeDestinations);
            $this->url.="&ExcludeDestinations=$ExcludeDestinations_encoded";

        }

        $call_id=trim($call_id);

        if ($call_id) {
            $call_id=urldecode($call_id);
            $where .= " and $this->callIdField = '".addslashes($call_id)."'";
            $call_id_encoded=urlencode($call_id);
            $this->url.="&call_id=$call_id_encoded";
        }

        if ($sip_proxy) {
            $sip_proxy=urldecode($sip_proxy);
            $where .= " and $this->SipProxyServerField = '".addslashes($sip_proxy)."'";
            $sip_proxy_encoded=urlencode($sip_proxy);
            $this->url.="&sip_proxy=$sip_proxy_encoded";
        }

        if ($SipCodec) {
            $SipCodec_enc=urlencode($SipCodec);

            $this->url.="&SipCodec=$SipCodec_enc";
            if ($SipCodec != "empty") {
                $where .= " and $this->SipCodecField = '".addslashes($SipCodec)."'";
            } else {
                $where .= " and $this->SipCodecField = ''";
            }
        }

        if ($SipRPID) {
            $SipRPID_enc=urlencode($SipRPID);
            $this->url.="&SipRPID=$SipRPID_enc";
            if ($SipRPID != "empty") {
                $where .= " and $this->SipRPIDField = '".addslashes($SipRPID)."'";
            } else {
                $where .= " and $this->SipRPIDField = ''";
            }
        }

        if ($SipUserAgents) {
            $where .= " and $this->SipUserAgentsField like '%".addslashes($SipUserAgents)."%'";
            $SipUserAgents_enc=urlencode($SipUserAgents);
            $this->url.="&SipUserAgents=$SipUserAgents_enc";
        }

        if (strlen($SipStatus)) {
            $where .= " and $this->disconnectField ='".addslashes($SipStatus)."'";
            $this->url.="&SipStatus=$SipStatus";
        }

        if ($SipStatusClass) {
            $where .= " and $this->disconnectField like '$SipStatusClass%'";
            $this->url.="&SipStatusClass=$SipStatusClass";
        }

        if ($this->CDRTool[filter]["gateway"]) {
            $gatewayFilter=$this->CDRTool[filter]["gateway"];
            $where .= " and ($this->gatewayField = '".addslashes($gatewayFilter)."')";
        } else if ($gateway) {
            $gateway=urldecode($gateway);
            $gateway_encoded=urlencode($gateway);
            $where .= " and $this->gatewayField = '".addslashes($gateway)."'";
            $this->url.="&gateway=$gateway_encoded";
        }

        $c_number=trim($c_number);

        if (strlen($c_number)) {
            $c_number=urldecode($c_number);

            if (!$c_number_comp || $c_number_comp=="begin") {
                // $where .= " and ($this->CanonicalURIField like '".addslashes($c_number)."%' or $this->RemoteAddressField like '".addslashes($c_number)."%')";
                $where .= " and $this->CanonicalURIField like '".addslashes($c_number)."%'";
            } elseif ($c_number_comp=="equal") {
                // $where .= " and ($this->CanonicalURIField = '".addslashes($c_number)."' or $this->RemoteAddressField like '".addslashes($c_number)."%') ";
                $where .= " and $this->CanonicalURIField = '".addslashes($c_number)."'";
            } elseif ($c_number_comp=="contain") {
                // $where .= " and ($this->CanonicalURIField like '%".addslashes($c_number)."%' or $this->RemoteAddressField like '%".addslashes($c_number)."%') ";
                $where .= " and $this->CanonicalURIField like '%".addslashes($c_number)."%'";
            }
            $c_number_encoded=urlencode($c_number);
            $this->url.="&c_number=$c_number_encoded&c_number_comp=$c_number_comp";
        }

        if ($duration) {
            if (preg_match("/\d+/",$duration) ) {
                $where .= " and ($this->durationField > 0 and $this->durationField $duration) ";
            } elseif (preg_match("/onehour/",$duration) ) {
                $where .= " and ($this->durationField < 3610 and $this->durationField > 3530) ";
            } elseif ($duration == "zero") {
                $where .= " and $this->durationField = 0";
            } elseif ($duration == "zeroprice" && $this->priceField) {
                $where .= " and $this->durationField > 0 and $this->priceField ='' ";
            } elseif ($duration == "nonzero") {
                $where .= " and $this->durationField > 0";
            } elseif ($duration == "onewaymedia") {
                $where .= " and (($this->inputTrafficField > 0 && $this->outputTrafficField = 0) || ($this->inputTrafficField = 0 && $this->outputTrafficField > 0)) " ;
            }

            $duration_enc=urlencode($duration);
            $this->url.="&duration=$duration_enc";

        }

        if ($MediaTimeout) {
            $this->url.="&MediaTimeout=1";
            $where .= " and $this->MediaTimeoutField > ''";
        }

        $this->url.="&maxrowsperpage=$this->maxrowsperpage";
        $url_calls = $this->scriptFile.$this->url."&action=search";

        if ($group_by) {
            $this->url.="&group_by=$group_by";
        }

        $this->url_edit        = $this->scriptFile.$this->url."&action=edit";
        $this->url_run        = $this->scriptFile.$this->url."&action=search";
        $this->url_export    = $_SERVER["PHP_SELF"].$this->url."&action=search&export=1";

        if ($duration == "unnormalized") {
            $where .=  " and $this->normalizedField = '0' ";
        }

        if ($group_by) {

            $this->group_byOrig=$group_by;

            if ($group_by=="UserAgentLeft") {
                $group_by="SUBSTRING_INDEX($this->SipUserAgentsField, '+', '1')";
            } else if ($group_by=="UserAgentRight") {
                $group_by="SUBSTRING_INDEX($this->SipUserAgentsField, '+', '-1')";
            } else if ($group_by=="hour") {
                $group_by="HOUR(AcctStartTime)";
            } else if (preg_match("/^DAY/",$group_by)) {
                $group_by="$group_by(AcctStartTime)";
            } else if (preg_match("/BYMONTH/",$group_by)) {
                $group_by="DATE_FORMAT(AcctStartTime,'%Y-%m')";
            } else if (preg_match("/BYYEAR/",$group_by)) {
                $group_by="DATE_FORMAT(AcctStartTime,'%Y')";
            } else if ($group_by=="UserAgentType") {
                $group_by="SUBSTRING_INDEX($this->SipUserAgentsField, ' ', '1')";
            }

            $this->group_by=$group_by;

            if ($group_by==$this->callIdField) {
                $having=" having count($group_by) > 1 ";
            }

            $query= "select sum($this->durationField) as duration,
            SEC_TO_TIME(sum($this->durationField)) as duration_print,
            count($group_by) as calls,
            $group_by
            from $cdr_table
            where $where
            group by $group_by
            $having
            ";
        } else {
            $query = "select count(*) as records
            from $cdr_table where ".$where;
        }


        if ($this->CDRdb->query($query)) {
             $this->CDRdb->next_record();
             if ($group_by) {
                $rows = $this->CDRdb->num_rows();
             } else {
                $rows = $this->CDRdb->f('records');
             }
        } else {
            printf ("%s",$this->CDRdb->Error);
            $rows = 0;
        }

        $this->rows=$rows;

        if ($this->CDRTool['filter']['aNumber']) {
            $this->showResultsMenuSubscriber();
        } else {
            $this->showResultsMenu();
        }

        if (!$this->next)   {
            $i=0;
            $this->next=0;
        } else {
            $i=$this->next;
        }
        $j=0;
        $z=0;

        if (!$this->export) print "<p>";
        
        if ($rows>0)  {
            
            if ($call_id && $unnormalize) {
                $query=sprintf("update %s
                set %s = '0'
                where %s = '%s'",
                addslashes($cdr_table),
                addslashes($this->normalizedField),
                addslashes($this->callIdField),
                addslashes($call_id)
                );

                $this->CDRdb->query($query);
            }

            if ($UnNormalizedCalls=$this->getUnNormalized($where,$cdr_table)) {
                $this->NormalizeCDRS($where,$cdr_table);
                if (!$this->export && $this->status['normalized'] ) {
                    printf ("%d CDRs normalized. ",$this->status['normalized']);
                    if ($this->status['cached_keys']['saved_keys']) {
                        printf ("Quota usage updated for %d accounts. ",$this->status['cached_keys']['saved_keys']);
                    }
                }
            }

            if ($rows > $this->maxrowsperpage)  {
                $maxrows=$this->maxrowsperpage+$this->next;
                if ($maxrows > $rows) {
                    $maxrows=$rows;
                    $prev_rows=$maxrows;
                }
            } else {
                $maxrows=$rows;
            }

            if ($duration == "unnormalized") {
                // if display un normalized calls we must substract
                // the amount of calls normalized above
                $maxrows=$maxrows-$this->status['normalized'];
            }
        
            if ($group_by) {
                if ($order_by=="group_by") {
                    $order_by1=$group_by;
                } else {
                    if ($order_by == $this->inputTrafficField ||
                        $order_by == $this->outputTrafficField ||
                        $order_by == $this->durationField ||
                        $order_by == $this->priceField ||
                        $order_by == "zeroP" ||
                        $order_by == "nonzeroP" )  {
                        $order_by1=$order_by;
                    } else {
                        $order_by1="calls";
                    }
                }

                $this->SipMethodField=$this->CDRFields['SipMethod'];
                $query= "
                select sum($this->durationField) as $this->durationField,
                SEC_TO_TIME(sum($this->durationField)) as hours,
                count($group_by) as calls,
                $this->SipMethodField,
                2*sum($this->inputTrafficField)/1024/1024 as $this->inputTrafficField,
                2*sum($this->outputTrafficField)/1024/1024 as $this->outputTrafficField,
                SUM($this->durationField = '0') as zero,
                SUM($this->durationField > '0') as nonzero,
                ";

                if ($order_by=="zeroP" || $order_by=="nonzeroP") {
                    $query.="
                    SUM($this->durationField = '0')/count($group_by)*100 as zeroP,
                    SUM($this->durationField > '0')/count($group_by)*100 as nonzeroP,
                    ";
                }

                $query.="
                sum($this->inputTrafficField)*8*2/1024/sum($this->durationField) as netrate_in,
                sum($this->outputTrafficField)*8*2/1024/sum($this->durationField) as netrate_out
                ";

                if ($this->priceField) {
                    $query.= ", sum($this->priceField) as $this->priceField ";
                }

                $query.= "
                , $group_by as mygroup
                from $cdr_table
                where $where
                group by $group_by
                $having
                order by $order_by1 $order_type
                limit $i,$this->maxrowsperpage
                ";

                $this->CDRdb->query($query);

                $this->showTableHeaderStatistics($begin_datetime,$end_datetime);

                while ($i<$maxrows)  {
                
                    $found=$i+1;
                    $this->CDRdb->next_record();

                    $calls                 = $this->CDRdb->f('calls');
                    $seconds               = $this->CDRdb->f($this->durationField);
                    $seconds_print            = number_format($this->CDRdb->f($this->durationField),0);
                    $minutes               = number_format($this->CDRdb->f($this->durationField)/60,0,"","");
                    $minutes_print           = number_format($this->CDRdb->f($this->durationField)/60,0);
                    $hours              = $this->CDRdb->f('hours');

                    $AcctInputOctets    = number_format($this->CDRdb->f($this->inputTrafficField),2,".","");
                    $AcctOutputOctets   = number_format($this->CDRdb->f($this->outputTrafficField),2,".","");
                    $NetRateIn          = $this->CDRdb->f('netrate_in');
                    $NetRateOut         = $this->CDRdb->f('netrate_out');
                    $SipMethod          = $this->CDRdb->f($this->callTypeField);
                    $AcctTerminateCause = $this->CDRdb->f($this->disconnectField);
                    $mygroup            = $this->CDRdb->f('mygroup');

                    $zero                = $this->CDRdb->f('zero');
                    $nonzero            = $this->CDRdb->f('nonzero');
                    $success            = number_format($nonzero/$calls*100,2,".","");
                    $failure            = number_format($zero/$calls*100,2,".","");

                    $NetworkRateIn      = number_format($NetRateIn,2);
                    $NetworkRateOut     = number_format($NetRateOut,2);
                    $NetworkRate        = max($NetworkRateIn,$NetworkRateOut);

                    if ($this->priceField) {
                        $price          = $this->CDRdb->f($this->priceField);
                    }

                    $rr=floor($found/2);
                    $mod=$found-$rr*2;
                
                    if ($mod ==0) {
                        $inout_color="lightgrey";
                    } else {
                        $inout_color="white";
                    }

                    $traceValue="";

                    if ($this->group_byOrig==$this->DestinationIdField) {
                        if ($this->CDRTool['filter']['domain'] && $this->destinations[$this->CDRTool['filter']['domain']]) {
                            $description=$this->destinations[$this->CDRTool['filter']['domain']][$mygroup];
                        } else {
                            $description=$this->destinations["default"][$mygroup];
                        }

                        $mygroup_print=$mygroup;
                        if ($mygroup) {
                            $traceValue=$mygroup;
                        } else {
                            $traceValue="empty";
                        }

                    } else if ($this->group_byOrig==$this->aNumberField) {
                        # Normalize Called Station Id
                        $N=$this->NormalizeNumber($mygroup);
                        $mygroup_print=$N[username]."@".$N[domain];
                        $description="";
                        $traceField="a_number";
                        $traceValue=urlencode($mygroup);
                    } else if ($this->group_byOrig==$this->cNumberField) {
                        $traceField="c_number";
                        $traceValue=urlencode($mygroup);
                    } else if ($this->group_byOrig==$this->SipCodecField) {
                        $traceField="SipCodec";
                        $mygroup_print = $mygroup;
                    } else if (preg_match("/UserAgent/",$this->group_byOrig)) {
                        $traceField="SipUserAgents";
                        $mygroup_print = $mygroup;
                    } else if (preg_match("/^BY/",$this->group_byOrig)) {
                        $traceField="MONTHYEAR";
                        $mygroup_print = $mygroup;
                    } else if ($this->group_byOrig==$this->callIdField) {
                        $traceField="call_id";
                    } else if ($this->group_byOrig=="DAYOFWEEK") {
                        if ($mygroup == "1") {
                            $description="Sunday";
                        } else if ($mygroup == "2") {
                            $description="Monday";
                        } else if ($mygroup == "3") {
                            $description="Tuesday";
                        } else if ($mygroup == "4") {
                            $description="Wednesday";
                        } else if ($mygroup == "5") {
                            $description="Thursday";
                        } else if ($mygroup == "6") {
                            $description="Friday";
                        } else if ($mygroup == "7") {
                            $description="Saturday";
                        }
                        $mygroup_print=$mygroup;

                    } else if ($this->group_byOrig=="DAYOFMONTH") {
                        $description    =$this->CDRdb->f('day');
                        $mygroup_print = $mygroup;
                    } else if ($this->group_byOrig=="DAYOFYEAR") {
                        $description    =$this->CDRdb->f('day');
                        $mygroup_print = $mygroup;
                    } else if ($this->group_byOrig=="SourceIP") {
                        $traceField="gateway";
                        $mygroup_print = $mygroup;
                    } else if ($this->group_byOrig=="SipResponseCode") {
                        $description    =$this->disconnectCodesDescription[$mygroup];
                        $mygroup_print = $mygroup;
                        $traceField="SipStatus";
                    } else {
                        $description   = "";
                        $mygroup_print = $mygroup;
                    }

                    if (!$traceField) {
                        $traceField    = $group_by;
                    }

                    if (!$traceValue) {
                        $traceValue    = $mygroup;
                    }

                    if (!$traceValue) {
                        $traceValue="empty";
                    }

                    $traceValue_enc=urlencode($traceValue);

                    if (!$this->export) {
                        print "
                        <tr bgcolor=$inout_color>
                        <td><b>$found</b></td>
                        <td align=right>$calls</td>
                        <td align=right>$seconds_print</td>
                        <td align=right>$minutes_print</td>
                        <td align=right>$hours</td>
                        ";
                        if ($perm->have_perm("showPrice")) {
                            $pricePrint=number_format($price,4,".","");
                        } else {
                            $pricePrint='x.xxx';
                        }
                        print "
                        <td align=right>$pricePrint</td>
                        <td align=right>$AcctInputOctets</td>
                        <td align=right>$AcctOutputOctets</td>
                        <td align=right>$success%</td>
                        <td align=right>($nonzero calls)</td>
                        <td align=right>$failure%</td>
                        <td align=right>($zero calls)</td>
                        <td>$mygroup_print</td>
                        <td>$description</td>
                        <td>";
                        printf("<a href=%s&%s=%s&%s_comp=begin target=_new>Display calls</a></td>",$url_calls,$traceField,$traceValue_enc,$traceField);
                        print "
                        </tr>
                        ";
                    } else {
                         print "$found,";
                         print "$calls,";
                         print "$seconds,";
                         print "$minutes,";
                         print "$hours,";
                         if ($perm->have_perm("showPrice")) {
                             $pricePrint=$price;
                         } else {
                             $pricePrint='x.xxx';
                         }
                         print "$pricePrint,";
                         print "$AcctInputOctets,";
                         print "$AcctOutputOctets,";
                         print "$success,";
                         print "$nonzero,";
                         print "$failure,";
                         print "$zero,";
                         print "$mygroup_print,";
                         print "$description";
                         print "\n";
                    }
                    $i++;
                 }

                 if (!$this->export) {
                    print "
                    </table>
                    </td>
                    </tr>
                    </table>
                    ";
                 }
        
            } else {
                if (!$this->export && !$this->CDRTool['filter']['aNumber']) {
                    printf ("For more information about each call click on its Id column. ");
                }

                if ($order_by=="zeroP" || $order_by=="nonzeroP") {
                    $order_by="timestamp";
                }
                $query="select *, UNIX_TIMESTAMP($this->startTimeField) as timestamp
                from $cdr_table where ".
                $where.
                " order by $order_by $order_type ".
                " limit $i,$this->maxrowsperpage";

                $this->CDRdb->query($query);

                if ($this->CDRTool['filter']['aNumber']) {
                    $this->showTableHeaderSubscriber($begin_datetime,$end_datetime);
                } else {
                    if (!$this->export) {
                        $this->showTableHeader($begin_datetime,$end_datetime);
                    } else {
                        $this->showExportHeader();
                    }
                }

                while ($i<$maxrows)  {
                    global $found;
                    $found=$i+1;
                    $this->CDRdb->next_record();                                            

                    $Structure=$this->_readCDRFieldsFromDB();
                    $CDR = new $this->CDR_class($this, $Structure);

                    if ($this->CDRTool['filter']['aNumber']) {
                        $CDR->showSubscriber();
                    } else {
                         if (!$this->export) {
                            $CDR->show();
                        } else {
                            $CDR->export();
                        }
                    }

                    $i++;
                 }

                 if (!$this->export) {
                    print "
                    </table>
                    </td>
                    </tr>
                    </table>
                    ";
                 }
        
            }

            $this->showPagination($this->next,$maxrows);
        }
    }

    function LoadDomains() {

        if (!$this->AccountsDBClass) {
            return false;
        }

        if (strlen($this->DATASOURCES[$this->cdr_source]['enableThor'])) {
            $this->domain_table          = "sip_domains";
        } else {
            $this->domain_table          = "domain";
        }

        $query=sprintf("select domain from %s",$this->domain_table);
        if ($this->CDRTool['filter']['aNumber']) {
            $els=explode("@",$this->CDRTool['filter']['aNumber']);
            $query.=" where domain = '$els[1]' ";
        } else if ($this->CDRTool['filter']['domain']) {
            $fdomain=$this->CDRTool['filter']['domain'];
            $query.=" where domain = '$fdomain' ";
        }

        if (!$this->AccountsDB->query($query)) {
            printf ("<p>Database error %s %s",$this->AccountsDB->Error,$query);
            return false;
        }

        while($this->AccountsDB->next_record()) {
            if ($this->AccountsDB->f('domain')) {
                $this->localDomains[]=$this->AccountsDB->f('domain');
            }
        }

        return count($this->localDomains);
    }

    function getQuota($account) {
        if (!$account) return;

        list($username,$domain) = explode("@",$account);

        if ($this->enableThor) {

            $query=sprintf("select * from sip_accounts where username = '%s' and domain = '%s'",$username,$domain);
    
            if (!$this->AccountsDB->query($query)) {
                $log=sprintf ("Database error for query 1 %s: %s (%s)",$query,$this->AccountsDB->Error,$this->AccountsDB->Errno);
                syslog(LOG_NOTICE,$log);
                return 0;
            }

            if ($this->AccountsDB->num_rows()) {
            	$this->AccountsDB->next_record();
            	$_profile=json_decode(trim($this->AccountsDB->f('profile')));
                return $_profile->quota;

            } else {
                return 0;
            }
        } else {

            $query=sprintf("select quota from subscriber where username = '%s' and domain = '%s'",$username,$domain);

            if (!$this->AccountsDB->query($query)) {
                $log=sprintf ("Database error for query %s: %s (%s)",$query,$this->AccountsDB->Error,$this->AccountsDB->Errno);
                syslog(LOG_NOTICE,$log);
                return 0;
            }

            if ($this->AccountsDB->num_rows()) {
            	$this->AccountsDB->next_record();
                return $this->AccountsDB->f('quota');
            } else {
                return 0;
            }
        }
    }

    function getBlockedByQuotaStatus($account) {

        if (!$account) return 0;
        list($username,$domain) = explode("@",$account);

        if ($this->enableThor) {
            $query=sprintf("select * from sip_accounts where username = '%s' and domain = '%s'",$username,$domain);
    
            if (!$this->AccountsDB->query($query)) {

                $log=sprintf ("Database error for query2 %s: %s (%s)",$query,$this->AccountsDB->Error,$this->AccountsDB->Errno);
                syslog(LOG_NOTICE,$log);
                return 0;
            }

            if ($this->AccountsDB->num_rows()) {
            	$this->AccountsDB->next_record();

            	$_profile=json_decode(trim($this->AccountsDB->f('profile')));
                if (in_array('quota',$_profile->groups)) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            $query=sprintf("select CONCAT(username,'@',domain) as account from grp where grp = 'quota' and username = '%s' and domain = '%s'",$username,$domain);
    
            if (!$this->AccountsDB->query($query)) {
                $log=sprintf ("Database error for query %s: %s (%s)",$query,$this->AccountsDB->Error,$this->AccountsDB->Errno);
                syslog(LOG_NOTICE,$log);
                return 0;
            }

            if ($this->AccountsDB->num_rows()) {
                return 1;
            } else {
                return 0;
            }

        }

        return 0;
    }
}

class CDR_ser_radius extends CDR {

    var $show_in_icon=0;
    var $show_out_icon=0;
    var $QoSParameters=array(
        "PS"=>"Audio packets sent",
        "OS"=>"Audio octets sent",
        "SP"=>"Comfort noise packets sent",
        "SO"=>"Silence octets sent",
        "PR"=>"Audio packets received",
        "OR"=>"Audio octets received",
        "CR"=>"Comfort noise packets received",
        "SR"=>"Comfort noise octets received",
        "PL"=>"Receive packets lost",
        "BL"=>"Receive maximum burst packets lost",
        "EN"=>"Encoder1, encoder 2",
        "DE"=>"Decoder1, decoder 2",
        "JI"=>"Jitter in ms"
        );

    function CDR_ser_radius(&$parent, $CDRfields) {

        $this->CDRS = &$parent;

        $this->cdr_source  = $this->CDRS->cdr_source;

        foreach (array_keys($this->CDRS->CDRFields) as $field) {
            $this->$field = $CDRfields[$this->CDRS->CDRFields[$field]];
        }

        if ($this->CanonicalURI) {
            $this->CanonicalURI   = quoted_printable_decode($this->CanonicalURI);
        }

        if ($this->RemoteAddress) {
            $this->RemoteAddress  = quoted_printable_decode($this->RemoteAddress);
        }

        if ($this->BillingPartyId) {
            $this->BillingPartyId = quoted_printable_decode($this->BillingPartyId);
        }

        if ($this->aNumber) {
            $this->aNumber        = quoted_printable_decode($this->aNumber);
        }

        if ($this->cNumber) {
            $this->cNumber        = quoted_printable_decode($this->cNumber);
        }

        if (!$this->applicationType && $this->SipMethod) {
            $_method=strtolower($this->SipMethod);
            if ($_method=="publish"   ||
                $_method=="subscribe" ||
                $_method=="notify"    ){
                $this->applicationType="presence";
            } else if ($_method=="message") {
                $this->applicationType="message";
            } else {
                $this->applicationType="audio";
            }
        }

        $this->applicationType=strtolower($this->applicationType);

        if (strstr($this->applicationType,'video')) {
            // we rate calls containing video same as audio
            // is not possible to determine that a session is
            // video from start to end as SIP allows streams to
            // be added and substracted on the fly
            $this->applicationType='audio';
        }

        if (!in_array($this->applicationType,$this->supportedApplicationTypes)) {
            $this->applicationType = $this->defaultApplicationType;
        }

        $this->applicationTypeNormalized=$this->applicationType;

        if ($this->aNumber) {
            $NormalizedNumber        = $this->CDRS->NormalizeNumber($this->aNumber,"source");
            $this->aNumberPrint      = $NormalizedNumber[NumberPrint];
            $this->aNumberNormalized = $NormalizedNumber[Normalized];
            $this->aNumberDomain     = $NormalizedNumber['domain'];
        }

        if ($this->domain) {
             if (is_array($this->CDRS->DATASOURCES[$this->cdr_source]['domainTranslation'])
                 && in_array($this->domain,array_keys($this->CDRS->DATASOURCES[$this->cdr_source]['domainTranslation']))
                 && strlen($this->CDRS->DATASOURCES[$this->cdr_source]['domainTranslation'][$this->domain])) {
                 $this->domainNormalized=$this->CDRS->DATASOURCES[$this->cdr_source]['domainTranslation'][$this->domain];
             } else if (is_array($this->CDRS->DATASOURCES[$this->cdr_source]['SourceIPRealmTranslation'])
                 && in_array($this->SourceIP,array_keys($this->CDRS->DATASOURCES[$this->cdr_source]['SourceIPRealmTranslation']))
                 && strlen($this->CDRS->DATASOURCES[$this->cdr_source]['SourceIPRealmTranslation'][$this->SourceIP])) {
                 $this->domainNormalized=$this->CDRS->DATASOURCES[$this->cdr_source]['SourceIPRealmTranslation'][$this->SourceIP];
             } else {
                 $this->domainNormalized=$this->domain;
             }
     
             $this->domainNormalized=strtolower($this->domainNormalized);
        }

        if (!$this->BillingPartyId || $this->BillingPartyId == 'n/a') {
            $this->BillingPartyId=$this->aNumberPrint;
        }

        $this->BillingPartyId=strtolower($this->BillingPartyId);

        $this->BillingPartyIdPrint=$this->BillingPartyId;

        $this->RemoteAddressPrint=$this->RemoteAddress;

        $this->SipRPIDPrint=$this->SipRPID;

        if (!$this->domain) {
            $_els=explode("@",$this->BillingPartyId);
            if (count($_els)==2) $this->domain=$_els[1];
        }

        $_timestamp_stop=$this->timestamp+$this->duration;

        $this->dayofweek       = date("w",$this->timestamp);
        $this->hourofday       = date("G",$this->timestamp);
        $this->dayofyear       = date("Y-m-d",$this->timestamp);

        // Called Station ID or cNumber should not be used for rating purposes because
        // it is chosen by the subscriber but the Proxy rewrites it into a different
        // final destination (the Canonical URI)

        // Canonical URI is the final logical SIP destination after all
        //   lookups like aliases, usrloc , call forwarding, ENUM
        //   mappings or PSTN gateways but before the DNS lookup
        //   Canonical URI must be saved in the SIP Proxy and added as an extra
        //   Radius attribute in the Radius START packet

        if (!$this->CanonicalURI) {
            if ($this->RemoteAddress) {
                $this->CanonicalURI=$this->RemoteAddress;
            } else if ($this->cNumber) {
                $this->CanonicalURI=$this->cNumber;
            }
        }

        if ($this->CanonicalURI) {
            $this->CanonicalURIPrint=$this->CanonicalURI;
    
            $NormalizedNumber                   = $this->CDRS->NormalizeNumber($this->CanonicalURI,"destination",$this->BillingPartyId,$this->domain,$this->gateway,'',$this->ENUMtld);
            $this->CanonicalURINormalized       = $NormalizedNumber['Normalized'];
            $this->CanonicalURIUsername         = $NormalizedNumber['username'];
            $this->CanonicalURIDomain           = $NormalizedNumber['domain'];
            $this->CanonicalURIPrint            = $NormalizedNumber['NumberPrint'];
            $this->CanonicalURIDelimiter        = $NormalizedNumber['delimiter'];

            // Destination Id is used for rating purposes
            $this->DestinationId                = $NormalizedNumber['DestinationId'];
            $this->destinationName              = $NormalizedNumber['destinationName'];
        }

        if ($this->cNumber) {
            $NormalizedNumber                   = $this->CDRS->NormalizeNumber($this->cNumber,"destination",$this->BillingPartyId,$this->domain,$this->gateway,'',$this->ENUMtld);
            $this->cNumberNormalized            = $NormalizedNumber['Normalized'];
            $this->cNumberUsername              = $NormalizedNumber['username'];
            $this->cNumberDomain                = $NormalizedNumber['domain'];
            $this->cNumberPrint                  = $NormalizedNumber['username'].$NormalizedNumber['delimiter'].$NormalizedNumber['domain'];
            $this->cNumberDelimiter                = $NormalizedNumber['delimiter'];
        }

        if ($this->RemoteAddress) {
            // Next hop is the real destination after all lookups including DNS
            $NormalizedNumber                   = $this->CDRS->NormalizeNumber($this->RemoteAddress,"destination",$this->BillingPartyId,$this->domain,$this->gateway,'',$this->ENUMtld);
            $this->RemoteAddressPrint           = $NormalizedNumber['NumberPrint'];
            $this->RemoteAddressNormalized      = $NormalizedNumber['Normalized'];
            $this->RemoteAddressDestinationId   = $NormalizedNumber['DestinationId'];
            $this->RemoteAddressDestinationName = $NormalizedNumber['destinationName'];
            $this->RemoteAddressUsername        = $NormalizedNumber['username'];
            $this->RemoteAddressDelimiter       = $NormalizedNumber['delimiter'];
    
            $this->remoteGateway                = $NormalizedNumber['domain'];
            $this->remoteUsername                = $NormalizedNumber['username'];
    
        }

        if ($this->applicationType=="presence") {

            $this->destinationPrint = $this->cNumberUsername.$this->cNumberDelimiter.$this->cNumberDomain;
            $this->DestinationForRating = $this->cNumberNormalized;

        } else {
            if (!$this->DestinationId) {
                if ($this->CanonicalURIDomain) {
                    $this->destinationPrint = $this->CanonicalURIUsername.$this->CanonicalURIDelimiter.$this->CanonicalURIDomain;
                } else {
                    $this->destinationPrint = $this->cNumberUsername.$this->cNumberDelimiter.$this->cNumberDomain;
                }

                if (strstr($this->CanonicalURINormalized,'@')) {
                    $this->DestinationForRating = $this->CanonicalURINormalized;
                } else {
                    $this->DestinationForRating = $this->RemoteAddressNormalized;
                }

            } else {
                $this->DestinationForRating = $this->CanonicalURINormalized;
                $this->destinationPrint = $this->CanonicalURIPrint;
            }
        }

        if ($this->inputTraffic) {
            $this->inputTrafficPrint  = number_format($this->inputTraffic/1024,2);
        }

        if ($this->outputTraffic) {
            $this->outputTrafficPrint = number_format($this->outputTraffic/1024,2);
        }

        $this->durationPrint      = sec2hms($this->duration);

        if ($this->disconnect) {
            $this->disconnectPrint    = $this->NormalizeDisconnect($this->disconnect);
        }

        $this->NetworkRateDictionary=array(
                                  'callId'          => $this->callId,
                                  'Timestamp'       => $this->timestamp,
                                  'Duration'        => $this->duration,
                                  'inputTraffic'    => $this->inputTraffic,
                                  'outputTraffic'   => $this->outputTraffic,
                                  'DestinationId'   => $this->DestinationId,
                                   'From'            => $this->BillingPartyId,
                                  'To'              => $this->DestinationForRating,
                                  'Domain'          => $this->domain,
                                  'Gateway'         => $this->gateway,
                                  'Application'     => $this->applicationType,
                                  'ENUMtld'         => $this->ENUMtld
                                  );

        $this->traceIn();
        $this->traceOut();

        $this->obfuscateCallerId();

        //$this->isCalleeLocal();
        $this->isCallerLocal();

    }

    function traceIn () {
        $datasource=$this->CDRS->traceInURL[$this->SourceIP];
        global $DATASOURCES;

        if (!$datasource || !$DATASOURCES[$datasource]) {
            return;
        }

        $tplus     = $this->timestamp+$this->duration+300;
        $tmin      = $this->timestamp-300;
        $c_number  = $this->remoteUsername;
        $cdr_table = Date('Ym',time($this->timestamp));

        $this->traceIn=
                        "<a href=callsearch.phtml".
                        "?cdr_source=$datasource".
                        "&cdr_table=$cdr_table".
                        "&trace=1".
                        "&action=search".
                        "&c_number=$c_number".
                        "&c_number_comp=begins".
                        "&begin_datetime=$tmin".
                        "&end_datetime=$tplus".
                        " target=bottom>".
                        "In".
                        "</a>";
    }

    function traceOut () {
        $datasource=$this->CDRS->traceOutURL[$this->remoteGateway];
        global $DATASOURCES;

        if (!$datasource || !$DATASOURCES[$datasource]) {
            return;
        }

        $tplus     = $this->timestamp+$this->duration+300;
        $tmin      = $this->timestamp-300;
        $c_number  = preg_replace("/^(0+)/","",$this->remoteUsername);
        $cdr_table = Date('Ym',time($this->timestamp));

        $this->traceOut=
                        "<a href=callsearch.phtml".
                        "?cdr_source=$datasource".
                        "&cdr_table=$cdr_table".
                        "&trace=1".
                        "&action=search".
                        "&c_number=$c_number".
                        "&c_number_comp=contain".
                        "&begin_datetime=$tmin".
                        "&end_datetime=$tplus".
                        " target=bottom>".
                        "Out".
                        "</a>";
    }

    function show() {
        global $found;
        global $perm;

        $rr=floor($found/2);
        $mod=$found-$rr*2;

        if ($mod ==0) {
            $inout_color="lightgrey";
        } else {
            $inout_color="white";
        }

        $this->ratePrint=nl2br($this->rate);

        if ($this->CDRS->Accounts[$this->BillingPartyId]['timezone']) {
            $timezone_print=$this->CDRS->Accounts[$this->BillingPartyId]['timezone'];
        } else {
            $timezone_print=$this->CDRS->CDRTool['provider']['timezone'];
        }

        $found_print=$found;

        if ($this->normalized) $found_print.='N';

        $providerTimezone=$this->CDRS->CDRTool['provider']['timezone'];

        $CallInfoVerbose="
        <table border=0 bgcolor=#CCDDFF class=extrainfo id=row$found cellpadding=0 cellspacing=0>
        <tr>
        <td valign=top>
        <table border=0 cellpadding=0 cellspacing=0>
        <tr>
            <td colspan=3><b>Signalling information</b></td>
        </tr>
        ";

        $CallInfoVerbose.= sprintf("
        <tr>
            <td width=10></td>
            <td colspan=2><a href=%s&call_id=%s><font color=orange>Click here to show only this call id</font></a></td>
        </tr>
        ",
        $this->CDRS->url_run,
        urlencode($this->callId)
        );

        if ($this->CDRS->sipTraceDataSource) {
            $trace_datasource = $this->CDRS->sipTraceDataSource;
            $callid_enc       = urlencode($this->callId);
            $fromtag_enc      = urlencode($this->SipFromTag);
            $totag_enc        = urlencode($this->SipToTag);

            $this->traceLink="<a href=\"javascript:void(null);\" onClick=\"return window.open('sip_trace.phtml?cdr_source=$trace_datasource&callid=$callid_enc&fromtag=$fromtag_enc&totag=$totag_enc&proxyIP=$this->SipProxyServer', 'Trace',
            'toolbar=0,status=0,menubar=0,scrollbars=1,resizable=1,width=1000,height=600')\"><font color=red>Click here to see the SIP trace for this call</font></a> &nbsp;";

            $CallInfoVerbose.= "
            <tr>
                <td width=10></td>
                <td>Call id: </td>
                <td>$this->callId</td>
            </tr>
            ";
        }

        $CallInfoVerbose.= sprintf("
        <tr>
            <td width=10></td>
            <td colspan=2>%s</td>
        </tr>
        ", $this->traceLink);

        $CallInfoVerbose.= "
        <tr>
            <td width=10></td>
            <td>From/to tags: </td>
            <td>$this->SipFromTag/$this->SipToTag</td>
        </tr>
        <tr>
            <td></td>
            <td>Start time: </td>
            <td>$this->startTime $providerTimezone</td>
        </tr>
        ";
        if ($this->normalized) {
            $CallInfoVerbose.= "
            <tr>
                <td></td>
                <td>Stop time: </td>
                <td>$this->stopTime $providerTimezone</td>
            </tr>
            ";
        }

        $CallInfoVerbose.= "
        <tr>
            <td></td>
            <td>Method:</td>
            <td>$this->SipMethod from <i>$this->SourceIP:$this->SourcePort</i>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>From:</td>
            <td>$this->aNumberPrint</td>
        </tr>
        <tr>
            <td></td>
            <td>Domain:</td>
            <td>$this->domain</td>
        </tr>
        <tr>
            <td></td>
            <td>To (dialed URI):</td>
            <td>$this->cNumberPrint</td>
        </tr>
        ";

        if ($this->CanonicalURI) {
        $CallInfoVerbose.= sprintf("
        <tr>
            <td></td>
            <td>Canonical URI:   </td>
            <td>%s</td>
        </tr>
        ",htmlentities($this->CanonicalURI));
        }

        $CallInfoVerbose.= sprintf("
        <tr>
            <td></td>
            <td>Next hop URI:</td>
            <td>%s</td>
        </tr>
        ",htmlentities($this->RemoteAddress));

        if ($this->DestinationId) {
            $CallInfoVerbose.= "
            <tr>
                <td></td>
                <td>Destination: </td>
                <td>$this->destinationName ($this->DestinationId)</td>
            </tr>
            ";
        }

        if ($this->ENUMtld && $this->ENUMtld != 'none' && $this->ENUMtld != 'N/A') {
            $CallInfoVerbose.= "
            <tr>
                <td></td>
                <td>ENUM TLD: </td>
                <td>$this->ENUMtld</td>
            </tr>
            ";
        }

        if ($this->SipRPID && $this->SipRPID!='n/a') {
            $CallInfoVerbose .= "
            <tr>
            <td></td>
            <td>Caller ID:  </td>
            <td>$this->SipRPIDPrint</td>
            </tr>
            ";
        }

        $CallInfoVerbose.= "
        <tr>
            <td></td>
            <td>Billing Party:</td>
            <td><font color=brown>$this->BillingPartyIdPrint</font></td>
        </tr>
        </table>
        </td>
        <td width=30>
        <td valign=top>
        ";


        if ($this->SipCodec) {
            $this->SipCodec   = quoted_printable_decode($this->SipCodec);

            $CallInfoVerbose.= "
            <table border=0 cellpadding=0 cellspacing=0>

            <tr>
                <td colspan=3><b>Media information</b></td>
            </tr>
            ";
            if ($this->CDRS->mediaTraceDataSource) {
                $media_trace_datasource = $this->CDRS->mediaTraceDataSource;

                $this->mediaTraceLink="<a href=\"javascript:void(null);\" onClick=\"return window.open('media_trace.phtml?cdr_source=$media_trace_datasource&callid=$callid_enc&fromtag=$fromtag_enc&totag=$totag_enc&proxyIP=$this->SipProxyServer', 'Trace',
                'toolbar=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=730')\">Click here to see the media information for this call</a> &nbsp;";

                $CallInfoVerbose.= sprintf("
                <tr>
                    <td width=10></td>
                    <td colspan=2>%s</td>
                </tr>
                ", $this->mediaTraceLink);

            }

            $CallInfoVerbose.= "
            <tr>
                <td></td>
                <td>Application: </td>
                <td>$this->applicationType</td>
            </tr>
            <tr>
                <td></td>
                <td>Codecs: </td>
                <td>$this->SipCodec</td>
            </tr>
            <tr>
                <td></td>
                <td>Caller RTP: </td>
                <td>$this->inputTrafficPrint KB</td>
            </tr>
            <tr>
                <td></td>
                <td>Called RTP: </td>
                <td>$this->outputTrafficPrint KB</td>
            </tr>
            ";

            if ($this->MediaTimeout) {
                $CallInfoVerbose.= "
                <tr>
                <td></td>
                <td>Media info:</td>
                <td><font color=red>$this->MediaTimeout</font></td>
                </tr>
                ";
            }

            if ($this->SipUserAgents) {
                $this->SipUserAgents   = quoted_printable_decode($this->SipUserAgents);

                $callerAgents=explode("+",$this->SipUserAgents);
                $callerUA=htmlentities($callerAgents[0]);
                $calledUA=htmlentities($callerAgents[1]);

                $CallInfoVerbose.= "
                <tr>
                    <td></td>
                    <td>Caller SIP UA: </td>
                    <td>$callerUA</td>
                </tr>
                <tr>
                    <td></td>
                    <td>Called SIP UA: </td>
                    <td>$calledUA</td>
                </tr>
                ";
            }

            if (is_array($this->QoS)) {
                foreach (array_keys($this->QoS) as $_key) {
                    if ($this->QoSParameters[$_key]) {
                        $_desc=$this->QoSParameters[$_key];
                    } else {
                        $_desc=$_key;
                    }
                    $CallInfoVerbose.=
                    sprintf ("<tr><td></td><td>%s</td><td>%s</td></tr>\n",
                    $_desc,$this->QoS[$_key]);
                }
            }

            $CallInfoVerbose.= "
            </table>";
        }

        $CallInfoVerbose.=  "
        </td>
        <td width=30></td>
        <td valign=top>
        ";

        if ($perm->have_perm("showPrice") && $this->normalized) {
            $CallInfoVerbose.= "
            <table border=0 cellpadding=0 cellspacing=0>
    
                <tr>
                    <td colspan=3><b>Rating information</b></td>
    
                </tr>
            ";

            if ($this->price > 0 || $this->rate) {
                $CallInfoVerbose.= "
                <tr>
                <td></td>
                <td colspan=2>$this->ratePrint</td>
                </tr>
                ";

            } else {
                $CallInfoVerbose.= "
                <tr>
                <td></td>
                <td colspan=2>Free call</td>
                </tr>
                ";
            }

            $CallInfoVerbose.= "
            </table>
            ";
        }

        $CallInfoVerbose.=  "
        </td>
        </tr>
        </table>";

        print "
        <tr bgcolor=$inout_color>
        <td valign=top onClick=\"return toggleVisibility('row$found')\"><a href=#>$found_print</a></td>
        <td valign=top onClick=\"return toggleVisibility('row$found')\"><nobr>$this->startTime</nobr></td>
        <td valign=top onClick=\"return toggleVisibility('row$found')\">$this->SipProxyServer</td>
        <td valign=top onClick=\"return toggleVisibility('row$found')\"><nobr>$this->aNumberPrint</td>
        <td valign=top>$this->traceIn</td>
        <td valign=top><nobr>$this->destinationPrint</nobr>";

        if ($this->DestinationId) {
            if ($this->DestinationId != $this->CanonicalURI) {
                print " ($this->destinationName $this->DestinationId)";
            } else {
                print " ($this->destinationName)";
            }
        }

        print "</td>";
        print "<td valign=top>$this->traceOut</td>";

        if (!$this->normalized){
            print "<td valign=top align=left colspan=4><font color=red>in progress</a></td>";
        } else {
            print "<td valign=top align=right>$this->durationPrint</td>";
            if ($this->CDRS->rating) {
                if ($this->price == "0.0000" && !$this->rate) {
                    $this->pricePrint="";
                } else {
                    if ($perm->have_perm("showPrice")) {
                        $this->pricePrint=$this->price;
                    } else {
                        $this->pricePrint='x.xxx';
                    }
                }
    
                print "<td valign=top align=right>$this->pricePrint</td>";
            }

            print "
            <td valign=top align=right>$this->inputTrafficPrint </td>
            <td valign=top align=right>$this->outputTrafficPrint</td>
            ";
        }

        $SIPclass=substr($this->disconnect,0,1);

        if ($SIPclass=="6") {
            $status_color="<font color=red>";
        } else if ($SIPclass=="5" ) {
            $status_color="<font color=red>";
        } else if ($SIPclass=="4" ) {
            $status_color="<font color=blue>";
        } else if ($SIPclass=="3" ) {
            $status_color="<font color=green>";
        } else if ($SIPclass=="2" ) {
            $status_color="<font color=green>";
        } else  {
            $status_color="<font color=black>";
        }

        print "
        <td valign=top align=right>$status_color $this->disconnectPrint</font></td>
        <td valign=top align=right>$this->SipCodec</td>
        </tr>
        <tr>
        <td></td>
        <td colspan=11>$CallInfoVerbose</td>
        </tr>

        ";
    }

    function export() {
        global $found;

        $disconnectName   = $this->CDRS->disconnectCodesDescription[$this->disconnect];
        $UserAgents       = explode("+",$this->SipUserAgents);
        $CallingUserAgent = trim($UserAgents[0]);
        $CalledUserAgent  = trim($UserAgents[1]);

        print "$found";
        print ",$this->startTime";
        print ",$this->stopTime";
        print ",$this->BillingPartyIdPrint";
        print ",$this->domain";
        print ",$this->SipRPIDPrint";
        print ",$this->aNumberPrint";
        print ",$this->destinationPrint";
        print ",$this->DestinationId";
        print ",$this->destinationName";
        print ",$this->RemoteAddressPrint";
        print ",$this->CanonicalURIPrint";
        print ",$this->duration";
        print ",$this->price";
        print ",$this->SipProxyServer";
        print ",$this->inputTraffic";
        print ",$this->outputTraffic";
        print ",$CallingUserAgent";
        print ",$CalledUserAgent";
        print ",$this->disconnect";
        print ",$disconnectName";
        print ",$this->SipCodec";
        print ",$this->applicationType";
        print "\n";
    }

    function showSubscriber() {
        global $found;

        $rr=floor($found/2);
        $mod=$found-$rr*2;

        if ($mod ==0) {
            $inout_color="lightgrey";
        } else {
            $inout_color="white";
        }

        if (!$this->CDRS->export) {
            $timezone_print=$this->CDRS->CDRTool['provider']['timezone'];

            print "
            <tr bgcolor=$inout_color>
            <td valign=top>$found</td>
            <td valign=top>$this->startTime $timezone_print</td>
            <td valign=top><nobr>$this->aNumberPrint</nobr></td>
            <td valign=top><nobr>$this->cNumberPrint</nobr></td>
            <td valign=top><nobr>$this->destinationPrint $this->destinationName</td>
            <td valign=top align=right>$this->durationPrint</td>
            ";
            if ($this->CDRS->rating) print "<td valign=top align=right>$this->price</td>";
            print "
            </tr>
            ";
        } else {
            $disconnectName=$this->CDRS->disconnectCodesDescription[$this->disconnect];
            $UserAgents=explode("+",$this->SipUserAgents);
            $CallingUserAgent=trim($UserAgents[0]);
            $CalledUserAgent=trim($UserAgents[1]);
            print "$found,$this->startTime,$this->stopTime,$this->BillingPartyId,$this->domain,$this->aNumberPrint,$this->cNumberPrint,$this->DestinationId,$this->destinationName,$this->RemoteAddressPrint,$this->duration,$this->price,$this->SipProxyServer,$this->inputTraffic,$this->outputTraffic,$CallingUserAgent,$CalledUserAgent,$this->disconnect,$disconnectName,$this->SipCodec,$this->applicationType\n";
        }
    }

    function isCallerLocal() {
        // used by quota
        if (in_array($this->aNumberDomain,$this->CDRS->localDomains)) {
            $this->CallerIsLocal=1;
            return true;
        }
        return false;
    }

    function isCalleeLocal() {
        if ($this->CanonicalURIUsername == $this->RemoteAddressUsername &&
            in_array($this->CanonicalURIDomain,$this->CDRS->localDomains) &&
            !preg_match("/^0/",$this->CanonicalURIUsername)) {
            $this->CalleeIsLocal=1;
        }
    }

    function obfuscateCallerId() {
        global $obfuscateCallerId;
        if ($obfuscateCallerId) {

            //Caller party
            $caller_els=explode("@",$this->aNumberPrint);
    
            if (is_numeric($caller_els[0]) && strlen($caller_els[0]>3)) {
                $_user=substr($caller_els[0],0,strlen($caller_els[0])-3).'xxx';
            } else {
                $_user='caller';
            }

            if (count($caller_els)== 2) {
                $this->aNumberPrint=$_user.'@'.$caller_els[1];
            } else {
                $this->aNumberPrint=$_user;
            }

            //Billing party
            $caller_els=explode("@",$this->BillingPartyIdPrint);
    
            if (is_numeric($caller_els[0]) && strlen($caller_els[0]>3)) {
                $_user=substr($caller_els[0],0,strlen($caller_els[0])-3).'xxx';
            } else {
                $_user='party';
            }
    
            $this->BillingPartyIdPrint=$_user.'@'.$caller_els[1];

            // Destination
            $caller_els=explode("@",$this->destinationPrint);
    
            if (is_numeric($caller_els[0]) && strlen($caller_els[0]>3)) {
                $_user=substr($caller_els[0],0,strlen($caller_els[0])-3).'xxx';
            } else {
                $_user='destination';
            }
    
            if (count($caller_els)== 2) {
                $this->destinationPrint=$_user.'@'.$caller_els[1];
            } else {
                $this->destinationPrint=$_user;
            }

            $caller_els=explode("@",$this->cNumberPrint);
    
            if (is_numeric($caller_els[0]) && strlen($caller_els[0]>3)) {
                $_user=substr($caller_els[0],0,strlen($caller_els[0])-3).'xxx';
            } else {
                $_user='dialedNumber';
            }
    
            if (count($caller_els)== 2) {
                $this->cNumberPrint=$_user.'@'.$caller_els[1];
            } else {
                $this->cNumberPrint=$_user;
            }

            $caller_els=explode("@",$this->RemoteAddressPrint);
    
            if (is_numeric($caller_els[0]) && strlen($caller_els[0]>3)) {
                $_user=substr($caller_els[0],0,strlen($caller_els[0])-3).'xxx';
            } else {
                $_user='remoteAddress';
            }
    
            if (count($caller_els)== 2) {
                $this->RemoteAddressPrint=$_user.'@'.$caller_els[1];
            } else {
                $this->RemoteAddressPrint=$_user;
            }

            // Canonical URI
            $caller_els=explode("@",$this->CanonicalURIPrint);
    
            if (is_numeric($caller_els[0]) && strlen($caller_els[0]>3)) {
                $_user=substr($caller_els[0],0,strlen($caller_els[0])-3).'xxx';
            } else {
                $_user='canonicalURI';
            }

            if (count($caller_els)== 2) {
                $this->CanonicalURIPrint=$_user.'@'.$caller_els[1];
            } else {
                $this->CanonicalURIPrint=$_user;
            }

            if (is_numeric($this->SipRPIDPrint) && strlen($this->SipRPIDPrint) > 3) {
                $this->SipRPIDPrint=substr($this->SipRPID,0,strlen($this->SipRPID)-3).'xxx';
            } else {
                $_user='callerId';
            }

            // IP address
            $this->SourceIP='xxx.xxx.xxx.xxx';
        }
     }
}

class SIP_trace {
    var $enableThor  = false;
    var $trace_array = array();
    var $traced_ip   = array();
    var $SIPProxies  = array();
    var $mediaTraceDataSource  = false;

    function SIP_trace ($cdr_source) {
        global $DATASOURCES, $auth;
        $this->cdr_source = $cdr_source;

        $this->cdrtool  = new DB_CDRTool();

        if (strlen($DATASOURCES[$this->cdr_source]['enableThor'])) {
            $this->enableThor = $DATASOURCES[$this->cdr_source]['enableThor'];
        }

        if (strlen($DATASOURCES[$this->cdr_source]['mediaTraceDataSource'])) {
            $this->mediaTraceDataSource = $DATASOURCES[$this->cdr_source]['mediaTraceDataSource'];
        }

        if ($this->enableThor) {
	    	require("/etc/cdrtool/ngnpro_engines.inc");

            if ($DATASOURCES[$this->cdr_source]['soapEngineId'] && in_array($DATASOURCES[$this->cdr_source]['soapEngineId'],array_keys($soapEngines))) {
                $this->soapEngineId=$DATASOURCES[$this->cdr_source]['soapEngineId'];
                require_once("ngnpro_soap_library.php");

                $this->SOAPlogin = array(
                                       "username"    => $soapEngines[$this->soapEngineId]['username'],
                                       "password"    => $soapEngines[$this->soapEngineId]['password'],
                                       "admin"       => true
                                       );
    
                $this->SOAPurl=$soapEngines[$this->soapEngineId]['url'];
        
                $this->SoapAuth = array('auth', $this->SOAPlogin , 'urn:AGProjects:NGNPro', 0, '');
    
                // Instantiate the SOAP client
                $this->soapclient = new WebService_NGNPro_SipPort($this->SOAPurl);
    
                $this->soapclient->setOpt('curl', CURLOPT_TIMEOUT,        5);
                $this->soapclient->setOpt('curl', CURLOPT_SSL_VERIFYPEER, 0);
                $this->soapclient->setOpt('curl', CURLOPT_SSL_VERIFYHOST, 0);
    
            } else {
                print "Error: soapEngineID not defined in datasource $this->cdr_source";
                return false;
            }

        } else {
            $this->table             = $DATASOURCES[$this->cdr_source]['table'];
            $db_class                = $DATASOURCES[$this->cdr_source]['db_class'];
            $this->purgeRecordsAfter = $DATASOURCES[$this->cdr_source]['purgeRecordsAfter'];
            $this->db                = new $db_class;
        }

        if (is_object($auth)) $this->isAuthorized=1;

        if (is_array($DATASOURCES[$this->cdr_source]['SIPProxies'])) {
            $this->SIPProxies=$DATASOURCES[$this->cdr_source]['SIPProxies'];
        }

    }

    function isProxy($ip,$sip_proxy='') {
        if (!$ip) return false;
        if (!$this->enableThor) {
            if (!is_array($this->SIPProxies)) {
                return false;
            }
    
            if (in_array($ip,array_keys($this->SIPProxies))) {
                return true;
            }
        } else if ($sip_proxy) {
            return isThorNode($ip,$sip_proxy);
        }

        return false;
    }

    function getTrace ($proxyIP,$callid,$fromtag,$totag) {

        if ($this->enableThor) {
            $this->seen_ip=array();
            $this->trace_array=$this->getTraceFromThor($proxyIP,$callid,$fromtag,$totag);

            $this->rows = count($this->trace_array);

        } else {
            // get trace from SQL

            $query=sprintf("select *,UNIX_TIMESTAMP(date) as timestamp
            from %s where callid = '%s' order by id asc",
            $this->table,
            $callid);
    
    
            if (!$this->db->query($query)) {
                printf ("<p>Error: database error %s %s",$this->db->Error,$query);
                return false;
            }
    
            $this->rows = $this->db->num_rows();
    
            $columns = 0;
    
            while ($this->db->next_record()) {
    
                if (preg_match("/^(udp|tcp|tls):(.*):(.*)$/",$this->db->f('toip'),$m)) {
                    $toip      = $m[2];
                    $transport = $m[1];
                    $toport    = $m[3];
                } else if (preg_match("/^(.*):(.*)$/",$this->db->f('toip'),$m)) {
                    $toip      = $m[1];
                    $transport = 'udp';
                    $toport    = $m[2];
                } else {
                    $toip = $this->db->f('toip');
                    $toport    = '5060';
                }

                if (preg_match("/^(udp|tcp|tls):(.*):(.*)$/",$this->db->f('fromip'),$m)) {
                    $fromip   = $m[2];
                    $fromport = $m[3];
                } else if (preg_match("/^(.*):(.*)$/",$this->db->f('fromip'),$m)) {
                    $fromip   = $m[1];
                    $fromport = $m[2];
                } else {
                    $fromip = $this->db->f('fromip');
                    $transport = 'udp';
                    $fromport = '5060';
                }

                if (!$this->seen_ip[$toip] && $this->isProxy($toip)) {
                    $this->seen_ip[$toip]++;
                }
    
                if (!$this->seen_ip[$fromip] && $this->isProxy($fromip)) {
                    $this->seen_ip[$fromip]++;
                }

                if (!$this->column[$fromip]) {
                    $this->column[$fromip]=$columns+1;
                    $this->column_port[$fromip]=$fromport;
                    $columns++;
                }
    
                if (!$this->column[$toip]) {
                    $this->column[$toip]=$columns+1;
                    $this->column_port[$toip]=$toport;
                    $columns++;
                }
    
                $this->trace_array[$this->db->f('id')]=
                            array (
                                   'id'        => $this->db->f('id'),
                                   'direction' => $this->db->f('direction'),
                                   'fromip'    => $fromip,
                                   'toip'      => $toip,
                                   'method'    => $this->db->f('method'),
                                   'fromport'  => $fromport,
                                   'toport'    => $toport,
                                   'transport' => $transport,
                                   'date'      => $this->db->f('date'),
                                   'status'    => $this->db->f('status'),
                                   'timestamp' => $this->db->f('timestamp'),
                                   'msg'       => $this->db->f('msg'),
                                   'md5'       => md5($this->db->f('msg'))
                                   );
            }
        }

    }

    function getTraceFromThor($proxyIP,$callid,$fromtag,$totag) {
        // get trace using soap request
        if (!$proxyIP || !$callid || !$fromtag) return false;

        if (!is_object($this->soapclient)) {
            print "Error: soap client is not defined.";
            return false;
        }

        if ($this->traced_ip[$proxyIP]) return true;

        $this->traced_ip[$proxyIP]++;

        $filter=array('nodeIp'  => $proxyIP,
                      'callId'  => $callid,
                      'fromTag' => $fromtag,
                      'toTag'   => $totag
                      );
        $this->soapclient->addHeader($this->SoapAuth);

        $result     = $this->soapclient->getSipTrace($filter);

        if (PEAR::isError($result)) {
            $error_msg   = $result->getMessage();
            $error_fault = $result->getFault();
            $error_code  = $result->getCode();

            printf("<font color=red>Error from %s: %s: %s</font>",$this->SOAPurl,$error_fault->faultstring,$error_fault->faultcode);
            return false;
        }

        /*
        print "<pre>";
        print_r($result);
        print "</pre>";
        */

        $columns=0;

        $trace_array=array();

        foreach ($result as $_trace) {

            if (preg_match("/^(udp|tcp|tls):(.*):(.*)$/",$_trace->toIp,$m)) {
                $toip      = $m[2];
                $transport = $m[1];
                $toport    = $m[3];
            } else if (preg_match("/^(.*):(.*)$/",$_trace->toIp,$m)) {
                $toip      = $m[1];
                $transport = 'udp';
                $toport    = $m[2];
            } else {
                $toip      = $_trace->toIp;
                $transport = 'udp';
                $toport    = '5060';
            }

            if (preg_match("/^(udp|tcp|tls):(.*):(.*)$/",$_trace->fromIp,$m)) {
                $fromip    = $m[2];
                $fromport  = $m[3];
            } else if (preg_match("/^(.*):(.*)$/",$_trace->fromIp,$m)) {
                $fromip    = $m[1];
                $fromport  = $m[2];
            } else {
                $fromip    = $_trace->fromIp;
            }

            if (!$this->seen_ip[$toip] && $this->isProxy($toip)) {
                $this->seen_ip[$toip]++;
            }

            if (!$this->seen_ip[$fromip] && $this->isProxy($fromip)) {
                $this->seen_ip[$fromip]++;
            }

            if (!$this->column[$fromip]) {
                $this->column[$fromip] = $columns+1;
                $this->column_port[$fromip]=$fromport;
                $columns++;
            }

            if (!$this->column[$toip]) {
                $this->column[$toip]   = $columns+1;
                $this->column_port[$toip]=$toport;
                $columns++;
            }

            preg_match("/^(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)$/",$_trace->date,$m);
            $timestamp = mktime($m[4],$m[5],$m[6],$m[2],$m[3],$m[1]);

            $idx=$proxyIP.'_'.$_trace->id;

            $trace_array[$idx]=
                        array (
                               'id'         => $idx,
                               'direction'  => $_trace->direction,
                               'fromip'     => $fromip,
                               'toip'       => $toip,
                               'fromport'   => $fromport,
                               'toport'     => $toport,
                               'method'     => $_trace->method,
                               'transport'  => $transport,
                               'date'       => $_trace->date,
                               'status'     => $_trace->status,
                               'timestamp'  => $timestamp,
                               'msg'        => $_trace->msg,
                               'md5'        => md5($_trace->msg)
                               );
        }

        return $trace_array;

    }


    function show($proxyIP,$callid,$fromtag,$totag) {

        $action           = $_REQUEST['action'];
        $toggleVisibility = $_REQUEST['toggleVisibility'];

        if ($action=='toggleVisibility') {
            $this->togglePublicVisibility($callid,$fromtag,$toggleVisibility);
        }

        if ($_SERVER['HTTPS'] == "on") {
            $protocolURL = "https://";
        } else {
            $protocolURL = "http://";
        }

        $this->getTrace($proxyIP,$callid,$fromtag,$totag);

        if (!count($this->trace_array)) {
            print "<p>
            SIP trace for session id $callid is not available.";
            return;
        }

        print "
        <h1>CDRTool trace</h1>
        <h2>SIP session $callid $authorize</h2>

        <table border=0 width=100%>
        <tr>
        <form method=post name=visibility>
        ";
        if ($this->isAuthorized) {
            $key="callid-".trim($callid).trim($fromtag);

            $query=sprintf("select * from memcache where `key` = '%s'",addslashes($key));
            $this->cdrtool->query($query);

            $basicURL = $protocolURL.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

            if ($this->cdrtool->num_rows()) {
                $selected_toggleVisibility['public']="selected";
                $fullURL=$basicURL."&public=1";
                $color2="lightblue";
            } else {
                $selected_toggleVisibility['private']="selected";
                $fullURL=$basicURL;
            }

            print "
            <td align=left width=50% bgcolor=$color2>

            Trace is visible

            <select name=toggleVisibility onChange=\"document.visibility.submit.disabled = true; location.href = '$basicURL&action=toggleVisibility&toggleVisibility=' + this.options[this.selectedIndex].value\">
            <option $selected_toggleVisibility[public] value=1>without authorization
            <option $selected_toggleVisibility[private] value=0>only for authorized users
            </select>
            <input type=hidden name=action value=toggleVisibility>
            ";
            print "<a href=$fullURL>URL for this trace</a></td>";
        } else {
            print "<td align=left width=50%>";
        }

        if ($this->mediaTraceDataSource) {
            $this->mediaTraceLink=sprintf("<p><a href=\"javascript:void(null);\" onClick=\"return window.open('media_trace.phtml?cdr_source=%s&callid=%s&fromtag=%s&totag=%s&proxyIP=%s', 'mediatrace',
            'toolbar=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=730')\">Click here for the media trace</a>",
            $this->mediaTraceDataSource,
            urlencode($callid),
            urlencode($fromtag),
            urlencode($totag),
            $proxyIP
            );
        }

        print "</td>
        </form>
        <td align=right width=50%>
        Click on each trace entry to see the full message.
        $this->mediaTraceLink

        </td>
        </tr>
        </table>
        ";

        foreach (array_keys($this->trace_array) as $key) {

            if ($this->trace_array[$key]['direction'] == 'in') {

                if (is_array($this->SIPProxies)) {
                    $thisIP=explode(":",$this->trace_array[$key]['fromip']);
                    if ($this->isProxy($thisIP[0],$proxyIP)) {
                        $this->trace_array[$key]['isProxy'] = 1;
                    }
                }

                if ($this->trace_array[$key]['fromip'] == $this->trace_array[$key]['toip']) {
                    $this->trace_array[$key]['arrow_align'] = "left";
                    $arrow_direction="loop";
                } else if ($this->column[$this->trace_array[$key]['fromip']] < $this->column[$this->trace_array[$key]['toip']]) {
                    $arrow_direction="right";
                    $this->trace_array[$key]['arrow_align'] = "right";
                } else {
                    $arrow_direction="left";
                    $this->trace_array[$key]['arrow_align'] = "left";
                }
                $this->trace_array[$key]['msg_possition']   = $this->column[$this->trace_array[$key]['toip']];
                $this->trace_array[$key]['arrow_possition'] = $this->column[$this->trace_array[$key]['fromip']];
                $this->trace_array[$key]['arrow_direction'] = $arrow_direction;

            } else {
                if ($this->trace_array[$key]['fromip'] == $this->trace_array[$key]['toip']) {
                    $this->trace_array[$key]['arrow_align'] = "left";
                    $arrow_direction="loop";
                } else if ($this->column[$this->trace_array[$key]['fromip']] < $this->column[$this->trace_array[$key]['toip']]) {
                    $this->trace_array[$key]['arrow_align'] = "left";
                    $arrow_direction="right";
                } else {
                    $this->trace_array[$key]['arrow_align'] = "right";
                    $arrow_direction="left";
                }

                $this->trace_array[$key]['msg_possition']   = $this->column[$this->trace_array[$key]['fromip']];
                $this->trace_array[$key]['arrow_possition'] = $this->column[$this->trace_array[$key]['toip']];
                $this->trace_array[$key]['arrow_direction'] = $arrow_direction;
            }
        }

        /*
        print "<pre>";
        print_r($this->trace_array);
        print "</pre>";
        */

        print "
        <p>
        <a name=#top>
        <table cellpadding=2 cellspacing=0 border=0 width=100%>
        <tr bgcolor=lightgrey>
        <td></td>
        <td align=center><b>Id</b></td>
        <td align=center><b>Size</b></td>
        <td align=center><b>Time</b></td>
        ";

        foreach (array_keys($this->column) as $_key) {
               $IPels=explode(":",$_key);

            print "<td align=center class=border>";
            if ($proxyIP != $IPels[0] && $this->isProxy($IPels[0],$proxyIP)) {
                $trace_link=sprintf("<a href=\"javascript:void(null);\" onClick=\"return window.open('sip_trace.phtml?cdr_source=%s&callid=%s&fromtag=%s&totag=%s&proxyIP=%s', 'Trace',
                'toolbar=0,status=1,menubar=1,scrollbars=1,resizable=1,width=1000,height=600')\"><font color=red><b>%s:%s (Trace) </b></font></a>",
                urlencode($this->cdr_source),
                urlencode($callid),
                urlencode($fromtag),
                urlencode($totag),
                $IPels[0],
                $_key,
                $this->column_port[$_key]
                );
                printf ("<b><font color=blue>%s</b>",$trace_link);
            } else {
                printf ("<b>%s</b>",$_key);
            }
            print "</td>";
        }

        print "</tr>";

        $i=0;

        foreach (array_keys($this->trace_array) as $key) {

            $i++;

            $id        = $this->trace_array[$key]['id'];
            $msg       = $this->trace_array[$key]['msg'];
            $fromip    = $this->trace_array[$key]['fromip'];
            $toip      = $this->trace_array[$key]['toip'];
            $date      = substr($this->trace_array[$key]['date'],11);
            $status    = $this->trace_array[$key]['status'];
            $direction = $this->trace_array[$key]['direction'];
            $timestamp = $this->trace_array[$key]['timestamp'];
            $method    = $this->trace_array[$key]['method'];
            $isProxy   = $this->trace_array[$key]['isProxy'];
            $transport  = $this->trace_array[$key]['transport'];

            $msg_possition   = $this->trace_array[$key]['msg_possition'];
            $arrow_possition = $this->trace_array[$key]['arrow_possition'];
            $arrow_direction = $this->trace_array[$key]['arrow_direction'];
            $arrow_align     = $this->trace_array[$key]['arrow_align'];
            $md5             = $this->trace_array[$key]['md5'];

            if ($i==1) $begin_timestamp = $timestamp;
            $timeline=$timestamp-$begin_timestamp;

            $sip_phone_img=getImageForUserAgent($msg);

            //if (!$msg) continue;

            if ($seen_msg[$md5]) continue;

            $SIPclass=substr($status,0,1);
    
            if ($SIPclass=="6") {
                $status_color="<font color=red>";
            } else if ($SIPclass=="5" ) {
                $status_color="<font color=red>";
            } else if ($SIPclass=="4" ) {
                $status_color="<font color=blue>";
            } else if ($SIPclass=="3" ) {
                $status_color="<font color=green>";
            } else if ($SIPclass=="2" ) {
                $status_color="<font color=green>";
            } else if ($SIPclass=="1" ) {
                $status_color="<font color=\"#cc6600\">";
            } else  {
                $status_color="<font color=black>";
            }

            $_lines=explode("\n",$msg);

            if (preg_match("/^(.*) SIP/",$_lines[0],$m)) {
                $_lines[0]=$m[1];
            } else if (preg_match("/^SIP\/2\.0 (.*)/",$_lines[0],$m)) {
                $_lines[0]=$m[1];
            }

            unset($media);
            unset($diversions);

            $j=0;
            $t=0;

            foreach ($_lines as $_line) {
                if (preg_match("/^(Diversion: ).*;(.*)$/",$_line,$m)) {
                    $diversions[]=$m[1].$m[2];
                }

                if (preg_match("/^c=IN \w+ ([\d|\w\.]+)/i",$_line,$m)) {
                    $media['ip']=$m[1];
                }

                if (preg_match("/^m=(\w+) (\d+) /i",$_line,$m)) {
                    $t++;
                    $media['streams'][$m[2]]=$m[1];
                }

                if (preg_match("/^a=alt:1/i",$_line,$m)) {
                    $media['streams'][$t]=$media['streams'][$t]." ICE";
                }

                if (preg_match("/^Cseq:\s*\d+\s*(.*)$/i",$_line,$m)) {
                    $status_for_method=$m[1];
                }
                $j++;
            }

            $_els=explode(";",$_lines[0]);

            $cell_content = "<a name=\"packet$i\">
            $status_color $_els[0]
            ";

            if ($status) $cell_content.=" <font color=black>for ".$status_for_method."</font>";

            if (is_array($diversions)) {
                foreach ($diversions as $_diversion) {
                    $cell_content.="<br><b>$_diversion</b>";
                }
            }

            if (is_array($media['streams'])) {
                foreach (array_keys($media['streams']) as $_key) {
                    $_stream=$media['streams'][$_key].':'.$media['ip'].':'.$_key;
                    $cell_content.="<br><b>$_stream</b>";
                }
            }

            $cell_content.="
            </font>
            </a>
            ";

            print "
            <tr class=border onClick=\"return toggleVisibility('row$i')\">
            <td>
            ";

            if ($timeline && !$_seen_timeline[$timeline]) {
                printf ("%s+%ds </font>",$status_color,$timeline);
                $_seen_timeline[$timeline]++;
            }

            $len=strlen($msg);

            print "
            </td>
            <td class=bbot>$status_color$i/$this->rows&nbsp;</font></td>
            <td class=bbot>$len bytes</td>
            <td class=bbot><nobr>$status_color$date</nobr></font>
            ";

            $column_current=1;
            
            while ($column_current <= count($this->column)) {

                if ($arrow_possition==$column_current) {
                    if ($direction=='out') {
                        if ($arrow_direction == 'left') {
                            $arrow="green_arrow_left.png";
                        } else if ($arrow_direction == 'right') {
                            $arrow="green_arrow_right.png";
                        } else if ($arrow_direction == 'loop'){
                            $arrow="LoopArrow.png";
                        }
                    } else {
                        if ($arrow_direction == 'left') {
                            $arrow="blue_arrow_left.png";
                        } else if ($arrow_direction == 'right') {
                            $arrow="blue_arrow_right.png";
                        } else if ($arrow_direction == 'loop'){
                            $arrow="LoopArrow.png";
                        }
                    }

                }

                if ($arrow_possition==$column_current) {
                    print "<td class=bbot align=$arrow_align>";

                    if ($arrow_direction == 'left') {
                        print "<img src=images/$arrow border=0>";
                        if (!$isProxy && $direction=='in' && $sip_phone_img && $sip_phone_img!='unknown.png')
                        print "<img src=images/30/$sip_phone_img border=0>";
                    } else {
                        if (!$isProxy && $direction=='in' && $sip_phone_img && $sip_phone_img!='unknown.png')
                        print "<img src=images/30/$sip_phone_img border=0>";
                        print "<img src=images/$arrow border=0>";
                    }

                    print "<br>";
                    if ($transport == 'tls') print "<img src=images/lock15.gif border=0> ";
                    if ($direction == 'in') {
                        printf ("%s port %d",strtoupper($transport),$this->trace_array[$key]['fromport']);
                    } else {
                        printf ("%s port %d",strtoupper($transport),$this->trace_array[$key]['toport']);
                    }

                } else {
                    print "<td class=bbot>";
                }

                if ($msg_possition == $column_current) {
                    print $cell_content;
                    print "<br>";
                    if ($direction == 'in') {
                        printf ("%s port %d",strtoupper($transport),$this->trace_array[$key]['toport']);
                    } else {
                        printf ("%s port %d",strtoupper($transport),$this->trace_array[$key]['fromport']);
                    }
                } else {
                    print "&nbsp;";
                }

                print "
                </td>
                ";

                $column_current++;
                if ($arrow_direction=='loop') $seen_msg[$md5]++;
            }

            print "</tr>";

            if (is_array($this->SIPProxies)) {
                $IPels=explode(":",$fromip);
                $justIP=$IPels[0];
                foreach (array_keys($this->SIPProxies) as $localProxy) {
                    if ($localProxy==$justIP) {
                        $direction="out";
                        break;
                    }
                }
            }

            $trace_span=count($this->column)+2;

            print "
            <tr>
            <td></td>
            <td colspan=$trace_span>
            ";

            print "
            <table class=extrainfo id=row$i width=100%>
            <tr>
            <td valign=top align=center class=border>
            ";

            if ($direction == "out") {
                print "
                <nobr>
                <h1>SIP Proxy</h1></nobr>
                ";
            } else {
                if ($sip_phone_img) print "<img src=images/$sip_phone_img border=0>";
            }

            if ($timeline > 0) {
                printf ("<p>+%s s<br>(%s)",$timeline,sec2hms($timeline));
            }
        
            $msg=nl2br(htmlentities($msg));

            print "
            </td>
            <td valign=top class=border colspan=2 width=100%>$status_color $msg </font></td>
            </td>
            </tr>
            </table>
            ";

            print "
            </td>
            </tr>
            ";
        }

        print "
        </table>
        ";
    }

    function togglePublicVisibility($callid,$fromtag,$public='0') {

        $key="callid-".trim($callid).trim($fromtag);

        if (!$public) {
            $query=sprintf("delete from memcache where `key` = '%s'",addslashes($key));
            $this->cdrtool->query($query);
        } else {
            $query=sprintf("delete from memcache where `key` = '%s'",addslashes($key));
            $this->cdrtool->query($query);
            $query=sprintf("insert into memcache values ('%s','public')",addslashes($key));
            $this->cdrtool->query($query);
        }
    }

    function purgeRecords($days='') {

        if ($this->enableThor) {
            return true;
        }

        $b=time();

        if ($days) {
            $this->purgeRecordsAfter=$days;
        } else if (!$this->purgeRecordsAfter) {
            $this->purgeRecordsAfter=15;
        }

        $beforeDate=Date("Y-m-d", time()-$this->purgeRecordsAfter*3600*24);

        $query=sprintf("select id as min, date from %s order by id ASC limit 1",
                       $this->table);
        

        if ($this->db->query($query)) {
            if ($this->db->num_rows()) {
                $this->db->next_record();
                $min=$this->db->f('min');
                $begindate=$this->db->f('date');
            } else {
                $log=sprintf("No records found in %s\n",$this->table);
                print $log;
                syslog(LOG_NOTICE,$log);
                return false;
            }

        } else {
            $log=sprintf("Error: %s (%s)\n",$this->db->Error,$query);
            print $log;
            syslog(LOG_NOTICE,$log);
            return false;
        }

        $query=sprintf("select id as max from %s where date < '%s' order by id DESC limit 1",
                $this->table,$beforeDate);


        if ($this->db->query($query) && $this->db->num_rows()) {
            $this->db->next_record();
            $max=$this->db->f('max');
        } else {
            $log=sprintf("No records found in %s before %s, records start after %s\n",
            $this->table,$beforeDate,$begindate);
            syslog(LOG_NOTICE,$log);
            print $log;
            return false;
        }

        $deleted=0;
        $i=$min;

        $interval=1000;

        $rows2delete=$max-$min;
        $found = 0;

        print "$rows2delete traces to delete between $min and $max\n";

        while ($i<=$max) {
            $found=$found+$interval;

            if ($i + $interval < $max) {
                $top=$i;
            } else {
                $top=$max;
            }
            $query=sprintf("delete low_priority from %s
                            where id >= '%d' and id <='%d'",
                            $this->table,$min,$top);
            if ($this->db->query($query)) {
                $deleted=$deleted+$this->db->affected_rows();
            } else {
                $log=sprintf("Error: %s (%s)",$this->db->Error,$this->db->Errno);
                syslog(LOG_NOTICE,$log);
                return false;
            }

            if ($found > $progress*$rows2delete/100) {
                $progress++;
                if ($progress%10==0) {
                    print "$progress% ";
                }
                flush();
            }

            $i=$i+$interval;
        }

        print "\n";

        $e    =time();
        $d    =$e-$b;
        $rps=0;

        if ($deleted && $d) $rps=$deleted/$d;

        $log=sprintf("%s records before %s from %s deleted in %d s @ %.0f rps\n",$deleted,$beforeDate,$this->table,$d,$rps);
        syslog(LOG_NOTICE,$log);
        print $log;

        return true;
    }

}

class Media_trace {
    var $enableThor  = false;
    var $table = 'media_sessions';

    function Media_trace ($cdr_source) {
        global $DATASOURCES;

        $this->cdr_source = $cdr_source;

        $this->cdrtool  = new DB_CDRTool();

        if (strlen($DATASOURCES[$this->cdr_source]['enableThor'])) {
            $this->enableThor = $DATASOURCES[$this->cdr_source]['enableThor'];
        }

        if ($this->enableThor) {
	    	require("/etc/cdrtool/ngnpro_engines.inc");

            if ($DATASOURCES[$this->cdr_source]['soapEngineId'] && in_array($DATASOURCES[$this->cdr_source]['soapEngineId'],array_keys($soapEngines))) {
                $this->soapEngineId=$DATASOURCES[$this->cdr_source]['soapEngineId'];
                require_once("ngnpro_soap_library.php");

                $this->SOAPlogin = array(
                                       "username"    => $soapEngines[$this->soapEngineId]['username'],
                                       "password"    => $soapEngines[$this->soapEngineId]['password'],
                                       "admin"       => true
                                       );
    
                $this->SOAPurl=$soapEngines[$this->soapEngineId]['url'];
        
                $this->SoapAuth = array('auth', $this->SOAPlogin , 'urn:AGProjects:NGNPro', 0, '');
    
                // Instantiate the SOAP client
                $this->soapclient = new WebService_NGNPro_SipPort($this->SOAPurl);
    
                $this->soapclient->setOpt('curl', CURLOPT_TIMEOUT,        5);
                $this->soapclient->setOpt('curl', CURLOPT_SSL_VERIFYPEER, 0);
                $this->soapclient->setOpt('curl', CURLOPT_SSL_VERIFYHOST, 0);
    
            } else {
                print "Error: soapEngineID not defined in datasource $this->cdr_source";
                return false;
            }

        } else {
            if ($DATASOURCES[$this->cdr_source]['table']) {
                $this->table             = $DATASOURCES[$this->cdr_source]['table'];
            }

            $db_class                = $DATASOURCES[$this->cdr_source]['db_class'];
            $this->db                = new $db_class;
        }

    }

    function getTrace ($proxyIP,$callid,$fromtag,$totag) {

        if ($this->enableThor) {
            $this->info = json_decode($this->getTraceFromThor($proxyIP,$callid,$fromtag,$totag));

        } else {

            // get trace from SQL

            $query=sprintf("select info from %s where call_id = '%s' and from_tag = '%s' and to_tag= '%s'",
            $this->table,
            addslashes($callid),
            addslashes($fromtag),
            addslashes($totag)
            );

            if (!$this->db->query($query)) {
                printf ("<p>Error: database error %s %s",$this->db->Error,$query);
                return false;
            }
    
            if ($this->db->num_rows()) {
                $this->db->next_record();
                $this->info = json_decode($this->db->f('info'));
            }
        }

    }

    function getTraceFromThor($proxyIP,$callid,$fromtag,$totag) {
        // get trace using soap request
        if (!$proxyIP || !$callid || !$fromtag) return false;

        if (!is_object($this->soapclient)) {
            print "Error: soap client is not defined.";
            return false;
        }

        $filter=array('nodeIp'  => $proxyIP,
                      'callId'  => $callid,
                      'fromTag' => $fromtag,
                      'toTag'   => $totag
                      );

        $this->soapclient->addHeader($this->SoapAuth);

        $result     = $this->soapclient->getMediaTrace($filter);

		//print_r($result);

        if (PEAR::isError($result)) {
            $error_msg   = $result->getMessage();
            $error_fault = $result->getFault();
            $error_code  = $result->getCode();

            printf("<font color=red>Error from %s: %s: %s</font>",$this->SOAPurl,$error_fault->faultstring,$error_fault->faultcode);
            return false;
        }

        $this->info = json_decode($result);
    }

    function show($proxyIP,$callid,$fromtag,$totag) {

        if ($_SERVER['HTTPS'] == "on") {
            $protocolURL = "https://";
        } else {
            $protocolURL = "http://";
        }

        $this->getTrace($proxyIP,$callid,$fromtag,$totag);

        if (!is_object($this->info)) {
            print "<p>No information available.";
            return false;
        }

        print "
        <h1>CDRTool Media information</h1>
        <h2>Media session $callid</h2>
        ";

        foreach (array_values($this->info->streams) as $_val) {
            $_diff=$_val->end_time-$_val->timeout_wait;
            $seen_stamp[$_val->start_time]++;
            $seen_stamp[$_val->end_time]++;
            $seen_stamp[$_diff]++;
            $media_types[]=$_val->media_type;
        }

        print "<h3>Media information</h3>";

        print "<table border=0>";
        printf ("<tr><td class=border>Call duration</td><td class=border>%s</td></tr>",$this->info->duration);
        list($relay_ip,$relay_port)=explode(":",$this->info->streams[0]->caller_local);
        printf ("<tr><td class=border>Media relay</td><td class=border>%s</td></tr>",$relay_ip);
        print "</table>";

        print "<h3>Media streams</h3>";

        print "<table border=0>";
        print "<th></th>";

        foreach (array_values($media_types) as $_type) {
            printf ("<th>%s</th>",ucfirst($_type));
        }

        print "</tr>";

        foreach ($this->info->streams[0]  as $_val => $_value) {
            printf ("<tr><td class=border>%s</td>",ucfirst(preg_replace("/_/"," ",$_val)));
            $j=0;
            while ($j < count($media_types)) {
                printf ("<td class=border>%s</td>",$this->info->streams[$j]->$_val);
                $j++;
            }

            printf ("</tr>\n");
        }

        print "</table>";

        /*
        print "<pre>";
        print_r($this->info);
        print "</pre>";
        */

        print "<h3>Stream succession</h3>";

        $w=500;
        $w1=30;
        $stamps=array_keys($seen_stamp);
        sort($stamps);

        $w2=$w+$w1;

        print "<table border=0 width=$w2>";

        foreach (array_values($this->info->streams) as $_val) {

            $w_col1=intval($_val->start_time*$w/$this->info->duration);
            $w_col2=intval(($_val->end_time-$_val->start_time-$_val->timeout_wait)*$w/$this->info->duration);
            $w_col3=intval(($this->info->duration-$_val->end_time+$_val->timeout_wait)*$w/$this->info->duration);

            print "<tr><td width=$w1 class=border>$_val->media_type</td>";

            $t2=$_val->end_time-$_val->timeout_wait;
            $t3=$this->info->duration;

            print "<td>
            <table width=100%><tr>";
            print "<td width=$w_col1 bgcolor=white></td>";
            print "<td width=$w_col2 bgcolor=green align=right><font color=white>$t2</font></td>";
            if ($_val->timeout_wait) {
                print "<td width=$w_col3 bgcolor=red align=right><font color=white>$t3</font></td>";
            } else {
                print "<td width=$w_col3 bgcolor=white align=right></td>";
            }

            print "</table>";
            print "</td></tr>";

        }
        print "</table>";

        print "<h4>Legend</h4>";
        print "<table border=0>
        <tr><td bgcolor=green width=50>&nbsp;</td><td>Session data</td></tr>
        <tr><td bgcolor=red width=50>&nbsp;</td><td>Timeout period</td></tr>
        </table>
        ";


    }

}

include_once("phone_images.php");
function getImageForUserAgent($agent) {
    global $userAgentImages;

    foreach ($userAgentImages as $agentRegexp => $image) {
        if (preg_match("/(user-agent|server):.*$agentRegexp/i", $agent)) {
            return $image;
        }
    }
    return "unknown.png";
}

function isThorNode($ip,$sip_proxy) {

    if (!$ip || !$sip_proxy) return false;

    $socket = fsockopen($sip_proxy, 9500, $errno, $errstr, 1);

    if (!$socket) {
        return false;
    }
    
    $request=sprintf("is_online %s as sip_proxy",$ip);
    
    if (fputs($socket,"$request\r\n") !== false) {
        $ret = trim(fgets($socket,4096));
        fclose($socket);
    } else {
        fclose($socket);
        return false;
    }

    if ($ret == 'Yes') {
        return true;
    } else {
        return false;
    }
}

?>