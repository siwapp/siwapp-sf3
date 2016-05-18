<?php

namespace Siwapp\ConfigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Siwapp\ConfigBundle\Form\GlobalSettingsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @Route("/configuration")
 */
class ConfigController extends Controller
{
    /**
     * @Route("/global_settings", name="global_settings")
     * @Template("SiwappConfigBundle:Config:global_settings.html.twig")
     */
    public function globalSettingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $property_repository = $em->getRepository('SiwappConfigBundle:Property');

        $data = $property_repository->getAll();
        $data['taxes'] = $em->getRepository('SiwappCoreBundle:Tax')->findAll();
        $data['series'] = $em->getRepository('SiwappCoreBundle:Serie')->findAll();

        $form = $this->createForm('Siwapp\ConfigBundle\Form\GlobalSettingsType', $data);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                unset($data['series'], $data['taxes']);

                if (!empty($data['company_logo'])) {
                    /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
                    $file = $data['company_logo'];
                    // Generate a unique name for the file before saving it
                    $fileName = 'logo.' . $file->guessExtension();
                    // Move the file to the uploads directory.
                    $uploadsDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';
                    try {
                        $newFile = $file->move($uploadsDir, $fileName);
                        // Update the property to the new file name.
                        $data['company_logo'] = $newFile;
                    }
                    catch (FileException $e) {
                        $this->get('session')->getFlashBag()->add('warning', 'Could not store the logo. Make sure the web/uploads folder exists and is writable.');
                    }
                }

                $property_repository->setPropertiesFromArray($data);
                $this->get('session')->getFlashBag()->add('success', 'Settings updated.');
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }
}
