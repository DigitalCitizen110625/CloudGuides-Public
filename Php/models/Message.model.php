<?php declare(strict_types=1);
require_once('../services/EmailValidator.service.php');


/*
* 	DESCRIPTION	: Model used to define the properties and functionality of contact messages
*/
class Message extends DatabaseModel
{

    
    public string  $name;      //Name of the person sending the message
    public string  $address;   //Email address of the sender (i.e. fromAddress)
    public string  $content;   //Textual content of the message
    public $sendSuccess;       //After validation, did the message transmit succssfully?

    private const  maxNameLength = 100;
    private const  maxAddressLength = 100;
    private const  maxContentLength = 2000;

    public function __construct(string $name = "", string $address = "", string $content = "")
    {
        $this->name = $name;
        $this->address = $address;
        $this->content = $content;
        $this->sendSuccess = null;
    }
        
    /*
    * 	DESCRIPTION	: Checks if the email address is well formed
    *	PARAMETERS	: string email : Email address to check
    *   RETURNS     : bool : True if no errors detected, false otherwise
    */
    private function validateEmail(string $email)
    {
        //Ensure the email pattern is valid according to the regex
        $emailvalidator = new EmailAddressValidator($email);

        //isValid will return 1 on success, FALSE otherwise;
        if(!$emailvalidator->isValid())
        {
            return false;
        }

        return true;

        //Alternative native php email validation
        //return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /*
    * 	DESCRIPTION	: Ensures each of the models properties are valid according to the entities 
    *                 properties in the db
    *	PARAMETERS	: NA
    *	RETURNS		: NA
    */
    public function isValid() : bool
    {
        if(!$this->validateLength($this->name, self::maxNameLength))
        {
            return false;
        }
        else if(!$this->validateLength($this->address, self::maxAddressLength))
        {
            return false;
        }
        else if(!$this->validateEmail($this->address))
        {
            return false;
        }
        else if(!$this->validateLength($this->content, self::maxContentLength))
        {
            return false;
        }

        //All checks passed
        return true;
    }
}
?>