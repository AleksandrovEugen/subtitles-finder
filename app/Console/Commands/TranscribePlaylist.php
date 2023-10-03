<?php

namespace App\Console\Commands;

use App\Models\Playlist;
use App\Models\Video;
use App\Services\FragmentService;
use App\Services\WhisperService;
use Illuminate\Console\Command;

class TranscribePlaylist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:transcribe-playlist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate subtitles for all videos in playlist using Whisper';

    /**
     * Execute the console command.
     */
    public function handle(WhisperService $whisperService, FragmentService $fragmentService)
    {
        $playlistTitle = $this->choice('Which playlist should synchronize?',
            Playlist::all()->pluck('title', 'id')->toArray()
        );

        $playlist = Playlist::where('title', $playlistTitle)->first();

        $videos = Video::where('playlist_id', $playlist->id)
            ->whereNull('subtitles')
            ->whereNull('subtitles_autogenerated')
            ->orderBy('id')
            ->get();

        $progressBar = $this->output->createProgressBar(count($videos));
        $progressBar->start();

        foreach ($videos as $video) {
            $progressBar->advance();

            $subtitles = $whisperService->transcribe($video->youtube_id, 'ru', $this->output);

            if ($subtitles) {
                $video->update(['subtitles_autogenerated' => $subtitles]);
                $this->info('Transcribed video - '.$video->title);
            }

            $fragmentService->createFragments($video);
        }

        $progressBar->finish();
    }
}
