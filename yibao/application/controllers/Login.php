<?php
header("content-type:text/html;charset=utf-8");         //设置编码

defined('BASEPATH') OR exit('No direct script access allowed');

// use \Firebase\JWT\JWT;
// include('D:wamp64/www/yibao/third_party/JWT.php');


class Login extends CI_Controller {


	function __construct()
	{
		parent::__construct();
		$this->load->helper('post_login');

		$this->load->model('user_model');
	}



	public function login()
	{

		try
		{



			$this->load->library('form_validation');
			$this->form_validation->set_rules('sno','sno','required');
			// $this->form_validation->set_rules('password','password','required');


			if($this->form_validation->run() != false)
			{
					$sno = $this->input->post('sno',true);
					// $password = $this->input->post('password',true);



					$user = array(
						'sno' => '',
						'phone' => '',
						'user_name' => '',
						// 'nickname' => $user['nickname'],
						'major' => '',
						'grade' => '',
						// 'avatar_path' => ''
					);

					if($this->user_model->get_user($sno) == false) //此时数据库未有该学生的数据
					{
						foreach($user as $key => $value)
							{
								$user[$key] = $this->input->post($key,true);
							}


							$data = array(
								'sno' => $sno,
								'phone' => $user['phone'],
								'user_name' => $user['user_name'],
								'nickname' => 'user'. $sno,
								'major' => $user['major'],
								'grade' => $user['grade'],
								'avatar_path' => 'public/avatars/default.jpg'
							);

							$uid = $this->user_model->add_user($data);//对学生数据进行插入
							if($uid === false)
							{
								// echo ;
								throw new Exception('数据库插入失败，请重新尝试');

							}else
							{
								$key = "salt:let's encrypt";
								$token = array(
								"sno"		=> $sno,
							    "exp"       => $_SERVER['REQUEST_TIME']+604800
							    // "exp"       => $_SERVER['REQUEST_TIME']-1

								);
								// echo $jwt = JWT::encode($token, $key);
					   			$this->load->library('JWT');
					   			$objJWT = new JWT();
								$jwt = $objJWT->encode($token, $key);
								$message = '登录成功';
								$error_code = 0;
								$data['jwt'] = $jwt;
								$json['user'] = $data;

								echo_success($json);
							}

							

					}else
					{

							$data = $this->user_model->get_info($sno);
							$key = "salt:let's encrypt";
							$token = array(
							"sno"		=> $sno,
						    "exp"       => $_SERVER['REQUEST_TIME']+604800
						    // "exp"       => $_SERVER['REQUEST_TIME']-1

							);
							// echo $jwt = JWT::encode($token, $key);
				   			$this->load->library('JWT');
				   			$objJWT = new JWT();
							$jwt = $objJWT->encode($token, $key);
							$message = '登录成功';
							$error_code = 0;
							$data[0]['jwt'] = $jwt;
							$json['user'] = $data[0];


							echo_success($json );
						
					}

			}else
			{
				$message = trim(strip_tags(validation_errors())) ;
				throw new Exception($message, 1);
			}



		}
		catch(Exception $e)
		{

			echo_failure(40001,$e->getMessage());
			return;
		}

	}


}


