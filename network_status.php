<?
require("/etc/cdrtool/global.inc");
page_open(
    array("sess" => "CDRTool_Session",
          "auth" => "CDRTool_Auth",
          "perm" => "CDRTool_Perm"
          ));

require("sip_statistics.php");
require("media_sessions.php");
$perm->check("statistics");

$title="SIP registrar statistics";
include("header.phtml");

global $CDRTool;
if (strlen($CDRTool['filter']['domain'])) $allowedDomains=explode(' ',$CDRTool['filter']['domain']);

$layout = new pageLayoutLocal();
$layout->showTopMenu($title);

print "<table border=0>";
foreach (array_keys($DATASOURCES) as $datasource) {

    if (in_array($datasource,$CDRTool['dataSourcesAllowed']) && !$DATASOURCES[$datasource]['invisible']) {
        print "<tr>";
        print "<td valign=top>";

        if ($DATASOURCES[$datasource]['networkStatus']) {
	      	printf ("<h1>%s</h1>",$DATASOURCES[$datasource]['name']);
            printf ("<img src=images/SipThorNetwork.php?engine=%s align=left>",
            $DATASOURCES[$datasource]['networkStatus']);

        } else if ($DATASOURCES[$datasource]['db_registrar']){
	      	printf ("<h1>%s</h1>",$DATASOURCES[$datasource]['name']);
			require_once("cdr_generic.php");
			$online = new SIPonline($datasource,$DATASOURCES[$datasource]['db_registrar']);
            $online->showAll();
        }

        print "</td>";
        print "<td valign=top>";

        if ($DATASOURCES[$datasource]['mediaSessions']) {
           	$MediaSessions = new MediaSessionsNGNPro($DATASOURCES[$datasource]['mediaSessions'],$allowedDomains);
            $MediaSessions->getSessions();
            $MediaSessions->getSummary();
            print "<h2>Media relays</h2>";
            $MediaSessions->showSummary();

        } else if ($DATASOURCES[$datasource]['mediaDispatcher']) {
           	$MediaSessions = new MediaSessions($DATASOURCES[$datasource]['mediaDispatcher'],$allowedDomains);
            $MediaSessions->getSessions();
            $MediaSessions->getSummary();
            print "<h2>Media relays</h2>";
            $MediaSessions->showSummary();
        }

        if ($DATASOURCES[$datasource]['networkStatus']) {
        	$NetworkStatistics = new NetworkStatistics($DATASOURCES[$datasource]['networkStatus'],$allowedDomains);
            print "<h2>SIP accounts</h2>";
            $NetworkStatistics->showStatistics();

            if (!$allowedDomains) {
                print "<p>";
                print "<h2>Network topology</h2>";
                $NetworkStatistics->showStatus();
            }
        }

        print "</td>";
        print "</tr>";
    }
}

print "</table>";

$layout->showFooter();

print "
</body>
</html>
";
page_close();

?>
