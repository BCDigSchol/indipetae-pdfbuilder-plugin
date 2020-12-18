<?php echo head(array(
    'title' => __('PDF Builder | Rebuild PDFs')
)); ?>

<div id="primary">
    <?php echo flash(); ?>
    <p><?php echo __('Click the button to rebuild all PDFs.') ?></p>
    <?php echo $form ?>

    <h2>Rebuild jobs</h2>
    <table>
        <thead>
        <th>Job ID</th>
        <th>User ID</th>
        <th>Status</th>
        <th>Started</th>
        <th>Stopped</th>
        </thead>
    <?php $jobInProgress = false; ?>
    <?php foreach($jobs as $job): ?>
        <?php if(in_array($job->status, array('starting', 'in progress'))) { $jobInProgress = true; } ?>
        <tr class="<?php echo $jobInProgress ? 'jobinprogress' : ''; ?>">
            <td><?php echo $job->id; ?></td>
            <td><?php echo $job->user_id; ?></td>
            <td><?php echo $job->status; ?></td>
            <td><?php echo $job->started; ?></td>
            <td><?php echo $job->stopped; ?></td>
        </tr>
    <?php endforeach; ?>
    </table>

</div>

<?php echo foot(); ?>
