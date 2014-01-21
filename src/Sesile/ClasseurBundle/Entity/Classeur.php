<?php

namespace Sesile\ClasseurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * Classeur
 *
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Sesile\ClasseurBundle\Entity\ClasseursUsersRepository")
 */
class Classeur
{
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
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
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
     * @var int
     *
     * @ORM\Column(name="validant", type="integer")
     *
     * @ORM\ManyToOne(targetEntity="Sesile\UserBundle\Entity\User", inversedBy="classeurs_a_valider")
     * @ORM\JoinColumn(name="validant", referencedColumnName="id")
     *
     */
    private $validant;

    /**
     * @var int
     * -1 = privé, 0 = public, id user = à partir de
     * @ORM\Column(name="visibilite", type="integer")
     *
     */
    private $visibilite;

    /**
     * @var string
     *
     * @ORM\Column(name="circuit", type="string", length=255)
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
     * Set type
     *
     * @param string $type
     * @return Classeur
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * Set validant
     *
     * @param integer $validant
     * @return Classeur
     */
    public function setValidant($validant)
    {
        $this->validant = $validant;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setValidantValue()
    {
        $this->validant = $this->circuit[0];
    }

    /**
     * @ORM\PrePersist
     */
    public function setStatusValue()
    {
        $this->status = 1;
    }

    /**
     * Get validant
     *
     * @return integer
     */
    public function getValidant()
    {
        return $this->validant;
    }

    /**
     * Set visibilite
     *
     * @param integer $visibilite
     * @return Classeur
     */
    public function setVisibilite($visibilite)
    {
        $this->visibilite = $visibilite;

        return $this;
    }

    /**
     * Get visibilite
     *
     * @return integer
     */
    public function getVisibilite()
    {
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
    private function getPrevValidant()
    {
        $circuit = explode(",", $this->getCircuit());
        $curr_validant = array_search($this->validant, $circuit);
        $prev_validant = $curr_validant - 1;
        return ($prev_validant >= 0) ? $circuit[$prev_validant] : $this->getUser();
    }

    /**
     *
     * @return int l'id du prochain validant dans le circuit. 0 si le circuit est terminé
     */
    private function getNextValidant(\Doctrine\ORM\EntityManager $em)
    {
        //$d = $em->getRepository("SesileDelegationsBundle:Delegations");
        //$delegation = $d->getClasseursRetractables($userid);

        $circuit = explode(",", $this->getCircuit());
        $curr_validant = array_search($this->validant, $circuit);
        $next_validant = $curr_validant + 1;
        return ($next_validant < count($circuit)) ? $circuit[$next_validant] : 0;
    }

    public function isAtLastValidant(\Doctrine\ORM\EntityManager $em){
        return ($this->getNextValidant($em)==0);
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

    public function refuser()
    {
        $this->$this->setValidant($this->getPrevValidant());
        $this->setStatus(0);
    }

    public function retracter($userid)
    {
        $this->setValidant($userid);
        $this->setStatus(4);
    }

    public function supprimer()
    {
        $this->setValidant(0);
        $this->setStatus(3);
    }


    public function isValidable($userid)
    {
        return ($this->getValidant() == $userid);
    }

    public function isModifiable($userid)
    {
        return ((($this->getValidant() == $userid) || ($this->getUser() == $userid)) && $this->getStatus() != 3);
    }

    /**
     * FIXME : désolé j'ai pas trouvé rapidement de méthode moins crade que passer l'entitymanager donc tant pis... :(
     */
    public function isRetractable($userid, \Doctrine\ORM\EntityManager $em)
    {
        $c = $em->getRepository("SesileClasseurBundle:ClasseursUsers");
        $classeurs = $c->getClasseursRetractables($userid);

        foreach ($classeurs as $classeur) {
            if ($classeur->getId() == $this->getId()) {
                return true;
            }
        }
        return false;
    }

    public function isSupprimable($userid)
    {
        return ($this->getUser() == $userid && $this->getStatus() != 3);
    }

    public function isSignable(\Doctrine\ORM\EntityManager $em)
    {

        if($this->getType()=='elpez' || $this->isAtLastValidant($em)){
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
}