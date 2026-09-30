<?php

// Isabela Ruiz, Nicolas Ortiz

namespace Database\Seeders;

use App\Models\User;
use App\Models\Watch;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Usuario Demo',
            'email' => 'demo@example.com',
            'password' => 'password',
        ]);

        User::factory()->admin()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $watches = [
            ['Casio F-91W', 'Casio', 'Clásico digital con cronómetro, alarma diaria y correa de resina.', 95000, 25, 'img/watches/casio-f91w.svg'],
            ['Casio G-Shock GA-2100', 'Casio', 'Caja octagonal "CasiOak", resistente a golpes y a 200 m de agua.', 420000, 12, 'img/watches/casio-gshock-ga2100.svg'],
            ['Seiko 5 SNK809', 'Seiko', 'Automático con esfera azul, calendario día/fecha y correa de lona.', 650000, 8, 'img/watches/seiko-5-snk809.svg'],
            ['Seiko Presage Cocktail', 'Seiko', 'Automático de vestir con esfera degradé y cristal de zafiro.', 1800000, 5, 'img/watches/seiko-presage-cocktail.svg'],
            ['Citizen Eco-Drive BM7100', 'Citizen', 'Carga solar, sin baterías, con correa de acero inoxidable.', 980000, 9, 'img/watches/citizen-ecodrive-bm7100.svg'],
            ['Citizen Promaster Diver', 'Citizen', 'Reloj de buceo Eco-Drive, hermético hasta 200 metros.', 1500000, 6, 'img/watches/citizen-promaster-diver.svg'],
            ['Orient Bambino V2', 'Orient', 'Automático elegante con cristal abovedado y esfera champán.', 720000, 10, 'img/watches/orient-bambino-v2.svg'],
            ['Orient Kamasu', 'Orient', 'Diver automático con bisel de cerámica y 200 m de resistencia.', 890000, 7, 'img/watches/orient-kamasu.svg'],
            ['Tissot PRX Powermatic 80', 'Tissot', 'Estilo años 70, automático con 80 horas de reserva de marcha.', 2900000, 4, 'img/watches/tissot-prx-powermatic80.svg'],
            ['Tissot Gentleman', 'Tissot', 'Caja de acero con zafiro, ideal para uso diario y formal.', 3400000, 3, 'img/watches/tissot-gentleman.svg'],
            ['Timex Weekender', 'Timex', 'Analógico casual con correa NATO intercambiable e Indiglo.', 280000, 20, 'img/watches/timex-weekender.svg'],
            ['Fossil Grant Chrono', 'Fossil', 'Cronógrafo de cuarzo con correa de cuero y estilo vintage.', 540000, 11, 'img/watches/fossil-grant-chrono.svg'],
        ];

        foreach ($watches as [$name, $brand, $description, $price, $stock, $image]) {
            Watch::create(compact('name', 'brand', 'description', 'price', 'stock', 'image'));
        }
    }
}
