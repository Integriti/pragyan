<?php
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/**
 * First check if there is a "valid" user with verifyemail true having the admin permission.
 * If there is not, then make an enabled user with email=admin@cms.org and generate a password for him,
 * 		and echo (or return or show) the email and password.
 * If there is, and the current userId does not have permission for admin, return getContent(0,0,0,false);
 *
 * Then check if there is coressponding row in permlist for every module class action.
 * 		Also vice-versa check if there is function for every permsission in database.
 * Also check for page level permissions.
 *
 * Then check if the uploads directory is writable. If not give error.
 *
 * Then allow to change all perm_ranks.
 *
 *	function admin($userId) {
 }*/


	/*  Consistency check:
1) Check all available classes (modules) - to refine

2) See, if all their functions actionView(), actionEdit(), actionX exist
in the perms table or not (and create also). If
not, give option to create that permission. - to refine

3) See if any extra option exists in the database, if it does, warn the user. - to refine

4) See if any user with the name admin exists or not. If it does not,
create it and give it a random and display all required information, - to remove

5) See if the admin user has all perms at page 0 or not. If not, give him
all perms and inform him - to remove

6) See if all minimum rows n tables required for the cms to run exist or
not, if they do not, create them. - to remove

7) User management: List of all users, ability to edit everything about
them, ability to activate users, ability to create users - to refine

8) Ability to change perm ranks (like page move up and move ) - done
 *
 *
 * */

function globalSettingsForm()
{
	global $pageFullPath;
	global $CMSTEMPLATE;
	global $urlRequestRoot,$templateFolder,$cmsFolder;
	$globals=getGlobalSettings();
	foreach($globals as $var=>$val) 
		$$var=$val;
	$allow_pagespecific_header=$allow_pagespecific_header==0?"":"checked";
	$allow_pagespecific_template=$allow_pagespecific_template==0?"":"checked";
	$activate_useronreg=$default_user_activate==0?"":"checked";
	$default_mailverify=$default_mail_verify==0?"":"checked";
	$breadcrumb_submenu=$breadcrumb_submenu==0?"":"checked";
	$templates = getAvailableTemplates();
	
	global $ICONS;
	$globalform=<<<globalform
	<form name='admin_page_form' method='POST' action='./+admin&subaction=global'>
	<fieldset>
	<legend>{$ICONS['Global Settings']['small']}Global Settings</legend>
	<table style="width:100%">
	<tr>
	<td style="width:35%">Website Name :</td>
	<td style="width:65%"><input type="text" name='cms_title' value="$cms_title"></td>
	</tr>
	<tr>
	<td>Site Description :</td>
	<td><textarea style="width:98%" rows=10 cols=10 name='cms_desc' />$cms_desc</textarea></td>
	</tr>
	<tr>
	<td>Site Keywords (comma-separated) :</td>
	<td><input type="text" name='cms_keywords' value='$cms_keywords'></td>
	</tr>
	<tr>
	<td>Site Footer :</td>
	<td><textarea style="width:98%" rows=10 cols=10 name='cms_footer' />$cms_footer</textarea></td>
	</tr>
	<tr>
	<td>Default template :</td>
	<td><select name='default_template' >
globalform;

	
	for($i=0; $i<count($templates); $i++)
	{
		if($templates[$i]==DEF_TEMPLATE)
		$globalform.="<option value='".$templates[$i]."' selected >".ucwords($templates[$i])."</option>";
		else
		$globalform.="<option value='".$templates[$i]."' >".ucwords($templates[$i])."</option>";
	}

$globalform.=<<<globalform
	</select>
	</td>
	</tr>
	<tr>
	<td>Website Email :</td>
	<td><input type="text" name='cms_email' value='$cms_email'></td>
	</tr>
	<tr>
	<td>Upload Limit (bytes) :</td>
	<td><input type="text" name='upload_limit' value='$upload_limit'></td>
	</tr>
	<tr>
	<td>Site Reindex Frequency (days) :</td>
	<td><input type="text" name='reindex_frequency' value='$reindex_frequency'></td>
	</tr>
	<tr>
	<td>Allow Page-specific Headers ?</td>
	<td><input name='allow_page_header' type='checkbox' $allow_pagespecific_header></td>
	</tr>
	<tr>
	<td>Allow Page-specific Template ?</td>
	<td><input name='allow_page_template' type='checkbox' $allow_pagespecific_template></td>
	</tr>
	<tr>
	<td>Send Mail on Registration ?</td>
	<td><input name='send_mail_on_reg' type='checkbox' $default_mailverify></td>
	</tr>
	<tr>
	<td>Show Breadcrumbs Submenu ?</td>
	<td><input name='breadcrumb_submenu' type='checkbox' $breadcrumb_submenu></td>
	</tr>
	<tr>
	<td>Activate User On Registration ?</td>
	<td><input name='activate_useronreg' type='checkbox' $activate_useronreg></td>
	</tr>
	<tr>
	<td><input type='hidden' name='update_global_settings' /><input type='submit' value='Update' />
	<input type='button' value='Cancel' onclick="window.open('./+view','_top')" /></td>
	</tr>
	</table>
	</fieldset>
	</form>
globalform;
	return $globalform;
}
function templateManagementForm()
{
	$templates = getAvailableTemplates();
	$templatesList = "<select id='templates'>";
	
	foreach($templates as $template)
		$templatesList .= "<option value='" . $template . "'>" . $template . "</option>";
	$templatesList .= "</select>";
	global $ICONS;
	require_once("upload.lib.php");
	$form=<<<FORM
	<script type="text/javascript">
	function delconfirm(obj) {
		if(confirm("Are you sure want to delete '" + document.getElementById('templates').value + "' template?"))
		{
			document.getElementById("file").value="";
			obj.form.action += "uninstall&deltemplate=" + document.getElementById('templates').value;
			return true;
		}
		return false;
		
	}
	</script>
	<form name='template' method='POST' action='./+admin&subaction=template&subsubaction=' enctype="multipart/form-data">
	<fieldset>
	<legend>{$ICONS['Templates Management']['small']}Template Management</legend>
	Add new Template (select a ZIP file containing template): <input type='file' name='file' id='file'><input type='submit' name='btn_install' value='Upload' onclick='this.form.action+="install"'>
	<br/><br/>Delete Existing Template: {$templatesList}<input type='submit' name='btn_uninstall' value='Uninstall' onclick='return delconfirm(this);'>
	</fieldset>
	</form>
FORM;
	return $form;
}

function extension($file) {
	$start = strrpos($file,".");
	$len = strlen($file);
	return substr($file,$start,$len-$start);
}

function delDir($dirname) {
	if (is_dir($dirname))
		$dir_handle = opendir($dirname);
	if (!isset($dir_handle) || !$dir_handle)
		return false;
	while($file = readdir($dir_handle)) {
		if ($file != "." && $file != "..") {
			if (!is_dir($dirname."/".$file))
				unlink($dirname."/".$file);
			else
				delDir($dirname.'/'.$file); 		
		}
	}
	closedir($dir_handle);
	rmdir($dirname);
	return true;
}

