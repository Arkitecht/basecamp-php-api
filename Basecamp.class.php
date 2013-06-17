<?
/**
	* Project: Basecamp PHP API 
	*	File: Basecamp.class.php
	*
	* 
	*
	*
	*
	*/ 
require_once(dirname(__FILE__).'/Basecamp/Attachment.php');
require_once(dirname(__FILE__).'/Basecamp/Project.php');
require_once(dirname(__FILE__).'/Basecamp/Person.php');

class Basecamp {
	private $_client;
	private $_oAuth;
	var $client_id;
	private $client_secret;
	private $password;
	private $username;
	private $authMethod;
	var $authenticated = false;
	var $oauth_url;
	var $app_name;
	var $account;	
	private $session_key;
	private $access_token; 
	var $debug = false;
	
	function __construct($app_name) {
		$this->app_name = $app_name;
	}
	
	function setOAuthAuthentication($client_id,$client_secret,$oauth_url,$session_key='oAuth') {	
		session_start();	
		$this->authMethod = 'oAuth';
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->oauth_url = $oauth_url;		
		$this->_oAuth = new OAuth($client_id,$client_secret);
		$this->session_key = $session_key;
		
		if ( $_SESSION[$session_key]->access_token ) {
			$this->access_token 	= $_SESSION[$session_key]->access_token;
			$this->authenticated 	= true;
		} 
	}
	
	function setOAuthAuthenticationToken($token) {
		$this->authMethod 		= 'oAuth';
		$this->access_token 	= $token;
		$this->authenticated 	= true;
	}
	
	function setServerAuthentication($username,$password) {		
		$this->authMethod = 'server';
		$this->username 	= $username;
		$this->password 	= $password;		
		$this->authenticated = true;
	}
	
	function setDebug($debug=true) {
		$this->debug = $debug;
	}
	
	function getIdentity() {
		$authorization = $this->_makeAuthenticatedRequest('https://launchpad.37signals.com/authorization.json');
		return $authorization->identity;
	}

	
	function getAccounts() {
		$authorization = $this->_makeAuthenticatedRequest('https://launchpad.37signals.com/authorization.json');
		return $authorization->accounts;
	}
	
	function setAccount($account) {
		$this->account = $account;
	}
		
	function getAccountURL($target) {
		if ( !$this->account ) {
			throw	new Exception('You must set an account first, using Basecamp::setAccount');
		}
		return "$this->account/$target";
	}
	
	//- mark Projects
	
	function getProjects() {
		return $this->_makeAuthenticatedRequest($this->getAccountURL('projects.json'));
	}
	
	function getProject($id,$native=false) {
		$project = $this->_makeAuthenticatedRequest($this->getAccountURL('projects/'.$id.'.json'));
		if ( $native ) return Basecamp_Project::objectFromProject($project,$this);
		return $project;
	}
	
	function getProjectAccess($id) {
		return $this->getAccesses('projects',$id);
	}
	
	function grantProjectAccess($id,$users=array(),$emails=array()) {
		return $this->grantAccesss('projects',$id,$users,$emails);
	}
	
	function revokeProjectAccess($id,$user) {
		return $this->revokeAccess('projects',$id,$user);
	}
	
	function getProjectEvents($id,$since='',$page='') {
		return $this->getEvents($since,$page,'projects',$id);
	}
	
	function getProjectTopics($id,$page='') {
		return $this->getTopics($page,'projects',$id);
	}
	
	function getProjectAttachments($id,$page='') {
		return $this->getAttachments($page,$id);
	}
	
	function getProjectTodoLists($id,$completed=false) {
		return $this->getTodoLists($completed,$id);	
	}
	
	function getProjectDocuments($id) {
		return $this->getDocuments($id);	
	}	
	
	function getProjectMilestones($id,$past=false) {
		return $this->getMilestones('projects',$id,$past);
	}
		
	function getProjectMilestone($project,$id) {
		return $this->getMilestone('projects',$project,$id);
	}
	
	function createProjectMilestone($id,$summary,$description,$starts_at,$ends_at='',$all_day=false) {
		return $this->createMilestone('projects',$id,$summary,$description,$starts_at,$ends_at,$all_day);
	}
	
	function updateProjectMilestone($project,$id,$summary='',$description='',$starts_at='',$ends_at='',$all_day='') {
		return $this->updateMilestone('projects',$project,$id,$summary,$description,$starts_at,$ends_at,$all_day);
	}
	
