<?php

/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\RepositoryForms\FieldType\Mapper;

use eZ\Publish\API\Repository\FieldTypeService;
use EzSystems\RepositoryForms\Data\Content\FieldData;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\DataTransformer\FieldValueTransformer;
use EzSystems\RepositoryForms\FieldType\DataTransformer\TextLineValueTransformer;
use EzSystems\RepositoryForms\FieldType\FieldTypeFormMapperInterface;
use EzSystems\RepositoryForms\FieldType\FieldValueFormMapperInterface;
use Symfony\Component\Form\FormInterface;

class TextLineFormMapper implements FieldTypeFormMapperInterface, FieldValueFormMapperInterface
{
    /**
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    private $fieldTypeService;

    public function __construct(FieldTypeService $fieldTypeService)
    {
        $this->fieldTypeService = $fieldTypeService;
    }

    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data)
    {
        $fieldDefinitionForm
            ->add('minLength', 'integer', [
                'required' => false,
                'property_path' => 'validatorConfiguration[StringLengthValidator][minStringLength]',
                'label' => 'field_definition.ezstring.min_length',
                'attr' => ['min' => 0],
            ])
            ->add('maxLength', 'integer', [
                'required' => false,
                'property_path' => 'validatorConfiguration[StringLengthValidator][maxStringLength]',
                'label' => 'field_definition.ezstring.max_length',
                'attr' => ['min' => 0],
            ])
            ->add(
                // Creating from FormBuilder as we need to add a DataTransformer.
                $fieldDefinitionForm->getConfig()->getFormFactory()->createBuilder()
                    ->create('defaultValue', 'text', [
                        'required' => false,
                        'label' => 'field_definition.ezstring.default_value',
                    ])
                    ->addModelTransformer(new TextLineValueTransformer())
                    // Deactivate auto-initialize as we're not on the root form.
                    ->setAutoInitialize(false)->getForm()
            );
    }

    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $label = $fieldDefinition->getName($formConfig->getOption('languageCode')) ?: reset($fieldDefinition->getNames());

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        'text',
                        ['required' => $fieldDefinition->isRequired, 'label' => $label]
                    )
                    ->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType($fieldDefinition->fieldTypeIdentifier)))
                    // Deactivate auto-initialize as we're not on the root form.
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
