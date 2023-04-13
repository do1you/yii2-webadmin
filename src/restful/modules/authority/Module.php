<?php

namespace apiadmin\authority;

/**
 * Module module definition class
 */
class Module extends \webadmin\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'apiadmin\authority\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
