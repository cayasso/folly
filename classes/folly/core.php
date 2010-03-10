<?php defined('SYSPATH') or die('No direct script access.');

abstract class Folly_Core
{
	
	/**
	 * @var  Jelly_Model	The Jelly model that Folly object uses
	 */
	private $_model;
	
	/**
	 * @var  array			Collection of Jelly fields used in this form
	 */
	private $_fields = array();
	
	/**
	 * @var  array			Array of form's attributes (action, method etc.)
	 */
	private $_attributes = array(
		'action' => NULL,
		);
	
	/**
	 * @var  string			View file used to render this form
	 */
	public $form_view = 'folly/form';
	
	/**
	 * Factory for instantiating a Folly object.
	 * 
	 * $model can be a Jelly model or a model's name.
	 * $key is either the primary key or an array of search
	 * conditions, used when fetching a record via Folly.
	 * If $fields is passed, form contains only fields found
	 * in that array, in that order.
	 *
	 * @param   mixed  $model
	 * @param   mixed  $attributes
	 * @param   array  $fields
	 * @return  Folly
	 */
	public static function factory($model, $key = NULL, array $fields = NULL)
	{
		return new Folly($model, $key, $fields);
	}
	
	/**
	 * Folly constructor method.
	 *
	 * @param   mixed  $model
	 * @param   mixed  $key
	 * @param   array  $fields
	 */
	public function __construct(& $model, $key = NULL, array $fields = NULL)
	{
		if($model instanceof Jelly_Model)
		{
			$this->_model = $model;
		}
		else
		{
			if($key === NULL)
			{
				$this->_model = Jelly::factory($model);
			}
			else if(is_array($key))
			{
				$query = Jelly::select($model);
				foreach($key as $key => $value)
				{
					$query->where($key, '=', $value);
				}
				$this->_model = $query->limit(1)->execute();
			}
			else
			{
				$this->_model = Jelly::select($model, $key);
			}
		}
				
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
		return $this->get($name);
	}
	
	/**
	 * Returns a field from the _fields array
	 *
	 * @param   string   		$name
	 * @return  Folly_Element
	 */
	public function get($name)
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
	 * Setter / getter for form's attributes.
	 *
	 * @param   string   $key
	 * @param   string   $value
	 * @return  $this
	 */
	public function attrs($key = NULL, $value = NULL)
	{
		if($key === NULL AND $value === NULL)
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
	 * @return  View
	 */
	public function render($display = TRUE)
	{
		$result = View::factory($this->form_view)
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
	 * @return  Jelly_Model
	 */
	public function model()
	{
		return $this->_model;
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
		try
		{
			$this->_model->save();
		}
		catch(Validate_Exception $e)
		{
			foreach($e->array->errors('validate') as $field => $error)
			{				
				$this->get($field)->error($error);				
			}
		}
		return $this;
	}
}
