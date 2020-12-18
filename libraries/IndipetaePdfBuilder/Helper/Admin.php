<?php


class IndipetaePdfBuilder_Helper_Admin
{
    public static function getBuildJobs(array $options = []): array
    {
        $limit = 10;
        $order = 'id desc';
        $table = get_db()->getTable('Process');
        $select = $table->getSelect()->limit($limit)->order($order);
        $job_objects = $table->fetchObjects($select);

        $build_jobs = [];
        foreach ($job_objects as $job_object) {
            // Because job args are serialized to a string using some combination of PHP serialize() and json_encode(),
            // just do a simple string search rather than try to deal with that.
            if (!empty($job_object->args) && strrpos($job_object->args, 'IndipetaePdfBuilder') !== false) {
                $build_jobs[] = $job_object;
            }
        }

        return $build_jobs;
    }

    /**
     * @return Omeka_Record_AbstractRecord[]
     */
    public static function rebuildAll()
    {
        $db = get_db();
        $table = $db->getTable('Item');
        $select = $table->getSelect();
        $table->applySorting($select, 'id', 'ASC');
        return $table->fetchObjects($select);
    }
}