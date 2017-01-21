<?php

namespace Siwapp\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CoreController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $bundles = $this->getParameter('kernel.bundles');
        $url = array_key_exists('SiwappDashboardBundle', $bundles) ? 'dashboard_index' : 'invoice_index';
        return $this->redirect($this->generateUrl($url));
    }

    /**
     * @Route("/item/autocomplete-description", name="item_autocomplete_description")
     */
    public function autocompleteDescriptionAction(Request $request)
    {
        $entities = $this->getDoctrine()
            ->getRepository('SiwappCoreBundle:Item')
            ->findLikeDescription($request->get('term'));

        return new JsonResponse($entities);
    }
}
