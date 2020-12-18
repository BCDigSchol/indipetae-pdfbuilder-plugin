<?php

class IndipetaePdfBuilder_Job_Build extends Omeka_Job_AbstractJob
{
    public function perform(): void
    {
        _log("Starting build job...", Zend_Log::INFO);

        // Wait five seconds to make sure file is saved.
        sleep(5);

        $item_id = $this->_options['item_id'];
        $item = $this->_db->getTable('Item')->find($item_id);
        if ($item) {
            $letter = new IndipetaePdfBuilder_Model_Letter($item);
            IndipetaePdfBuilder_Factory::getBuilder()->build($letter);
            _log("Dispatched build for $item_id", Zend_Log::INFO);
        } else {
            _log("IndipetaePdfBuilder_Job_Build: could not find item $item_id", Zend_Log::ERR);
        }
    }
}