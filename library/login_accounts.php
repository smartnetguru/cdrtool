<?
function check_telephone($tel,$country) {
        global $err;
        if ($country == "NL") {
        	if (preg_match("/^\+31\-\d{2}-\d{7}$/",$tel) ||
                	preg_match("/^\+31\-\d{3}-\d{6}$/",$tel)) {
                } else {
                        $err="NL numbers must be in format +31-DD-DDDDDDD or +31-DDD-DDDDDD";
                        return 0;
                }
        }
        return 1;
}
# subscriber form
$f = new form; 

$f->add_element(array(	"name"=>"username",
			"type"=>"text",
			"size"=>"25",
			"length_e"=>"2",
			"minlength"=>"2",
			"maxlength"=>"25",
			"valid_regex"=>"^[-a-zA-Z0-9@_\.]{2,}$",
            "valid_e"=>"Username required: - mininum 2 chars (letters, digits, _, -, @, .)"
			));
$f->add_element(array(	"name"=>"password",
                "type"=>"text",
                "size"=>"25",
                "minlength"=>"5",
                "maxlength"=>"25",
                "pass"=>1,
                "valid_regex"=>"^[a-zA-Z0-9|_|-]*$",
                "valid_e"=>"Password: Letters, digits _ - only - minim 5 characters",
                "value"=>"$password"
                ));

$f->add_element(array(	"name"=>"name",
			"type"=>"text",
			"length_e"=>"3",
			"minlength"=>"3",
			"maxlength"=>"100",
			"size"=>"30",
			"valid_regex"=>"^[-a-zA-Z0-9|_|\.|\s ]*$",
			"valid_e"=>"Name required - min 1 chars (letters, digits _ - . spaces only)",
			"icase"=>1
			));
$f->add_element(array(	"name"=>"organization",
			"type"=>"text",
			"length_e"=>"6",
			"maxlength"=>"100",
			"size"=>"30",
			"valid_regex"=>"^[-a-zA-Z0-9|_|\.|\s ]*$",
			"valid_e"=>"Organization required - min 6 chars (letters, digits _ - . spaces only)",
			"icase"=>1
			));
$f->add_element(array(	"name"=>"email",
			"type"=>"text",
			"length_e"=>6,
			"minlength"=>"6",
			"maxlength"=>"100",
			"size"=>"30",
			"valid_e"=>"Syntax error in E-Mail address.",
			"valid_regex"=>"^([-a-zA-Z0-9._]+@[-a-zA-Z0-9_]+(\.[-a-zA-Z0-9_]+)+)*$"
			));
$f->add_element(array(	"name"=>"aNumberFilter",
			"type"=>"text",
			"maxlength"=>"100",
			"size"=>"60"
			));
$f->add_element(array(	"name"=>"domainFilter",
			"type"=>"text",
			"maxlength"=>"255",
			"size"=>"60"
			));
$f->add_element(array(	"name"=>"impersonate",
			"type"=>"text",
			"maxlength"=>"255",
			"size"=>"11"
			));
$f->add_element(array(	"name"=>"gatewayFilter",
			"type"=>"text",
			"maxlength"=>"255",
			"size"=>"60"
			));
$f->add_element(array(	"name"=>"compidFilter",
			"type"=>"text",
			"maxlength"=>"255",
			"size"=>"60"
			));
$f->add_element(array(	"name"=>"cscodeFilter",
			"type"=>"text",
			"maxlength"=>"255",
			"size"=>"60"
			));
$f->add_element(array(	"name"=>"serviceFilter",
			"type"=>"text",
			"maxlength"=>"255",
			"size"=>"60"
			));
$f->add_element(array(	"name"=>"afterDateFilter",
			"type"=>"text",
			"maxlength"=>"10",
			"size"=>"11"
			));

$f->add_element(array(	"name"=>"tel",
			"type"=>"text",
			"size"=>"30"
			));
$f->add_element(array(	"name"=>"expire",
			"type"=>"text",
			"size"=>"11"
			));
$blocked_els=array(
                array("label"=>"","value"=>"0"),
                array("label"=>gettext("Blocked"),"value"=>"1")
            );
$f->add_element(array("type"=>"select",
                      "name"=>"blocked",
                      "options"=>$blocked_els,
                      "size"=>1,
                      "value"=>""
			));

$f->add_element(
            array("type"=>"submit",
		      "name"=>"submit",
		      "extrahtml"=>"border=0",
               "value"=>"Submit")
		);

while(list($k,$v) = each($DATASOURCES)) {
    if ($k!="unknown") {
        $cdrSourcesEls[]=array("label"=>$v[name],"value"=>$k);
    }
}