function getSuggestions($pattern) {
	$suggestionsQuery = "SELECT IF(user_email LIKE \"$pattern%\", 1, " .
			"IF(`user_fullname` LIKE \"$pattern%\", 2, " .
			"IF(`user_fullname` LIKE \"% $pattern%\", 3, " .
			"IF(`user_email` LIKE \"%$pattern%\", 4, " .
			"IF(`user_fullname` LIKE \"%$pattern%\", 5, 6" .
			"))))) AS `relevance`,	`user_email`, `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE " .
			"  `user_activated`=1 AND(`user_email` LIKE \"%$pattern%\" OR `user_fullname` LIKE \"%$pattern%\" ) ORDER BY `relevance`";
//			echo $suggestionsQuery;
	$suggestionsResult = mysql_query($suggestionsQuery);

	$suggestions = array($pattern);

	while($suggestionsRow = mysql_fetch_row($suggestionsResult)) {
		$suggestions[] = $suggestionsRow[1] . ' - ' . $suggestionsRow[2];
	}

	return join($suggestions, ',');
}

function admin($pageid, $userid) {
	
	if(isset($_GET['doaction']) && $_GET['doaction'] == 'getsuggestions' && isset($_GET['forwhat'])) {
		if(strlen($_GET['forwhat']) >= 3) {
			echo getSuggestions($_GET['forwhat']);
			disconnect();
			exit();
		}
	}
	global $urlRequestRoot,$templateFolder,$cmsFolder,$ICONS;
    if(isset($_GET['indexsite'])) {
		global $cmsFolder;
		include("$cmsFolder/modules/search/admin/spider.php");
		if($_GET['indexsite'] == 1) {
			$serveruri=$_SERVER['SCRIPT_NAME'];
			$uri=substr($serveruri,0,stripos($serveruri,"index.php"));
			$site = "http://" . $_SERVER['HTTP_HOST'] . $uri . "home/";
			index_site($site, 0, -1, 'full', "", "+\n&", 0);
			displayinfo("Index for site created");
		} else {
			index_all();
		}
	}
	
	$result = mysql_fetch_array(mysql_query("SELECT `value` FROM `" . MYSQL_DATABASE_PREFIX . "global` WHERE `attribute` = 'reindex_frequency'"));
	if($result != NULL)
		$threshold = $result['value'];
	else
		$threshold = 30;
	$result = mysql_fetch_array(mysql_query("SELECT to_days(CURRENT_TIMESTAMP)-to_days(`indexdate`) AS 'diff' FROM `sites` WHERE `url` LIKE '%home%'"));
	
	if($result == NULL)
		displayinfo("It seems the site doesn't have index for the search to work. Click <a href='./+admin&indexsite=1'>here</a> to index the site.");
	else if($result['diff'] > $threshold)
		displayinfo("Your site index was created {$result['diff']} days before. Click <a href='./+admin&indexsite=2'>here</a> to reindex your site.");
	
	$quicklinks = <<<ADMINPAGE
	<fieldset>
	<legend>{$ICONS['Website Administration']['small']}Website Administration</legend>
	<a name='quicklinks'></a>
	<table class="iconspanel">
	<tr>
	<td><a href="./+admin&subaction=global">{$ICONS['Global Settings']['large']}<br/>Global Settings</a></td>
	<td><a href="./+admin&subaction=expert">{$ICONS['Site Maintenance']['large']}<br/>Site Maintenance</a></td>
	<td><a href="./+admin&subaction=template">{$ICONS['Templates Management']['large']}<br/>Templates Management</a></td>
	<td><a href="./+admin&subaction=email">{$ICONS['Email Registrants']['large']}<br/>Email Registrants</a></td>
	</tr>
	<tr>
	<td colspan=2><a href="./+admin&subaction=useradmin">{$ICONS['User Management']['large']}<br/>User Management</a></td>
	<td colspan=2><a href="./+admin&subaction=editgroups">{$ICONS['User Groups']['large']}<br/>Group Management</a></td>
	
	</tr>

	</table>
	</fieldset>
ADMINPAGE;
	if(isset($_GET['subaction'])) {
		require_once("email.lib.php");
		if($_GET['subaction'] == "email")
			return $quicklinks . displayEmail();
		else if($_GET['subaction'] == "openemail")
			return $quicklinks . displayEmail(escape($_GET['name']));
		else if($_GET['subaction'] == "emailsend") {
			sendEmail();
			return  $quicklinks . displayEmail(escape($_POST['emailtemplates']));
		}
		else if($_GET['subaction'] == "emailsave") {
			saveEmail();
			return  $quicklinks . displayEmail(escape($_POST['emailtemplates']));
		}
	}
        if(isset($_GET['subaction']) && $_GET['subaction']=='template')
	{ 
		
		if(isset($_GET['subsubaction']))
		{
			require_once("template.lib.php"); 
			$op=handleTemplateMgmt();
			if($op!="") return $op;
			else return $quicklinks.templateManagementForm();
		}
		else return $quicklinks.templateManagementForm();
	}
	global $sourceFolder;	
	if(!isset($_GET['subaction'])) return $quicklinks;
	require_once("users.lib.php");
	$op="";$ophead=""; $str="";
	if (((isset($_GET['subaction']) || isset($_GET['subsubaction']))) || (isset ($_GET['id'])) || (isset ($_GET['movePermId']))||(isset ($_GET['module']))) {
		if ($_GET['subaction'] == 'global' && isset($_POST['update_global_settings'])) updateGlobalSettings();
		
		else if ($_GET['subaction'] == 'useradmin'){ $op .= handleUserMgmt(); $ophead="{$ICONS['User Management']['small']}User Management"; }
		else if ($_GET['subaction'] == 'editgroups') {
			require_once("permission.lib.php");
			$pagepath = array();
			parseUrlDereferenced($pageid, $pagepath);
			$virtue = '';
			$maxPriorityGroup = getMaxPriorityGroup($pagepath, $userid, array_reverse(getGroupIds($userid)), $virtue);
			$modifiableGroups = getModifiableGroups($userid, $maxPriorityGroup);
			$op .= groupManagementForm($userid, $modifiableGroups, $pagepath);
			$ophead="{$ICONS['Group Management']['small']}Group Management";
		}
		else if ($_GET['subaction'] == 'reloadtemplates'){ $op .= reloadTemplates(); $ophead="{$ICONS['Templates Management']['small']}Reloading Templates"; }
		
		else if ($_GET['subaction'] == 'checkPerm'){ $op .= admin_checkFunctionPerms(); $ophead="{$ICONS['Access Permissions']['small']}Checking Permissions Consistency"; }
		elseif ($_GET['subaction'] == 'checkAdminUser'){ $op .= admin_checkAdminUser(); $ophead="Checking Administrator User"; }
		elseif ($_GET['subaction'] == 'checkAdminPerms'){ $op .= admin_checkAdminPerms(); $ophead="Checking Administrator Permissions"; }
		elseif (($_GET['subaction'] == 'changePermRank')){ $op .= admin_changePermRank(); $ophead="{$ICONS['Access Permissions']['small']}Changing Permissions Rank"; }
		elseif (($_GET['subaction'] == 'editprofileform') ||
			(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'editprofileform'))
			{ $op .= admin_editProfileForm(); $ophead="{$ICONS['User Profile']['small']}Edit User Profile Form"; }
		elseif (($_GET['subaction']) == 'viewsiteregistrants' || $_GET['subaction'] == 'editsiteregistrants') 
			$op .= admin_editRegistrants(); 
		elseif (isset ($_GET['id'])) $op .= admin_userAdmin();
		elseif (isset ($_GET['movePermId'])){ $op .= admin_changePermRank(); $ophead="{$ICONS['Access Permissions']['small']}Changing Permissions Rank"; }
		elseif (isset ($_GET['module'])){ $op .= admin_changePermRank(escape($_GET['module'])); $ophead="{$ICONS['Access Permissions']['small']}Changing Permissions Rank for module '".escape($_GET['module'])."'"; }
	}
	if($op!="")
	{
		$op ="<fieldset><legend>$ophead</legend>$op</fieldset>";
	}
	
	if($_GET['subaction']=='global')
	 $str .= globalSettingsForm();
	else if($_GET['subaction']=='editgroups') {
		//do nothing so that "expert only" doesn't comes up
	}
	else if($_GET['subaction']=='useradmin')
	{
		
		$op .= userManagementForm();
	}
	else 
	{
		$str .= "<fieldset><legend>{$ICONS['Site Maintenance']['small']}Experts Only</legend>";
		$str .= '<a href="./+admin&subaction=checkPerm">Check Permission List</a><br />';
		$str .= '<a href="./+admin&subaction=checkAdminUser">Check Admin User</a><br />';
		$str .= '<a href="./+admin&subaction=checkAdminPerms">Check Admin Perms</a><br />';
		$str .= '<a href="./+admin&subaction=changePermRank">Change Perm Ranks</a><br />';
		$str .= '<a href="./+admin&subaction=editprofileform">Edit User Profile Form</a><br />';
		$str .= '<a href="./+admin&subaction=reloadtemplates">Reload Templates</a><br />';
		$str .= '<a href="./+admin&indexsite=2">Reindex Site for Searching</a></br/></fieldset>';
		
		
	}
	
	return $quicklinks.$str.$op;

}

