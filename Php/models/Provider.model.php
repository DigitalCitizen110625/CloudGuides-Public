<?php declare(strict_types=1);

/*
* 	DESCRIPTION	: Model used to define the properties of what constitutes a provider in the database
*/
class Provider extends DatabaseModel
{
    public string $name;                //Name of the provider (e.g. Azure, Amazon Web Services etc...)

    private const nullableId = false;   //Db column keys  must not be null
    private const maxNameLength = 50;   //Hard limit imposed by db column

    public function __construct(int $id = -1, string $name = "")
    {
        parent::__construct();
        $this->id = $id;
        $this->name = $name;
    }

    /*
    * 	DESCRIPTION	: Checks that each property is valid. All properties should pass the checks before insertion into the db
    *	PARAMETERS	: NA
    *   RETURNS     : bool : True if all checks passed, false otherwise
    */
    public function isValid() : bool
    {
        //Model validation fails if any property is invalid
        if(!$this->validateId($this->id, $this->nullableId)) 
        {
            return false;
        }
        if(!$this->validateLength($this->name, $this->maxNameLength))
        {
            return false;
        }

        return true;
    }

    
}
?>