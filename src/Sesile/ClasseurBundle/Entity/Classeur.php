<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;

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
    private $description;

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
     * @ORM\OneToMany(targetEntity="Sesile\DocumentBundle\Entity\Document", mappedBy="classeur")
     */
    protected $documents;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\ClasseurBundle\Entity\Action", mappedBy="classeur", cascade={"remove"})
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
     *
     * @Exclude()
     */
    private $copy;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\EtapeClasseur", mappedBy="classeur", cascade={"remove"})
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
     * @var integer
     *
     * @ORM\Column(name="EtapeDeposante", type="integer")
     * @Groups("classeur")
     */
    private $etapeDeposante;

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
    private $typeSignable = array(
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'application/xml',
            'text/plain'
    );

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
//        $this->circuit = $circuit;

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

    public function getLastValidant() {
        $circuit = explode(",", $this->getCircuit());
        $idLastUser = end($circuit);

        return $idLastUser;
    }


    public function isAtLastValidant(){
        $ordreCircuit = $this->getOrdreEtape();
        if($this->getStatus() != 0 && $this->getStatus() != 4)
        {
            $ordreCircuit++;
        }
        $nbEtapes = count($this->getEtapeClasseurs());
        //var_dump($ordreCircuit,$nbEtapes,"<br>");
        //var_dump("Etape status : " . $this->getStatus() . "<br>");
        if ($ordreCircuit == $nbEtapes){
            return true;
        }
        else {
            return false;
        }
        /*$ordreCircuit = $this->getOrdreCircuit() + 1;
        if ($ordreCircuit == count(explode(",", $this->getCircuit()))) {
            return true;
        } else {
            return false;
        }*/

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
//        $this->setValidant($this->getUser());
        $this->setCircuitZero('');
        $this->setOrdreValidant('');
        $this->setStatus(0);
    }

    public function retracter()
    {

        if(count(explode(',',$this->getOrdreValidant())) == 1)
        {
            $this->setOrdreZero();
//        $this->setValidant($this->getPrevValidant());
//        $this->setValidant($this->getUser());
            $this->setCircuitZero('');
            $this->setOrdreValidant('');
            $this->setStatus(4);
        }
        else{

            $this->setCircuitZero( substr($this->getCircuit(), 0, strrpos($this->getCircuit(), ',')) );
            $this->setOrdreValidant( substr($this->getOrdreValidant(), 0, strrpos($this->getOrdreValidant(), ',')) );
            $this->setOrdreEtape($this->setOrdreMoins());
            $this->setStatus(4);
        }

    }

    public function supprimer()
    {
        $this->setOrdreValidant('');
        $this->setStatus(3);
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

    public function isModifiableByDelegates($delegates, $validants){

        $arrayid = array();
        foreach($delegates as $d){
            $arrayid[] = $d->getId();
        }

        return (( (in_array($validants, $arrayid) ) || (in_array($this->getUser(), $arrayid))) && $this->getStatus() != 3);

    }

    /**
     * La fonction renvoie true or false pour les boutons retractable et l affichage des dossiers retractable
     *
     * @param $userid           : id du user courant + delegant
     * @param $validants        : id des validants courant du classeur
     * @param $prevValidants    : id des validants de l etape precedente du classeur
     * @return bool             : true or false
     */
    public function isRetractableByDelegates($userid, $validants, $prevValidants) {
      // var_dump('userId : ', $userid, 'prevValidants : ', $prevValidants, $this->getId(), '<br>');
//var_dump($prevValidants);

        if ( in_array($prevValidants, $userid)
             && !in_array($userid[0], $validants)
//            && !array_diff($userid, $validants)
            && $this->getStatus() == 1
        /*    && $this->getOrdreEtape() != 0 */
        ) {
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

    public function isSignable()
    {
        if($this->getType()->getId() == 2){
        //if($this->getType()->getId() == 2 && $this->isAtLastValidant()){
            $docs=$this->getDocuments();
            foreach($docs as $doc){
                if($doc->getType() == 'application/xml'){
                    return true;
                }
            }

        }
        return false;
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

    /**
     * Function pour tester si le classeur est signable
     * @return bool
     */
    public function isSignableAndLastValidant() {
        if($this->isAtLastValidant()){

            $docs = $this->getDocuments();

            // Si au moins un document est signable alors le classeur peut etre signé
            foreach($docs as $doc){
                if(in_array($doc->getType(), $this->typeSignable)){
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
     * @param \Sesile\UserBundle\Entity\User $copy
     *
     * @return Classeur
     */
    public function addCopy(\Sesile\UserBundle\Entity\User $copy)
    {
        $this->copy[] = $copy;

        return $this;
    }

    /**
     * Remove copy
     *
     * @param \Sesile\UserBundle\Entity\User $copy
     */
    public function removeCopy(\Sesile\UserBundle\Entity\User $copy)
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
}
