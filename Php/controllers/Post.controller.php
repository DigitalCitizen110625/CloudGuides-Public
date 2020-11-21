<?php
require_once ("../controllers/ControllerBase.controller.php");
require ('../services/PostRepository.service.php');
require ('../dataLayer/MySqlDatabase.data.php');


/*
* 	DESCRIPTION	: Responsible for processing requests involving access of posts/article from the dataabse
*/
class PostController extends ControllerBase
{

    private PostRepository $repository; 


    public function __construct()
    {
        $this->repository = new PostRepository(new MySqlDatabase());
    }


    /*
    * 	DESCRIPTION	: Responsible for ensuring all user input is valid from the submit, and update post pages. 
    *                 Also responsible for registering new providers, and services from both pages.
    *	PARAMETERS	: AdminPostView $viewModel : Viewmodel used to store erorrs, and form data if an error has occured
    *                 array $postedValues : Contains all $_POST data submitted from the form
    *	RETURNS		: bool : TRUE if all user input was valid, false otherwise
    */
    private function processPostSubmission(AdminPostView $viewModel, array $postedValues) : bool 
    {
         //Ensure each input field is filled in before proceeding
        $providerIdSet = strlen($postedValues['providerId']) > 0;
        $newProviderNameSet = !empty($postedValues['newProviderName']);

        //Exactly one of the provider fields must be filled in, not both
        if(($providerIdSet && $newProviderNameSet) || (!$providerIdSet && !$newProviderNameSet))
        {
            //None, or both providers were specified
            $viewModel->addError("Post must have a single provider");
        }
        else if($providerIdSet)
        {
            //Provider selected from the drop down list
            $viewModel->provider->id = (int)$postedValues['providerId'];
        }
        else if($newProviderNameSet)
        {
            //New provider defined
            $viewModel->provider->name = $postedValues['newProviderName'];
        }


        //Service input validation
        $serviceIdSet = strlen($postedValues['serviceId']) > 0;
        $newServiceNameSet = !empty($postedValues['newServiceName']);
        
        //Exactly one of the service fields must be filled in, not both
        if(($serviceIdSet && $newServiceNameSet) || (!$serviceIdSet && !$newServiceNameSet))
        {
            $viewModel->addError("Post must have a single service");
        }
        else if($serviceIdSet)
        {
            //Service selected from the service drop down list
            $viewModel->service->id = (int)$postedValues['serviceId'];
        }
        else if($newServiceNameSet)
        {
            //New service defined
            $viewModel->service->name = $postedValues['newServiceName'];
        }


        //Post textual content validation
        if(empty($postedValues['title']))
        {
            $viewModel->addError("Post must have a title");
        }

        if(empty($postedValues['subheading']))
        {
            $viewModel->addError("Post must have a subheading");
        }

        if(empty($postedValues['imageUrl']))
        {
            $viewModel->addError("Post must have an image");
        }

        if(empty($postedValues['content']))
        {
            $viewModel->addError("Post must have some content to display");
        }

        
        //Save the users input for reprinting to the view
        $viewModel->post->id = $postedValues['id'];
        $viewModel->post->title = $postedValues['title'];
        $viewModel->post->content = $postedValues['content'];
        $viewModel->post->imageUrl = $postedValues['imageUrl'];
        $viewModel->post->subheading = $postedValues['subheading'];


        //Return to the view if any errors were detected
        if(count($viewModel->errors) !== 0) { return false; }      
        

        //If a new provider was defined, save it to the database, and add its id to the post
        if($newProviderNameSet)
        {
            try
            {
                //Save the new provider, and print it to the view on reload
                $viewModel->post->providerId = (int)$this->repository->insert(['name' => $viewModel->provider->name], PROVIDERS_TABLE);
            }
            catch (Exception $e)
            {
                $viewModel->addError("Error creating new provider, please try again later");
                return false;
            }
        }


        //If a new service was defined, then save it to the database, and add its id to the post
        if($newServiceNameSet)
        {
            try
            {
                //Save the new service and print it to the view on reload
                $viewModel->post->serviceId = (int)$this->repository->insert([ 'providerId'=> $viewModel->provider->id, 'name' => $viewModel->service->name], SERVICES_TABLE);
            }
            catch (Exception $e)
            {
                $viewModel->addError("Error creating new service,  please try again later");
                return false;
            }
        }


        //Remove the processed inputs, and pass the remaining data to the post for dynamic instantiation
        unset($postedValues['newProviderName']);
        unset($postedValues['newServiceName']);
        $viewModel->post = $viewModel->post->dynamicInit($postedValues);
                            

        //The user must be logged in to submit a post, so we can get their account data from their session
        if(!$this->getSession())
        {
            //Session data was empty
            $viewModel->addError("Unable to access user credentials, please re-login and try again");
            return false;
        }
        $viewModel->post->userId = (int)$_SESSION["id"];


        //Validate the post before insertion into the db
        if(!$viewModel->post->isValid())
        {
            return false;
        }

        //All checks passed
        return true;
    }


