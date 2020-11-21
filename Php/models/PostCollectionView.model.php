<?php declare(strict_types=1);


/*
* 	DESCRIPTION	: View model used to contain a collection of PostView objects. 
*/
class PostCollectionView extends ViewModelBase
{
    public array $postViews;     //Contains array of post view models

    public function __construct()
    {
        parent::__construct();
        $this->postViews = array();
    }

    /*
    * 	DESCRIPTION	: Adds another post to the collection
    *	PARAMETERS	: Post $post : Post object to add
    *   RETURNS     : NA
    */
    public function addPostView(PostView $postView)
    {
        array_push($this->postViews, $postView);
    }

    /*
    * 	DESCRIPTION	: Clears the posts collection of all entries
    *	PARAMETERS	: NA
    *   RETURNS     : NA
    */
    public function clearPosts()
    {
        $this->postViews = array();
    }
}
?>