<?php

require_once __DIR__ . '/vendor/autoload.php';

class IndipetaePdfBuilderPlugin extends Omeka_Plugin_AbstractPlugin
{
    public $_hooks = ['after_save_item'];

    protected $_filters = ['admin_navigation_main'];

    public function hookAfterSaveItem(array $args): void
    {
        _log('Sending build job for PDF: ' . $args['record']->id, ZEND_LOG::INFO);

        // Remove any old PDFs.
        $letter = new IndipetaePdfBuilder_Model_Letter($args['record']);
        $letter->removePDF('indipetae-transcription');

        // Dispatch the job.
        $job_dispatcher = Zend_Registry::get('job_dispatcher');
        $job_dispatcher->setUser(current_user());
        $job_dispatcher->sendLongRunning(IndipetaePdfBuilder_Job_Build::class, ['item_id' => $args['record']->id]);
    }


    /**
     * Admin navigation to PDF rebuild link
     *
     * @param $nav
     * @return mixed
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Rebuild PDFs'),
            'uri' => url('indipetae-pdf-builder/admin/rebuild'),
        );

        return $nav;
    }
}