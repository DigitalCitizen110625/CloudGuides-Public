<?php declare(strict_types=1);

/*
* 	DESCRIPTION	: Model used to define the properties of what constitutes a post in the database
*/
class PostView extends ViewModelBase
{

    public Provider $provider;      //Provider db model
    public Service  $service;       //Service db model
    public Post     $post;          //Post db model
    public User     $user;          //User db model

    public function __construct()
    {
        parent::__construct();
        $this->provider = new Provider();
        $this->service = new Service();
        $this->post = new Post();
        $this->user = new User();
    }
}
?>