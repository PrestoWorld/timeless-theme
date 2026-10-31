<?php
/**
 * BookPress Template Canvas for Timeless Theme.
 *
 * Provides the full HTML shell with Google Fonts (Be Vietnam Pro, Newsreader)
 * and compiled Tailwind CSS matching the bookowl-timeless design.
 *
 * @package BookPress
 * @subpackage Timeless
 */

$content = '';
if (isset($regions) && is_array($regions) && isset($regions['content'])) {
    $content = (string) $regions['content'];
} elseif (isset($data) && is_array($data) && isset($data['content'])) {
    $content = (string) $data['content'];
} elseif (isset($BP_CONTENT)) {
    $content = (string) $BP_CONTENT;
}
$headCss = (string) ($BP_HEAD_CSS ?? '');
$storeName = (string) ($data['store_name'] ?? 'CheepHub');
$storeSlogan = (string) ($data['store_slogan'] ?? 'Tiệm Sách & Thuê Sách Toàn Quốc 20k/45 Ngày');
?><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?= htmlspecialchars($storeName . ' - ' . $storeSlogan, ENT_QUOTES) ?></title>
    <meta name="description" content="<?= htmlspecialchars($storeName . ' - Nền tảng mua bán, ký gửi và cho thuê sách cũ giá rẻ toàn quốc chỉ 20.000đ/45 ngày. Trải nghiệm đọc sách văn minh, tối giản và tiết kiệm.', ENT_QUOTES) ?>" />
    <meta property="og:title" content="<?= htmlspecialchars($storeName . ' - ' . $storeSlogan, ENT_QUOTES) ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($storeName . ' - Nền tảng mua bán, ký gửi và cho thuê sách cũ giá rẻ toàn quốc chỉ 20.000đ/45 ngày.', ENT_QUOTES) ?>" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/content/themes/timeless/assets/css/dist/tailwind.css">
    <?= $headCss ?>
</head>
<body class="bg-[#FAF9F5] text-[#1C1C1E] font-sans antialiased selection:bg-[#E05A38]/15 selection:text-[#E05A38]">
    <?= $BP_ADMIN_BAR ?? '' ?>
    <?= $content ?>
</body>
</html>

