<?php

defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set("Asia/Shanghai");

/**
* 
*/
class Books extends MY_Controller 
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Books_model');
	}

	/**
	*捐赠图书
	*/
	function donate_books()
	{
		try 
		{
			$this->load->library('form_validation');

			// $this->form_validation->set_rules('days','days','required');//捐赠天数
			$this->form_validation->set_rules('type','type','required|greater_than[0]');//必须大于0
			$this->form_validation->set_rules('books_name','books_name','required');//必须为整数
			// 'donate_books_info'
			if($this->form_validation->run() != false)
			{
				$data = array(
					'books_name' => $this->input->post('books_name',true),
					'type' => $this->input->post('type',true),
					'sno' => $this->sno,
					'status' => 1,//0表示未租借
					'rent_times' => 0,
					'deadline' => 0,//表示没有租借
				);

				/*插入数据库*/
				$bid = $this->Books_model->donate_books($data);
				if($bid == false)
				{
					throw new Exception("图书信息插入错误", 1);
				}
				else
				{

					$res = $this->donate_books_image($bid);
					if($res == false)
					{
						throw new Exception("图书图片插入错误！", 1);
					}

					$json['books'] = $this->Books_model->show_books_by_type(20000, 0);
					echo_success($json);
				}
			}
			else
			{
				$message = trim(strip_tags(validation_errors())) ;
				throw new Exception($message, 1);
			}
			
		} 
		catch (Exception $e) 
		{
			echo_failure(60101,$e->getMessage());
		}
	}

	function donate_books_image($bid)
	{
		$this->load->library('upload');

		$i = 0;
		foreach($_FILES as $key => $val)
		{
			/*组合$config*/
			$i++;
            $config['upload_path'] = '/usr/share/nginx/html/yibao/public/books/';
            //$config['upload_path'] = './public/books_photo/';
            $config['allowed_types'] = 'jepg|jpg|png';
            $config['file_name'] = $this->sno.time().$i;//命名方式为学号+时间+第几张图片
            $config['max_size'] = 10240;
            //$config['max_width'] = 1024;
            //$config['max_height'] = 768;

            /*图片上传到固定文件下，将路径插入数据库*/
            $this->upload->initialize($config);
            if (! $this->upload->do_upload($key)) 
            {
            	//$message = strip_tags($this->upload->display_errors());//上传错误原因
            	//echo_failure('6001',$message);
            	return false;
            }
            else
            {
            	$data['bid'] = $bid;
            	$file_info = $this->upload->data();
                $data['path'] = 'public/books/'.$file_info['orig_name'];
            	
            	$bimage_id = $this->Books_model->donate_books_image($data);
			    if($bimage_id == false)
			    {
			        return false;
			    } 	
            }
        }
        return true;
	}

	/**
	*根据类别显示商品接口
	*/
	function show_books_by_type()
	{
		try
		{
			$this->load->library('form_validation');
			$this->form_validation->set_rules('type','type','required|greater_than[0]');//必须大于0
			$this->form_validation->set_rules('page','page','required|integer');//必须为整数

			/*验证输入*/
			if($this->form_validation->run() != false)
			{
		        $type = $this->input->post('type');//根据图书类别
				$page = $this->input->post('page');

				$json['books'] = $this->Books_model->show_books_by_type($type,$page);
				echo_success($json);
			}
			else
			{
				$message = trim(strip_tags(validation_errors()));
				throw new Exception($message, 1);
			}
			
		}
		catch(Exception $e)
		{
			echo_failure(60102,$e->getMessage());
		}
		
	}

	/**
	*租借图书
	*/
	public function rent_books()
	{
		try
		{
			$this->load->library('form_validation');
			$this->form_validation->set_rules('bid','bid','required');
			$this->form_validation->set_rules('days','days','required');

			if($this->form_validation->run() != false)
			{
				$bid = $this->input->post('bid');
				$days = $this->input->post('days');

				$res = $this->Books_model->bid_is_exist($bid);
				if($res == false)
				{
					throw new Exception("图书ID不存在！", 1);
				}

				// 判断该图书可不可借,租借图书
				$res = $this->Books_model->rent_books($bid,$days);
				if($res == 0)
				{
					throw new Exception("图书已经被租借！", 1);
				}
				else if($res == 1)
				{
					throw new Exception("图书租借信息记录错误，暂不可租借！", 1);
				}
				else
				{
					$json['books'] = $this->Books_model->show_books_by_type(20000, 0);
					echo_success($json);
				}

			} 
			else 
			{
				$message = trim(strip_tags(validation_errors()));
				throw new Exception($message, 1);
			}

		}
		catch(Exception $e)
		{
			echo_failure(60103,$e->getMessage());
		}
	}

	public function return_books ()
	{
		try
		{
			$this->load->library('form_validation');
			$this->form_validation->set_rules('bid','bid','required');

			if($this->form_validation->run() != false)
			{
				$bid = $this->input->post('bid');
				$res = $this->Books_model->bid_is_exist($bid);
				if($res == false)
				{
					throw new Exception("图书ID不存在！", 1);
				}

				//还书
				$res = $this->Books_model->return_books($bid);
				if($res == false)
				{
					throw new Exception("图书归还信息记录错误,请再次尝试！", 1);
				}
				
				$json['books'] = $this->Books_model->show_books_by_type(20000, 0);
				echo_success($json);
				
			} 
			else 
			{
				$message = trim(strip_tags(validation_errors()));
				throw new Exception($message, 1);
			}

		}
		catch(Exception $e)
		{
			echo_failure(60103,$e->getMessage());
		}
		
	}
}
?>