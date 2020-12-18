<?php

class IndipetaePdfBuilder_Model_Letter
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
        return (string)$this->item->id;
    }

    public function transcription(): string
    {
        $transcription_field = $this->getField('Description', 'Transcription')->getValues();
        return $transcription_field[0] ?? '';
    }

    public function transcriptionBack(): string
    {
        $transcription_field = $this->getField('Description', 'Transcription')->getValues();
        return $transcription_field[0] ?? '';
    }

    /**
     * Get an array of metadata fields to display
     *
     * Each field has a label and a value. Change the order of the fields in the array to change the order that
     * they render in the PDF.
     *
     * @return IndipetaePdfBuilder_MetadataField[]
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
            $this->notes(),
            $this->collection(),
        ];
    }

    public function callNumber(): IndipetaePdfBuilder_MetadataField
    {
        $archive_values = $this->getField('Identifier', 'Archive')->getValues();
        $folder_values = $this->getField('Has Format', 'Folder')->getValues();
        $number_values = $this->getField('Has Version', 'Number')->getValues();

        $archive = $archive_values[0] ?? '';
        $folder = $folder_values[0] ?? '';
        $number = $number_values[0] ?? '';
        return new IndipetaePdfBuilder_MetadataField('Call Number', ["$archive, $folder, $number"]);
    }

    public function collection(): IndipetaePdfBuilder_MetadataField
    {
        $collection_name = '';
        if ($collection = $this->item->getCollection()) {
            $collection_name = metadata($collection, array('Dublin Core', 'Title'));
        }
        return new IndipetaePdfBuilder_MetadataField('Collection', [$collection_name]);
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
        _log('adding file...');
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

    public function transcribedBy(): IndipetaePdfBuilder_MetadataField
    {
        $transcribed_by_values = $this->getField('Contributor', 'Transcribed by');
        $transcriber_string = implode(', ', $transcribed_by_values->getValues());

        return new IndipetaePdfBuilder_MetadataField('Transcribed by', [$transcriber_string]);
    }

    public function date(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Date', 'Date');
    }

    public function from(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Coverage', 'From');
    }

    public function to(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Spatial Coverage', 'To');
    }

    public function sender(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Creator', 'Sender');
    }

    public function grade(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Replaces', 'Grade');
    }

    public function recipient(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Audience', 'Recipient');
    }

    public function destination(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Publisher', 'Destination');
    }

    public function models(): IndipetaePdfBuilder_MetadataField
    {
        // Space added to label to fix WKHMLToPDF line-break bug.
        return $this->getField('Subject', 'Models/Saints/ Missionaries');
    }

    public function otherNames(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Relation', 'Other names');
    }

    public function leftForMissionLands(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Date Issued', 'Left for mission lands');
    }

    public function anteriorDesire(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Medium', 'Anterior desire');
    }

    protected function notes(): IndipetaePdfBuilder_MetadataField
    {
        return $this->getField('Abstract', 'Notes');
    }

    protected function getField(string $field, string $label): IndipetaePdfBuilder_MetadataField
    {
        $values = metadata($this->item, ['Dublin Core', $field], ['no_filter' => true, 'all' => true]);
        return new IndipetaePdfBuilder_MetadataField($label, $values);
    }
}