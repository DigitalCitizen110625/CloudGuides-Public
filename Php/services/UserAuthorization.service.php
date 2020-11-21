<?php

/*
* 	DESCRIPTION	: Checks if users are logged in, and authorized to access certain pages
*/
class UserAuthorization
{
  
    /*
    * 	DESCRIPTION	: Checks if the user is logged in
    *	PARAMETERS	: array $session : session variables containing the users login data
    *   RETURNS     : bool : True if the user is logged in, false otherwise
    */
    public function isUserLoggedIn(array $session) : bool
    {
        if (session_status() == PHP_SESSION_NONE)
        {
            session_start();
        }
        if (!isset($session['id']) || !isset($session['username']) || !isset($session['admin']) )
        {
            //All session variables must be set for the user to be logged in
            return false;
        }

        return true;
    }
}
?>