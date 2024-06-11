<?php
date_default_timezone_set('Europe/Berlin');

$sport_feeds = [
    "TransfermarktDE" => "https://www.transfermarkt.de/rss/news",
    "Kicker" => "https://newsfeed.kicker.de/news/aktuell",
    "Sportschau" => "https://www.sportschau.de/fussball/index~rss2.xml"
];

$sport_articles = [];
foreach ($sport_feeds as $source => $url) {
    $rss = simplexml_load_file($url);
    if ($rss === false) {
        echo "Fehler beim Laden des RSS-Feeds von $source.";
        continue;
    }
    $count = 0;
    foreach ($rss->channel->item as $item) {
        if ($count >= 5) break;

        // Filter description to remove any image tags
        $description = (string)$item->description;
        $description = preg_replace('/<img[^>]+\>/i', '', $description);

        $sport_articles[] = [
            'title' => (string)$item->title,
            'link' => (string)$item->link,
            'source' => $source,
            'description' => $description,
            'pubDate' => date('d.m.Y H:i', strtotime((string)$item->pubDate))
        ];
        $count++;
    }
}

// Sort articles by publication date (newest first)
usort($sport_articles, function($a, $b) {
    return strtotime($b['pubDate']) - strtotime($a['pubDate']);
});

foreach ($sport_articles as $article) {
    echo '<div class="news-item">';
    echo '<div class="news-content">';
    echo '<div class="news-title">' . $article['title'] . '</div>';
    echo '<div class="news-source">' . $article['source'] . ' - ' . $article['pubDate'] . '</div>';
    echo '<p>' . $article['description'] . '</p>';
    echo '<a href="' . $article['link'] . '" class="news-link" target="_blank">Mehr lesen</a>';
    echo '</div></div>';
}
?>
