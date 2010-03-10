<?php defined('SYSPATH') or die('No direct script access.');

abstract class Folly_Element_Core
{	
	/**
	 * @var  Jelly_Model	The Jelly model that Folly_Element object is connected to
	 */
	protected $_model;
	
	/**
	 * @var  Jelly_Field	The Jelly field that this element object uses
	 */
	private $_field;
	
	/**
	 * @var  array			Error messages from validation for this field
	 */
	private $_errors = array();	
	
	/**
	 * @var  string			Name of this field
	 */
	public $name;
	
	/**
	 * @var  string			View file used to render this element
	 */
	public $field_view = 'folly/field';
	
	/**
	 * @var  string			View file used to render element's errors
	 */
	public $error_view = 'folly/error';
	
	/**
	 * Folly_Element constructor method.
	 *
	 * @param   mixed  $model
	 * @param   mixed  $attributes
	 * @param   array  $fields
	 */
	public function __construct(Jelly_Field & $field = NULL, Jelly_Model & $model = NULL)
	{
		$this->_field = $field;
		$this->_model = $model;
		$this->name = $field->name;
	}
		
	/**
	 * Renders the field
	 *
	 * @return  html field
	 */
	
	public function __toString()
	{
		return $this->render();
	}
	
	/**
	 * Renders the field using a view.
	 *
	 * @param   bool    $display
	 * @return  View
	 */
	public function render($display = TRUE)
	{
		$result = View::factory($this->field_view)
			->set('name', $this->_field->name)
			->set('label', $this->_field->label)
			->set('field', $this->_model->input($this->_field->name))
			->set('errors', count($this->_errors) ? $this->errors(): NULL);
		
		if($display === TRUE)
		{
			return $result->render();
		}
		else
		{
			return $result;
		}
	}
	
	/**
	 * Allows setting field attributes using an assignment
	 *
	 * @param   string   $name
	 * @param   string   $value
	 * @return  $this
	 */
	public function __set($name, $value)
	{
		$this->attrs($name, $value);
	}
	
	/**
	 * Setter for field's attributes
	 *
	 * @param   string   $key
	 * @param   string   $value
	 * @return  $this
	 */
	public function attrs($key = NULL, $value = NULL)
	{		
		if(property_exists($this->_field, $key))
		{
			$this->_field->$key = $value;
		}
		else
		{
			$this->_field->attributes[$key] = $value;
		}
		return $this;
	}
	
	/**
	 * Method for adding errors to the field
	 *
	 * @param   string   $message
	 * @return  $this
	 */
	public function error($message)
	{		
		$this->_errors[] = $message;
		return $this;
	}
	
	/**
	 * Renders the field's errors using a view
	 *
	 * @return  View
	 */
	public function errors()
	{		
		return View::factory($this->error_view)
			->set('errors', $this->_errors)
			->set('name', $this->name);
	}
}
