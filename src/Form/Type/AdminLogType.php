<?php
/**
 * AdminLogg form type
 *
 * PHP Version 5
 *
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 */

namespace Ai\Bundle\AdminLoggerBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * AdminLogg form type class
 *
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 */
class AdminLogType extends AbstractType
{
    protected $em;

    /**
     * AdminLogType constructor.
     *
     * @param EntityManager $em entity manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Configure form options
     *
     * @param OptionsResolver $resolver options
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
            'label' => false,
            'virtual' => true
            )
        );
    }

    /**
     * Configure form view
     *
     * @param FormView      $view    form view
     * @param FormInterface $form    form
     * @param array         $options other options
     *
     * @return void
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $object = $form->getData();
        $objectMetaData = $this->em->getClassMetadata(get_class($object));
        $objectId = $objectMetaData->getIdentifierValues($object);

        if (empty($objectId)) {
            return;
        }

        if (is_array($objectId)) {
            $objectId = $objectId[$objectMetaData->getSingleIdentifierFieldName()];
        }

        $collection = [];
        if ($objectId && $objectId > 0) {
            $collection = $this->em->getRepository('AiAdminLoggerBundle:AdminLog')->findBy(
                [
                'entityId' => $objectId,
                'entityClass' => get_class($object)
                ],
                ['crdate' => 'asc']
            );
        }
        $view->vars['options'] = $options;
        $view->vars['collection'] = $collection;
    }

    /**
     * Get form name
     *
     * @return string
     */
    public function getName()
    {
        return 'ai_admin_log';
    }

    /**
     * Get form parent name
     *
     * @return string
     */
    public function getParent()
    {
        return 'form';
    }
}