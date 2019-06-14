<?php

class IndipetaePDFBuilder_Job_Build extends Omeka_Job_AbstractJob
{
    public function perform(): void
    {
        $item_id = $this->_options['item_id'];
        $item = $this->_db->getTable('Item')->find($item_id);
        if ($item) {
            $letter = new IndipetaePDFBuilder_Model_Letter($item);
            IndipetaePDFBuilder_Factory::getBuilder()->build($letter);
        } else {
            _log("IndipetaePDFBuilder_Job_Build: could not find item $item_id", Zend_Log::ERR);
        }
    }
}