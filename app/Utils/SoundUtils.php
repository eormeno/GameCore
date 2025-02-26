<?php
declare(strict_types=1);

namespace App\Utils;

/**
 * https://24daysindecember.net/2022/12/12/creating-music-with-php/
 */

class WaveFile
{
    private const RIFF = 'RIFF';
    private const WAVE = 'WAVE';
    private const FMT_CHUNK_MARKER = 'fmt ';
    private const FMT_CHUNK_SIZE = 16;
    private const FORMAT_TYPE = 1; // PCM
    private const CHANNELS = 1;
    private const DATA_CHUNK_MARKER = 'data';
    private const TOTAL_HEADER_SIZE = 44;
    private const BITS_PER_SAMPLE = 16;
    private const BUFFER_SIZE = 16384;

    private $file;

    private int $sample_rate;

    private string $buffer = '';

    public function __construct(string $file, int $sample_rate)
    {
        $this->file = fopen($file, "w");
        $this->sample_rate = $sample_rate;
        $this->create_fmt_chunk();
        $this->create_data_chunk();
    }

    private function write_string(string $string): void
    {
        fwrite($this->file, pack('a*', $string), strlen($string));
    }

    private function write_short(int $value): void
    {
        fwrite($this->file, pack('v', $value), 2);
    }

    private function write_long(int $value): void
    {
        fwrite($this->file, pack('V', $value), 4);
    }

    public function write_sample(int $value): void
    {
        $this->buffer .= pack('s', $value);
        $this->flush_buffer(false);
    }

    private function flush_buffer(bool $force): void
    {
        if ('' === $this->buffer) {
            return;
        }

        $current_buffer_size = strlen($this->buffer);

        if (!$force && ($current_buffer_size < static::BUFFER_SIZE)) {
            return;
        }

        fwrite($this->file, $this->buffer, $current_buffer_size);
        $this->buffer = '';
    }

    private function create_fmt_chunk(): void
    {
        $this->write_string(static::RIFF);
        $this->write_long(0); // placeholder (file size)
        $this->write_string(static::WAVE);
        $this->write_string(static::FMT_CHUNK_MARKER);
        $this->write_long(static::FMT_CHUNK_SIZE);
        $this->write_short(static::FORMAT_TYPE);
        $this->write_short(static::CHANNELS);
        $this->write_long($this->sample_rate);
        $this->write_long($this->sample_rate * static::BITS_PER_SAMPLE * static::CHANNELS / 8);
        $this->write_short(static::BITS_PER_SAMPLE * static::CHANNELS / 8);
        $this->write_short(static::BITS_PER_SAMPLE);
    }

    private function create_data_chunk(): void
    {
        $this->write_string(static::DATA_CHUNK_MARKER);
        $this->write_long(0); // placeholder (data size)
    }

    private function update_sizes(): void
    {
        $file_size = fstat($this->file)['size'];
        fseek($this->file, 4, SEEK_SET);
        $this->write_long($file_size);

        fseek($this->file, 40, SEEK_SET);
        $this->write_long((int)($file_size - static::TOTAL_HEADER_SIZE));
    }

    public function get_max_absolute_sample_value(): int
    {
        return 2 ** (static::BITS_PER_SAMPLE - 1) - 1;
    }

    public function get_time_between_samples(): float
    {
        return 1 / $this->sample_rate;
    }

    public function __destruct()
    {
        $this->flush_buffer(true);
        $this->update_sizes();
        fclose($this->file);
    }
}

class Note
{
    private const PITCH_TO_FREQUENCY = [
        'C' => 261.625565300598634,
        'C#' => 277.182630976872096,
        'D' => 293.664767917407560,
        'D#' => 311.126983722080910,
        'E' => 329.627556912869929,
        'F' => 349.228231433003884,
        'F#' => 369.994422711634398,
        'G' => 391.995435981749294,
        'G#' => 415.304697579945138,
        'A' => 440,
        'A#' => 466.163761518089916,
        'B' => 493.883301256124111,
    ];

    private bool $is_pause = false;
    private float $duration;

    private float $frequency;

    public function is_pause(): bool
    {
        return $this->is_pause;
    }

    public function convert_to_pause(): void
    {
        $this->is_pause = true;
    }

    public function get_duration(): float
    {
        return $this->duration;
    }

    public function set_duration(float $duration): void
    {
        $this->duration = $duration;
    }

    public function set_pitch(string $pitch): void
    {
        if (!isset(static::PITCH_TO_FREQUENCY[$pitch])) {
            throw new \InvalidArgumentException('Invalid pitch.');
        }

        $this->frequency = static::PITCH_TO_FREQUENCY[$pitch];
    }

