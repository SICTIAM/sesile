<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


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
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation", type="datetime")
     */
    private $creation;

    /**
     * @var \Date
     *
     * @ORM\Column(name="validation", type="datetime")
     */
    private $validation;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sesile\ClasseurBundle\Entity\TypeClasseur")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    private $type;

    /**
     * @var integer
     * En cours = 1 finalisé = 2, refusé = 0, retiré = 3, retracté = 4
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="user", type="integer")
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="classeurs")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     *
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="classeurs_a_valider", cascade={"persist"})
     * @ORM\JoinTable(name="Classeur_valider")
     */
    private $validant;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordreCircuit", type="integer"))
     */
    private $ordreCircuit = 0;


    /**
     * @var int
     * 0 = privé, 1 = public, 2 = prive a partir de moi, 3 = service organisationnel (et le circuit)
     * @ORM\Column(name="visibilite", type="integer")
     *
     */
    private $visibilite;

    /**
     * @var string
     *
     * @ORM\Column(name="circuit", type="string", length=255, nullable=true)
     */
    private $circuit;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\DocumentBundle\Entity\Document", mappedBy="classeur")
     */
    protected $documents;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\ClasseurBundle\Entity\Action", mappedBy="classeur")
     */
    protected $actions;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="classeurs", cascade={"persist"})
     * @ORM\JoinTable(name="Classeur_visible")
     */
    private $visible;


    /**
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\EtapeClasseur", mappedBy="classeur", cascade={"persist"})
     */
    private $etapeClasseurs;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordreEtape", type="integer")
     */
    private $ordreEtape = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="EtapeDeposante", type="integer")
     */
    private $etapeDeposante;

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
    public function setValidantValue()
    {
        if(strpos($this->circuit, ",") === false) {
            $this->validant = $this->circuit;
        }
        else {
            $circuit = explode(",", $this->circuit);
            $this->validant = $circuit[0];
        }
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


        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->actions = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     *
     * @return int l'id du précédent validant dans le circuit. L'id du déposant si on revient au premier
     */

    public function getPrevValidant()
    {
        $circuit = explode(",", $this->getCircuit());
        $ordre =  $this->setOrdreMoins();
        return ($this->getOrdreCircuit() > 0) ? $circuit[$ordre] : $this->getUser();
    }


    /**
     *
     * @return int l'id du prochain validant dans le circuit. 0 si le circuit est terminé
     */
    public function getNextValidant(\Doctrine\ORM\EntityManager $em)
    {
        $circuit = explode(",", $this->getCircuit());
        $this->setOrdrePlus();
        return ($this->getOrdreCircuit() < count($circuit)) ? $circuit[$this->getOrdreCircuit()] : 0;
    }

    /**
     *
     * @return int l'id du validant courant dans le circuit.
     */
    public function getCurrentValidant(\Doctrine\ORM\EntityManager $em)
    {

        $circuit = explode(",", $this->getCircuit());
        $curr_validant = $circuit[$this->getOrdreCircuit()];
        return $curr_validant;
    }


    public function isAtLastValidant(){
        $ordreCircuit = $this->getOrdreCircuit() + 1;
        if ($ordreCircuit == count(explode(",", $this->getCircuit()))) {
            return true;
        } else {
            return false;
        }

    }

    public function valider(\Doctrine\ORM\EntityManager $em)
    {
        $this->setValidant($this->getNextValidant($em));
        // le classeur est arrivé à la derniere étape, on le finalise
        if ($this->getValidant() == 0) {
            $this->setStatus(2);
        } else {
            $this->setStatus(1);
        }
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
        $this->setValidant($this->getUser());
        $this->setStatus(0);
    }

    public function retracter($userid)
    {
        $this->setValidant($this->getPrevValidant());
        $this->setOrdreCircuit($this->setOrdreMoins());
        $this->setStatus(4);
    }

    public function supprimer()
    {
        $this->setValidant(0);
        $this->setStatus(3);
    }

    public function isValidable($userid) {
        return ($this->getValidant() == $userid);
    }

    public function isValidableByDelegates($delegates) {
        $arrayid = array();

        foreach($delegates as $d){
            $arrayid[] = $d->getId();
        }

        return (in_array($this->getValidant(), $arrayid));
    }

    public function isModifiable($userid)
    {
        return ((($this->getValidant() == $userid) || ($this->getUser() == $userid)) && $this->getStatus() != 3);
    }

    public function isModifiableByDelegates($delegates){

        $arrayid = array();
        foreach($delegates as $d){
            $arrayid[] = $d->getId();
        }

        return (( (in_array($this->getValidant(), $arrayid) ) || (in_array($this->getUser(), $arrayid))) && $this->getStatus() != 3);

    }

    /**
     * Return true or false
     * @param array
     *
     * La fonction renvoie true or false pour les boutons retractable et l affichage des dossiers retractable
     */
    public function isRetractableByDelegates($userid) {

        if (in_array($this->getPrevValidant(), $userid)  && $this->getValidant() != $userid && $this->getStatus() == 1 && $this->getOrdreCircuit() != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function isSupprimable($userid)
    {
        return ($this->getUser() == $userid && $this->getStatus() != 3);
    }

    public function isSupprimableByDelegates($delegates){
        $arrayid = array();

        foreach($delegates as $d){
            $arrayid[] = $d->getId();
        }

        return (in_array($this->getUser(), $arrayid) && $this->getStatus() != 3);

    }

//    public function isSignable(\Doctrine\ORM\EntityManager $em)
    public function isSignable()
    {

        if($this->getType()->getNom() == 'Helios' && $this->isAtLastValidant()){
            $docs=$this->getDocuments();
            foreach($docs as $doc){
                if($doc->getType()=='application/xml'){
                    return true;
                }
            }

        }
        return false;
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

    /**
     * Set ordreCircuit plus un
     */
    public function setOrdrePlus()
    {
        $this->ordreCircuit++;

        return $this;
    }

    /**
     * Set ordreCircuit moins un
     */
    public function setOrdreMoins()
    {
        $oCircuit = $this->getOrdreCircuit();

        $oCircuit = $oCircuit - 1;
        $oCircuit <= 0 ? $oCircuit = 0 : $oCircuit;

        return $oCircuit;
    }

    /**
     * Set ordreCircuit a zero
     */
    public function setOrdreZero()
    {
        $this->ordreCircuit = 0;

        return $this;
    }

    /**
     * Get private visibility after me
     */
    public function getPrivateAfterMeVisible() {
        $circuit = explode(",", $this->circuit);
        return array_slice($circuit, $this->getOrdreCircuit());
    }

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
     * Add actions
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $actions
     * @return Classeur
     */
    public function addAction(\Sesile\ClasseurBundle\Entity\Classeur $actions)
    {
        $this->actions[] = $actions;

        return $this;
    }

    /**
     * Remove actions
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $actions
     */
    public function removeAction(\Sesile\ClasseurBundle\Entity\Classeur $actions)
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
     * @param \Sesile\UserBundle\Entity\User $visible
     * @return Classeur
     */
    public function addVisible(\Sesile\UserBundle\Entity\User $visible)
    {
        $this->visible[] = $visible;

        return $this;
    }

    /**
     * Remove visible
     *
     * @param \Sesile\UserBundle\Entity\User $visible
     */
    public function removeVisible(\Sesile\UserBundle\Entity\User $visible)
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
     * @param \Sesile\UserBundle\Entity\EtapeClasseur $etapeClasseurs
     * @return Classeur
     */
    public function addEtapeClasseur(\Sesile\UserBundle\Entity\EtapeClasseur $etapeClasseurs)
    {
        $this->etapeClasseurs[] = $etapeClasseurs;
    
        return $this;
    }

    /**
     * Remove etapeClasseurs
     *
     * @param \Sesile\UserBundle\Entity\EtapeClasseur $etapeClasseurs
     */
    public function removeEtapeClasseur(\Sesile\UserBundle\Entity\EtapeClasseur $etapeClasseurs)
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
     * Set etapeDeposante
     *
     * @param string $etapeDeposante
     * @return Classeur
     */
    public function setEtapeDeposante($etapeDeposante)
    {
        $this->etapeDeposante = $etapeDeposante;
    
        return $this;
    }

    /**
     * Get etapeDeposante
     *
     * @return string 
     */
    public function getEtapeDeposante()
    {
        return $this->etapeDeposante;
    }

    /**
     * Add validant
     *
     * @param \Sesile\UserBundle\Entity\User $validant
     * @return Classeur
     */
    public function addValidant(\Sesile\UserBundle\Entity\User $validant)
    {
        $this->validant[] = $validant;
    
        return $this;
    }

    /**
     * Remove validant
     *
     * @param \Sesile\UserBundle\Entity\User $validant
     */
    public function removeValidant(\Sesile\UserBundle\Entity\User $validant)
    {
        $this->validant->removeElement($validant);
    }

}