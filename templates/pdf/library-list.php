<?php
/**
 * HTML pour export PDF (Dompdf).
 *
 * @var string $title
 * @var list<string> $subtitleParts
 * @var list<array{titre: string, meta: string, support: string, styles: string, poster_src: string}> $rows
 * @var bool $truncated
 * @var bool $showPosterColumn
 */
$rowCount = count($rows);
$generatedAt = date('d/m/Y H:i');
$showPosterColumn = $showPosterColumn ?? false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    <style>
        @page { margin: 18mm 14mm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.35;
        }
        h1 {
            font-size: 16pt;
            margin: 0 0 4pt;
        }
        .meta-header {
            font-size: 9pt;
            color: #444;
            margin: 0 0 12pt;
        }
        .meta-header p { margin: 2pt 0; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #555;
            border-bottom: 1pt solid #999;
            padding: 4pt 6pt 4pt 0;
        }
        td {
            vertical-align: top;
            padding: 6pt 6pt 6pt 0;
            border-bottom: 0.5pt solid #ddd;
        }
        .col-poster { width: 36pt; padding-right: 8pt; }
        .col-poster img {
            width: 32pt;
            height: auto;
            max-height: 48pt;
            display: block;
        }
        .film-title { font-weight: bold; }
        .film-meta { font-size: 9pt; color: #444; }
        .film-extra { font-size: 8.5pt; color: #666; }
        .footer-note {
            margin-top: 10pt;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
    <div class="meta-header">
        <?php foreach ($subtitleParts as $part): ?>
            <p><?= htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php endforeach; ?>
        <p><?= (int) $rowCount ?> titre<?= $rowCount > 1 ? 's' : '' ?> — généré le <?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <?php if ($rows === []): ?>
        <p>Aucun titre à afficher avec les filtres actuels.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <?php if ($showPosterColumn): ?>
                        <th class="col-poster"></th>
                    <?php endif; ?>
                    <th>Titre</th>
                    <th>Support</th>
                    <th>Style</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php if ($showPosterColumn): ?>
                            <td class="col-poster">
                                <?php if (($row['poster_src'] ?? '') !== ''): ?>
                                    <img src="<?= htmlspecialchars($row['poster_src'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            <div class="film-title"><?= htmlspecialchars($row['titre'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                            <?php if ($row['meta'] !== ''): ?>
                                <div class="film-meta"><?= htmlspecialchars($row['meta'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="film-extra"><?= htmlspecialchars($row['support'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="film-extra"><?= htmlspecialchars($row['styles'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($truncated): ?>
        <p class="footer-note">
            Liste tronquée à <?= (int) \Moncine\ExportPdf::MAX_ROWS ?> titres. Affinez les filtres pour un export plus court.
        </p>
    <?php endif; ?>
    <p class="footer-note">Moncine — export personnel, ne pas diffuser sans accord.</p>
</body>
</html>
