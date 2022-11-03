<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Model {

    public function __construct() {
        parent::__construct();
        
        $this->load->database();
        
        $this->table = "users";
    }

    function getRows($params = array()){
        $this->db->select("*");
        $this->db->from($this->table);
        
        if(empty($params)){
            $params = array();
        }

        if(array_key_exists("params", $params)){
            foreach($params["params"] as $key => $value){
                $this->db->where($key, $value);
            }
        }
        
        if(array_key_exists("id", $params)){
            $this->db->where("id", $params["id"]);
            $query = $this->db->get();
            $result = $query->row_array();
        }else{
            if(array_key_exists("returnType", $params) && $params["returnType"] == "unico"){
                $query = $this->db->get();
                $result = ($query->num_rows() > 0) ? $query->row_array() : false;
            }else{
                $query = $this->db->get();
                $result = ($query->num_rows() > 0) ? $query->result_array() : false;
            }
        }

        return $result;
    }
    
    public function insert($data){
        if(!array_key_exists("created_at", $data)){
            $data["created_at"] = date("Y-m-d H:i:s");
        }
        
        $insert = $this->db->insert($this->table, $data);
        
        return $insert ? $this->db->insert_id() : false;
    }

}