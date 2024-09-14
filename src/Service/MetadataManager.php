<?php
namespace App\Service;

use App\Entity\Track;

class MetadataManager
{
    public function update_title(Track $track) {
        $output = [];
        $returnVar = 0;
        $command = 'kid3-cli -c "set title \"' . $track->getTitle() . '\"" ' . $track->getPath();
        exec($command, $output, $returnVar);
        if ($returnVar) {
            throw new \Exception("Could not set title command '" . $command . "' failed");
        }
    }
}

