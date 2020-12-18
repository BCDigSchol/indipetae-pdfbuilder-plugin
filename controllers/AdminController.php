<?php

class IndipetaePdfBuilder_AdminController extends Omeka_Controller_AbstractActionController
{
    public function rebuildAction(): void
    {
        $form = new Elasticsearch_Form_Server();

        $this->view->form = new IndipetaePdfBuilder_Form_Rebuild();

        if ($this->_request->isPost()) {
            $this->sendRebuildJob();
        } else {
            _log('Did not send job');
        }

        $this->showForm();
    }

    private function showForm(): void
    {
        $jobs = IndipetaePdfBuilder_Helper_Admin::getBuildJobs();
        $this->view->assign("jobs", $jobs);
        $this->view->form = new IndipetaePdfBuilder_Form_Rebuild();
    }

    private function sendRebuildJob(): void
    {
        try {
            $job_dispatcher = Zend_Registry::get('job_dispatcher');
            $job_dispatcher->setUser(current_user());
            $job_dispatcher->sendLongRunning('IndipetaePdfBuilder_Job_BuildAll');
            $this->_helper->flashMessenger(__('Rebuilding started.'), 'success');
        } catch (Exception $err) {
            $this->_helper->flashMessenger($err->getMessage(), 'error');
        }
    }
}