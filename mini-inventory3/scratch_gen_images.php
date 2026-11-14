<?php
$slides = [
    [
        "file" => "la.jpg",
        "title" => "Pusat Distribusi Logistik Modern",
        "subtitle" => "Los Angeles Hub - Fasilitas Pergudangan 24/7",
        "tag" => "FACILITY SPOTLIGHT",
        "color1" => [30, 58, 138],
        "color2" => [15, 23, 42]
    ],
    [
        "file" => "chicago.jpg",
        "title" => "Manajemen Inventaris Cerdas",
        "subtitle" => "Chicago Fulfillment Center - Sinkronisasi Stok Real-Time",
        "tag" => "SMART WAREHOUSING",
        "color1" => [24, 73, 115],
        "color2" => [17, 24, 39]
    ],
    [
        "file" => "ny.jpg",
        "title" => "Sistem Otomasi & Logistik Kilat",
        "subtitle" => "New York Logistics Base - Pelacakan Produk Akurat",
        "tag" => "AUTOMATION SUITE",
        "color1" => [51, 65, 85],
        "color2" => [15, 23, 42]
    ]
];

foreach ($slides as $s) {
    $w = 1200;
    $h = 420;
    $im = imagecreatetruecolor($w, $h);

    for ($y = 0; $y < $h; $y++) {
        $ratio = $y / $h;
        $r = (int)($s["color1"][0] * (1 - $ratio) + $s["color2"][0] * $ratio);
        $g = (int)($s["color1"][1] * (1 - $ratio) + $s["color2"][1] * $ratio);
        $b = (int)($s["color1"][2] * (1 - $ratio) + $s["color2"][2] * $ratio);
        $color = imagecolorallocate($im, $r, $g, $b);
        imageline($im, 0, $y, $w, $y, $color);
    }

    $gridColor = imagecolorallocatealpha($im, 255, 255, 255, 118);
    for ($x = 0; $x < $w; $x += 60) {
        imageline($im, $x, 0, $x, $h, $gridColor);
    }
    for ($y = 0; $y < $h; $y += 60) {
        imageline($im, 0, $y, $w, $y, $gridColor);
    }

    $accentColor = imagecolorallocatealpha($im, 59, 130, 246, 105);
    imagefilledellipse($im, 980, 210, 360, 360, $accentColor);

    $white = imagecolorallocate($im, 255, 255, 255);
    $cyan = imagecolorallocate($im, 56, 189, 248);
    $gray = imagecolorallocate($im, 203, 213, 225);

    imagestring($im, 5, 80, 130, "MINI INVENTORY // " . $s["tag"], $cyan);
    imagestring($im, 5, 80, 175, $s["title"], $white);
    imagestring($im, 4, 80, 215, $s["subtitle"], $gray);
    imagestring($im, 3, 80, 265, "File: " . $s["file"] . " | Rasio: 1200x420 px", $gray);

    imagejpeg($im, $s["file"], 92);
    imagedestroy($im);
}
echo "Sample images created successfully!\n";
