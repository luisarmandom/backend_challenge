<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_Activity_Model extends CI_Model {

    public function __construct() {
        parent::__construct();
        
        $this->load->database();
        
        $this->table = "user_activities";
    }

    function get_by($id){

        $result = "";
        $this->db->select("*");
        $this->db->from($this->table);

        $this->db->where("uid", $id);
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }
}