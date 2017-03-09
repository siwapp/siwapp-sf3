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
        $translator = $this->get('translator');
        $property_repository = $em->getRepository('SiwappConfigBundle:Property');
        $all_taxes = $em->getRepository('SiwappCoreBundle:Tax')->findAll();
        $all_series = $em->getRepository('SiwappCoreBundle:Series')->findAll();

        $data = $property_repository->getAll();
        $data['taxes'] = $all_taxes;
        $data['series'] = $all_series;

        $form = $this->createForm('Siwapp\ConfigBundle\Form\GlobalSettingsType', $data);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                $series = $formData['series'];
                $taxes = $formData['taxes'];
                unset($formData['series'], $formData['taxes']);

                if (!empty($formData['company_logo'])) {
                    /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
                    $file = $formData['company_logo'];
                    // Generate a unique name for the file before saving it
                    $fileName = 'logo.' . $file->guessExtension();
                    // Move the file to the uploads directory.
                    $uploadsDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';
                    try {
                        $newFile = $file->move($uploadsDir, $fileName);
                        // Update the property to the new file name.
                        $formData['company_logo'] = 'uploads/' . $newFile->getFileName();
                    }
                    catch (FileException $e) {
                        $msg = $translator->trans('flash.logo_upload_error', [], 'SiwappConfigBundle');
                        $this->get('session')->getFlashBag()->add('warning', $msg);
                    }
                } else {
                    // Unset the key otherwise the previous logo will be deleted.
                    unset($formData['company_logo']);
                }

                $property_repository->setPropertiesFromArray($formData);
                // Save series.
                foreach ($series as $item) {
                    $em->persist($item);
                }
                // Remove series.
                foreach ($all_series as $item) {
                    if (!in_array($item, $series)) {
                        $em->remove($item);
                    }
                }
                // Save taxes.
                foreach ($taxes as $tax) {
                    $em->persist($tax);
                }
                // Remove taxes.
                foreach ($all_taxes as $item) {
                    if (!in_array($item, $taxes)) {
                        $em->remove($item);
                    }
                }
                $em->flush();
                $msg = $translator->trans('flash.updated', [], 'SiwappConfigBundle');
                $this->get('session')->getFlashBag()->add('success', $msg);
            }
        }

        return array(
            'form' => $form->createView(),
            'settings' => $data,
        );
    }
}