function updateGlobalSettings()
{
	$global=array();
	$global['allow_pagespecific_header']=isset($_POST['allow_page_header'])?1:0;
	$global['allow_pagespecific_template']=isset($_POST['allow_page_template'])?1:0;
	$global['default_user_activate']=isset($_POST['activate_useronreg'])?1:0;
	$global['default_mail_verify']=isset($_POST['send_mail_on_reg'])?1:0;
	$global['breadcrumb_submenu']=isset($_POST['breadcrumb_submenu'])?1:0;

	$global['cms_title']=escape($_POST['cms_title']);
	$global['default_template']=escape($_POST['default_template']);
	$global['cms_email']=escape($_POST['cms_email']);
	$global['upload_limit']=escape($_POST['upload_limit']);
	$global['reindex_frequency']=escape($_POST['reindex_frequency']);
	$global['cms_desc']=escape($_POST['cms_desc']);
	$global['cms_keywords']=escape($_POST['cms_keywords']);
	$global['cms_footer']=escape($_POST['cms_footer']);

	setGlobalSettings($global);

	displayinfo("Global Settings successfully updated! Changes will come into effect on next page reload.");
	
}

function admin_checkFunctionPerms() {
	global $sourceFolder;
	$returnStr="";
	//1) Check all available classes (modules)
	if ($handle = opendir($sourceFolder . '/modules')) {
		while (false !== ($file = readdir($handle))) {
			$list[] = $file;
		}
		closedir($handle);
	}
	foreach ($list as $temp) {
		if (strpos($temp, '.lib.php')==strlen($temp)-8) {
			$moduleArray[] = str_replace('.lib.php', '', $temp);
		}
	}
	$moduleList = "";
	foreach ($moduleArray as $module) {
		$moduleList .= $module . ", ";
	}
	$moduleList .= "";	

	$returnStr.="<br/>The following modules/classes exist in the file system:<br>$moduleList";
	$moduleList = "";

	//	2) See, if all their functions actionView(), actionEdit(), actionX exist
	//in the perms table or not (and create also). If
	//not, give option to create that permission.

	global $sourceFolder;
	global $moduleFolder;
	foreach ($moduleArray as $module) {
		$perm = array ();
		reset($perm);
		$i = 0;
		if (($module != 'forum') && ($module != 'poll') && ($module != 'contest')/* && ($module != 'gallery')*/) {

	
			require_once ($sourceFolder . "/" . $moduleFolder . "/" . $module . ".lib.php");

			$functionArray = get_class_methods($module);
	
			if($functionArray==NULL)  //means something's wrong, probably the class is not defined properly
			{
				$returnStr.="<br/><b>Please check the Class definition of $module. It may have undefined functions. Please define the functions or declare the class as an abstract class</b>";
				continue;
			}
			foreach ($functionArray as $method) {
				if ((substr($method, 0, 6)) == 'action') {
					$permission = str_replace('action', "", $method);
					$permission = strtolower($permission);
					$perm[$i] = $permission;
					$i = $i +1;
				}
			}

			$permList = "";
			foreach ($perm as $permElements) {
				$permList .= $permElements . ", ";
			}
			$returnStr.="<br/>The following methods/functions/actions exist in the filesystem class for $module:<br> $permList";
			$perm[] = 'create';
			$permExists = "";
			$i = 0;

			foreach ($perm as $permission) {
				$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module`='$module' AND `perm_action`='$permission'";
				$result = mysql_query($query);
				if (mysql_num_rows($result) > 0) {
					if ($i == 1)
						$permExists .= ", "; // Just to append ,(comma) after every perm but last
					$permExists .= $permission;
					$i = 1;
				} else {
					$returnStr.="<br/><b>$permission DOES NOT exist for $module but will be created</b><br>";
					$query = "SELECT MAX(perm_id) as MAX FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist`";
					$result = mysql_query($query) or die(mysql_error());
					$row = mysql_fetch_assoc($result);
					$permid = $row['MAX'] + 1;
					$query = "SELECT MAX(perm_rank) as MAX FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module`='$module'";
					$result = mysql_query($query) or die(mysql_error());
					$row = mysql_fetch_assoc($result);
					$permrank = $row['MAX'] + 1;
					$desc = $permission . " the " . $module;
					$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "permissionlist`(`perm_id` ,`page_module` ,`perm_action` ,`perm_text` ,`perm_rank` ,`perm_description`)VALUES ('$permid', '$module', '$permission', '$permission', '$permrank', '$desc') ";
					$result = mysql_query($query) or die(mysql_error());
					if (mysql_affected_rows())
						displayinfo("$permission has been created for $module");
				}
			}

			$permExists .= ".";//Adding the last period.
			$returnStr.="<br/>The following permissions exist in database for $module :<br>$permExists";
			 
		}

	}

	//3) See if any extra option exists in the database, if it does, warn the user.

	foreach ($moduleArray as $module) {
		if (($module != 'forum') && ($module != 'poll') && ($module!='contest')/* && ($module != 'gallery')*/) {
			require_once ($sourceFolder . "/" . $moduleFolder . "/" . $module . ".lib.php");
			$class = new $module ();
			$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module`='$module'";
			$result = mysql_query($query);
			while ($tempres = mysql_fetch_assoc($result)) {

				$permName = ucfirst($tempres['perm_action']);
				$method = "action" . $permName;

				if (!(method_exists($class, $method)))
					$returnStr.="<br/>Permission $method, perm id = $tempres[perm_id] exists in database but not in class $module";

			}

		}
	}
	return $returnStr;
}
//4) See if any user with the name admin exists or not. If it does not,
//create it and give it a random and display all required information,

function admin_checkAdminUser() {
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_name`='admin'";
	$result = mysql_query($query);
	if (mysql_num_rows($result) > 0) {
		displayinfo("User \"Admin\" exists in database.");
	} else {
		$query = "SELECT MAX(user_id) as MAX FROM `" . MYSQL_DATABASE_PREFIX . "users` ";
		$result = mysql_query($query) or die(mysql_error() . "check.lib L:141");
		$row = mysql_fetch_assoc($result);
		$uid = $row['MAX'] + 1;
		$passwd = rand();
		$adminPasswd = md5($passwd);
		$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "users`( `user_id` ,`user_name` ,`user_email` ,`user_fullname` ,`user_password`  ,`user_activated`)VALUES ( $uid , 'admin', 'admin@cms.org', 'Administrator', '$adminPasswd', '1')";
		
		$result = mysql_query($query) or die(mysql_error());
		if (mysql_affected_rows() > 0) {
			displayinfo("User Admin has been created with email admin@cms.org and password as $passwd");
		} else
			displayerror("Failed to create user Admin");
	}
}

function admin_checkAdminPerms()
/*
 *
 * 5) See if the admin user has all perms at page 0 or not. If not, give him
 *    all perms and inform him
 */
 {
	$returnStr="";
	$str="";
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_name`='admin' ";
	$result = mysql_query($query);
	if (mysql_num_rows($result) > 0) {
		$temp = mysql_fetch_array($result);
		$user_Id = $temp['user_id'];
		$query1 = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist`";
		$result1 = mysql_query($query1);
		while ($temp1 = mysql_fetch_assoc($result1)) {
			foreach ($temp1 as $var => $val) {
				if ($var == 'perm_id') {
					$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "userpageperm` WHERE `perm_type`='user' AND `usergroup_id`=$user_Id AND `page_id`=0 AND `perm_id`=$val AND `perm_permission`='Y'";
					$result = mysql_query($query) or die(mysql_error());
					if (!mysql_num_rows($result)) {
						$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "userpageperm` (`perm_type`,`page_id`,`usergroup_id`,`perm_id`,`perm_permission`) VALUES ('user','0','$user_Id','$val','Y')";
						$result2 = mysql_query($query);
						if (mysql_affected_rows())
							$returnStr.="\n<br>User Admin userId=$user_Id has been allotted permission $temp1[perm_action] of module $temp1[page_module] over page 0";
						else
							$returnStr.="\n<br>Failed to create permission $temp1[perm_action] of module $temp1[page_module] over page 0 for User Admin userId=$user_Id";
					} else {
						$str .= "";
						$str .= "\n<tr><td>" . $temp1['page_module'] . "</td><td>" . $temp1['perm_action'] . "</td></tr>";
					}
				}
			}
		}
		if ($str != '')
			$returnStr.="The following permissions exist for user admin: <table border=\"1\"><tr><th>Module</th><th>Permission</th></tr>" .$str. "</table>";

	} else {
		$returnStr.=admin_checkAdminUser();
		$returnStr.=admin_checkAdminPerms();
	}
	return $returnStr;
}


