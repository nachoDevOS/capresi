<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;

class AddPaymentEmploye extends AbstractAction
{
    public function getTitle()
    {
        return 'Adelantos';
    }

    public function getIcon()
    {
        return 'voyager-dollar';
    }

    public function getPolicy()
    {
        return 'read';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-default pull-right',
        ];
    }

    public function getDefaultRoute()
    {
        return route('employes.payments', ['id' => $this->data->id]);
    }

    public function shouldActionDisplayOnDataType()
    {
        return $this->dataType->slug == 'employes';
    }
}