<?php declare(strict_types=1);

/*
* 	DESCRIPTION	: Model used to define the properties of what constitutes a post in the database
*/
class Post extends DatabaseModel
{
    public int      $providerId;
    public int      $serviceId;
    public string   $imageUrl;
    public string   $title;
    public string   $subheading;
    public string   $content;
    public string   $submissionDate; 
    public int      $userId;     

    //Because each of the properties correspond to a column in the post table, they have a hard limit on their max length
    private const nullableId = false;        //Db column keys must not be null
    private const maxImageUrlLength = 255;
    private const maxTitleLength = 255;
    private const maxSubheadingLength = 255;
    private const maxContentLength =  65535;

    public function __construct(
        int $id = -1, 
        int $providerId = -1, 
        int $serviceId = -1, 
        string $imageUrl = "", 
        string $title = "", 
        string $subheading = "", 
        string $content = "", 
        string $submissionDate = "", 
        int $userId  = -1)
    {
        parent::__construct();
        $this->id = $id;
        $this->providerId = $providerId;
        $this->serviceId = $serviceId;
        $this->imageUrl = $imageUrl;
        $this->title = $title;
        $this->subheading = $subheading;
        $this->content = $content;
        $this->submissionDate = $submissionDate;
        $this->userId = $userId;
    }

    /*
    * 	DESCRIPTION	: Checks that the objects id is valid
    *	PARAMETERS	: NA
    *   RETURNS     : bool : True if the id property is valid, false otherwise
    */
    public function isIdValid()
    {
        //Id must not be the default value
        if(!$this->validateId($this->id, self::nullableId))
        {
            return false;
        }
    }
    


    /*
    * 	DESCRIPTION	: Checks that each property, except the ID, is valid
    *	PARAMETERS	: NA
    *   RETURNS     : bool : True if all checks passed, false otherwise
    */
    public function isValid() : bool
    {
        //Model validation fails if any property is invalid
        if(!$this->validateId($this->providerId, self::nullableId))
        {
            return false;
        }
        else if(!$this->validateId($this->serviceId, self::nullableId))
        {
            return false;
        }
        else if(!$this->validateId($this->userId, self::nullableId))
        {
            return false;
        }
        else if(!$this->validateLength($this->title, self::maxTitleLength)) 
        {
            return false;
        }
        else if(!$this->validateLength($this->subheading, self::maxSubheadingLength)) 
        {
            return false;
        }
        else if(!$this->validateLength($this->content, self::maxContentLength)) 
        {
            return false;
        }
        else if(!$this->validateLength($this->imageUrl, self::maxImageUrlLength)) 
        {
            return false;
        }

        //Model is valid once all checks pass
        return true;
    }
}
?>