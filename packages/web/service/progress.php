<?php
require_once('../commons/base.inc.php');
try {
    $Host = $FOGCore->getHostItem(false);
    // get the task
    $Task = $Host->get('task');
    if (!$Task->isValid()) throw new Exception(sprintf('%s: %s (%s)', _('No Active Task found for Host'), $Host->get('name'),$MACAddress));
    // get the image (for image size)
    $Image = $Task->getImage();
    if (!$Image->isValid()) throw new Exception(_('Invalid image'));
    // break apart the received data
    $str = explode('@',base64_decode($_REQUEST['status']));
    // The types that get progress info: Down (1), Up (2), MultiCast (8), Down Debug (15), Up Debug (16), Down No Snap (17)
    $imagingTasks = in_array($Task->get('typeID'),array(1,2,8,15,16,17,24));
    if ($imagingTasks) {
        // If the subsets all exist, write the data, otherwise leave it alone.
        if ($str[0] && $str[1] && $str[2] && $str[3] && $str[4] && $str[5]) {
            $Task->set('bpm', $str[0])
                ->set('timeElapsed', $str[1])
                ->set('timeRemaining', $str[2])
                ->set('dataCopied', $str[3])
                ->set('dataTotal', $str[4])
                ->set('percent',trim($str[5]))
                ->set('pct',trim($str[5]))
                ->save();
            // Suppose I could just add the data together, but easier to just
            // Use the largest partition on the system as the file representation.
            if ($str[6] > (int)$Image->get('size'))
                $Image->set('size',$str[6])->save();
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
