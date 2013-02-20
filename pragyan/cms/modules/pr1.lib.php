<?php
/**
 * @package pragyan
 * @copyright (c) 2013 Pragyan Team
 * @author Hari V
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
if(!defined('__PRAGYAN_CMS'))
{
  header($_SERVER['SERVER_PROTOCOL'].' 403 FORBIDDEN');
  echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
  echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
  exit(1);
}

class pr1 implements module {
  private $userId;
  private $moduleComponentId;
  public function getHtml($gotuid, $gotmoduleComponentId, $gotaction){
    $this->userId=$gotuid;
    $this->moduleComponentId=$gotmoduleComponentId;
    $this->action=$gotaction;
    if($this->action=="view")
      return $this->actionView();
    if($this->action=="team")
      return $this->actionTeam();
    if($this->action=="head")
      return $this->actionHead();
    else
      return $this->actionView();
  }
  private function getFormSuggestions($input)
  {
    //$formQuery="SELECT * FROM `pr1_formTable` WHERE 'event_name' LIKE '{$input}'";
    /*$formQuery=mysql_query($formQuery);
    $suggestions=array($input);
    while($formQueryResult=mysql_fetch_array($formQuery))
      {
	$suggestions[] = $formQueryResult['event_name'];
      }
      return join($suggestions,',');*/
    return $input." batman";
    
    
  }
  private function getUserSuggestions($input)
  {
    $emailQuery="SELECT * FROM `pragyanV3_users` WHERE `user_id` LIKE '{$input}'";
    $emailQuery=mysql_query($emailQuery);
    $suggestions=array($input);
    while($emailQueryResult=mysql_fetch_array($emailQuery))
    {
      $suggestions[] = $emailQueryResult['user_email'].' - '.$emailQueryResult['user_name'];
    }
    return join($suggestions,',');
  }
  public function actionView()
  {
    global $urlRequestRoot, $moduleFolder, $cmsFolder, $templateFolder, $sourceFolder;
    $js=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/pr1/jquery-1.3.2.min.js";
    $scriptsFolder=$urlRequestRoot."/".$cmsFolder."/".$templateFolder."/common/scripts";
    $imagesFolder=$urlRequestRoot."/".$cmsFolder."/".$templateFolder."/common/images";
    $actionview=<<<AB
      <!--<script type="text/javascript" src="{$js}"></script>-->
      <script type="text/javscript">
      $(document).ready(function(){
	  $("#myResultDiv").css({'display':'none'});
	  $("#showResult").css({'display':'block'});
	})
      </script>
      <div id="showResult">
      <form action="./+pr&subaction=showResult" method="POST" id="showMyResultForm">
      Event Name:<input type="text" id="enterEventName" name="enterEventName" autocomplete="off"/>
      <div id="suggestionsBox" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
      <input type="submit" id="submitEventName" />
      <script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">
      </script>
      <script language="javascript">
      var userBox = new SuggestionBox(document.getElementById('enterEventName'), document.getElementById('suggestionsBox'), "./+team&subaction=getsuggestions&forwhat=%pattern%");
    userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
    </script>
	</form>
      </div>
AB;
    if(isset($_GET['subaction']) && $_GET['subaction']=="getsuggestions" && isset($_GET['forwhat']))
    {
      echo $this->getFormSuggestions(escape($_GET['forwhat']));
      exit();
    }
    if(isset($_GET['subaction']) && $_GET['subaction']="showResult" && isset($_POST['enterEventName']) && $_POST['enterEventName']!="")
     {
       $eventName=mysql_real_escape_string($_POST['enterEventName']);
       $getEventIdQuery="SELECT * FROM `pr1_formTable` WHERE `event_name`='{$eventName}' AND `page_moduleComponentId`='{$this->moduleComponentId}'";
       $getEventIdQuery=mysql_query($getEventIdQuery);
       if(mysql_num_rows($getEventIdQuery)==0)
	 displayerror("Invalid Event Name");
       else
       {
	 $getEventIdQueryResult=mysql_fetch_assoc($getEventIdQuery);
	 $eventId=$getEventIdQueryResult['form_moduleComponentId'];
	 $getResultQuery="SELECT * FROM `pr1_rankList` WHERE `form_moduleComponentId`='{$eventId}' AND `page_moduleComponentId`='{$this->moduleComponentId}' ORDER BY `user_rank` ASC";
	 $getResultQuery=mysql_query($getResultQuery);
	 $actionview.=<<<AB
	 <!--<script type="text/javascript" src="{$js}"></script>-->
	 <script type="text/javascript">
	   $(document).ready(function(){
	       $("#showResult").css({'display':'none'});
	     });
	 </script>
	 <h2>{$eventName} Results</h2>
	  <table id="myResultTable" border="1px">
	  <tr><th>PRAGYAN ID</th>
	      <th>EMAIL ID</th>
	      <th>NAME</th>
	      <th>RESULT</th>
              <th>PRIZE MONEY</th>
         </tr>
AB;
	 while($getResultQueryResult=mysql_fetch_array($getResultQuery))
	 {
	   $pragyanId=$getResultQueryResult['user_pragyanId'];
	   $getUserDetailsQuery="SELECT * FROM `pragyanV3_users` WHERE `user_id`='{$pragyanId}'";
	   $getUserDetailsQuery=mysql_query($getUserDetailsQuery);
	   $getUserDetailsQueryResult=mysql_fetch_assoc($getUserDetailsQuery);
	   $emailId=$getUserDetailsQueryResult['user_email'];
	   $userName=$getUserDetailsQueryResult['user_name'];
	   switch($getResultQueryResult['user_rank'])
	   {
	   case 0:
	     $res="Consolation";
	     break;
	   case 1:
	     $res="First";
	     break;
	   case 2:
	     $res="Second";
	     break;
	   case 3:
	     $res="Third";
	     break;
	   default:
	     $res="Participant";
	   }
	   $actionview.=<<<AB
	     <tr>
	     <td>{$getResultQueryResult['user_pragyanId']}</td>
	     <td>{$emailId}</td>
	     <td>{$userName}</td>
	     <td>{$res}</td>
	     <td>{$getResultQueryResult['user_prizeMoney']} </td>
	     </tr>
AB;
	 }
	 $actionview.="</table>";
       }
     }
    return $actionview;
  }
  public function actionTeam()
  {
    
    global $urlRequestRoot, $moduleFolder, $cmsFolder, $templateFolder, $sourceFolder;
    $js=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/pr1/jquery-1.3.2.min.js";
    $scriptsFolder=$urlRequestRoot."/".$cmsFolder."/".$templateFolder."/common/scripts";
    $imagesFolder=$urlRequestRoot."/".$cmsFolder."/".$templateFolder."/common/images";
    require_once($sourceFolder."/".$moduleFolder."/form/registrationformgenerate.php");
    require_once($sourceFolder."/".$moduleFolder."/form/registrationformsubmit.php");
    $actionteam =<<<AB
     <!-- <script type="text/javascript" src="{$js}"></script>-->
      <script type="text/javascript">
      $(document).ready(function(){
	  $("#regForm").css({'display':'block'});
	  $("#resultsForm").css({'display':'none'});
	  $("#updateForm").css({'display':'none'});
	  $("#showRegForm").click(function(){
	      dispRegForm();
	    });
	  $("#setResultsForm").click(function(){
	      dispResultsForm();
	    });
	  $("#updateResults").click(function(){
	      dispUpdateForm();
	    });
	  function dispRegForm()
	  {
	    $("#regForm").css({'display':'block'});
	    $("#resultsForm").css({'display':'none'});
	    $("#updateForm").css({'display':'none'});
	    $("#pragyanIdReg").css({'display':'none'});
	    $("#resultsTableDiv").css({'display':'none'});
	    $("#eventRegisterForm").css({'display':'none'});
	    $(".cms-error").css({'display':'none'});
	    $(".cms-info").css({'display':'none'});
	  }
	  function dispResultsForm()
	  {
	    $("#resultsForm").css({'display':'block'});
	    $("#regForm").css({'display':'none'});
	    $("#updateForm").css({'display':'none'});
	    $("#pragyanIdReg").css({'display':'none'});
	    $("#resultsTableDiv").css({'display':'none'});
	    $("#eventRegisterForm").css({'display':'none'});
	    $(".cms-error").css({'display':'none'});
	    $(".cms-info").css({'display':'none'})
	  }
	  function dispUpdateForm()
	  {
	    $("#resultsForm").css({'display':'none'});
            $("#regForm").css({'display':'none'});
            $("#updateForm").css({'display':'block'});
	    $("#pragyanIdReg").css({'display':'none'});
	    $("#resultsTableDiv").css({'display':'none'});
	    $("#eventRegisterForm").css({'display':'none'});
	    $(".cms-error").css({'display':'none'});
	    $(".cms-info").css({'display':'none'})
	  }
	});
      </script>
      <div id="buttonsDiv" class="mainButtonsDiv">
      <input type="button" id="showRegForm" class="mainButton" value="New Registrant !" />
      <input type="button" id="setResultsForm" class="mainButton" value="Set Results !" />
      <input type="button" id="updateResults" class="mainButton" value="Update !" />
      </div>
      <br />
      <div id="regForm">
	  <h2>Register New User</h2>
      <form action="./+team&subaction=getEventName" method="POST" id="getEventForm">
	  Enter Event Name:<input type="text" id="enterEventNameReg" name="enterEventNameReg" autocomplete="off"/>
	  <div id="suggestionsBox" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
      <input type="submit" id="submitEventNameReg" value="Submit" />
      <script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">
      </script>
      <script language="javascript">
	  var userBox = new SuggestionBox(document.getElementById('enterEventNameReg'), document.getElementById('suggestionsBox'), "./+team&subaction=getsuggestions&forwhat=%pattern%");
    userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
    </script>
      </form>
      </div>
      <div id="resultsForm">
					   <h2 id="resultsFormTitle">Set Results</h2>
      <form action="./+team&subaction=setResults" method="POST" id="setResultsForm">
	  Enter Event Name :<input type="text" id="enterEventNameRes" name="enterEventNameRes" autocomplete="off" />
	<input type="submit" id="submitEventNameRes" value="Submit" />
	</form>
      </div>
      <div id="updateForm">
				      <h2>Update Event</h2>
      <form action="./+team&subaction=updateTables" method="POST" id="updateTableForm">
      Enter Event Name :<input type="text" id="enterEventNameUpdate" name="enterEventNameUpdate" autocomplete="off"/>
	<input type="submit" id="submitEventNameUpdate" value="Submit" />
	</form>
      </div>
AB;
    if(isset($_GET['subaction']) && $_GET['subaction']=="getsuggestions" && isset($_GET['forwhat']))
    {
      echo $this->getFormSuggestions(escape($_GET['forwhat']));
      exit();
    }
    if(isset($_POST['rank']) && isset($_POST['pId']) && isset($_POST['form']) && isset($_POST['output']))
    {
      $rank=mysql_real_escape_string($_POST['rank']);
      $pId=mysql_real_escape_string($_POST['pId']);
      $formId=mysql_real_escape_string($_POST['form']);
      $prizeMoney=mysql_real_escape_string($_POST['output']);
      $updateRankQuery="UPDATE `pr1_rankList` SET `user_rank`='{$rank}', `user_prizeMoney`='{$prizeMoney}' WHERE `user_pragyanId`='{$pId}' AND `page_moduleComponentId`='{$this->moduleComponentId}' AND `form_moduleComponentId`='{$formId}'";
      $updateRankQuery=mysql_query($updateRankQuery);
    }
    if(isset($_GET['subaction']) && $_GET['subaction']=="getEventName" && isset($_POST['enterEventNameReg']) && $_POST['enterEventNameReg']!="")
      {
	$eventName=mysql_real_escape_string($_POST['enterEventNameReg']);
	$getFormIdQuery="SELECT `form_moduleComponentId` FROM `pr1_formTable` WHERE `event_name`='{$eventName}' AND `page_moduleComponentId`='{$this->moduleComponentId}'";
	$getFormIdQuery=mysql_query($getFormIdQuery);
	if(mysql_num_rows($getFormIdQuery)==0)
	  displayerror("Invalid Event Name");
	else
	  {
	    $getFormIdQueryRes=mysql_fetch_assoc($getFormIdQuery);
	    $formModuleComponentId=$getFormIdQueryRes['form_moduleComponentId'];
	    $actionteam.=<<<AB
	      <!--<script type="text/javascript" src="{$js}"></script>-->
	      <script type="text/javascript">
	      $(document).ready(function(){
		  $("#regForm").css({'display':'none'});
		  $("#resultsForm").css({'display':'none'});
		  $("#updateForm").css({'display':'none'});
		});
	</script>
	    <div id="pragyanIdReg">
	    <h2>{$eventName}</h2>
	   <form action="./+team&subaction=getPragyanId&useremail=hari.pragyan@gmail.com" method="POST" id="getPragyanIdForm">
	    Enter Pragyan Id:<input type="text" id="enterPragyanIdReg" name="enterPragyanIdReg" autocomplete="off" />
				<input type="hidden" name="formId" value="{$formModuleComponentId}" />
	    <input type="hidden" name="eventName" value="{$eventName}" />
	    <input type="submit" id="submitPragyanIdReg" />
	    </form>
	    </div>
AB;
	  }
      }
    if(isset($_GET['subaction']) && $_GET['subaction']=="getPragyanId" && isset($_POST['enterPragyanIdReg']) && $_POST['enterPragyanIdReg']!="")
    {
      $pragyanId=mysql_real_escape_string($_POST['enterPragyanIdReg']);
      $formId=$_POST['formId'];
      $eventName=$_POST['eventName'];
      $getPragyanIdQuery="SELECT * FROM `pragyanV3_users` WHERE `user_id`='$pragyanId' OR `user_email`='$pragyanId'";
      $getPragyanIdQuery=mysql_query($getPragyanIdQuery);
      if(mysql_num_rows($getPragyanIdQuery)==0)
	displayerror("You have to register to Pragyan Site First!");
      else
      {
	$getPragyanIdQueryResult=mysql_fetch_assoc($getPragyanIdQuery);
	$pragyanId=$getPragyanIdQueryResult['user_id'];
	$getRegisteredStatusQuery="SELECT * FROM `form_elementdata` WHERE `user_id`='{$pragyanId}' AND `page_modulecomponentid`='{$formId}'";
	$getRegisteredStatusQuery=mysql_query($getRegisteredStatusQuery);
	if(mysql_num_rows($getRegisteredStatusQuery)>0)
	  displayerror("User has already Registered");
	else
	{
	  $actionteam.=<<<AB
	    <!--<script type="text/javascript" src="{$js}"></script>-->
	    <script type="text/javascript">
	    $(document).ready(function(){
		$("#regForm").css({'display':'none'});
		$("#resultsForm").css({'display':'none'});
		$("#updateForm").css({'display':'none'});
		$("fieldset").append("<input type='hidden' value='{$formId}' name='formSubmitId' />");
		$("fieldset").append("<input type='hidden' value='{$pragyanId}' name='userSubmitId' />");
		$("fieldset").append("<input type='hidden' value='{$eventName}' name='eventSubmitName' />");
	      });
	  </script>
AB;
	  $actionteam.="<div id='eventRegisterForm'>";
	  $actionteam.="<input type='hidden' name='formSubmitId' value='{$formId}' />";
	  $actionteam.="<input type='hidden' name='userSubmitId' value='{$pragyanId}'>";
	  $actionteam.=generateRegistrationForm($formId,$pragyanId,"./+team&subaction=submitForm");
	  $actionteam.="</div>";
	}
      }
    }
    if(isset($_GET['subaction']) && $_GET['subaction']=="submitForm")
    {
      $formSubmitId=$_POST['formSubmitId'];
      $userSubmitId=$_POST['userSubmitId'];
      $eventSubmitName=$_POST['eventSubmitName'];
      $insertUserToRankListQuery="INSERT INTO `pr1_rankList` VALUES ('{$this->moduleComponentId}','{$formSubmitId}','{$eventSubmitName}','{$userSubmitId}','100000','0')";
      $insertUserToRankListQuery=mysql_query($insertUserToRankListQuery);
      submitRegistrationForm($formSubmitId,$userSubmitId);
    }
    if(isset($_GET['subaction']) && $_GET['subaction']=="setResults" && isset($_POST['enterEventNameRes']) && $_POST['enterEventNameRes']!="")
    {
      
      $eventNameRes=mysql_real_escape_string($_POST['enterEventNameRes']);
      $getFormIdFromEventQuery="SELECT `form_moduleComponentId` FROM `pr1_formTable` WHERE `event_name`='{$eventNameRes}' AND `page_moduleComponentId`='{$this->moduleComponentId}'";
      $getFormIdFromEventQuery=mysql_query($getFormIdFromEventQuery);
      if(mysql_num_rows($getFormIdFromEventQuery)==0)
	displayerror("Invalid From Name");
      else
      {
	$getFormIdFromEventQueryResult=mysql_fetch_assoc($getFormIdFromEventQuery);
	$resFormId=$getFormIdFromEventQueryResult['form_moduleComponentId'];
	$actionteam.=<<<AB
	  <!--<script type="text/javascript" src="{$js}"></script>-->
	  <script type="text/javascript">
	  $(document).ready(function(){
	      $("#regForm").css({'display':'none'});
	      $("#resultsForm").css({'display':'none'});
	      $("#updateForm").css({'display':'none'});
	      
	    });
	</script>
	    <div id="resultsTableDiv">
	    <h2>{$eventNameRes}</h2>
	  <table id="resultsTable" border="1px">
	  <tr>
	  <th>PRAGYAN ID</th>
	  <th>EMAIL ID</th>
	  <th>NAME</th>
	  <th>RANK</th>
	  <th>PRIZE MONEY</th>
	  <th>CHANGE</th>
	  </tr>
AB;
	$getResultQuery="SELECT * FROM `pr1_rankList` WHERE `form_moduleComponentId`='{$resFormId}' AND `page_moduleComponentId`='{$this->moduleComponentId}' ORDER BY `user_rank` ASC";
	$getResultQuery=mysql_query($getResultQuery);
	while($getResultQueryResult=mysql_fetch_array($getResultQuery))
	{
	  $userPid=$getResultQueryResult['user_pragyanId'];
	  $getUserEmailQuery="SELECT * FROM `pragyanV3_users` WHERE `user_id`='{$userPid}'";
	  $getUserEmailQuery=mysql_query($getUserEmailQuery);
	  $getUserEmailQueryResult=mysql_fetch_assoc($getUserEmailQuery);
	  $userEmail=$getUserEmailQueryResult['user_email'];
	  $userName=$getUserEmailQueryResult['user_name'];
	  $actionteam.=<<<AB
	    <tr>
                <td>{$getResultQueryResult['user_pragyanId']}</td>
                <td>{$userEmail}</td>
		<td>{$userName}</td>
		<td id="table{$getResultQueryResult['user_pragyanId']}">{$getResultQueryResult['user_rank']}</td>
		<td id="prizeMoney{$getResultQueryResult['user_pragyanId']}">{$getResultQueryResult['user_prizeMoney']}</td>
		<td><input type="button" id="button{$getResultQueryResult['user_pragyanId']}" value="Edit" onclick="updateRank(this.id,this.value,'{$resFormId}')" /></td>
	    <script language="javascript">
	    function updateRank(rankId,method,formId)
	    {
	      rankId=rankId.replace("button","");
	      if(method=="Edit")
	      {
		var value=$("#table"+rankId).html();
		var prize=$("#prizeMoney"+rankId).html();
		var inputTag="<input type='text' id='"+rankId+"' name='"+rankId+"' value='"+value+"' />";
		var prizeMoneyInput="<input type='text' id='prizeMoneyInput"+rankId+"' name='prizeMoneyInput"+rankId+"' value='"+prize+"' />";
		$("#table"+rankId).html(inputTag);
		$("#prizeMoney"+rankId).html(prizeMoneyInput);
		$("#button"+rankId).val('Update');
		
	      }
	      else if(method=="Update")
	      {
		var value=$("#"+rankId).val();
		var prizeMoney=$("#prizeMoneyInput"+rankId).val();
		
		$.ajax({
		  type:"POST",
		      url:"./+team",
		      data:{rank:value,pId:rankId,form:formId,output:prizeMoney},    
		  success:function(data)
		   {
		     $("#table"+rankId).html(value);
		     $("#prizeMoney"+rankId).html(prizeMoney);
		     $("#button"+rankId).val('Edit');
		   }
		  });
	      
	      }
	    }
            </script>
</tr>								 
AB;
	}
	$actionteam.="</table></div>";
      }
    }
    if(isset($_GET['subaction']) && $_GET['subaction']=="updateTables" && isset($_POST['enterEventNameUpdate']) && $_POST['enterEventNameUpdate']!="")
    {
      $eventNameForUpdate=mysql_real_escape_string($_POST['enterEventNameUpdate']);
      $getFormIdQuery="SELECT * FROM `pr1_formTable` WHERE `event_name`='{$eventNameForUpdate}' AND `page_moduleComponentId`='{$this->moduleComponentId}'";
      $getFormIdQuery=mysql_query($getFormIdQuery);
      if(mysql_num_rows($getFormIdQuery)==0)
     	displayerror("Invalid event name");
      else
      {
	$getFormIdQueryResult=mysql_fetch_assoc($getFormIdQuery);
	$updateFormId=$getFormIdQueryResult['form_moduleComponentId'];
	$checkEventUpdatedQuery="SELECT * FROM `pr1_rankList` WHERE `form_moduleComponentId`='{$updateFormId}'";
	$checkEventUpdatedQuery=mysql_query($checkEventUpdatedQuery);
	if(mysql_num_rows($checkEventUpdatedQuery)>0)
	  displayerror("Event has already been updated");
	else
	{
	  $getUsersQuery="SELECT DISTINCT `user_id` FROM `form_elementdata` WHERE `page_modulecomponentid`='{$updateFormId}'";
	  $getUsersQuery=mysql_query($getUsersQuery);
	  while($getUsersQueryResult=mysql_fetch_array($getUsersQuery))
	  {
	    $updateUserId=$getUsersQueryResult['user_id'];
	    $insertUserQuery="INSERT INTO `pr1_rankList` VALUES('{$this->moduleComponentId}','{$updateFormId}','{$eventNameForUpdate}','{$updateUserId}','100000','0')";
	    $insertUserQuery=mysql_query($insertUserQuery);
	  }
	  displayinfo("Successfully Updated Form");
	}
      }
    }
    return $actionteam;
  }

  public static function getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName) {
    return getPermissions($userId, $pageId, "view");
  }
  public function actionHead()
  {
    return "helloHead";
  }
  public function deleteModule($moduleComponentId)
  {
    return true;
  }
  public function createModule($moduleComponentId)
  {
    return true;
  }
  public function copyModule($moduleComponentId, $newId)
  {
    return true;
  }
}