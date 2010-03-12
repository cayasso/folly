<?php defined('SYSPATH') or die('No direct script access.');

abstract class Folly_Core
{
	
	/**
	 * @var  array			An array of Jelly model names that are used
	 */
	protected $models = array();
	
	/**
	 * @var  array			Collection of Folly_Elements used in this form
	 */
	protected $elements = array();
	
	/**
	 * @var  string			Form's action attribute
	 */
	public $action;
	
	/**
	 * @var  array			Array of form's attributes (action, method etc.)
	 */
	protected $attributes = array();
	
	/**
	 * @var  string			View file used to render this form
	 */
	public $form_view = 'folly/form';
	
	/**
	 * Factory for instantiating a Folly object.
	 *
	 * @param   mixed  $name
	 * @param   array  $attributes
	 * @return  Folly
	 */
	public static function factory($name, array $attributes = NULL)
	{
		return new Folly($name, $attributes);
	}
	
	/**
	 * Folly constructor method.
	 *
	 * @param   mixed  $name
	 * @param   array  $attributes
	 */
	public function __construct($name, array $attributes = NULL)
	{		
		$this->attrs('name', $name);
		if(is_array($attributes))
		{
			$this->attributes = array_merge($this->attributes, $attributes);
		}		
	}	
	
	
	/**
	 * Allows setting element's value or form's attributes using assignment
	 *
	 * @param   string   $name
	 * @param   string   $value
	 * @return  $this
	*/	
	public function __set($name, $value)
	{
		$this->set($name, $value);
		return $this;
	}	
	
	/**
	 * Allows setting element's value or form's attributes
	 * 
	 * @param   string   $name
	 * @param   string   $value
	 * @return  $this
	 */
	public function set($name, $value)
	{
		if($element = $this->element($name))
		{
			$element->set('value', $value);
		}
		else
		{
			$this->attrs($name, $value);
		}
		return $this;
	}
	
	/**
	 * Returns an element or false if not found
	 *
	 * @param   string   		$name
	 * @return  mixed
	 */
	public function __get($name)
	{
		return $this->element($name);
	}
		
	/**
	 * Setter / getter for the _elements array
	 * 
	 * @param   string   		$name
	 * @param   mixed   		$value
	 * @return  mixed
	 */
	public function element($name, $value = NULL)
	{
		if($value === NULL)
		{
			// Find and return an element by it's name			
			foreach($this->elements as $element)
			{
				if($element->name() === $name) return $element;
			}
			return FALSE;
		}
		else
		{
			// Set a element's value if it's found
			if($element = $this->element($name))
			{
				$element->set($name, $value);				
			}			
		}
		return $this;		
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
		if($key === NULL)
		{
			// Just return the attributes array
			return $this->attributes;
		}
		else
		{
			if($value === NULL)
			{
				// Return a certain attribute's value
				return $this->attributes[$key];
			}
			else
			{			
				if(is_array($value))
				{
					// Ensure Form::open() works
					$this->attributes[$key] = implode(' ', $value);
				}
				else
				{
					// Set an attribute's value
					$this->attributes[$key] = $value;
				}
			}
		}
		return $this;
	}
	
	/**
	 * Setter / getter for form's action
	 *
	 * @param   string   $action
	 * @return  $this
	 */
	public function action($action = NULL)
	{
		if($action === NULL)
		{
			// Return form's action
			return $this->action;
		}
		else
		{
			// Set form's action
			$this->action = $action;
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
			->set('action', $this->action)
			->set('attributes', $this->attrs())
			->set('elements', $this->elements);
			
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
	 * Getter / setter of associated Jelly model(s)
	 * 
	 * Whenever a new model is added (by passing either a
	 * Jelly_Model object, or a model name that's not already
	 * added, or an array(model_name, primary_key))
	 * the model's fields are added into the _elements array.
	 * 
	 * If $fields array is passed, only the fields found in
	 * that array are added, in that order.
	 *
	 * @param   mixed		$model
	 * @param   array		$fields
	 * @return  $this
	 */
	public function model($model, array $fields = NULL)
	{
		if(in_array($model, $this->models))
		{			
			// Find a model used by at least one of the elements
			
			foreach($this->elements as $element)
			{
				if($element instanceof Folly_Element_Jelly)
				{
					if($element->model(TRUE) === $model)
					{
						return $element->model();
					}
				}
			}
			return FALSE;
		}
		else
		{
			if($model instanceof Jelly_Model)
			{
				$name = Jelly::model_name($model);
			}
			else if(is_array($model))
			{
				// Instantiate a Jelly model using a primary key
				list($name, $key) = $model;
				$model = Jelly::select($name, $key);
			}
			else
			{
				// Instantiate an 'empty' Jelly model
				$name = $model;
				$model = Jelly::factory($model);
			}
			
			// Add model's name to array of associated models			
			$this->models[] = $name;
			
			// Loop through model's fields and add them to the form as necessary
			foreach($model->meta()->fields() as $field)
			{
				// Skip fields that have their render property set to false
				if(!isset($field->hide))
				{
					if(is_array($fields))
					{
						// If only certain fields are wanted to be added, filter them here
						if(in_array($field->name, $fields))
						{
							$key = array_search($field->name, $fields) + count($this->elements);
							$this->elements[$key] = new Folly_Element_Jelly($field->name, $model);
						}
					}
					else
					{
						$this->elements[] = new Folly_Element_Jelly($field->name, $model);
					}
				}
			}
		}
		return $this;
	}
		
	/**
	 * Loads an array of values into the associated Jelly object using it's set() method.
	 *
	 * @param   array    $values
	 * @return  $this
	 */
	public function values(array $values)
	{
		foreach($this->models as $model)
		{
			$this->model($model)->set($values);
		}		
		return $this;
	}
	
	/**
	 * Saves the associated Jelly model using it's save() method.
	 *
	 * @return  $this
	 */
	public function save()
	{
		foreach($this->models as $model)
		{
			try
			{
				$this->model($model)->save();
			}
			catch(Validate_Exception $e)
			{
				foreach($e->array->errors('validate') as $element => $error)
				{				
					$this->element($element)->error($error);				
				}
			}
		}
		return $this;
	}
}