    /******************************************************************************
    *
    *   GET Requests
    *
    ******************************************************************************/


    /*
    * 	DESCRIPTION	: Gets a list of service names, and their ids, for the target provider
    *	PARAMETERS	: string $providerId : Id of the taget provider 
    *	RETURNS		: array: Associative array with the service data
    */
    public function getServices(string $providerId) : array
    {
        try
        {
            return $this->repository->query(SERVICES_LIST_QUERY. $providerId);
        }
        catch(Exception $e)
        {
            return false;
        }
    }

    /*
    * 	DESCRIPTION	: Queries the db for all posts for each registered provider
    *	PARAMETERS	: PostCollectionView $viewModel : Collection of post view models with each containing data for a single post
    *   RETURNS     : NA
    */
    public function getAdminDashboard(PostCollectionView $viewModel)
    {
        $viewModel->clearPosts();

        try
        {
            /* 
            *   Query will get: 
            *   - provider.providerName
            *   - service.serviceName
            *   - user.authorName
            *   - post.id
            *   - post.title
            *   - post.submissionDate
            */
            $postSummaryCollection = $this->repository->query(POSTS_SUMMARY_VIEW);
        }
        catch (Exception $e)
        {
            //DB error during post query
            $viewModel->addError("Database error during post retrieval"); 
            return;
        }

        //Check if there's any posts 
        if(empty($postSummaryCollection))
        {
            //Posts were not found
            $viewModel->addError("No posts could be found"); 
            return;
        }

        //Init a new post model for each entry in the summary collection
        foreach($postSummaryCollection as $postSummary)
        {
            //Note that null is disallowed for the object id, so we pass -1 instead. All ids in the database must be above 0
            $postView = new PostView();
            $postView->provider = new Provider(-1, $postSummary['providerName']);
            $postView->service = new Service(-1, $postSummary['serviceName']);
            $postView->user = new User(-1, $postSummary['authorName']);
            $postView->post = new Post();


            //Remove the provider, service, and user entries from the array, leaving just the 
            //  entries for the Post() class. These be used to dynamically instantiate the viewModel
            unset($postSummary['providerName']);
            unset($postSummary['serviceName']);
            unset($postSummary['authorName']);

            //Convert the remaining post data into an object, and add it to the view viewModel
            $postView->post = $postView->post->dynamicInit($postSummary);
            $viewModel->addPostView($postView);
        }

        return;
    }

