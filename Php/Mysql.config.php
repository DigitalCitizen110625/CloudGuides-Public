<?php 

//Signals which set of credentials to use during database connection
define("DB_USE_LOCAL", false);

//Local mysql credentials (phpmyadmin)
define("DB_HOST", "<Removed>");
define("DB_USER", "<Removed>");
define("DB_PASSWORD", "<Removed>");
define("DB_DATABASE", "<Removed>");

//AWS RDS credentials
define("DB_REMOTE_HOST", "<Removed>");
define("DB_REMOTE_USER", "<Removed>");
define("DB_REMOTE_PASSWORD", "<Removed>");
define("DB_REMOTE_DATABASE", "<Removed>");
define("DB_REMOTE_PORT", '<Removed>');

//Database constants for accessing various tables, and views
define("POST_TABLE", "posts");
define("USERS_TABLE", "users");
define("PROVIDERS_TABLE", "providers");
define("SERVICES_TABLE", "services");
define("AZURE", "Azure");
define("AMAZON_WEB_SERVICES", "Amazon Web Services");
define("POSTS_SUMMARY_VIEW", 'SELECT posts.id, providers.name AS `providerName`, services.name AS `serviceName`, posts.title AS `title`, posts.submissionDate AS `submissionDate`, users.username AS `authorName` FROM `posts` INNER JOIN providers ON providers.id = posts.providerId INNER JOIN services ON services.id = posts.serviceId INNER JOIN users ON users.id = posts.userId');
define("SERVICES_LIST_QUERY", 'SELECT services.name , services.id FROM `services` WHERE services.providerId = ');
define("POSTS_FOR_PROVIDER", 'SELECT providers.name AS `providerName`, services.name AS `serviceName`, services.id, posts.title, posts.id, posts.submissionDate, posts.imageUrl, posts.subheading FROM posts INNER JOIN services ON services.id = posts.serviceId INNER JOIN providers ON providers.id = posts.providerId WHERE posts.providerId = ');
define("SELECTED_POST", 'SELECT posts.id, providers.name AS `providerName`, services.name AS `serviceName`, posts.title AS `title`, posts.submissionDate AS `submissionDate`, posts.content AS content, users.username AS `authorName`, posts.imageUrl FROM `posts` INNER JOIN providers ON providers.id = posts.providerId INNER JOIN services ON services.id = posts.serviceId INNER JOIN users ON users.id = posts.userId WHERE posts.id = ');
?>