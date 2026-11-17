<?php

declare(strict_types=1);

namespace Spyck\AutomationEasyAdminBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Spyck\EasyAdminExtension\Controller\AbstractController;

abstract class AbstractScheduleController extends AbstractController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Schedule')
            ->setEntityLabelInPlural('Schedules')
            ->setSearchFields(['name', 'code'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextField::new('code')
            ->setRequired(false);
        yield BooleanField::new('active');
        yield ChoiceField::new('matchHours')
            ->allowMultipleChoices()
            ->hideOnIndex()
            ->setChoices($this->getMatchHours())
            ->setRequired(false);
        yield ChoiceField::new('matchDays')
            ->allowMultipleChoices()
            ->hideOnIndex()
            ->setChoices($this->getMatchDays())
            ->setRequired(false);
        yield ChoiceField::new('matchWeeks')
            ->allowMultipleChoices()
            ->hideOnIndex()
            ->setChoices($this->getMatchWeeks())
            ->setRequired(false);
        yield ChoiceField::new('matchWeekdays')
            ->allowMultipleChoices()
            ->hideOnIndex()
            ->setChoices($this->getMatchWeekdays())
            ->setRequired(false);
    }

    private function getMatchHours(): array
    {
        $hours = range(0, 23);

        return array_combine($hours, $hours);
    }

    private function getMatchDays(): array
    {
        $days = range(1, 31);

        $data = array_combine($days, $days);
        $data['Last Day of the Month'] = 'L';

        return $data;
    }

    private function getMatchWeeks(): array
    {
        $weeks = range(1, 52);

        return array_combine($weeks, $weeks);
    }

    private function getMatchWeekdays(): array
    {
        $weekdays = range(1, 7);

        return array_combine($weekdays, $weekdays);
    }
}
