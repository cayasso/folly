<?php defined('SYSPATH') or die('No direct script access.');

class Folly_Element_Jelly extends Folly_Element
{	
	/**
	 * @var  Jelly_Model	The Jelly model that this field uses
	 */
	private $model;
	
	/**
	 * @var  Jelly_Field	The Jelly field that this field uses
	 */
	private $field;
				
	/**
	 * @var  string			View file used to render this element's 'field' part
	 */
	public $field_view = 'jelly/field';
	
	/**
	 * Folly_Element_Jelly constructor method.
	 *
	 * @param   string		$name
	 * @param   Jelly_Model	$model
	 */
	public function __construct($name, $model = NULL)
	{
		parent::__construct($name);
		
		$this->model = & $model;
		$this->field = $this->model->meta()->fields($name);
		$this->label = & $this->field->label;
		
		if(isset($this->field->attributes))
		{
			$this->attributes = & $this->field->attributes;
		}
		
		// The value of a Jelly Field element resides in the model
		unset($this->value);
	}
	
	/**
	 * Allows setting field properties / attributes
	 *
	 * @param   string   $name
	 * @param   string   $value
	 * @return  $this
	 */
	protected function set($name, $value)
	{
		if($name === 'value')
		{
			$this->model->set($this->name, $value);
		}
		else
		{
			parent::set($name, $value);
		}
		return $this;
	}
	
	/**
	 * Gets the Jelly field's value
	 *
	 * @param   string   $name
	 * @return  $this
	 */
	public function get($name)
	{
		if($name === 'value')
		{
			return $this->model->get($this->name);
		}
		else
		{
			return parent::get($name);
		}
	}
	
	/**
	 * Gets the Jelly_Field object for this element
	 *
	 * @return  View
	 */
	protected function field()
	{
		return $this->model->input($this->name, $this->field_view);
	}
	
	/**
	 * Returns associated Jelly model or model's name
	 *
	 * @param   bool   $name_only
	 * @return  Jelly_Model
	 */
	public function model($name_only = FALSE)
	{		
		return $name_only === TRUE ? Jelly::model_name($this->model) : $this->model;
	}
}
