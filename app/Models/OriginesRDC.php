<?php

namespace App\Models;

class OriginesRDC
{
    private const PROVINCES = [
        'Bas-Uele' => [
            'chef_lieu' => 'Buta',
            'territoires' => ['Aketi', 'Ango', 'Bambesa', 'Bondo', 'Buta', 'Poko'],
        ],
        'Équateur' => [
            'chef_lieu' => 'Mbandaka',
            'territoires' => ['Basankusu', 'Bikoro', 'Bolomba', 'Bomongo', 'Ingende', 'Lukolela', 'Makanza'],
        ],
        'Haut-Katanga' => [
            'chef_lieu' => 'Lubumbashi',
            'territoires' => ['Kambove', 'Kasenga', 'Mitwaba', 'Pweto', 'Sakania'],
        ],
        'Haut-Lomami' => [
            'chef_lieu' => 'Kamina',
            'territoires' => ['Bukama', 'Kabongo', 'Kamina', 'Kanyama', 'Malemba-Nkulu'],
        ],
        'Haut-Uele' => [
            'chef_lieu' => 'Isiro',
            'territoires' => ['Dungu', 'Faradje', 'Niangara', 'Rungu', 'Wamba', 'Watsa'],
        ],
        'Ituri' => [
            'chef_lieu' => 'Bunia',
            'territoires' => ['Aru', 'Djugu', 'Irumu', 'Mahagi', 'Mambasa'],
        ],
        'Kasaï' => [
            'chef_lieu' => 'Tshikapa',
            'territoires' => ['Dekese', 'Ilebo', 'Luebo', 'Mweka'],
        ],
        'Kasaï-Central' => [
            'chef_lieu' => 'Kananga',
            'territoires' => ['Demba', 'Dibaya', 'Dimbelenge', 'Kazumba', 'Luiza'],
        ],
        'Kasaï-Oriental' => [
            'chef_lieu' => 'Mbuji-Mayi',
            'territoires' => ['Kabeya Kamwanga', 'Katanda', 'Lupatapata', 'Miabi', 'Tshilenge'],
        ],
        'Kongo-Central' => [
            'chef_lieu' => 'Matadi',
            'territoires' => ['Kasangulu', 'Kimvula', 'Lukula', 'Luozi', 'Madimba', 'Mbanza Ngungu', 'Muanda', 'Seke Banza', 'Songololo', 'Tshela'],
        ],
        'Kwango' => [
            'chef_lieu' => 'Kenge',
            'territoires' => ['Feshi', 'Kahemba', 'Kasongo Lunda', 'Kenge', 'Popokabaka'],
        ],
        'Kwilu' => [
            'chef_lieu' => 'Bandundu',
            'territoires' => ['Bagata', 'Bulungu', 'Gungu', 'Idiofa', 'Masimanimba'],
        ],
        'Lomami' => [
            'chef_lieu' => 'Kabinda',
            'territoires' => ['Kabinda', 'Kamiji', 'Lubao', 'Luilu', 'Ngandajika'],
        ],
        'Lualaba' => [
            'chef_lieu' => 'Kolwezi',
            'territoires' => ['Dilolo', 'Kapanga', 'Lubudi', 'Mutshatsha', 'Sandoa'],
        ],
        'Maï-Ndombe' => [
            'chef_lieu' => 'Inongo',
            'territoires' => ['Bolobo', 'Inongo', 'Kiri', 'Kutu', 'Kwamouth', 'Mushie', 'Oshwe', 'Yumbi'],
        ],
        'Maniema' => [
            'chef_lieu' => 'Kindu',
            'territoires' => ['Kabambare', 'Kailo', 'Kasongo', 'Kibombo', 'Pangi', 'Punia', 'Lubutu'],
        ],
        'Mongala' => [
            'chef_lieu' => 'Lisala',
            'territoires' => ['Bongandanga', 'Bumba', 'Lisala'],
        ],
        'Nord-Kivu' => [
            'chef_lieu' => 'Goma',
            'territoires' => ['Beni', 'Lubero', 'Masisi', 'Nyiragongo', 'Rutshuru', 'Walikale'],
        ],
        'Nord-Ubangi' => [
            'chef_lieu' => 'Gbadolite',
            'territoires' => ['Bosobolo', 'Businga', 'Mobayi Mbongo', 'Yakoma'],
        ],
        'Sud-Ubangi' => [
            'chef_lieu' => 'Gemena',
            'territoires' => ['Budjala', 'Gemena', 'Kungu', 'Libenge'],
        ],
        'Sud-Kivu' => [
            'chef_lieu' => 'Bukavu',
            'territoires' => ['Fizi', 'Idjwi', 'Kabare', 'Kalehe', 'Mwenga', 'Shabunda', 'Uvira', 'Walungu'],
        ],
        'Sankuru' => [
            'chef_lieu' => 'Lusambo',
            'territoires' => ['Katako Kombe', 'Kole', 'Lodja', 'Lomela', 'Lubefu', 'Lusambo'],
        ],
        'Tanganyika' => [
            'chef_lieu' => 'Kalemie',
            'territoires' => ['Kabalo', 'Kalemie', 'Kongolo', 'Manono', 'Moba', 'Nyunzu'],
        ],
        'Tshopo' => [
            'chef_lieu' => 'Kisangani',
            'territoires' => ['Bafwasende', 'Banalia', 'Basoko', 'Isangi', 'Opala', 'Ubundu', 'Yahuma'],
        ],
        'Tshuapa' => [
            'chef_lieu' => 'Boende',
            'territoires' => ['Befale', 'Boende', 'Bokungu', 'Djolu', 'Ikela', 'Monkoto'],
        ],
        'Kinshasa' => [
            'chef_lieu' => 'Kinshasa',
            'territoires' => [
                'Masina', 'Lemba', 'Bandalungwa', 'Barumbu', 'Bumbu', 'Gombe',
                'Kalamu', 'Kasavubu', 'Kimbanseke', 'Kinshasa', 'Kintambo',
                'Kinsenso', 'Limete', 'Lingwala', 'Maluku', 'Matete', 'Makala',
                'Mont-Ngafula', 'N’Djili', 'Ngaba', 'Ngaliema', 'Ngiri-Ngiri',
                'Nsele', 'Selembao',
            ],
            'territoire_label' => 'Commune',
        ],
    ];

    public static function provinces(): array
    {
        $provinces = [];
        foreach (self::PROVINCES as $name => $data) {
            $provinces[] = ['nom' => $name, 'chef_lieu' => $data['chef_lieu']];
        }
        return $provinces;
    }

    public static function territoriesFor(string $province): array
    {
        return self::PROVINCES[self::normalizeProvince($province)]['territoires'] ?? [];
    }

    public static function isKnownProvince(string $province): bool
    {
        return isset(self::PROVINCES[self::normalizeProvince($province)]);
    }

    public static function territoryLabel(string $province): string
    {
        return self::PROVINCES[self::normalizeProvince($province)]['territoire_label'] ?? 'Territoire';
    }

    public static function normalizeProvince(string $province): string
    {
        return $province === 'Mai-Ndombe' ? 'Maï-Ndombe' : $province;
    }
}
