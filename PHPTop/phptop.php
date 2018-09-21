<?PHP
echo (" <h2>Processes : </h2> <br> <center>");
$cmd = `/usr/bin/top -b -n1 `;

//// parse the page //////////////////
$cmd=str_replace("      "," ",$cmd);
$cmd=str_replace("     "," ",$cmd);
$cmd=str_replace("    "," ",$cmd);
$cmd=str_replace("   "," ",$cmd);
$cmd=str_replace("  "," ",$cmd);
$cmd=str_replace(" "," ",$cmd);
$cmd=str_replace(" ","</td><td>",$cmd);
$cmd=str_replace("\n","</td></tr><tr><td>",$cmd);
$cmd=str_replace("<tr><td></td><td>","<tr><td>",$cmd);
$cmd=str_replace("<tr><td>PID","<tr><td COLSPAN=10 height=50></td></tr><tr bgcolor=e0e0e0><td>PID",$cmd);
///////////////////////////////////////

echo '<table width=900 align=middle border=0><tr><td>';
echo $cmd;
echo '</td></tr></table>';
?>
