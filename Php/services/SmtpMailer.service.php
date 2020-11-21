<?php

require_once ('../../vendor/autoload.php');      //Composer's autoloader
require_once ('../Email.config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


/*
* 	DESCRIPTION	: Singleton class responsible for configuring and sending emails over SMTP
*/
class SmtpMailer
{
	#region Properties, Fields, Constructors
	
	
    private static $instance = null; 	//Holds the class instance.
    private string $fromAddress;    
    private string $toAddress;    
    private string $fromName;    
    private string $smtpUserName;    
    private string $smtpPassword;    
    private string $host;    
    private string $port; 
    private string $bodyTemplate;   


    private function __construct()
    {
        $this->toAddress = EMAIL_TO_ADDRESS;
        $this->fromAddress = EMAIL_FROM_ADDRESS;
        $this->fromName = EMAIL_FROM_NAME;
        $this->smtpUserName = EMAIL_SMTP_USERNAMAE;
        $this->smtpPassword = EMAIL_SMTP_PASSWORD;
        $this->host = EMAIL_HOST;
        $this->port = EMAIL_PORT;   
        $this->bodyTemplate = EMAIL_BODY_TEMPLATE;
	}

    final private function __clone()
    {
        throw new Exception('Feature disabled.');
    }

    final private function __wakeup()
    {
        throw new Exception('Feature disabled.');
	}



	#endregion
	#region PrivateMethods

	

   	/*
	* 	DESCRIPTION	: Configures the SMTP settings of the email object
    *	PARAMETERS	: PHPMailer $smtpEmailObject : Unconfigured email object 
    *	RETURNS		: PHPMailer : Configrued email objet
    */
	private function configureSmtpSettings($smtpEmailObject)
	{
        /*
        *   NOTE: As per https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting#updating-ca-certificates and 
        *   https://stackoverflow.com/questions/3477766/phpmailer-smtp-error-could-not-connect-to-smtp-host
        *   PHPMailer v5.6 and up has strict SSL behavior enforced. If there is an issue with your, or the 
        *   endpoints SSL Certificates, then $mail->send() will throw an error. It's not recommended, but you 
        *   can bypass this by using the following SMTP options below
        */
        $smtpEmailObject->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
		);


        //$smtpEmailObject->SMTPDebug = SMTP::DEBUG_SERVER;               // Enable verbose debug output
        $smtpEmailObject->Host       = $this->host;                       // Set the SMTP server to send through
        $smtpEmailObject->SMTPAuth   = true;                              // Enable SMTP authentication
        $smtpEmailObject->Username   = $this->smtpUserName;               // SMTP username
        $smtpEmailObject->Password   = $this->smtpPassword;               // SMTP password
        $smtpEmailObject->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;    // Enable TLS encryption
		$smtpEmailObject->Port       = $this->port;                                    
		return $smtpEmailObject;
	}
	

   	/*
	* 	DESCRIPTION	: Sets the body content of the email message
	*	PARAMETERS	: PHPMailer $smtpEmailObject : Mail object to set the body content 
	*				  string $name : Name of the user who submitted the message
	*	  			  string $message :  Content of the message
	*			      string $returnAddress : Email address of where to return the message
    *	RETURNS		: PHPMailer :  Email object with the message body filled in 
    */
	private function buildMessageBody($smtpEmailObject, $name, $message, $returnAddress)
	{
		//Replace the placeholders in the body template, with the submitted values
		$this->bodyTemplate = str_replace('<% fullName %>', $name, $this->bodyTemplate); 
		$this->bodyTemplate = str_replace('<% email %>', $returnAddress, $this->bodyTemplate); 
		$this->bodyTemplate = str_replace('<% message %>', $message, $this->bodyTemplate); 
		$smtpEmailObject->Body    = $this->bodyTemplate;
		return $smtpEmailObject;
	}

	
   	/*
	* 	DESCRIPTION	: Sets the messages sender, and recipient details
	*	PARAMETERS	: PHPMailer $smtpEmailObject : Mail object to set the recipients details
	*				  string $subject : Text of the emails subject line
    *	RETURNS		: PHPMailer :  Email object with the sender, and recipient filled in
    */
	private function setMessageHeader($smtpEmailObject, $subject)
	{
        $smtpEmailObject->setFrom($this->fromAddress, $this->fromName);         //Who is the message from?   
		$smtpEmailObject->addAddress($this->toAddress, 'OnlineAccountHoldings');//Who is the receipient?
		$smtpEmailObject->Subject = $subject;
		return $smtpEmailObject;
	}



	#endregion
	#region PublicMethods



   /*
    * 	DESCRIPTION	: Gets a reference of the singleton instance
    *	PARAMETERS	: NA
    *	RETURNS		: SmtpMailer : Reference to the instantiated instance of the class
    */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new SmtpMailer();
        }
        return self::$instance;
    }


    /*
    * 	DESCRIPTION	: Configures the emails SMTP settings, sets the headers, and message body, and transmits the email
	*	PARAMETERS	: string $message : Contents of the message
	*			   	  string $subject : Text for the subject line
	*				  string $returnAddress : Email address of where to reply
	*				  string $name : Name of the user sending the message
    *	RETURNS		: NA
    *   SOURCE      : The following code was provided by PHPMailer, and can be found at:
	*                 https://github.com/PHPMailer/PHPMailer, and 
	*				  https://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-using-smtp-php.html
    */
    public function sendEmail(string $message, string $subject, string $returnAddress, string $name)
    {

		$mail = new PHPMailer(true);    							//Passing `true` enables exceptions
		
		$mail->isSMTP();  
		$mail = $this->configureSmtpSettings($mail);
		$mail = $this-> SetMessageHeader($mail, $subject);
		
		$mail->isHTML(true);										//Passing `true` allows an HTML format for the email
		$mail= $this->buildMessageBody($mail, $name, $message, $returnAddress);      
		$mail->send();
	}
	

	#endregion
}
?>