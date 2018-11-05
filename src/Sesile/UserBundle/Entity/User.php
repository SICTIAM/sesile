<?php

namespace Sesile\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use FOS\UserBundle\Model\UserInterface;
use JMS\Serializer\Annotation as Serializer;
use Sesile\MainBundle\Entity\Collectivite;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\Serializer\Annotation\Exclude;
use Sesile\ClasseurBundle\Entity\Classeur;
use Sesile\UserBundle\Entity\Note;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Sesile\UserBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class User extends BaseUser {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"listClasseur",
     *                      "listEtapeClasseur",
     *                      "listCircuitByCollectivite",
     *                      "listCircuitByUser",
     *                      "getByIdCircuit",
     *                      "currentUser",
     *                      "userPack",
     *                      "searchUser",
     *                      "userRole",
     *                      "listUsers",
     *                      "classeurById",
     *                      "UserId"})
     *     })
     *
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Nom", type="string", length=255, nullable=true)
     * @Serializer\Groups({"classeurById",
     *                      "listClasseur",
     *                      "listEtapeClasseur",
     *                      "listCircuitByCollectivite",
     *                      "listCircuitByUser",
     *                      "getByIdCircuit",
     *                      "currentUser",
     *                      "userPack",
     *                      "searchUser",
     *                      "listUsers",
     *                      "UserId"
     *      })
     */
    protected $Nom;

    /**
     * @var string
     *
     * @ORM\Column(name="Prenom", type="string", length=255, nullable=true)
     * @Serializer\Groups({"classeurById",
     *                      "listClasseur",
     *                      "listEtapeClasseur",
     *                      "listCircuitByCollectivite",
     *                      "listCircuitByUser",
     *                      "getByIdCircuit",
     *                      "currentUser",
     *                      "userPack",
     *                      "searchUser",
     *                      "listUsers",
     *                      "UserId"
     *      })
     *
     */
    protected $Prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listClasseur",
     *                      "currentUser",
     *                      "UserId"})
     */
    protected $path;

    /**
     * @var string
     *
     * @ORM\Column(name="ozwilloId", type="string", length=255, nullable=true)
     */
    protected $ozwilloId;

    /**
     * @Assert\Image(
     *
     *      mimeTypesMessage = "Ce fichier n'est pas une image",
     *      maxSize = "5M",
     *      maxSizeMessage = "Too big."
     *      )
     */
    protected $file;


    /**
     * @var string
     *
     * @ORM\Column(name="pathSignature", type="string", length=255, nullable=true)
     * @Serializer\Groups({"classeurById", "currentUser", "UserId", "currentUser"})
     */
    protected $pathSignature;

    /**
     * @Assert\Image(
     *
     *      mimeTypesMessage = "Ce fichier n'est pas une image",
     *      maxSize = "5M",
     *      maxSizeMessage = "Too big."
     *      )
     */
    protected $fileSignature;

    /**
     * @var string
     * @ORM\Column(name="qualite", type="string", length=255, nullable=true)
     * @Serializer\Groups({"classeurById", "currentUser", "UserId"})
     */
    protected $qualite;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=255, nullable=true)
     * @Serializer\Groups({"currentUser", "UserId"})
     */
    protected $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="code_postal", type="string", length=6, nullable=true)
     * @Serializer\Groups({"currentUser", "UserId"})
     */
    protected $cp;

    /**
     * @var string
     *
     * @ORM\Column(name="pays", type="string", length=255, nullable=true)
     * @Serializer\Groups({"currentUser", "UserId"})
     */
    protected $pays;

    /**
     * @var string
     *
     * @ORM\Column(name="departement", type="string", length=255, nullable=true)
     * @Serializer\Groups({"currentUser", "UserId"})
     */
    protected $departement;

    /**
     * @var string
     * @ORM\Column(name="role", type="string", length=255, nullable=true)
     * @Serializer\Groups({"classeurById","currentUser"})
     */
    protected $role;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\UserRole", mappedBy="user", cascade={"remove", "persist"}, orphanRemoval=true)
     * @Serializer\Groups({"classeurById", "currentUser", "UserId"})
     */
    private $userrole;

    /**
     * @var string
     *
     * @ORM\Column(name="apitoken", type="string", length=40, nullable=true)
     * @Serializer\Groups({"UserId"})
     */
    protected $apitoken;


    /**
     * @var string
     *
     * @ORM\Column(name="apisecret", type="string", length=40, nullable=true)
     * @Serializer\Groups({"UserId"})
     */
    protected $apisecret;


    /**
     * @var boolean
     *
     * @ORM\Column(name="apiactivated", type="boolean", nullable=true, options={"default" = false})
     * @Serializer\Groups({"UserId"})
     */
    protected $apiactivated;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Sesile\MainBundle\Entity\Collectivite", inversedBy="users")
     * @ORM\JoinColumn(name="collectivite", referencedColumnName="id")
     * @Serializer\Groups({"currentUser", "listUsers", "UserId"})
     *
     */
    protected $collectivite;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", mappedBy="visible", cascade={"persist"})
     * @Exclude()
     */
    private $classeurs;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\ClasseurBundle\Entity\Classeur", mappedBy="copy", cascade={"persist"})
     * @Exclude()
     */
    private $classeursCopy;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\Groupe", mappedBy="usersCopy", cascade={"persist"})
     * @Exclude()
     */
    private $circuitsCopy;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\UserPack", mappedBy="users", cascade={"persist"})
     * @Exclude()
     */
    private $userPacks;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\EtapeClasseur", mappedBy="users", cascade={"persist"})
     * @Exclude()
     */
    private $etapeClasseurs;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\UserBundle\Entity\EtapeClasseur", mappedBy="userValidant", cascade={"remove"})
     * @Exclude()
     */
    private $etapeValide;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\EtapeGroupe", mappedBy="users", cascade={"persist"})
     * @Exclude()
     */
    private $etapeGroupes;

    /**
     * @var float
     * 
     * @ORM\Column(name="sesile_version", type="float", nullable=true)
     */
    private $sesileVersion = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Sesile\UserBundle\Entity\Note", inversedBy="users", cascade={"persist"})
     * @ORM\JoinTable(name="User_has_Note")
     * @ORM\OrderBy({"created"="DESC"})
     */
    private $notes;

    /**
     * @var array
     * @Serializer\Groups({"currentUser", "UserId"})
     */
    protected $roles;

    /**
     * @var array
     * @Serializer\Groups({"currentUser", "searchUser", "listUsers", "UserId"})
     */
    protected $email;

    /**
     * @var array
     * @Serializer\Groups({"currentUser", "UserId"})
     */
    protected $username;

    /**
     * @var array
     * @Serializer\Groups({"currentUser", "UserId"})
     */
    protected $enabled;

    /**
     * @var string
     * @Serializer\Groups({"currentUser"})
     */
    private $ozwilloUrl;

    /**
     * @var Collection|Collectivite[]
     *
     * @ORM\ManyToMany(targetEntity="Sesile\MainBundle\Entity\Collectivite", inversedBy="users")
     * @ORM\JoinTable(
     *  name="Ref_Collectivite_User",
     *  joinColumns={
     *      @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="collectivite_id", referencedColumnName="id")
     *  }
     * )
     * @ORM\OrderBy({"nom" = "ASC"})
     * @Serializer\Groups({"currentUser", "UserId"})
     */
    protected $collectivities;

    /**
     * @var string $currentOrgId
     * @Serializer\Groups({"currentUser", "UserId"})
     */
    protected $currentOrgId;


    public function setPath($path) {
        return $this->path = $path;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function getPath() {
        return $this->path;
    }

    public function getFile() {
        return $this->file;
    }

    public function getAbsolutePath() {
        return null === $this->path ? null : $this->getUploadRootDir() . $this->path;
    }

    public function getWebPath() {
        return null === $this->path ? null : $this->getUploadDir() . $this->path;
    }

    protected function getUploadRootDir() {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir() {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        /*$controller = new Controller();
        $upload = $controller->container->getParameter('upload');
        return $upload['path'];*/

        return 'uploads/avatars/';
    }

    /**
     * @ORM\OneToMany(targetEntity="Sesile\DelegationsBundle\Entity\Delegations", mappedBy="user")
     * @Exclude()
     */
    protected $delegationsRecues;

    /**
     * @ORM\OneToMany(targetEntity="Sesile\DelegationsBundle\Entity\Delegations", mappedBy="delegant")
     * @Exclude()
     */
    protected $delegationsDonnees;

    public function setPrenom($Prenom) {
        $this->Prenom = $Prenom;
        return $this;
    }

    public function setNom($Nom) {
        $this->Nom = $Nom;
        return $this;
    }

    /**
     * Renvoie le nom
     * @return string
     */
    public function getNom() {
        return $this->Nom;
    }


    /**
     * Renvoie le prenom
     * @return string
     */
    public function getPrenom() {
        return $this->Prenom;
    }
    /**
     * Renvoie active
     * @return boolean
     */
    public function getEnabled() {
        return $this->enabled;
    }

    /**
     * @ORM\PrePersist()
     */
    public function preUpload() {
        if (null !== $this->file) {
            // faites ce que vous voulez pour générer un nom unique
            $this->path = sha1(uniqid(mt_rand(), true)) . '.' . $this->file->guessExtension();
        }
    }

    /**
     *
     *
     */
    public function upload($Dirpath) {
        if (null === $this->file) {
            return;
        }

        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        if (!file_exists($Dirpath)) {
            mkdir($Dirpath);
        }
        $this->file->move($Dirpath, $this->path);
        unset($this->file);
    }

    /**
     *
     */
    public function removeUpload($Dirpath) {

        $file = $Dirpath . $this->path;
        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setApisecret("secret_" . md5(uniqid(rand(), true)));
        $this->setApitoken("token_" . md5(uniqid(rand(), true)));
        $this->collectivities = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Add delegationsRecues
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues
     * @return User
     */
    public function addDelegationsRecue(\Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues) {
        $this->delegationsRecues[] = $delegationsRecues;

        return $this;
    }

    /**
     * Remove delegationsRecues
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues
     */
    public function removeDelegationsRecue(\Sesile\DelegationsBundle\Entity\Delegations $delegationsRecues)
    {
        $this->delegationsRecues->removeElement($delegationsRecues);
    }

    /**
     * Get delegationsRecues
     *
     * @return Collection
     */
    public function getDelegationsRecues()
    {
        return $this->delegationsRecues;
    }

    /**
     * Add delegationsDonnees
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees
     * @return User
     */
    public function addDelegationsDonnee(\Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees)
    {
        $this->delegationsDonnees[] = $delegationsDonnees;

        return $this;
    }

    /**$user
     * Remove delegationsDonnees
     *
     * @param \Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees
     */
    public function removeDelegationsDonnee(\Sesile\DelegationsBundle\Entity\Delegations $delegationsDonnees)
    {
        $this->delegationsDonnees->removeElement($delegationsDonnees);
    }

    /**
     * Get delegationsDonnees
     *
     * @return Collection
     */
    public function getDelegationsDonnees()
    {
        return $this->delegationsDonnees;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return User
     */
    public function setVille($ville)
    {
        $this->ville = $ville;
        return $this;
    }

    /**
     * Get ville
     *
     * @return string
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set cp
     *
     * @param string $cp
     * @return User
     */
    public function setCp($cp)
    {
        $this->cp = $cp;

        return $this;
    }

    /**
     * Get cp
     *
     * @return string
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * Set pays
     *
     * @param string $pays
     * @return User
     */
    public function setPays($pays)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays
     *
     * @return string
     */
    public function getPays()
    {
        return $this->pays;
    }

    /**
     * Set departement
     *
     * @param string $departement
     * @return User
     */
    public function setDepartement($departement)
    {
        $this->departement = $departement;

        return $this;
    }

    /**
     * Get departement
     *
     * @return string
     */
    public function getDepartement()
    {
        return $this->departement;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set apitoken
     *
     * @param string $apitoken
     * @return User
     */
    public function setApitoken($apitoken)
    {
        $this->apitoken = $apitoken;

        return $this;
    }

    /**
     * Get apitoken
     *
     * @return string
     */
    public function getApitoken()
    {

        //Création des tokens d'api si absents
        if (empty($this->apitoken)) {
            $this->setApitoken("token_" . md5(uniqid(rand(), true)));
        }
        return $this->apitoken;
    }

    /**
     * Set apisecret
     *
     * @param string $apisecret
     * @return User
     */
    public function setApisecret($apisecret)
    {
        $this->apisecret = $apisecret;

        return $this;
    }

    /**
     * Get apisecret
     *
     * @return string
     */
    public function getApisecret()
    {

        //Création des tokens d'api si absents
        if (empty($this->apisecret)) {
            $this->setApisecret("secret_" . md5(uniqid(rand(), true)));
        }
        return $this->apisecret;
    }

    /**
     * Set apiactivated
     *
     * @param boolean $apiactivated
     * @return User
     */
    public function setApiactivated($apiactivated)
    {
        $this->apiactivated = $apiactivated;

        return $this;
    }

    /**
     * Get apiactivated
     *
     * @return boolean
     */
    public function getApiactivated()
    {
        return $this->apiactivated;
    }

    /**
     * Set collectivite
     *
     * @param \Sesile\MainBundle\Entity\Collectivite $collectivite
     * @return User
     */
    public function setCollectivite(\Sesile\MainBundle\Entity\Collectivite $collectivite = null)
    {
        $this->collectivite = $collectivite;
    
        return $this;
    }

    /**
     * Get collectivite
     *
     * @return \Sesile\MainBundle\Entity\Collectivite 
     */
    public function getCollectivite()
    {
        return $this->collectivite;
    }

    /**
     * Add classeurs
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeurs
     * @return User
     */
    public function addClasseur(Classeur $classeurs)
    {
        $this->classeurs[] = $classeurs;
    
        return $this;
    }

    /**
     * Remove classeurs
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeurs
     */
    public function removeClasseur(Classeur $classeurs)
    {
        $this->classeurs->removeElement($classeurs);
    }

    /**
     * Get classeurs
     *
     * @return Collection
     */
    public function getClasseurs()
    {
        return $this->classeurs;
    }

    /**
     * Add userPacks
     *
     * @param \Sesile\UserBundle\Entity\UserPack $userPacks
     * @return User
     */
    public function addUserPack(\Sesile\UserBundle\Entity\UserPack $userPacks)
    {
        $this->userPacks[] = $userPacks;
    
        return $this;
    }

    /**
     * Remove userPacks
     *
     * @param \Sesile\UserBundle\Entity\UserPack $userPacks
     */
    public function removeUserPack(\Sesile\UserBundle\Entity\UserPack $userPacks)
    {
        $this->userPacks->removeElement($userPacks);
    }

    /**
     * Get userPacks
     *
     * @return Collection
     */
    public function getUserPacks()
    {
        return $this->userPacks;
    }

    /**
     * Add etapeClasseurs
     *
     * @param \Sesile\UserBundle\Entity\EtapeClasseur $etapeClasseurs
     * @return User
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
     * @return Collection
     */
    public function getEtapeClasseurs()
    {
        return $this->etapeClasseurs;
    }

    /**
     * Add etapeGroupes
     *
     * @param \Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupes
     * @return User
     */
    public function addEtapeGroupe(\Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupes)
    {
        $this->etapeGroupes[] = $etapeGroupes;
    
        return $this;
    }

    /**
     * Remove etapeGroupes
     *
     * @param \Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupes
     */
    public function removeEtapeGroupe(\Sesile\UserBundle\Entity\EtapeGroupe $etapeGroupes)
    {
        $this->etapeGroupes->removeElement($etapeGroupes);
    }

    /**
     * Get etapeGroupes
     *
     * @return Collection
     */
    public function getEtapeGroupes()
    {
        return $this->etapeGroupes;
    }

    /**
     * Set pathSignature
     *
     * @param string $pathSignature
     * @return User
     */
    public function setPathSignature($pathSignature)
    {
        $this->pathSignature = $pathSignature;
    
        return $this;
    }

    /**
     * Get pathSignature
     *
     * @return string 
     */
    public function getPathSignature()
    {
        return $this->pathSignature;
    }

    /**
     * Set qualite
     *
     * @param string $qualite
     * @return User
     */
    public function setQualite($qualite)
    {
        $this->qualite = $qualite;
    
        return $this;
    }

    /**
     * Get qualite
     *
     * @return string 
     */
    public function getQualite()
    {
        return $this->qualite;
    }

    public function getFileSignature() {
        return $this->fileSignature;
    }

    public function setFileSignature($file)
    {
        $this->fileSignature = $file;

    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUploadSignature() {
        if (null !== $this->fileSignature) {
            // faites ce que vous voulez pour générer un nom unique

            $this->pathSignature = sha1(uniqid(mt_rand(), true)) . '.' . $this->fileSignature->guessExtension();

        }

    }

    /**
     *
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function uploadSignature() {
        if (null === $this->fileSignature) {
            return;
        }

        $Dirpath  = $this->getUploadRootDirSign();
        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        if (!file_exists($Dirpath)) {
            mkdir($Dirpath);
        }
        $this->fileSignature->move($Dirpath, $this->pathSignature);
        $this->fileSignature = null;
    }

    /**
     * @param $Dirpath
     */
    public function removeUploadSignature($Dirpath) {

        $file = $Dirpath . $this->pathSignature;
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function getAbsolutePathSign() {
        return null === $this->pathSignature ? null : $this->getUploadDirSign() . $this->pathSignature;
    }

    public function getWebPathSign() {
        return null === $this->pathSignature ? null : $this->getUploadDirSign() . $this->pathSignature;
    }

    protected function getUploadRootDirSign() {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__ . '/../../../../web/' . $this->getUploadDirSign();
    }

    protected function getUploadDirSign() {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return 'uploads/signatures/';
    }

    /**
     * Tells if the the given user is this user.
     *
     * Useful when not hydrating all fields.
     *
     * @param null|UserInterface $user
     *
     * @return boolean
     */
    public function isUser(UserInterface $user = null)
    {
        return null !== $user && $this->getId() === $user->getId();
    }

    /**
     * Add classeursCopy
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeursCopy
     *
     * @return User
     */
    public function addClasseursCopy(Classeur $classeursCopy)
    {
        $this->classeursCopy[] = $classeursCopy;

        return $this;
    }

    /**
     * Remove classeursCopy
     *
     * @param \Sesile\ClasseurBundle\Entity\Classeur $classeursCopy
     */
    public function removeClasseursCopy(Classeur $classeursCopy)
    {
        $this->classeursCopy->removeElement($classeursCopy);
    }

    /**
     * Get classeursCopy
     *
     * @return Collection
     */
    public function getClasseursCopy()
    {
        return $this->classeursCopy;
    }

    /**
     * Add classeursCopy
     *
     * @param \Sesile\UserBundle\Entity\Groupe $circuitsCopy
     *
     * @return User
     */
    public function addCircuitsCopy(Groupe $circuitsCopy)
    {
        $this->circuitsCopy[] = $circuitsCopy;

        return $this;
    }

    /**
     * Remove circuitsCopy
     *
     * @param \Sesile\UserBundle\Entity\Groupe $circuitsCopy
     */
    public function removeCircuitsCopy(Groupe $circuitsCopy)
    {
        $this->circuitsCopy->removeElement($circuitsCopy);
    }

    /**
     * Get circuitsCopy
     *
     * @return Collection
     */
    public function getCircuitsCopy()
    {
        return $this->circuitsCopy;
    }

    /**
     * Set sesileVersion
     *
     * @param integer $sesileVersion
     *
     * @return User
     */
    public function setSesileVersion($sesileVersion)
    {
        $this->sesileVersion = $sesileVersion;

        return $this;
    }

    /**
     * Get sesileVersion
     *
     * @return integer
     */
    public function getSesileVersion()
    {
        return $this->sesileVersion;
    }

    /**
     * Add etapeValide
     *
     * @param \Sesile\UserBundle\Entity\EtapeClasseur $etapeValide
     *
     * @return User
     */
    public function addEtapeValide(\Sesile\UserBundle\Entity\EtapeClasseur $etapeValide)
    {
        $this->etapeValide[] = $etapeValide;

        return $this;
    }

    /**
     * Remove etapeValide
     *
     * @param \Sesile\UserBundle\Entity\EtapeClasseur $etapeValide
     */
    public function removeEtapeValide(\Sesile\UserBundle\Entity\EtapeClasseur $etapeValide)
    {
        $this->etapeValide->removeElement($etapeValide);
    }

    /**
     * Get etapeValide
     *
     * @return Collection
     */
    public function getEtapeValide()
    {
        return $this->etapeValide;
    }

    /**
     * @return Collection
     */
    public function getRoles()
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * Add userrole
     *
     * @param \Sesile\UserBundle\Entity\UserRole $userrole
     *
     * @return User
     */
    public function addUserrole(\Sesile\UserBundle\Entity\UserRole $userrole)
    {
        $this->userrole[] = $userrole;

        return $this;
    }

    /**
     * Remove userrole
     *
     * @param \Sesile\UserBundle\Entity\UserRole $userrole
     */
    public function removeUserrole(\Sesile\UserBundle\Entity\UserRole $userrole)
    {
        $this->userrole->removeElement($userrole);
    }

    /**
     * Get userrole
     *
     * @return Collection
     */
    public function getUserrole()
    {
        return $this->userrole;
    }

    /**
     * Add note
     *
     * @param \Sesile\UserBundle\Entity\Note $note
     *
     * @return User
     */
    public function addNote(Note $note)
    {
        $this->notes[] = $note;

        return $this;
    }

    /**
     * Remove note
     *
     * @param \Sesile\UserBundle\Entity\Note $note
     */
    public function removeNote(Note $note)
    {
        $this->notes->removeElement($note);
    }

    /**
     * Get notes
     *
     * @return Collection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set ozwilloId
     *
     * @param string $ozwilloId
     *
     * @return User
     */
    public function setOzwilloId($ozwilloId)
    {
        $this->ozwilloId = $ozwilloId;

        return $this;
    }

    /**
     * Get ozwilloId
     *
     * @return string
     */
    public function getOzwilloId()
    {
        return $this->ozwilloId;
    }

    /**
     * Set ozwilloId
     *
     * @param string $ozwilloUrl
     *
     * @return User
     */
    public function setOzwillo (string $ozwilloUrl)
    {
        $this->ozwilloUrl = $ozwilloUrl;

        return $this;
    }

    /**
     * Get ozwilloId
     *
     * @return string
     */
    public function getOzwillo ()
    {
        return $this->ozwilloUrl;
    }

    /**
     * @param Collectivite $collectivite
     *
     * @return User
     */
    public function addCollectivity(Collectivite $collectivite)
    {
        if ($this->collectivities->contains($collectivite)) {
            return;
        }
        $this->collectivities->add($collectivite);
        $collectivite->addUser($this);

        return $this;
    }

    /**
     * @param Collectivite $collectivite
     *
     * @return User
     */
    public function removeCollectivity(Collectivite $collectivite)
    {
        if (!$this->collectivities->contains($collectivite)) {
            return;
        }
        $this->collectivities->removeElement($collectivite);
        $collectivite->removeUser($this);

        return $this;
    }

    /**
     * @return ArrayCollection|Collection|Collectivite[]
     */
    public function getCollectivities()
    {
        return $this->collectivities;
    }

    /**
     * @param string $orgId
     * @return null|Collectivite
     */
    public function getCollectivityById($orgId)
    {
        foreach ($this->collectivities as $collectivity) {
            if ($collectivity->getId() == $orgId) {
                return $collectivity;
            }
        }
        return null;
    }

    /**
     * @return Collectivite|null
     */
    public function getFirstCollectivity()
    {
        if ($this->collectivities && count($this->collectivities) > 0) {
            return $this->collectivities->first();
        }

        return null;
    }

    /**
     * @param string $collectivityId
     *
     * @return bool
     */
    public function hasCollectivity($collectivityId)
    {
        if ($collectivity = $this->getCollectivityById($collectivityId)) {
            return $collectivity;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getCurrentOrgId()
    {
        return $this->currentOrgId;
    }

    /**
     * @param string $currentOrgId
     *
     * @return User
     */
    public function setCurrentOrgId($currentOrgId)
    {
        $this->currentOrgId = $currentOrgId;

        return $this;
    }
}
