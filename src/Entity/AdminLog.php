<?php
/**
 * AdminLog entity
 *
 * PHP Version 5
 * 
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 */

namespace Ai\Bundle\AdminLoggerBundle\Entity;

use App\Application\Sonata\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AdminLog
 *
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 *
 * @ORM\Table(
 *   name="admin_log",
 *   indexes={
 *     @ORM\Index(name="name_idx", columns={"crdate"}),
 *     @ORM\Index(name="name_idx", columns={"entity_id"}),
 *     @ORM\Index(name="name_idx", columns={"entity_class"}),
 * }
 * )
 * @ORM\Entity(repositoryClass="Ai\Bundle\AdminLoggerBundle\Entity\AdminLogRepository")
 */
class AdminLog
{
    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';
    const TYPE_REMOVE = 'remove';

    protected static $TYPES = [
        self::TYPE_CREATE => 'list.label_create_type',
        self::TYPE_UPDATE => 'list.label_update_type',
        self::TYPE_REMOVE => 'list.label_remove_type',
    ];

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="crdate", type="datetime")
     */
    private $crdate;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="group_label", type="string", length=255)
     */
    private $groupLabel;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_label", type="string", length=255)
     */
    private $adminLabel;

    /**
     * @var string
     *
     * @ORM\Column(name="entity_class", type="string", length=255)
     */
    private $entityClass;

    /**
     * @var integer
     *
     * @ORM\Column(name="entity_id", type="integer")
     */
    private $entityId;

    /**
     * @var string
     *
     * @ORM\Column(name="entity_name", type="string")
     */
    private $entityName;

    /**
     * @var string
     *
     * @ORM\Column(name="category_class", type="string", length=255, nullable=true)
     */
    private $categoryClass;

    /**
     * @var integer
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    private $categoryId;

    /**
     * @var string
     *
     * @ORM\Column(name="category_name", type="string", nullable=true)
     */
    private $categoryName;

    /**
     * @var string
     *
     * @ORM\Column(name="changeset", type="json_array", nullable=true)//TODO jsonb
     */
    private $changeset;

    /**
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @return string
     */
    public function __toString()
    {
        return '';// TODO: Implement __toString() method.
    }

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
     * Set crdate
     *
     * @param \DateTime $crdate
     * @return AdminLog
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;

        return $this;
    }

    /**
     * Get crdate
     *
     * @return \DateTime
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return AdminLog
     */
    public function setType($type)
    {
        if (!array_key_exists($type, self::$TYPES)) {
            throw new \InvalidArgumentException("Invalid type");
        }

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
     * @return array
     */
    public static function getTypes()
    {
        return self::$TYPES;
    }

    /**
     * Set groupLabel
     *
     * @param string $groupLabel
     * @return AdminLog
     */
    public function setGroupLabel($groupLabel)
    {
        $this->groupLabel = $groupLabel;

        return $this;
    }

    /**
     * Get groupLabel
     *
     * @return string
     */
    public function getGroupLabel()
    {
        return $this->groupLabel;
    }

    /**
     * Set adminLabel
     *
     * @param string $adminLabel
     * @return AdminLog
     */
    public function setAdminLabel($adminLabel)
    {
        $this->adminLabel = $adminLabel;

        return $this;
    }

    /**
     * Get adminLabel
     *
     * @return string
     */
    public function getAdminLabel()
    {
        return $this->adminLabel;
    }

    /**
     * Set entityClass
     *
     * @param string $entityClass
     * @return AdminLog
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get entityClass
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Set entityId
     *
     * @param integer $entityId
     * @return AdminLog
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return integer
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set entityName
     *
     * @param string $entityName
     * @return AdminLog
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Get entityName
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Set categoryClass
     *
     * @param string $categoryClass
     * @return AdminLog
     */
    public function setCategoryClass($categoryClass)
    {
        $this->categoryClass = $categoryClass;

        return $this;
    }

    /**
     * Get categoryClass
     *
     * @return string
     */
    public function getCategoryClass()
    {
        return $this->categoryClass;
    }

    /**
     * Set categoryId
     *
     * @param integer $categoryId
     * @return AdminLog
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set categoryName
     *
     * @param string $categoryName
     * @return AdminLog
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * Get categoryName
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * Set changeset
     *
     * @param array $changeset
     * @return AdminLog
     */
    public function setChangeset($changeset)
    {
        $this->changeset = $changeset;

        return $this;
    }

    /**
     * Get changeset
     *
     * @return array
     */
    public function getChangeset()
    {
        return $this->changeset;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return AdminLog
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
