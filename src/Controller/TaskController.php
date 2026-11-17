<?php

declare(strict_types=1);

namespace Spyck\AutomationEasyAdminBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Spyck\AutomationBundle\Entity\Task;
use Spyck\AutomationBundle\Service\TaskService;
use Spyck\AutomationBundle\Utility\DataUtility;
use Spyck\EasyAdminExtension\Controller\AbstractController;
use Spyck\EasyAdminExtension\Form\Type\ParameterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AdminRoute(path: 'spyck/automation/task', name: 'spyck_automation_task')]
final class TaskController extends AbstractController
{
    public static function getEntityFqcn(): string
    {
        return Task::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Task')
            ->setEntityLabelInPlural('Tasks')
            ->setDefaultSort(['priority' => 'ASC'])
            ->setSearchFields(['name']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield AssociationField::new('module');
        yield IntegerField::new('priority');
        yield BooleanField::new('active');
        yield CollectionField::new('variables')
            ->allowAdd()
            ->allowDelete()
            ->hideOnIndex()
            ->setEntryToStringMethod(static fn (?array $entry): string => null === $entry ? 'New' : sprintf('Variable: %s - %s', $entry['key'], $entry['value']))
            ->setFormType(ParameterType::class);
        yield AssociationField::new('schedules')
            ->setFormTypeOption('by_reference', false)
            ->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        $clone = $this->getCloneAction();

        $message = Action::new('message', 'Run', 'fa fa-envelope')
            ->linkToCrudAction('message');

        return $actions
            ->add(Crud::PAGE_INDEX, $clone)
            ->add(Crud::PAGE_INDEX, $message);
    }

    #[AdminRoute(path: '/{entityId}/message', options: ['methods' => [Request::METHOD_GET]])]
    public function message(AdminContext $context, TaskService $taskService): Response
    {
        $task = $context->getEntity()->getInstance();

        DataUtility::assert($task instanceof Task, $this->createNotFoundException('Task not found'));

        $taskService->executeTaskAsMessage($task->getModule(), $task->getVariables());

        $this->addFlash('success', sprintf('Task "%s" has been executed.', $task));

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->unset('entityId')
                ->generateUrl()
        );
    }
}
