<?php
namespace App\Console\Commands;

use App\Utils\TerrainGenerator;
use Illuminate\Console\Command;

class TerrainTools extends Command
{
    protected $signature = 'terrain:generate {width} {height} {seed?}';
    protected $description = 'Generate a terrain using the ProceduralTerrainGenerator';

    protected $terrainGenerator;

    public function __construct(TerrainGenerator $terrainGenerator)
    {
        parent::__construct();
        $this->terrainGenerator = $terrainGenerator;
    }

    public function handle()
    {
        $mapWidth = $this->argument('width');
        $mapHeight = $this->argument('height');
        $seed = $this->argument('seed') ?? null;

        // Definición de un tileset completo con descripciones para cada categoría.
        $tileset = [

            'terrain' => [
                '00' => 'Terreno base con césped y tierra',
                '01' => 'Terreno con rocas pequeñas',
                '02' => 'Terreno con vegetación ligera',
                '03' => 'Terreno arenoso',
                '04' => 'Terreno con tierra oscura',
                '05' => 'Terreno con musgo',
                '06' => 'Terreno con piedras dispersas',
                '07' => 'Terreno rocoso',
                '08' => 'Terreno con hierba alta',
                '09' => 'Terreno con camino natural',
                '0A' => 'Terreno con pequeñas colinas',
                '0B' => 'Terreno con arroyos secos',
                '0C' => 'Terreno con pasto',
                '0D' => 'Terreno con tierra húmeda',
                '0E' => 'Terreno con sombra de árboles',
                '0F' => 'Terreno base mixto',
            ],
            'forest' => [
                '10' => 'Bosque denso con árboles altos',
                '11' => 'Bosque con árboles dispersos',
                '12' => 'Bosque con maleza',
                '13' => 'Bosque con troncos caídos',
                '14' => 'Bosque con arboleda',
                '15' => 'Bosque de pinos',
                '16' => 'Bosque con follaje denso',
                '17' => 'Bosque con caminos de tierra',
                '18' => 'Bosque con claros',
                '19' => 'Bosque frondoso',
                '1A' => 'Bosque con arbustos',
                '1B' => 'Bosque mixto',
                '1C' => 'Bosque de robles',
                '1D' => 'Bosque con senderos',
                '1E' => 'Bosque con vegetación densa',
                '1F' => 'Bosque con árboles de hoja caduca',
            ],
            'river' => [
                '30' => 'Río recto',
                '31' => 'Curva izquierda del río',
                '32' => 'Curva derecha del río',
                '33' => 'Río en bifurcación',
                '34' => 'Río con ligera corriente',
                '35' => 'Río con cascada pequeña',
                '36' => 'Río con orilla ancha',
                '37' => 'Río angosto',
                '38' => 'Río sinuoso',
                '39' => 'Río en zigzag',
                '3A' => 'Río con meandro',
                '3B' => 'Río con borde abrupto',
                '3C' => 'Río con vegetación ribereña',
                '3D' => 'Río cristalino',
                '3E' => 'Río en depresión',
                '3F' => 'Río final del segmento',
            ],
            'margins' => [
                '40' => 'Borde suave de transición',
                '41' => 'Orilla con piedras pequeñas',
                '42' => 'Transición de vegetación a agua',
                '43' => 'Orilla con arena y rocas',
                '44' => 'Borde con vegetación dispersa',
                '45' => 'Borde con rocas medianas',
                '46' => 'Transición con pasto',
                '47' => 'Orilla con hierba',
                '48' => 'Borde natural',
                '49' => 'Borde con musgo',
                '4A' => 'Orilla de transición',
                '4B' => 'Borde rústico',
                '4C' => 'Orilla mixta',
                '4D' => 'Transición suave',
                '4E' => 'Borde de agua con vegetación',
                '4F' => 'Borde final de transición',
            ],
            'sea' => [
                '50' => 'Mar calmo',
                '51' => 'Mar con oleaje suave',
                '52' => 'Mar con oleaje moderado',
                '53' => 'Mar agitado',
                '54' => 'Mar con espuma',
                '55' => 'Mar profundo',
                '56' => 'Mar claro',
                '57' => 'Mar con reflejos',
                '58' => 'Mar en calma',
                '59' => 'Mar con olas pequeñas',
                '5A' => 'Mar con olas grandes',
                '5B' => 'Mar turbulento',
                '5C' => 'Mar con espuma densa',
                '5D' => 'Mar con tonalidades azules',
                '5E' => 'Mar con tonalidades verdes',
                '5F' => 'Mar final del segmento',
            ],
            'beach' => [
                '60' => 'Playa de arena clara',
                '61' => 'Playa con pequeñas rocas',
                '62' => 'Playa con dunas',
                '63' => 'Playa con vegetación costera',
                '64' => 'Playa de arena oscura',
                '65' => 'Playa con conchas',
                '66' => 'Playa con sombras de palmeras',
                '67' => 'Playa amplia',
                '68' => 'Playa con acantilados bajos',
                '69' => 'Playa con olas suaves',
                '6A' => 'Playa con aguas poco profundas',
                '6B' => 'Playa con borde irregular',
                '6C' => 'Playa con formaciones rocosas',
                '6D' => 'Playa con pequeñas mareas',
                '6E' => 'Playa con vegetación dispersa',
                '6F' => 'Playa final del segmento',
            ],
            // Tiles para elementos especiales
            'bridge' => '70',  // Puente
            'road' => '80',  // Camino o ruta
        ];

        $seeds = ['elevacion1', 'rios2', 'agua3'];

        // Generación del mapa.
        $mapaGenerado = $this->terrainGenerator->generate($mapWidth, $mapHeight, $tileset, $seeds);
        // Visualización del mapa generado.
        echo "<pre>";
        foreach ($mapaGenerado as $fila) {
            echo implode(" ", $fila) . "\n";
        }
        echo "</pre>";
    }
}