    /*
    * 	DESCRIPTION	: Finds the requested entity, and saves it to the model
    *	PARAMETERS	: string $postId : Id the post to find
    *                 PostView $model : Viewmodel containing the post, and all related entities (ex: provider, and service)
    *   RETURNS     : NA
    */
    public function getUpdate(AdminPostView $viewModel, string $postId) : bool 
    {
        $post = null;
        $providersCollection = null;
        $servicesCollection = null;

        try
        {
            /* 
            *   Limit query to 1 record
            *   Get all columns
            *   Find the entity where the id matches the postId
            */
            $post = $this->repository->dynamicQuery(1, [], ['id' => $postId]);
            if(empty($post)) { return false; }

            //Post was found
            $post = $post[0];

            //Get all providers 
            $providersCollection = $this->repository->dynamicQuery(null, [], [], PROVIDERS_TABLE);

            //Get all services associated with the posts provider
            $servicesCollection = $this->repository->dynamicQuery(null, [], ['id' => $post['providerId']], SERVICES_TABLE);
        }
        catch(Exception $e)
        {
            $viewModel->addError('Database error during post query, please try again later');
            return false;
        }

        //Populate the providers collection
        foreach($providersCollection as $providerArray)
        {
            $provider = new Provider();
            $provider = $provider->dynamicInit($providerArray);
            $viewModel->addProvider($provider);
        }
        
        //Popualte the services collection
        foreach($servicesCollection as $serviceArray)
        {
            $service = new Service();
            $service = $service->dynamicInit($serviceArray);
            $viewModel->addService($service);
        }

        //Init the models using the data retrieved from the database
        $viewModel->post = $viewModel->post->dynamicInit($post);

        //Find the provider data matching the posts providerId
        $providerKey = array_search($post['providerId'], array_column($providersCollection, 'id'));
        $viewModel->provider = $viewModel->provider->dynamicInit($providersCollection[$providerKey]);

        //Find the service data matching the posts serviceId
        $serviceKey = array_search($post['serviceId'], array_column($servicesCollection, 'id'));
        $viewModel->service = $viewModel->service->dynamicInit($servicesCollection[$serviceKey]);
        return true;
    }

    /*
    * 	DESCRIPTION	: Clears the view model, and adds a list of available providers
    *	PARAMETERS	: AdminPostView $viewModel : Contains the post data printable to the view
    *   RETURNS     : NA
    */
    public function getSubmit(AdminPostView $viewModel)
    {
        try
        {
            /* We want:
            *   - Query the providers table
            *   - All rows from the table
            *   - All columns from the table
            */
            $providersCollection = $this->repository->dynamicQuery(null, [], [], PROVIDERS_TABLE);

            //Populate the providers collection
            foreach($providersCollection as $providerArray)
            {
                $provider = new Provider();
                $provider = $provider->dynamicInit($providerArray);
                $viewModel->addProvider($provider);
            }
        }
        catch(Exception $e)
        {
            $viewModel->addError('Database error during post query, please try again later');
        }
    }

    /*
    * 	DESCRIPTION	: Gets all data for the requested post, and stores it in the model
    *	PARAMETERS	: PostView $viewModel : Contains the post data printable to the view
    *                 string $postId : Id of the requested post
    *   RETURNS     : bool : True if the post was found with no errors, false otherwise
    */
    public function getArticlePost(PostView $viewModel, string $postId) : bool
    {
        /* Our query will retrieve the following columns : 
        *  - Provider name
        *  - Service name
        *  - Post id 
        *  - Image url
        *  - Post title
        *  - Post content
        *  - Submission date
        *  - Authors name
        */
        try
        {
            $article = $this->repository->query(SELECTED_POST . "$postId");
            if(empty($article))
            {
                //Post was not found
                return false;
            }
        }
        catch(Exception $e)
        {
            $viewModel->addError('Database error during post query, please try again later');
            return false;
        }

        $article = $article[0];
        $viewModel->provider->name = $article['providerName'];
        $viewModel->service->name = $article['serviceName'];
        $viewModel->user->username = $article['authorName'];


        //Remove the provider, service, and user data leaving only the post values
        unset($article['providerName']);
        unset($article['serviceName']);
        unset($article['authorName']);
        $viewModel->post = $viewModel->post->dynamicInit($article);

        return true;
    }


