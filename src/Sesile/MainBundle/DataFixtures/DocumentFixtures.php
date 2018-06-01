<?php


namespace Sesile\MainBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\DocumentBundle\Entity\Document;
use Sesile\UserBundle\Entity\EtapeClasseur;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class DocumentFixtures
 * @package Sesile\MainBundle\DataFixtures
 */
class DocumentFixtures extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    const DOCUMENT_REFERENCE_ONE = 'document-one';
    const DOCUMENT_REFERENCE_XML = 'document-xml';

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
        $classeur = $this->getReference('classeur-one');
        $document = self::aValidDocument('doc', __DIR__.'/testFile.txt', $classeur);
        $manager->persist($document);
        $this->addReference(self::DOCUMENT_REFERENCE_ONE, $document);
        $documentXml = self::aValidDocument('helios', __DIR__.'/helios.xml', $classeur);
        $manager->persist($documentXml);
        $this->addReference(self::DOCUMENT_REFERENCE_XML, $documentXml);
        $manager->flush();
    }

    /**
     * @param string   $name
     * @param string   $filePath
     * @param Classeur $classeur
     *
     * @return Document
     */
    public static function aValidDocument($name = 'file', $filePath, Classeur $classeur)
    {
        $file = new File($filePath, true);
        $document = new Document();
        $document->setName($name);
        $document->setRepourl($file->getBasename());
        $document->setType($file->getMimeType());
        $document->setSigned(false);
        $document->setClasseur($classeur);

        return $document;
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
            ClasseurFixtures::class,
        );
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}