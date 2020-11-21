<?php declare(strict_types=1);

/*
* 	DESCRIPTION	: Defines the base behaviour, and commonly used methods by all models representing tables from the database
*/
abstract class DatabaseModel
{
    public int      $id;   //Id used to identify the record in the database
  
    public function __construct(int $id = -1)
    {
        $this->id = $id;
    }

    /*
    * 	DESCRIPTION	: Creates a new instance of the class, and sets the propert values according to the array. 
    *                 Can set the value of some, or all properties.
    *	PARAMETERS	: array $objectProperties : Assoc string array containing the names, and values for each property of the object
    *   RETURNS     : Post : Ref to the newly instantiated post object
    */
    public static function dynamicInit(array $objectProperties)
    {

        $instance = new static();

        /*
        *   Iterate over the properties of the class, and where the name of the property matches the element name in the objectProperty array, 
        *   copy the values from the array, into the new object
        *   Ex: $instance = new User();
        *       $property = "username"
        *       So look for the element with the "username" key in objectProperties[], and get its value
        *       Then paste the value into $instance->username
        */
        foreach ($instance as $property => $value)
        {
            //Ensure the name of the property exists for the calling object
            if(!array_key_exists($property, $objectProperties))
            {
                continue;
            }

            /* 
            *   NOTE: $instance->$property means set the value of the classes property, with the name specified in $property
            *   Ex:   $instance = new User();
            *         $property = "username"
            *         $instance->$property will be the same as calling $instance->username
            */   
            $type = gettype($instance->$property);
            if($type == 'integer')
            {
                $instance->$property = (int)$objectProperties[$property];
            }
            else if($type == 'string')
            {
                $instance->$property = trim((string)$objectProperties[$property]);
            }
            else
            {
                $instance->$property = $objectProperties[$property];
            } 
        }
        return $instance;
    }


    /*
    * 	DESCRIPTION	: All ids must be non-null, and above 0
    *	PARAMETERS	: int $newId : New id to validate
    *   RETURNS     : bool : True if no errors detected, false otherwise
    */
    protected function validateId(int $newId, bool $nullableInt) : bool
    {

        if($nullableInt === false)
        {
            //Null ints are NOT considered valid
            if(is_null($newId) )
            {
                return false;
            }
        }
        else if($newId < 0)
        {
            return false;
        }

        return true;
    }

    
    /*
    * 	DESCRIPTION	: Trims the string, and shecks if it's less than or equal to the specified max length
    *	PARAMETERS	: string text : The string to validate
    *                 int maxLength : Max allowable length of the string 
    *   RETURNS     : bool : True if no errors detected, false otherwise
    */
    protected function validateLength(string $text, int $maxLength) : bool 
    {
        if(is_null($text) || empty($text))
        {
            //String must be initialized, and contain at least one non-whitespace char
            return false;
        }

        /*
        *   Remove the following leading, and trailing chars from the string:
        *   " " (ASCII 32 (0x20)), an ordinary space.
        *   "\t" (ASCII 9 (0x09)), a tab.
        *   "\n" (ASCII 10 (0x0A)), a new line (line feed).
        *   "\r" (ASCII 13 (0x0D)), a carriage return.
        *   "\0" (ASCII 0 (0x00)), the NUL-byte.
        *   "\x0B" (ASCII 11 (0x0B)), a vertical tab.
        */
        trim($text," \t\n\r\0\x0B");


        if(strlen($text) === 0)
        {
            //String is empty
            return false;
        }
        else if(strlen($text) > $maxLength)
        {
            //String is over the allowable max length
            return false;
        }

        return true;
    }
}
?>