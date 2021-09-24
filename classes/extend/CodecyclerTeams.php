<?php namespace Codecycler\SURFconext\Classes\Extend;

use Event;

class CodecyclerTeams
{
    public function subscribe()
    {
        Event::listen('backend.form.extendFields', function ($formController) {
            if (!$formController->getController() instanceof \Codecycler\Teams\Controllers\Teams) {
                return;
            }

            if (!$formController->model instanceof \Codecycler\Teams\Models\Team) {
                return;
            }

            $formController->addTabFields([
                'surfconext_organisation' => [
                    'label' => 'schac_home_organization',
                    'type' => 'text',
                    'span' => 'left',
                    'tab' => 'SURFconext',
                    'comment' => 'Organization (e.g. university.nl)',
                ],
            ]);
        });
    }
}