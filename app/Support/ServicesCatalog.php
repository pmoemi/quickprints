<?php

namespace App\Support;

use App\Models\ServiceItem;

/**
 * Default services catalogue — aligned with QuickPrints invoice branding:
 * Signages | Branding | Printing
 */
class ServicesCatalog
{
    /** @return array<string, list<string>> */
    public static function all(): array
    {
        return [
            'Signages' => [
                '3D Non Illuminated Signage',
                '3D Illuminated Signage',
                'LED Illuminated Signs',
                'Acrylic Letters & Logos',
                'Metal Letters',
                'Pylon Signs',
                'Fascia Boards',
                'Directional Signs',
                'Billboards',
                'Totems',
                'Pavement Signs',
                'Safety Signs & Wayfinding',
                'Shop Front Signage',
                'Reception Wall Branding',
                'Window Graphics',
                'One-Way Vision',
                'Building Wraps',
                'Backlit Signage',
                'Neon-Style LED Signs',
                'Menu Boards',
            ],
            'Branding' => [
                'Full Vehicle Wrap',
                'Partial Vehicle Wrap',
                'Vehicle Decals',
                'Fleet Branding',
                'Motorbike Branding',
                'Boat Branding',
                'Business Cards',
                'Letterheads',
                'Company Profiles',
                'Brochures',
                'Flyers',
                'Branded Pens',
                'Branded Mugs',
                'Branded Bags',
                'T-Shirt Printing',
                'Polo Shirts',
                'Hoodies & Caps',
                'Work Uniforms',
                'School Uniforms',
                'Exhibition Stands',
                'Event Backdrops',
                'Stage Branding',
                'Logo Design',
            ],
            'Printing' => [
                'Vinyl Banners',
                'Mesh Banners',
                'Canvas Prints',
                'Poster Prints',
                'Pop-Up Displays',
                'Roll-Up Banners',
                'Wallpapers & Murals',
                'Floor Graphics',
                'Fabric Printing',
                'Perforated Vinyl',
                'Large Format Printing',
                'Business Stationery',
                'Envelopes & Folders',
                'ID Cards',
                'Loyalty Cards',
                'Calendars',
                'Diaries',
                'Digital Ads',
                'Social Media Graphics',
                'Website Banners',
            ],
        ];
    }

    public static function totalItems(): int
    {
        return array_sum(array_map('count', self::all()));
    }

    /** Add catalogue services that are not already in the database (safe to run anytime). */
    public static function seedMissing(): int
    {
        $nextId = (int) (ServiceItem::query()->max('id') ?? 0);
        $added = 0;

        foreach (self::all() as $category => $names) {
            foreach ($names as $sortOrder => $name) {
                $exists = ServiceItem::query()
                    ->where('category', $category)
                    ->where('name', $name)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $nextId++;
                ServiceItem::query()->create([
                    'id' => $nextId,
                    'category' => $category,
                    'name' => $name,
                    'sort_order' => $sortOrder,
                ]);
                $added++;
            }
        }

        return $added;
    }
}
