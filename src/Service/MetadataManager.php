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

    public function update_artist(Track $track) {
        $artists = $track->getArtistTracks();
        if ($artists->count() < 1) {
            throw new \Exception("No artists to this track");
        }
        $artist = $artists->get(0)->getArtist();
        $output = [];
        $returnVar = 0;
        $command = 'kid3-cli -c "set artist \"' . $artist->getName() . '\"" ' . $track->getPath();
        exec($command, $output, $returnVar);
        if ($returnVar) {
            throw new \Exception("Could not set title command '" . $command . "' failed");
        }
    }

    public function update_metadata(Track $track) {
        $this->update_title($track);
        $this->update_artist($track);
    }
}

