<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . "/libraries/REST_Controller.php";

class Authentication extends REST_Controller {

    public function __construct() { 
        parent::__construct();
        
        $this->load->model("user");
        $this->load->model("user_activity_model");
    }
    
    public function login_post() {

        $email = $this->input->post("email");
        $password = $this->input->post("password");
        
        if(!empty($email) && !empty($password)){
            
            // revisa si un usuario con esas credenciales existe
            $tblRes["returnType"] = "unico";
            $tblRes["params"] = array(
                "email" => $email,
                "password" => md5($password)
            );
            $user = $this->user->getRows($tblRes);
            
            if($user){
                $this->response([
                    "status" => true,
                    "message" => "Login exitoso.",
                    "data" => $user
                ], REST_Controller::HTTP_OK);
            }else{
                //BAD_REQUEST (400) 
                $this->response("Contraseña o email equivocado.", REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            $this->response("Escribe un email y contraseña.", REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    public function registration_post() {

        $email = strip_tags($this->input->post("email"));
        $password = $this->input->post("password");
        
        if(!empty($email) && !empty($password)){
            
            // Revisa si el email (usuario) ya existe
            $tblRes["returnType"] = "multi";
            $tblRes["params"] = array(
                "email" => $email,
            );
            $userCount = $this->user->getRows($tblRes);
            
            if($userCount > 0){
                $this->response([
                        "status" => false,
                        "message" => "El usuario ya existe.",
                        "data" => array()
                    ], REST_Controller::HTTP_BAD_REQUEST);
            }else{
                $userData = array(
                    "email" => $email,
                    "password" => md5($password)
                );
                $insert = $this->user->insert($userData);
                
                // Revisa si fue insertado el usuario
                if($insert){
                    $this->response([
                        "status" => true,
                        "message" => "El usuario se agregó satisfactoriamente.",
                        "data" => $insert
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response("Hubo un problema, por favor intenta de nuevo.", REST_Controller::HTTP_BAD_REQUEST);
                }
            }
        }else{
            $this->response("Provee la información completa a agregar.", REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    public function user_get($id = 0) {
        // Si no hay id, regresa todos los usuarios
        $tblRes = $id ? array("id" => $id) : "";
        $users = $this->user->getRows($tblRes);
        
        if(!empty($users)){
            //OK (200)
            $this->response($users, REST_Controller::HTTP_OK);
        }else{
            //NOT_FOUND (404) 
            $this->response([
                "status" => false,
                "message" => "No se encontró usuario con ese id."
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }


    public function conversations_post(){

        $uid = strip_tags($this->input->post("uid"));

        if(!empty($uid)){
            $user_exists = $this->user->getRows(array("id" => $uid));
            $res         = array();
            $finalRes    = array();
            
            if ($user_exists) {
                $all_convos = $this->user_activity_model->get_by($user_exists["id"]);
                //return $res = $all_convos;
                if (sizeof($all_convos) > 0) {
                    $i = 0;
                    foreach ($all_convos as $row) {
                        // print_r("|||||||");
                        // print_r($val);
                        // print_r($key);
                        // print_r("|||||||");
                        $res[$i]["conversation"][] = array(
                            "id"          => $row["id"],
                            "messageFrom" => $row["message_from"],
                            "value"       => $row["message_text"],
                            "timestamp"   => intval($row["timestamp"]),
                        );
                        $get_datetime = $row["timestamp"];
                        $i++;
                    }
                    
                    $x = 0;
                    foreach($res as $re){
                        $temp = $re["conversation"];
                        foreach($temp as $r){
                            $finalRes[$x] = $r;
                            $x++;
                        }
                    }

                    $this->response([
                        "code" => REST_Controller::HTTP_OK,
                        "payload" => $finalRes//$res
                    ], REST_Controller::HTTP_OK);
                }
            }else{
                //NOT_FOUND (404) 
                $this->response([
                    "status" => false,
                    "message" => "No se encontró usuario con ese id."
                ], REST_Controller::HTTP_NOT_FOUND);
            }

            // if(!empty($user_exists)){ 
            //     $i = 0;
            //     foreach ($all_convos as $row) {

            //         //$res[$i]["conversation"][] = array(
            //         $res[$i] = array(
            //             "id"          => $row["id"],
            //             "messageFrom" => $row["message_from"],
            //             "value"       => $row["message_text"],
            //             "timestamp"   => intval($row["timestamp"]),
            //         );
            //         $get_datetime = $row["timestamp"];

            //         $i++;
            //     }
                
            //     $this->response([
            //         "code" => REST_Controller::HTTP_OK,
            //         "payload" => $res
            //     ], REST_Controller::HTTP_OK);
                    
            // }else{
            //     //NOT_FOUND (404) 
            //     $this->response([
            //         "status" => false,
            //         "message" => "No se encontró usuario con ese id."
            //     ], REST_Controller::HTTP_NOT_FOUND);
            // }
            
            //echo json_encode($res, JSON_UNESCAPED_UNICODE);
        }else{
            //NOT_FOUND (404) 
            $this->response([
                "status" => false,
                "message" => "Por favor escribe el id que buscar."
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

}