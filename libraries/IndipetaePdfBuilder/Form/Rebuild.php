<?php


class IndipetaePdfBuilder_Form_Rebuild extends Omeka_Form
{
    public function init() {
        parent::init();

        $this->addElement('submit', 'submit', array(
            'label' => __('Rebuild PDFs')
        ));

        $this->addDisplayGroup(array('submit'), 'submit_button');
    }
}