<?php namespace Codecycler\SURFconext\Classes\Extend;

use Event;

class LearnKitDepartments
{
    public function subscribe()
    {
        Event::listen('backend.list.extendColumns', function ($tableController) {
            if (! $tableController->getController() instanceof \LearnKit\LMS\Controllers\Departments) {
                return;
            }

            if (! $tableController->model instanceof \LearnKit\LMS\Models\Department) {
                return;
            }

            $tableController->addColumns([
                'is_created_by_surfconext' => [
                    'label' => 'SURFconext auto',
                    'type' => 'switch',
                    'sortable' => true,
                ],
            ]);
        });
    }
}
