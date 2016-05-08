<?php

namespace Siwapp\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController extends Controller
{
    /**
     * @Route("/", name="dashboard_index")
     * @Template("SiwappDashboardBundle:Default:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('SiwappInvoiceBundle:Invoice')->findAll();

        return array(
            'entities' => array_slice($entities, 0, 4),
            'overdue_invoices' => array_slice($entities, 0, 4),
            'currency' => 'EUR',
        );
    }
}
