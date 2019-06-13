<?php

class IndipetaePDFBuilder_MetadataBuilder
{
    /**
     * @var Item
     */
    private $letter;

    public function __construct(Item $letter)
    {
        $this->letter = $letter;
    }

    public function getField(string $field, string $label): IndipetaePDFBuilder_MetadataField
    {
        $values = metadata($this->letter, ['Dublin Core', $field], ['no_filter' => true, 'all' => true]);
        return new IndipetaePDFBuilder_MetadataField($label, $values);
    }
}