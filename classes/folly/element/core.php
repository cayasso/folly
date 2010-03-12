<?php defined('SYSPATH') or die('No direct script access.');

abstract class Folly_Element_Core
{		
	/**
	 * @var  array			Error messages from validation for this field
	 */
	protected $errors = array();	
	
	/**
	 * @var  string			Name of this field
	 */
	protected $name;
	
	/**
	 * @var  string			Value of this field
	 */
	public $value;
	
	/**
	 * @var  string			Label of this field
	 */
	public $label;
	
	/**
	 * @var  array			An array of attributes
	 */
	protected $attributes = array();
	
	/**
	 * @var  string			View file used to render this element
	 */
	public $element_view = 'folly/field';
	
	/**
	 * @var  string			View file used to render this element's 'field' part
	 */
	public $field_view = 'folly/field';
	
	/**
	 * @var  string			View file used to render element's errors
	 */
	public $error_view = 'folly/error';
	
	/**
	 * Folly_Element constructor method.
	 *
	 * @param   string $name
	 * @param   array  $attributes
	 */
	public function __construct($name, $attributes = NULL)
	{
		$this->name = $name;
		
		if(is_array($attributes))
		{
			$this->attributes = array_merge($this->attributes, $attributes);
		}
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
	 * Gets the element's name
	 *
	 * @return  string
	 */
	public function name()
	{
		return $this->name;
	}
	
	/**
	 * Gets the field view object
	 *
	 * @return  View
	 */
	protected function field()
	{
		return View::factory($this->field_view, get_object_vars($this));
	}
	
	/**
	 * Renders the field using a view.
	 *
	 * @param   bool    $display
	 * @return  View
	 */
	public function render($display = TRUE)
	{
		$result = View::factory($this->element_view)
			->set('name', $this->name)
			->set('label', $this->label)
			->set('field', $this->field())
			->set('attributes', $this->attributes)
			->set('errors', count($this->errors) ? $this->errors(): NULL);
		
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
	 * Allows setting field properties / attributes using an assignment
	 *
	 * @param   string   $name
	 * @param   string   $value
	 * @return  $this
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
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
			$this->attrs($name, $value);
		return $this;
	}
	
	/**
	 * Allows getting field attributes
	 *
	 * @param   string   $name
	 * @return  $this
	 */
	public function __get($name)
	{
		return $this->get($name);
	}
	
	/**
	 * Allows getting field properties / attributes
	 *
	 * @param   string   $name
	 * @return  $this
	 */
	public function get($name)
	{
		if(property_exists($this, $name))
		{
			return $this->$name;
		}
		else
		{
			return $this->attrs($name);
		}		
	}
	
	/**
	 * Setter / getter for field's attributes
	 *
	 * @param   string   $key
	 * @param   string   $value
	 * @return  $this
	 */
	public function attrs($key = NULL, $value = NULL)
	{		
		if($key === NULL)
		{
			return $this->attributes;
		}
		else
		{
			if($value === NULL)
			{
				return $this->attributes[$key];
			}
			else
			{			
				if(is_array($value))
				{
					$this->attributes[$key] = implode(' ', $value);
				}
				else
				{
					$this->attributes[$key] = $value;
				}
			}
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
		$this->errors[] = $message;
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
			->set('errors', $this->errors)
			->set('name', $this->name);
	}
}
