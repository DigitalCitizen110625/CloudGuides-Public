<?php declare(strict_types=1);

/*
* 	DESCRIPTION	: Stores and tracks errors printable to the UI, for the message database model
*/
class MessageView extends ViewModelBase
{
    public Message $message;            //Model used for data validations, and storing message details

    public function __construct()
    {
        parent::__construct();
        $this->message = new Message();
    }
}
?>