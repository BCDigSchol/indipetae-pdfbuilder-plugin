<?php

require_once __DIR__ . '/vendor/autoload.php';

class IndipetaePDFBuilderPlugin extends Omeka_Plugin_AbstractPlugin
{
    public $_hooks = ['after_save_item'];

    public function hookAfterSaveItem(array $args): void
    {
        _log('Sending build job for PDF: ' . $args['record']->id, ZEND_LOG::INFO);

        // Remove any old PDFs.
        $letter = new IndipetaePDFBuilder_Model_Letter($args['record']);
        $letter->removePDF('indipetae-transcription');

        // Dispatch the job.
        $job_dispatcher = Zend_Registry::get('job_dispatcher');
        $job_dispatcher->setUser(current_user());
        $job_dispatcher->sendLongRunning(IndipetaePDFBuilder_Job_Build::class, ['item_id' => $args['record']->id]);
    }
}