$f->add_element(array("type"=>"select",
                      "name"=>"sources",
                      "options"=>$cdrSourcesEls,
                      "size"=>8,
                      "multiple"=>"1",
                      "value"=>""
			        )
            );


function showForm($id="") {
    global $CDRTool, $verbose, $perm, $auth, $sess, $cdr, $f,
    $perms, $source, $sources, $action;

    $sources=explode(",",$sources);

    global $afterDateFilter;

    if (preg_match("/^0000-00-00$/",$afterDateFilter)) {
        $afterDateFilter="";
    }

    $f->load_defaults();
    $f->start("","POST","","", "");
    
    print "<input type=hidden name=check value=yes>";
    print "<input type=hidden name=action value=\"$action\">";
    
    if ($frzall) {
        $f->freeze();
    }

    if (!$perm->have_perm("admin")) {
        $ff=array("sources",
                  "gatewayFilter",
                  "domainFilter",
                  "aNumberFilter",
                  "serviceFilter",
                  "compidFilter",
                  "cscodeFilter",
                  "afterDateFilter",
                  "impersonate");
            $f->freeze($ff);
        }

    
    print "
    <p>
    <table class=border width=100%>
    <tr>
    <td valign=top width=50%>
    <h3>Contact details</h3>
    <p>";
    print _("The fields marked with ");
    print " <font color=orange>*</font> ";
    print _("are mandatory");
    print ":</font>
    <p>
    ";
    
    print "
    <table border=0>
    ";
    
    $f->show_element("action","");
    
    if ($id) {
            $f->add_element(array("type"=>"hidden",
                                  "name"=>"id",
                                  "value"=>"$id"
                            ));
    }
    
    print "
    <tr>
    <td valign=top><font color=$labelcolor>
    ";
    print _("Name");
    print ":<font color=orange>*</font>
    ";
    print "
    </td>
    <td colspan=2 valign=top><font color=$formelcolor>
    ";
    $f->show_element("name","");
    print "
    </font>
    </td>
    </tr>
    ";
    
    print "
    <tr>
    <td valign=top><font color=$labelcolor>
    ";
    print _("Organization");
    print ":
    </td>
    <td colspan=2 valign=top><font color=$formelcolor>
    ";
    $f->show_element("organization","");
    print "
    </font>
    </td>
    </tr>
    ";
    
    print "
    <tr>	
    <td valign=top>
    <font  color=$labelcolor>
    ";
    print _("E-mail");
    print ":<font color=orange>*</font>
    </font>
    </td>
    <td colspan=2 valign=top><font color=$formelcolor>
    ";
    $f->show_element("email",""); 
    print "
    </font>
    </td>
    </tr>
    ";
    
    
    print "
    <tr>
    <td valign=top><font color=$labelcolor>
    ";
    print _("Telephone");
    print "
    </td>
    <td colspan=2 valign=top><font color=$labelcolor>
    ";
    $f->show_element("tel","");        
    print "
    </font>
    </td>
    </tr>
    ";
    print "
    <tr>
    <td valign=top><font color=$labelcolor>
    ";
    
    print _("Username");
    print ":<font color=orange>*</font>
    </font>
    </td>
    <td colspan=2 valign=top><font color=$labelcolor>
    ";
    
    $f->show_element("username",""); 
    
    print "
    </font>
    </td>
    </tr>
    ";
    
    print "
    <tr>
    <td valign=top><font color=$labelcolor>
    ";
    print _("Password");
    print ":<font color=orange>*</font>
    </td>
    <td colspan=2 valign=top><font color=$labelcolor>
    ";
    $f->show_element("password","");
    print "</td>
    </tr>
    ";
           print "
           <tr>   
           <td valign=top><font color=$labelcolor>
           E-mail settings:</td>
           <td colspan=2 valign=top><font color=$labelcolor>
           ";
           print "<input type=checkbox name=mailsettings value=1> ";
           print "
           </font>
           </td>  
           </tr>  
           ";

    if ($perm->have_perm("admin")) {
           print "
           <tr>
           <td colspan=3>
           <hr noshade size=1 width=100%>
           </td>
           </tr>
           ";
           print "
           <tr>   
           <td valign=top><font color=$labelcolor>
           Expire date </td>
           <td colspan=2 valign=top><font color=$labelcolor>
           ";
           $f->show_element("expire","");
           print "
           </font>
           </td>  
           </tr>  
           ";
           print "
           <tr>   
           <td valign=top><font color=$labelcolor>
           Impersonate </td>
           <td colspan=2 valign=top><font color=$labelcolor>
           ";
           $f->show_element("impersonate","");
           print "
           </font>
           </td>  
           </tr>  
           ";
           print "
           <tr>   
           <td valign=top><font color=$labelcolor>
           Delete </td>
           <td colspan=2 valign=top><font color=$labelcolor>
           ";
           print "<input type=checkbox name=delete value=1>";
           print "
           </font>
           </td>  
           </tr>  
           ";

           /*
           print "
           <tr>   
           <td valign=top><font color=$labelcolor>
           Lock </td>
           <td colspan=2 valign=top><font color=$labelcolor>
           ";
                   $f->show_element("blocked","");
           print "
           </font>
           </td>  
           </tr>  
           ";
           */
           print "
           <tr>   
           <td colspan=2><hr noshade size=1>
           </tr>
           ";
    }
    
    print "
    </table>";

    print "</td>
    <td valign=top>
    ";

     print "
     <h3>Permissions</h3>";
     print "<table border=0>";
     print "<tr>
     <td valign=top colspan=2 align=left>
     ";
 
     print "<table width=100% border=0>
     <tr>
     ";
     if ($perm->have_perm("admin")) {
        print "<td valign=top>
        <b>Functions</b><br>";
        print $perm->perm_sel("perms", $perms);
     }
     print "<td valign=top><b>Data sources</b><br>";
     $f->show_element("sources","");
     print "
     </td>
     </tr>
     </table>";
 
     print "
     </tr>
     <tr>   
     <td colspan=2><hr noshade size=1>
     <b>Filters</b>
     </tr>
     ";
     print "<tr><td>Trusted peers</td><td>";
     $f->show_element("gatewayFilter","");
     print "</td></tr>";
     print "<tr><td>Domains</td><td>";
     $f->show_element("domainFilter","");
     print "</td></tr>";
     print "<tr><td>Subscribers</td><td>";
     $f->show_element("aNumberFilter","");
     print "</td></tr>";
     print "</td></tr>";
     print "<tr><td>After date</td><td>";
     $f->show_element("afterDateFilter","");
     print "</td></tr>";
     print "
     </table>";
    print "</td>
    </tr>
    <tr>
    <td>";
    if (!$frzall) {
        $f->show_element("submit","");
    }
    print "</td>
    <td>
    </td>
    </tr>
    </table>
    ";
    $f->finish();                     // Finish form
}

