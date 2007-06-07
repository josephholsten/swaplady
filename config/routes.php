<?php
require_once 'Zend/Controller/Router/Rewrite.php';

// You may want to use the simpler Zend_Controller_Router
// which provides a simple routing
// http://example.com/
// -> controller => index
//    action     => index
//    params     => array()
// http://example.com/user
// -> controller => user
//    action     => index
//    params     => array()
// http://example.com/user/new
// -> controller => user
//    action     => new
//    params     => array()
// http://example.com/user/show/id/1/name/adam
// -> controller => user
//    action     => show
//    params     => array('id'=>1, 'name'=>'adam)

// More complicated routing is available through the RewriteRouter
$router = new Zend_Controller_Router_Rewrite();
// RewriteRouter automatically provides the following route:
// $this->addRoute('default',
//     new Zend_Controller_Router_Route(':controller/:action/*',
//     array('controller' => 'index', 'action' => 'index')));
// If want this, comment the following line
// $router->removeDefaultRoutes();
$router->addRoute('build',
    new Zend_Controller_Router_Route('build',
    array('controller' => 'search', 'action' => 'build')));
$router->addRoute('newSearch',
    new Zend_Controller_Router_Route('search',
    array('controller' => 'search', 'action' => 'new')));
$router->addRoute('showSearch',
    new Zend_Controller_Router_Route('search/:q',
    array('controller' => 'search', 'action' => 'show')));
$router->addRoute('tagIndex',
    new Zend_Controller_Router_Route('tag',
    array('controller' => 'tag', 'action' => 'index')));
$router->addRoute('tagShow',
    new Zend_Controller_Router_Route('tag/:tag',
    array('controller' => 'tag', 'action' => 'show')));
$router->addRoute('standard',
    new Zend_Controller_Router_Route(':controller/:action/:id'));

Zend_Registry::set('router', $router);