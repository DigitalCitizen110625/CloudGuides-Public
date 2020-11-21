<?php declare(strict_types=1);


/*
* 	DESCRIPTION	: View model used to contain a provider for the parnet post collection 
*/
class TopicsPostCollectionView extends PostCollectionView
{
    public Provider $provider;      //Provider data for the post collection

    public function __construct()
    {
        parent::__construct();
        $this->provider = new Provider();
    }
}
?>