<?php

namespace Database\Seeders;

use App\Models\Apartment;
use Illuminate\Database\Seeder;

class ApartmentSeeder extends Seeder
{
    public function run(): void
    {
        $buildings = [
            'A' => ['Narra Hall', '12 Mabini Street, Barangay San Antonio, Pasig City'],
            'B' => ['Molave Hall', '48 Rizal Avenue, Barangay Poblacion, Makati City'],
            'C' => ['Acacia Hall', '7 Bonifacio Drive, Barangay Malate, Manila'],
        ];

        $units = [
            ['A-102', 1, 'Studio', 1, 1, 6500, 'available', 'Open-plan studio with a kitchenette, private bath, and a window facing the courtyard.'],
            ['A-103', 2, 'Studio', 1, 1, 6800, 'available', 'Freshly repainted studio with split-type air-conditioning and built-in storage.'],
            ['A-104', 2, '1 Bedroom', 1, 1, 9500, 'available', 'One-bedroom unit with a separate living area and a small service balcony.'],
            ['A-105', 3, '1 Bedroom', 1, 1, 9800, 'reserved', 'Corner one-bedroom with city view, semi-furnished, near the elevator lobby.'],
            ['A-106', 3, '2 Bedroom', 2, 1, 13500, 'available', 'Two-bedroom unit with a dining nook, one air-conditioner, and a utility area.'],
            ['A-107', 4, '2 Bedroom', 2, 2, 15000, 'available', 'Two-bedroom with two baths, a balcony, and one covered parking slot.'],
            ['A-108', 4, '3 Bedroom', 3, 2, 19500, 'available', 'Three-bedroom corner unit with a maid\'s quarter and two air-conditioners.'],
            ['A-109', 5, '3 Bedroom', 3, 2, 21000, 'maintenance', 'Three-bedroom unit undergoing repainting and plumbing repair until next month.'],
            ['A-110', 5, 'Penthouse', 4, 3, 30000, 'available', 'Top-floor penthouse with a wraparound terrace and a private lift lobby.'],

            ['B-102', 1, 'Studio', 1, 1, 7000, 'available', 'Ground-floor studio with a private entrance and a small drying area.'],
            ['B-103', 1, 'Studio', 1, 1, 7200, 'available', 'Studio unit with tile flooring, a kitchenette, and a walk-in shower.'],
            ['B-104', 2, '1 Bedroom', 1, 1, 10200, 'available', 'One-bedroom with a study nook, split-type air-conditioning, and a balcony.'],
            ['B-105', 2, '1 Bedroom', 1, 1, 10500, 'reserved', 'One-bedroom facing the pool, fully air-conditioned, with one parking slot.'],
            ['B-106', 3, '2 Bedroom', 2, 1, 14000, 'available', 'Two-bedroom unit with an open kitchen, a dining area, and a service balcony.'],
            ['B-107', 3, '2 Bedroom', 2, 2, 15800, 'available', 'Two-bedroom with two baths, a walk-in closet, and city-facing windows.'],
            ['B-108', 4, '2 Bedroom', 2, 2, 16200, 'available', 'Renovated two-bedroom with new fixtures, two air-conditioners, and a balcony.'],
            ['B-109', 4, '3 Bedroom', 3, 2, 22000, 'available', 'Three-bedroom family unit with a maid\'s room, two baths, and two parking slots.'],
            ['B-110', 5, '3 Bedroom', 3, 2, 23500, 'maintenance', 'Three-bedroom unit with a leaking ceiling under repair; not yet bookable.'],
            ['B-111', 5, 'Penthouse', 4, 3, 32000, 'available', 'Penthouse with a private roof deck, three baths, and a two-car garage.'],

            ['C-005', 1, 'Studio', 1, 1, 7500, 'available', 'Studio near the lobby with a kitchenette, private bath, and tiled floors.'],
            ['C-006', 1, 'Studio', 1, 1, 7800, 'available', 'Semi-furnished studio with a bed, wardrobe, air-conditioner, and water heater.'],
            ['C-007', 2, '1 Bedroom', 1, 1, 11000, 'available', 'One-bedroom with a living area, dining table for two, and a laundry corner.'],
            ['C-008', 2, '1 Bedroom', 1, 1, 11200, 'available', 'One-bedroom with a balcony, split-type air-conditioning, and a study desk.'],
            ['C-009', 3, '2 Bedroom', 2, 1, 14500, 'available', 'Two-bedroom with a shared bath, a dining nook, and one air-conditioner.'],
            ['C-010', 3, '2 Bedroom', 2, 2, 17000, 'available', 'Two-bedroom with two baths, a balcony, and a covered parking slot.'],
            ['C-011', 4, '2 Bedroom', 2, 2, 17500, 'reserved', 'Corner two-bedroom with park view, fully air-conditioned, and new curtains.'],
            ['C-012', 4, '3 Bedroom', 3, 2, 24000, 'available', 'Three-bedroom unit with a maid\'s quarter, two baths, and a large balcony.'],
            ['C-013', 5, '3 Bedroom', 3, 2, 25000, 'available', 'Three-bedroom penthouse-level unit with city view and two parking slots.'],
            ['C-014', 5, 'Penthouse', 5, 3, 38000, 'available', 'Five-bedroom penthouse with a wraparound terrace, three baths, and a private lift.'],
            ['C-015', 5, 'Penthouse', 4, 3, 35000, 'maintenance', 'Penthouse scheduled for terrace waterproofing; available again next month.'],
        ];

        foreach ($units as [$number, $floor, $type, $bedrooms, $bathrooms, $rent, $status, $description]) {
            [$building, $address] = $buildings[$number[0]];

            Apartment::updateOrCreate(
                ['unit_number' => $number],
                [
                    'building_name' => $building,
                    'property_address' => $address,
                    'floor' => (string) $floor,
                    'unit_type' => $type,
                    'bedrooms' => $bedrooms,
                    'bathrooms' => $bathrooms,
                    'monthly_rent' => $rent,
                    'security_deposit' => $rent * 2,
                    'status' => $status,
                    'description' => $description,
                ]
            );
        }
    }
}
