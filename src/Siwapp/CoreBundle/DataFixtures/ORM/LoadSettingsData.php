<?php
namespace Siwapp\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Util\Inflector;
use Doctrine\Common\Persistence\ObjectManager;

use Siwapp\ConfigBundle\Entity\Property;

class LoadSettingsData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $settings = [
            'company_name' => 'My Company',
            'company_identification' => 'XX123456789',
            'company_address' => 'My street name, 12',
            'company_email' => 'info@mycompany.com',
            'company_phone' => '+01 234 567 89',
            'company_fax' => '+01 234 567 89',
            'company_url' => 'https://www.mycompany.com',
            'currency' => 'EUR',
        ];
        foreach ($settings as $name => $value) {
            $setting = new Property();
            $setting->setKey($name);
            $setting->setValue($value);

            $manager->persist($setting);
            $manager->flush();
            $this->addReference($name, $setting);
        }
    }

    public function getOrder()
    {
        return '0';
    }
}
