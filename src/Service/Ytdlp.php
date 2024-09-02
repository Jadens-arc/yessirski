<?php
namespace App\Service;

use App\Entity\Track;

class Ytdlp
{
    /**
     * @throws \Exception
     */
    public function validate_url(string $url): void
    {
        if (!$url) {
            throw new \Exception("Please provide a URL");
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception("Not a valid URL");
        }
    }

    public function get_video_title(string $url): string
    {
        $this->validate_url($url);
        $output = [];
        $returnVar = 0;
        exec('yt-dlp --get-title ' . $url . ' 2>/dev/null', $output, $returnVar);
        if ($returnVar || !sizeof($output)) {
            throw new \Exception("Could not parse content");
        }
        return $output[0];
    }


    public function download_video(Track $track, $url): bool
    {

        $command = 'yt-dlp -x --audio-format mp3 -o ' . $track->getPath() . " " . $url . ' 2>/dev/null';
        $output = [];
        $returnVar = 0;

        // Execute the command
        exec($command, $output, $returnVar);
        return !$returnVar;
    }
}