<?php

namespace Siwapp\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/customers")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="customer_index")
     * @Template
     */
    public function indexAction()
    {
        return array();
    }
}
