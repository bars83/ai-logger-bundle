<?php
/**
 * AdminLogger event listener
 *
 * PHP Version 5
 *
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 */

namespace Ai\Bundle\AdminLoggerBundle\Event;

use Doctrine\Common\Persistence\ObjectManager;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Sonata\AdminBundle\Event\PersistenceEvent;
use Ai\Bundle\AdminLoggerBundle\Entity\AdminLog;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\PersistentCollection;
use \Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * AdminLogger event listener class
 *
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 */
class AdminLogger
{
    use ContainerAwareTrait;

    const LOGGER_TYPE_ALL = 'all';
    const LOGGER_TYPE_MAPPING = 'mapping';

    protected static $LOGGER_TYPES = [
        self::LOGGER_TYPE_ALL => 'logger.type_all',
        self::LOGGER_TYPE_ALL => 'logger.type_mapping',
    ];

    protected static $TYPES_MAPPING = [
        PersistenceEvent::TYPE_POST_PERSIST => AdminLog::TYPE_CREATE,
        PersistenceEvent::TYPE_POST_UPDATE => AdminLog::TYPE_UPDATE,
        PersistenceEvent::TYPE_POST_REMOVE => AdminLog::TYPE_REMOVE,
        PersistenceEvent::TYPE_PRE_PERSIST => AdminLog::TYPE_CREATE,
        PersistenceEvent::TYPE_PRE_UPDATE => AdminLog::TYPE_UPDATE,
        PersistenceEvent::TYPE_PRE_REMOVE => AdminLog::TYPE_REMOVE,
    ];

    protected $user;

    /**
     * Create event
     *
     * @param PersistenceEvent $event event object
     *
     * @return void
     * @throws InvalidArgumentException
     *
     * @throws AccessDeniedException
     */
    public function create(PersistenceEvent $event)
    {
        try {
            $this->logObjectChanges($event);
        } catch (\Exception $e) {
            $this->container->get('session')->getFlashBag()->add('sonata_flash_error', $e->getMessage());
        }
    }

    /**
     * Update event
     *
     * @param PersistenceEvent $event event object
     *
     * @return void
     * @throws InvalidArgumentException
     *
     * @throws AccessDeniedException
     */
    public function update(PersistenceEvent $event)
    {
        try {
            $this->logObjectChanges($event);
        } catch (\Exception $e) {
            $this->container->get('session')->getFlashBag()->add('sonata_flash_error', $e->getMessage());
        }
    }

    /**
     * Remove event
     *
     * @param PersistenceEvent $event event object
     *
     * @return void
     * @throws InvalidArgumentException
     *
     * @throws AccessDeniedException
     */
    public function remove(PersistenceEvent $event)
    {
        try {
            $this->logObjectChanges($event);
        } catch (\Exception $e) {
            $this->container->get('session')->getFlashBag()->add('sonata_flash_error', $e->getMessage());
        }
    }

    /**
     * Fix object changes
     *
     * @param PersistenceEvent $event event object
     *
     * @return void
     * @throws InvalidArgumentException
     *
     * @throws AccessDeniedException
     */
    protected function logObjectChanges(PersistenceEvent $event)
    {
        if (!$this->user = $this->container->get('security.token_storage')->getToken()->getUser()) {
            throw new AccessDeniedException('User not found');
        }

        $loggerType = $this->container->getParameter('ai_admin_logger')['type'];

        if (!array_key_exists($loggerType, self::$LOGGER_TYPES)) {
            throw new InvalidArgumentException('Invalid type ai_admin_logger.');
        }

        $object = $event->getObject();
        if (!is_object($object)) {
            return;
        }

        if ($loggerType === self::LOGGER_TYPE_ALL) {
            $this->log($loggerType, $object, $event);
        }

        $mapping = $this->container->getParameter('ai_admin_logger')['mapping'];
        if ($loggerType === self::LOGGER_TYPE_MAPPING
            && array_key_exists(get_class($object), $mapping)
            && $objMapping = $mapping[get_class($object)]
        ) {
            $this->log($loggerType, $object, $event, $objMapping);
        }

    }

