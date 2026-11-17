<?php

declare(strict_types=1);

namespace Spyck\AutomationEasyAdminBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Spyck\AutomationBundle\Entity\ScheduleForSystem;

#[AdminRoute(path: 'spyck/automation/schedule-for-system', name: 'spyck_automation_schedule_for_system')]
final class ScheduleForSystemController extends AbstractScheduleController
{
    public static function getEntityFqcn(): string
    {
        return ScheduleForSystem::class;
    }
}
