<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class CreateJwt extends CI_Controller {

	function __construct()
	{
		# code...
		parent::__construct();

		$this->load->library('JWT.php');

	}

		public function createJwt()
		{

			$this->load->library('form_validation');
			$sno = $this->input->post('sno');
			$this->form_validation->set_rules('sno','sno','required');

			if($this->form_validation->run() != false)
			{


				$key = "salt:let's encrypt";
				$sno = "031502424";
				$token = array(
				"sno"		=> $sno,
			    "exp"       => $_SERVER['REQUEST_TIME']+604800
			    // "exp"       => $_SERVER['REQUEST_TIME']-1

				);
				// echo $jwt = JWT::encode($token, $key);


	   			$this->load->library('JWT');
	   			$objJWT = new JWT();
				$jwt = $objJWT->encode($token, $key);

				echo $jwt;
		    }

		}

	}