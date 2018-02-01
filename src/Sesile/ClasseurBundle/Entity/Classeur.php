<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use Sesile\UserBundle\Entity\EtapeClasseur;
use Sesile\UserBundle\Entity\Groupe;
use Sesile\UserBundle\Entity\User;

/**
 * Classeur
 *
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Sesile\ClasseurBundle\Entity\ClasseursUsersRepository")
 * @ORM\Entity(repositoryClass="Sesile\ClasseurBundle\Entity\ClasseurRepository")
 */
class Classeur {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"classeurById", "listClasseur"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     * @Groups({"classeurById", "listClasseur"})
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Groups({"listClasseur", "classeurById"})
     *
     */
    private $description = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation", type="datetime")
     * @Groups({"classeurById","listClasseur"})
     */
    private $creation;

    /**
     * @var \Date
     *
     * @ORM\Column(name="validation", type="datetime")
     * @Groups({"classeurById", "listClasseur"})
     */
    private $validation;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sesile\ClasseurBundle\Entity\TypeClasseur", fetch="EAGER")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     * @ORM\OrderBy({"nom": "ASC"})
     * @Groups({"classeurById"})
     *
     */
    private $type;

    /**
     * @var integer
     * En cours = 1 finalisé = 2, refusé = 0, retiré = 3, retracté = 4
     * @ORM\Column(name="status", type="integer")
     * @Groups({"classeurById", "listClasseur"})
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     * @Groups({"classeurById", "listClasseur"})
     */
    private $user;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\Groupe")
     * @ORM\JoinColumn(name="circuit_id", referencedColumnName="id")
     */
    private $circuit_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordreCircuit", type="integer"))
     * @Groups({"classeurById"})
     */
    private $ordreCircuit = 0;

    /**
     * @var int
     * 0 = privé, 1 = public, 2 = prive a partir de moi, 3 = service organisationnel (et le circuit)
     * @ORM\Column(name="visibilite", type="integer")
     * @Groups({"classeur"})
     *
     */
    private $visibilite;

