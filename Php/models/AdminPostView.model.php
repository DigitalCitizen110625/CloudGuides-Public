<?php

/*
* 	DESCRIPTION	: Used when the view needs to show a list of services, and providers, for a selected post
*/
class AdminPostView extends PostView
{
    public array $providersCollection;  //Collection of providers registered in the db
    public array $servicesCollection;   //Collection of services available for a selected provider


    public function __construct()
    {
        parent::__construct();
        $this->providersCollection = array();
        $this->servicesCollection = array();
    }

    /*
    * 	DESCRIPTION	: Adds a provider to the providersCollection array
    *	PARAMETERS	: Provider $provider : Object to add
    *   RETURNS     : NA
    */
    public function addProvider(Provider $provider)
    {
        array_push($this->providersCollection, $provider);
    }

    /*
    * 	DESCRIPTION	: Adds a service to the servicesCollection array
    *	PARAMETERS	: Service $service : Object to add
    *   RETURNS     : NA
    */
    public function addService(Service $service)
    {
        array_push($this->servicesCollection, $service);
    }


    /*
    * 	DESCRIPTION	: Clears the providers collection of all entries
    *	PARAMETERS	: NA
    *   RETURNS     : NA
    */
    public function clearProviders()
    {
        $this->providersCollection = array();
    }


    /*
    * 	DESCRIPTION	: Clears the services collection of all entries
    *	PARAMETERS	: NA
    *   RETURNS     : NA
    */
    public function clearServices()
    {
        $this->servicesCollection = array();
    }
}
?>