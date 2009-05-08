# Normalization
*/5 * * * * root php /var/www/CDRTool/scripts/normalize.php          >/dev/null

# Check quota
*/5  * * * * root php /var/www/CDRTool/scripts/OpenSIPS/quotaCheck.php    >/dev/null
  0  2 1 * * root php /var/www/CDRTool/scripts/OpenSIPS/quotaReset.php    >/dev/null 
 10  3 1 * * root php /var/www/CDRTool/scripts/OpenSIPS/quotaDeblock.php  >/dev/null 
 
# Purge SIP trace table 
  20 3 * * * root php /var/www/CDRTool/scripts/OpenSIPS/purgeSIPTrace.php >/dev/null 

# Statistics
*/5  * * * * root php /var/www/CDRTool/scripts/buildStatistics.php   >/dev/null

# Next two jobs are only used when using a central radacct table:
#  0  3 1 * * root php /var/www/CDRTool/scripts/OpenSIPS/rotateTables.php >/dev/null 
#  0  4 * * * root php /var/www/CDRTool/scripts/purgeTables.php      >/dev/null

# Send email with last missed calls to SIP subscribers
15 2 * * * root /var/www/CDRTool/scripts/OpenSIPS/notifyLastSessions.php >/dev/null 

# Import rating tables
15 5 * * * root /var/www/CDRTool/scripts/importRatingTables.php >/dev/null
