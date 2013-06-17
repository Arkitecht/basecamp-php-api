<?
require_once(dirname(__FILE__)."/Object.php");
class Basecamp_Project extends Basecamp_Object {		
	public function __construct($account,$id,$basecamp='') {
		$this->account 	= $account;
		$this->id				= $id;
		if ( $basecamp ) {
			$this->_basecamp = $basecamp;
			$this->_basecamp->setAccount($account);
		}
	}
	
	public static function objectFromProject($projectObject,$basecamp='') {
		$project = new Basecamp_Project('','');
		foreach ((array)$projectObject as $key => $val ) {
			$project->$key = $val;
		}
		if ( $basecamp ) {
			$project->setBasecamp($basecamp);
			$project->account = $basecamp->account;
		}
		return $project;
	}
		
	//- mark Access	
	function getAccess($id) {
		return $this->_basecamp->getAccesses('projects',$this->id);
	}

	function grantAccess($users=array(),$emails=array()) {
		return $this->_basecamp->grantAccesss('projects',$this->id,$users,$emails);
	}
	
	function revokeAccess($user) {
		return $this->_basecamp->revokeAccess('projects',$this->id,$user);
	}

	//- mark Events
	function getEvents($since='',$page='') {
		return $this->_basecamp->getEvents($since,$page,'projects',$this->id);
	}
	
	//- mark Topics
	function getTopics($page='') {
		return $this->_basecamp->getTopics($page,'projects',$this->id);
	}
		
	//- mark Messages	
	function getMessages($page='',$type='',$id='') {
		return $this->_basecamp->getTopics($page,'projects',$this->id);
	}
	
	function getMessage($id) {
		return $this->_basecamp->getMessage($this->id,$id);
	}
	
	function createMessage($subject,$content,$subscribers=array()) {
		return $this->_basecamp->createMessage($this->id,$subject,$content,$subscribers);
	}
	
	function updateMessage($id,$subject,$content) {
		return $this->_basecamp->updateMessage($this->id,$id,$subject,$content);
	}
	
	function deleteMessage($id) {
		return $this->_basecamp->deleteMessage($this->id,$id);
	}	
	
	//- mark Attachments
	function getAttachments($page='') {
		return $this->_basecamp->getAttachments($page,$this->id);
	}
	
	//- mark Uploads
	function createUpload($tokenOrFilename,$name,$content,$subscribers=array(),$isFilename=false) {
		return $this->_basecamp->createUpload($this->id,$tokenOrFilename,$name,$content,$subscribers,$isFilename);
	}
	
	function getUpload($id) {
		return $this->_basecamp->getUpload($this->id,$id);
	}
	
	//- mark Todo Lists
	function getTodoLists($completed=false) {
		return $this->_basecamp->getTodoLists($completed,$this->id);	
	}
	
	function getTodoList($id) {
		return $this->_basecamp->getTodoList($this->id,$id);
	}
	
	function createTodoList($name,$description) {
		return $this->_basecamp->createTodoList($this->id,$name,$description);
	}
	
	function updateTodoList($id,$name,$description,$position='') {
		return $this->_basecamp->updateTodoList($this->id,$id,$name,$description,$position);
	}
	
	function deleteTodoList($id) {
		return $this->_basecamp->deleteTodoList($this->id,$id);
	}
	
	//-mark Todos
	
	function getTodo($id) {
		return $this->_basecamp->getTodo($this->id,$id);
	}
	
	function createTodo($list,$todo,$dueAt='',$assignee='') {
		return $this->_basecamp->createTodo($this->id,$list,$todo,$dueAt,$assignee);
	}
		
	function updateTodo($project,$id,$todo,$dueAt='',$assignee='',$completed='',$position='') {
		return $this->_basecamp->updateTodo($this->id,$id,$todo,$dueAt,$assignee,$completed,$position);
	}
	
	function completeTodo($id) {
		return $this->_basecamp->updateTodo($this->id,$id,'','','',true);
	}
	
	function unCompleteTodo($id) {
		return $this->_basecamp->updateTodo($this->id,$id,'','','',false);
	}
	
	function reorderTodo($id,$position) {
		return $this->_basecamp->updateTodo($this->id,$id,'','','','',$position);
	}
	
	function deleteTodo($id) {
		return $this->_basecamp->deleteTodo($this->id,$id);
	}
	
	//-mark Comments
	function createComment($on,$id,$comment,$subscribers='',$attachmentName='',$tokenOrFilename='',$isFilename=false) {
		return $this->_basecamp->createComment($this->id,$on,$id,$comment,$subscribers,$attachmentName,$tokenOrFilename,$isFilename);
	}
	
	function createTodoComment($id,$comment,$subscribers='',$attachmentName='',$tokenOrFilename='',$isFilename=false) {
		return $this->createComment($this->id,'todos',$id,$comment,$subscribers,$attachmentName,$tokenOrFilename,$isFilename);
	}
	
	function createMessageComment($id,$comment,$subscribers='',$attachmentName='',$tokenOrFilename='',$isFilename=false) {
		return $this->createComment($this->id,'messages',$id,$comment,$subscribers,$attachmentName,$tokenOrFilename,$isFilename);
	}
	
	function deleteComment($id) {
		return $this->_basecamp->deleteComment($this->id,$id); 
	}
	
	//- mark Documents
	function getDocuments() {
		return $this->_basecamp->getDocuments($this->id);	
	}	
	
	function getDocument($id) {
		return $this->_basecamp->getDocument($this->id,$id);
	}
	
	function createDocument($title,$content) {
		return $this->_basecamp->createDocument($this->id,$title,$content);
	}
	
	function updateDocument($id,$title,$content) {
		return $this->_basecamp->updateDocument($this->id,$id,$title,$content);
	}
	
	function deleteDocument($id) {
		return $this->_basecamp->deleteDocument($this->id,$id);
	}

	//-mark Milestones (Calendar Events)
	function getMilestones($past=false) {
		return $this->_basecamp->getMilestones('projects',$this->id,$past);
	}
		
	function getMilestone($id) {
		return $this->_basecamp->getMilestone('projects',$this->id,$id);
	}
	
	function createMilestone($summary,$description,$starts_at,$ends_at='',$all_day=false) {
		return $this->_basecamp->createMilestone('projects',$this->id,$summary,$description,$starts_at,$ends_at,$all_day);
	}
	
	function updateMilestone($id,$summary='',$description='',$starts_at='',$ends_at='',$all_day='') {
		return $this->_basecamp->updateMilestone('projects',$this->id,$id,$summary,$description,$starts_at,$ends_at,$all_day);
	}
	
	function deleteMilestone($id) {
		return $this->_basecamp->deleteMilestone('projects',$this->id,$id);	
	}
	
/*	
	function create($name,$description) {
		return $this->_makeAuthenticatedRequest($this->getAccountURL('projects.json'),json_encode(array('name'=>$name,'description'=>$description)),'POST');
	}
*/
	
	function update($name,$description) {
		$this->name 				= $name;
		$this->description 	= $description;
		return $this->_basecamp->updateProject($this->id,$name,$description);
	}
	
	function archive() {
		return $this->_basecamp->archiveProject($this->id);
	}
	
	function unArchive($id) {
		return $this->_basecamp->unArchiveProject($this->id);
	}
	
	function delete() {
		return $this->_basecamp->deleteProject($this->id);
	}
}

?>