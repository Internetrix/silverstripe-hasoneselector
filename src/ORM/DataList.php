<?php

namespace Moo\HasOneSelector\ORM;

use Exception;
use Moo\HasOneSelector\Form\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\ORM\DataList as BaseDataList;
use SilverStripe\ORM\DataObject;

/**
 * Class DataList is data list to manage add/remove managed object from the
 * HasOneSelectorField class
 */
class DataList extends BaseDataList
{
    /**
     * @var GridField
     */
    protected $gridField;

    /**
     * HasOneSelectorDataList constructor.
     * @param GridField $gridField
     */
    public function __construct(GridField $gridField)
    {
        $this->gridField = $gridField;

        parent::__construct($gridField->getDataClass());
    }

    /**
     * Set the current selected record into the has one relation
     *
     * @param DataObject $item
     * @return void
     * @throws Exception
     */
    public function add($item)
    {
        $this->gridField->setRecord($item);
        $config = $this->gridField->getConfig();
        $config->removeComponentsByType(GridFieldAddNewButton::class);
        $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);

    }

    /**
     * Clear the record within the has one relation
     *
     * @param DataObject $item
     * @return void
     * @throws Exception
     */
    public function remove($item)
    {
        $this->gridField->setRecord(null);
        $config = $this->gridField->getConfig();
        $config->addComponent(new GridFieldAddNewButton('buttons-before-right'));
        $config->addComponent(new GridFieldAddExistingAutocompleter('buttons-before-left'));
    }
}
