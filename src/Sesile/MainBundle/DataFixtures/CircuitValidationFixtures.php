<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\ClasseurBundle\Entity\TypeClasseur;
use Sesile\UserBundle\Entity\EtapeGroupe;
use Sesile\UserBundle\Entity\Groupe;

/**
 * Class CircuitValidationFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class CircuitValidationFixtures extends Fixture implements DependentFixtureInterface
{
    const CIRCUIT_VALIDATION_REFERENCE = 'circuit-validation';
    const CIRCUIT_VALIDATION_REFERENCE_TWO = 'circuit-validation-two';

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $etapeGroup = self::aValidEtapeGroupe(1, [$this->getReference('user-one')], [$this->getReference('user-pack-one')]);
        $etapeGroup2 = self::aValidEtapeGroupe(2, [$this->getReference('user-one')]);
        $group = new Groupe();
        $group
            ->setNom('Circuit de validation')
            ->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_ONE_REFERENCE))
            ->setCreation(new \DateTime());
        $type = $this->getReference(TypeClasseurFixtures::CLASSEUR_TYPE_ONE_REFERENCE);
        $group->addType($type);
        $group->addEtapeGroupe($etapeGroup);
        $group->addEtapeGroupe($etapeGroup2);

        $manager->persist($type);
        $manager->persist($group);
        $etapeGroup->setGroupe($group);
        $manager->persist($etapeGroup);
        $etapeGroup2->setGroupe($group);
        $manager->persist($etapeGroup2);
        /**
         * second
         */
        $etapeGroup3 = new EtapeGroupe();
        $etapeGroup3
            ->setOrdre(1)
            ->addUser($this->getReference('user-two'));
        $etapeGroup4 = self::aValidEtapeGroupe(2, [$this->getReference('user-two')]);

        $group2 = new Groupe();
        $group2
            ->setNom('Circuit de validation two')
            ->setCollectivite($this->getReference(CollectiviteFixtures::COLLECTIVITE_TWO_REFERENCE))
            ->setCreation(new \DateTime());
        $type = $this->getReference(TypeClasseurFixtures::CLASSEUR_TYPE_ONE_REFERENCE);
        $group2->addType($type);
        $group2->addEtapeGroupe($etapeGroup3);
        $group2->addEtapeGroupe($etapeGroup4);

        $manager->persist($group2);
        $etapeGroup3->setGroupe($group2);
        $manager->persist($etapeGroup3);
        $etapeGroup4->setGroupe($group2);
        $manager->persist($etapeGroup4);
        $manager->flush();
        $this->addReference(self::CIRCUIT_VALIDATION_REFERENCE, $group);
        $this->addReference(self::CIRCUIT_VALIDATION_REFERENCE_TWO, $group2);
    }


    public static function aValidEtapeGroupe($order = 1, $users = [], $userPacks = [])
    {
        $etapeGroup = new EtapeGroupe;
        $etapeGroup->setOrdre($order);
        foreach ($users as $user) {
            $etapeGroup->addUser($user);
        }
        foreach ($userPacks as $userPack) {
            $etapeGroup->addUserPack($userPack);
        }

        return $etapeGroup;

    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    function getDependencies()
    {
        return array(
            TypeClasseurFixtures::class,
            UserPackFixtures::class,
        );
    }
}