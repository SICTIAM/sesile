<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\MainBundle\Entity\CollectiviteOzwillo;

/**
 * Class ColelctiviteFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class CollectiviteFixtures extends Fixture
{
    const COLLECTIVITE_ONE_REFERENCE = 'collectivite-one';
    const COLLECTIVITE_TWO_REFERENCE = 'collectivite-two';
    const COLLECTIVITE_THREE_REFERENCE = 'collectivite-three';

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $collectivite = self::aValidCollectivite();
        $collectiviteOzwillo = self::aValidCollectiviteOzwillo($collectivite);


        $manager->persist($collectivite);
        $manager->persist($collectiviteOzwillo);

        /**
         * create a new collectivite that will have no users
         */
        $collectivite2 = self::aValidCollectivite('nice', 'b Mairie de Nice', '11111111');
        $collectiviteOzwillo2 = self::aValidCollectiviteOzwillo(
            $collectivite2,
            'ad284e47-10ce-470a-9f33-bf1b74a46d60',
            'ad284e47-10ce-470a-9f33-bf1b74a46d60',
            'secret',
            '11111'
        );


        $manager->persist($collectivite2);
        $manager->persist($collectiviteOzwillo2);
        //create collectivity without ozwillo and null siren
        $collectivite3 = self::aValidCollectivite('migration', 'Collectivity to migrate', null);
        $manager->persist($collectivite3);
        $manager->flush();
        $this->addReference(self::COLLECTIVITE_ONE_REFERENCE, $collectivite);
        $this->addReference(self::COLLECTIVITE_TWO_REFERENCE, $collectivite2);
        $this->addReference(self::COLLECTIVITE_THREE_REFERENCE, $collectivite3);
    }

    public static function aValidCollectivite($domain = 'sictiam', $name = 'a Sictiam Collectivité', $siren = '123456789')
    {
        $collectivite = new Collectivite();
        $collectivite
            ->setActive(true)
            ->setDomain($domain)
            ->setNom($name)
            ->setSiren($siren)
            ->setDeleteClasseurAfter(180);

        return $collectivite;
    }

    /**
     * @param $collectivite
     * @param string $instanceId
     * @param string $clientId
     * @param string $secret
     * @param string $dcId
     * @param string $serviceId
     * @return CollectiviteOzwillo
     */
    public static function aValidCollectiviteOzwillo(
        $collectivite,
        $instanceId = '2e771747-f906-4125-ba96-806553bc2ce2',
        $clientId = '2e771747-f906-4125-ba96-806553bc2ce2',
        $secret = 'ozwillo-client-secret',
        $dcId = '123456789',
        $serviceId = '49d86a4c-d814-417f-8308-ed2302034b87',
        $organisationId = 'bd8abc83-2f34-4e14-b17c-334eeeed952f'
    ) {
        $collectiviteOzwillo = new CollectiviteOzwillo();
        $collectiviteOzwillo
            ->setInstanceId($instanceId)
            ->setCollectivite($collectivite)
            ->setClientId($clientId)
            ->setClientSecret($secret)
            ->setDcId($dcId)
            ->setNotifiedToKernel(false)
            ->setServiceId($serviceId)
            ->setInstanceRegistrationUri('https://kernel.ozwillo-preprod.eu/apps/pending-instance/'.$instanceId)
            ->setDestructionSecret('MZQgq7jqKh3sPQ==')
            ->setStatusChangedSecret("9Ac+j4a3j/hhew==")
            ->setOrganizationId($organisationId);

        return $collectiviteOzwillo;
    }
}