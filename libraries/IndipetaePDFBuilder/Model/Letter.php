<?php

class IndipetaePDFBuilder_Model_Letter
{
    /**
     * @var Item
     */
    private $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function title(): string
    {
        return $this->getField('Title', 'Title')->getValues()[0];
    }

    public function id(): string
    {
        return (string) $this->item->id;
    }

    public function transcription(): string
    {
        return $this->getField('Description', 'Transcription')->getValues()[0];
    }

    public function transcriptionBack(): string
    {
        return $this->getField('Extent', 'Transcription — back')->getValues()[0];
    }

    /**
     * Get an array of metadata fields to display
     *
     * Each field has a label and a value. Change the order of the fields in the array to change the order that
     * they render in the PDF.
     *
     * @return IndipetaePDFBuilder_MetadataField[]
     */
    public function metadata(): array
    {
        return [
            $this->transcribedBy(),
            $this->callNumber(),
            $this->date(),
            $this->from(),
            $this->to(),
            $this->sender(),
            $this->grade(),
            $this->recipient(),
            $this->destination(),
            $this->models(),
            $this->otherNames(),
            $this->leftForMissionLands(),
            $this->anteriorDesire(),
            $this->collection(),
        ];
    }

    public function callNumber(): IndipetaePDFBuilder_MetadataField
    {
        $archive = $this->getField('Identifier', 'Archive')->getValues()[0];
        $folder = $this->getField('Has Format', 'Folder')->getValues()[0];
        $number = $this->getField('Has Version', 'Number')->getValues()[0];
        return new IndipetaePDFBuilder_MetadataField('Call Number', ["$archive $folder $number"]);
    }

    public function collection(): IndipetaePDFBuilder_MetadataField
    {
        $collection_name = '';
        if ($collection = $this->item->getCollection()) {
            $collection_name = metadata($collection, array('Dublin Core', 'Title'));
        }
        return new IndipetaePDFBuilder_MetadataField('Collection', [$collection_name]);
    }

    /**
     * Doesn't work for now!
     *
     * @return string
     * @todo figure out how to add citations
     */
    public function citation(): string
    {
        return "@TODO: Figure out how to add citations";
    }

    /**
     * Add a PDF to the item
     *
     * @param string $path_to_pdf
     */
    public function addFile(string $path_to_pdf): void
    {
        insert_files_for_item($this->item, 'Filesystem', [$path_to_pdf]);
    }

    /**
     * Remove a PDF from the item
     *
     * @param string $filename_prefix
     */
    public function removePDF(string $filename_prefix): void
    {
        foreach ($this->item->getFiles() as $file) {
            if (strpos($file->original_filename, $filename_prefix) === 0) {
                $file->delete();
            }
        }
    }

    public function transcribedBy(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Contributor', 'Transcribed by');
    }

    public function date(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Date', 'Date');
    }

    public function from(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Coverage', 'From');
    }

    public function to(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Spatial Coverage', 'To');
    }

    public function sender(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Creator', 'Sender');
    }

    public function grade(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Replaces', 'Grade');
    }

    public function recipient(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Audience', 'Recipient');
    }

    public function destination(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Publisher', 'Destination');
    }

    public function models(): IndipetaePDFBuilder_MetadataField
    {
        // Space added to label to fix WKHMLToPDF line-break bug.
        return $this->getField('Subject', 'Models/Saints/ Missionaries');
    }

    public function otherNames(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Relation', 'Other names');
    }

    public function leftForMissionLands(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Date Issued', 'Left for mission lands');
    }

    public function anteriorDesire(): IndipetaePDFBuilder_MetadataField
    {
        return $this->getField('Medium', 'Anterior desire');
    }

    protected function getField(string $field, string $label): IndipetaePDFBuilder_MetadataField
    {
        $values = metadata($this->item, ['Dublin Core', $field], ['no_filter' => true, 'all' => true]);
        return new IndipetaePDFBuilder_MetadataField($label, $values);
    }
}