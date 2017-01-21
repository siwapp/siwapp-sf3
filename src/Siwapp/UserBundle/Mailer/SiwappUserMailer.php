<?php

namespace Siwapp\UserBundle\Mailer;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Mailer\Mailer;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Make FOS mailer use sender email using Siwapp's config.
 */
class SiwappUserMailer extends Mailer
{
    /**
     * {@inheritdoc}
     */
    public function __construct($mailer, UrlGeneratorInterface  $router, EngineInterface $templating, array $parameters, ObjectManager $em)
    {
        parent::__construct($mailer, $router, $templating, $parameters);

        $this->entityManager = $em;
    }

    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        $repo = $this->entityManager->getRepository('SiwappConfigBundle:Property');
        $companyName = $repo->get('company_name');
        $companyEmail = $repo->get('company_email');
        if ($companyName && $companyEmail && $fromEmail === ['webmaster@example.com' => 'webmaster']) {
            $fromEmail = [$companyEmail => $companyName];
        }

        parent::sendEmailMessage($renderedTemplate, $fromEmail, $toEmail);
    }

}
