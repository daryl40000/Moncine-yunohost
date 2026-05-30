<?php
/**
 * Bouton export PDF (mêmes filtres / tri que la page courante).
 *
 * @var string $scope ExportPdfScope::COLLECTION|WISHLIST
 * @var string $returnUrl
 * @var string $sortBy
 * @var string $sortDir
 * @var string $query
 * @var string $kindFilter optionnel (collection)
 */
$query = $query ?? '';
$kindFilter = $kindFilter ?? '';
?>
<form method="post" action="/export-pdf.php" class="inline-form collection-export-pdf-form">
    <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
    <input type="hidden" name="scope" value="<?= Moncine\View::escape($scope) ?>">
    <input type="hidden" name="return" value="<?= Moncine\View::escape($returnUrl) ?>">
    <input type="hidden" name="sort" value="<?= Moncine\View::escape($sortBy) ?>">
    <input type="hidden" name="dir" value="<?= Moncine\View::escape($sortDir) ?>">
    <input type="hidden" name="q" value="<?= Moncine\View::escape($query) ?>">
    <?php if ($scope === Moncine\ExportPdfScope::COLLECTION && $kindFilter !== ''): ?>
        <input type="hidden" name="kind" value="<?= Moncine\View::escape($kindFilter) ?>">
    <?php endif; ?>
    <input type="hidden" name="include_posters" value="1">
    <button type="submit" class="btn btn-secondary">Exporter en PDF</button>
</form>
