<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Admin_Template extends Kohana_Controller_Template {

	public $template = 'admin/template';
	public $auth;
	public $user;
	public $acl;
	protected $_resource_required = array();
	protected $_acl_required = 'all'; // actions requiring acl checking, array() or 'all'

	public function before()
	{
		parent::before();
		
		if( ! isset($this->_resource)) {
			$this->_resource = $this->request->param('resource')
				? $this->request->param('resource')
				: $this->request->controller();
		}
		
		$this->auth = Auth::instance();
		$this->user = Auth::instance()->get_user();
		
		if($this->request != Request::initial())
			$this->auto_render = FALSE;

		if($this->auto_render)
		{
			$params = Route::get('admin/dashboard')->matches($this->request->uri())
				? Breadcrumbs::add('Admin')
				: Breadcrumbs::add('Admin', Route::url('admin/dashboard'));
			
			$this->template->title = 'ERA Database';
			$this->template->styles = array();
			$this->template->scripts_top = array();
			$this->template->scripts_bottom = array();
			$this->template->messages = Message::render();
		}

		if( ! Auth::instance()->logged_in() AND ! in_array($this->request->action(), array('login','register')))
		{
			Auth::instance()->logged_in('login')
				? $this->request->redirect(Route::get('admin')->uri())
				: $this->request->redirect(Route::get('admin/login')->uri());
		}
		
		$this->acl = new ACL;
		$this->acl->add_role('guest');
		$this->acl->add_role('login','guest');
		$this->acl->add_role('admin','login');
		$this->acl->add_resource('member');
		$this->acl->add_resource('equine');
		
		$this->acl->allow('*','user','login');
		Kohana::$environment == Kohana::DEVELOPMENT AND $this->acl->allow('*','user','register');
		$this->acl->allow('login','user','*');
		$this->acl->allow('login','main','index');
		$this->acl->allow('login','approval','list');
		$this->acl->allow('login','approval','view', new Assert_UserOwnsResource);
		$this->acl->allow('login','member','list');
		$this->acl->allow('login','member',array('edit','delete'), new Assert_UserMember);
		$this->acl->allow('login',array('event_result','reclaim','equine'),array('add','list'));
		$this->acl->allow('login',array('equine','event_result','reclaim'),array('edit','delete'), new Assert_MemberOwnsResource);
		$this->acl->allow('admin','*','*');
		
		$this->_set_acl_config();
		
		$resource = in_array($this->request->action(), $this->_resource_required)
			? $this->_load_resource()
			: $this->_resource;
		
		if ($this->_acl_required === 'all' OR in_array($this->request->action(), $this->_acl_required))
		{
			if( ! $this->acl->is_allowed($this->auth->get_user(), $resource, $this->request->action()))
			{
				if( ! $resource instanceof Acl_Resource_Interface AND $this->_resource_required AND Kohana::$environment == Kohana::DEVELOPMENT)
					throw new Kohana_Exception('The requested resource ":model" does not implement Acl_Resource_Interface.', array(':model' => $this->_resource));
				Message::error('acl.deny.'.$this->request->action(), array(':resource'=>$this->_resource));
				$this->request->redirect($this->request->referrer());
			}
		}
	}
	
	/**
	 * Map ACL settings from config to class properties
	 */
	protected function _set_acl_config()
	{
		$config = Kohana::$config->load('acl.'.$this->_resource);

		// Apply config to associated class properties
		if( ! empty($config))
		{
			// Convert config to class properties
			foreach($config as $property => $value)
			{
				if(property_exists($this, '_'.$property))
				{
					$this->{'_'.$property} = $value;
				}
				else
				{
					throw new Exception('That ACL property does not exist.');
				}
			}
		}
		unset($config);
	}
	
	protected function _load_resource()
	{
		$id = $this->request->param('id', 0);

		$resource = ORM::factory($this->_resource, array('id'=>$id));

		if ( ! $resource->loaded())
		{
			throw new Kohana_Exception('That :resource does not exist.', array(':resource'=>$this->_resource), 404);
		}
		return $resource;
	}

	public function after()
	{
		if($this->auto_render)
		{
			$this->template->styles = array_merge($this->template->styles, array(
				Route::get('media')->uri(array('file'=>'css/reset.css')) => 'screen',
				Route::get('media')->uri(array('file'=>'css/print.css')) => 'print',
				Route::get('media')->uri(array('file'=>'vendor/jquery-ui/smoothness/jquery-ui-1.8.12.custom.css')) => 'screen',
				Route::get('media')->uri(array('file'=>'css/admin-era.css')) => 'screen',
				//Route::get('media')->uri(array('file'=>'vendor/bootstrap/css/bootstrap.css')) => 'screen'
			));

			$this->template->scripts_top = array_merge($this->template->scripts_top, array(
				'https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js',
				'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js',
				Route::get('media')->uri(array('file'=>'js/admin-era.js')),
			));

			if( ! isset($this->template->content))
			{
				$this->template->content = $this->response->body();
			}
		}

		parent::after();
	}
	
}