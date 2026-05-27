<?php

namespace App\Support;

class ServicesCatalog
{
    /** @return array<string, list<string>> */
    public static function all(): array
    {
        return [
            'Large Format Printing' => ['Vinyl Banners', 'Mesh Banners', 'Canvas Prints', 'Wallpapers', 'Floor Graphics', 'Window Graphics', 'Building Wraps', 'Backlit Prints', 'Poster Prints', 'One-Way Vision', 'Perforated Vinyl', 'Fabric Prints', 'Pop-Up Displays'],
            'Signage & Branding' => ['Acrylic Letters', 'Metal Letters', 'LED Illuminated Signs', 'Pylon Signs', 'Directional Signs', 'Fascia Boards', 'Billboards', 'Totems', 'Pavement Signs', 'Safety Signs'],
            'Vehicle Branding' => ['Full Vehicle Wrap', 'Partial Vehicle Wrap', 'Vehicle Decals', 'Fleet Branding', 'Motorbike Branding', 'Boat Branding', 'Branded Covers'],
            'Corporate Branding' => ['Letterheads', 'Business Cards', 'Envelopes', 'Folders', 'Company Profiles', 'ID Cards', 'Loyalty Cards', 'Brochures', 'Flyers'],
            'Promotional Items' => ['Branded Pens', 'Mugs', 'Notebooks', 'Bags', 'USB Drives', 'Keychains', 'Calendars', 'Diaries', 'Branded Umbrellas'],
            'Apparel & Uniforms' => ['T-Shirt Printing', 'Polo Shirts', 'Hoodies', 'Caps', 'Reflective Jackets', 'Work Uniforms', 'School Uniforms', 'Sports Kits'],
            'Fabrication & Metalwork' => ['Steel Fabrication', 'Aluminium Fabrication', 'Stainless Steel Signs', 'Gate Fabrication', 'Shelving', 'Display Stands', 'Frames'],
            'Events & Exhibitions' => ['Exhibition Stands', 'Pop-Up Banners', 'Event Backdrops', 'Stage Branding', 'Table Covers', 'Crowd Barriers', 'Event Programs'],
            'Photography & Media' => ['Product Photography', 'Corporate Headshots', 'Event Photography', 'Videography', 'Drone Photography', 'Photo Editing'],
            'Digital Services' => ['Social Media Graphics', 'Email Templates', 'Digital Ads', 'Website Banners', 'Motion Graphics', 'Logo Design'],
        ];
    }
}
