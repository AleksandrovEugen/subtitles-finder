<?php

namespace App\Services;

use App\Models\Fragment;
use App\Models\Video;

final class FragmentService
{
    public function createFragments(Video $video): void
    {
        if (! empty($video->subtitles)) {
            $fieldName = 'subtitles';
            $isGenerated = false;
        } elseif (! empty($video->subtitles_autogenerated)) {
            $fieldName = 'subtitles_autogenerated';
            $isGenerated = true;
        } else {
            return;
        }

        $fragments = $this->splitSubtitles($video->$fieldName);

        foreach ($fragments as $fragment) {
            Fragment::create([
                'video_id' => $video->id,
                'time_string' => $fragment['time_string'],
                'text' => $fragment['text'],
                'is_autogenerated' => $isGenerated,
            ]);
        }
    }

    public function deleteFragments(Video $video): void
    {
        Fragment::where('video_id', $video->id)->delete();
    }

    public function reindexVideo(Video $video): void
    {
        $this->deleteFragments($video);
        $this->createFragments($video);
    }

    protected function splitSubtitles(string $text): array
    {
        $lines = explode("\n", $text);
        $result = [];
        $result[] = [
            'time_string' => '00:00:00.000',
            'text' => '',
        ];

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line) || preg_match('/^WEBVTT$|^Kind:|Language:|Редактор субтитров/iu', $line)) {
                continue;
            }

            $timeString = '';

            if (preg_match('/(\d{2}:\d{2}(\:\d{2})?\.\d{3}) --> (\d{2}:\d{2}(\:\d{2})?\.\d{3})/', $line, $matches)) {
                $timeString = $matches[1];
            }

            $prevItem = $result[count($result) - 1];

            $isPrevTextFinished = preg_match('/[.!?]$/', $prevItem['text']);

            if (! empty($timeString) && ! $isPrevTextFinished) {
                continue;
            } elseif (empty($timeString) && ! $isPrevTextFinished) {
                $result[count($result) - 1]['text'] .= ' '.$line;
                $result[count($result) - 1]['text'] = trim($result[count($result) - 1]['text']);
            } elseif (empty($timeString) && $isPrevTextFinished) {
                $result[] = ['time_string' => '', 'text' => $line];
            } elseif (! empty($timeString) && $isPrevTextFinished) {
                $result[] = ['time_string' => $timeString, 'text' => ''];
            }
        }

        // remove last item if text is empty
        if (empty($result[count($result) - 1]['text'])) {
            array_pop($result);
        }

        return $result;
    }
}
