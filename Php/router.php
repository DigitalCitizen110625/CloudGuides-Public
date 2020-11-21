<?php
#region Request Processing


$requestedFile = RegexRequestedFile($_SERVER['REQUEST_URI']);

/******************************************************************************
*
*   GET
*
******************************************************************************/
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    switch ($requestedFile)
    {

        case ('admin'):
            
            //If the user is not logged in, and tries to access any of the admin pages, transfer them to the login page
            if(!checkAuthorization())
            {
                //User not logged in, or not authorized to access the page
                header('Location: ./login.view.php', TRUE, 301 );
                return;
            }
            require ('../controllers/Post.controller.php');
            $controller = new PostController(); 
            $controller->getAdminDashboard($viewModel);
            break;


        case ('submit'):  
            if(!checkAuthorization())
            {
                //User not logged in, or not authorized to access the page
                header('Location: ./login.view.php', TRUE, 301 );
                return;
            }

            require ('../controllers/Post.controller.php');
            $controller = new PostController();

            //Ajax request to get the services for the selected provider
            if(array_key_exists('providerId', $_REQUEST))
            {    
                //Id must be defined in the query string
                if(!$controller->validateRequestId($_REQUEST['providerId']))
                {
                    //Invalid id in request
                    echo json_encode(false);
                    exit;
                }
    
                //Return the services as a jason array
                echo json_encode($controller->getServices($_REQUEST['providerId']));
                exit;
            }
            else
            {
                $controller->getSubmit($viewModel); 
            }
            break;


        case ('update'):
            if(!checkAuthorization())
            {
                //User not logged in, or not authorized to access the page
                header('Location: ./login.view.php', TRUE, 301 );
                return;
            }

            //Id must be defined in the query string
            require ('../controllers/Post.controller.php');
            $controller = new PostController(); 
            if(!array_key_exists('id', $_REQUEST))
            {
                //IProvider id was not provided in the request
                header('Location: ./404.view.php', TRUE, 301 );
                exit;
            }
            else if(!$controller->validateRequestId($_REQUEST['id']))
            {
                //Invalid id param in request
                header('Location: ./404.view.php', TRUE, 301 );
                exit;
            }
            else if(!$controller->getUpdate($viewModel, $_REQUEST['id']))
            {
                //Post not found
                header('Location: ./404.view.php', TRUE, 301 );
            }
            break;


        case ('post'):
            require ('../controllers/Post.controller.php');
            $controller = new PostController(); 


            //Id must be defined in the query string
            if(!array_key_exists('id', $_REQUEST))
            {
                //IProvider id was not provided in the request
                header('Location: ./404.view.php', TRUE, 301 );
                exit;
            }
            else if(!$controller->validateRequestId($_REQUEST['id']))
            {
                //Invalid id in request
                header('Location: ./404.view.php', TRUE, 301 );
                exit;
            }
            else if(!$controller->getArticlePost($viewModel, $_REQUEST['id']))
            {
                //Post not found
                header('Location: ./404.view.php', TRUE, 301 );
            }
            break;


            case ('topics'):
                require ('../controllers/Post.controller.php');
                $controller = new PostController(); 
    
    
                //Id must be defined in the query string
                if(!array_key_exists('id', $_REQUEST))
                {
                    //IProvider id was not provided in the request
                    header('Location: ./404.view.php', TRUE, 301 );
                    exit;
                }
                else if(!$controller->validateRequestId($_REQUEST['id']))
                {
                    //Invalid id in request
                    header('Location: ./404.view.php', TRUE, 301 );
                    exit;
                }
                else if(!$controller->getTopics($viewModel, $_REQUEST['id']))
                {
                    //Post not found
                    header('Location: ./404.view.php', TRUE, 301 );
                }
            break;


        default:
            break;
    }
}



/******************************************************************************
*
*   POST
*
******************************************************************************/
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    switch ($requestedFile)
    {
        case ('logout'):
            require ('../controllers/Account.controller.php');
            $controller = new AccountController();
            $controller->destroySession();
            header('Location: ./index.view.php', TRUE, 301 );
            break;

        case ('login'):
            require ('../controllers/Account.controller.php');
            $controller = new AccountController();
            if($controller->login($viewModel, $_POST))
            {
                //Login successful
                header('Location: ./admin.view.php', TRUE, 301 );
            }
            break;

        case ('register'):
            require ('../controllers/Account.controller.php');
            $controller = new AccountController();
            if($controller->register($viewModel, $_POST))
            {
                //Registration successful
                header('Location: ./index.view.php', TRUE, 301 );
            }
            break;

        case ('submit'):  
            require ('../controllers/Post.controller.php');
            $controller = new PostController(); 

            //Repopulate the list of providers , and process the newley posted data
            $controller->getSubmit($viewModel); 
            if($controller->upsertPost($viewModel, $_POST))
            {
                //Post insertion successful
                header('Location: ./admin.view.php', TRUE, 301 );
            }
            break;

        case ('delete'):
            //Strictly called as an ajax action, so the return must be json encode 
            if(!array_key_exists('id', $_POST))
            {
                echo json_encode(false);
                exit;
            }
            require ('../controllers/Post.controller.php');
            $controller = new PostController(); 
            if($controller->delete($_POST['id']))
            {
                echo json_encode(true);
            }
            else
            {
                echo json_encode(false);
            }
            exit;
            break;

        case ('contact'):
            require ('../controllers/Message.controller.php');
            $controller = new MessageController();
            $controller->sendMessage($viewModel, $_POST);
            break;

        default:
            break;
    }
}



#endregion
#region Functions



/*
* 	DESCRIPTION	: Determines the name of the requested file name using the request uri
*	PARAMETERS	: string $requestUri : URI of the requested web page (ex: http://localhost/CloudGuides/Php/views/post.view.php?id=4)
*   RETURNS     : string : Name of the requested file (without the file type extension, ex: The uri from above will return "post")
*/
function regexRequestedFile(string $requestUri) : string
{
    $match = array();
    preg_match('/(?:.*\/)(\w+)[.]/i', $requestUri, $match);
    return $match[1];   //Match[0] is the non-capturing group, match[1] contains the captured \w+
}

function checkAuthorization()
{
    require ('../services/UserAuthorization.service.php');
    require ('../session.php');
    $authService = new UserAuthorization();
    return $authService->isUserLoggedIn($_SESSION);
}


#endregion
?>