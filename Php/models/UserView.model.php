<?php

/*
* 	DESCRIPTION	: Stores and tracks errors printable to the UI, for the user database model
*/
class UserView extends ViewModelBase
{
    public User $user;              //Model representing a user entity in the database

    public function __construct()
    {
        parent::__construct();
        $this->user = new User();
    }
}