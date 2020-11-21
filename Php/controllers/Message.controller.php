<?php
require_once('../services/SmtpMailer.service.php');


/*
* 	DESCRIPTION	: Responsible for processing requests to send emails. 
*/
class MessageController
{
    /*
    * 	DESCRIPTION	: Validates the message, and sends it using an SMTP mailer
    *	PARAMETERS	: Message $viewModel : Model used to validate, and store the message data
    *                 array $userInput : Contains the users input for the message
    *	RETURNS		: NA
    */
    public function sendMessage(MessageView $viewModel, array $userInput)
    {
        //Save the users input in case they make an error
        $viewModel->message->name = trim($userInput['name']);
        $viewModel->message->address = trim($userInput['address']);
        $viewModel->message->content = trim($userInput['content']);
        $viewModel->clearErrors();


        //Needs a name of who sent the email
        if(empty($viewModel->message->name))
        {
            $viewModel->addError("Name is required");
        }
        //Needs an email address of where to send the message
        else if(empty($viewModel->message->address))
        {
            $viewModel->addError("Email is required");
        }
        //Needs at least some text 
        else if(empty($viewModel->message->content))
        {
            $viewModel->addError("A message is required");
        }

        //Disallow sending the email until all errors are fixed
        if(count($viewModel->errors) !== 0)
        {
            return;
        }

        //Input validation passed, now ensure the model is valid according to the smtp service settings
        if(!$viewModel->message->isValid())
        {
            $viewModel->addError("Please ensure the address is well formed, and that all entries do not exceed the max input length");
            return;
        }


        try
        {
            $mailer = SmtpMailer::getInstance();
            $mailer->sendEmail($viewModel->message->content, 'CloudeGuidesConatctMessage', $viewModel->message->address, $viewModel->message->name);
        }
        catch (Exception $e)
        {
            $viewModel->message->sendSuccess = false;
            $viewModel->addError("Unknown error during message transmission");
            return;
        }

        //Clear their input so we can show a blank form again
        $viewModel->message->sendSuccess = true;
        $viewModel->message->name = "";
        $viewModel->message->address = "";
        $viewModel->message->content = "";
    }
}
?>