/*
 * 7) User management: List of all users, ability to edit everything about them,
 *  ability to activate users, ability to create users
 *
 */

function admin_userAdmin() {
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` ORDER BY `user_id` ASC";
	$result = mysql_query($query) or die(mysql_error() . "admin.lib L192");
	$table .=<<<HEAD
	<script language="javascript">
	function checkDelete(butt,userDel,userId)
							{
								 if(confirm('Are you sure you want to delete '+userDel+'?'))
								{


									window.location+="&id="+userId+"&userDel="+userDel;

								}
								else return false;
							}
					    </script>
	<table border="1">
	<td id="user_id">User Id</td>
	<td id="user_name">User Name</td>
	<td id="user_email">User Email</td>
	<td id="user_fullname">Full Name</td>
	<td id="user_password">Password Hash(MD5)</td>
	<td id="user_regdate">Registration date</td>
	<td id="user_lastlogin">Last Login</td>
	<td id="user_activated">Active</td>
	<td id="user_delete">Delete</td>
HEAD;
	$links =<<<LINKS
	<input type="button" onclick="window.open('./+admin&id=search','_top')" value="Search User" />
	<input type="button" onclick="window.open('./+admin&id=new','_top')" value="New User" /><hr/>

LINKS;
	$count = count($_GET);
	if ((!(isset ($_GET['id'])))) {
		while ($temp = mysql_fetch_assoc($result)) {
			$table .= "<tr>";
			foreach ($temp as $var => $val) {
				$table .= "<td><a style=\"cursor:pointer;\" onclick=\"window.location='./+admin&id=$temp[user_id]'\"> $val</a></td>";
			}
						$table.="<td><input type=\"Button\" name=\"deleteUser\" value=\"Delete\" onclick=\"return checkDelete(this,'".$temp['user_name']."','".$temp['user_id']."');\"></td>";
			$table .= "</tr> ";
		}
		$table .= "</table>";
		return  $links.$table;
	}
	if ($_POST['userAdminAction'] == 'Create') {
		foreach ($_POST as $var => $val) {
			$$var = $val;
			{
				if ($val == '') {
					if ((($var == 'user_regdate')) || (($var == 'user_lastlogin')) || ($var == 'user_activated'));
					else {
						displayerror('Please enter ' . $var . ' <a href="./+admin&id=new">Go Back</a>');
						$err = 1;
					}
				}
			}
		}
		if ($err) {
			return null;
		}
		$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "users` (`user_id` ,`user_name` ,`user_email` ,`user_fullname` ,`user_password` ,`user_regdate` ,`user_lastlogin` ,`user_activated`)VALUES ('$user_id' ,'$user_name' ,'$user_email' ,'$user_fullname' , MD5('$user_password') ,CURRENT_TIMESTAMP , '', '$user_activated')";
		$result = mysql_query($query) or die(mysql_error());
		if (mysql_affected_rows()) {
			displayinfo("User Created");
			$user_Id = $_POST['user_id'];
		} else {
			displayerror("Failed to create user");
			return null;
		}

	}
	if(isset($_GET['userDel'])){

//		displaywarning("You are going to delete user $_GET[userDel] <a href=\"./+admin&userDel=$_GET[userDel]&id=$_GET[id]&confirmed\"><I>continue</I></a> Cancel");
		$userId = escape($_GET['id']);
		$userName = escape($_GET['userDel']);
		$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = $userId AND `user_name`='$userName'";
		$resultDel=mysql_query($query) or displayerror(mysql_error());
		if($resultDel)displayinfo("User $userId $userName deleted");
		else displayerror("$resultDel Failed to delete user $userName");
		return null;


	}
	if ((isset ($_GET['id']))) {
		if (($_GET['id'] != 'new') && ($_GET['id'] != 'search') && ($_POST['userAdminAction'] != 'Search')&&(!isset($_GET['userDel']))) {

			$user_Id = escape($_GET['id']);
			if ($user_Id == '')
				$user_Id = $_POST['user_id'];
			$user_Id = $user_Id;
			$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_id`=$user_Id";
			{
				$result = mysql_query($query);
				if (mysql_num_rows($result) > 0) {
					$temp = mysql_fetch_assoc($result);
					$readonly = "readonly";
					$submitValue = "Save";
					$chngPass =<<<CHG
<td><INPUT type="checkbox" name="changePassword" value="">Change Password?</td>
CHG;
					foreach ($temp as $var => $val) {
						$$var = $val;
					}
				} else {
					displayerror("User id $user_Id Does not exist");
					return null;
				}
			}
			if (isset ($_POST['userAdminAction'])) {
				foreach ($_POST as $var => $val) {
					$$var = $val;
				}
				if (isset ($_POST['changePassword'])) {
					$user_password = md5($user_password);
					$chngPasswd = "`user_password`='$user_password',";
				}
				$querySave = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users` SET `user_name`='$user_name',`user_email`='$user_email',`user_fullname`='$user_fullname',$chngPasswd`user_activated`='$user_activated',`user_loginmethod`='$user_loginmethod' WHERE `user_id`=$user_id";
				$resultSave = mysql_query($querySave) or die(mysql_error());
				if (!mysql_error())
					displayinfo("User data saved");
				else
					displayerror("Failed to save data");
			}
		}
		elseif ($_GET['id'] == 'search') {
			$readonly = "";
			$submitValue = "Search";
			$user_Id = "search";
			$user_activated = 1;
			displayinfo("Search uses AND operator");

		}
		elseif ($_GET['id'] == 'new') {
			$readonly = "readonly";
			$submitValue = "Create";
			$query = "SELECT MAX(user_id) AS MAX FROM `" . MYSQL_DATABASE_PREFIX . "users`";
			$result = mysql_query($query) or die(mysql_error() . "check.lib L:266");
			$row = mysql_fetch_assoc($result);
			$user_id = $row['MAX'] + 1;
			$user_activated = 0;
		}
		if ($_POST['userAdminAction'] == 'Search') {

			$i = 0;
			$readonly = "";
			$submitValue = "Search";
			foreach ($_POST as $var => $val) {
				if ($val == 'Search');
				else {
					if ($val != '') {
						if ($i == 1)
							$string .= " AND ";
						$val = $val;
						$$var = $val;
						$string .= "`$var` LIKE CONVERT( _utf8 '%$val%'USING latin1 ) ";
						$i = 1;
					}
				}
			}
			$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE $string ";
			$resultSearch = mysql_query($query);
			if (mysql_num_rows($resultSearch) > 0) {
				$num = mysql_num_rows($resultSearch);
				displayinfo("$num results found");
				while ($temp = mysql_fetch_assoc($resultSearch)) {
					$table .= "<tr>";
					foreach ($temp as $var => $val) {
						$table .= "<td><a style=\"cursor:pointer;\" onclick=\"window.location='./+admin&id=$temp[user_id]'\"> $val</a></td>";

					}
					$table.="<td><input type=\"Button\" name=\"deleteUser\" value=\"Delete\" onclick=\"return checkDelete(this,'".$temp[user_name]."','".$temp[user_id]."');\"></td>";
					$table .= "</tr> ";
				}
				$table .= "</table>";
				return $table . $links;
			} else
				displayerror("User not found!");
			$user_Id = "search";
		}
		if ($user_activated == 0)
			$user_active = 1;
		else
			$user_active = 0;
		$editUserForm =<<<FORM
