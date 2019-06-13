<?php

class IndipetaePDFBuilder_MetadataField
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var array
     */
    private $values;

    public function __construct(string $label, array $values)
    {
        $this->label = $label;
        $this->values = $values;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}