<?php
$articles = $this->getVar("articles");
$title = $this->getVar("title");
?>
<h1 class="edit-form"><?= $title ?></h1>
<table style="width: 100%;border:1px solid #ddd;" class="liste-articles">
    <thead>
    <tr>
        <th style="border:1px solid #ddd;">
            Modifier
        </th>
        <th style="border:1px solid #ddd;">
            Catégorie
        </th>
        <th style="border:1px solid #ddd;">
            Titre
        </th>
        <th style="border:1px solid #ddd;">
            Date
        </th>
        <th style="border:1px solid #ddd;">
            Date pour tri
        </th>
        <th style="border:1px solid #ddd;">
            Public
        </th>
    </tr>
    </thead>
    <tbody>
<?php foreach($articles as $key=>$article): ?>
    <tr>
        <td style="border:1px solid #ddd;">
            <a title="Modifier" href="<?= __CA_URL_ROOT__ ?>/index.php/Articles/Edit/EditForm/template/article/id/<?= $article["page_id"] ?>">
                <i class="caIcon fa fa-file editIcon fa-2x" title="Modifier"></i>
            </a>
        </td>
        <td style="border:1px solid #ddd;">
            <?= $article["article"]["categ"] ?>
        </td>
        <td style="border:1px solid #ddd;">
            <b style="font-weight: 700;"><?= $article["title"] ?></b>
        </td>
        <td style="border:1px solid #ddd;">
            <?= $article["article"]["date"] ?>
        </td>
        <td style="border:1px solid #ddd;">
            <?= $article["article"]["date_sort"] ?>
        </td>
        <td style="border:1px solid #ddd;">
            <?= ($article["access"] == 1 ? "oui" : "non") ?>
        </td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>

<style>
    .liste-articles thead th {
        font-family: "Helvetica Neue", Arial, sans-serif;
        font-weight: 700;
        background-color: #eee;
        padding:4px 8px;
    }
    .liste-articles td {
        padding:4px 8px;
    }
</style>