    /*
    * 	DESCRIPTION	: Get a collection of posts for the selected provider
    *	PARAMETERS	: TopicsPostCollectionView $viewModel: Model used to store the posts, and provider data
    *                 string $providerId: Id of the selcted provider
    *   RETURNS     : bool : True if all posts were found, false otherwise
    */
    public function getTopics(TopicsPostCollectionView $viewModel, string $providerId) : bool
    {
        try
        {
            //Get all the data for a collection of view models representing each post
            $postViewCollection = $this->repository-> query(POSTS_FOR_PROVIDER . $providerId);
            if(empty($postViewCollection))
            {
                return false;
            }

            //Since we've grabbed all the posts for a single provider, they should all be the same
            $viewModel->provider->name = $postViewCollection[0]['providerName'];


            foreach($postViewCollection as $dbPostView)
            {
                $newPostView = new PostView();
                $newPostView->provider->name = $dbPostView['providerName'];
                $newPostView->service->name = $dbPostView['serviceName'];


                //The post views contain additional details not part of the post model, so we want to remove this 
                //  extra data allowing us to dynamically instantiate a post model using the remaining data
                unset($dbPostView['providerName']);
                unset($dbPostView['serviceName']);
                $newPostView->post = $newPostView->post->dynamicInit($dbPostView);
                $viewModel->addPostView($newPostView);
            }
        }
        catch (Exception $e)
        {
            //DB error during post query
            $viewModel->addError("Unexpected errror during post retrieval");
        }

        return true;
    }

    
    /******************************************************************************
    *
    *   POST Requests
    *
    ******************************************************************************/


    /*
    * 	DESCRIPTION	: Ensures the model is valid, and passes the post to the repo for insertion, or updating
    *	PARAMETERS	: array $userInput : Posted user inputs
    *                 AdminPostView $viewModel : Model used to store the post data
    *   RETURNS     : bool : True if post submission was successfull, false otherwise
    */
    public function upsertPost(AdminPostView $viewModel, array $userInput) : bool
    {
        //Clear the errors between requests
        $viewModel->clearErrors();

        if(!$this->processPostSubmission($viewModel, $userInput))
        {
            return false;
        }

        try
        {
            //New posts have their id defaulted to -1
            if($viewModel->post->id > -1)
            {
                //Because we're updating the post, we have to remove the "submissionDate" field since we're not changing the original submission date
                $updatedPost = (array)($viewModel->post);
                unset($updatedPost['submissionDate']);

                //Model has an id, so we're updating a post
                $affectedRows = $this->repository->update($updatedPost, $viewModel->post->id); 
                
                //Check that the update was successful
                if($affectedRows < 1)
                {
                    $viewModel->addError("Update unsuccessful, no rows affected");
                    return false;
                }
            }
            else
            {
                //The submission date will be added automatically by the db upon submission, so we must remove it beforehand
                $newPost = (array)($viewModel->post);
                unset($newPost['submissionDate']);

                //Recall that the id is defaulted to -1 upon instantiation of the post object, so we must remove it from the insertion data
                unset($newPost['id']);

                //Save the new post
                $recordId = $this->repository->insert($newPost); 
            }
        }
        catch (Exception $e)
        {
            //DB error during insertion
            $viewModel->addError("Database exception during upsert, please try again later");
            return false;
        }

        return true;
    }

    /*
    * 	DESCRIPTION	: Deletes the requested post from the database
    *	PARAMETERS	: string $postId : Id of the entitiy to delete
    *   RETURNS     : bool : True if the deletion was a success, false otherwise
    */
    public function delete(string $postId) : bool
    {
        try
        {
            $affectedRows =  $this->repository->delete($postId);
            if(!$affectedRows > 0 )
            {
                //Record was not deleted
                return false;
            }
           
            //Record was deleted 
            return true;
        }
        catch (Exception $e)
        {
            //DB error during post query
            $this->viewModel->addErrors("Error during post deletion, please try again later");
            return false;
        }
    }
}
?>