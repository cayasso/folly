<?php defined('SYSPATH') or die('No direct script access.');

abstract class Folly_Core
{
	
	private $_model;
	private $_fields;
	private $_attributes = array(
		'action' => NULL,
		);
	
	/**
	 * Factory for instantiating a Folly object.
	 * 
	 * $model can be a Jelly model or a model's name.
	 * $attributes are the form's attributes. If it's
	 * a string, it's taken as the form's action attribute.
	 * If $fields is passed, form contains only the fields
	 * in that array, in that order.
	 *
	 * @param   mixed  $model
	 * @param   mixed  $attributes
	 * @param   array  $fields
	 * @return  Folly
	 */
	public static function factory($model, $attributes = NULL, array $fields = NULL)
	{
		return new Folly($model, $attributes, $fields);
	}
	
	/**
	 * Folly constructor method.
	 *
	 * @param   mixed  $model
	 * @param   mixed  $attributes
	 * @param   array  $fields
	 */
	public function __construct(& $model, $attributes, array $fields = NULL)
	{
		$this->_model = $model instanceof Jelly_Model ? $model : Jelly::factory($model);
		
		foreach($this->_model->meta()->fields() as $field)
		{
			if(!$field instanceof Field_Primary)
			{
				if(is_array($fields))
				{
					if(in_array($field->name, $fields))
					{
						$key = array_search($field->name, $fields);
						$this->_fields[$key] = new Folly_Element($field, $this->_model);
					}
				}
				else
				{
					$this->_fields[] = new Folly_Element($field, $this->_model);
				}
			}
		}
		
		$this->attrs('name', $this->_model->meta()->model());
		
		if(is_array($attributes))
		{
			$this->_attributes = $attributes;
		}
		else if(is_string($attributes))
		{
			$this->_attributes['action'] = $attributes;
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
		$this->_model->__set($name, $value);
		return $this;
	}
	
	/**
	 * Returns a field from the _fields array
	 *
	 * @param   string   		$name
	 * @return  Folly_Element
	 */
	public function __get($name)
	{
		foreach($this->_fields as $field)
		{
			if($field->name === $name) $found = $field;
		}
		if(empty($found))
		{
			throw new Kohana_Exception('No field by the name of `'.$name.'` was found.');
		}
		else
		{
			return $found;
		}		
	}
	
	/**
	 * Renders the form
	 *
	 * @return  html form
	 */
	
	public function __toString()
	{
		return $this->render();
	}
	
	/**
	 * Setter / getter for form's attributes
	 *
	 * @param   string   $key
	 * @param   string   $value
	 * @return  $this
	 */
	public function attrs($key = NULL, $value = NULL)
	{
		if($key === NULL)
		{
			return $this->_attributes;
		}
		else
		{
			if($value !== NULL)
			{
				$this->_attributes[$key] = $value;
			}
			else
			{
				return $this->_attributes[$key];
			}
		}
		return $this;
	}
	
	/**
	 * Renders the form using a view.
	 *
	 * @param   bool    $display
	 * @return  html form
	 */
	public function render($display = TRUE)
	{
		$result = View::factory('folly/form')
			->set('action', $this->attrs('action'))
			->set('attributes', $this->attrs())
			->set('fields', $this->_fields);
			
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
	 * Returns the associated Jelly model
	 *
	 * @return  Jelly_Model	$model
	 */
	public function model()
	{
		return $this->_model;
	}
	
	/**
	 * Loads a Jelly model using primary key $key using Jelly's set() method.
	 *
	 * @param   mixed    $key
	 * @return  $this
	 */
	public function load($key)
	{
		$this->_model->set(Jelly::select($this->attrs('name'), $key)->as_array());
		
		return $this;
	}
	
	/**
	 * Loads an array of values into the associated Jelly object using it's set() method.
	 *
	 * This should only be used for setting from database results 
	 * since the model declares itself as saved and loaded after.
	 *
	 * @param   array    $values
	 * @return  $this
	 */
	public function values(array $values)
	{
		$this->_model->set($values);
		return $this;
	}
	

	/**
	 * Saves the associated Jelly model using it's save() method.
	 *
	 * @return  $this
	 */
	public function save()
	{
		$this->_model->save();
		return $this;
	}
}
