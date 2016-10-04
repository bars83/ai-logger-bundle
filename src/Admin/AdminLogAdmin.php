<?php
/**
 * AdminLogAdmin
 *
 * PHP Version 5
 *
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 */

namespace Ai\Bundle\AdminLoggerBundle\Admin;

use Ai\Bundle\AdminLoggerBundle\Form\Type\AdminLinkType;
use Ai\Bundle\AdminLoggerBundle\Form\Type\AdminLogType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Ai\Bundle\AdminLoggerBundle\Entity\AdminLog;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * AdminLogAdmin Class
 *
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 */
class AdminLogAdmin extends Admin
{
    use ContainerAwareTrait;

    protected static $adminOptions=[];

    protected static $adminRoutes=[];

    protected static $adminGroups=[];

    /**
     * Admin configure
     *
     * @return void
     */
    public function configure()
    {
        parent::configure();

        $this->datagridValues['_sort_by']    = 'crdate';
        $this->datagridValues['_sort_order'] = 'DESC';
    }

    /**
     * Configure admin filter form
     *
     * @param DatagridMapper $datagridMapper maper object
     *
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'crdate',
                'doctrine_orm_datetime_range',
                ['field_type'=>'sonata_type_datetime_range_picker'],
                null,
                [
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy',
                    'required' => false,
                    'attr' => ['class' => 'date_time_selector']
                ]
            )
            ->add(
                'type', null, [], 'sonata_type_translatable_choice',
                [
                    'choices' => AdminLog::getTypes(),
                    'translation_domain' => 'messages'
                ]
            )
            ->add('user')
            ->add('adminLabel')
            ->add(
                'groupLabel', null, [],  'choice',
                [
                    'choices' => $this->getGroups()
                ]
            )
            ->add('entityName')
            ->add('categoryName');
    }

    /**
     * Configure admin list
     *
     * @param ListMapper $listMapper mapper object
     *
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('crdate')
            ->add(
                'group', 'string',
                [
                'template' => 'AiAdminLoggerBundle:CRUD:list_group_field.html.twig',
                ]
            )
            ->add(
                'admin', 'string',
                [
                'template' => 'AiAdminLoggerBundle:CRUD:list_admin_field.html.twig',
                ]
            )
            ->add(
                'entity_link', 'string',
                [
                'template' => 'AiAdminLoggerBundle:CRUD:list_entity_field.html.twig',
                ]
            )
            ->add(
                'category_link', 'string',
                [
                'template' => 'AiAdminLoggerBundle:CRUD:list_category_field.html.twig',
                ]
            )
            ->add('user')
            ->add(
                'type', 'string',
                [
                'label' => 'list.label_type_description',
                'template' => 'AiAdminLoggerBundle:CRUD:list_type_field.html.twig',
                ]
            )
            ->add(
                '_action', 'actions',
                [
                'template' => 'AiAdminLoggerBundle:CRUD:list_actions_field.html.twig',
                ]
            );
    }

    /**
     * Admin Edit form configuration
     *
     * @param FormMapper $formMapper mapper object
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
    }

    /**
     * Admin entity show configuration
     *
     * @param ShowMapper $showMapper mapper object
     *
     * @return void
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('crdate')
            ->add(
                'group', 'string',
                [
                    'template' => 'AiAdminLoggerBundle:CRUD:show_group_field.html.twig',
                ]
            )
            ->add(
                'admin', 'string',
                [
                    'template' => 'AiAdminLoggerBundle:CRUD:show_admin_field.html.twig',
                ]
            )
            ->add(
                'entity_link', 'string',
                [
                    'template' => 'AiAdminLoggerBundle:CRUD:show_entity_field.html.twig',
                ]
            )
            ->add(
                'category_link', 'string',
                [
                    'template' => 'AiAdminLoggerBundle:CRUD:show_category_field.html.twig',
                ]
            )
            ->add('user')
            ->add(
                'type', 'string',
                [
                'template' => 'AiAdminLoggerBundle:CRUD:show_type_field.html.twig',
                ]
            )
            ->add(
                'changeset', 'string',
                [
                'template' => 'AiAdminLoggerBundle:CRUD:show_changeset_field.html.twig'
                ]
            );
    }

    /**
     * Admin routes configuration
     *
     * @param RouteCollection $collection route collection
     *
     * @return void
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('delete');
    }

    /**
     * Get admin groups
     *
     * @return array
     */
    protected function getGroups()
    {
        if (self::$adminGroups) {
            return self::$adminGroups;
        }

        foreach ($this->getAdminsOptions() as $option) {
            self::$adminGroups[$option['groupLabel']] = $option['groupLabelTrans'];
        }

        return self::$adminGroups;
    }

    /**
     * Get admin options
     *
     * @param object $entityClass entity class
     *
     * @return mixed|null
     */
    public function getAdminOptionByClass($entityClass)
    {
        $adminsOptions = $this->getAdminsOptions();
        if (array_key_exists($entityClass, $adminsOptions)) {
            return $adminsOptions[$entityClass];
        }

        return null;
    }

    /**
     * Get admin options
     *
     * @return array
     */
    public function getAdminsOptions()
    {
        if (self::$adminOptions) {
            return self::$adminOptions;
        }

        $configurationPool = $this->getConfigurationPool();
        $entityClasses = array_flip(
            array_map(
                function ($v) {
                    return array_shift($v);
                }, $configurationPool->getAdminClasses()
            )
        );

        foreach ($configurationPool->getAdminGroups() as $sectionLabel => $group) {

            foreach ($group['items'] as $adminServiceId) {
                if (!$entityClass = $entityClasses[$adminServiceId]) {
                    continue;
                }

                $routes = $this->getAdminRoutes();
                if (!array_key_exists($adminServiceId, $routes)) {
                    continue;
                }

                self::$adminOptions[$entityClass] = [
                    'adminServiceId' => $adminServiceId,
                    'groupLabel' => $sectionLabel,
                    'groupLabelTrans' => $group['label'],
                    //'adminLabel'    => $configurationPool->getAdminByAdminCode($adminServiceId)->getLabel(),
                    'route' => $routes[$adminServiceId],
                ];
            }
        }

        return self::$adminOptions;
    }

    /**
     * Get admin routes
     *
     * @return array
     */
    protected function getAdminRoutes()
    {
        if (self::$adminRoutes) {
            return self::$adminRoutes;
        }

        $router = $this->container->get('router');
        foreach ($router->getRouteCollection() as $routeName => $route) {
            $adminServiceId = $route->getDefault('_sonata_admin');
            if ($adminServiceId && preg_match('/edit/', $routeName)) {
                self::$adminRoutes[$adminServiceId] = $routeName;
            }
        }

        return self::$adminRoutes;
    }
}
