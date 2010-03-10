<?php defined('SYSPATH') or die('No direct script access.');

abstract class Folly_Element_Core extends Folly_Core
{	
	/**
	 * @var  Jelly_Field	The Jelly field that this element object uses
	 */
	private $_field;
	
	/**
	 * @var  string			Name of this field
	 */
	public $name;
	
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
	 * Renders the field using a view.
	 *
	 * @param   bool    $display
	 * @return  html form
	 */
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
	
	/**
	 * Allows setting field values using an assignment
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
	 * Setter for field's attributes
	 *
	 * @param   string   $name
	 * @param   string   $value
	 * @return  $this
	 */
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
}