<form method="POST" action="./+admin&id=$user_Id">
<br />
<table>
<input type="hidden" id="userAdminAction" name="userAdminAction" value="$submitValue">

<tr><td>User Id </td><td><input type="text" maxlength="11" id="user_id" name="user_id" value="$user_id" $readonly></td></tr>
<tr><td>User Name </td><td><input type="text" maxlength="100" id="user_name" name="user_name" value="$user_name"></td></tr>
<tr><td>User Email </td><td><input type="text" maxlength="100" id="user_email" name="user_email" value="$user_email"></td></tr>
<tr><td>User FullName </td><td><input type="text" maxlength="100" id="user_fullname" name="user_fullname" value="$user_fullname"></td></tr>
<tr><td>User Password </td><td><input type="password" maxlength="100" id="user_password" name="user_password" value="$user_password"></td>$chngPass</tr>
<tr><td>User Reg Date </td><td><input type="text" maxlength="100" id="user_regdate" name="user_regdate" value="$user_regdate" $readonly></td></tr>
<tr><td>Last Login </td><td><input type="text" maxlength="100" id="user_lastlogin" name="user_lastlogin" value="$user_lastlogin" $readonly></td></tr>
<tr><td>User Activated </td><td><select id="user_activated" name="user_activated" style="width: 10mm" >
<option selected="selected">$user_activated</option>
<option>$user_active</option>
</select>
</td></tr>
<tr><td>User Login Method </td><td><select id="user_loginmethod" name="user_loginmethod" style="width: 60mm" >
<option selected="selected">ldap</option>
<option>imap</option>
<option>ads</option>
<option>db</option>
</select>
</td></tr>
<tr><td><input type="submit" value="$submitValue" > <input type="reset"></td></tr></table>
</form>
FORM;
		return $editUserForm . $links;
	}
}


/*
 * 8) Ability to change perm ranks (like page move up and move )
 *
 * */

function admin_changePermRank($module="") {
	require_once("tbman_executer.lib.php");

	$pv = $_POST;
	$pv['querystring'] = "SELECT * FROM `". MYSQL_DATABASE_PREFIX ."permissionlist`";
	$table = new tbman_executer($pv);
	$table->formaction="./+admin&subaction=changePermRank";
	return $table->execute();
	
}


