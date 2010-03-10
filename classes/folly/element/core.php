<?php defined('SYSPATH') or die('No direct script access.');

abstract class Folly_Element_Core extends Folly
{	
	private $_model;
	private $_field;
	public $name;
	
	public function __construct(Jelly_Field & $field = NULL, Jelly_Model & $model = NULL)
	{
		$this->_field = $field;
		$this->_model = $model;
		$this->name = $field->name;
	}
	
	public function render($display = TRUE)
	{
		$result = View::factory('folly/field')
			->set('name', $this->_field->name)
			->set('label', $this->_field->label)
			->set('field', $this->_model->input($this->_field->name));
		
		if($display === TRUE)
		{
			return $result->render();
		}
		else
		{
			return $result;
		}
	}
	
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}
	
	public function set($name, $value)
	{		
		if($name === 'label')
		{
			$this->_field->label = $value;
		}
		else
		{
			$this->_field->attributes[$name] = $value;
		}
		return $this;
	}
	
	public function __toString()
	{
		return $this->render();
	}
}