function accountList() {
    global $auth, $perm, $verbose, $search_text;
    $uid=$auth->auth["uid"];

    $db        = new DB_CDRTool;

    $query="select * from auth_user";
    if (!$perm->have_perm("admin")) {
            $query=$query. " where user_id = '$uid'";
    }
    $query=$query." order by name asc";
    $db->query($query);
    dprint($query);
    $rows=$db->num_rows();
    print "
    <p>
    <center>
    <table width=100% cellpadding=1 cellspacing=1 border=6 bgcolor=lightgrey>
    <tr bgcolor=#CCCCCC>
     ";
     print "<td class=h><b>";
     print _("Name");
     print "<td class=h><b>";
     print _("Organization");
     print "<td class=h><b>";
     print _("Username");
     print "<td class=h><b>";
     print _("E-mail");
     print "<td class=h><b>";
     print _("Tel");
     print "<td class=h><b>";
     print _("Sources");
     print "<td class=h><b>";
     print _("Expire");
     print "
     </tr>
     ";
    while($db->next_record()) {
        $id_db          =$db->f('user_id');
        $name		    =$db->f('name');
        $username	    =$db->f('username');
        $email		    =$db->f('email');
        $organization 	=$db->f('organization');
        $password	    =$db->f('password');
        $tel		    =$db->f('tel');
        $domainFilter 	=$db->f('domainFilter');
        $aNumberFilter 	=$db->f('aNumberFilter');
        $expire     	=$db->f('expire');
        $sources	    =preg_replace ("/,/",", ",$db->f('sources'));

        if (date('Y-m-d') > $expire) {
        	$bgcolor="orange";
        } else {
        	$bgcolor="white";
        }

        print "
        <tr bgcolor=$bgcolor>
        <td> <a href=$PHPSELF?id=$id_db&action=edit>$name</a>
        <td> $organization
        <td> $username
        <td> <nobr><a href=mailto:$email>$email</a></nobr>
        <td> <nobr>$tel</nobr>
        <td> $sources
        <td> $expire
        </tr>
        ";
        
        $j++;
    }

    print "</table>
    </center>
    ";
}
?>