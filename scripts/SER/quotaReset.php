#!/usr/bin/php
<?
# this script resets the quota system 
# this script must be run once at the beginning of the calendar month

define_syslog_variables();
openlog("CDRTool quota", LOG_PID, LOG_LOCAL0);

require("/etc/cdrtool/global.inc");
require("cdr_lib.phtml");

while (list($k,$v) = each($DATASOURCES)) {
    if (strlen($v["UserQuotaClass"])) {

        unset($CDRS);
        $class_name=$v["class"];
        $CDRS = new $class_name($k);

        $SERQuota_class = $v["UserQuotaClass"];

		$log=sprintf("Reset user quotas for data source %s\n",$v['name']);
        syslog(LOG_NOTICE,$log);
        //print $log;

        $Quota = new $SERQuota_class($CDRS);
		$Quota->deleteQuotaInitFlag();
	}
}
?>
