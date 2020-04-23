<?php

use core\Router;

$router = new Router;

$router->get('/', 'HomeController@index');

/** Login */
$router->get('/login', 'LoginController@signin');
$router->post('/login', 'LoginController@signinDo');

/** Cadastro */
$router->get('/cadastro', 'LoginController@signup');
$router->post('/cadastro', 'LoginController@signupDo');


/** Feed */
$router->post('/post/new', 'PostController@new');


/** Perfil */
$router->get('/perfil/{id}/follow', 'ProfileController@follow');
$router->get( '/perfil/{id}', 'ProfileController@index');
$router->get('/perfil', 'ProfileController@index');


$router->get('/sair', 'LoginController@logout');
