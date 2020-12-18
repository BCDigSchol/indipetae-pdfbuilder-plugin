<?php


class IndipetaePdfBuilder_Job_BuildAll extends Omeka_Job_AbstractJob
{
    public function perform(): void
    {
        _log("Starting rebuild all job...");

        $items = $this->_db->getTable('Item')->findAll();
        $builder = IndipetaePdfBuilder_Factory::getBuilder();

        foreach ($items as $item) {
            $letter = new IndipetaePdfBuilder_Model_Letter($item);
            IndipetaePdfBuilder_Factory::getBuilder()->build($letter);
        }

        sleep(1);

        _log("Completed rebuild all job...");
    }

}