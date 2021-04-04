<?php

class Controller_User extends Controller_Admin_Template {

	public function action_register()
	{
		if($this->request->post())
		{
			try
			{
				$extra_rules = Validation::factory($this->request->post())
					->rule('password_confirm', 'matches', array(
						':validation', ':field', 'password'
					));
				
				$user = ORM::factory('user')
					->values($this->request->post())
					->save($extra_rules)
					->add('roles', ORM::factory('role', array('name' => 'login')));

				Auth::instance()->force_login($user);
				Message::success('auth.register.success');
				$this->request->redirect(Route::get('admin')->uri());
			}
			catch(ORM_Validation_Exception $e)
			{
				$errors = $e->errors('models');
			}
		}

		$this->template->content = View::factory('user/register')
			->set('values', $this->request->post())
			->bind('errors', $errors);
	}

	public function action_login()
	{
		if(Auth::instance()->logged_in())
		{
			$this->request->redirect(Route::get('admin')->uri());
		}

		if($this->request->post())
		{
			try
			{
				if(Auth::instance()->login($this->request->post('username'), $this->request->post('password')))
				{
					Message::success('auth.logged_in', array(':username'=>Auth::instance()->get_user()->username));
					$this->request->redirect(Route::get('admin')->uri());
				}
				else
				{
					$validation = Validation::factory($this->request->post())
						->rule('username', 'not_empty')
						->rule('username', 'min_length', array(':value', 1))
						->rule('username', 'max_length', array(':value', 127))
						->rule('password', 'not_empty');

					if($validation->check())
					{
						$validation->error('password','invalid');
					}
					$errors = $validation->errors('auth');
				}
			}
			catch(ORM_Validation_Exception $e)
			{
				$errors = $e->errors('auth');
			}
		}

		$this->template->content = View::factory('user/login')
			->set('values', $this->request->post())
			->bind('errors', $errors);
	}

	public function action_logout()
	{
		Auth::instance()->logout();
		$this->request->redirect(Route::get('default')->uri());
	}

}