    public function get_frequency(): float
    {
        return $this->frequency;
    }
}

class Melody implements \Iterator
{
    private float $tempo;

    private array $notes = [];

    private int $current_index = 0;

    public function __construct(float $tempo)
    {
        $this->tempo = $tempo;
    }

    private function duration_to_seconds(float $duration): float
    {
        return 60 * 4 / $this->tempo / $duration;
    }

    public function add_note(string $pitch, float $duration): void
    {
        $note = new Note();
        $note->set_pitch($pitch);
        $note->set_duration($this->duration_to_seconds($duration));
        $this->notes[] = $note;
    }

    public function add_pause(float $duration): void
    {
        $note = new Note();
        $note->set_duration($this->duration_to_seconds($duration));
        $note->convert_to_pause();
        $this->notes[] = $note;
    }

    public function rewind(): void
    {
        $this->current_index = 0;
    }

    public function current(): mixed
    {
        return $this->notes[$this->current_index];
    }

    public function key(): mixed
    {
        return $this->current_index;
    }

    public function next(): void
    {
        ++$this->current_index;
    }

    public function valid(): bool
    {
        return isset($this->notes[$this->current_index]);
    }
}

interface VirtualInstrument
{
    public function get_sample_value(float $frequency, float $current_time): float;
}

class SineWaveSynth implements VirtualInstrument
{
    public function get_sample_value(float $frequency, float $current_time): float
    {
        return sin(2 * M_PI * $frequency * $current_time);
    }
}

class Track
{
    private Melody $melody;
    private VirtualInstrument $virtual_instrument;

    public function __construct(Melody $melody, VirtualInstrument $virtual_instrument)
    {
        $this->melody = $melody;
        $this->virtual_instrument = $virtual_instrument;
    }

    public function export_to(WaveFile $wave_file)
    {
        $time_between_samples = $wave_file->get_time_between_samples();
        $max_sample_value = $wave_file->get_max_absolute_sample_value();

        /** @var Note $note */
        foreach ($this->melody as $note) {
            for ($current_time = 0; $current_time < $note->get_duration(); $current_time += $time_between_samples) {
                if ($note->is_pause()) {
                    $wave_file->write_sample(0);
                    continue;
                }

                $normalized_sample_value = $this->virtual_instrument->get_sample_value($note->get_frequency(), $current_time);
                $sample_value = $normalized_sample_value * $max_sample_value * 0.9;
                $sample_value = (int) ceil($sample_value);
                $wave_file->write_sample($sample_value);
            }
        }
    }
}

class SoundUtils
{
    public static function fakeSound(string $path, array $resource)
    {
        $filename = 'melody.wav';
        $filepath = __DIR__ . DIRECTORY_SEPARATOR . $filename;
        $sample_rate = 48000;

        $wave_file = new WaveFile($filepath, $sample_rate);
        $sine_wave_synth = new SineWaveSynth();

        $beats_per_minute = 113;
        $melody = new Melody($beats_per_minute);

        $melody->add_note('C', 16);
        $melody->add_note('D', 16);
        $melody->add_note('F', 16);
        $melody->add_note('D', 16);

        $melody->add_note('A', 16);
        $melody->add_pause(8);
        $melody->add_note('A', 16);
        $melody->add_pause(8);
        $melody->add_note('G', 4);
        $melody->add_pause(8);

        $melody->add_note('C', 16);
        $melody->add_note('D', 16);
        $melody->add_note('F', 16);
        $melody->add_note('D', 16);

        $melody->add_note('G', 16);
        $melody->add_pause(8);
        $melody->add_note('G', 16);
        $melody->add_pause(8);
        $melody->add_note('F', 4);
        $melody->add_pause(8);

        $melody->add_note('C', 16);
        $melody->add_note('D', 16);
        $melody->add_note('F', 16);
        $melody->add_note('D', 16);

        $melody->add_note('F', 8);
        $melody->add_pause(8);
        $melody->add_note('G', 8);
        $melody->add_note('E', 4);
        $melody->add_note('D', 8);
        $melody->add_pause(8);
        $melody->add_note('C', 8);
        $melody->add_note('G', 8);
        $melody->add_pause(8);
        $melody->add_note('F', 8);
        $melody->add_pause(8);
        $melody->add_pause(4);

        $track = new Track($melody, $sine_wave_synth);
        $track->export_to($wave_file);

        echo sprintf("Success!\n%s has been created.\n", $filepath);

    }
}
