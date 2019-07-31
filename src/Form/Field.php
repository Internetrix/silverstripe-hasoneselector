<?php

namespace Moo\HasOneSelector\Form;

use Exception;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldComponent;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Class Field provides CMS field to manage selecting/adding/editing object within
 * has_one relation of the current object being edited
 */
class Field extends CompositeField
{
    /**
     * Instance of form field that find and display selected record
     *
     * @var GridField
     */
    protected $gridField;

    /**
     * Instance of form field that holds the value
     *
     * @var FormField
     */
    protected $valueField;

    /**
     * HasOneSelector Field constructor
     *
     * @param string $name
     * @param string $title
     * @param DataObject $owner
     * @param string $dataClass
     */
    public function __construct($name, $title = ' ', DataObject $owner, $dataClass = DataObject::class)
    {
        // Create grid field
        $this->initGridField($name, $title, $owner, $dataClass);

        $this->addExtraClass('b-hasoneselector-field');

        // Ensure there is a left label to allow for field to be aligned with others
        $this->leftTitle = $title;

        // Create composite field, gridfield to find and select has one relation with hidden field holds the value
        parent::__construct([
            $this->gridField,
        ]);
    }

    /**
     * Returns a "field holder" for this field.
     *
     * Forms are constructed by concatenating a number of these field holders.
     *
     * The default field holder is a label and a form field inside a div.
     *
     * @param array $properties
     *
     * @return DBHTMLText
     * @see FieldHolder.ss
     *
     */
    public function FieldHolder($properties = [])
    {
        // Set title based on left title property
        $properties['Title'] = $this->leftTitle;

        // Render field holder
        return parent::FieldHolder($properties);
    }

    /**
     * Get instance of value holder field that hold the value of has one
     *
     * @return FormField
     */
    protected function getValueHolderField()
    {
        if (is_null($this->valueField)) {
            // Name of the has one relation
            $recordName = $this->gridField->getName() . 'ID';

            // Field to hold the value
            $this->valueField = HiddenField::create($recordName, '', '');
        }

        return $this->valueField;
    }

    /**
     * Initiate instance of grid field. This is a subclass of GridField
     *
     * @param string $name
     * @param string $title
     * @param DataObject $owner
     * @param string $dataClass
     * @return GridField
     */
    protected function initGridField($name, $title, DataObject $owner, $dataClass = DataObject::class)
    {
        if (is_null($this->gridField)) {
            $this->gridField = GridField::create($name, $title, $owner, $dataClass);
        }
        $this->gridField->setValueHolderField($this->getValueHolderField());

        if ($owner->{$name} && $owner->{$name}->exists()) {
            $this->removeLinkable();
            $this->removeAddable();
        }

        return $this->gridField;
    }

    /**
     * Remove the linkable grid field component
     *
     * @return $this
     */
    public function removeLinkable()
    {
        // Remove grid field linkable component
        $this->gridField->getConfig()->getComponents()->each(function ($component) {
            if ($component instanceof GridFieldAddExistingAutocompleter) {
                $this->gridField->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
            }
        });

        return $this;
    }

    /**
     * Add linkable grid field component
     *
     * @param GridFieldComponent|null $component
     * @return $this
     */
    public function enableLinkable(GridFieldComponent $component = null)
    {
        // Use default linkable grid field component
        if (is_null($component)) {
            $component = new GridFieldAddExistingAutocompleter('buttons-before-left');
        }

        // Add grid field component
        $this->gridField->getConfig()->addComponent($component);

        return $this;
    }

    /**
     * Remove the addable grid field component
     *
     * @return $this
     */
    public function removeAddable()
    {
        // Remove grid field addable component
        $this->gridField->getConfig()->getComponents()->each(function ($component) {
            if ($component instanceof GridFieldAddNewButton) {
                $this->gridField->getConfig()->removeComponentsByType(GridFieldAddNewButton::class);
            }
        });

        return $this;
    }

    /**
     * Add addable grid field component
     *
     * @param GridFieldComponent|null $component
     * @return $this
     */
    public function enableAddable(GridFieldComponent $component = null)
    {
        // Use default addable grid field component
        if (is_null($component)) {
            $component = new GridFieldAddNewButton('buttons-before-right');
        }

        // Add grid field component
        $this->gridField->getConfig()->addComponent($component);

        return $this;
    }

    /**
     * Proxy any undefined methods to the grid field as this is the main field and the composite is wrapper to manage
     * the field and value of has one
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $arguments = [])
    {
        if ($this->gridField instanceof GridField) {
            return $this->gridField->{$method}(...$arguments);
        }

        return parent::__call($method, $arguments);
    }
}
