<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceArea;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service_areas = [
            ["service_area_title" => "Maintenance & Repairs"],
            ["service_area_title" => "Furniture & Appliances"],
            ["service_area_title" => "Logistics & Events"]
        ];

        foreach ($service_areas as $area) {
            ServiceArea::firstOrCreate($area);
        }

        $categories = [
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Electrical & Power Systems',
                'category_slug' => 'electrical-power-systems',
                'category_image' => url('assets/img/categories/aswby1587325294067.png'),
                'category_description' => 'Maintenance and repairs related to electrical and power systems.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Plumbing, Water & Sewage',
                'category_slug' => 'plumbing-water-sewage',
                'category_image' => url('assets/img/categories/i82fb1587325327961.png'),
                'category_description' => 'Plumbing services including water and sewage systems maintenance.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Civil Works & Painting',
                'category_slug' => 'civil-works-painting',
                'category_image' => url('assets/img/categories/bvadyd1587325381041.png'),
                'category_description' => 'Civil engineering services and painting works.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Locksmith',
                'category_slug' => 'locksmith',
                'category_image' => url('assets/img/categories/snrnig1612180838707.png'),
                'category_description' => 'Locksmith services for residential and commercial properties.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Air Conditioning / HVAC Systems',
                'category_slug' => 'air-conditioning-hvac-systems',
                'category_image' => url('assets/img/categories/my3b31587325429674.png'),
                'category_description' => 'Air conditioning and HVAC system services.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Swimming Pool Maintenance',
                'category_slug' => 'swimming-pool-maintenance',
                'category_image' => url('assets/img/categories/6lq0w1575037821799.png'),
                'category_description' => 'Maintenance services for swimming pools.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Carpentry & Furniture Repairs',
                'category_slug' => 'carpentry-furniture-repairs',
                'category_image' => url('assets/img/categories/carpentry.png'),
                'category_description' => 'Carpentry services and furniture repair.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Interior Decorations',
                'category_slug' => 'interior-decorations',
                'category_image' => url('assets/img/categories/bg8opa1588009591000.png'),
                'category_description' => 'Interior decoration services for homes and offices.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Cleaning & Fumigation Services',
                'category_slug' => 'cleaning-fumigation-services',
                'category_image' => url('assets/img/categories/cleaning.png'),
                'category_description' => 'Cleaning and fumigation services for residential and commercial spaces.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Gardening & Landscaping',
                'category_slug' => 'gardening-landscaping',
                'category_image' => url('assets/img/categories/flowers.png'),
                'category_description' => 'Gardening and landscaping services for home and business properties.'
            ],
            [
                'parent_category' => 'Furniture & Appliances', // replace with the ID of the service_area
                'category_name' => 'Home Electronics',
                'category_slug' => 'home-electronics',
                'category_image' => url('assets/img/categories/domestic.png'),
                'category_description' => 'Home electronic appliances installation and repair.'
            ],
            [
                'parent_category' => 'Furniture & Appliances', // replace with the ID of the service_area
                'category_name' => 'Office Equipment',
                'category_slug' => 'office-equipment',
                'category_image' => url('assets/img/categories/office-equipment.png'),
                'category_description' => 'Installation and maintenance of office equipment.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Safety & Security Systems',
                'category_slug' => 'safety-security-systems',
                'category_image' => url('assets/img/categories/fire-detection.png'),
                'category_description' => 'Safety and security system installation and repair services.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'ICT/Internet Services',
                'category_slug' => 'ict-internet-services',
                'category_image' => url('assets/img/categories/ppzlor1587367221435.png'),
                'category_description' => 'Information and communication technology services, including internet services.'
            ],
            [
                'parent_category' => 'Logistics & Events', // replace with the ID of the service_area
                'category_name' => 'Catering & Event Management Services',
                'category_slug' => 'catering-event-management-services',
                'category_image' => url('assets/img/categories/chandelier.png'),
                'category_description' => 'Catering and event management services.'
            ],
            [
                'parent_category' => 'Furniture & Appliances', // replace with the ID of the service_area
                'category_name' => 'Kitchen Appliances',
                'category_slug' => 'kitchen-appliances',
                'category_image' => url('assets/img/categories/ygljz91587366015122.png'),
                'category_description' => 'Kitchen appliance installation and maintenance.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Waste Management & Environmental Services',
                'category_slug' => 'waste-management-environmental-services',
                'category_image' => url('assets/img/categories/sewage.png'),
                'category_description' => 'Waste management and environmental services.'
            ],
            [
                'parent_category' => 'Logistics & Events', // replace with the ID of the service_area
                'category_name' => 'Warehousing Logistics and Relocation Services',
                'category_slug' => 'warehousing-logistics-relocation-services',
                'category_image' => url('assets/img/categories/warehouse.png'),
                'category_description' => 'Logistics and relocation services, including warehousing.'
            ],
            [
                'parent_category' => 'Furniture & Appliances', // replace with the ID of the service_area
                'category_name' => 'Home Furniture',
                'category_slug' => 'home-furniture',
                'category_image' => url('assets/img/categories/1pe9uo1588009179310.png'),
                'category_description' => 'Installation and maintenance of home furniture.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Housekeeping / Domestic Supports',
                'category_slug' => 'housekeeping-domestic-supports',
                'category_image' => url('assets/img/categories/vgb6b1606120894443.png'),
                'category_description' => 'Housekeeping and domestic support services.'
            ],
            [
                'parent_category' => 'Logistics & Events', // replace with the ID of the service_area
                'category_name' => 'Serviced Offices and Apartments',
                'category_slug' => 'serviced-offices-apartments',
                'category_image' => url('assets/img/categories/6fvxkn1588939913405.png'),
                'category_description' => 'Serviced offices and apartments management services.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Renewable Energy Solutions',
                'category_slug' => 'renewable-energy-solutions',
                'category_image' => url('assets/img/categories/ipt8q1600059586337.png'),
                'category_description' => 'Renewable energy solutions and services.'
            ],
            [
                'parent_category' => 'Logistics & Events', // replace with the ID of the service_area
                'category_name' => 'Pickup & Delivery',
                'category_slug' => 'pickup-delivery',
                'category_image' => url('assets/img/categories/q6wcfq1606121332084.png'),
                'category_description' => 'Pickup and delivery services.'
            ],
            [
                'parent_category' => 'Maintenance & Repairs', // replace with the ID of the service_area
                'category_name' => 'Facility Inspection Service',
                'category_slug' => 'facility-inspection-service',
                'category_image' => url('assets/img/categories/2ono61644407950594.PNG'),
                'category_description' => 'Inspection services for facilities and buildings.'
            ]
        ];

        foreach ($categories as $category) {
            $service_area = ServiceArea::where('service_area_title', $category['parent_category'])->first();

            if ($service_area) {
                Category::firstOrCreate(
                    ['category_slug' => $category['category_slug']],
                    [
                        'id' => generate_uuid(),
                        'parent_category' => $service_area->id,
                        'category_name' => $category['category_name'],
                        'category_image' => $category['category_image'],
                        'category_description' => $category['category_description']
                    ]
                );
            }
        }


        // services 
        $services = json_decode('[
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Burning & Small Smoke",
              "service_slug": "burning-small-smoke"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Circuit Breakers",
              "service_slug": "circuit-breakers"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Electrical  Fittings",
              "service_slug": "electrical-fittings"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Fluctuating Power",
              "service_slug": "fluctuating-power"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Generator Maintenance",
              "service_slug": "generator-maintenance"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Lights",
              "service_slug": "lights"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Loss of Power",
              "service_slug": "loss-of-power"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Panel & Fuse Box",
              "service_slug": "panel-fuse-box"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Smoke Detectors",
              "service_slug": "smoke-detectors"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Socket Outlets",
              "service_slug": "socket-outlets"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Sparks & Popping bonds",
              "service_slug": "sparks-popping-bonds"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Switches",
              "service_slug": "switches"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Wiring",
              "service_slug": "wiring"
            },
            {
              "scat_category": "Electrical & Power Systems",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Blocked Drains",
              "service_slug": "blocked-drains"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Borehole",
              "service_slug": "borehole"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Burst / Leaking Pipes",
              "service_slug": "burst-leaking-pipes"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Dishwasher",
              "service_slug": "dishwasher"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Garden  Sprinkler",
              "service_slug": "garden-sprinkler"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Low / High Water Pressure",
              "service_slug": "low-high-water-pressure"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Outdoor Plumbing",
              "service_slug": "outdoor-plumbing"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Septic Evacuation / Sewage Treatment",
              "service_slug": "septic-evacuation-sewage-treatment"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Service Water Dispenser",
              "service_slug": "service-water-dispenser"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Shower / Bathtub",
              "service_slug": "shower-bathtub"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Sink / Faucet",
              "service_slug": "sink-faucet"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Toilet WC not Flushing",
              "service_slug": "toilet-wc-not-flushing"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Unpleasant  Odour",
              "service_slug": "unpleasant-odour"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Washing Machine",
              "service_slug": "washing-machine"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Water Heater",
              "service_slug": "water-heater"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Water Treatment Plant",
              "service_slug": "water-treatment-plant"
            },
            {
              "scat_category": "Plumbing, Water & Sewage",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Building Renovations & Refurbishment",
              "service_slug": "building-renovations-refurbishment"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Ceiling Maintenance",
              "service_slug": "ceiling-maintenance"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Cracked Wall Repairs",
              "service_slug": "cracked-wall-repairs"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Internal Roads and Drains Maintenance",
              "service_slug": "internal-roads-and-drains-maintenance"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Laying and Repair of Interlocking Stones",
              "service_slug": "laying-and-repair-of-interlocking-stones"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Painting",
              "service_slug": "painting"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Plastering",
              "service_slug": "plastering"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Roofing & Maintenance",
              "service_slug": "roofing-maintenance"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Space Planning & Remodeling",
              "service_slug": "space-planning-remodeling"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Tiling",
              "service_slug": "tiling"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Water Proofing",
              "service_slug": "water-proofing"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Welding and Metal Works",
              "service_slug": "welding-and-metal-works"
            },
            {
              "scat_category": "Civil Works & Painting",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Locksmith",
              "service_name": "Auto and residential",
              "service_slug": "auto-and-residential"
            },
            {
              "scat_category": "Locksmith",
              "service_name": "Fixing / repair of lock",
              "service_slug": "fixing-repair-of-lock"
            },
            {
              "scat_category": "Locksmith",
              "service_name": "Repair and servicing of safe and vault",
              "service_slug": "repair-and-servicing-of-safe-and-vault"
            },
            {
              "scat_category": "Locksmith",
              "service_name": "Transponder key programming",
              "service_slug": "transponder-key-programming"
            },
            {
              "scat_category": "Locksmith",
              "service_name": "Vehicle ignition lock repair",
              "service_slug": "vehicle-ignition-lock-repair"
            },
            {
              "scat_category": "Air Conditioning / HVAC Systems",
              "service_name": "HVAC (Chilled, Air Handling Units, etc)",
              "service_slug": "hvac-chilled-air-handling-units-etc"
            },
            {
              "scat_category": "Air Conditioning / HVAC Systems",
              "service_name": "Packaged Unit AC",
              "service_slug": "packaged-unit-ac"
            },
            {
              "scat_category": "Air Conditioning / HVAC Systems",
              "service_name": "Portable AC",
              "service_slug": "portable-ac"
            },
            {
              "scat_category": "Air Conditioning / HVAC Systems",
              "service_name": "Split Unit AC",
              "service_slug": "split-unit-ac"
            },
            {
              "scat_category": "Air Conditioning / HVAC Systems",
              "service_name": "Window Units AC",
              "service_slug": "window-units-ac"
            },
            {
              "scat_category": "Air Conditioning / HVAC Systems",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Swimming Pool Maintenance",
              "service_name": "Dosing System",
              "service_slug": "dosing-system"
            },
            {
              "scat_category": "Swimming Pool Maintenance",
              "service_name": "Pumps",
              "service_slug": "pumps"
            },
            {
              "scat_category": "Swimming Pool Maintenance",
              "service_name": "Swimming Pool Maintenance",
              "service_slug": "swimming-pool-maintenance"
            },
            {
              "scat_category": "Swimming Pool Maintenance",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Carpentry & Furniture Repairs",
              "service_name": "Ceiling & POP",
              "service_slug": "ceiling-pop"
            },
            {
              "scat_category": "Carpentry & Furniture Repairs",
              "service_name": "Doors & Windows",
              "service_slug": "doors-windows"
            },
            {
              "scat_category": "Carpentry & Furniture Repairs",
              "service_name": "Furniture Repairs",
              "service_slug": "furniture-repairs"
            },
            {
              "scat_category": "Carpentry & Furniture Repairs",
              "service_name": "Glass Installation / Repair / Replacement",
              "service_slug": "glass-installation-repair-replacement"
            },
            {
              "scat_category": "Carpentry & Furniture Repairs",
              "service_name": "Locks Repair / Replacement",
              "service_slug": "locks-repair-replacement"
            },
            {
              "scat_category": "Carpentry & Furniture Repairs",
              "service_name": "Shelves / Cabinets",
              "service_slug": "shelves-cabinets"
            },
            {
              "scat_category": "Carpentry & Furniture Repairs",
              "service_name": "Stairs",
              "service_slug": "stairs"
            },
            {
              "scat_category": "Carpentry & Furniture Repairs",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Interior Decorations",
              "service_name": "Carpeting and Rug Laying",
              "service_slug": "carpeting-and-rug-laying"
            },
            {
              "scat_category": "Interior Decorations",
              "service_name": "Curtains and Window Blinds",
              "service_slug": "curtains-and-window-blinds"
            },
            {
              "scat_category": "Interior Decorations",
              "service_name": "Event Hall Decorations",
              "service_slug": "event-hall-decorations"
            },
            {
              "scat_category": "Interior Decorations",
              "service_name": "Home Decoration",
              "service_slug": "home-decoration"
            },
            {
              "scat_category": "Interior Decorations",
              "service_name": "Wall Paper",
              "service_slug": "wall-paper"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Deep Cleaning (Internal & External  Env.)",
              "service_slug": "deep-cleaning-internal-external-env"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Domestic Cleaning Services",
              "service_slug": "domestic-cleaning-services"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Fumigation",
              "service_slug": "fumigation"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Housekeeping and domestic support",
              "service_slug": "housekeeping-and-domestic-support"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Interlocking / Paving Stones Cleaning",
              "service_slug": "interlocking-paving-stones-cleaning"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Pest Control",
              "service_slug": "pest-control"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Refuse Collection & Removal",
              "service_slug": "refuse-collection-removal"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Roads and Drains Cleaning",
              "service_slug": "roads-and-drains-cleaning"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Rodents Control",
              "service_slug": "rodents-control"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Street Sweeping",
              "service_slug": "street-sweeping"
            },
            {
              "scat_category": "Cleaning & Fumigation Services",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Gardening & Landscaping",
              "service_name": "Fertilization  and Weed Control",
              "service_slug": "fertilization-and-weed-control"
            },
            {
              "scat_category": "Gardening & Landscaping",
              "service_name": "Flower Pots",
              "service_slug": "flower-pots"
            },
            {
              "scat_category": "Gardening & Landscaping",
              "service_name": "Gardening Services",
              "service_slug": "gardening-services"
            },
            {
              "scat_category": "Gardening & Landscaping",
              "service_name": "Gardening Tools and Equipment",
              "service_slug": "gardening-tools-and-equipment"
            },
            {
              "scat_category": "Gardening & Landscaping",
              "service_name": "Horticultural/Landscaping",
              "service_slug": "horticulturallandscaping"
            },
            {
              "scat_category": "Gardening & Landscaping",
              "service_name": "Landfill Revegetation",
              "service_slug": "landfill-revegetation"
            },
            {
              "scat_category": "Gardening & Landscaping",
              "service_name": "Lawn Mowing",
              "service_slug": "lawn-mowing"
            },
            {
              "scat_category": "Gardening & Landscaping",
              "service_name": "Parks and Open Space Services",
              "service_slug": "parks-and-open-space-services"
            },
            {
              "scat_category": "Gardening & Landscaping",
              "service_name": "Tree Trimming",
              "service_slug": "tree-trimming"
            },
            {
              "scat_category": "Gardening & Landscaping",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Dish washer",
              "service_slug": "dish-washer"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "DSTV Services",
              "service_slug": "dstv-services"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Electric Cooker",
              "service_slug": "electric-cooker"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Fan",
              "service_slug": "fan"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Gas Cooker",
              "service_slug": "gas-cooker"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Humidifier",
              "service_slug": "humidifier"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Microwave",
              "service_slug": "microwave"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Refrigerators & Freezers",
              "service_slug": "refrigerators-freezers"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Television",
              "service_slug": "television"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Washing Machine",
              "service_slug": "washing-machine"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Water  Heater",
              "service_slug": "water-heater"
            },
            {
              "scat_category": "Home Electronics",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Audio -Visual Equipment",
              "service_slug": "audio-visual-equipment"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Computers and Laptop",
              "service_slug": "computers-and-laptop"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Doors Labels & Directional Signs",
              "service_slug": "doors-labels-directional-signs"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Intercom Services",
              "service_slug": "intercom-services"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Internet Services",
              "service_slug": "internet-services"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Office Accessories and Toiletries Supply",
              "service_slug": "office-accessories-and-toiletries-supply"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Office Furnishing",
              "service_slug": "office-furnishing"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Office Space Planning  & Remodelling",
              "service_slug": "office-space-planning-remodelling"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Photocopiers, Printets and Scannner",
              "service_slug": "photocopiers-printets-and-scannner"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Projectors",
              "service_slug": "projectors"
            },
            {
              "scat_category": "Office Equipment",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Safety & Security Systems",
              "service_name": "Body Scanner",
              "service_slug": "body-scanner"
            },
            {
              "scat_category": "Safety & Security Systems",
              "service_name": "CCTV",
              "service_slug": "cctv"
            },
            {
              "scat_category": "Safety & Security Systems",
              "service_name": "Electric Fence",
              "service_slug": "electric-fence"
            },
            {
              "scat_category": "Safety & Security Systems",
              "service_name": "Fire Alarm",
              "service_slug": "fire-alarm"
            },
            {
              "scat_category": "Safety & Security Systems",
              "service_name": "Intruder Detection / Access Control",
              "service_slug": "intruder-detection-access-control"
            },
            {
              "scat_category": "Safety & Security Systems",
              "service_name": "Motorised Gate",
              "service_slug": "motorised-gate"
            },
            {
              "scat_category": "Safety & Security Systems",
              "service_name": "Smoke  & Heat Detectors",
              "service_slug": "smoke-heat-detectors"
            },
            {
              "scat_category": "Safety & Security Systems",
              "service_name": "Sprinkler System",
              "service_slug": "sprinkler-system"
            },
            {
              "scat_category": "Safety & Security Systems",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "ICT/Internet Services",
              "service_name": "Doorbell",
              "service_slug": "doorbell"
            },
            {
              "scat_category": "ICT/Internet Services",
              "service_name": "Intercom Telephone Connectivity",
              "service_slug": "intercom-telephone-connectivity"
            },
            {
              "scat_category": "ICT/Internet Services",
              "service_name": "Internet  Connection",
              "service_slug": "internet-connection"
            },
            {
              "scat_category": "ICT/Internet Services",
              "service_name": "Laptop / Desktop Computers",
              "service_slug": "laptop-desktop-computers"
            },
            {
              "scat_category": "ICT/Internet Services",
              "service_name": "Laptop / Desktop Monitor",
              "service_slug": "laptop-desktop-monitor"
            },
            {
              "scat_category": "ICT/Internet Services",
              "service_name": "Local Area Connection Problem (LAN)",
              "service_slug": "local-area-connection-problem-lan"
            },
            {
              "scat_category": "ICT/Internet Services",
              "service_name": "Others (Specify)",
              "service_slug": "others-specify"
            },
            {
              "scat_category": "ICT/Internet Services",
              "service_name": "Poor Telephone Network Reception",
              "service_slug": "poor-telephone-network-reception"
            },
            {
              "scat_category": "ICT/Internet Services",
              "service_name": "Video Intercom Connectivity",
              "service_slug": "video-intercom-connectivity"
            },
            {
              "scat_category": "ICT/Internet Services",
              "service_name": "Wide Area Network Problem (WAN)",
              "service_slug": "wide-area-network-problem-wan"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Cake Baking & Supply",
              "service_slug": "cake-baking-supply"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Catering",
              "service_slug": "catering"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Corporate Event Organisers",
              "service_slug": "corporate-event-organisers"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Deejay / Comedians",
              "service_slug": "deejay-comedians"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Hall and Space Rentals",
              "service_slug": "hall-and-space-rentals"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Master Of Ceremony",
              "service_slug": "master-of-ceremony"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Photography & Video Coverage",
              "service_slug": "photography-video-coverage"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Social Event Organiser",
              "service_slug": "social-event-organiser"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Tea Coffers",
              "service_slug": "tea-coffers"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Undertakers",
              "service_slug": "undertakers"
            },
            {
              "scat_category": "Catering & Event Management Services",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Kitchen Appliances",
              "service_name": "Biceps Curl Bench",
              "service_slug": "biceps-curl-bench"
            },
            {
              "scat_category": "Kitchen Appliances",
              "service_name": "Cable Row Machine",
              "service_slug": "cable-row-machine"
            },
            {
              "scat_category": "Kitchen Appliances",
              "service_name": "Chest Fly Machine",
              "service_slug": "chest-fly-machine"
            },
            {
              "scat_category": "Kitchen Appliances",
              "service_name": "Chest Press Machine",
              "service_slug": "chest-press-machine"
            },
            {
              "scat_category": "Kitchen Appliances",
              "service_name": "Gymnasium Equipment Supplies",
              "service_slug": "gymnasium-equipment-supplies"
            },
            {
              "scat_category": "Kitchen Appliances",
              "service_name": "Shoulder Press Machine",
              "service_slug": "shoulder-press-machine"
            },
            {
              "scat_category": "Kitchen Appliances",
              "service_name": "Treadmill",
              "service_slug": "treadmill"
            },
            {
              "scat_category": "Kitchen Appliances",
              "service_name": "Triceps Extension Bar",
              "service_slug": "triceps-extension-bar"
            },
            {
              "scat_category": "Kitchen Appliances",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Waste Management & Environmental Services",
              "service_name": "Clinical Waste Management",
              "service_slug": "clinical-waste-management"
            },
            {
              "scat_category": "Waste Management & Environmental Services",
              "service_name": "Construction Waste Removal",
              "service_slug": "construction-waste-removal"
            },
            {
              "scat_category": "Waste Management & Environmental Services",
              "service_name": "Glass / Plastic / E-Waste Disposal",
              "service_slug": "glass-plastic-e-waste-disposal"
            },
            {
              "scat_category": "Waste Management & Environmental Services",
              "service_name": "Hazardous Waste Management",
              "service_slug": "hazardous-waste-management"
            },
            {
              "scat_category": "Waste Management & Environmental Services",
              "service_name": "Liquid Waste Evacuation",
              "service_slug": "liquid-waste-evacuation"
            },
            {
              "scat_category": "Waste Management & Environmental Services",
              "service_name": "Refuse Collection and Disposal",
              "service_slug": "refuse-collection-and-disposal"
            },
            {
              "scat_category": "Waste Management & Environmental Services",
              "service_name": "Spillage Clean-Up",
              "service_slug": "spillage-clean-up"
            },
            {
              "scat_category": "Waste Management & Environmental Services",
              "service_name": "Waste Treatment",
              "service_slug": "waste-treatment"
            },
            {
              "scat_category": "Waste Management & Environmental Services",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Warehousing Logistics  and Relocation Services",
              "service_name": "Home Relocation Services",
              "service_slug": "home-relocation-services"
            },
            {
              "scat_category": "Warehousing Logistics  and Relocation Services",
              "service_name": "Logistics Services",
              "service_slug": "logistics-services"
            },
            {
              "scat_category": "Warehousing Logistics  and Relocation Services",
              "service_name": "Mailing Services",
              "service_slug": "mailing-services"
            },
            {
              "scat_category": "Warehousing Logistics  and Relocation Services",
              "service_name": "Movers",
              "service_slug": "movers"
            },
            {
              "scat_category": "Warehousing Logistics  and Relocation Services",
              "service_name": "Office Relocation Services",
              "service_slug": "office-relocation-services"
            },
            {
              "scat_category": "Warehousing Logistics  and Relocation Services",
              "service_name": "Storage/Safe Equipment",
              "service_slug": "storagesafe-equipment"
            },
            {
              "scat_category": "Warehousing Logistics  and Relocation Services",
              "service_name": "Temporary Storage of Personal Effects",
              "service_slug": "temporary-storage-of-personal-effects"
            },
            {
              "scat_category": "Warehousing Logistics  and Relocation Services",
              "service_name": "Warehousing/Storage Services",
              "service_slug": "warehousingstorage-services"
            },
            {
              "scat_category": "Warehousing Logistics  and Relocation Services",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Beautician",
              "service_slug": "beautician"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Body Massage",
              "service_slug": "body-massage"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Collection  and Delivery Services",
              "service_slug": "collection-and-delivery-services"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Errand Services",
              "service_slug": "errand-services"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Fitness Trainers",
              "service_slug": "fitness-trainers"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Gele Tiers",
              "service_slug": "gele-tiers"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Home Barbing",
              "service_slug": "home-barbing"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Home Cooking",
              "service_slug": "home-cooking"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Home Doctor",
              "service_slug": "home-doctor"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Home Shopping Assistance",
              "service_slug": "home-shopping-assistance"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Home Tutor Services",
              "service_slug": "home-tutor-services"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "House Party Services",
              "service_slug": "house-party-services"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Housekeeping",
              "service_slug": "housekeeping"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Ladies Hair Styling",
              "service_slug": "ladies-hair-styling"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Laundry Services",
              "service_slug": "laundry-services"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Manicure & Pedicure",
              "service_slug": "manicure-pedicure"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Nursing",
              "service_slug": "nursing"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Personal Nutritionist",
              "service_slug": "personal-nutritionist"
            },
            {
              "scat_category": "Home Furniture",
              "service_name": "Others",
              "service_slug": "others"
            },
            {
              "scat_category": "Serviced Offices and Apartments",
              "service_name": "Rent Coliving Space",
              "service_slug": "rent-coliving-space"
            },
            {
              "scat_category": "Serviced Offices and Apartments",
              "service_name": "Rent Coworking Space",
              "service_slug": "rent-coworking-space"
            },
            {
              "scat_category": "Serviced Offices and Apartments",
              "service_name": "Rent Serviced Apartment",
              "service_slug": "rent-serviced-apartment"
            },
            {
              "scat_category": "Renewable Energy Solutions",
              "service_name": "Inverter Power Backup System",
              "service_slug": "inverter-power-backup-system"
            },
            {
              "scat_category": "Renewable Energy Solutions",
              "service_name": "Solar Power Backup System",
              "service_slug": "solar-power-backup-system"
            },
            {
              "scat_category": "Renewable Energy Solutions",
              "service_name": "Wind Turbine System",
              "service_slug": "wind-turbine-system"
            },
            {
              "scat_category": "Pickup & Delivery",
              "service_name": "Pickup and Delivery",
              "service_slug": "pickup-and-delivery"
            },
            {
              "scat_category": "Facility Inspection Service",
              "service_name": "Monthly Routine",
              "service_slug": "monthly-routine"
            },
            {
              "scat_category": "Facility Inspection Service",
              "service_name": "One-Off",
              "service_slug": "one-off"
            },
            {
              "scat_category": "Facility Inspection Service",
              "service_name": "Quarterly Routine",
              "service_slug": "quarterly-routine"
            },
            {
              "scat_category": "Facility Inspection Service",
              "service_name": "Weekly Routine",
              "service_slug": "weekly-routine"
            }
        ]', true);

        foreach($services as $service) {
            $service_category = Category::where('category_name', $service['scat_category'])->first();

            if ($service_area) {
                Service::firstOrCreate(
                    ['service_slug' => $service['service_slug']],
                    [
                        "id" => generate_uuid(),
                        "category_id" => $service_category->id ?? generate_uuid(),
                        "service_name" => $service['service_name'],
                        "service_slug" => $service['service_slug']
                    ]
                );
            }
        }   
    }
}
