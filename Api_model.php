<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Api_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }
	
	//get user hash
	function getHash($username){
		$this->db->select('u_password');
		$this->db->from('user');
		$this->db->where('u_username', $username);
		$result = $this->db->get();
		
		$result = $result->row_array();
		return $result['u_password'];
	}
	
	//get user model data
	function getUserLoginData($username){
		$this->db->select('user_id,u_username,u_email,u_user_billing_key,u_user_detail_key,u_activation_link');
		$this->db->from('user');
		$this->db->where('u_username', $username);
		$result = $this->db->get();
		return $result->row_array();
	}
	
	//get user group
	function getUserGroupData($userId){
		$this->db->select('user_group_id, ug_name, ug_description');
		$this->db->from('user_group');
		$this->db->join('user_group_assignment', 'user_group_id=uga_user_group_key');
		$this->db->where('uga_user_key', $userId);
		$result = $this->db->get();
		return $result->result_array();
	}

	//get user projects
	function getUserProjects($userId){
		$this->db->select('*');
		$this->db->from('project');
		$this->db->where('p_user_key', $userId);
		
		return $this->db->get()->result_array();
	}
	
	//get project rfis
	function getProjectRfis($userId, $projectId){
		$this->db->select('*');
		$this->db->from('rfi');
		$this->db->where('rfi_project_key', $projectId);
		$this->db->where('rfi_user_key', $userId);
		
		return $this->db->get()->result_array();
	}
	
	//get project documents
	function getProjectDocuments($userId, $projectId){
		$this->db->select('*');
		$this->db->from('project_file');
		$this->db->join('project', 'project_id=pf_project_key');
		$this->db->where('pf_project_key', $projectId);
		$this->db->where('p_user_key', $userId);
		
		return $this->db->get()->result_array();
	}
	
	
	//get rfi data
	function getRfi($rfiId){
		$this->db->select('*');
		$this->db->from('rfi');
		
		//joins
		$this->db->join('rfi_status', 'rfi_status_id=rfi_rfi_status_key');
		
		//where
		$this->db->where('rfi_id', $rfiId);
		
		return $this->db->get()->result_array();
	}
	
	//get rfi messages
	function getRfiMessages($rfiId){
		$this->db->select('*');
		$this->db->from('rfi_message');
		$this->db->where('rm_rfi_key', $rfiId);
		
		return $this->db->get()->result_array();
	}
	
	//get rfi filelist
	function getRfiFilelist($rfiId){
		$this->db->select('*');
		$this->db->from('rfi_file');
		$this->db->join('file_status', 'file_status_id=rf_file_status_key');
		//$this->db->join('rfi_edit', 're_rfi_file_key=rfi_file_id', 'outer left');
		$this->db->where('rf_rfi_key', $rfiId);
		
		return $this->db->get()->result_array();
	}
	
	//get rfi filelist
	function getRfiEditlist($rfiId){
		$this->db->select('*');
		$this->db->from('rfi_edit');
		$this->db->join('file_status', 'file_status_id=re_file_status_key');
		$this->db->join('rfi_file', 're_rfi_file_key=rfi_file_id', 'outer left');
		$this->db->where('re_rfi_key', $rfiId);
		
		return $this->db->get()->result_array();
	}
	
	//get rfi file data
	function getRfiFiledata($rfiId, $rfiFileId){
		$this->db->select('*');
		$this->db->from('rfi_file');
		$this->db->join('file_status', 'file_status_id=rf_file_status_key');
		$this->db->where('rf_rfi_key', $rfiId);
		$this->db->where('rfi_file_id', $rfiFileId);
		
		return $this->db->get()->row_array();
	}
	
	//get rfi file data
	function getRfiEditfile($rfiId, $rfiFileId){
		$this->db->select('*');
		$this->db->from('rfi_edit');
		$this->db->join('file_status', 'file_status_id=re_file_status_key');
		$this->db->join('rfi_file', 're_rfi_file_key=rfi_file_id', 'outer left');
		$this->db->where('re_rfi_key', $rfiId);
		$this->db->where('rfi_edit_id', $rfiFileId);
		
		return $this->db->get()->row_array();
	}
	
	//get rfi file data
	function getProjectDocument($projectId, $fileId){
		$this->db->select('*');
		$this->db->from('project_file');
		$this->db->join('file_status', 'file_status_id=pf_file_status_key');
		$this->db->where('project_file_id', $fileId);
		$this->db->where('pf_project_key', $projectId);
		
		return $this->db->get()->row_array();
	}
	
	//check if RFI exist
	/*
	function rfiExist($projectFileId){
		$this->db->select('*');
		$this->db->from('rfi');
		$this->db->where('rfi_project_file_key', $projectFileId);
		$result = $this->db->get();
		
		if($result->result_id->num_rows == 0) return false;
		else return true;
	}
	
	//get rfi id
	
	function getRfiId($projectFileId){
		$this->db->select('rfi_id');
		$this->db->from('rfi');
		$this->db->where('rfi_project_file_key', $projectFileId);
		$result = $this->db->get()->row_array();
		$result = $result['rfi_id'];
		
		return $result;
		
	}
	*/
	
	//create RFI from project file
	function createRfi($rfiData){
		$this->db->insert('rfi', $rfiData);
		return $this->db->insert_id();
	}
	
	//add file to project
	function addFileToProject($filename, $projectId, $hash){

		$data = array(
			'pf_name' => $filename,
			'pf_hash' => $hash,
			'pf_project_key' => $projectId,
			'pf_file_status_key' => 1
			);
		$this->db->insert('project_file', $data);

	}
	
	//add message to rfi
	function addRfiMessage($messageData){
		$this->db->insert('rfi_message', $messageData);
		return $this->db->insert_id();
	}
	

	//add file to project
	function addFileToRfi($filename, $rfiId, $hash){

		$data = array(
			'rf_name' => $filename,
			'rf_hash' => $hash,
			'rf_rfi_key' => $rfiId,
			'rf_file_status_key' => 1
			);
		$this->db->insert('rfi_file', $data);
		
	}
	
	//upload file edits
	function addFileEdit($name, $rfiId, $orig, $hash, $user){
		
		//check if entry exists
		$this->db->select('*');
		$this->db->from('rfi_edit');
		$this->db->where('re_hash', $hash);
		$this->db->where('re_name', $name);
		$result = $this->db->get();
		
		//insert if not exist
		if($result->result_id->num_rows != 0) return true;
		else{
			//update entry
			$object = array(
				're_user_key' => $user,
				're_rfi_key' => $rfiId,
				're_name' => $name,
				're_hash' => $hash,
				're_rfi_file_key' => $orig,
				're_file_status_key' => 1
				);
			$this->db->insert('rfi_edit', $object);
		}
	}
	
}