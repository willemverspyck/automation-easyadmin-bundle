<?php

declare(strict_types=1);

namespace Spyck\AutomationEasyAdminBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Spyck\AutomationBundle\Entity\ScheduleForEvent;

#[AdminRoute(path: 'spyck/automation/schedule-for-event', name: 'spyck_automation_schedule_for_event')]
final class ScheduleForEventController extends AbstractScheduleController
{
    public static function getEntityFqcn(): string
    {
        return ScheduleForEvent::class;
    }
}
