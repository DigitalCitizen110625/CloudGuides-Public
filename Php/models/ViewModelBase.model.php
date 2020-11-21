<?php 

/*
* 	DESCRIPTION	: Base class from which all view models derive. Addes properties, and methods of managing model errors.
*/
abstract class ViewModelBase
{

    public array $errors;           //Collection of error strings printable to to the UI

    public function __construct()
    {
        $this->errors = array();
    }

    /*
    * 	DESCRIPTION	: Adds a message to the error property
    *	PARAMETERS	: string $message : Errior message to display on the UI
    *   RETURNS     : NA
    */
    public function addError(string $message)
    {
        if(empty(trim($message)))
        {
            return;
        }
        array_push($this->errors, $message);
    }
        
    /*
    * 	DESCRIPTION	: Removes all elements from the error array
    *	PARAMETERS	: NA
    *   RETURNS     : NA
    */
    public function clearErrors()
    {
        $this->errors = array();
    }

}