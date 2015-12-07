<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	function __construct(){	
		parent::__construct();

		//headers
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");
		
		//load model
		$this->load->model('front/api_model');
		$this->load->model('front/user_model');
		
		//get method
		$this->method = $_SERVER['REQUEST_METHOD'];
		
		//get user data
		$this->userData = $this->session->get_userdata();
		
		//some restricted chars
		$this->restricted = array(" ", "/", ",", "\\", "?", "'", "\"", "%", "&", "UNION", "SELECT", "NULL", "--");
	}
	
	//api login
	function login(){
	
		//json array return
		$jsonArray['status'] = 0;
		$jsonArray['message'] = "Error";
		
		if($this->method == "POST"){
			//login data
			$username = $this->input->post('username', true);
			$password = $this->input->post('password', true);
		
			//get password hash
			$hash = $this->user_model->getHash($username);
			$test = password_verify($password, $hash);

			//check test
			if($test){
				//get required user data
				$userLoginData = $this->user_model->getUserLoginData($username);
				$userGroupData = $this->user_model->getUserGroupData($userLoginData['user_id']);
			
				if($userLoginData['u_activation_link'] != 1){
					$jsonArray['message'] = "This account is not activated";
					$jsonArray['status'] = 0;
					$this->menuData['message'][] = "Your account is not activated!";
				}else{
					$this->session->set_userdata('user', $userLoginData);
					$this->session->set_userdata('usergroup', $userGroupData);
					$this->session->set_userdata('isUserGroup', isUserGroup());
					$this->session->set_userdata('isAdminGroup', isAdminGroup());
					$this->session->set_userdata('isEngiGroup', isEngiGroup());
					$this->session->set_userdata('isSubGroup', isSubGroup());
				
					//json
					$jsonArray['data'] = $this->session->get_userdata();
					$jsonArray['status'] = 1;
					$jsonArray['message'] = "OK";
				
				
				}
			}else{
				if($this->input->post())
				{
					$jsonArray['status'] = 0;
					$jsonArray['message'] = "Wrong login data";
				}else{
					$jsonArray['status'] = 0;
					$jsonArray['message'] = "Empty input data";
				}
			}
		}else{
			$jsonArray['status'] = 0;
			$jsonArray['message'] = "Only POST method allowed";
		}
		echo json_encode($jsonArray);
	}
	
	//initiate and resolve beginning
	private function initiate($methods){
		
		//initialize json array
		$jsonArray['status'] = 0;
		$jsonArray['message'] = "Error";
		
		if(in_array($this->method, $methods)){
		
			//get all user data from session
			solveGroupAssignment();
			
			if($this->menuData['isUserLogged'] != 0){
				
				$jsonArray['status'] = 1;
			
			}else{
				$jsonArray['status'] = 0;
				$jsonArray['message'] = "You are not logged in as proper user";
				
				
			}
			
		}else{
			$jsonArray['status'] = 0;
			$jsonArray['message'] = "Method not allowed";
			
		}
		
		
		return $jsonArray;
	
	}
	
	//get user projects
	function projects(){
		
		$jsonArray = $this->initiate(array("GET"));
		
		if($jsonArray['status'] != 0){
		
			//get project list
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "Projects retreived";
			$jsonArray['data'] = $this->api_model->getUserProjects($this->userData['user']['user_id']);
			
		}
		
		echo json_encode($jsonArray);
		return 1;
			
	}
	
	//get projec RFIs
	function projectRfis(){
	
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
		
			//get project ID
			$projectId = $this->input->post('projectId', true);
		
			//get rfi list
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "RFI list retreived";
			$jsonArray['data'] = $this->api_model->getProjectRfis($this->userData['user']['user_id'], $projectId);
			
		}
		
		echo json_encode($jsonArray);
		return 1;
		
	}
	
	//get all project documents
	function getProjectDocuments(){
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
		
			//get project ID
			$projectId = $this->input->post('projectId', true);
		
			//get rfi list
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "Project documents list retreived";
			$jsonArray['data'] = $this->api_model->getProjectDocuments($this->userData['user']['user_id'], $projectId);
			
		}
		
		echo json_encode($jsonArray);
		return 1;
	}
	
	//get document file
	function getProjectDocument(){
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			
			//get project id and file id
			$projectId = $this->input->post('projectId', true);
			$fileId = $this->input->post('fileId', true);
			
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "Document file retreived";
			$jsonArray['data'] = $this->api_model->getProjectDocument($projectId, $fileId);
			
			//set files path
			$path_original = 'uploads/'.$jsonArray['data']['pf_hash'].'/'.$jsonArray['data']['pf_name'];

			//get files data
			if(file_exists($path_original)){
				$jsonArray['data']['file_data_original'] = base64_encode(file_get_contents($path_original));
				
			}else{
				$jsonArray['status'] = 0;
				$jsonArray['message'] = "Original file does not exist on server";
			}
		}
		
		echo json_encode($jsonArray);
		return 1;
	}
	
	//get single rfi
	function getRfi(){
	
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
		
			//get RFI id
			$rfiId = $this->input->post('rfiId', true);
				
			//get RFI data
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "RFI retreived";
			$jsonArray['data'] = $this->api_model->getRfi($rfiId);
		}
		
		echo json_encode($jsonArray);
		return 1;
	}
	
	//create rfi
	function createRfi(){
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			
			//set RFI data
			$rfiData['rfi_name'] = $this->input->post('rfiName', true);
			$rfiData['rfi_description'] = $this->input->post('rfiDescription', true);
			$rfiData['rfi_project_key'] = $this->input->post('projecId', true);
			
			$rfiData['rfi_user_key'] = $this->userData['user']['user_id'];
			$rfiData['rfi_rfi_status_key'] = 1;
			
			//create RFI
			$rfiId = $this->api_model->createRfi($rfiData);
			
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "RFI created";
			$jsonArray['data'] = $this->api_model->getRfi($rfiId);
		}
		
		echo json_encode($jsonArray);
		return 1;
		
	}
	
	//get rfi messages
	function getRfiMessages(){
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			//get RFI id
			$rfiId = $this->input->post('rfiId', true);
			
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "RFI messages retreived";
			$jsonArray['data'] = $this->api_model->getRfiMessages($rfiId);
		}
		
		echo json_encode($jsonArray);
		return 1;
	}
	
	//get RFI files
	function getRfiFilelist(){
	
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			//get RFI id
			$rfiId = $this->input->post('rfiId', true);
			
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "RFI filelist retreived";
			$jsonArray['data'] = $this->api_model->getRfiFilelist($rfiId);
		}
		
		echo json_encode($jsonArray);
		return 1;
	
	}
	
	//get RFI edit files
	function getRfiEditlist(){
	
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			//get RFI id
			$rfiId = $this->input->post('rfiId', true);
			
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "RFI editlist retreived";
			$jsonArray['data'] = $this->api_model->getRfiEditlist($rfiId);
		}
		
		echo json_encode($jsonArray);
		return 1;
	
	}
	
	//get RFI file
	function getRfiFile(){
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			//get RFI id
			$rfiId = $this->input->post('rfiId', true);
			$rfiFileId = $this->input->post('rfiFileId', true);
			
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "RFI file retreived";
			$jsonArray['data'] = $this->api_model->getRfiFiledata($rfiId, $rfiFileId);
			
			//set files path
			$path_original = 'uploads/'.$jsonArray['data']['rf_hash'].'/'.$jsonArray['data']['rf_name'];

			//get files data
			if(file_exists($path_original)){
				$jsonArray['data']['file_data_original'] = base64_encode(file_get_contents($path_original));
			}else{
				$jsonArray['status'] = 0;
				$jsonArray['message'] = "Original file does not exist on server";
			}
		}
		
		echo json_encode($jsonArray);
		return 1;
	}
	
	//get RFI file
	function getRfiEditFile(){
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			//get RFI id
			$rfiId = $this->input->post('rfiId', true);
			$rfiEditId = $this->input->post('rfiEditId', true);
			
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "RFI file retreived";
			$jsonArray['data'] = $this->api_model->getRfiEditfile($rfiId, $rfiEditId);
			
			//set files path
			$path_edit = 'edits/'.$jsonArray['data']['re_hash'].'/'.$jsonArray['data']['re_name'];
			$path_original = 'uploads/'.$jsonArray['data']['rf_hash'].'/'.$jsonArray['data']['rf_name'];

			//get files data
			if(file_exists($path_original)){
				$jsonArray['data']['file_data_original'] = base64_encode(file_get_contents($path_original));
				
				if(file_exists($path_edit)){
					$jsonArray['data']['file_data_edit'] = base64_encode(file_get_contents($path_edit));
				}else{
					$jsonArray['status'] = 1;
					$jsonArray['message'] = "Edit file does not exist on server, but original file is retreived";
				}
			}else{
				$jsonArray['status'] = 0;
				$jsonArray['message'] = "Original file does not exist on server";
			}
		}
		
		echo json_encode($jsonArray);
		return 1;
	}
	
	//send message to rfi
	function sendRfiMessage(){
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			//get RFI id
			$messageData['rm_rfi_key'] = $this->input->post('rfiId', true);
			$messageData['rm_message_date'] = date('m/d/Y h:i:s');
			$messageData['rm_user_key'] = $this->userData['user']['user_id'];
			$messageData['rm_message'] = $this->input->post('rfiMessage', true);
			
			$jsonArray['status'] = 1;
			$jsonArray['message'] = "RFI message added";
			$messageId = $this->api_model->addRfiMessage($messageData);
			$jsonArray['data'] = $this->api_model->getRfiMessages($this->input->post('rfiId', true));
		}
		
		echo json_encode($jsonArray);
		return 1;
	}
	

	//upload files
	function uploadFile(){
	
		//todo: check if project belongs to logged in user
		//todo: add thumbnails
		
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			//get RFI id
			$projectId = $this->input->post('projectId', true);
			
			//generate hash
			$toHash = $this->userData['user']['u_username'].$this->config->item('encryption_key').$projectId;
			$this->folderHash = hash ('sha256', $toHash);
			
			//target dir
			$target_dir = "uploads/".$this->folderHash."/";
			$target_dir_tb = "uploads/tb_".$this->folderHash."/";
		
			//folders exist?
			if (!file_exists($target_dir)) {
				mkdir($target_dir, 0777, true);
			}
			if (!file_exists($target_dir_tb)) {
				mkdir($target_dir_tb, 0777, true);
			}
			
			//get file name and type
			$name = strtolower(basename($_FILES["files"]["name"]));
			$name = str_replace($this->restricted, "_", $name);
			
			// get file extension
			$imgName = explode('.', $name);
			$imageFileExtension = $imgName[count($imgName) - 1];
			
			//trim name
			$name = mb_strimwidth($imgName[0], 0, 10, "").".".$imageFileExtension;
			
			//setup files
			$target_file = $target_dir . basename($name);
			$target_file_tb = $target_dir_tb . basename($name);
			
			//setup vars
			$uploadOk = 1;
			$filemessage = '';
			$fileType = pathinfo($target_file,PATHINFO_EXTENSION);
			
			//pdf for thumb
			if($imageFileExtension == 'pdf'){
				$target_file_tb .= '.jpg';
			}
			
			//do file exist?
			if (file_exists($target_file)) {
				$name = time()."-".$name;
				$target_file = $target_dir . basename($name);
				$target_file_tb = $target_dir_tb . basename($name);
			
				//pdf for thumb
				if($imageFileExtension == 'pdf'){
					$target_file_tb .= '.jpg';
				}
			
			}
			
			//check if file is not injecting anything 
			if( strpos(file_get_contents($_FILES["files"]["tmp_name"]),"<?php") !== false) {
				$filemessage .= $name ." Hacking attempt or file not valid.\r\n";
				$uploadOk = 0;
			}
			
			//check if file is not injecting anything 
			if( strpos(file_get_contents($_FILES["files"]["tmp_name"]),"<script") !== false) {
				$filemessage .= $name ." Hacking attempt or file not valid.\r\n";
				$uploadOk = 0;
			}
			
			
			//allow only certain file extension
			$extensions = array('jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'pdf');
			if(!in_array($imageFileExtension, $extensions)) {
				$filemessage .= $name ." only JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, PDF files are allowed.\r\n";
				$uploadOk = 0;
			}
			
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 1) {
				if (move_uploaded_file($_FILES["files"]["tmp_name"], $target_file)) {
					
					//make thumbnail
					$this->make_thumb($target_file, $target_file_tb, 300);
					
					//add to database
					$this->api_model->addFileToProject($name, $projectId, $this->folderHash);
					
					$this->load->model('front/message_model');
					//$this->rfimessage_model->sendProjectEmail($projectId, 3, "Added Files To Project");
					$this->message_model->addLog($projectId, "Added Files To Project" , $this->userData['user']['user_id']);
		
					$filemessage .= "The file ". $name. " has been uploaded.\r\n";
		
				} else {
					$filemessage .= "There was an error uploading your file ". $name .".\r\n";
				}
			}else{
				$jsonArray['status'] = 0;
				$jsonArray['message'] = $filemessage;
				echo json_encode($jsonArray);
				return 1;
			}
		
			$jsonArray['status'] = 1;
			$jsonArray['message'] = $filemessage;
			//$jsonArray['data'] = $this->api_model->getRfiFilelist($rfiId);
		}
		
		echo json_encode($jsonArray);
		return 1;
	
	}
	
	//upload files
	function uploadRfiFile(){
	
		//todo: check if project belongs to logged in user
		//todo: add thumbnails
		
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			//get RFI id
			$rfiId = $this->input->post('rfiId', true);
			
			//generate hash
			$toHash = $this->userData['user']['u_username'].$this->config->item('encryption_key').$rfiId;
			$this->folderHash = hash ('sha256', $toHash);
			
			//target dir
			$target_dir = "uploads/".$this->folderHash."/";
			$target_dir_tb = "uploads/tb_".$this->folderHash."/";
		
			//folders exist?
			if (!file_exists($target_dir)) {
				mkdir($target_dir, 0777, true);
			}
			if (!file_exists($target_dir_tb)) {
				mkdir($target_dir_tb, 0777, true);
			}
			
				
			//get file name and type
			$name = strtolower(basename($_FILES["files"]["name"]));
			$name = str_replace($this->restricted, "_", $name);
			
			// get file extension
			$imgName = explode('.', $name);
			$imageFileExtension = $imgName[count($imgName) - 1];
			
			//get short name
			$name = mb_strimwidth($imgName[0], 0, 10, "").".".$imageFileExtension;
			
			//set paths
			$target_file = $target_dir . basename($name);
			$target_file_tb = $target_dir_tb . basename($name);
			
			//set vars
			$uploadOk = 1;
			$filemessage = '';
			$fileType = pathinfo($target_file,PATHINFO_EXTENSION);
			
			//pdf for thumb
			if($imageFileExtension == 'pdf'){
				$target_file_tb .= '.jpg';
			}
			
			//do file exist?
			if (file_exists($target_file)) {
				$name = time()."-".$name;
				$target_file = $target_dir . basename($name);
				$target_file_tb = $target_dir_tb . basename($name);
			
				//pdf for thumb
				if($imageFileExtension == 'pdf'){
					$target_file_tb .= '.jpg';
				}
			
			}
			
			//check if file is not injecting anything 
			if( strpos(file_get_contents($_FILES["files"]["tmp_name"]),"<?php") !== false) {
				$filemessage .= $name ." Hacking attempt or file not valid.\r\n";
				$uploadOk = 0;
			}
			
			//check if file is not injecting anything 
			if( strpos(file_get_contents($_FILES["files"]["tmp_name"]),"<script") !== false) {
				$filemessage .= $name ." Hacking attempt or file not valid.\r\n";
				$uploadOk = 0;
			}
			
			
			//allow only certain file extension
			$extensions = array('jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'pdf');
			if(!in_array($imageFileExtension, $extensions)) {
				$filemessage .= $name ." only JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, PDF files are allowed.\r\n";
				$uploadOk = 0;
			}
			
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 1) {
				if (move_uploaded_file($_FILES["files"]["tmp_name"], $target_file)) {
					
					//add to database
					$this->api_model->addFileToRfi($name, $rfiId, $this->folderHash);
					
					$this->load->model('front/rfimessage_model');
					//$this->rfimessage_model->sendProjectEmail($rfiId, 3, "Added Files To Project");
					$this->rfimessage_model->addLog($rfiId, "Added Files To Project" , $this->userData['user']['user_id']);
		
					$filemessage .= "The file ". $name. " has been uploaded.\r\n";
		
				} else {
					$filemessage .= "There was an error uploading your file ". $name .".\r\n";
				}
			}else{
				$jsonArray['status'] = 0;
				$jsonArray['message'] = $filemessage;
				echo json_encode($jsonArray);
				return 1;
			}
		
			$jsonArray['status'] = 1;
			$jsonArray['message'] = $filemessage;
			//$jsonArray['data'] = $this->api_model->getRfiFilelist($rfiId);
		}
		
		echo json_encode($jsonArray);
		return 1;
	
	}
	
	function uploadRfiEditFile(){
		//todo: check if project belongs to logged in user
		//todo: add thumbnails
		
		$jsonArray = $this->initiate(array("POST"));
		
		if($jsonArray['status'] != 0){
			//get RFI id
			$rfiId = $this->input->post('rfiId', true);
			$originalId = $this->input->post('originalId', true);
			
			//generate hash
			$toHash = $this->userData['user']['u_username'].$this->config->item('encryption_key').$rfiId;
			$this->folderHash = hash ('sha256', $toHash);
			
			//target dir
			$target_dir = "edits/".$this->folderHash."/";
		
			//folders exist?
			if (!file_exists($target_dir)) {
				mkdir($target_dir, 0777, true);
			}
			
			//get file name and type
			$name = strtolower(basename($_FILES["files"]["name"]));
			$name = explode('.', $name);
			$name = $name[0] . "-" . time() . ".jpg";
			//todo security: restricted
		
			$target_file = $target_dir . basename($name);
			
			$uploadOk = 1;
			$filemessage = '';
			$fileType = pathinfo($target_file,PATHINFO_EXTENSION);
			
			
			//check if file is not injecting anything 
			if( strpos(file_get_contents($_FILES["files"]["tmp_name"]),"<?php") !== false) {
				$filemessage .= $name ." Hacking attempt or file not valid.\r\n";
				$uploadOk = 0;
			}
			
			//check if file is not injecting anything 
			if( strpos(file_get_contents($_FILES["files"]["tmp_name"]),"<script") !== false) {
				$filemessage .= $name ." Hacking attempt or file not valid.\r\n";
				$uploadOk = 0;
			}
			
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 1) {
				if (move_uploaded_file($_FILES["files"]["tmp_name"], $target_file)) {
					
					//add to database
					//$this->api_model->addFileToRfi($name, $rfiId, $this->folderHash);
					$this->api_model->addFileEdit($name, $rfiId, $originalId, $this->folderHash, $this->userData['user']['user_id']);
					
					$this->load->model('front/rfimessage_model');
					//$this->rfimessage_model->sendProjectEmail($rfiId, 3, "Added Files To Project");
					$this->rfimessage_model->addLog($rfiId, "Added Files To Project" , $this->userData['user']['user_id']);
		
					$filemessage .= "The file ". $name. " has been uploaded.\r\n";
		
				} else {
					$filemessage .= "There was an error uploading your file ". $name .".\r\n";
				}
			}else{
				$jsonArray['status'] = 0;
				$jsonArray['message'] = $filemessage;
				echo json_encode($jsonArray);
				return 1;
			}
		
			$jsonArray['status'] = 1;
			$jsonArray['message'] = $filemessage;
			//$jsonArray['data'] = $this->api_model->getRfiFilelist($rfiId);
		}
		
		echo json_encode($jsonArray);
		return 1;
	
	}
	
	//edit files
	private function make_thumb($src, $dest, $desired_width) {
	
		if(getimagesize($src)){
			// read the source image
			$imgBlob = file_get_contents($src);
			$source_image = imagecreatefromstring($imgBlob);
			unset($imgBlob); 
			
			$width = imagesx($source_image);
			$height = imagesy($source_image);
		
			//save crop data
			$cropSize = $desired_width;
			
			//min height - increase to crop if necessary
			$desired_height = ceil($height * ($desired_width / $width));

			//check for min size
			if($desired_height < $desired_width){
				$ratio = $desired_width/$desired_height;
				$desired_height = $desired_width;
				$desired_width = ceil($ratio * $desired_height);
			}
			
			// create a new, "virtual" image
			$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
		
			// copy source image at a resized size
			imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
			
			// crop
			$virtual_image = imagecrop($virtual_image, array('x' => 0,'y' => 0, 'width' => $cropSize, 'height' => $cropSize));
		
			// create the physical thumbnail image to its destination
			imagejpeg($virtual_image, $dest);
			
		}else{
			// get file extension
			$imgName = explode('.', $src);
			$extension = $imgName[count($imgName) - 1];
			
			//if extension is PDF
			if($extension == "pdf"){
			
				$src = $this->config->item('base_path').$src;
				$dest = $this->config->item('base_path').$dest;
			
				//$magick_dir
				$send_cmd = $this->config->item('magick_dir') .'convert '.$src.' -resize 300x300 -gravity center -extent 300x300 '.$dest ;
				
				$result = exec($send_cmd, $out);
				
			}else{
				$src = 'assets/img/placeholders/'.$extension.'.png';
				
				//safeguarg
				if(!file_exists($src)){
					$src = 'assets/img/placeholders/placeholder.png';
				}
				
				copy ($src, $dest);
			}
			
			
		}

	}
}
