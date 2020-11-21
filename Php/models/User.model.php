<?php declare(strict_types=1);
require_once('../services/EmailValidator.service.php');

/*
* 	DESCRIPTION	:  Model used to define the properties and functionality of a user in the database
*/
class User extends DatabaseModel
{
    public string $username;                //Name displayed on the account
    public string $email;                   //Email address
    public string $password;                //Pre-hashed email
    public int    $admin;                   //int 1 for admin, 0 for non-admin

    private const nullableId = true;        //Db column keys must not be null
    private const maxEmailLength = 255;     //Hard limit imposed by db column
    private const maxUsernameLength = 255;  
    private const maxPasswordLength = 255; 

    public function __construct(int $id = -1, string $username = "", string $email = "", string $password = "", int $admin = -1)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->admin = $admin;
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
    * 	DESCRIPTION	: Checks that each property is valid. All proerties should pass the checks before insertion into the db
    *	PARAMETERS	: NA
    *   RETURNS     : bool : True if all checks passed, false otherwise
    */
    public function isValid()
    {
        if(!$this->validateId($this->admin, self::nullableId)) 
        {
            return false;
        }
        if(!$this->validateLength($this->username, self::maxUsernameLength))
        {
            return false;
        }
        if(!$this->validateLength($this->email, self::maxEmailLength))
        {
            return false;
        }
        if(!$this->validateEmail($this->email))
        {
            return false;
        }
        if(!$this->validateLength($this->password, self::maxPasswordLength))
        {
            return false;
        }

        //All checks passed
        return true;
    }
}