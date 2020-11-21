<?php


/*
* 	DESCRIPTION	: Base class from which all controller type classes derive from. Contains methods commonly used by all controllers.
*/
abstract class ControllerBase
{
    /*
    * 	DESCRIPTION	: Starts a new or resumes an existing session
    *	PARAMETERS	: NA
    *   RETURNS     : bool : True if session resumed, and data was found, false otherwise
    */
    protected function getSession() : bool
    {
        if (session_status() == PHP_SESSION_NONE)
        {
            session_start();
        }
        if(empty($_SESSION))
        {
            return false;
        }

        return true;
    }

    /*
    * 	DESCRIPTION	: Destroys all data registered to a session
    *	PARAMETERS	: NA
    *   RETURNS     : NA
    */
    public function destroySession()
    {
        /*
        *   Check if the user is logged in, if so, remove their session(logout), and redirect them back to the main page
        *   Note: Only call this script from files in the View directory. Calling it from another directory will break the redirection action below
        */
        if (session_status() == PHP_SESSION_NONE)
        {
            session_start();
        }
        else
        {
            //Unset all of the session variables
            $_SESSION = array();
            session_destroy();
        }
    }

    /*
    * 	DESCRIPTION	: Checks that the id request param is well formed
    *	PARAMETERS	: NA
    *   RETURNS     : bool : True if all checks passed, false otherwise
    */
    public function validateRequestId(string $id) : bool
    {
        if(!isset($id))
        {
            //No id provided
            return false;
        }

        //The id must be a digit
        else if(!preg_match('/^\d+$/',$id))
        {
            //Invalid id
            return false;
        }
        return true;
    }
}
?>