    /**
     * @var string
     *
     * @ORM\Column(name="circuit", type="string", length=255, nullable=true)
     * @Groups({"listClasseur", "classeurById"})
     */
    private $circuit;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\DocumentBundle\Entity\Document", mappedBy="classeur", cascade={"remove"})
     * @Groups({"classeurById"})
     */
    protected $documents;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\ClasseurBundle\Entity\Action", mappedBy="classeur", cascade={"all"})
     * @ORM\OrderBy({"date" = "DESC"})
     * @Groups({"classeurById"})
     */
    protected $actions;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="classeurs", cascade={"persist"})
     * @ORM\JoinTable(name="Classeur_visible")
     *
     * @Exclude()
     */
    private $visible;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="classeursCopy", cascade={"persist"})
     * @ORM\JoinTable(name="Classeur_copy")
     * @Groups({"classeurById"})
     */
    private $copy;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\EtapeClasseur", mappedBy="classeur", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="etapeClasseurs", referencedColumnName="id",nullable=true)
     * @Groups({"classeurById", "listClasseur"})
     *
     */
    private $etapeClasseurs;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordreEtape", type="integer")
     * @Groups("classeur")
     */
    private $ordreEtape = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="ordreValidant", type="string", length=255, nullable=true)
     * @Groups("classeur")
     */
    private $ordreValidant;

    /**
     * @var array
     *
     * @Exclude()
     *
     * Liste des types signables
     */
    public $typeSignable = array(
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'application/xml',
            'text/xml',
            'text/plain'
    );

    /**
     * @var boolean
     * @Groups({"listClasseur", "classeurById"})
     */
    private $signableAndLastValidant = false;

    /**
     * @var boolean
     * @Groups({"listClasseur", "classeurById"})
     */
    private $validable = false;

    /**
     * @var boolean
     * @Groups({"listClasseur", "classeurById"})
     */
    private $retractable = false;

    /**
     * @var boolean
     * @Groups({"listClasseur", "classeurById"})
     */
    private $refusable = false;

    /**
     * @var boolean
     * @Groups({"listClasseur", "classeurById"})
     */
    private $removable = false;

    /**
     * @var boolean
     * @Groups({"listClasseur", "classeurById"})
     */
    private $deletable = false;

    /**
     * @var boolean
     * @Groups({"listClasseur", "classeurById"})
     */
    private $etapeDeposante = false;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Classeur
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Classeur
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set creation
     *
     * @param \DateTime $creation
     * @return Classeur
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;

        return $this;
    }


    /**
     * Get creation
     *
     * @return \DateTime
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreationValue()
    {
        $this->creation = new \DateTime();
    }

    /**
     * Set circuit
     *
     * @param array $circuit
     * @return Classeur
     */
    public function setCircuit($circuit)
    {

        if ($this->getCircuit() != null) {
            $this->circuit = $this->getCircuit() . ',' . $circuit;
        }
        else {
            $this->circuit = $circuit;
        }

        return $this;
    }

    /**
     * Set circuit
     *
     * @param array $circuit
     * @return Classeur
     */
    public function setCircuitZero($circuit)
    {
        $this->circuit = $circuit;

        return $this;
    }

    /**
     * Get circuit
     *
     * @return array
     */
    public function getCircuit()
    {
        return $this->circuit;
    }


    /**
     * Set user
     *
     * @param integer $user
     * @return Classeur
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return integer
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Classeur
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @ORM\PrePersist
     */
    public function setStatusValue()
    {
        $this->status = 1;
    }

    /**
     * Set visibilite
     *
     * @param integer $visibilite
     * @return Classeur
     */
    public function setVisibilite($visibilite) {
        $this->visibilite = $visibilite;
        return $this;
    }

    /**
     * Get visibilite
     *
     * @return integer
     */
    public function getVisibilite() {
        return $this->visibilite;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actions = new ArrayCollection();
        $this->etapeClasseurs = new ArrayCollection();
    }

    public function isAtLastValidant(){

        if(
            $this->getStatus() != 2
            && $this->getStatus() != 3
            && $this->getEtapeValidante()
            && !$this->getNextEtapeValidante()
        ) {
            return true;
        } else {
            return false;
        }

    }

    public function getEtapeValidante() {
        $etapeClasseurs = $this->getEtapeClasseurs();
        foreach ($etapeClasseurs as $etapeClasseur) {
            if ($etapeClasseur->getEtapeValidante()) {
                return $etapeClasseur;
            }
        }
        return false;
    }

    public function countEtapeValide() {
        $count = 0;
        $etapeClasseurs = $this->getEtapeClasseurs();
        foreach ($etapeClasseurs as $etapeClasseur) {
            if ($etapeClasseur->getEtapeValide()) {
                $count++;
            }
        }
        return $count;
    }

    public function soumettre()
    {
        $circuit = explode(",", $this->getCircuit());
        $this->setOrdreZero();
        return $circuit[0];
    }


    public function refuser()
    {
        $this->setOrdreZero();
//        $this->setValidant($this->getPrevValidant());
//        $this->setValidant($this->getUser());
        $this->setCircuitZero('');
        $this->setOrdreValidant('');
        $this->setStatus(0);
    }

    public function isValidable($userid,  $validants) {
        return (in_array($userid, $validants));
    }

    public function isValidableByDelegates($delegates, $validants) {

        foreach($validants as $validant){
            if (in_array($validant, $delegates)) {
                return true;
            }
        }
        return false;
    }

    public function isModifiable($userid, $validants)
    {
        return (((in_array($validants, $userid)) || ($this->getUser() == $userid)) && $this->getStatus() != 3);
    }

    public function getEtapeByOrdre (int $ordre) {
        foreach ($this->getEtapeClasseurs() as $etapeClasseur) {
            if($etapeClasseur->getOrdre() == $ordre) {
                return $etapeClasseur;
            }
        }
        return false;
    }

    public function getPrevEtapeValidante() {
        $etapeValidante = $this->getEtapeValidante();
        $ordre = $etapeValidante->getOrdre() - 1;

        return $this->getEtapeByOrdre($ordre);

    }

    public function getNextEtapeValidante() {
        $etapeValidante = $this->getEtapeValidante();
        $ordre = $etapeValidante->getOrdre() + 1;
        return $this->getEtapeByOrdre($ordre);
    }

    public function setRetractable(bool $retractable) {

        $this->retractable = $retractable;

        return $this;
    }

    public function getRetractable() {
        return $this->retractable;
    }

    public function setRefusable(bool $refusable) {
        $this->refusable = $refusable;
        return $this;
    }

    public function getRefusable() {
        return $this->refusable;
    }

    /**
     * Function pour tester si le classeur est signable
     * @return bool
     */
    public function isSignablePDF() {
        $docs = $this->getDocuments();

        // Si au moins un document est signable alors le classeur peut etre signé
        foreach($docs as $doc){
            if(in_array($doc->getType(), $this->typeSignable)){
                return true;
            }
        }
        return false;
    }


    public function setSignableAndLastValidant($signableAndLastValidant) {
        $this->signableAndLastValidant = $signableAndLastValidant;
        return $this;
    }

    public function setValidable($validable) {
        $this->validable = $validable;
        return $this;
    }

    public function getValidable() {
        return $this->validable;
    }

    public function setRemovable($removable) {
        $this->removable = $removable;
        return $this;
    }

    public function getRemovable() {
        return $this->removable;
    }

    public function setDeletable($deletable) {
        $this->deletable = $deletable;
        return $this;
    }

    public function getDeletable() {
        return $this->deletable;
    }

    public function setEtapeDeposante($etapeDeposante) {
        $this->etapeDeposante = $etapeDeposante;
        return $this;
    }

    public function getEtapeDeposante() {
        return $this->etapeDeposante;
    }

    public function getXmlDocuments()
    {
        $docs = $this->getDocuments();
        $xmldocuments = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($docs as $doc) {
            if ($doc->getType() == 'application/xml') {
                $xmldocuments->add($doc);
            }
        }
        return $xmldocuments;
    }

    public function getPdfDocuments()
    {
        $docs = $this->getDocuments();
        $pdfdocuments = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($docs as $doc) {
            if ($doc->getType() == 'application/pdf') {
                $pdfdocuments->add($doc);
            }
        }
        return $pdfdocuments;
    }

    /**
     * Set ordreCircuit plus un
     */
    public function setOrdrePlus()
    {
        $this->ordreEtape++;

        return $this;
    }

    /**
     * Set ordreCircuit moins un
     */
    public function setOrdreMoins()
    {
        $oCircuit = $this->getOrdreEtape();

        $oCircuit = $oCircuit - 1;
        $oCircuit <= 0 ? $oCircuit = 0 : $oCircuit;

        return $oCircuit;
    }

    /**
     * Set ordreCircuit a zero
     */
    public function setOrdreZero()
    {
        $this->ordreEtape = 0;

        return $this;
    }

    /**
     * Get private visibility after me
     */
    /*public function getPrivateAfterMeVisible() {
        $circuit = explode(",", $this->circuit);
        return array_slice($circuit, $this->getOrdreCircuit());
    }*/

    /**
     * Add documents
     *
     * @param \Sesile\DocumentBundle\Entity\Document $documents
     * @return Classeur
     */
    public function addDocument(\Sesile\DocumentBundle\Entity\Document $documents)
    {
        $this->documents[] = $documents;

        return $this;
    }

    /**
     * Remove documents
     *
     * @param \Sesile\DocumentBundle\Entity\Document $documents
     */
    public function removeDocument(\Sesile\DocumentBundle\Entity\Document $documents)
    {
        $this->documents->removeElement($documents);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Set validation
     *
     * @param \DateTime $validation
     * @return Classeur
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Get validation
     *
     * @return \DateTime
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Add action
     *
     * @param \Sesile\ClasseurBundle\Entity\Action $action
     *
     * @return Classeur
     */
    public function addAction(Action $action)
    {
        $this->actions[] = $action;
        $action->setClasseur($this);

        return $this;
    }

    /**
     * Remove actions
     *
     * @param \Sesile\ClasseurBundle\Entity\Action $actions
     */
    public function removeAction(Action $actions)
    {
        $this->actions->removeElement($actions);
    }

    /**
     * Get actions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Set type
     *
     * @param \Sesile\ClasseurBundle\Entity\TypeClasseur $type
     * @return Classeur
     */
    public function setType(\Sesile\ClasseurBundle\Entity\TypeClasseur $type = null)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return \Sesile\ClasseurBundle\Entity\TypeClasseur 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add visible
     *
     * @param User $visible
     * @return Classeur
     */
    public function addVisible(User $visible)
    {
        $this->visible[] = $visible;

        return $this;
    }

    /**
     * Remove visible
     *
     * @param User $visible
     */
    public function removeVisible(User $visible)
    {
        $this->visible->removeElement($visible);
    }

    /**
     * Get visible
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     * @return Classeur
     */
    public function setOrdreCircuit($ordre)
    {
        $this->ordreCircuit = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdreCircuit()
    {
        return $this->ordreCircuit;
    }


    /**
     * Add etapeClasseurs
     *
     * @param EtapeClasseur $etapeClasseur
     * @return Classeur
     */
    public function addEtapeClasseur(EtapeClasseur $etapeClasseur)
    {
        //$this->etapeClasseurs[] = $etapeClasseur;
        $this->etapeClasseurs->add($etapeClasseur);
        $etapeClasseur->setClasseur($this);
    
        return $this;
    }

    /**
     * Remove etapeClasseurs
     *
     * @param EtapeClasseur $etapeClasseurs
     */
    public function removeEtapeClasseur(EtapeClasseur $etapeClasseurs)
    {
        $this->etapeClasseurs->removeElement($etapeClasseurs);
    }

    /**
     * Get etapeClasseurs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEtapeClasseurs()
    {
        return $this->etapeClasseurs;
    }

    /**
     * Set ordreEtape
     *
     * @param string $ordreEtape
     * @return Classeur
     */
    public function setOrdreEtape($ordreEtape)
    {
        $this->ordreEtape = $ordreEtape;
    
        return $this;
    }

    /**
     * Get ordreEtape
     *
     * @return string 
     */
    public function getOrdreEtape()
    {
        return $this->ordreEtape;
    }

    /**
     * Précédente étape
     * @return int
     */
    public function getPrevOrdreEtape() {
        $prevOrdreEtape = intval($this->getOrdreEtape());
        if ($prevOrdreEtape != 0) {
            $prevOrdreEtape--;
        }
        else {
            $prevOrdreEtape = 0;
        }
        return $prevOrdreEtape;
    }

    /**
     * Etape suivante
     * @param $maxEtapes
     * @return bool|int
     */
    public function getNextOrdreEtape($maxEtapes) {
        $nextOrdreEtape = intval($this->getOrdreEtape());
        if ($nextOrdreEtape << $maxEtapes) {
            $nextOrdreEtape++;
        } else {
            return false;
        }
        return $nextOrdreEtape;
    }

    /**
     * Set ordreValidant
     *
     * @param string $ordreValidant
     * @return Classeur
     */
    public function setOrdreValidant($ordreValidant)
    {
        $this->ordreValidant = $ordreValidant;
    
        return $this;
    }

    /**
     * Get ordreValidant
     *
     * @return string 
     */
    public function getOrdreValidant()
    {
        return $this->ordreValidant;
    }


    /**
     * Get short nom
     *
     * @return string
     */
    public function getShortNom()
    {
        // Le nombre maximum de caractere a afficher
        $nbMaxCharacters = 100;
        $findme = " ";
        $nom = $this->nom;

        // Test si le nom est trop long, on affiche 100 caracteres
        if (strlen($nom) > $nbMaxCharacters && strpos($nom, $findme)) {
            return substr($nom, 0, $nbMaxCharacters) . '...';
        }
        // Test si le nom est trop long qu il n y a pas d espace on affiche que 50 caractères
        elseif (strlen($nom) > $nbMaxCharacters) {
            return substr($nom, 0, $nbMaxCharacters/2) . '...';
        }
        else {
            return $nom;
        }
    }


    /**
     * Add copy
     *
     * @param User $copy
     *
     * @return Classeur
     */
    public function addCopy(User $copy)
    {
        $this->copy[] = $copy;

        return $this;
    }

    /**
     * Remove copy
     *
     * @param User $copy
     */
    public function removeCopy(User $copy)
    {
        $this->copy->removeElement($copy);
    }

    /**
     * Get copy
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCopy()
    {
        return $this->copy;
    }

    /**
     * Set circuitId
     *
     * @param Groupe $circuitId
     *
     * @return Classeur
     */
    public function setCircuitId(Groupe $circuitId = null)
    {
        $this->circuit_id = $circuitId;

        return $this;
    }

    /**
     * Get circuitId
     *
     * @return Groupe
     */
    public function getCircuitId()
    {
        return $this->circuit_id;
    }
}
