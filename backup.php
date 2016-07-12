<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function backup_config() {
    $configarray = array(
    "name" => "Backup",
    "description" => "此插件可以帮助用户下载备份",
    "version" => "1.0",
    "author" => "<a href=\"http://devtan.xyz\" target=\"_blank\" title=\"Dev-Tan\">Dev-Tan</a>",
    "language" => "english",
    "fields" => array(
        "option1" => array ("FriendlyName" => "<strong>IP</strong>", "Type" => "text", "Size" => "25", "Description" => "备份服务器IP", "Default" => "23.247.25.117", ),
        #"option3" => array ("FriendlyName" => "数据库名", "Type" => "text", "Size" => "25", "Description" => "您WHMCS数据库的名字", "Default" => "whmcs", ),
        "option2" => array ("FriendlyName" => "Port", "Type" => "text", "Size" => "25", "Description" => "备份服务器端口", "Default" => "82",),
        #"option3" => array ("FriendlyName" => "Option3", "Type" => "yesno", "Size" => "25", "Description" => "Sample Check Box", ),
        #"option4" => array ("FriendlyName" => "Option4", "Type" => "dropdown", "Options" => "1,2,3,4,5", "Description" => "Sample Dropdown", "Default" => "3", ),
        #"option5" => array ("FriendlyName" => "Option5", "Type" => "radio", "Options" => "Demo1,Demo2,Demo3", "Description" => "Radio Options Demo", ),
        #"option6" => array ("FriendlyName" => "Option6", "Type" => "textarea", "Rows" => "3", "Cols" => "50", "Description" => "Description goes here", "Default" => "Test", ),
    ));
    return $configarray;
}

function backup_activate() {

    # Create Custom DB Table
    $query = "CREATE TABLE `mod_backup` (`serverid` INT( 255 ) NOT NULL ,`servername` TEXT NOT NULL   ,`panel` TEXT NOT NULL)";
    $result = mysql_query($query);

    # Return Result
    return array('status'=>'success','description'=>'插件激活成功。');
    #return array('status'=>'error','description'=>'You can use the error status return to indicate there was a problem activating the module');
    #return array('status'=>'info','description'=>'You can use the info status return to display a message to the user');

}

function backup_deactivate() {

    # Remove Custom DB Table
    #$query = "DROP TABLE `mod_addonexample`";
    #$result = full_query($query);

    # Return Result
    return array('status'=>'success','description'=>'插件停用成功。');
    #return array('status'=>'error','description'=>'If an error occurs you can return an error message for display here');
    #return array('status'=>'info','description'=>'If you want to give an info message to a user you can return it here');

}

function backup_output($vars) {
     echo '﻿<form id="form1" name="form1" method="post" action="?module=backup&action=sc">
  <p>
    <label for="sl"></label>
  服务器ID：
  <input type="text" name="sl" id="sl"/>
  </p>
  <p>备份文件夹：
    <label for="value"></label>
    <input type="text" name="value" id="value" />
  </p>
  <p>面板：
    <label for="pl"></label>
    <input type="text" name="pl" id="pl" />
  </p>
  <p>
    <input type="submit" name="button" id="button" value="提交" />
  </p>
</form>' ;
#mysql_connect("localhost","admin_vps","Vps22842218");
#mysql_select_db("admin_vps");
if ($_GET["action"]=="sc"){
echo "Your Submit had been receive.<br>";
$sl=$_POST["sl"];
$value=$_POST["value"];
$pl=$_POST["pl"];
mysql_query("INSERT INTO mod_backup (serverid,servername,panel) VALUE ($sl,'$value','$pl')");
} 
}

function backup_clientarea($vars) {
$command = "getclientsproducts";
$adminuser = "";
$values["clientid"] = $_SESSION['uid'];
 
$results = localAPI($command,$values,$adminuser);

$productlist = '<div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span>您能在这里下载到您购买的DA主机的备份，目前仅支持DA主机，EP主机请自行去面板备份，如果下载地址1失败请尝试第2个。</div><form method="post"><table class="table">
      <thead>
        <tr>
          <th>服务ID</th>
          <th>服务器ID</th>
          <th>面板用户名</th>
		  <th>备份下载地址1</th>
		  <th>备份下载地址2</th>
        </tr>
      </thead>
      <tbody>';
foreach($results['products']['product'] as $producedetails) {
	if ($producedetails['status'] == "Active") {
	$result = mysql_query("SELECT * FROM mod_backup");
	while($row = mysql_fetch_array($result)) {
	$server=$row['serverid'];
	$serviceid = $producedetails['id'];
	$serverid = $producedetails['serverid'];
	if ($serverid == $server) {
	$serviceusername = $producedetails['username'];
    $result = mysql_query("SELECT * FROM mod_backup where serverid=$serverid");
	while($row = mysql_fetch_array($result)) {
	$servername=$row['servername'];
	$serverpanel=$row['panel']; }
	if ($serverpanel == "da") {
	$downloadlink1='http://'.$vars['option1'].':'.$vars['option2'].'/'.$servername.'/user.admin.'.$serviceusername.'.tar.gz';
	$downloadlink2='http://'.$vars['option1'].':'.$vars['option2'].'/'.$servername.'/reseller.admin.'.$serviceusername.'.tar.gz';
	$productlist .= '        <tr>
          <td>'.$serviceid.'</td>
          <td>'.$serverid.'</td>
		  <td>'.$serviceusername.'</td>
          <td><a href="'.$downloadlink1.'">下载</a></td>
          <td><a href="'.$downloadlink2.'">下载</a></td>
        </tr>';
        }
	if ($serverpanel == "ep") {
	$downloadlink='http://'.$vars['option1'].':'.$vars['option2'].'/'.$servername.'/user.admin.'.$serviceusername.'.tar.gz';
	$productlist .= '        <tr>
          <td>'.$serviceid.'</td>
          <td>'.$serverid.'</td>
		  <td>'.$serviceusername.'</td>
          <td><a href="'.$downloadlink.'">下载</a></td>
          <td><a href="#">下载</a></td>
        </tr>';}	
		}}}
}
$productlist .= "</tbody>
    </table>";
    return array(
        'pagetitle' => '备份下载',
        'breadcrumb' => array('index.php?m=backup'=>'Backup'),
        'templatefile' => 'clienthome',
        'requirelogin' => true, # or false
        'vars' => array(
            'productlist' => $productlist,
        ),
    );
 
}
?>