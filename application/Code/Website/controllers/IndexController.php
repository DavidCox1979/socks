<?php
class IndexController extends Zend_Controller_Action
{
    function indexAction()
    {
        $this->render( 'index', null, true );
    }
}