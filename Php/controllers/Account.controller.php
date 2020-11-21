<?php
require_once "../services/AccountRepository.service.php";
require_once "../dataLayer/MySqlDatabase.data.php";
require_once "../controllers/ControllerBase.controller.php";

/*
* 	DESCRIPTION	: Responsible for processing requests involving account access (i.e. registration, and logins)
*/
class AccountController extends ControllerBase
{

    private AccountRepository $repository;

    public function __construct()
    {
        $this->repository = new AccountRepository(new MySqlDatabase());
    }


    /*
    * 	DESCRIPTION	: Validates the user input, and passes the packaged user model to the db for registration
    *	PARAMETERS	: UserView $viewModel : Model used to store the user input, and ensure it's valid before saved to the db
    *                 array $userInput : Collection of user input strings from the UI
    *   RETURNS     : bool : True is all checks passed, and the user account was registered, false otherwise
    */
    public function register(UserView $viewModel, array $userInput) : bool
    {
        $viewModel->clearErrors();

        //Save the users input. By default, all new accounts will not be registered as admins (i.e. admin = false)
        $viewModel->user->username = trim($userInput['username']);
        $viewModel->user->email = trim($userInput['email']);
        $viewModel->user->password = trim($userInput['password']);
        $viewModel->user->admin = false;


        //Ensure each input field in the form is filled in
        if(empty($viewModel->user->username))
        {
            $viewModel->addError("Username is required");
        }

        if(empty($viewModel->user->email))
        {
            $viewModel->addError("Email is required");
        }

        if(empty($viewModel->user->password))
        {
            $viewModel->addError("Password is required");
        }

        if($viewModel->user->password !== $userInput['passwordConfirmation'])
        {
            $viewModel->addError("Passwords do not match");
        }

        if(empty($userInput['termsCheck']))
        {
            $viewModel->addError("Terms of service must be accepted");
        }

        //On error, reprint the entered values, and display all error messages
        if(count($viewModel->errors) !== 0) { return false; }

        try 
        {
            //The email must be unique
            //We don't care which columns are returned from the query. We just need to confirm if a match was made, 
            //  as this indicates an account was found with a matching email
            $existingUser = $this->repository->get(USERS_TABLE, 1, [],  ['email' => $viewModel->user->email]);
            if(!empty($existingUser))
            {
                $viewModel->addError("An account with that email already exists");
                return false; 
            }
        }
        catch (Exception $e)
        {
            $viewModel->addError("Unknown error during account lookup, please try again later...");
            return false;
        }


        //Hash the password so it doesn't save as plain text
        $viewModel->user->password = password_hash($userInput['password'], PASSWORD_DEFAULT);


        //Ensure all fields are valid before submission into the db
        if(!$viewModel->user->isValid()) 
        { 
            $viewModel->addError("Please ensure the email address is well formed, and all entries are below the max length");
            return false;
        }

        try
        {
            //Create the account in the database
            $viewModel->user->id = $this->repository->create(USERS_TABLE, (array)$viewModel->user);

            //Login the user
            $this->getSession();
            $_SESSION['id'] = $viewModel->user->id;
            $_SESSION['username'] = $viewModel->user->username;
            $_SESSION['admin'] = $viewModel->user->admin;
        }
        catch (Exception $e)
        {
            $viewModel->addError("Unknown error during account creation, please try again later...");
            return false;
        }

        return true;
    }

        
    /*
    * 	DESCRIPTION	: Validate the users input, and log them into the requested account
    *	PARAMETERS	: $viewModel : Model used to validate, and store the return input
    *                 array $userInput: $_POST form data
    *   RETURNS     : bool: TRUE if the login process was a success, false otherwise
    */
    public function login($viewModel, array $userInput) : bool 
    {
        //Clear the previous errors
        $viewModel->clearErrors();

        //Save the users input
        $viewModel->user->email =  trim($userInput['email']);
        $viewModel->user->password =  trim($userInput['password']);

        //Ensure each input field is filled in
        if(empty(trim($viewModel->user->email)))
        {
            $viewModel->addError("Email is required");
        }

        if(empty(trim($viewModel->user->password)))
        {
            $viewModel->addError("Password is required");
        }

        //Don't query the database while their login credentials are invalid
        if(count($viewModel->errors) !== 0)
        {
            return false;
        }

        //Emails must be unique => find the account with a matching email
        $existingUser = null; 
        try
        {
            //We want the query to return all the account feilds, as these will be used later to check the password, and as session variables
            $existingUser = $this->repository->get(USERS_TABLE, 1,  [], ['email' => $viewModel->user->email]);
            $existingUser = $existingUser[0];
            if(!isset($existingUser))
            {
                $viewModel->addError("No account with that email and password exist");
                return false; 
            }
            
            //Convert the user data into a matching model
            $viewModel->user = $viewModel->user->dynamicInit($existingUser);
        }
        catch (Exception $e)
        {
            $viewModel->addError("Unknown error during account creation, please try again later...");
            return false;
        }

        //Verify that the entered password matches the hashed account password
        if (!password_verify($userInput['password'] , $viewModel->user->password))
        {
            $viewModel->addError("Password is incorrect");
            return false;
        } 

        //log in the user
        $this->getSession();
        $_SESSION['id'] = $viewModel->user->id;
        $_SESSION['username'] = $viewModel->user->username;
        $_SESSION['admin'] = $viewModel->user->admin;
        return true;
    }
}