function admin_editProfileForm() {
	include_once('profile.lib.php');
	return getProfileFormEditForm();
}

function admin_editRegistrants() {
	include_once('profile.lib.php');
	return getProfileViewRegistrantsForm();
}


function groupManagementForm($currentUserId, $modifiableGroups, &$pagePath) {
	require_once("group.lib.php");
	global $ICONS;
	global $urlRequestRoot, $cmsFolder, $templateFolder, $moduleFolder,$sourceFolder;
	$scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
	$imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";

	/// Parse any get variables, do necessary validation and stuff, so that we needn't check inside every if
	$groupRow = $groupId = $userId = null;
	$subAction = ''; //isset($_GET['subaction']) ? $_GET['subaction'] : '';
	if ((isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'editgroup' && isset($_GET['groupname'])) || (isset($_POST['btnEditGroup']) && isset($_POST['selEditGroups'])))
		$subAction = 'showeditform';
	elseif(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'associateform')
		$subAction = 'associateform';
	elseif (isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'deleteuser' && isset($_GET['groupname']) && isset($_GET['useremail']))
		$subAction = 'deleteuser';
	elseif (isset($_POST['btnAddUserToGroup']))
		$subAction = 'addusertogroup';
	elseif (isset($_POST['btnSaveGroupProperties']))
		$subAction = 'savegroupproperties';
	elseif (isset($_POST['btnEditGroupPriorities']) || (isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'editgrouppriorities'))
		$subAction = 'editgrouppriorities';

	if(isset($_POST['selEditGroups']) || isset($_GET['groupname'])) {
		$groupRow = getGroupRow( isset($_POST['selEditGroups']) ? escape($_POST['selEditGroups']) : escape($_GET['groupname']) );
		$groupId = $groupRow['group_id'];
		if($subAction != 'editgrouppriorities' && (!$groupRow || !$groupId || $groupId < 2)) {
			displayerror('Error! Invalid group requested.');
			return ;
		}

		if(!is_null($groupId)) {
			if($modifiableGroups[count($modifiableGroups) - 1]['group_priority'] < $groupRow['group_priority']) {
				displayerror('You do not have the permission to modify the selected group.');
				return '';
			}
		}
	}
	if(isset($_GET['useremail'])) {
		$userId = getUserIdFromEmail($_GET['useremail']);
	}

	if($subAction != 'editgrouppriorities' && (isset($_GET['subaction']) && $_GET['subaction'] == 'editgroups' && !is_null($groupId))) {
		if ($subAction == 'deleteuser') {
			if($groupRow['form_id'] != 0) {
				displayerror('The group is associated with a form. To remove a user, use the edit registrants in the assoicated form.');
			}
			elseif (!$userId) {
				displayerror('Unknown E-mail. Could not find a registered user with the given E-mail Id');
			}
			else {
				$deleteQuery = 'DELETE FROM `' . MYSQL_DATABASE_PREFIX . 'usergroup` WHERE `user_id` = ' . $userId . ' AND `group_id` = ' . $groupId;
				$deleteResult = mysql_query($deleteQuery);
				if(!$deleteResult || mysql_affected_rows() != 1) {
					displayerror('Could not delete user with the given E-mail from the given group.');
				}
				else {
					displayinfo('Successfully removed user from the current group');

					if($userId == $currentUserId) {
						$virtue = '';
						$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
						$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
					}
				}
			}
		}
		elseif ($subAction == 'savegroupproperties' && isset($_POST['txtGroupDescription'])) {
			$updateQuery = "UPDATE `" . MYSQL_DATABASE_PREFIX . "groups` SET `group_description` = '".escape($_POST['txtGroupDescription'])."' WHERE `group_id` = $groupId";
			$updateResult = mysql_query($updateQuery);
			if (!$updateResult) {
				displayerror('Could not update database.');
			}
			else {
				displayinfo('Changes to the group have been successfully saved.');
			}
			$groupRow = getGroupRow($groupRow['group_name']);
		}
		elseif ($subAction == 'addusertogroup' && isset($_POST['txtUserEmail']) && trim($_POST['txtUserEmail']) != '') {
			if($groupRow['form_id'] != 0) {
				displayerror('The selected group is associated with a form. To add a user, register the user to the form.');
			}
			else {
				$passedEmails = explode(',', escape($_POST['txtUserEmail']));

				for($i = 0; $i < count($passedEmails); $i++) {
					$hyphenPos = strpos($passedEmails[$i], '-');
					if ($hyphenPos >= 0) {
						$userEmail = trim(substr($passedEmails[$i], 0, $hyphenPos - 1));
					}
					else {
						$userEmail = escape($_POST['txtUserEmail']);
					}

					$userId = getUserIdFromEmail($userEmail);
					if(!$userId || $userId < 1) {
						displayerror('Unknown E-mail. Could not find a registered user with the given E-mail Id');
					}

					if(!addUserToGroupName($groupRow['group_name'], $userId)) {
						displayerror('Could not add the given user to the current group.');
					}
					else {
						displayinfo('User has been successfully inserted into the given group.');
					}
				}
			}
		}
		elseif ($subAction == 'associateform') {
			if(isset($_POST['btnAssociateGroup'])) {
				$pageIdArray = array();
				$formPageId = parseUrlReal(escape($_POST['selFormPath']), $pageIdArray);
				if($formPageId <= 0 || getPageModule($formPageId) != 'form') {
					displayerror('Invalid page selected! The page you selected is not a form.');
				}
				elseif (!getPermissions($currentUserId, $formPageId, 'editregistrants', 'form'))
					displayerror('You do not have the permissions to associate the selected form with a group.');
				else {
					$formModuleId = getModuleComponentIdFromPageId($formPageId, 'form');
					require_once("$sourceFolder/$moduleFolder/form.lib.php");

					if(isGroupEmpty($groupId) || form::getRegisteredUserCount($formModuleId) == 0) {
						associateGroupWithForm($groupId, $formModuleId);
						$groupRow = getGroupRow($groupRow['group_name']);
					}
					else
						displayerror('Both the group and the form already contain registered users, and the group cannot be associated with the selected form.');
				}
			}
			elseif(isset($_POST['btnUnassociateGroup'])) {
				if($groupRow['form_id'] <= 0) {
					displayerror('The selected group is currently not associated with any form.');
				}
				elseif(!getPermissions($currentUserId, getPageIdFromModuleComponentId('form', $groupRow['form_id']), 'editregistrants', 'form')) {
					displayerror('You do not have the permissions to unassociate the form from this group.');
				}
				else {
					unassociateFormFromGroup($groupId);
					$virtue = '';
					$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
					$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
					$groupRow = getGroupRow($groupRow['group_name']);
				}
			}
		}

		if($modifiableGroups[count($modifiableGroups) - 1]['group_priority'] < $groupRow['group_priority']) {
			displayerror('You do not have the permission to modify the selected group.');
			return '';
		}

		$usersTable = '`' . MYSQL_DATABASE_PREFIX . 'users`';
		$usergroupTable = '`' . MYSQL_DATABASE_PREFIX . 'usergroup`';
		$userQuery = "SELECT `user_email`, `user_fullname` FROM $usergroupTable, $usersTable WHERE `group_id` =  $groupId AND $usersTable.`user_id` = $usergroupTable.`user_id` ORDER BY `user_email`";
		$userResult = mysql_query($userQuery);
		if(!$userResult) {
			displayerror('Error! Could not fetch group information.');
			return '';
		}
	
		$userEmails = array();
		$userFullnames = array();
		while($userRow = mysql_fetch_row($userResult)) {
			$userEmails[] = $userRow[0];
			$userFullnames[] = $userRow[1];
		}
		
		$groupEditForm = <<<GROUPEDITFORM
			<h2>Group '{$groupRow['group_name']}' - '{$groupRow['group_description']}'</h2><br />
			<fieldset style="padding: 8px">
				<legend>{$ICONS['User Groups']['small']}Group Properties</legend>
				<form name="groupeditform" method="POST" action="./+admin&subaction=editgroups&groupname={$groupRow['group_name']}">
					Group Description: <input type="text" name="txtGroupDescription" value="{$groupRow['group_description']}" />
					<input type="submit" name="btnSaveGroupProperties" value="Save Group Properties" />
				</form>
			</fieldset>

			<br />
			<fieldset style="padding: 8px">
				<legend>{$ICONS['User Groups']['small']}Existing Users in Group:</legend>
GROUPEDITFORM;

		$userCount = mysql_num_rows($userResult);
		global $urlRequestRoot, $cmsFolder, $templateFolder,$sourceFolder;
		$deleteImage = "<img src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/edit-delete.png\" alt=\"Remove user from the group\" title=\"Remove user from the group\" />";

		for($i = 0; $i < $userCount; $i++) {
			$isntAssociatedWithForm = ($groupRow['form_id'] == 0);
			if($isntAssociatedWithForm)
				$groupEditForm .= '<a onclick="return confirm(\'Are you sure you wish to remove this user from this group?\')" href="./+admin&subaction=editgroups&subsubaction=deleteuser&groupname=' . $groupRow['group_name'] . '&useremail=' . $userEmails[$i] . '">' . $deleteImage . "</a>";
			$groupEditForm .= " {$userEmails[$i]} - {$userFullnames[$i]}<br />\n";
		}

		$associateForm = '';
		if($groupRow['form_id'] == 0) {
			$associableForms = getAssociableFormsList($currentUserId, !isGroupEmpty($groupId));
			$associableFormCount = count($associableForms);
			$associableFormsBox = '<select name="selFormPath">';
			for($i = 0; $i < $associableFormCount; ++$i) {
				$associableFormsBox .= '<option value="' . $associableForms[$i][2] . '">' . $associableForms[$i][1] . ' - ' . $associableForms[$i][2] . '</option>';
			}
			$associableFormsBox .= '</select>';
			$associateForm = <<<GROUPASSOCIATEFORM

			Select a form to associate the group with: $associableFormsBox
			<input type="submit" name="btnAssociateGroup" value="Associate Group with Form" />
GROUPASSOCIATEFORM;
		}
		else {
			$associatedFormPageId = getPageIdFromModuleComponentId('form', $groupRow['form_id']);
			$associateForm = 'This group is currently associated with the form: ' . getPageTitle($associatedFormPageId) . ' (' . getPagePath($associatedFormPageId) . ')<br />' .
					'<input type="submit" name="btnUnassociateGroup" value="Unassociate" />';
		}

		$groupEditForm .= '</fieldset>';
		if($groupRow['form_id'] == 0) {
			$groupEditForm .= <<<GROUPEDITFORM
				<br />
				<fieldset style="padding: 8px">
					<legend>{$ICONS['Add']['small']}Add Users to Group</legend>
					<form name="addusertogroup" method="POST" action="./+admin&subaction=editgroups&groupname={$groupRow['group_name']}">
						Email ID: <input type="text" name="txtUserEmail" id="txtUserEmail" value="" style="width: 256px" autocomplete="off" />
						<div id="suggestionDiv" class="suggestionbox"></div>

						<script language="javascript" type="text/javascript" src="$scriptsFolder/ajaxsuggestionbox.js"></script>
						<script language="javascript" type="text/javascript">
						<!--
							var addUserBox = new SuggestionBox(document.getElementById('txtUserEmail'), document.getElementById('suggestionDiv'), "./+admin&doaction=getsuggestions&forwhat=%pattern%");
							addUserBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
						-->
						</script>

						<input type="submit" name="btnAddUserToGroup" value="Add User to Group" />
					</form>
				</fieldset>
GROUPEDITFORM;
		}
		$groupEditForm .= <<<GROUPEDITFORM
			<br />
			<fieldset style="padding: 8px">
				<legend>{$ICONS['Group Associate Form']['small']}Associate With Form</legend>
				<form name="groupassociationform" action="./+admin&subaction=editgroups&subsubaction=associateform&groupname={$groupRow['group_name']}" method="POST">
					$associateForm
				</form>
			</fieldset>
GROUPEDITFORM;

		return $groupEditForm;
	}

	if ($subAction == 'editgrouppriorities') {
		$modifiableCount = count($modifiableGroups);
		$userMaxPriority = $maxPriorityGroup = 1;
		if($modifiableCount != 0) {
			$userMaxPriority = max($modifiableGroups[0]['group_priority'], $modifiableGroups[$modifiableCount - 1]['group_priority']);
			$maxPriorityGroup = $modifiableGroups[0]['group_priority'] > $modifiableGroups[$modifiableCount - 1]['group_priority'] ? $modifiableGroups[0]['group_id'] : $modifiableGroups[$modifiableCount - 1]['group_id'];
		}

		if(isset($_GET['dowhat']) && !is_null($groupId)) {
			if($_GET['dowhat'] == 'incrementpriority' || $_GET['dowhat'] == 'decrementpriority') {
				shiftGroupPriority($currentUserId, $groupRow['group_name'], $_GET['dowhat'] == 'incrementpriority' ? 'up' : 'down', $userMaxPriority, true);
			}
			elseif($_GET['dowhat'] == 'movegroupup' || $_GET['dowhat'] == 'movegroupdown') {
				shiftGroupPriority($currentUserId, $groupRow['group_name'], $_GET['dowhat'] == 'movegroupup' ? 'up' : 'down', $userMaxPriority, false);
			}
			elseif($_GET['dowhat'] == 'emptygroup') {
				emptyGroup($groupRow['group_name']);
			}
			elseif($_GET['dowhat'] == 'deletegroup') {
				if(deleteGroup($groupRow['group_name'])) {
					$virtue = '';
					$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
					$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
				}
			}

			$modifiableGroups = reevaluateGroupPriorities($modifiableGroups);
		}
		elseif(isset($_GET['dowhat']) && $_GET['dowhat'] == 'addgroup') {
			if(isset($_POST['txtGroupName']) && isset($_POST['txtGroupDescription']) && isset($_POST['selGroupPriority'])) {
				$existsQuery = 'SELECT `group_id` FROM `' . MYSQL_DATABASE_PREFIX . "groups` WHERE `group_name` = '".escape($_POST['txtGroupName'])."'";
				$existsResult = mysql_query($existsQuery);
				if(trim($_POST['txtGroupName']) == '') {
					displayerror('Cannot create a group with an empty name. Please type in a name for the new group.');
				}
				elseif(mysql_num_rows($existsResult) >= 1) {
					displayerror('A group with the name you specified already exists.');
				}
				else {
					$idQuery = 'SELECT MAX(`group_id`) FROM `' . MYSQL_DATABASE_PREFIX . 'groups`';
					$idResult = mysql_query($idQuery);
					$idRow = mysql_fetch_row($idResult);
					$newGroupId = 2;
					if(!is_null($idRow[0])) {
						$newGroupId = $idRow[0] + 1;
					}

					$newGroupPriority = 1;
					if($_POST['selGroupPriority'] <= $userMaxPriority && $_POST['selGroupPriority'] > 0) {
						$newGroupPriority = escape($_POST['selGroupPriority']);
					}

					$addGroupQuery = 'INSERT INTO `' . MYSQL_DATABASE_PREFIX . 'groups` (`group_id`, `group_name`, `group_description`, `group_priority`) ' .
							"VALUES($newGroupId, '".escape($_POST['txtGroupName'])."', '".escape($_POST['txtGroupDescription'])."', $newGroupPriority)";
					$addGroupResult = mysql_query($addGroupQuery);
					if($addGroupResult) {
						displayinfo('New group added successfully.');

						if(isset($_POST['chkAddMe'])) {
							$insertQuery = 'INSERT INTO `' . MYSQL_DATABASE_PREFIX . "usergroup`(`user_id`, `group_id`) VALUES ($currentUserId, $newGroupId)";
							if(!mysql_query($insertQuery)) {
								displayerror('Error adding user to newly created group: ' . $insertQuery . '<br />' . mysql_query());
							}
						}
						$virtue = '';
						$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
						$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
					}
					else {
						displayerror('Could not run MySQL query. New group could not be added.');
					}
				}
			}

			$modifiableGroups = reevaluateGroupPriorities($modifiableGroups);
		}

		$modifiableCount = count($modifiableGroups);
		if($modifiableGroups[0]['group_priority'] < $modifiableGroups[$modifiableCount - 1]['group_priority']) {
			$modifiableGroups = array_reverse($modifiableGroups);
		}
		$previousPriority = $modifiableGroups[0]['group_priority'];
		global $cmsFolder, $urlRequestRoot, $moduleFolder, $templateFolder,$sourceFolder;
		$iconsFolderUrl = "$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16";
		$moveUpImage = '<img src="' . $iconsFolderUrl . '/actions/go-up.png" title="Increment Group Priority" alt="Increment Group Priority" />';
		$moveDownImage = '<img src="' . $iconsFolderUrl . '/actions/go-down.png" alt="Decrement Group Priority" title="Decrement Group Priority" />';
		$moveTopImage = '<img src="' . $iconsFolderUrl . '/actions/go-top.png" alt="Move to next higher priority level" title="Move to next higher priority level" />';
		$moveBottomImage = '<img src="' . $iconsFolderUrl . '/actions/go-bottom.png" alt="Move to next lower priority level" title="Move to next lower priority level" />';
		$emptyImage = '<img src="' . $iconsFolderUrl . '/actions/edit-clear.png" alt="Empty Group" title="Empty Group" />';
		$deleteImage = '<img src="' . $iconsFolderUrl . '/actions/edit-delete.png" alt="Delete Group" title="Delete Group" />';

		$groupsForm = '<h3>Edit Group Priorities</h3><br />';
		for($i = 0; $i < $modifiableCount; $i++) {
			if($modifiableGroups[$i]['group_priority'] != $previousPriority) {
				$groupsForm .= '<br /><br /><hr /><br />';
			}
			$groupsForm .=
					'<span style="margin: 4px;" title="' . $modifiableGroups[$i]['group_description'] . '">' .
					'<a href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=incrementpriority&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveUpImage . '</a>' .
					'<a href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=decrementpriority&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveDownImage . '</a>' .
					'<a href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=movegroupup&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveTopImage . '</a>' .
					'<a href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=movegroupdown&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveBottomImage . '</a>' .
					'<a onclick="return confirm(\'Are you sure you want to empty this group?\')" href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=emptygroup&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $emptyImage . '</a>' .
					'<a onclick="return confirm(\'Are you sure you want to delete this group?\')" href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=deletegroup&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $deleteImage . '</a>' .
					'<a href="./+admin&subaction=editgroups&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $modifiableGroups[$i]['group_name'] . "</a></span>\n";
			$previousPriority = $modifiableGroups[$i]['group_priority'];
		}

		$priorityBox = '<option value="1">1</option>';
		for($i = 2; $i <= $userMaxPriority; ++$i) {
			$priorityBox .= '<option value="' . $i . '">' . $i . '</option>';
		}
		$groupsForm .= <<<GROUPSFORM
		<br /><br />
		<fieldset style="padding: 8px">
			<legend>Create New Group:</legend>

			<form name="groupaddform" method="POST" action="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=addgroup">
				<label>Group Name: <input type="text" name="txtGroupName" value="" /></label><br />
				<label>Group Description: <input type="text" name="txtGroupDescription" value="" /></label><br />
				<label>Group Priority: <select name="selGroupPriority">$priorityBox</select><br />
				<label><input type="checkbox" name="chkAddMe" value="addme" /> Add me to group</label><br />
				<input type="submit" name="btnAddNewGroup" value="Add Group" />
			</form>
		</fieldset>
GROUPSFORM;

		return $groupsForm;
	}


	$modifiableCount = count($modifiableGroups);
	$groupsBox = '<select name="selEditGroups">';
	for($i = 0; $i < $modifiableCount; ++$i) {
		$groupsBox .= '<option value="' . $modifiableGroups[$i]['group_name'] . '">' . $modifiableGroups[$i]['group_name'] . ' - ' . $modifiableGroups[$i]['group_description'] . "</option>\n";
	}
	$groupsBox .= '</select>';

	$groupsForm = <<<GROUPSFORM
		<form name="groupeditform" method="POST" action="./+admin&subaction=editgroups">
			$groupsBox
			<input type="submit" name="btnEditGroup" value="Edit Selected Group" /><br /><br />
			<input type="submit" name="btnEditGroupPriorities" value="Add/Shuffle/Remove Groups" />
		</form>

GROUPSFORM;

	return $groupsForm;
}
