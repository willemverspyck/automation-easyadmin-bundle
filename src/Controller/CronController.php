<?php

declare(strict_types=1);

namespace Spyck\AutomationEasyAdminBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Spyck\AutomationBundle\Entity\Cron;
use Spyck\AutomationBundle\Service\CronService;
use Spyck\AutomationBundle\Utility\DataUtility;
use Spyck\EasyAdminExtension\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AdminRoute(path: 'spyck/automation/cron', name: 'spyck_automation_cron')]
final class CronController extends AbstractController
{
    private const RESET_PERMISSION = 'ROLE_ADMIN';

    public function __construct(private readonly CronService $cronService)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Cron::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Cron')
            ->setEntityLabelInPlural('Crons')
            ->setDefaultSort(['timestamp' => 'DESC'])
            ->askConfirmationOnBatchActions(true);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('module')
            ->hideOnForm();
        yield ChoiceField::new('priority')
            ->setChoices([
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
            ])
            ->renderExpanded(false);
        yield TextField::new('callback')
            ->hideOnForm();
        yield ArrayField::new('variables')
            ->hideOnForm();
        yield ChoiceField::new('status')
            ->setChoices(Cron::getStatuses(true))
            ->hideOnForm();
        yield IntegerField::new('duration')
            ->hideOnForm();
        yield ArrayField::new('messages')
            ->onlyOnDetail();
        yield IntegerField::new('errors')
            ->hideOnForm();
        yield DateTimeField::new('timestamp')
            ->hideOnForm();
        yield DateTimeField::new('timestampAvailable');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                EntityFilter::new('module')
                    ->canSelectMultiple()
            )
            ->add(
                TextFilter::new('callback')
            )
            ->add(
                ChoiceFilter::new('status')
                    ->setChoices(Cron::getStatuses(true))
                    ->canSelectMultiple()
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        $reset = Action::new('reset', 'Reset', 'fa fa-rotate')
            ->linkToCrudAction('reset')
            ->askConfirmation('Are you sure you want to reset this cron?');

        $batchReset = Action::new('reset-batch', 'Reset selected', 'fa fa-rotate')
            ->linkToCrudAction('resetBatch');

        return $actions
            ->disable(Action::DELETE, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $reset)
            ->addBatchAction($batchReset)
            ->setPermission('reset', self::RESET_PERMISSION)
            ->setPermission('reset-batch', self::RESET_PERMISSION);
    }

    #[AdminRoute(path: '/{entityId}/reset', name: 'reset', options: ['methods' => [Request::METHOD_GET]])]
    public function reset(AdminContext $context, CronService $cronService): Response
    {
        $cron = $context->getEntity()->getInstance();

        if (false === $cron instanceof Cron) {
            throw $this->createNotFoundException('Cron not found');
        }

        $this->denyAccessUnlessGranted(self::RESET_PERMISSION, $cron);

        $cronService->patchCronForReset($cron);

        $this->addFlash('success', 'Cron has been reset.');

        return $this->redirectToIndex();
    }

    #[AdminRoute(path: '/reset-batch', name: 'reset_batch', options: ['methods' => [Request::METHOD_POST]])]
    public function resetBatch(BatchActionDto $batch): Response
    {
        $this->denyAccessUnlessGranted(self::RESET_PERMISSION);

        DataUtility::assert(Cron::class === $batch->getEntityFqcn() && 'reset-batch' === $batch->getName(), $this->createAccessDeniedException());

        if (false === $this->isCsrfTokenValid(sprintf('ea-batch-action-%s-%s', $batch->getName(), $batch->getEntityFqcn()), $batch->getCsrfToken())) {
            throw $this->createAccessDeniedException();
        }

        $repository = $this->container->get('doctrine')->getRepository(Cron::class);

        foreach ($batch->getEntityIds() as $id) {
            $cron = $repository->find($id);

            if ($cron instanceof Cron) {
                $this->cronService->patchCronForReset($cron, true);
            }
        }

        $this->addFlash('success', 'Crons have been reset.');

        return $this->redirectToIndex();
    }
}