	function deleteProjectMilestone($project,$id) {
		return $this->deleteMilestone('projects',$project,$id);	
	}
	
	function createProject($name,$description) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL('projects.json'),json_encode(array('name'=>$name,'description'=>$description)),'POST');
	}
	
	function updateProject($id,$name,$description) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL('projects/'.$id.'.json'),json_encode(array('name'=>$name,'description'=>$description)),'PUT');
	}
	
	function archiveProject($id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL('projects/'.$id.'.json'),json_encode(array('archived'=>true)),'PUT');
	}
	
	function unArchiveProject($id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL('projects/'.$id.'.json'),json_encode(array('archived'=>false)),'PUT');
	}
	
	function deleteProject($id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL('projects/'.$id.'.json'),null,'DELETE');
	}

	//- mark People
	
	function me($asPerson=false) {
		$me = $this->_makeAuthenticatedRequest($this->getAccountURL('people/me.json'));
		if ( $asPerson ) {
			return Basecamp_Person::objectFromResponse($me,&$this);
		}
		return $me;
	}


	function getPeople() {
		return $this->_makeAuthenticatedRequest($this->getAccountURL('people.json'));
	}
	
	function getPerson($id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL('people/'.$id.'.json'));
	}
	
	function getPersonEvents($id,$since='',$page='') {
		return $this->getEvents($since,$page,'people',$id);
	}
	
	function deletePerson($id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL('people/'.$id.'.json'),null,'DELETE');
	}
	
	//- mark Access
	
	function getAccesses($type,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("$type/$id/accesses.json"));		
	}
	
	function grantAccesss($type,$id,$users=array(),$emails=array()) {
		$users  = $this->_makeArray($users);
		$emails = $this->_makeArray($emails);
	
		if ( $users ) 	$args["ids"] = $users;
		if ( $emails ) 	$args["email_addresses"] = $emails;

		if ( !$users && !$emails ) return;

		return $this->_makeAuthenticatedRequest($this->getAccountURL("$type/$id/accesses.json"),json_encode($args),'POST');				
	}
	
	function revokeAccess($type,$id,$user) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("$type/$id/accesses/$user.json"),null,'DELETE');				
	}
	
	
	//- mark Events
	
	function getEvents($since='',$page='',$type='',$id='') {
		if ( $since ) $args['since'] = $since;
		if ( $page ) 	$args['page'] = $page;
		
		if ( $type && $id ) $url = "$type/$id/";
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL($url."events.json"),$args);				
	}
	
	//- mark Topics
	function getTopics($page='',$type='',$id='') {
		if ( $page ) 	$args['page'] = $page;
		
		if ( $type && $id ) $url = "$type/$id/";
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL($url."topics.json"),$args);				
	}
		
	//- mark Messages
	function getMessages($page='',$type='',$id='') {
		return $this->getTopics($page,$type,$id);
	}
	
	function getMessage($project,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/messages/$id.json"));	
	}
	
	function createMessage($project,$subject,$content,$subscribers=array()) {
		$subscribers = $this->_makeArray($subscribers);
		
		$args['subject'] 			= $subject;
		$args['content'] 			= $content;
		if ( $subscribers ) 	$args['subscribers'] 	= $subscribers;
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/messages.json"),json_encode($args),'POST');	
	}
	
	function createMessageComment($project,$id,$comment,$subscribers='',$attachmentName='',$tokenOrFilename='',$isFilename=false) {
		return $this->createComment($project,'messages',$id,$comment,$subscribers,$attachmentName,$tokenOrFilename,$isFilename);
	}
	
	function updateMessage($project,$id,$subject,$content) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/messages/$id.json"),json_encode(array('subject'=>$subject,'content'=>$content)),'PUT');
	}
	
	function deleteMessage($project,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/messages/$id.json"),null,'DELETE');				
	}
	
	//- mark Attachments
	
	function createAttachment($filename) {
		$fileData = file_get_contents($filename);
		if ( $fileData ) {
			return $this->_makeAuthenticatedRequest($this->getAccountURL("attachments.json"),$fileData,'POST',array('Content-Type'=>'image/png'));
		}
	}
	
	function getAttachments($page='',$project='') {
		if ( $project ) $url = "projects/$project/attachments.json";
		else $url = "attachments.json";
		
		if ( $page ) $args['page'] = $page;
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL($url),$args);
	}
	
	//- mark Uploads
	
	function createUpload($project,$tokenOrFilename,$name,$content,$subscribers=array(),$isFilename=false) {
		if ( $isFilename ) {
			//createAttachment and get token
		} else $token = $tokenOrFilename;
		
		$args['content'] 			= $content;
		$args['attachments'] 	= array(new Basecamp_Attachment($token,$name));
		$subscribers 					= $this->_makeArray($subscribers);
		if ( $subscribers ) $args['subscribers'] = $subscribers;
		
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL("/projects/$project/uploads.json"),json_encode($args),'POST');		
	}
	
	function getUpload($project,$id) {		
		return $this->_makeAuthenticatedRequest($this->getAccountURL("/projects/$project/uploads/$id.json"));		
	}
	
	
	//-mark TodoLists
	
	
	function getTodoLists($completed=false,$project='') {
		if ( $project ) {
			$url = "projects/$project/todolists";
			if ( $completed ) $url .= "/completed.json";
			else $url .= ".json";
		} else {
			$url = "todolists";
			if ( $completed ) $url .= "/completed.json";
			else $url .= ".json";			
		}		
		return $this->_makeAuthenticatedRequest($this->getAccountURL($url));		
	}
	
	function getAssignedTodoLists($user) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("people/$user/assigned_todos.json"));
	}
	
	function getTodoList($project,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/todolists/$id.json"));
	}
	
	function createTodoList($project,$name,$description) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/todolists.json"),json_encode(array('name'=>$name,'description'=>$description)),'POST');
	}
	
	function updateTodoList($project,$id,$name,$description,$position='') {
		if ( $name ) 				$args['name'] = $name;
		if ( $description )	$args['description'] = $description;
		if ( $position ) 		$args['position'] = $position;
	
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/todolists/$id.json"),json_encode($args),'PUT');
	}
	
	function deleteTodoList($project,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/todolists/$id.json"),null,'DELETE');
	}
	
	//- mark Todos	
	
	function getTodo($project,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/todos/$id.json"));
	}
	
	function createTodo($project,$list,$todo,$dueAt='',$assignee='') {
		$args['content'] = $todo;
		if ( $dueAt ) 		$args['due_at'] 	= $dueAt;
		if ( $assignee ) 	$args['assignee'] = array( "id"=>$assignee, "type"=>"Person" );
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/todolists/$list/todos.json"),json_encode($args),'POST');
	}
	
	function createTodoComment($project,$id,$comment,$subscribers='',$attachmentName='',$tokenOrFilename='',$isFilename=false) {
		return $this->createComment($project,'todos',$id,$comment,$subscribers,$attachmentName,$tokenOrFilename,$isFilename);
	}
	
	function updateTodo($project,$id,$todo,$dueAt='',$assignee='',$completed='',$position='') {
		if ( $todo )			$args['content'] 	= $todo;
		if ( $dueAt ) 		$args['due_at'] 	= $dueAt;
		if ( $assignee ) 	$args['assignee'] = array( "id"=>$assignee, "type"=>"Person" );
		if ( $completed || $completed === false ) $args['completed'] = $completed;
		if ( $position )	$args['position'] = $position;
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/todos/$id.json"),json_encode($args),'PUT');
	}
	
	function completeTodo($project,$id) {
		return $this->updateTodo($project,$id,'','','',true);
	}
	
	function unCompleteTodo($project,$id) {
		return $this->updateTodo($project,$id,'','','',false);
	}
	
	function reorderTodo($project,$id,$position) {
		return $this->updateTodo($project,$id,'','','','',$position);
	}
	
	function deleteTodo($project,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/todos/$id.json"),'','DELETE');
	}
	
	
	//-mark Comments
	
	function createComment($project,$on,$id,$comment,$subscribers='',$attachmentName='',$tokenOrFilename='',$isFilename=false) {
		$args['content'] = $comment;
		$subscribers 					= $this->_makeArray($subscribers);
		if ( $subscribers ) $args['subscribers'] = $subscribers;
		
		if ( $attachmentName ) {
			if ( $isFilename ) {
				//createAttachment and get token
			} else $token = $tokenOrFilename;
			$args['attachments'] 	= array(new Basecamp_Attachment($token,$attachmentName));
		}
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/$on/$id/comments.json"),json_encode($args),'POST');
	}
	
	function deleteComment($project,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/comments/$id.json"),'','DELETE');
	}
	
	//- mark Documents
	
	function getDocuments($project) {
		$url = 'documents.json';
		if ( $project ) $url = "projects/$project/documents.json";
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL($url));
	}
	
	function getDocument($project,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/documents/$id.json"));
	}
	
	function createDocument($project,$title,$content) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/documents.json"),json_encode(array('title'=>$title,'content'=>$content)),'POST');
	}
	
	function updateDocument($project,$id,$title,$content) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/documents/$id.json"),json_encode(array('title'=>$title,'content'=>$content)),'PUT');
	}
	
	function deleteDocument($project,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("projects/$project/documents/$id.json"),null,'DELETE');
	}

	//- mark Calendars
	
	function getCalendars() {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("calendars.json"));
	}
	
	function getCalendar($id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("calendars/$id.json"));
	}
	
	function getCalendarEvents($id,$past=false) {
		return $this->getMilestones('calendars',$id,$past);
	}
	
	function getCalendarMilestones($id,$past=false) {
		return $this->getMilestones('calendars',$id,$past);
	}
	
	function getCalendarEvent($calendar,$id) {
		return $this->getMilestone('calendars',$calendar,$id);
	}
	
	function getCalendarMilestone($calendar,$id) {
		return $this->getMilestone('calendars',$calendar,$id);
	}
	
	function createCalendarEvent($id,$summary,$description,$starts_at,$ends_at='',$all_day=false) {
		return $this->createMilestone('calendars',$id,$summary,$description,$starts_at,$ends_at,$all_day);
	}
	
	function createCalendarMilestone($id,$summary,$description,$starts_at,$ends_at='',$all_day=false) {
		return $this->createMilestone('calendars',$id,$summary,$description,$starts_at,$ends_at,$all_day);
	}

	function updateCalendarEvent($calendar,$id,$summary='',$description='',$starts_at='',$ends_at='',$all_day='') {
		return $this->updateMilestone('calendars',$calendar,$id,$summary,$description,$starts_at,$ends_at,$all_day);
	}
	
	function updateCalendarMilestone($calendar,$id,$summary='',$description='',$starts_at='',$ends_at='',$all_day='') {
		return $this->updateMilestone('calendars',$calendar,$id,$summary,$description,$starts_at,$ends_at,$all_day);
	}
	
	function deleteCalendarEvent($calendar,$id) {
		return $this->deleteMilestone('calendars',$calendar,$id);
	}
	
	function deleteCalendarMilestone($calendar,$id) {
		return $this->deleteMilestone('calendars',$calendar,$id);
	}
		
	function createCalendar($name) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("calendars.json"),json_encode(array('name'=>$name)),'POST');
	}
	
	function updateCalendar($id,$name) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("calendars/$id.json"),json_encode(array('name'=>$name)),'PUT');
	}
	
	function deleteCalendar($id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("calendars/$id.json"),null,'DELETE');
	}
	
	//-mark Calendar Events (We call them Milestones to avoid confuson)
	function getMilestones($source,$sourceid,$past=false) {
		$url = "$source/$sourceid/calendar_events";
		if ( $past ) $url .= '/past.json';
		else $url .= '.json';
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL($url));
	}
	
	function getMilestone($source,$sourceid,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("$source/$sourceid/calendar_events/$id.json"));
	}
	
	function createMilestone($source,$sourceid,$summary,$description,$starts_at,$ends_at='',$all_day=false) {
		$args['summary'] 			= $summary;
		$args['description'] 	= $description;
		$args['starts_at']	 	= $starts_at;
		$args['all_day']	 		= $all_day;
		if ( $ends_at ) $args['ends_at'] = $ends_at;
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL("$source/$sourceid/calendar_events.json"),json_encode($args),'POST');
	}
	
	function updateMilestone($source,$sourceid,$id,$summary='',$description='',$starts_at='',$ends_at='',$all_day='') {
		if ( $summary ) 		$args['summary'] 			= $summary;
		if ( $description ) $args['description'] 	= $description;
		if ( $starts_at) 		$args['starts_at']	 	= $starts_at;
		if ( $all_day || $all_day === false ) $args['all_day']	 		= $all_day;
		if ( $ends_at ) $args['ends_at'] = $ends_at;
		
		return $this->_makeAuthenticatedRequest($this->getAccountURL("$source/$sourceid/calendar_events/$id.json"),json_encode($args),'PUT');
	}
	
	function deleteMilestone($source,$sourceid,$id) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL("$source/$sourceid/calendar_events/$id.json"),null,'DELETE');
	}
	
	//- makeArray
	function _makeArray($arg) {
		if ( $arg && !is_array($arg) ) return array($arg);
		return $arg;
	}
	
	//- mark Format Date
	function formatDate($date,$dateOnly=false) {
		$time = strtotime($date);
		$date = date('Y-m-d',$time);
		if ( !$dateOnly ) $date .= 'T'.date('H:i:sP',$time);
		return $date;
	}
	
	function getRawURL($url) {
		return $this->_makeAuthenticatedRequest($url);
	}
	
	//- mark Main request function
	
	function _makeAuthenticatedRequest($resource,$params=array(),$method='GET',$headers=array(),$returnRawResponse=false) {
		//$debug = true;
		
		if ( !$this->authMethod ) {
			throw	new Exception('You must set an authentication method using Basecamp::setOAuthAuthentication or Basecamp::setServerAuthentication');
		}
		
		$default_headers = array(
			'Content-Type'=>'application/json',
			'User-Agent'=>$this->app_name
		);
		
		if ( $this->authMethod == 'oAuth' )
				$default_headers['Authorization'] = 'Bearer '.$this->access_token;
		
		$all_headers = array_merge($default_headers,$headers);
		
		$headers = array();
		foreach ( $all_headers as $header_name => $header_val ) {
			$headers[] = "$header_name: $header_val";
		}
		
		$ch = curl_init();
		
		if ( $this->authMethod == 'server' )
			curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
		
		if ( $method == 'GET' ) {
			if ( $params ) $resource = $resource.'?'.http_build_query($params);
			//print "Resource: $resource\n";
		} elseif ( $method == 'POST' ) {
			curl_setopt($ch, CURLOPT_POST, true );
			if ( $params ) curl_setopt($ch, CURLOPT_POSTFIELDS, $params );
		} else {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method );
			if ( $params ) curl_setopt($ch, CURLOPT_POSTFIELDS, $params );
		}
		
		curl_setopt($ch, CURLOPT_URL, $resource);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		if ( $this->debug ) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true );
		}
		
		$response = curl_exec($ch);
		
		if ( $this->debug ) {
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			
			$body = substr($response, $header_size);
			
			$response = $body;
		}
		
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if ( $http_code == 401 ) {
			$response = json_encode(array('error_code'=>401,'error'=>'Unauthorized Access'));
		}
			
		if ( $this->debug ) {		
			print_r(curl_getinfo($ch));
			print $header;
			print $response;
		}
		
		if ( $returnRawResponse ) return $response;
		else return json_decode($response);
	}
	
	
	//- mark oAuth Functions
	
	function getDialogURL() {
		$dialog_url = 'https://launchpad.37signals.com/authorization/new?type=web_server&client_id='.$this->client_id.'&redirect_uri='.urlencode('http://'.$_SERVER['HTTP_HOST'].dirname(strtok($_SERVER['REQUEST_URI'],'?')).$this->oauth_url);
		return $dialog_url;
	}
	
	function redirectToDialog() {
		$dialog_url = $this->getDialogURL();
		header("Location: $dialog_url");
	}
	
	function fetchToken($code) {
		$response = $this->_makeRequest('https://launchpad.37signals.com/authorization/token',array(
			'code'=>$code,
			'client_id'=>$this->client_id,
			'client_secret'=>$this->client_secret,
			'type'=>'web_server',
			'redirect_uri'=>'http://'.$_SERVER['HTTP_HOST'].dirname(strtok($_SERVER['REQUEST_URI'],'?')).$this->oauth_url
		),'POST');
	
		
		$decoded = json_decode($response);
		$_SESSION[$this->session_key] = $decoded;		
		return $decoded;
	} 
	
	function refreshToken($refreshToken) {
		$response = $this->_makeRequest('https://launchpad.37signals.com/authorization/token',array(
			'type' => 'refresh',
			'client_id'=>$this->client_id,
			'client_secret'=>$this->client_secret,		
			'redirect_uri'=>'http://'.$_SERVER['HTTP_HOST'].dirname(strtok($_SERVER['REQUEST_URI'],'?')).$this->oauth_url,
			'refresh_token'=>$refreshToken
		),'POST');
		$decoded = json_decode($response);
		$_SESSION[$this->session_key] = $decoded;		
		return $decoded;
	}

	function _makeRequest($resource,$params,$method='GET',$headers=array()) {
		$all_headers = $default_headers = array('Content-Type: application/json',"User-Agent: $this->app_name");
		try {
			$this->_oAuth->fetch($resource,$params,$method,$all_headers);
			return $this->_oAuth->getLastResponse();
		} catch ( OAuthException $e ) {
			return $e;
		}
	}	
}
?>