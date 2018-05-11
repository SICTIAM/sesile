<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\UserBundle\Entity\EtapeClasseur;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ClasseurFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class ClasseurFixtures extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    const CLASSEURS_REFERENCE = 'classeur-one';
    const CLASSEURS_REFERENCE_TWO = 'classeur-two';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
//        $superUser = $this->getReference('user-super');
        $userOne = $this->getReference('user-one');
        $userPackOne = $this->getReference('user-pack-one');

        $etapeClasseur = self::aValidEtapeClasseur($userOne, $userPackOne, 1, true);
        $classeur = self::aValidClasseur(
            'Fixture Classeur',
            'Fixture Classeur Description',
            $userOne,
            [$userOne],
            [$this->getReference('user-two')],
            $this->getReference('circuit-validation'),
            $this->getReference('collectivite-one'),
            [$etapeClasseur],
            $this->getReference('classeur-type-one')
        );
        $manager->persist($etapeClasseur);
        $manager->persist($classeur);
        $manager->flush();
        $this->addReference(self::CLASSEURS_REFERENCE, $classeur);

        $etapeClasseur1 = self::aValidEtapeClasseur($userOne, $userPackOne, 1);
        $etapeClasseur2 = self::aValidEtapeClasseur($userOne, $userPackOne, 2, true);
        //new classeur avec une nouvelle collectivite
        $classeur2 = self::aValidClasseur(
            'Another Classeur',
            'Another Classeur Description',
            $userOne,
            [$userOne],
            [$this->getReference('user-two')],
            $this->getReference('circuit-validation'),
            $this->getReference('collectivite-two'),
            [$etapeClasseur1, $etapeClasseur2],
            $this->getReference('classeur-type-one')
        );
        $manager->persist($classeur2);
        $manager->flush();

        $this->addReference(self::CLASSEURS_REFERENCE_TWO, $classeur);

        $userTwo = $this->getReference('user-two');
        /**
         * add classeur for user two
         */
        $classeur3 = self::aValidClasseur(
            'User Two Classeur',
            'User Two Classeur Description',
            $userTwo,
            [$userTwo],
            [],
            $this->getReference('circuit-validation'),
            $this->getReference('collectivite-one'),
            [$etapeClasseur1, $etapeClasseur2],
            $this->getReference('classeur-type-one')
        );
        $manager->persist($classeur3);
        $manager->flush();

    }

    /**
     * @param $name
     * @param $description
     * @param $user
     * @param array $visibleToUsers
     * @param array $userCopy
     * @param $circuitValidation
     * @param $collectivite
     * @param array $etapesClasseur
     * @param $classeurType
     * @param int $status
     *
     * @return Classeur
     */
    public static function aValidClasseur(
        $name,
        $description,
        $user,
        array $visibleToUsers = [],
        array $userCopy = [],
        $circuitValidation,
        $collectivite,
        array $etapesClasseur = [],
        $classeurType,
        $status = 0
    ) {
        $classeur = new Classeur();
        $classeur
            ->setUser($user)
            ->setCircuitId($circuitValidation)
            ->setCollectivite($collectivite)
            ->setDescription($description)
            ->setNom($name)
            ->setType($classeurType)
            ->setValidation(new \DateTime('2018-06-03 11:36'))
            ->setStatus($status)
            ->setVisibilite(1);
        foreach ($visibleToUsers as $user) {
            $classeur
                ->addVisible($user);
        }
        foreach ($etapesClasseur as $etape) {
            $classeur
                ->addEtapeClasseur($etape);
        }
        foreach ($userCopy as $copy) {
            $classeur
                ->addCopy($copy);
        }

        return $classeur;
    }

    /**
     * @param $user
     * @param $userPack
     * @param int $ordre
     * @param bool $validante
     *
     * @return EtapeClasseur
     */
    public static function aValidEtapeClasseur($user, $userPack, $ordre = 1, $validante = false, $valide = false)
    {
        $etapeClasseur = new EtapeClasseur();
        $etapeClasseur
            ->addUser($user)
            ->setEtapeValidante($validante)
            ->setEtapeValide($valide)
            ->setOrdre(1)
            ->setDate(new \DateTime())
            ->addUserPack($userPack);

        return $etapeClasseur;
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
            CollectiviteFixtures::class,
            UserFixtures::class,
            CircuitValidationFixtures::class,
            TypeClasseurFixtures::class,
            UserPackFixtures::class,
        );
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}