    /**
     * Log
     *
     * @param string $loggerType self::$LOGGER_TYPES
     * @param object $object entity object
     * @param object $event event object
     * @param array $objMapping object mapping in config
     *
     * @return void
     */
    protected function log($loggerType, $object, $event, array $objMapping = [])
    {

        $objectClass = get_class($object);
        $admin = $event->getAdmin();

        $em = $admin->getModelManager()->getEntityManager($admin->getClass());
        $objectMetaData = $em->getClassMetadata($objectClass);
        $objectId = $objectMetaData->getIdentifierValues($object);

        if (empty($objectId)) {
            return;
        }

        if (is_array($objectId)) {
            $objectId = $objectId[$objectMetaData->getSingleIdentifierFieldName()];
        }

        $adminOptions = $admin
            ->getConfigurationPool()
            ->getAdminByAdminCode('ai_admin_logger.admin.admin_log')
            ->getAdminOptionByClass($objectClass);
        $adminLog = new AdminLog();
        $adminLog->setGroupLabel($adminOptions['groupLabel']);
        $adminLog->setAdminLabel($admin->getLabel());
        $adminLog->setEntityClass($objectClass);
        $adminLog->setEntityId($objectId);

        if (!empty($objMapping)
            && $objectMetaData->hasField($objMapping['title_field'])
        ) {
            $adminLog->setEntityName($objectMetaData->getFieldValue($object, $objMapping['title_field']));
        } else {
            $adminLog->setEntityName(((string)$object));
        }

        if (self::$TYPES_MAPPING[$event->getType()] === AdminLog::TYPE_UPDATE) {
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $changeset = $uow->getEntityChangeSet($object);
            $adminFieldGroups = $this->getAdminFieldGroups($admin);
            $changes = [];
            foreach ($changeset as $fieldName => $changedData) {
                //Continue if field doesn't mapping
                if ($loggerType === self::LOGGER_TYPE_MAPPING
                    && !in_array($fieldName, $objMapping['fields'])
                ) {
                    unset($changeset[$fieldName]);
                    continue;
                }

                //Save collections changes
                if ($changedData instanceof PersistentCollection) {
                    $oldData = $changedData->getSnapshot();
                    $newData = $objectMetaData->getFieldValue($object, $fieldName);
                    $newData = $newData instanceof PersistentCollection ? $newData->toArray() : [];

                    foreach ($oldData as $k => $item) {
                        $oldData[$item->getId()] = (string)$item;
                        unset($oldData[$k]);
                    }

                    foreach ($newData as $k => $item) {
                        $newData[$item->getId()] = (string)$item;
                        unset($newData[$k]);
                    }

                    $addObjects = array_diff($newData, $oldData);
                    $delObjects = array_diff($oldData, $newData);

                    $changedStr = '';
                    if (!empty($addObjects)) {
                        foreach ($addObjects as $id => $obj) {
                            $changedStr .= sprintf(" Added object Id %s - %s. ", $id, $obj);
                        }
                    }
                    if (!empty($delObjects)) {
                        foreach ($delObjects as $id => $obj) {
                            $changedStr .= sprintf(" Removed object Id %s - %s.", $id, $obj);
                        }
                    }
                    if ($changedStr != '') {
                        $changes[$fieldName] = [
                            'old' => is_array($oldData) ? implode(', ', $oldData) : '',
                            'new' => is_array($newData) ? implode(', ', $newData) : '',
                        ];
                        $changes[$fieldName] = array_merge($changes[$fieldName], $adminFieldGroups[$fieldName]);
                    }
                } else {
                    //Remove canonicalTitle
                    if (array_key_exists('canonicalTitle', $changeset)) {
                        unset($changeset['canonicalTitle']);
                        continue;
                    }

                    //Save othe changes
                    $changes[$fieldName] = [
                        'old' => $changeset[$fieldName][0],
                        'new' => $changeset[$fieldName][1],
                    ];
                    if (array_key_exists($fieldName, $adminFieldGroups))
                        $changes[$fieldName] = array_merge($changes[$fieldName], $adminFieldGroups[$fieldName]);
                }
            }

            if (empty($changes)) {
                return;
            }

            $adminLog->setChangeset($changes);
        }
        $category = null;
        if ($loggerType === self::LOGGER_TYPE_MAPPING
            && array_key_exists('category', $objMapping)
            && $objectMetaData->hasAssociation($objMapping['category']['field'])
        ) {
            $category = $objectMetaData->getFieldValue($object, $objMapping['category']['field']);
        }

        if (!$category && $objectMetaData->hasAssociation('rubrics')) {
            $category = $objectMetaData->getFieldValue($object, 'rubrics');
        }

        if (!$category && $objectMetaData->hasAssociation('category')) {
            $category = $objectMetaData->getFieldValue($object, 'category');
        }

        if ($category) {
            if ($category instanceof PersistentCollection) {
                $category = $category->first();
            }

            if (is_object($category)) {

                $categoryMetaData = $em->getClassMetadata(get_class($category));

                $categoryId = $categoryMetaData->getIdentifierValues($category);
                if (is_array($categoryId)) {
                    $categoryId = $categoryId[$categoryMetaData->getSingleIdentifierFieldName()];
                }

                $adminLog->setCategoryClass(get_class($category));
                $adminLog->setCategoryId($categoryId);

                if ($loggerType === self::LOGGER_TYPE_MAPPING
                    && $categoryMetaData->hasField($objMapping['category']['title_field'])
                ) {
                    $adminLog->setCategoryName($categoryMetaData->getFieldValue($category, $objMapping['category']['title_field']));
                } else {
                    $adminLog->setCategoryName((string)$category);
                }
            }
        }

        $adminLog->setType(self::$TYPES_MAPPING[$event->getType()]);
        $adminLog->setUser($this->user);
        $em->persist($adminLog);
        $em->flush();
    }

    /**
     * Ger field form options
     *
     * @param AdminInterface $admin sonata admin class
     *
     * @return array
     */
    protected function getAdminFieldGroups(AdminInterface $admin)
    {
        $adminFormGroups = $admin->getFormGroups();
        if (!$adminFormGroups)
            $adminFormGroups = [];
        $adminFieldGroups = [];
        foreach ($adminFormGroups as $groupName => $group) {
            foreach ($group['fields'] as $field => $val) {
                $adminFieldGroups[$field] = [
                    'groupName' => str_replace('.', '/', $groupName),
                    'name' => $group['name'],
                    'translation_domain' => $group['translation_domain'],
                    'value' => $val,
                ];
            }
        }

        return $adminFieldGroups;
    }
}